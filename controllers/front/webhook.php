<?php
require_once __DIR__ . '/../AbstractRestController.php';

class WooviWebhookModuleFrontController extends AbstractRestController
{
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
            'operation' => 'post'
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
