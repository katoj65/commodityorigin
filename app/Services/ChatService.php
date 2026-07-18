<?php

namespace App\Services;

use App\Models\Chat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class ChatService
{
    /**
     * Get a base query builder for chat messages.
     */
    public function query(): Builder
    {
        return Chat::query();
    }

    /**
     * Get every message for a user, oldest first.
     *
     * @return Collection<int, Chat>
     */
    public function messagesForUser(int $userId): Collection
    {
        return Chat::query()
            ->where('user_id', $userId)
            ->oldest('created_at')
            ->get();
    }

    /**
     * Store a single chat message.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Chat
    {
        return Chat::query()->create($data)->refresh();
    }

    /**
     * Send a user message and record the AI's reply.
     *
     * @return array{0: Chat, 1: Chat} the user message and the assistant reply
     */
    public function sendMessage(int $userId, string $message, ?int $agentId = null): array
    {
        $userMessage = $this->create([
            'user_id' => $userId,
            'agent_id' => $agentId,
            'role' => 'user',
            'message' => $message,
        ]);

        $reply = $this->create([
            'user_id' => $userId,
            'agent_id' => $agentId,
            'role' => 'assistant',
            'message' => $this->generateReply($message),
        ]);

        return [$userMessage, $reply];
    }

    /**
     * Update the text of a user's own message.
     *
     * AI (assistant) messages cannot be edited.
     */
    public function updateMessage(Chat $chat, string $message): Chat
    {
        if ($chat->role !== 'user') {
            throw ValidationException::withMessages([
                'message' => 'Only your own messages can be edited.',
            ]);
        }

        $chat->update(['message' => $message]);

        return $chat;
    }

    /**
     * Delete a chat message.
     */
    public function destroy(Chat $chat): void
    {
        $chat->delete();
    }

    /**
     * Clear every message for a user.
     */
    public function clearForUser(int $userId): void
    {
        Chat::query()->where('user_id', $userId)->delete();
    }

    /**
     * Generate a placeholder AI reply until agent-specific responses are wired up.
     */
    private function generateReply(string $message): string
    {
        return "Got it — I'll route this to your subscribed agents once agent-specific responses are wired up: \"{$message}\"";
    }
}
