<?php

/**
 * 2007-2023 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2023 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */


use Ramsey\Uuid\Uuid;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

class WooviValidationModuleFrontController extends ModuleFrontController
{
    /**
     * This class should be use by your Instant Payment
     * Notification system to validate the order remotely
     */
    public function postProcess()
    {
        /*
         * If the module is not active anymore, no need to process anything.
         */
        if ($this->module->active == false) {
            die;
        }

        /**
         * Since it is an example, we choose sample data,
         * You'll have to get the correct values :)
         */
        $cart = $this->context->cart;
        $cart_id = $cart->id;
        $customer_id = $cart->id_customer;
        $amount = $cart->getOrderTotal(true, Cart::BOTH);

        /*
         * Restore the context from the $cart_id & the $customer_id to process the validation properly.
         */
        Context::getContext()->cart = new Cart((int) $cart_id);
        Context::getContext()->customer = new Customer((int) $customer_id);
        Context::getContext()->currency = new Currency((int) Context::getContext()->cart->id_currency);
        Context::getContext()->language = new Language((int) Context::getContext()->customer->id_lang);

        $secure_key = Context::getContext()->customer->secure_key;
        if ($this->isValidOrder() === true) {
            $payment_status = Configuration::get('PS_OS_WS_PAYMENT');
            $message = null;
        } else {
            $payment_status = Configuration::get('PS_OS_ERROR');

            /**
             * Add a message to explain why the order has not been validated
             */
            $message = $this->module->l('An error occurred while processing payment');
        }

        $module_name = $this->module->displayName;
        $currency_id = (int) Context::getContext()->currency->id;

        $this->validateFormRequiredFields();

        $this->module->validateOrder(
            $cart_id,
            $payment_status,
            $amount,
            $module_name,
            $message,
            array(),
            $currency_id,
            false,
            $secure_key
        );

        $uuid = Uuid::uuid4();
        $order_total = $this->context->getCurrentLocale()->formatPrice($amount, (new Currency($currency_id))->iso_code);
        $arr = array(
            'correlationID' => $uuid->toString(),
            'value' => $this->extractNumbersFromNonDigits($order_total),
            'customer' => [
                'name' => $_POST['customerName'],
                'email' => $_POST['customerEmail'],
                'phone' => $_POST['customerPhone']
            ]
        );
        $arr_json = json_encode($arr);
        $this->createChargeWoovi($arr_json);
        $this->saveCorrelationIDToOrder($uuid->toString(), $cart_id);

        $customer = new Customer($customer_id);
        Tools::redirect($this->context->link->getPageLink(
            'order-confirmation',
            true,
            (int) $this->context->language->id,
            [
                'id_cart' => (int) $this->context->cart->id,
                'id_module' => (int) $this->module->id,
                'id_order' => (int) $this->module->currentOrder,
                'key' => $customer->secure_key,
            ]
        ));
    }

    protected function createChargeWoovi($arr_json)
    {
        try {
            $client = new Client();
            $appId = Configuration::get('WOOVI_APP_ID_OPENPIX');
            $headers = ['Authorization' => $appId, 'Content-Type' => 'application/json'];
            $request = new Request('POST', 'https://api.woovi.com/api/v1/charge', $headers, $arr_json);
            $client->sendAsync($request)->wait();
        } catch (ClientException $e) {
            $response_debug = Psr7\Message::toString($e->getResponse());
            PrestaShopLogger::addLog(strval($response_debug), 2);
        }
    }

    protected function extractNumbersFromNonDigits($total)
    {
        $pattern = '/\D+/';
        $replacement = '';
        $only_digits = preg_replace($pattern, $replacement, $total);
        return $only_digits;
    }

    protected function saveCorrelationIDToOrder($uuid, $cart_id)
    {
        Db::getInstance()->update(
            'orders',
            array('correlation_id' => $uuid),
            'id_cart = "' . $cart_id . '"',
            1,
            true
        );
    }

    protected function isValidOrder()
    {
        /*
         * Add your checks right there
         */
        return true;
    }

    protected function validateFormRequiredFields()
    {
        if (empty($_POST['customerName'] || $_POST['customerPhone'] || $_POST['customerEmail'])) {
            $this->errors[] = $this->l('All Pix form fields are required.');
            $this->redirectWithNotifications($this->context->link->getPageLink('order'));
        }
    }
}
