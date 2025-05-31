<?php

use App\Http\Controllers\Checkout\CheckoutController;
use App\Http\Controllers\Checkout\UpsellController;
use App\Http\Controllers\Checkout\Upsell2Controller;
use Illuminate\Support\Facades\Route;

Route::get('/checkout', [CheckoutController::class, 'index']);
Route::post('/checkout/create-order', [CheckoutController::class, 'createOrder'])->name('checkout.createOrder');

Route::get('/upsell1', function () {
    return view('checkout.upsell1');
});

Route::post('/upsell1/process', [UpsellController::class, 'processUpsell'])->name('upsell1.process');

Route::get('/upsell2', function () {
    return view('checkout.upsell2');
});

Route::post('/upsell2/process', [Upsell2Controller::class, 'processUpsell2'])->name('upsell2.process');
