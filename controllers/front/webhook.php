<?php
require_once __DIR__ . '/../AbstractRestController.php';

class WooviWebhookModuleFrontController extends AbstractRestController
{
    protected function validSignature($payload, $signature)
    {
        $publicKey = base64_decode(Configuration::get('OPENPIX_PUBLIC_KEY_BASE64'));

        $verify = openssl_verify(
            $payload,
            base64_decode($signature),
            $publicKey,
            'sha256WithRSAEncryption'
        );

        return $verify === 1 ? true : false;
    }

    protected function isValidWebhookPayload($data)
    {
        if (!isset($data['event']) || empty($data['event'])) {
            if (!isset($data['evento']) || empty($data['evento'])) {
                return false;
            }
        }

        return true;
    }

    protected function instant_payment_notifications_handler()
    {
        $body = file_get_contents('php://input', true);
        $data = json_decode($body, true);

        $this->validateWebhook($data, $body);
    }

    protected function validateWebhook($data, $body)
    {
        $signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? null;

        if (!$signature || !$this->validSignature($body, $signature)) {
            header('HTTP/1.2 400 Bad Request');
            $response = [
                'error' => 'Invalid webhook signature.'
            ];

            echo json_encode($response);
            exit();
        }

        if (!$this->isValidWebhookPayload($data)) {
            header('HTTP/1.2 400 Bad Request');
            $response = [
                'error' => 'Invalid webhook payload.'
            ];

            echo json_encode($response);
            exit();
        }
    }
    protected function processGetRequest()
    {
        $this->ajaxDie(json_encode([
            'sucess' => true,
            'operation' => 'get'
        ]));
    }

    protected function processPostRequest()
    {
        $this->ajaxDie(json_encode([
            'sucess' => true,
            'operation' => 'post',
            'webhook' => $this->instant_payment_notifications_handler()
        ]));
    }

    protected function processPutRequest()
    {
        $this->ajaxDie(json_encode([
            'sucess' => true,
            'operation' => 'put'
        ]));
    }

    protected function processDeleteRequest()
    {
        $this->ajaxDie(json_encode([
            'sucess' => true,
            'operation' => 'delete'
        ]));
    }
}
