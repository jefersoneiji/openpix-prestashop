<form action="{$action}" id="payment-form" class="form-horizontal" method="post">

  <div class="form-group">
    <label class="form-control-label" for="customerName">{l s='Full name' mod='woovi'}</label>
    <input type="text" name="customerName" id="customerName" class="form-control" placeholder="{l s='Full name' mod='woovi'}" autocomplete="cc-name" required>
  </div>

  <div class="form-group">
    <label class="form-control-label" for="customerEmail">{l s='E-mail' mod='woovi'}</label>
    <input type="email" name="customerEmail" id="customerEmail" class="form-control"  placeholder="{l s='E-mail' mod='woovi'}" autocomplete="email" required>
  </div>

  <div class="form-group">
    <label class="form-control-label" for="customerPhone">{l s='Phone number' mod='woovi'}</label>
	<input type="tel" name="customerPhone" id="customerPhone" class="form-control" placeholder="{l s='(00) 0 0000-0000' mod='woovi'}" autocomplete="tel" required >
  </div>

</form>
