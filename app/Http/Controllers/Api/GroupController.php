<?php

namespace App\Http\Controllers\Api;

use App\Enums\GroupRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Http\Resources\GroupResource;
use App\Models\FinanceGroup;
use App\Models\GroupMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class GroupController extends Controller
{
    public function index(Request $request)
{
    $groups = FinanceGroup::query()
        ->orderBy('id')
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

            return $group;
        });

        return response()->json(new GroupResource($group), 201);
    }

    public function show(Request $request, FinanceGroup $group): GroupResource
    {
        $this->authorizeGroup($request, $group);
        return new GroupResource($group);
    }

    public function update(UpdateGroupRequest $request, FinanceGroup $group): GroupResource
    {
        $this->authorizeGroup($request, $group);

        $data = [];
        if ($request->filled('name')) $data['name'] = $request->string('name')->toString();
        if ($request->filled('baseCurrency')) $data['base_currency'] = strtoupper($request->string('baseCurrency')->toString());

        $group->update($data);

        return new GroupResource($group);
    }

    public function destroy(Request $request, FinanceGroup $group): JsonResponse
    {
        $this->authorizeGroup($request, $group);
        $group->delete();

        return response()->json(['message' => 'Группа удалена.']);
    }

    protected function authorizeGroup(Request $request, FinanceGroup $group): void
    {
        abort_unless($group->members()->where('user_id', $request->user()->id)->exists(), 403, 'Нет доступа к группе.');
    }
}
