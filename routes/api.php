<?php

use App\Http\Controllers\API\AuthControllerAPI;
use App\Http\Controllers\API\CompetitionControllerAPI;
use App\Http\Controllers\API\ContactControllerAPI;
use App\Http\Controllers\API\TeamControllerAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Route Auth
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthControllerAPI::class, 'register']);
    Route::post('/login', [AuthControllerAPI::class, 'login']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [AuthControllerAPI::class, 'logout']);
        ROute::get('/me', [AuthControllerAPI::class, 'me']);
    });
});

// Route Team
Route::middleware(['auth:sanctum'])->group(function () {
    Route::group(['prefix' => 'team'], function () {
        Route::get('/', [TeamControllerAPI::class, 'index']);
        Route::get('/{id}', [TeamControllerAPI::class, 'show']);
        Route::post('/', [TeamControllerAPI::class, 'store']);
        Route::put('/{id}', [TeamControllerAPI::class, 'update']);
        Route::delete('/{id}', [TeamControllerAPI::class, 'destroy']);
    });

    Route::group(['prefix' => 'competitions'], function () {
        Route::get('/', [CompetitionControllerAPI::class, 'index']);
        Route::post('/add', [CompetitionControllerAPI::class, 'create']);
        Route::put('/update/{id}', [CompetitionControllerAPI::class, 'update']);
        Route::get('/show/{id}', [CompetitionControllerAPI::class, 'show']);
        Route::delete('/delete/{id}', [CompetitionControllerAPI::class, 'destroy']);
    });

    // Team Invitation
    Route::group(['prefix' => 'teams'], function () {
        Route::get('/', [TeamControllerAPI::class, 'index']);
        Route::post('/create', [TeamControllerAPI::class, 'create_team_invitation']);
        Route::post('/viewInvitations/respond', [TeamControllerAPI::class, 'respond_to_invitation']);
        Route::get('/viewInvitations', [TeamControllerAPI::class, 'get_pending_invitations']);
        Route::delete('/delete/{id}', [TeamControllerAPI::class, 'delete_team_invitation']);
    });
  
    
});

Route::prefix('/contactus')->group(function () {
    // Add your contact us routes here
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [ContactControllerAPI::class, 'index']);
        Route::get('/show/{id}', [ContactControllerAPI::class, 'show']);
        Route::delete('/delete/{id}', [ContactControllerAPI::class, 'destroy']);
    });

    Route::post('/send', [ContactControllerAPI::class, 'store']);
});