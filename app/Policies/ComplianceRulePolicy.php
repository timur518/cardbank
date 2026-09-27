<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ComplianceRule;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ComplianceRulePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ComplianceRule');
    }

    public function view(AuthUser $authUser, ComplianceRule $complianceRule): bool
    {
        return $authUser->can('View:ComplianceRule');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ComplianceRule');
    }

    public function update(AuthUser $authUser, ComplianceRule $complianceRule): bool
    {
        return $authUser->can('Update:ComplianceRule');
    }

    public function delete(AuthUser $authUser, ComplianceRule $complianceRule): bool
    {
        return $authUser->can('Delete:ComplianceRule');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ComplianceRule');
    }

    public function restore(AuthUser $authUser, ComplianceRule $complianceRule): bool
    {
        return $authUser->can('Restore:ComplianceRule');
    }

    public function forceDelete(AuthUser $authUser, ComplianceRule $complianceRule): bool
    {
        return $authUser->can('ForceDelete:ComplianceRule');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ComplianceRule');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ComplianceRule');
    }

    public function replicate(AuthUser $authUser, ComplianceRule $complianceRule): bool
    {
        return $authUser->can('Replicate:ComplianceRule');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ComplianceRule');
    }
}
