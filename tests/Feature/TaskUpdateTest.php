<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TaskUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_all_task_fields(): void
    {
        $task = $this->createTask();

        $data = [
            'title' => 'Обновлённая задача',
            'description' => 'Новое описание',
            'due_date' => '2026-12-15 18:00:00',
            'create_date' => '2026-10-15 10:00:00',
            'status' => TaskStatus::Completed->value,
            'priority' => TaskPriority::High->value,
            'category' => 'Личное',
        ];

        $this->putJson("/api/tasks/{$task->id}", $data)
            ->assertOk()
            ->assertJsonPath('data.id', $task->id)
            ->assertJsonPath('data.title', $data['title'])
            ->assertJsonPath('data.description', $data['description'])
            ->assertJsonPath('data.status', $data['status'])
            ->assertJsonPath('data.priority', $data['priority'])
            ->assertJsonPath('data.category', $data['category']);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            ...$data,
        ]);
    }

    public function test_it_updates_only_the_title(): void
    {
        $task = $this->createTask();

        $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Новое название',
        ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Новое название');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Новое название',
        ]);
    }

    public function test_it_preserves_unchanged_fields(): void
    {
        $task = $this->createTask();

        $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Изменённое название',
        ])
            ->assertOk()
            ->assertJsonPath('data.description', 'Исходное описание')
            ->assertJsonPath('data.status', TaskStatus::Pending->value)
            ->assertJsonPath('data.priority', TaskPriority::Medium->value)
            ->assertJsonPath('data.category', 'Работа');

        $task->refresh();

        $this->assertSame(
            '2026-11-01 12:00:00',
            $task->due_date->format('Y-m-d H:i:s')
        );
    }

    public function test_it_updates_status_and_priority(): void
    {
        $task = $this->createTask();

        $this->putJson("/api/tasks/{$task->id}", [
            'status' => TaskStatus::Completed->value,
            'priority' => TaskPriority::High->value,
        ])
            ->assertOk()
            ->assertJsonPath('data.status', TaskStatus::Completed->value)
            ->assertJsonPath('data.priority', TaskPriority::High->value);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => TaskStatus::Completed->value,
            'priority' => TaskPriority::High->value,
        ]);
    }

    public function test_it_clears_nullable_fields(): void
    {
        $task = $this->createTask();

        $this->putJson("/api/tasks/{$task->id}", [
            'description' => null,
            'category' => null,
        ])
            ->assertOk()
            ->assertJsonPath('data.description', null)
            ->assertJsonPath('data.category', null);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'description' => null,
            'category' => null,
        ]);
    }

    public function test_it_returns_404_for_a_non_existing_task(): void
    {
        $this->putJson('/api/tasks/999999', [
            'title' => 'Новое название',
        ])
            ->assertNotFound();
    }

    #[DataProvider('invalidFieldsProvider')]
    public function test_it_rejects_invalid_field_values(
        string $field,
        mixed $value
    ): void {
        $task = $this->createTask();

        $this->putJson("/api/tasks/{$task->id}", [
            $field => $value,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$field]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Исходная задача',
            'description' => 'Исходное описание',
            'status' => TaskStatus::Pending->value,
            'priority' => TaskPriority::Medium->value,
        ]);

        $this->assertDatabaseCount('tasks', 1);
    }

    public static function invalidFieldsProvider(): array
    {
        return [
            'null title' => ['title', null],
            'long title' => ['title', str_repeat('A', 256)],
            'invalid title type' => ['title', ['array']],
            'null due date' => ['due_date', null],
            'invalid due date' => ['due_date', 'invalid-date'],
            'null create date' => ['create_date', null],
            'invalid create date' => ['create_date', 'invalid-date'],
            'null status' => ['status', null],
            'invalid status' => ['status', 'Неизвестный'],
            'invalid priority' => ['priority', 'Критический'],
            'long category' => ['category', str_repeat('A', 101)],
        ];
    }

    public function test_it_does_not_modify_task_on_validation_failure(): void
    {
        $task = $this->createTask();

        $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Новое название',
            'priority' => 'Недопустимый',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['priority']);

        $task->refresh();

        $this->assertSame('Исходная задача', $task->title);
        $this->assertSame(
            TaskPriority::Medium,
            $task->priority
        );
    }

    private function createTask(): Task
    {
        return Task::create([
            'title' => 'Исходная задача',
            'description' => 'Исходное описание',
            'due_date' => '2026-11-01 12:00:00',
            'status' => TaskStatus::Pending->value,
            'priority' => TaskPriority::Medium->value,
            'category' => 'Работа',
        ]);
    }
}
