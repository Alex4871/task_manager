<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_an_existing_task(): void
    {
        $task = $this->createTask();

        $this->deleteJson("/api/tasks/{$task->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_it_returns_404_for_a_non_existing_task(): void
    {
        $this->deleteJson('/api/tasks/999999')
            ->assertNotFound();
    }

    public function test_it_deletes_only_the_requested_task(): void
    {
        $taskToDelete = $this->createTask('Удаляемая задача');
        $taskToKeep = $this->createTask('Оставшаяся задача');

        $this->deleteJson("/api/tasks/{$taskToDelete->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('tasks', [
            'id' => $taskToDelete->id,
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $taskToKeep->id,
            'title' => 'Оставшаяся задача',
        ]);

        $this->assertDatabaseCount('tasks', 1);
    }

    public function test_it_returns_404_when_deleting_the_same_task_twice(): void
    {
        $task = $this->createTask();

        $this->deleteJson("/api/tasks/{$task->id}")
            ->assertNoContent();

        $this->deleteJson("/api/tasks/{$task->id}")
            ->assertNotFound();

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_deleted_task_is_no_longer_accessible(): void
    {
        $task = $this->createTask();

        $this->deleteJson("/api/tasks/{$task->id}")
            ->assertNoContent();

        $this->getJson("/api/tasks/{$task->id}")
            ->assertNotFound();
    }

    private function createTask(string $title = 'Тестовая задача'): Task
    {
        return Task::create([
            'title' => $title,
            'due_date' => '2026-11-01 12:00:00',
        ]);
    }
}
