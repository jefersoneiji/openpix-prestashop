{*
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
*}

<div class="row">
	<div class="col-xs-12 col-md-6">
		<p class="payment_module" id="woovi_payment_button">
			{if $cart->getOrderTotal() < 2}
				<a href="">
					<img src="{$domain|cat:$payment_button|escape:'html':'UTF-8'}" alt="{l s='Pay with my payment module' mod='woovi'}" />
					{l s='Minimum amount required in order to pay with my payment module:' mod='woovi'} {convertPrice price=2}
				</a>
			{else}
				<a href="{$link->getModuleLink('woovi', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}" title="{l s='Pay with my payment module' mod='woovi'}">
					<img src="{$module_dir|escape:'htmlall':'UTF-8'}/logo.png" alt="{l s='Pay with my payment module' mod='woovi'}" width="32" height="32" />
					{l s='Pay with my payment module' mod='woovi'}
				</a>
			{/if}
		</p>
	</div>
</div>
