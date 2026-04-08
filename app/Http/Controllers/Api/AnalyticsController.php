<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinanceGroup;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(private readonly AnalyticsService $analyticsService)
    {
    }

    public function summary(Request $request): JsonResponse
    {
        $groupId = (int) $request->query('groupId');

        abort_unless(
            FinanceGroup::query()->where('id', $groupId)->exists(),
            403,
            'Нет доступа к группе.'
        );

        return response()->json(
            $this->analyticsService->summary($groupId, $request->query('startDate'), $request->query('endDate'))
        );
    }
}
