<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdPlacement;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AdPlacementPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AdPlacement');
    }

    public function view(AuthUser $authUser, AdPlacement $adPlacement): bool
    {
        return $authUser->can('View:AdPlacement');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AdPlacement');
    }

    public function update(AuthUser $authUser, AdPlacement $adPlacement): bool
    {
        return $authUser->can('Update:AdPlacement');
    }

    public function delete(AuthUser $authUser, AdPlacement $adPlacement): bool
    {
        return $authUser->can('Delete:AdPlacement');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AdPlacement');
    }

    public function restore(AuthUser $authUser, AdPlacement $adPlacement): bool
    {
        return $authUser->can('Restore:AdPlacement');
    }

    public function forceDelete(AuthUser $authUser, AdPlacement $adPlacement): bool
    {
        return $authUser->can('ForceDelete:AdPlacement');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AdPlacement');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AdPlacement');
    }

    public function replicate(AuthUser $authUser, AdPlacement $adPlacement): bool
    {
        return $authUser->can('Replicate:AdPlacement');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AdPlacement');
    }
}
