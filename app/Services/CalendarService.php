<?php

namespace App\Services;

use App\Models\Calendar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CalendarService
{
    /**
     * Get a base query builder for calendar events.
     */
    public function query(): Builder
    {
        return Calendar::query();
    }

    /**
     * Get a user's events, optionally scoped to a given month.
     *
     * @return Collection<int, Calendar>
     */
    public function eventsForUser(int $userId, ?int $month = null, ?int $year = null): Collection
    {
        return Calendar::query()
            ->where('user_id', $userId)
            ->when($month && $year, fn (Builder $query) => $query->whereYear('event_date', $year)->whereMonth('event_date', $month))
            ->orderBy('event_date')
            ->get();
    }

    /**
     * Get a user's pending events that are due today.
     *
     * @return Collection<int, Calendar>
     */
    public function dueTodayForUser(int $userId): Collection
    {
        return Calendar::query()
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->whereDate('event_date', now()->toDateString())
            ->orderBy('title')
            ->get();
    }

    /**
     * Create a new calendar event.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Calendar
    {
        return Calendar::query()->create($data)->refresh();
    }

    /**
     * Find a single calendar event by id.
     */
    public function show(int $id): Calendar
    {
        return Calendar::query()->findOrFail($id);
    }

    /**
     * Update an existing calendar event.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Calendar $calendar, array $data): Calendar
    {
        $calendar->update($data);

        return $calendar;
    }

    /**
     * Delete a calendar event.
     */
    public function destroy(Calendar $calendar): void
    {
        $calendar->delete();
    }
}
