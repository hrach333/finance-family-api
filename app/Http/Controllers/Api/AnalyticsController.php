<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Support\AuthorizesFinanceGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    use AuthorizesFinanceGroup;

    public function __construct(private readonly AnalyticsService $analyticsService)
    {
    }

    public function summary(Request $request): JsonResponse
    {
        $groupId = (int) $request->query('groupId');
        $this->authorizeGroup($request, $groupId);

        return response()->json(
            $this->analyticsService->summary($groupId, $request->query('startDate'), $request->query('endDate'))
        );
    }
}
