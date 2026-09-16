<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CardProvider;
use Illuminate\Auth\Access\HandlesAuthorization;

class CardProviderPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CardProvider');
    }

    public function view(AuthUser $authUser, CardProvider $cardProvider): bool
    {
        return $authUser->can('View:CardProvider');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CardProvider');
    }

    public function update(AuthUser $authUser, CardProvider $cardProvider): bool
    {
        return $authUser->can('Update:CardProvider');
    }

    public function delete(AuthUser $authUser, CardProvider $cardProvider): bool
    {
        return $authUser->can('Delete:CardProvider');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:CardProvider');
    }

    public function restore(AuthUser $authUser, CardProvider $cardProvider): bool
    {
        return $authUser->can('Restore:CardProvider');
    }

    public function forceDelete(AuthUser $authUser, CardProvider $cardProvider): bool
    {
        return $authUser->can('ForceDelete:CardProvider');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CardProvider');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CardProvider');
    }

    public function replicate(AuthUser $authUser, CardProvider $cardProvider): bool
    {
        return $authUser->can('Replicate:CardProvider');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CardProvider');
    }

}