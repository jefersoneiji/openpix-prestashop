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

        $this->handleWebhookOrderUpdate($data);
    }

    protected function handleWebhookOrderUpdate($data)
    {
        $correlationID = $data['charge']['correlationID'];
        $isUpdated = Db::getInstance()->update(
            'orders',
            array('current_state' => 5),
            'correlation_id = "' . $correlationID . '"',
            1,
            true
        );

        $status = $data['charge']['status'];
        if ($isUpdated) {
            header('HTTP/1.1 200 OK');
            $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'orders` WHERE `correlation_id` LIKE "' . $correlationID . '"';
            $response = [
                'message' => 'sucess',
                'order_id' => Db::getInstance()->getRow($query)['id_order'],
                'correlationID' => $correlationID,
                'status' => $status
            ];
            echo json_encode($response);
            exit();
        }

        if (!$isUpdated) {
            header('HTTP/1.1 200 OK');
            $response = [
                'message' => 'fail',
                'error' => 'order not found',
                'order_id' => null,
                'status' => $status
            ];
            echo json_encode($response);
            exit();
        }
    }

    protected function methodNotAllowedResponse(){
        header('HTTP/1.2 405 Method Not Allowed');
        $response = [
            'error' => 'Method not allowed. Use POST.'
        ];

        echo json_encode($response);
        exit();
    }

    protected function processGetRequest()
    {
        $this->methodNotAllowedResponse();
        $this->ajaxDie(json_encode([
            'sucess' => true,
            'operation' => 'get',
        ]));
    }

    protected function processPostRequest()
    {
        $this->instant_payment_notifications_handler();
        $this->ajaxDie(json_encode([
            'sucess' => true,
            'operation' => 'post',
        ]));
    }

    protected function processPutRequest()
    {
        $this->methodNotAllowedResponse();
        $this->ajaxDie(json_encode([
            'sucess' => true,
            'operation' => 'put',
        ]));
    }

    protected function processDeleteRequest()
    {
        $this->methodNotAllowedResponse();
        $this->ajaxDie(json_encode([
            'sucess' => true,
            'operation' => 'delete',
        ]));
    }
}
