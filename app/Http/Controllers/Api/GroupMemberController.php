<?php

namespace App\Http\Controllers\Api;

use App\Enums\GroupRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGroupMemberRequest;
use App\Http\Requests\UpdateGroupMemberRequest;
use App\Http\Resources\GroupMemberResource;
use App\Models\FinanceGroup;
use App\Models\GroupMember;
use App\Models\User;
use App\Support\AuthorizesFinanceGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupMemberController extends Controller
{
    use AuthorizesFinanceGroup;

    public function index(Request $request, FinanceGroup $group)
    {
        $this->authorizeGroup($request, $group);

        $members = $group->groupMembers()
            ->with('user')
            ->orderBy('id')
            ->get();

        return GroupMemberResource::collection($members);
    }

    public function store(StoreGroupMemberRequest $request, FinanceGroup $group): JsonResponse
    {
        $this->authorizeGroupManagement($request, $group);

        $user = User::query()
            ->where('email', mb_strtolower($request->string('email')->toString()))
            ->firstOrFail();

        if ($group->groupMembers()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Пользователь уже состоит в группе.'], 422);
        }

        $member = GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'role' => $request->input('role', GroupRole::MEMBER->value),
        ])->load('user');

        return response()->json(new GroupMemberResource($member), 201);
    }

    public function update(UpdateGroupMemberRequest $request, FinanceGroup $group, GroupMember $member): GroupMemberResource
    {
        $this->authorizeGroupManagement($request, $group);
        abort_unless($member->group_id === $group->id, 404);
        abort_if($member->role === GroupRole::OWNER->value, 422, 'Нельзя изменить роль владельца группы.');

        $member->update([
            'role' => $request->string('role')->toString(),
        ]);

        return new GroupMemberResource($member->load('user'));
    }

    public function destroy(Request $request, FinanceGroup $group, GroupMember $member): JsonResponse
    {
        $this->authorizeGroupManagement($request, $group);
        abort_unless($member->group_id === $group->id, 404);
        abort_if($member->role === GroupRole::OWNER->value, 422, 'Нельзя удалить владельца группы.');

        $member->delete();

        return response()->json(['message' => 'Пользователь удален из группы.']);
    }
}
