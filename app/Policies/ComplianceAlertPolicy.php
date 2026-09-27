<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ComplianceAlert;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ComplianceAlertPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ComplianceAlert');
    }

    public function view(AuthUser $authUser, ComplianceAlert $complianceAlert): bool
    {
        return $authUser->can('View:ComplianceAlert');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ComplianceAlert');
    }

    public function update(AuthUser $authUser, ComplianceAlert $complianceAlert): bool
    {
        return $authUser->can('Update:ComplianceAlert');
    }

    public function delete(AuthUser $authUser, ComplianceAlert $complianceAlert): bool
    {
        return $authUser->can('Delete:ComplianceAlert');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ComplianceAlert');
    }

    public function restore(AuthUser $authUser, ComplianceAlert $complianceAlert): bool
    {
        return $authUser->can('Restore:ComplianceAlert');
    }

    public function forceDelete(AuthUser $authUser, ComplianceAlert $complianceAlert): bool
    {
        return $authUser->can('ForceDelete:ComplianceAlert');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ComplianceAlert');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ComplianceAlert');
    }

    public function replicate(AuthUser $authUser, ComplianceAlert $complianceAlert): bool
    {
        return $authUser->can('Replicate:ComplianceAlert');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ComplianceAlert');
    }
}
