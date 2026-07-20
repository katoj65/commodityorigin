<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\CalendarService;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $tasks,
        private readonly CalendarService $calendar,
    ) {
    }

    /**
     * Display the task page — every task for the authenticated user.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Task/TaskPage', [
            'tasks' => TaskResource::collection($this->tasks->tasksForUser($request->user()->id))->resolve(),
        ]);
    }

    /**
     * Create a new task.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTask($request);
        $addToCalendar = $request->boolean('add_to_calendar');

        if ($addToCalendar) {
            $event = $this->calendar->create([
                'user_id' => $request->user()->id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'event_date' => $validated['task_date'],
                'type' => 'task',
                'status' => $validated['status'] ?? 'pending',
            ]);
            $validated['calendar_id'] = $event->id;
        }

        $this->tasks->create([
            'user_id' => $request->user()->id,
            ...$validated,
        ]);

        return back()->with('success', $addToCalendar ? 'Task created and added to your calendar.' : 'Task created successfully.');
    }

    /**
     * Display a single task.
     */
    public function show(Request $request, Task $task): JsonResponse
    {
        $this->authorizeOwner($request, $task);

        return response()->json([
            'task' => TaskResource::make($task)->resolve(),
        ]);
    }

    /**
     * Update an existing task.
     */
    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeOwner($request, $task);

        $validated = $this->validateTask($request);

        $this->tasks->update($task, $validated);

        return back()->with('success', 'Task updated successfully.');
    }

    /**
     * Delete a task.
     */
    public function destroy(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeOwner($request, $task);

        $this->tasks->destroy($task);

        return back()->with('success', 'Task deleted successfully.');
    }

    /**
     * Validate the create/update payload.
     *
     * @return array<string, mixed>
     */
    private function validateTask(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'task_date' => ['required', 'date'],
            'status' => ['nullable', 'string', Rule::in(['pending', 'completed'])],
        ]);
    }

    /**
     * Ensure the authenticated user owns the given task.
     */
    private function authorizeOwner(Request $request, Task $task): void
    {
        if ($task->user_id !== $request->user()->id) {
            throw new AccessDeniedHttpException('You do not own this task.');
        }
    }
}
