<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Services\CartPandaService;

class CheckoutController extends Controller
{
    public function index()
    {
        return view('checkout.index');
    }

    public function createOrder()
    {
        $name = request()->input('name');
        $cardNumber = request()->input('cardNumber');
        $cardNumber = preg_replace('/[^0-9]/', '', $cardNumber);
        $cardMonth = request()->input('cardMonth');
        $cardMonth = preg_replace('/[^0-9]/', '', $cardMonth);
        $cardYear = request()->input('cardYear');
        $cardYear = preg_replace('/[^0-9]/', '', $cardYear);
        $cardYear = substr($cardYear, -2);
        $cardCvv = request()->input('cardCvv');
        $cardCvv = preg_replace('/[^0-9]/', '', $cardCvv);

        logger()->info('Criando pedido', ['name' => $name, 'cardNumber' => $cardNumber, 'cardMonth' => $cardMonth, 'cardYear' => $cardYear, 'cardCvv' => $cardCvv]);
        $cartPandaService = new CartPandaService;
        try {
            $result = $cartPandaService->createOrder(name: $name, cardNumber: $cardNumber, cardMonth: $cardMonth, cardYear: $cardYear, cardCvv: $cardCvv);
        } catch (\Exception $e) {
            logger()->error('Erro ao criar pedido', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Order creation failed'], 500);
        }

        return response()->json($result);
    }
}
