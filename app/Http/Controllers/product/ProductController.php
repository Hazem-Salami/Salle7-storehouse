<?php

namespace App\Http\Controllers\product;

use App\Http\Controllers\Controller;
use App\Http\Requests\product\CreateProductRequest;
use App\Http\Requests\product\UpdateProductRequest;
use App\Models\Category;
use App\Services\Product\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    /**
     *
     * @var ProductService
     */
    protected ProductService $productService;

    // singleton pattern, service container
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function createProduct(CreateProductRequest $request): Response
    {
        return $this->productService->createProduct($request);
    }

    public function updateProduct(UpdateProductRequest $request): Response
    {
        return $this->productService->updateProduct($request);
    }

    public function deleteProduct(Request $request): Response
    {
        return $this->productService->deleteProduct($request);
    }

    public function getProductsCategory(Request $request): Response
    {
        return $this->productService->getProductsCategory($request);
    }

    public function getProducts(Request $request): Response
    {
        return $this->productService->getProducts($request);
    }
}
