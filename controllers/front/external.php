<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

use Ramsey\Uuid\Uuid;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * This Controller simulate an external payment gateway
 */
class WooviExternalModuleFrontController extends ModuleFrontController
{
    /**
     * {@inheritdoc}
     */
    public function postProcess()
    {
        if (false === $this->checkIfContextIsValid() || false === $this->checkIfPaymentOptionIsAvailable()) {
            Tools::redirect($this->context->link->getPageLink(
                'order',
                true,
                (int) $this->context->language->id,
                [
                    'step' => 1,
                ]
            ));
        }

        $customer = new Customer($this->context->cart->id_customer);

        if (false === Validate::isLoadedObject($customer)) {
            Tools::redirect($this->context->link->getPageLink(
                'order',
                true,
                (int) $this->context->language->id,
                [
                    'step' => 1,
                ]
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function initContent()
    {
        parent::initContent();

        $appId = Configuration::get('WOOVI_APP_ID_OPENPIX');
        $uuid = Uuid::uuid4();
        $this->context->smarty->assign([
            'action' => $this->context->link->getModuleLink($this->module->name, 'validation', ['option' => 'external'], true),
            'appId' => $appId,
            'uuid' => $uuid,
        ]);

        $this->setTemplate('module:woovi/views/templates/front/external.tpl');

        
        $arr = array('correlationID' => $uuid->toString(), 'value' => 120);
        $arr_json = json_encode($arr);
        
        try {
            $client = new Client();
            $headers = ['Authorization' => $appId, 'Content-Type' => 'application/json'];
            $request = new Request('POST', 'https://api.woovi.com/api/v1/charge', $headers, $arr_json);
            $res = $client->sendAsync($request)->wait();
            PrestaShopLogger::addLog('response from api: ' . $res->getBody(), 1);

        } catch (ClientException $e) {
            $response_debug = Psr7\Message::toString($e->getResponse());
            PrestaShopLogger::addLog(strval($response_debug), 2);
        }

        $cart_total = $this->context->cart->getOrderTotal(true, Cart::BOTH);

        // PrestaShopLogger::addLog(strval($cart_total), 1);
        // PrestaShopLogger::addLog($uuid->toString(), 1);
        // PrestaShopLogger::addLog($arr_json, 1);

    }

    /**
     * Check if the context is valid
     *
     * @return bool
     */
    private function checkIfContextIsValid()
    {
        return true === Validate::isLoadedObject($this->context->cart)
            && true === Validate::isUnsignedInt($this->context->cart->id_customer)
            && true === Validate::isUnsignedInt($this->context->cart->id_address_delivery)
            && true === Validate::isUnsignedInt($this->context->cart->id_address_invoice);
    }

    /**
     * Check that this payment option is still available in case the customer changed
     * his address just before the end of the checkout process
     *
     * @return bool
     */
    private function checkIfPaymentOptionIsAvailable()
    {
        if (!Configuration::get(Woovi::CONFIG_PO_EXTERNAL_ENABLED)) {
            return false;
        }

        $modules = Module::getPaymentModules();

        if (empty($modules)) {
            return false;
        }

        foreach ($modules as $module) {
            if (isset($module['name']) && $this->module->name === $module['name']) {
                return true;
            }
        }

        return false;
    }
}
