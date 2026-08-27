<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ContactService
{
    /**
     * Get a base query builder for contacts.
     */
    public function query(): Builder
    {
        return Contact::query();
    }

    /**
     * Get a user's contacts, favorites first then alphabetical.
     *
     * @return Collection<int, Contact>
     */
    public function contactsForUser(int $userId): Collection
    {
        return Contact::query()
            ->where('user_id', $userId)
            ->orderByDesc('is_favorite')
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new contact.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Contact
    {
        return Contact::query()->create($data)->refresh();
    }

    /**
     * Update an existing contact.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Contact $contact, array $data): Contact
    {
        $contact->update($data);

        return $contact;
    }

    /**
     * Delete a contact.
     */
    public function destroy(Contact $contact): void
    {
        $contact->delete();
    }
}
