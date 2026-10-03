<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Partner;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PartnerPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Partner');
    }

    public function view(AuthUser $authUser, Partner $partner): bool
    {
        return $authUser->can('View:Partner');
    }

    /**
     * Партнёры больше не заводятся/редактируются/удаляются вручную — это вычисляемая
     * проекция пользователей (см. App\Models\Partner), управлять ими нужно через UserResource.
     */
    public function create(AuthUser $authUser): bool
    {
        return false;
    }

    public function update(AuthUser $authUser, Partner $partner): bool
    {
        return false;
    }

    public function delete(AuthUser $authUser, Partner $partner): bool
    {
        return false;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return false;
    }

    public function restore(AuthUser $authUser, Partner $partner): bool
    {
        return $authUser->can('Restore:Partner');
    }

    public function forceDelete(AuthUser $authUser, Partner $partner): bool
    {
        return $authUser->can('ForceDelete:Partner');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Partner');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Partner');
    }

    public function replicate(AuthUser $authUser, Partner $partner): bool
    {
        return $authUser->can('Replicate:Partner');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Partner');
    }
}
