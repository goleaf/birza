<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\SearchProductsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductSearchRequest;
use Illuminate\Http\JsonResponse;

class ProductSearchController extends Controller
{
    public function search(ProductSearchRequest $request, SearchProductsAction $searchProductsAction): JsonResponse
    {
        return response()->json($searchProductsAction->handle(
            $request->searchTerm(),
            $request->searchLocale(),
        ));
    }
}
