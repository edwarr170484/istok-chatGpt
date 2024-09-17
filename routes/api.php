<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Middleware\ValidateToken;

use App\Http\Controllers\AuthController;

Route::controller(AuthController::class)->prefix('user')->group(function(){
    Route::post("/registration", "registration");
    Route::post("/login", "login");
    Route::post("/logout", "logout");
    
    Route::post("/auth", "auth")->middleware(ValidateToken::class);
});