<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\category\CategoryController;

Route::group(['prefix' => 'store/category'],
    function () {
        /*********** Public routes  ***********/

        /*********** Protected routes  ***********/
        Route::group(['middleware' => ['auth:api']],
            function () {

                Route::get('/get/{children_id?}/{load_more?}', [CategoryController::class, 'getCategories'])
                    ->where(['children_id' => '[0-9]+'])
                    ->where(['load_more' => '[2-9][0-9]*'])
                    ->name('cms.category.get');

            });
    });
