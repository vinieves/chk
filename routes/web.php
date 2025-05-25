<?php

use App\Http\Controllers\Checkout\CheckoutController;
use Illuminate\Support\Facades\Route;

Route::get('/checkout', [CheckoutController::class, 'index']);
Route::post('/checkout/create-order', [CheckoutController::class, 'createOrder'])->name('checkout.createOrder');
