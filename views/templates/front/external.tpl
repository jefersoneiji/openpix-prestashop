{**
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
 *}

{extends "$layout"}

{block name="content"}
  <section id="woovi-external" class="card card-block mb-2">
    <form action={$action} method="post" class="form-horizontal mb-1">
        <div class='text-sm-center'>
        <button type='submit' class="btn btn-primary">
            {l s='Confirm Order'}
        </button>
       </div> 
    </form>
     <div 
        id="openpix-order"
        data-appid={$appId}
        data-correlationid={$uuid} 
      />
    <script src="https://plugin.openpix.com.br/v1/openpix.js" async></script>
    </script>
  </section>
{/block}
