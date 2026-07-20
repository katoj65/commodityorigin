<?php

namespace App\Services;

use App\Models\Calendar;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TaskService
{
    /**
     * Get a base query builder for tasks.
     */
    public function query(): Builder
    {
        return Task::query();
    }

    /**
     * Get a user's tasks, soonest due date first.
     *
     * @return Collection<int, Task>
     */
    public function tasksForUser(int $userId): Collection
    {
        return Task::query()
            ->where('user_id', $userId)
            ->orderBy('task_date')
            ->get();
    }

    /**
     * Get a user's pending tasks that are due today — the ones requiring a
     * decision right now.
     *
     * @return Collection<int, Task>
     */
    public function dueTodayForUser(int $userId): Collection
    {
        return Task::query()
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->whereDate('task_date', now()->toDateString())
            ->orderBy('title')
            ->get();
    }

    /**
     * Create a new task.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Task
    {
        return Task::query()->create($data)->refresh();
    }

    /**
     * Create a task from a calendar event, linking it back to that event.
     */
    public function createFromCalendarEvent(Calendar $event): Task
    {
        return $this->create([
            'user_id' => $event->user_id,
            'calendar_id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'task_date' => $event->event_date,
            'status' => $event->status,
        ]);
    }

    /**
     * Keep a linked task in sync with its originating calendar event
     * (title, date, and status) after that event is updated.
     */
    public function syncFromCalendarEvent(Calendar $event): void
    {
        $task = Task::query()->where('calendar_id', $event->id)->first();

        if (! $task) {
            return;
        }

        $task->update([
            'title' => $event->title,
            'description' => $event->description,
            'task_date' => $event->event_date,
            'status' => $event->status,
        ]);
    }

    /**
     * Update an existing task.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task;
    }

    /**
     * Delete a task.
     */
    public function destroy(Task $task): void
    {
        $task->delete();
    }
}
