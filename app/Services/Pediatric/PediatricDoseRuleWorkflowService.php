<?php

namespace App\Services\Pediatric;

use App\Models\PediatricDoseRule;
use App\Models\PediatricDoseRuleAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PediatricDoseRuleWorkflowService
{
    public function submit(
        PediatricDoseRule $rule,
        int $userId,
        ?string $ip = null
    ): PediatricDoseRule {
        return DB::transaction(function () use ($rule, $userId, $ip) {
            $rule = PediatricDoseRule::query()
                ->lockForUpdate()
                ->findOrFail($rule->id);

            if (!in_array($rule->status, ['draft', 'rejected'], true)) {
                $this->fail('Only draft or rejected rules can be submitted.');
            }

            $this->validateClinicalFields($rule);

            $from = $rule->status;

            $rule->update([
                'status' => 'pending_review',
                'submitted_at' => now(),
                'reviewed_by' => null,
                'reviewed_at' => null,
                'rejection_reason' => null,
                'approved_by' => null,
                'approved_at' => null,
                'is_active' => false,
            ]);

            $this->audit(
                $rule,
                $userId,
                'submitted',
                $from,
                'pending_review',
                null,
                $ip
            );

            return $rule->refresh();
        });
    }

    public function approve(
        PediatricDoseRule $rule,
        int $userId,
        ?string $ip = null
    ): PediatricDoseRule {
        return DB::transaction(function () use ($rule, $userId, $ip) {
            $rule = PediatricDoseRule::query()
                ->lockForUpdate()
                ->findOrFail($rule->id);

            if ($rule->status !== 'pending_review') {
                $this->fail('Only pending rules can be approved.');
            }

            if ($rule->created_by === $userId) {
                $this->fail('You cannot approve your own rule.');
            }

            $this->validateClinicalFields($rule);

            $rule->update([
                'status' => 'approved',
                'approved_by' => $userId,
                'approved_at' => now(),
                'reviewed_by' => $userId,
                'reviewed_at' => now(),
                'rejection_reason' => null,

                // Activation is a separate permission/action.
                'is_active' => false,
            ]);

            $this->audit(
                $rule,
                $userId,
                'approved',
                'pending_review',
                'approved',
                null,
                $ip
            );

            return $rule->refresh();
        });
    }

    public function reject(
        PediatricDoseRule $rule,
        int $userId,
        string $reason,
        ?string $ip = null
    ): PediatricDoseRule {
        return DB::transaction(function () use (
            $rule,
            $userId,
            $reason,
            $ip
        ) {
            $rule = PediatricDoseRule::query()
                ->lockForUpdate()
                ->findOrFail($rule->id);

            if ($rule->status !== 'pending_review') {
                $this->fail('Only pending rules can be rejected.');
            }

            if ($rule->created_by === $userId) {
                $this->fail('You cannot reject your own rule.');
            }

            $rule->update([
                'status' => 'rejected',
                'reviewed_by' => $userId,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
                'approved_by' => null,
                'approved_at' => null,
                'is_active' => false,
            ]);

            $this->audit(
                $rule,
                $userId,
                'rejected',
                'pending_review',
                'rejected',
                $reason,
                $ip
            );

            return $rule->refresh();
        });
    }

    public function activate(
        PediatricDoseRule $rule,
        int $userId,
        ?string $ip = null
    ): PediatricDoseRule {
        return DB::transaction(function () use ($rule, $userId, $ip) {
            $rule = PediatricDoseRule::query()
                ->lockForUpdate()
                ->findOrFail($rule->id);

            if (
                $rule->status !== 'approved'
                || $rule->approved_at === null
                || $rule->approved_by === null
            ) {
                $this->fail('Only approved rules can be activated.');
            }

            $from = $rule->is_active ? 'active' : 'inactive';

            $rule->update(['is_active' => true]);

            $this->audit(
                $rule,
                $userId,
                'activated',
                $from,
                'active',
                null,
                $ip
            );

            return $rule->refresh();
        });
    }

    public function deactivate(
        PediatricDoseRule $rule,
        int $userId,
        ?string $ip = null
    ): PediatricDoseRule {
        return DB::transaction(function () use ($rule, $userId, $ip) {
            $rule = PediatricDoseRule::query()
                ->lockForUpdate()
                ->findOrFail($rule->id);

            $rule->update(['is_active' => false]);

            $this->audit(
                $rule,
                $userId,
                'deactivated',
                'active',
                'inactive',
                null,
                $ip
            );

            return $rule->refresh();
        });
    }

    public function recordChange(
        PediatricDoseRule $rule,
        int $userId,
        array $changes,
        ?string $ip = null
    ): void {
        $this->audit(
            $rule,
            $userId,
            'updated',
            $rule->status,
            $rule->status,
            null,
            $ip,
            $changes
        );
    }

    private function validateClinicalFields(
        PediatricDoseRule $rule
    ): void {
        $errors = [];

        if (blank($rule->source_reference)) {
            $errors['source_reference'] =
                'A clinical source reference is required.';
        }

        if (
            (float) $rule->concentration_mg <= 0
            || (float) $rule->concentration_ml <= 0
        ) {
            $errors['concentration'] =
                'Concentration values must be greater than zero.';
        }

        if ((float) $rule->dose_mg_per_kg_per_dose <= 0) {
            $errors['dose_mg_per_kg_per_dose'] =
                'The approved dose parameter must be greater than zero.';
        }

        if (
            $rule->max_age_months !== null
            && $rule->max_age_months < $rule->min_age_months
        ) {
            $errors['max_age_months'] =
                'Maximum age must not be less than minimum age.';
        }

        if (
            $rule->max_weight_kg !== null
            && $rule->min_weight_kg !== null
            && (float) $rule->max_weight_kg <
                (float) $rule->min_weight_kg
        ) {
            $errors['max_weight_kg'] =
                'Maximum weight must not be less than minimum weight.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function audit(
        PediatricDoseRule $rule,
        int $userId,
        string $action,
        ?string $from,
        ?string $to,
        ?string $reason,
        ?string $ip,
        array $changes = []
    ): void {
        PediatricDoseRuleAudit::create([
            'pediatric_dose_rule_id' => $rule->id,
            'user_id' => $userId,
            'action' => $action,
            'from_status' => $from,
            'to_status' => $to,
            'reason' => $reason,
            'changes' => $changes ?: null,
            'ip_address' => $ip,
        ]);
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages([
            'workflow' => [$message],
        ]);
    }
}
