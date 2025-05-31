<?php

use App\Http\Controllers\Checkout\CheckoutController;
use App\Http\Controllers\Checkout\UpsellController;
use Illuminate\Support\Facades\Route;

Route::get('/checkout', [CheckoutController::class, 'index']);
Route::post('/checkout/create-order', [CheckoutController::class, 'createOrder'])->name('checkout.createOrder');

Route::get('/upsell1', function () {
    return view('checkout.upsell1');
});

Route::post('/upsell1/process', [UpsellController::class, 'processUpsell'])->name('upsell1.process');
