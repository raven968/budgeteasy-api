<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Category\CreateCategoryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;

#[Prefix('v1/categories')]
#[Middleware('auth:sanctum')]
class CategoryController extends Controller
{
    #[Get('/', name: 'v1.categories.index')]
    public function index(Request $request): AnonymousResourceCollection
    {
        $categories = $request->user()->categories()->orderBy('name')->get();

        return CategoryResource::collection($categories)
            ->additional(['meta' => ['max' => Category::MAX_PER_USER]]);
    }

    #[Post('/', name: 'v1.categories.store')]
    public function store(StoreCategoryRequest $request, CreateCategoryAction $create_category): JsonResponse
    {
        $category = $create_category->handle($request->user(), $request->validated());

        return CategoryResource::make($category)
            ->response()
            ->setStatusCode(201);
    }

    #[Put('{category}', name: 'v1.categories.update', middleware: 'can:update,category')]
    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $category->update($request->validated());

        return CategoryResource::make($category);
    }

    #[Delete('{category}', name: 'v1.categories.destroy', middleware: 'can:delete,category')]
    public function destroy(Category $category): Response
    {
        $category->delete();

        return response()->noContent();
    }
}
