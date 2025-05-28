<?php

namespace App\Services;

use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class CartPandaService
{
    public $checkoutId;

    public function __construct()
    {
        $this->checkoutId = env('CHECKOUT_ID');
    }

    public function createOrder($name, $cardNumber, $cardMonth, $cardYear, $cardCvv, $fakerLocale = 'en_US'): array
    {
        set_time_limit(60);
        $faker = fake($fakerLocale);
        $email = $this->generateEmail($name);

        $arrName = explode(' ', $name);
        $firstName = $arrName[0];
        array_shift($arrName);
        $lastName = implode(' ', $arrName);

        $phone = $faker->phoneNumber();
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $phoneCode = '1';

        $process = new Process([
            'node',
            '../scripts/bot.js',
            $this->checkoutId,
            $firstName,
            $lastName,
            $email,
            $phone,
            $phoneCode,
            $cardNumber,
            $cardMonth,
            $cardYear,
            $cardCvv,
            env('CONNECTION_URL'),
        ]);
        $process->run();
        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $output = $process->getOutput();
        $errors = $process->getErrorOutput();

        if (! empty($errors)) {
            logger()->error('Erro ao criar pedido', ['errors' => $errors]);
        }
        logger()->info('Output do bot', ['output' => $output]);

        try {
            $result = json_decode($output, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Erro ao decodificar JSON');
            }

            if (isset($result['success']) && $result['success'] === true) {
                return [
                    'success' => true,
                    'message' => 'Pedido criado com sucesso',
                ];
            }


            return [
                'success' => false,
                'message' => 'Erro ao buscar resultado',
            ];
        } catch (\Exception $e) {

            return [
                'success' => false,
                'message' => 'Erro ao buscar resultado',
            ];
        }
    }

    public function generateEmail($name)
    {
        $fullName = explode(' ', $name);

        $nameFirst = rand(0, 100) > 20;
        $email = '';
        if (! $nameFirst) {
            $email .= Str::random(rand(1, 5));
        }
        $email .= $fullName[0];

        $hasNumbers = rand(0, 100) > 40;
        if ($hasNumbers) {
            $email .= rand(0, 9999);
        }

        $hasSymbols = rand(0, 100) > 40;
        if ($hasSymbols) {
            $email .= '_';
        }

        $hasSurname = rand(0, 100) > 20;
        if ($hasSurname) {
            $email .= $fullName[1] ?? '';
        }

        $randChar = rand(0, 100) > 60;
        if ($randChar) {
            $email .= Str::random(rand(1, 4));
        }

        $email .= '@gmail.com';

        return strtolower($email);
    }
}
