<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Services\ChatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AiChatController extends Controller
{
    public function __construct(private readonly ChatService $chats)
    {
    }

    /**
     * Display the full-page AI chat.
     *
     * Messages are provided via the shared `chatMessages` Inertia prop
     * (see HandleInertiaRequests) so the chat page and the floating
     * chat widget always stay in sync.
     */
    public function index(): Response
    {
        return Inertia::render('Chat/ChatPage');
    }

    /**
     * Send a message and record the AI's reply.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'agent_id' => ['nullable', 'integer', 'exists:agents,id'],
        ]);

        $this->chats->sendMessage(
            $request->user()->id,
            $validated['message'],
            $validated['agent_id'] ?? null,
        );

        return back();
    }

    /**
     * Edit a user's own message. AI messages cannot be edited.
     */
    public function update(Request $request, Chat $chat): RedirectResponse
    {
        $this->authorizeOwner($request, $chat);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $this->chats->updateMessage($chat, $validated['message']);

        return back();
    }

    /**
     * Delete a chat message (user or AI).
     */
    public function destroy(Request $request, Chat $chat): RedirectResponse
    {
        $this->authorizeOwner($request, $chat);

        $this->chats->destroy($chat);

        return back();
    }

    /**
     * Ensure the authenticated user owns the given chat message.
     */
    private function authorizeOwner(Request $request, Chat $chat): void
    {
        if ($chat->user_id !== $request->user()->id) {
            throw new AccessDeniedHttpException('You do not own this message.');
        }
    }
}
