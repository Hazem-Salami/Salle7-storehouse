<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\auth\StorehouseAuthController;
use App\Http\Controllers\mail\MailController;

/**
 * Auth routes
 */

Route::group(['prefix' => 'store'],
    function () {
        /*********** Public routes  ***********/
        // auth
        Route::post('/register', [StorehouseAuthController::class, 'register'])->name('store.register');
        Route::post('/login', [StorehouseAuthController::class, 'login'])->name('store.login');

        /*********** Protected routes  ***********/
        Route::group(['middleware' => ['auth:api']],
            function () {
                // auth
                Route::post('/logout', [StorehouseAuthController::class, 'logout'])->name('store.logout');

                Route::group(['middleware' => ['verification.check'],'prefix' => 'mail'],
                    function () {
                        Route::post('', [MailController::class, 'SendVerificationCode'])->name('store.mail.send');
                        Route::post('/verify', [MailController::class, 'verification'])->name('store.mail.verify');
                    });

                Route::get('/isverify', [MailController::class, 'isVerified'])
                    ->name('users.check.verify')
                    ->middleware('verification.check');

                Route::post('/file/send', [StorehouseAuthController::class, 'sendAuthFiles'])->name('store.file.send');
            });
    });
