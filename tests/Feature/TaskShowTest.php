<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_an_existing_task(): void
    {
        $task = $this->createTask(
            'Тестовая задача',
            '2026-11-01 12:00:00'
        );

        $this->getJson("/api/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $task->id)
            ->assertJsonPath('data.title', 'Тестовая задача')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'due_date',
                    'create_date',
                    'updated_at',
                    'status',
                    'priority',
                    'category',
                ],
            ]);
    }

    public function test_it_returns_404_for_a_non_existing_task(): void
    {
        $this->getJson('/api/tasks/999999')
            ->assertNotFound();
    }

    public function test_it_returns_only_the_requested_task(): void
    {
        $first = $this->createTask('Первая задача');
        $second = $this->createTask('Вторая задача');

        $this->getJson("/api/tasks/{$second->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $second->id)
            ->assertJsonPath('data.title', 'Вторая задача')
            ->assertJsonMissing([
                'id' => $first->id,
                'title' => 'Первая задача',
            ]);
    }

    private function createTask(
        string $title,
        string $dueDate = '2026-11-01 12:00:00'
    ): Task {
        return Task::create([
            'title' => $title,
            'due_date' => $dueDate,
        ]);
    }
}
