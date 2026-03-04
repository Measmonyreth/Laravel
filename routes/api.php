<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CloneCardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SaveController;
use App\Http\Controllers\TextSearchController;
use Illuminate\Support\Facades\Auth;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Auth::routes() is for the web UI (laravel/ui). Not needed for API routes using Sanctum.
// If you need the web auth scaffolding install `laravel/ui` or add specific routes instead.

Route::post('register',[AuthController::class,'register']);
Route::post('login',[AuthController::class,'login']);
// Route::post('otp/verify', [AuthController::class, 'verifyOtp']);
// Route::post('otp/resend', [AuthController::class, 'resendOtp']);
Route::post('logout',[AuthController::class,'logout'])->middleware('auth:sanctum');
Route::post('/user/update',[AuthController::class,'update'])->middleware('auth:sanctum');
Route::get('user/get',[AuthController::class,'getUser'])->middleware('auth:sanctum');

//categories

Route::get('categories',[CategoryController::class,'index']);
Route::post('category',[CategoryController::class,'store']);
Route::put('category/{id}',[CategoryController::class,'update']);
Route::delete('category/{id}',[CategoryController::class,'destroy']);

//products
Route::post('product',[ProductController::class,'store']);
Route::get('products',[ProductController::class,'index']);
Route::get('product-cate/{id}',[ProductController::class,'getProductByCate']);
Route::get('product-search',[ProductController::class,'search']);

//carts
Route::post('cart',[CartController::class,'addToCart'])->middleware('auth:sanctum');
Route::get('viewCart',[CartController::class,'viewCart'])->middleware('auth:sanctum');
Route::post('remove-cart-item/{proId}',[CartController::class,'removeFromCart'])->middleware('auth:sanctum');
Route::post('cart/clear',[CartController::class,'clearCart'])->middleware('auth:sanctum');
Route::post('cart/update',[CartController::class,'updateCart'])->middleware('auth:sanctum');
Route::post('cart/checkout',[CartController::class,'checkoutCart'])->middleware('auth:sanctum');

//addresses
Route::post('address',[AddressController::class,'store'])->middleware('auth:sanctum');
Route::put('address/{id}',[AddressController::class,'update'])->middleware('auth:sanctum');
Route::get('address',[AddressController::class,'index'])->middleware('auth:sanctum');
Route::delete('address/{id}',[AddressController::class,'destroy'])->middleware('auth:sanctum');

//orders
Route::post('order',[OrderController::class,'store'])->middleware('auth:sanctum');
Route::get('orders',[OrderController::class,'index'])->middleware('auth:sanctum');
Route::post('order/checkout',[OrderController::class,'checkout'])->middleware('auth:sanctum');

Route::post('/send-notification', [NotificationController::class, 'sendNotification']);
Route::post('/send-notification-topic', [NotificationController::class, 'sendToTopic']);

//save products
Route::post('/save-product',[SaveController::class,'save'])->middleware('auth:sanctum');
Route::post('/unsave-product',[SaveController::class,'unsave'])->middleware('auth:sanctum');
Route::get('/saved-products',[SaveController::class,'getSavedProducts'])->middleware('auth:sanctum');

// text search
Route::post('/text-search',[TextSearchController::class,'store'])->middleware('auth:sanctum');
Route::get('/text-searches',[TextSearchController::class,'index']);
Route::get('/text-searches/user',[TextSearchController::class,'showOfUser'])->middleware('auth:sanctum');

// clone card
Route::post('/clone-card',[CloneCardController::class,'store'])->middleware('auth:sanctum');
Route::get('/clone-cards',[CloneCardController::class,'user'])->middleware('auth:sanctum');
Route::put('/clone-card',[CloneCardController::class,'update'])->middleware('auth:sanctum');

// payment
Route::post('/payment',[PaymentController::class,'store'])->middleware('auth:sanctum');
Route::get('/payment_user',[PaymentController::class,'getpayment'])->middleware('auth:sanctum');
