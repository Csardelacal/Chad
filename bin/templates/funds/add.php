
<div class="topbar sticky">
	<span class="toggle-button-target" style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"><span class="toggle-button hidden"></span></span>
	Add funds to your account
</div>

<form class="regular" method="POST">
	
	<?php if (!$amt): ?>
	<div class="spacer" style="height: 30px"></div>
	
	<div class="row3 fluid">
		<div class="span2">
			<div class="field">
				<label for="amt">Amount</label>
				<input type="text" name="amt" id="amt">
				<input type="hidden" name="decimals" value="natural">
			</div>
		</div>
		
		<div class="span1">
			<div class="field">
				<label for="currency">Currency</label>
				<select name="currency" id="currency">
					<?php $currencies = db()->table('currency')->get('removed', null, 'IS')->fetchAll(); ?>
					<?php foreach ($currencies as $c): ?>
					<option value="<?= $c->ISOCode ?>"><?= $c->name ?> (<?= $c->ISO ?>)</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	</div>
	<?php endif; ?>
	
	<div class="spacer" style="height: 30px"></div>
	
	<?php foreach ($providers as /*@var $provider \payment\provider\ProviderInterface*/$provider): ?>
	<div class="payment-provider">
		<input type="radio" name="provider" value="<?= get_class($provider) ?>" id="pp-<?= str_replace('\\', '-', get_class($provider)) ?>">
		<img src="<?= $provider->getLogo()->getEncoded(); ?>" id="logo-<?= str_replace('\\', '-', get_class($provider)) ?>">
		
		<script type="text/javascript">
		(function () { 
			document.getElementById('logo-<?= str_replace('\\', '-', get_class($provider)) ?>').addEventListener('click', function () {
				document.getElementById('pp-<?= str_replace('\\', '-', get_class($provider)) ?>').click();
			}); 
		}());
		</script>
	</div>
	<?php endforeach; ?>
	
	<input type="submit" value="Add funds">
</form>

<?php if ($providers->isEmpty()): ?>
No payment providers enabled
<?php endif; ?>


