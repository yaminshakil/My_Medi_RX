<?php

namespace App\Policies;

use App\Models\PediatricDoseRule;
use App\Models\User;

class PediatricDoseRulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('pediatric-dose-rules.view')
            || $user->can('pediatric-dose-rules.create');
    }

    public function view(User $user, PediatricDoseRule $rule): bool
    {
        return $user->can('pediatric-dose-rules.view-all')
            || $rule->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('pediatric-dose-rules.create');
    }

    public function update(User $user, PediatricDoseRule $rule): bool
    {
        $canEdit = $user->can('pediatric-dose-rules.edit-all')
            || (
                $rule->created_by === $user->id
                && $user->can('pediatric-dose-rules.edit-own')
            );

        return $canEdit
            && in_array($rule->status, ['draft', 'rejected'], true);
    }

    public function submit(User $user, PediatricDoseRule $rule): bool
    {
        return $rule->created_by === $user->id
            && $user->can('pediatric-dose-rules.submit')
            && in_array($rule->status, ['draft', 'rejected'], true);
    }

    public function approve(User $user, PediatricDoseRule $rule): bool
    {
        return $user->can('pediatric-dose-rules.approve')
            && $rule->created_by !== $user->id
            && $rule->status === 'pending_review';
    }

    public function reject(User $user, PediatricDoseRule $rule): bool
    {
        return $this->approve($user, $rule);
    }

    public function activate(User $user, PediatricDoseRule $rule): bool
    {
        return $user->can('pediatric-dose-rules.activate')
            && $rule->status === 'approved'
            && $rule->approved_at !== null;
    }

    public function deactivate(User $user, PediatricDoseRule $rule): bool
    {
        return $user->can('pediatric-dose-rules.activate')
            && $rule->status === 'approved';
    }

    public function viewAudit(User $user, PediatricDoseRule $rule): bool
    {
        return $user->can('pediatric-dose-rules.audit-all')
            || (
                $rule->created_by === $user->id
                && $user->can('pediatric-dose-rules.audit-own')
            );
    }
}
