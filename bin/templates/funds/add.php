
<div class="heading topbar sticky">
	Add funds to your account
</div>

<div class="row l1">
	<div class="span l1">
		<form class="regular" method="POST">
			
			<?php if (!$account): ?>
			<div class="spacer" style="height: 30px"></div>
			
			<div class="row l1 fluid">
				<div class="span l1">
					<div class="field">
						<label for="account">Account</label>
						<select name="account" id="account">
							<?php $ugrants  = db()->table('rights\user')->get('user', db()->table('user')->get('_id', $authUser->user->id)); ?>
							<?php $options = db()->table('account')->get('ugrants', $ugrants)->all(); ?>
							<?php foreach ($options as $option): ?>
							<option value="<?= $option->_id ?>"><?= $option->name ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			<?php endif; ?>

			<?php if (!$amt): ?>
			<div class="spacer" style="height: 30px"></div>

			<div class="row l3 fluid">
				<div class="span l2">
					<div class="field">
						<label for="amt">Amount</label>
						<input type="text" name="amt" id="amt" value="<?= $amt ?>">
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
			<?php else: ?>
			
			<div class="spacer" style="height: 30px"></div>
			<div class="material">
				<div class="row l3 fluid">
					<div class="span l2">
						<p class="secondary small">Account:</p>
						<p><?= __($account->name) ?></p>
					</div>

					<div class="span l1">
						<p class="secondary small">Amount:</p>
						<?= $currency->ISO ?><?= number_format($amt / pow(10, $currency->decimals), $currency->decimals) ?>
						<input type="hidden" name="currency" id="amt" value="<?= $currency->ISO ?>">
						<input type="hidden" name="amt" id="amt" value="<?= $amt ?>">
					</div>
				</div>
			</div>
			<?php endif; ?>

			<div class="spacer" style="height: 30px"></div>
			
			<?php foreach ($providers as /*@var $provider \payment\provider\ProviderInterface*/$provider): ?>
			<div class="payment-provider" id="pp-<?= str_replace('\\', '-', get_class($provider)) ?>">
				<div class="row s4 l8 fluid">
					<div class="span s1 l1 pp-logo">
							<input type="radio" name="provider" value="<?= get_class($provider) ?>">
							<img src="<?= $provider->getLogo()->getEncoded(); ?>" id="logo-<?= str_replace('\\', '-', get_class($provider)) ?>">
					</div>
					<div class="span s3 l7">
						<?= __($provider->getName()) ?>
					</div>

				</div>
			</div>
			<script type="text/javascript">
			(function () { 
				document.getElementById('pp-<?= str_replace('\\', '-', get_class($provider)) ?>').addEventListener('click', function () {
					document.querySelector('.payment-provider.selected') && document.querySelector('.payment-provider.selected').classList.remove("selected");
					document.getElementById('pp-<?= str_replace('\\', '-', get_class($provider)) ?>').classList.add("selected");
					document.getElementById('pp-<?= str_replace('\\', '-', get_class($provider)) ?>').querySelector('input[type=radio]').click();
					document.getElementById('addfunds').removeAttribute('disabled');
				}); 
			}());
			</script>
			<div class="spacer" style="height: 10px"></div>
			<?php endforeach; ?>
			
			<?php $every = new Every('</div><div class="row l4">', 4); ?>
			
			<?php foreach ($authorizations as $authorization): ?>
			<?php $provider = $providers->filter(function ($e) use ($authorization) { return get_class($e) === $authorization->provider; })->rewind(); ?>
			<div class="payment-provider" id="pp-<?= str_replace('\\', '-', get_class($provider)) ?>-<?= $authorization->_id?>">
				<div class="row s4 l8 fluid">
					<div class="span s1 l1 pp-logo">
							<input type="radio" name="provider" value="<?= get_class($provider) ?>:<?= $authorization->_id?>">
							<img src="<?= $provider->getLogo()->getEncoded(); ?>" id="logo-<?= str_replace('\\', '-', get_class($provider)) ?>">
					</div>
					<div class="span s3 l7">
						<?= __($provider->getName()) ?>
						<div><small><?= $authorization->human?: 'Empty' ?></small></div>
					</div>

				</div>
			</div>
			<script type="text/javascript">
			(function () { 
				document.getElementById('pp-<?= str_replace('\\', '-', get_class($provider)) ?>-<?= $authorization->_id?>').addEventListener('click', function () {
					document.querySelector('.payment-provider.selected') && document.querySelector('.payment-provider.selected').classList.remove("selected");
					document.getElementById('pp-<?= str_replace('\\', '-', get_class($provider)) ?>-<?= $authorization->_id?>').classList.add("selected");
					document.getElementById('pp-<?= str_replace('\\', '-', get_class($provider)) ?>-<?= $authorization->_id?>').querySelector('input[type=radio]').click();
					document.getElementById('addfunds').removeAttribute('disabled');
				}); 
			}());
			</script>
			<div class="spacer" style="height: 10px"></div>
				
			<?php endforeach; ?>
			
			<div class="form-footer">
				<input type="submit" value="Add funds" id="addfunds" disabled>
			</div>
		</form>
	</div>
</div>

<?php if ($providers->isEmpty()): ?>
No payment providers enabled
<?php endif; ?>


