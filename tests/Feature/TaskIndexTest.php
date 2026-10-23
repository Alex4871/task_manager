<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_an_empty_task_list(): void
    {
        $this->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);
    }

    public function test_it_returns_created_tasks(): void
    {
        $first = $this->createTask('Первая задача', '2026-11-01 12:00:00');
        $second = $this->createTask('Вторая задача', '2026-11-02 12:00:00');

        $this->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $first->id)
            ->assertJsonPath('data.0.title', 'Первая задача')
            ->assertJsonPath('data.1.id', $second->id)
            ->assertJsonPath('data.1.title', 'Вторая задача')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_it_searches_tasks_by_title(): void
    {
        $this->createTask('Купить продукты');
        $this->createTask('Купить билеты');
        $this->createTask('Позвонить маме');

        $this->getJson('/api/tasks?search=Купить')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonMissing([
                'title' => 'Позвонить маме',
            ]);
    }

    public function test_search_is_case_insensitive(): void
    {
        $this->createTask('Laravel API');
        $this->createTask('Docker Compose');

        $this->getJson('/api/tasks?search=laravel')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Laravel API');
    }

    public function test_it_sorts_tasks_by_due_date_descending(): void
    {
        $this->createTask('Ранняя задача', '2026-11-01 12:00:00');
        $this->createTask('Поздняя задача', '2026-11-10 12:00:00');

        $this->getJson('/api/tasks?sort=due_date&order=desc')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Поздняя задача')
            ->assertJsonPath('data.1.title', 'Ранняя задача');
    }

    public function test_it_sorts_tasks_by_due_date_ascending_by_default(): void
    {
        $this->createTask('Поздняя задача', '2026-11-10 12:00:00');
        $this->createTask('Ранняя задача', '2026-11-01 12:00:00');

        $this->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Ранняя задача')
            ->assertJsonPath('data.1.title', 'Поздняя задача');
    }

    public function test_it_paginates_tasks(): void
    {
        $this->createTask('Первая задача', '2026-11-01 12:00:00');
        $this->createTask('Вторая задача', '2026-11-02 12:00:00');
        $this->createTask('Третья задача', '2026-11-03 12:00:00');

        $this->getJson('/api/tasks?per_page=2&page=2')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Третья задача')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.last_page', 2);
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
