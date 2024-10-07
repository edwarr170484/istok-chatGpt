<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Middleware\ValidateToken;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;

Route::controller(AuthController::class)->prefix('user')->group(function(){
    Route::post("/oauth", "oauth");
    Route::post("/registration", "registration");
    Route::post("/login", "login");
    Route::post("/logout", "logout")->middleware(ValidateToken::class);
    
    Route::post("/auth", "auth")->middleware(ValidateToken::class);
});

Route::controller(ChatController::class)->group(function(){
    Route::get("/questions", "questions")->middleware(ValidateToken::class);
    Route::get("/chat", "chat")->middleware(ValidateToken::class);
});


