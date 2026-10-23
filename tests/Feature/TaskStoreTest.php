<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TaskStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_task_with_all_fields(): void
    {
        $data = [
            'title' => 'Подготовить отчёт',
            'description' => 'Отчёт за октябрь',
            'due_date' => '2026-11-01 12:00:00',
            'create_date' => '2026-10-20 10:00:00',
            'status' => TaskStatus::Completed->value,
            'priority' => TaskPriority::High->value,
            'category' => 'Работа',
        ];

        $this->postJson('/api/tasks', $data)
            ->assertCreated()
            ->assertJsonPath('data.title', $data['title'])
            ->assertJsonPath('data.description', $data['description'])
            ->assertJsonPath('data.status', $data['status'])
            ->assertJsonPath('data.priority', $data['priority'])
            ->assertJsonPath('data.category', $data['category']);

        $this->assertDatabaseHas('tasks', $data);
    }

    public function test_it_creates_a_task_with_only_required_fields(): void
    {
        $data = [
            'title' => 'Минимальная задача',
            'due_date' => '2026-11-01 12:00:00',
        ];

        $this->postJson('/api/tasks', $data)
            ->assertCreated()
            ->assertJsonPath('data.title', $data['title'])
            ->assertJsonPath('data.status', TaskStatus::Pending->value)
            ->assertJsonPath('data.priority', TaskPriority::Medium->value)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'create_date',
                ],
            ]);

        $this->assertDatabaseHas('tasks', $data);
    }

    public function test_it_requires_a_title(): void
    {
        $this->postJson('/api/tasks', [
            'due_date' => '2026-11-01 12:00:00',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_it_rejects_a_title_longer_than_255_characters(): void
    {
        $this->postJson('/api/tasks', [
            'title' => str_repeat('A', 256),
            'due_date' => '2026-11-01 12:00:00',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_it_requires_a_due_date(): void
    {
        $this->postJson('/api/tasks', [
            'title' => 'Задача без срока',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['due_date']);

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_it_rejects_an_invalid_due_date(): void
    {
        $this->postJson('/api/tasks', [
            'title' => 'Задача',
            'due_date' => 'not-a-date',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['due_date']);

        $this->assertDatabaseCount('tasks', 0);
    }

    #[DataProvider('invalidFieldsProvider')]
    public function test_it_rejects_invalid_field_values(
        string $field,
        mixed $value
    ): void {
        $data = [
            'title' => 'Тестовая задача',
            'due_date' => '2026-11-01 12:00:00',
            $field => $value,
        ];

        $this->postJson('/api/tasks', $data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$field]);

        $this->assertDatabaseCount('tasks', 0);
    }

    public static function invalidFieldsProvider(): array
    {
        return [
            'invalid status' => ['status', 'Неизвестный'],
            'invalid priority' => ['priority', 'Критический'],
            'lowercase priority' => ['priority', 'высокий'],
            'invalid category' => ['category', str_repeat('A', 101)],
            'invalid description' => ['description', ['array']],
            'invalid create date' => ['create_date', 'invalid-date'],
        ];
    }

    public function test_it_accepts_nullable_optional_fields(): void
    {
        $data = [
            'title' => 'Задача с пустыми полями',
            'due_date' => '2026-11-01 12:00:00',
            'description' => null,
            'category' => null,
        ];

        $this->postJson('/api/tasks', $data)
            ->assertCreated()
            ->assertJsonPath('data.description', null)
            ->assertJsonPath('data.category', null);

        $this->assertDatabaseHas('tasks', $data);
    }
}
