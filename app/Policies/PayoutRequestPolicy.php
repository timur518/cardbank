<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PayoutRequest;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PayoutRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PayoutRequest');
    }

    public function view(AuthUser $authUser, PayoutRequest $payoutRequest): bool
    {
        return $authUser->can('View:PayoutRequest');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PayoutRequest');
    }

    public function update(AuthUser $authUser, PayoutRequest $payoutRequest): bool
    {
        return $authUser->can('Update:PayoutRequest');
    }

    public function delete(AuthUser $authUser, PayoutRequest $payoutRequest): bool
    {
        return $authUser->can('Delete:PayoutRequest');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PayoutRequest');
    }

    public function restore(AuthUser $authUser, PayoutRequest $payoutRequest): bool
    {
        return $authUser->can('Restore:PayoutRequest');
    }

    public function forceDelete(AuthUser $authUser, PayoutRequest $payoutRequest): bool
    {
        return $authUser->can('ForceDelete:PayoutRequest');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PayoutRequest');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PayoutRequest');
    }

    public function replicate(AuthUser $authUser, PayoutRequest $payoutRequest): bool
    {
        return $authUser->can('Replicate:PayoutRequest');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PayoutRequest');
    }
}
