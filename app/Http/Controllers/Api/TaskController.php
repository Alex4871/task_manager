<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\IndexTaskRequest;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    public function index(IndexTaskRequest $request): AnonymousResourceCollection
    {
        $tasks = $this->taskService
            ->getAll($request->validated());

        return TaskResource::collection($tasks)
            ->preserveQuery();
    }

    public function show(int $id): TaskResource
    {
        $task = $this->taskService->findById($id);

        return new TaskResource($task);
    }
}
