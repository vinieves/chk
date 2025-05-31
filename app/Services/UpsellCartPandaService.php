<?php

namespace App\Services;

use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class UpsellCartPandaService
{
    public $checkoutId2;

    public function __construct()
    {
        $this->checkoutId2 = env('CHECKOUT_ID2');
    }

    public function processUpsell($previousOrderData): array
    {
        set_time_limit(60);

        try {
            $process = new Process([
                'node',
                '../scripts/bot.js',
                $this->checkoutId2, // Usa CHECKOUT_ID2 para o upsell
                $previousOrderData['firstName'],
                $previousOrderData['lastName'],
                $previousOrderData['email'],
                $previousOrderData['phone'],
                $previousOrderData['phoneCode'],
                $previousOrderData['cardNumber'],
                $previousOrderData['cardMonth'],
                $previousOrderData['cardYear'],
                $previousOrderData['cardCvv'],
                env('CONNECTION_URL'),
            ]);

            $process->run();

            // Log de erros do processo se houver
            if (! $process->isSuccessful()) {
                logger()->error('Processo do upsell falhou', ['error' => $process->getErrorOutput()]);
            }

            $output = $process->getOutput();
            $errors = $process->getErrorOutput();

            // Log de erros se houver
            if (! empty($errors)) {
                logger()->error('Erro ao criar pedido do upsell', ['errors' => $errors]);
            }

            // Log do output para debug
            logger()->info('Output do bot (upsell)', ['output' => $output]);

            // Tenta decodificar o JSON apenas para log
            try {
                $result = json_decode($output, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    logger()->info('Resultado do upsell processado', ['result' => $result]);
                }
            } catch (\Exception $e) {
                logger()->error('Erro ao processar resultado do upsell', ['error' => $e->getMessage()]);
            }

        } catch (\Exception $e) {
            logger()->error('Erro no processo do upsell', ['error' => $e->getMessage()]);
        }

        // Sempre retorna sucesso e redireciona
        return [
            'success' => true,
            'redirect_url' => '/obrigado'  // Pode ajustar para onde quiser redirecionar após o upsell
        ];
    }
} 