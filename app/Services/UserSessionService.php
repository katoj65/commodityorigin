<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Jetstream\Agent;

/**
 * Resolves the authenticated user's active database sessions — shared
 * between ProfileController (Personal/Business Profile) and
 * SettingsController (Security tab), which both need the same "device,
 * location, last active" rows.
 */
class UserSessionService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function forUser(Request $request): array
    {
        if (config('session.driver') !== 'database') {
            return [];
        }

        return collect(
            DB::connection(config('session.connection'))
                ->table(config('session.table', 'sessions'))
                ->where('user_id', $request->user()->getAuthIdentifier())
                ->orderBy('last_activity', 'desc')
                ->get(),
        )->map(function ($session) use ($request) {
            $agent = tap(new Agent(), fn (Agent $agent) => $agent->setUserAgent($session->user_agent));

            return [
                'agent' => [
                    'is_desktop' => $agent->isDesktop(),
                    'platform' => $agent->platform(),
                    'browser' => $agent->browser(),
                ],
                'ip_address' => $session->ip_address,
                'is_current_device' => $session->id === $request->session()->getId(),
                'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
            ];
        })->all();
    }
}
