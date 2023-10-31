<?php

namespace App\Http\Controllers\category;

use App\Http\Controllers\Controller;
use App\Services\Category\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    /**
     *
     * @var CategoryService
     */
    protected CategoryService $categoryService;

    // singleton pattern, service container
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function getCategories(Request $request): Response
    {
        return $this->categoryService->getCategories($request);
    }
}
