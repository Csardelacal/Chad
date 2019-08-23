
<?php $ugrants  = db()->table('rights\user')->get('user', db()->table('user')->get('_id', $authUser->user->id)); ?>
<?php $accounts = db()->table('account')->get('ugrants', $ugrants)->all(); ?>
<?php $balances = $accounts->each(function($e) { return ['account' => $e->_id, 'books' => collect($e->books->toArray())->each(function ($f) { return ['currency' => $f->currency->ISO, 'decimals' => $f->currency->decimals, 'balance' => $f->balance()]; })->toArray()]; })->toArray(); ?>
<div class="heading topbar sticky">
	Retrieve funds from your account
</div>

<div class="row l1">
	<div class="span l1">
		<form class="regular" id="form" method="POST">

			<?php if (!$account): ?>
			<div class="spacer" style="height: 30px"></div>

			<div class="row l1 fluid">
				<div class="span l1">
					<div class="field">
						<label for="account">Account</label>
						<select name="account" id="account">
							<?php foreach ($accounts as $option): ?>
							<option value="<?= $option->_id ?>"><?= $option->name ?> (~ <?= $option->estimatedBalance($preferences->currency) / pow(10, $preferences->currency->decimals) ?> <?= $preferences->currency->symbol?>)</option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if (!$amt): ?>
			<div class="spacer" style="height: 30px"></div>

			<div class="row l3 fluid">
				<div class="span l2">
					<div class="field">
						<label for="amt">Amount</label>
						<input type="text" name="amt" id="amt">
						<input type="hidden" name="decimals" value="natural">
					</div>
				</div>

				<div class="span l1">
					<div class="field">
						<label for="currency">Currency</label>
						<select name="currency" id="currency">
							<?php $currencies = db()->table('currency')->get('removed', null, 'IS')->fetchAll(); ?>
							<?php foreach ($currencies as $c): ?>
							<option value="<?= $c->ISO ?>"><?= $c->name ?> (<?= $c->ISO ?>)</option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<div class="spacer" style="height: 30px"></div>

			<?php $every = new Every('</div><div class="row l3">', 3); ?>

			<div class="row l3">
				<?php foreach ($providers as /*@var $provider \payment\provider\ProviderInterface*/$provider): ?>
				<div class="span l1">
					<div class="payment-provider" id="pp-<?= str_replace('\\', '-', get_class($provider)) ?>">
						<div class="row s4 fluid">
							<div class="span s1 pp-logo">
									<input type="radio" name="provider" value="<?= get_class($provider) ?>">
									<img src="<?= $provider->getLogo()->getEncoded(); ?>" id="logo-<?= str_replace('\\', '-', get_class($provider)) ?>">
							</div>
							<div class="span s3 pp-descr">
								<?= __($provider->getName()) ?>
								<div><small>Payout provider</small></div>
							</div>

						</div>
					</div>
					<script type="text/javascript">
					(function () { 
						document.getElementById('pp-<?= str_replace('\\', '-', get_class($provider)) ?>').addEventListener('click', function () {
							document.querySelector('.payment-provider.selected') && (document.querySelector('.payment-provider.selected').className ="payment-provider");
							document.getElementById('pp-<?= str_replace('\\', '-', get_class($provider)) ?>').className ="payment-provider selected";
							document.getElementById('pp-<?= str_replace('\\', '-', get_class($provider)) ?>').querySelector('input[type=radio]').click();
							document.getElementById('amt').value = 0;
						}); 
					}());
					</script>
				</div>

				<?= $every->next(); ?>
				<?php endforeach; ?>
			</div>

			<div class="form-footer">
				<input type="submit" value="Retrieve" id="getfunds" disabled>
			</div>
		</form>
	</div>
</div>

<script type="text/javascript">
(function() {
	var balances = <?= json_encode($balances) ?>;
	var maximum  = 0;
	var decimals = 0;
	
	document.getElementById('form').addEventListener('submit', function (e) {
		var amt = document.getElementById('amt').value * Math.pow(10, decimals);
		
		if (amt > 0 && amt <= maximum) {
			//Do nothing, it's perfectly fine
		}
		else {
			e.preventDefault();
		}
	});
	
	
	function determineMax(e) {
		var account  = document.getElementById('account').value;
		var currency = document.getElementById('currency').value;
		
		for (var i = 0; i < balances.length; i++) {
			if (account !== balances[i].account) { continue; }
			
			for (var j = 0; j < balances[i].books.length; j++) {
				if (currency !== balances[i].books[j].currency) { continue; }
				maximum = balances[i].books[j].balance;
				decimals = balances[i].books[j].decimals;
			}
		}
		
		if (document.getElementById('amt').value * Math.pow(10, decimals) > maximum || !document.querySelector('.payment-provider.selected')) {
			document.getElementById('getfunds').setAttribute('disabled', 'disabled');
		}
		else {
			document.getElementById('getfunds').removeAttribute('disabled');
		}
	}
	
	document.getElementById('account').addEventListener('change', determineMax);
	document.getElementById('amt').addEventListener('keyup', determineMax);
	determineMax();
}());
</script>
	
<?php if ($providers->isEmpty()): ?>
No payment providers enabled
<?php endif; ?>
