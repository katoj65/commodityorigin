<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Http\Resources\CalendarResource;
use App\Models\Calendar;
use App\Services\CalendarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CalendarController extends Controller
{
    public function __construct(private readonly CalendarService $calendar)
    {
    }

    /**
     * Display the calendar page.
     */
    public function index(Request $request): Response
    {
        $events = $this->calendar->eventsForUser($request->user()->id);

        return Inertia::render('Calendar/CalendarPage', [
            'events' => CalendarResource::collection($events)->resolve(),
        ]);
    }

    /**
     * Create a new calendar event.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateEvent($request);

        $this->calendar->create([
            'user_id' => $request->user()->id,
            ...$validated,
        ]);

        return back()->with('success', 'Event saved successfully.');
    }

    /**
     * Update an existing calendar event.
     */
    public function update(Request $request, Calendar $calendar): RedirectResponse
    {
        $this->authorizeOwner($request, $calendar);

        $validated = $this->validateEvent($request);

        $this->calendar->update($calendar, $validated);

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
