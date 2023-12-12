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

if (!defined('_PS_VERSION_')) {
    exit;
}

class Woovi extends PaymentModule
{
    protected $config_form = false;

    const CONFIG_PO_EXTERNAL_ENABLED = 'WOOVI_PO_EXTERNAL_ENABLED';

    public function __construct()
    {
        $this->name = 'woovi';
        $this->tab = 'payments_gateways';
        $this->version = '0.0.1';
        $this->author = 'Jeferson Eiji';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Woovi');
        $this->description = $this->l('Woovi module for payments using open pix');

        $this->limited_countries = array('US', 'BR');

        $this->limited_currencies = array('USD', 'BRL');

        $this->ps_versions_compliancy = array('min' => '1.7.6', 'max' => _PS_VERSION_);
    }


    private function installConfiguration()
    {
        return (bool) Configuration::updateGlobalValue(static::CONFIG_PO_EXTERNAL_ENABLED, '1');
    }

    private function uninstallConfiguration()
    {
        return (bool) Configuration::updateGlobalValue(static::CONFIG_PO_EXTERNAL_ENABLED, '1');
    }

    private function checkIfCorrelationIDExists()
    {
        $query =  'SELECT * FROM `' . _DB_PREFIX_ . 'orders`';
        $correlation_id_column_exists = isset(Db::getInstance()->getRow($query)['correlation_id']);
        return $correlation_id_column_exists;
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        $iso_code = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));

        if (in_array($iso_code, $this->limited_countries) == false) {
            $this->_errors[] = $this->l('This module is not available in your country');
            return false;
        }

        if ($this->checkIfCorrelationIDExists() === false) {
            include(dirname(__FILE__) . '/sql/install.php');
        }

        Configuration::updateValue('WOOVI_APP_ID_OPENPIX', '');
        Configuration::updateValue('OPENPIX_PUBLIC_KEY_BASE64', 'LS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS0KTUlHZk1BMEdDU3FHU0liM0RRRUJBUVVBQTRHTkFEQ0JpUUtCZ1FDLytOdElranpldnZxRCtJM01NdjNiTFhEdApwdnhCalk0QnNSclNkY2EzcnRBd01jUllZdnhTbmQ3amFnVkxwY3RNaU94UU84aWVVQ0tMU1dIcHNNQWpPL3paCldNS2Jxb0c4TU5waS91M2ZwNnp6MG1jSENPU3FZc1BVVUcxOWJ1VzhiaXM1WloySVpnQk9iV1NwVHZKMGNuajYKSEtCQUE4MkpsbitsR3dTMU13SURBUUFCCi0tLS0tRU5EIFBVQkxJQyBLRVktLS0tLQo=');
        Configuration::updateValue('WOOVI_LABEL_TITLE', $this->l('Pix by Woovi'));
        Configuration::updateValue('WOOVI_LABEL_DESCRIPTION', $this->l('Tax free payments using pix'));

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('payment') &&
            $this->registerHook('paymentReturn') &&
            $this->registerHook('paymentOptions') &&
            $this->registerHook('moduleRoutes') &&
            $this->installConfiguration();
    }

    public function uninstall()
    {
        Configuration::deleteByName('WOOVI_APP_ID_OPENPIX');
        Configuration::deleteByName('WOOVI_LABEL_TITLE');
        Configuration::deleteByName('WOOVI_LABEL_DESCRIPTION');
        Configuration::deleteByName('OPENPIX_PUBLIC_KEY_BASE64');

        return parent::uninstall() &&
            (bool) Configuration::deleteByName(static::CONFIG_PO_EXTERNAL_ENABLED);
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitWooviModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        return $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitWooviModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Set up'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('AppID OpenPix'),
                        'required' => true,
                        'name' => 'WOOVI_APP_ID_OPENPIX',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Label Title'),
                        'name' => 'WOOVI_LABEL_TITLE',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Label Description'),
                        'name' => 'WOOVI_LABEL_DESCRIPTION',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'WOOVI_APP_ID_OPENPIX' => Configuration::get('WOOVI_APP_ID_OPENPIX', ''),
            'WOOVI_LABEL_TITLE' => Configuration::get('WOOVI_LABEL_TITLE'),
            'WOOVI_LABEL_DESCRIPTION' => Configuration::get('WOOVI_LABEL_DESCRIPTION'),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    /**
     * Add front controller routes for webhook notifications. 
     */
    public function hookModuleRoutes()
    {
        return [
            'module-woovi-webhok' => [
                'rule' => 'woovi/webhook',
                'keywords' => [],
                'controller' => 'webhook',
                'params' => [
                    'fc' => 'module',
                    'module' => 'woovi'
                ]
            ]
        ];
    }

    /**
     * This method is used to render the payment button,
     * Take care if the button should be displayed or not.
     */
    public function hookPayment($params)
    {
        $currency_id = $params['cart']->id_currency;
        $currency = new Currency((int)$currency_id);

        if (in_array($currency->iso_code, $this->limited_currencies) == false)
            return false;

        $this->smarty->assign('module_dir', $this->_path);

        return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
    }

    protected function getCorrelationIDFromOrder($cart_id)
    {
        $sql = 'SELECT correlation_id FROM `' . _DB_PREFIX_ . 'orders` WHERE `id_cart` LIKE "' . $cart_id . '"';
        $uuid = Db::getInstance()->getValue($sql);
        return $uuid;
    }

    /**
     * This hook is used to display the order confirmation page.
     */
    public function hookPaymentReturn($params)
    {
        if ($this->active == false)
            return;

        $order = (isset($params['objOrder'])) ? $params['objOrder'] : $params['order'];

        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR'))
            $this->smarty->assign('status', 'ok');

        $cart_id = $order->id_cart;
        $uuid = $this->getCorrelationIDFromOrder($cart_id);

        $shop_name = $this->context->shop->name;
        $appId = Configuration::get('WOOVI_APP_ID_OPENPIX');
        $order_total = $this->context->getCurrentLocale()->formatPrice($params['order']->getOrdersTotalPaid(), (new Currency($params['order']->id_currency))->iso_code);

        $this->smarty->assign(array(
            'shop_name' => [$shop_name],
            'id_order' => $order->id,
            'reference' => $order->reference,
            'params' => $params,
            'total' => $order_total,
            'uuid' => $uuid,
            'appId' => $appId,
        ));

        return $this->display(__FILE__, 'views/templates/hook/confirmation.tpl');
    }

    /**
     * Return payment options available for PS 1.7+
     *
     * @param array Hook parameters
     *
     * @return array|null
     */
    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }
        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        $option = new \PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $option->setCallToActionText($this->l(Configuration::get('WOOVI_LABEL_TITLE')));
        $option->setAction($this->context->link->getModuleLink($this->name, 'validation', [], true));

        return [
            $option
        ];
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);
        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }
}
