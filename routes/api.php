<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Wallet\ChargesController;
use App\Http\Controllers\ComplaintsAndSuggestions\ComplaintsSuggestionsController;
use App\Http\Controllers\AccountRecovery\AccountRecoveryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['middleware' => ['auth:api']], function () {

    Route::group(['prefix' => 'users'],
        function () {

            //wallets
            Route::get('/wallets/status', [ChargesController::class, 'getStatus'])->name('users.wallets.status');
            Route::get('/wallets/balance', [ChargesController::class, 'getAmount'])->name('users.wallets.balance');
            Route::post('/wallets/new', [ChargesController::class, 'createWallet'])->name('users.wallets.new');
            //Complaints And Suggestions
            Route::post('/suggestion/add', [ComplaintsSuggestionsController::class, 'addSuggestion'])->name('users.suggestion.add');
            Route::post('/complaint/add', [ComplaintsSuggestionsController::class, 'addComplaint'])->name('users.complaint.add');
            Route::get('/complaints', [ComplaintsSuggestionsController::class, 'getComplaints'])->name('users.complaints.get');
            Route::get('/suggestions', [ComplaintsSuggestionsController::class, 'getSuggestions'])->name('users.suggestions.get');
        });
});

Route::group(['prefix' => 'users/mail/'],
    function () {
        Route::post('', [AccountRecoveryController::class, 'sendResetPasswordCode'])->name('users.mail.send');
        Route::post('/reset', [AccountRecoveryController::class, 'resetPassword'])->name('users.mail.reset');
    });
