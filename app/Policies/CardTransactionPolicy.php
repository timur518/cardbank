<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CardTransaction;
use Illuminate\Auth\Access\HandlesAuthorization;

class CardTransactionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CardTransaction');
    }

    public function view(AuthUser $authUser, CardTransaction $cardTransaction): bool
    {
        return $authUser->can('View:CardTransaction');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CardTransaction');
    }

    public function update(AuthUser $authUser, CardTransaction $cardTransaction): bool
    {
        return $authUser->can('Update:CardTransaction');
    }

    public function delete(AuthUser $authUser, CardTransaction $cardTransaction): bool
    {
        return $authUser->can('Delete:CardTransaction');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:CardTransaction');
    }

    public function restore(AuthUser $authUser, CardTransaction $cardTransaction): bool
    {
        return $authUser->can('Restore:CardTransaction');
    }

    public function forceDelete(AuthUser $authUser, CardTransaction $cardTransaction): bool
    {
        return $authUser->can('ForceDelete:CardTransaction');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CardTransaction');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CardTransaction');
    }

    public function replicate(AuthUser $authUser, CardTransaction $cardTransaction): bool
    {
        return $authUser->can('Replicate:CardTransaction');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CardTransaction');
    }

}