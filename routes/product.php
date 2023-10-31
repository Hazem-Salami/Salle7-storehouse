<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\product\ProductController;

/**
 * Auth routes
 */

Route::group(['prefix' => 'store/product'],
    function () {
        /*********** Public routes  ***********/

        /*********** Protected routes  ***********/
        Route::group(['middleware' => ['auth:api', 'wallet.check']], function () {

                Route::group(['middleware' => ['category.existence']],
                    function () {

                        Route::post('/create/{id}', [ProductController::class, 'createProduct'])->name('store.product.create');
                        Route::get('/get/{id}', [ProductController::class, 'getProductsCategory'])->name('store.category.product.get');

                    });

                Route::get('/get', [ProductController::class, 'getProducts'])->name('store.product.get');

                Route::group(['middleware' => ['product.existence']],
                    function () {

                        Route::post('/{id}/update', [ProductController::class, 'updateProduct'])->name('store.product.update');
                        Route::delete('/{id}/delete', [ProductController::class, 'deleteProduct'])->name('store.product.delete');

                    });
            });
    });
