<?php

namespace App\Http\Controllers\Api;

use App\Enums\GroupRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Http\Resources\GroupResource;
use App\Models\FinanceGroup;
use App\Models\GroupMember;
use App\Support\AuthorizesFinanceGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    use AuthorizesFinanceGroup;

    public function index(Request $request)
    {
        $groups = $request->user()
            ->groups()
            ->withCount('groupMembers')
            ->orderBy('finance_groups.id')
            ->get();

        return GroupResource::collection($groups);
    }

    public function store(StoreGroupRequest $request): JsonResponse
    {
        $group = DB::transaction(function () use ($request) {
            $group = FinanceGroup::create([
                'name' => $request->string('name')->toString(),
                'base_currency' => strtoupper($request->string('baseCurrency')->toString()),
                'owner_id' => $request->user()->id,
            ]);

            GroupMember::create([
                'group_id' => $group->id,
                'user_id' => $request->user()->id,
                'role' => GroupRole::OWNER->value,
            ]);

            return $group->loadCount('groupMembers');
        });

        return response()->json(new GroupResource($group), 201);
    }

    public function show(Request $request, FinanceGroup $group): GroupResource
    {
        $this->authorizeGroup($request, $group);

        return new GroupResource($group->loadCount('groupMembers'));
    }

    public function update(UpdateGroupRequest $request, FinanceGroup $group): GroupResource
    {
        $this->authorizeGroupManagement($request, $group);

        $data = [];

        if ($request->filled('name')) {
            $data['name'] = $request->string('name')->toString();
        }

        if ($request->filled('baseCurrency')) {
            $data['base_currency'] = strtoupper($request->string('baseCurrency')->toString());
        }

        $group->update($data);

        return new GroupResource($group->loadCount('groupMembers'));
    }

    public function destroy(Request $request, FinanceGroup $group): JsonResponse
    {
        $this->authorizeGroupOwner($request, $group);
        $group->delete();

        return response()->json(['message' => 'Группа удалена.']);
    }
}
