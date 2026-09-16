<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CardProduct;
use Illuminate\Auth\Access\HandlesAuthorization;

class CardProductPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CardProduct');
    }

    public function view(AuthUser $authUser, CardProduct $cardProduct): bool
    {
        return $authUser->can('View:CardProduct');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CardProduct');
    }

    public function update(AuthUser $authUser, CardProduct $cardProduct): bool
    {
        return $authUser->can('Update:CardProduct');
    }

    public function delete(AuthUser $authUser, CardProduct $cardProduct): bool
    {
        return $authUser->can('Delete:CardProduct');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:CardProduct');
    }

    public function restore(AuthUser $authUser, CardProduct $cardProduct): bool
    {
        return $authUser->can('Restore:CardProduct');
    }

    public function forceDelete(AuthUser $authUser, CardProduct $cardProduct): bool
    {
        return $authUser->can('ForceDelete:CardProduct');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CardProduct');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CardProduct');
    }

    public function replicate(AuthUser $authUser, CardProduct $cardProduct): bool
    {
        return $authUser->can('Replicate:CardProduct');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CardProduct');
    }

}