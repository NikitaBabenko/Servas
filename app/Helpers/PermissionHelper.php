<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class PermissionHelper
{
    /**
     * Check if the user is the owner of the modal instance.
     */
    public static function userIsOwnerOfModal(object|null $modal, int $userId = null, string $userTable = 'user_id'): bool
    {
        if ($userId === null) {
            $userId = Auth::id();
        }

        return $userId !== null && !empty($modal) && $modal->$userTable === $userId;
    }

    /** Reject the entire write before mutation if any related object is foreign or absent. */
    public static function requireOwnedModalIds(array $ids, string $model): void
    {
        foreach ($ids as $id) {
            abort_unless(is_int($id) || (is_string($id) && ctype_digit($id)), 403);
            abort_unless(self::userIsOwnerOfModal($model::find($id)), 403);
        }
    }

    /**
     * Check if the user is the owner of the modal instance with the ID specified in the array.
     * Return an array with the IDs that passed the check.
     */
    public static function filterArrayOfModalIds(array $modalIds, string $modal): array
    {
        if (empty($modalIds)) {
            return [];
        }

        $filteredModalIds = [];
        $modalInstance = app($modal);

        foreach ($modalIds as $modalId) {
            $modal = $modalInstance->find($modalId);

            if (self::userIsOwnerOfModal($modal)) {
                $filteredModalIds[] = $modalId;
            }
        }

        return $filteredModalIds;
    }
}
