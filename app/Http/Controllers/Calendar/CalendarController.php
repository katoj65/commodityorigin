<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Http\Resources\CalendarResource;
use App\Http\Resources\TaskResource;
use App\Models\Calendar;
use App\Services\CalendarService;
use App\Services\TaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CalendarController extends Controller
{
    public function __construct(
        private readonly CalendarService $calendar,
        private readonly TaskService $tasks,
    ) {
    }

    /**
     * Display the calendar page.
     */
    public function index(Request $request): Response
    {
        $userId = $request->user()->id;
        $events = $this->calendar->eventsForUser($userId);

        return Inertia::render('Calendar/CalendarPage', [
            'events' => CalendarResource::collection($events)->resolve(),
            'tasks' => TaskResource::collection($this->tasks->tasksForUser($userId))->resolve(),
        ]);
    }

    /**
     * Create a new calendar event.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateEvent($request);
        $makeTask = $request->boolean('make_task');

        $event = $this->calendar->create([
            'user_id' => $request->user()->id,
            ...$validated,
        ]);

        if ($makeTask) {
            $this->tasks->createFromCalendarEvent($event);
        }

        return back()->with('success', $makeTask ? 'Event saved and added to your tasks.' : 'Event saved successfully.');
    }

    /**
     * Update an existing calendar event.
     */
    public function update(Request $request, Calendar $calendar): RedirectResponse
    {
        $this->authorizeOwner($request, $calendar);

        $validated = $this->validateEvent($request);

        $this->calendar->update($calendar, $validated);
        $this->tasks->syncFromCalendarEvent($calendar);

        return back()->with('success', 'Event updated successfully.');
    }

    /**
     * Delete a calendar event.
     */
    public function destroy(Request $request, Calendar $calendar): RedirectResponse
    {
        $this->authorizeOwner($request, $calendar);

        $this->calendar->destroy($calendar);

        return back()->with('success', 'Event deleted successfully.');
    }

    /**
     * Validate the create/update payload.
     *
     * @return array<string, mixed>
     */
    private function validateEvent(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'event_date' => ['required', 'date'],
            'type' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(['pending', 'completed'])],
        ]);
    }

    /**
     * Ensure the authenticated user owns the given calendar event.
     */
    private function authorizeOwner(Request $request, Calendar $calendar): void
    {
        if ($calendar->user_id !== $request->user()->id) {
            throw new AccessDeniedHttpException('You do not own this event.');
        }
    }
}
