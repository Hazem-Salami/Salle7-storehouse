<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\order\purchase\PurchaseOrderController;

/**
 * Auth routes
 */

Route::group(['prefix' => 'store/purchase/order'],
    function () {
        /*********** Public routes  ***********/

        /*********** Protected routes  ***********/
        Route::group(['middleware' => ['auth:api', 'wallet.check']], function () {
            Route::get('/waiting/get', [PurchaseOrderController::class, 'getWaitingPurchaseOrders'])->name('store.purchase.order.waiting.get');
            Route::get('/accepted/get', [PurchaseOrderController::class, 'getAcceptedPurchaseOrders'])->name('store.purchase.order.accepted.get');
            Route::get('/rejected/get', [PurchaseOrderController::class, 'getRejectedPurchaseOrders'])->name('store.purchase.order.rejected.get');
            Route::put('/accept/{purchaseOrder}', [PurchaseOrderController::class, 'acceptPurchaseOrder'])->name('store.purchase.order.accept');
            Route::put('/reject/{purchaseOrder}', [PurchaseOrderController::class, 'rejectPurchaseOrder'])->name('store.purchase.order.reject');
        });
    });
