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
    <p>{l s='This page maybe simulate an external payment gateway : Order will be created with OrderState "Remote payment accepted".' mod='woovi'}</p>
    <button onclick="displayOpenPixModal()">
      Clique para abrir o modal
    </button>
    <script src="https://plugin.openpix.com.br/v1/openpix.js" async></script>
    <script>
      function displayOpenPixModal() {
        window.$openpix = window.$openpix || []; // priorize o objeto já instanciado
        
        let configObj ={};
        configObj['appID']="{$appId}";
        window.$openpix.push(['config', configObj]);

        let pixObj = {};
        pixObj['correlationID'] = "{$uuid}";
        pixObj['value'] = 100;
        window.$openpix.push([
          'pix',
          pixObj,
        ]);

        const logEvents = (e) => {
          if (e.type === 'CHARGE_COMPLETED') {
            console.log('a cobrança foi paga');
          }

          if (e.type === 'CHARGE_EXPIRED') {
            console.log('a cobrança foi expirada');
          }

          if (e.type === 'ON_CLOSE') {
            console.log('o modal da cobrança foi fechado');
          }
        }
        
      // only register event listener when plugin is already loaded
        if(!!window.$openpix?.addEventListener) {
          const unsubscribe = window.$openpix.addEventListener(logEvents);

          // parar de escutar os eventos
          // unsubscribe();
        }
      }
    </script>
  </section>
{/block}
