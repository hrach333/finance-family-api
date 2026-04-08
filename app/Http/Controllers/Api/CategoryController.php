<?php

namespace App\Http\Controllers\Api;

use App\Enums\CategoryType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\FinanceGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $groupId = (int) $request->query('groupId');
        $this->authorizeGroup($request, $groupId);

        $query = Category::query()->where('group_id', $groupId);
        if ($request->filled('type')) $query->where('type', CategoryType::fromFrontend($request->string('type')->toString()));

        return CategoryResource::collection($query->orderBy('id')->get());
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $groupId = (int) $request->integer('groupId');
        $this->authorizeGroup($request, $groupId);

        $category = Category::create([
            'group_id' => $groupId,
            'type' => CategoryType::fromFrontend($request->string('type')->toString()),
            'name' => $request->string('name')->toString(),
            'icon_key' => $request->input('iconKey'),
        ]);

        return response()->json(new CategoryResource($category), 201);
    }

    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $this->authorizeGroup($request, $category->group_id);

        $data = [];
        if ($request->filled('type')) $data['type'] = CategoryType::fromFrontend($request->string('type')->toString());
        if ($request->filled('name')) $data['name'] = $request->string('name')->toString();
        if ($request->has('iconKey')) {
        $data['icon_key'] = $request->input('iconKey');
    }
        $category->update($data);

        return new CategoryResource($category);
    }

    public function destroy(Request $request, Category $category): JsonResponse
    {
        $this->authorizeGroup($request, $category->group_id);
        $category->delete();

        return response()->json(['message' => 'Категория удалена.']);
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
