<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\IndexTaskRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

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

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->create($request->validated());

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateTaskRequest $request, int $id): TaskResource
    {
        $task = $this->taskService->findById($id);

        $updatedTask = $this->taskService->update(
            $task,
            $request->validated()
        );

        return new TaskResource($updatedTask);
    }

    public function destroy(int $id): Response
    {
        $task = $this->taskService->findById($id);

        $this->taskService->delete($task);

        return response()->noContent();
    }
}
