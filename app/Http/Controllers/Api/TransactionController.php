<?php

namespace App\Http\Controllers\Api;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\FinanceGroup;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(private readonly TransactionService $transactionService)
    {
    }

    public function index(Request $request)
    {
        $groupId = (int) $request->query('groupId');
        $this->authorizeGroup($request, $groupId);

        $query = Transaction::query()->with(['account', 'category', 'creator', 'transferAccount'])->where('group_id', $groupId);

        if ($request->filled('accountId')) $query->where('account_id', (int) $request->query('accountId'));
        if ($request->filled('categoryId')) $query->where('category_id', (int) $request->query('categoryId'));
        if ($request->filled('type')) $query->where('type', TransactionType::fromFrontend((string) $request->query('type')));
        if ($request->filled('startDate')) $query->whereDate('transaction_date', '>=', (string) $request->query('startDate'));
        if ($request->filled('endDate')) $query->whereDate('transaction_date', '<=', (string) $request->query('endDate'));

        return TransactionResource::collection($query->orderByDesc('transaction_date')->orderByDesc('id')->get());
    }

    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $groupId = (int) $request->integer('groupId');
        $this->authorizeGroup($request, $groupId);

        $transaction = $this->transactionService->create([
            'group_id' => $groupId,
            'account_id' => $request->integer('accountId'),
            'created_by' => $request->integer('createdBy') ?: $request->user()->id,
            'type' => TransactionType::fromFrontend($request->string('type')->toString()),
            'amount' => $request->input('amount'),
            'currency' => strtoupper($request->string('currency')->toString()),
            'category_id' => $request->integer('categoryId') ?: null,
            'transfer_account_id' => $request->integer('transferAccountId') ?: null,
            'transaction_date' => $request->string('transactionDate')->toString(),
            'comment' => $request->input('comment'),
        ]);

        return response()->json(new TransactionResource($transaction), 201);
    }

    public function show(Request $request, Transaction $transaction): TransactionResource
    {
        $this->authorizeGroup($request, $transaction->group_id);
        $transaction->load(['account', 'category', 'creator', 'transferAccount']);

        return new TransactionResource($transaction);
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): TransactionResource
    {
        $this->authorizeGroup($request, $transaction->group_id);

        $data = [];
        if ($request->filled('accountId')) $data['account_id'] = $request->integer('accountId');
        if ($request->has('createdBy')) $data['created_by'] = $request->integer('createdBy') ?: null;
        if ($request->filled('type')) $data['type'] = TransactionType::fromFrontend($request->string('type')->toString());
        if ($request->filled('amount')) $data['amount'] = $request->input('amount');
        if ($request->filled('currency')) $data['currency'] = strtoupper($request->string('currency')->toString());
        if ($request->has('categoryId')) $data['category_id'] = $request->integer('categoryId') ?: null;
        if ($request->has('transferAccountId')) $data['transfer_account_id'] = $request->integer('transferAccountId') ?: null;
        if ($request->filled('transactionDate')) $data['transaction_date'] = $request->string('transactionDate')->toString();
        if ($request->has('comment')) $data['comment'] = $request->input('comment');

        $updated = $this->transactionService->update($transaction, $data);

        return new TransactionResource($updated);
    }

    public function destroy(Request $request, Transaction $transaction): JsonResponse
    {
        $this->authorizeGroup($request, $transaction->group_id);
        $this->transactionService->delete($transaction);

        return response()->json(['message' => 'Операция удалена.']);
    }

    protected function authorizeGroup(Request $request, int $groupId): void
    {
        abort_unless(
            FinanceGroup::query()->where('id', $groupId)->exists(),
            403,
            'Нет доступа к группе.'
        );
    }
}
