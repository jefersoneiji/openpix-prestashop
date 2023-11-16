<?php

abstract class AbstractRestController extends ModuleFrontController
{
    public function init()
    {
        header('Content-Type: ' . 'application/json');

        parent::init();

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                $this->processPostRequest();
                break;
            case 'GET':
                $this->processGetRequest();
                break;
            case 'PATCH':
            case 'PUT':
                $this->processPutRequest();
                break;
            case 'DELETE':
                $this->processDeleteRequest();
                break;
            default:
                break;
        }
    }

    abstract protected function processGetRequest();
    abstract protected function processPostRequest();
    abstract protected function processPutRequest();
    abstract protected function processDeleteRequest();
}
