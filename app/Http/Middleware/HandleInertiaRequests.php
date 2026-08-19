<?php

namespace App\Http\Middleware;

use App\Http\Resources\CalendarResource;
use App\Http\Resources\ChatResource;
use App\Http\Resources\CurrencyResource;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\TaskResource;
use App\Models\RoleMetadata;
use App\Services\AgentService;
use App\Services\CalendarService;
use App\Services\CartService;
use App\Services\ChatService;
use App\Services\CurrencyService;
use App\Services\NotificationService;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function __construct(
        private readonly ChatService $chats,
        private readonly CalendarService $calendar,
        private readonly TaskService $tasks,
        private readonly NotificationService $notifications,
        private readonly AgentService $agents,
        private readonly CartService $cart,
        private readonly CurrencyService $currencies,
    ) {
    }

    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $authenticatedUser = $request->user()?->loadMissing('profile');

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $authenticatedUser ? [
                    'id' => $authenticatedUser->id,
                    'first_name' => Str::ucfirst($authenticatedUser->first_name),
                    'last_name' => Str::ucfirst($authenticatedUser->last_name),
                    'name' => Str::ucfirst($authenticatedUser->name),
                    'email' => $authenticatedUser->email,
                    'role' => $authenticatedUser->role,
                    'telephone' => $authenticatedUser->telephone,
                    'profile_photo_path' => $authenticatedUser->profile_photo_path,
                    'profile_photo_url' => $authenticatedUser->profile_photo_url,
                    'email_verified_at' => $authenticatedUser->email_verified_at,
                    'two_factor_enabled' => ! is_null($authenticatedUser->two_factor_secret),
                    'profile' => $authenticatedUser->profile,
                    'currency_code' => $authenticatedUser->currency_code,
                    'created_at' => $authenticatedUser->created_at,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'roles' => fn () => RoleMetadata::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['slug', 'name', 'description']),
            'currencies' => fn () => CurrencyResource::collection($this->currencies->active())->resolve(),
            'chatMessages' => fn () => $authenticatedUser
                ? ChatResource::collection($this->chats->messagesForUser($authenticatedUser->id))->resolve()
                : [],
            'dueCalendarEvents' => fn () => $authenticatedUser
                ? CalendarResource::collection($this->calendar->dueTodayForUser($authenticatedUser->id))->resolve()
                : [],
            'dueTasksToday' => fn () => $authenticatedUser
                ? TaskResource::collection($this->tasks->dueTodayForUser($authenticatedUser->id))->resolve()
                : [],
            'unreadNotificationsCount' => fn () => $authenticatedUser
                ? $this->notifications->unreadCountForUser($authenticatedUser->id)
                : 0,
            'recentNotifications' => fn () => $authenticatedUser
                ? NotificationResource::collection($this->notifications->recentForUser($authenticatedUser->id))->resolve()
                : [],
            'cartActiveCount' => fn () => $authenticatedUser
                ? $this->cart->activeCountForUser($authenticatedUser->id)
                : 0,
            'subscribedAgents' => fn () => ($authenticatedUser && ! $authenticatedUser->hasRole('admin'))
                ? $this->agents->subscribedAgentsForUser($authenticatedUser->id)
                : [],
        ];
    }
}
