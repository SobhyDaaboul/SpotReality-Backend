<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SignUpController;
use App\Http\Controllers\LogInController;
use App\Http\Controllers\AuthController;
use App\Http\Controller\ChangePasswordController;
use App\Http\Controller\ForgetPasswordController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Home
Route::middleware('auth:api')->group(function () {
    Route::post('homes', [HomeController::class, 'store']);
    Route::get('homes', [HomeController::class, 'index']);
    Route::get('/homes/{home}', [HomeController::class, 'show']);
    Route::put('/homes/{home}', [HomeController::class, 'update']);
    Route::delete('/homes/{home}', [HomeController::class, 'destroy']);
});



//User 
Route::get('/users', [AuthController::class, 'index']);
Route::get('/users/{id}', [AuthController::class, 'show']);
Route::put('/users/{id}', [AuthController::class, 'update']);
Route::delete('/users/{id}', [AuthController::class, 'destroy']);


Route::middleware('api')->post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/register', [SignUpController::class, 'register']);



Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');

Route::post('/change-password', [ChangePasswordController::class, 'changePassword'])->name('password.change');