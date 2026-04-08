<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Support\AuthorizesFinanceGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    use AuthorizesFinanceGroup;

    public function index(Request $request)
    {
        $groupId = (int) $request->query('groupId');
        $this->authorizeGroup($request, $groupId);

        return AccountResource::collection(
            Account::query()->where('group_id', $groupId)->orderBy('id')->get()
        );
    }

    public function store(StoreAccountRequest $request): AccountResource
    {
        $groupId = (int) $request->input('groupId');
        $this->authorizeGroup($request, $groupId);

        $initialBalance = (float) $request->input('initialBalance', 0);

        $account = Account::create([
            'group_id' => $groupId,
            'user_id' => $request->input('userId'),
            'name' => $request->string('name')->toString(),
            'type' => \App\Enums\AccountType::fromFrontend($request->string('type')->toString()),
            'currency' => strtoupper($request->string('currency')->toString()),
            'initial_balance' => $initialBalance,
            'current_balance' => $initialBalance,
            'is_shared' => (bool) $request->input('shared', true),
            'is_active' => true,
        ]);

        return new AccountResource($account);
    }

    public function show(Request $request, Account $account): AccountResource
    {
        $this->authorizeGroup($request, $account->group_id);

        return new AccountResource($account);
    }

    public function update(UpdateAccountRequest $request, Account $account): AccountResource
    {
        $this->authorizeGroup($request, $account->group_id);

        $data = [];

        if ($request->filled('name')) {
            $data['name'] = $request->string('name')->toString();
        }

        if ($request->filled('type')) {
            $data['type'] = \App\Enums\AccountType::fromFrontend($request->string('type')->toString());
        }

        if ($request->filled('currency')) {
            $data['currency'] = strtoupper($request->string('currency')->toString());
        }

        if ($request->has('initialBalance')) {
            $initialBalance = (float) $request->input('initialBalance');
            $data['initial_balance'] = $initialBalance;
            $data['current_balance'] = $initialBalance;
        }

        if ($request->has('shared')) {
            $data['is_shared'] = (bool) $request->input('shared');
        }

        if ($request->has('isActive')) {
            $data['is_active'] = (bool) $request->input('isActive');
        }

        $account->update($data);

        return new AccountResource($account);
    }

    public function destroy(Request $request, Account $account): JsonResponse
    {
        $this->authorizeGroup($request, $account->group_id);
        $account->delete();

        return response()->json(['message' => 'Счет удален.']);
    }
}
