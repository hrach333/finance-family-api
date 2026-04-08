<?php

namespace App\Support;

use App\Enums\GroupRole;
use App\Models\FinanceGroup;
use Illuminate\Http\Request;

trait AuthorizesFinanceGroup
{
    protected function authorizeGroup(Request $request, int|FinanceGroup $group, array $roles = []): FinanceGroup
    {
        $groupModel = $group instanceof FinanceGroup
            ? $group
            : FinanceGroup::query()->findOrFail($group);

        $membership = $groupModel->groupMembers()
            ->where('user_id', $request->user()->id)
            ->first();

        abort_unless($membership, 403, 'Нет доступа к группе.');

        if ($roles !== []) {
            abort_unless(in_array($membership->role, $roles, true), 403, 'Недостаточно прав для этого действия.');
        }

        return $groupModel;
    }

    protected function authorizeGroupManagement(Request $request, int|FinanceGroup $group): FinanceGroup
    {
        return $this->authorizeGroup($request, $group, [
            GroupRole::OWNER->value,
            GroupRole::ADMIN->value,
        ]);
    }

    protected function authorizeGroupOwner(Request $request, int|FinanceGroup $group): FinanceGroup
    {
        return $this->authorizeGroup($request, $group, [
            GroupRole::OWNER->value,
        ]);
    }
}
