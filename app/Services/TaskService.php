<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskService
{
    public function getAll(array $filters): LengthAwarePaginator
    {
        $query = Task::query();

        if (! empty($filters['search'])) {
            $query->where(
                'title',
                'ILIKE',
                '%'.$filters['search'].'%'
            );
        }

        $sort = $filters['sort'] ?? 'due_date';
        $order = $filters['order'] ?? 'asc';
        $perPage = $filters['per_page'] ?? 10;
        $page = $filters['page'] ?? 1;

        return $query
            ->orderBy($sort, $order)
            ->orderBy('id')
            ->paginate(
                perPage: $perPage,
                page: $page
            );
    }

    public function findById(int $id): Task
    {
        return Task::query()->findOrFail($id);
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->refresh();
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
