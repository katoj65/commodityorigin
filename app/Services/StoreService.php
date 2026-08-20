<?php

namespace App\Services;

use App\Models\Store;
use App\Models\StoreItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StoreService
{
    /**
     * The validation rules for opening a store — just a re-confirmation of
     * the user's own email and password, checked against their account in
     * requestStore().
     *
     * @return array<string, array<int, string>>
     */
    public function confirmationRules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * The validation rules for an item's own details. Status changes are
     * handled separately via changeStatus() so every one is logged.
     *
     * @return array<string, array<int, string>>
     */
    public function itemRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency_code' => ['nullable', 'string', 'max:10'],
            'quantity' => ['required', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get a user's store, if they have one.
     */
    public function forUser(int $userId): ?Store
    {
        return Store::where('user_id', $userId)->first();
    }

    /**
     * Request a store for a user, confirmed by re-entering their own
     * account email and password. Starts pending — an admin must verify
     * it before the owner can open it (add items). Resubmitting after a
     * rejection moves the same store back to pending.
     *
     * @throws ValidationException if the email/password don't match the user's own account
     */
    public function requestStore(User $user, string $email, string $password): Store
    {
        if (strcasecmp($email, $user->email) !== 0 || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Those credentials do not match your account.',
            ]);
        }

        $existing = $this->forUser($user->id);

        if ($existing) {
            $existing->update([
                'verification_status' => 'pending',
                'verified_by' => null,
                'verified_at' => null,
                'rejection_reason' => null,
            ]);

            return $existing;
        }

        return Store::query()->create([
            'user_id' => $user->id,
            'verification_status' => 'pending',
        ]);
    }

    /**
     * Every store awaiting admin review, oldest first.
     *
     * @return Collection<int, Store>
     */
    public function pending(): Collection
    {
        return Store::query()
            ->where('verification_status', 'pending')
            ->with('user:id,first_name,last_name')
            ->oldest()
            ->get();
    }

    /**
     * Verify a store, allowing its owner to open it.
     */
    public function verify(Store $store, User $admin): Store
    {
        $store->update([
            'verification_status' => 'verified',
            'verified_by' => $admin->id,
            'verified_at' => now(),
            'rejection_reason' => null,
        ]);

        return $store;
    }

    /**
     * Reject a store, optionally explaining why.
     */
    public function reject(Store $store, User $admin, ?string $reason): Store
    {
        $store->update([
            'verification_status' => 'rejected',
            'verified_by' => $admin->id,
            'verified_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $store;
    }

    /**
     * Every item in a store, newest first, with its full status history.
     *
     * @return Collection<int, StoreItem>
     */
    public function items(int $storeId): Collection
    {
        return StoreItem::query()
            ->where('store_id', $storeId)
            ->with('statusLogs.changedBy:id,first_name,last_name')
            ->latest()
            ->get();
    }

    /**
     * Add a new item to a store. The item's starting status is recorded as
     * its first traceability log entry.
     *
     * @param  array<string, mixed>  $data
     */
    public function addItem(Store $store, array $data, User $changedBy): StoreItem
    {
        $item = StoreItem::query()->create([
            ...$data,
            'store_id' => $store->id,
            'sku' => $this->generateSku($store->id, $data['category'] ?? null),
            'status' => StoreItem::STATUSES[0],
        ]);

        $item->statusLogs()->create([
            'from_status' => null,
            'to_status' => $item->status,
            'changed_by' => $changedBy->id,
            'notes' => 'Item added to store.',
        ]);

        return $item;
    }

    /**
     * Import items from parsed spreadsheet rows. Each row is validated
     * independently against the same rules as the item form — rows that
     * fail are skipped and reported back rather than aborting the whole
     * import.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{imported: int, errors: array<int, array{row: int, errors: array<int, string>}>}
     */
    public function importItems(Store $store, array $rows, User $changedBy): array
    {
        $imported = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $validator = Validator::make($row, $this->itemRules());

            if ($validator->fails()) {
                $errors[] = [
                    // +1 to move from a 0-index to a 1-index, +1 more for the header row.
                    'row' => $index + 2,
                    'errors' => $validator->errors()->all(),
                ];

                continue;
            }

            $this->addItem($store, $validator->validated(), $changedBy);
            $imported++;
        }

        return ['imported' => $imported, 'errors' => $errors];
    }

    /**
     * Update an item's own details — never its status, which is changed
     * (and logged) exclusively through changeStatus().
     *
     * @param  array<string, mixed>  $data
     */
    public function updateItem(StoreItem $item, array $data): StoreItem
    {
        $item->update($data);

        return $item;
    }

    /**
     * Move an item to a new status, recording the transition so it stays
     * fully traceable.
     */
    public function changeStatus(StoreItem $item, string $status, ?string $notes, User $changedBy): StoreItem
    {
        if (! in_array($status, StoreItem::STATUSES, true)) {
            throw ValidationException::withMessages(['status' => 'That is not a valid status.']);
        }

        $previousStatus = $item->status;

        if ($previousStatus === $status) {
            return $item;
        }

        $item->update(['status' => $status]);

        $item->statusLogs()->create([
            'from_status' => $previousStatus,
            'to_status' => $status,
            'changed_by' => $changedBy->id,
            'notes' => $notes,
        ]);

        return $item;
    }

    /**
     * Remove an item from a store.
     */
    public function destroyItem(StoreItem $item): void
    {
        $item->delete();
    }

    /**
     * Generate a unique, human-readable SKU for a new item — unique per
     * store, matching the store_items table's [store_id, sku] constraint.
     */
    protected function generateSku(int $storeId, ?string $category): string
    {
        $prefix = $category !== null && $category !== '' ? strtoupper(substr($category, 0, 3)) : 'ITM';

        do {
            $sku = "{$prefix}-".strtoupper(Str::random(6));
        } while (StoreItem::query()->where('store_id', $storeId)->where('sku', $sku)->exists());

        return $sku;
    }
}
