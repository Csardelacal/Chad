
<div class="heading topbar sticky">
	Add funds to your account
</div>

<div class="row1">
	<div class="span1">
		<form class="regular" method="POST">
			
			<?php if (!$account): ?>
			<div class="spacer" style="height: 30px"></div>
			
			<div class="row1 fluid">
				<div class="span1">
					<div class="field">
						<label for="account">Account</label>
						<select name="account" id="account">
							<?php $options = db()->table('account')->get('owner', db()->table('user')->get('_id', $authUser->user->id))->fetchAll(); ?>
							<?php foreach ($options as $option): ?>
							<option value="<?= $option->_id ?>"><?= $option->name ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			<?php endif; ?>

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
			
			<?php $every = new Every('</div><div class="row4">', 4); ?>
			
			<div class="row4">
				<?php foreach ($providers as /*@var $provider \payment\provider\ProviderInterface*/$provider): ?>
				<div class="span1">
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
				</div>
				
				<?= $every->next(); ?>
				<?php endforeach; ?>
			</div>

			<input type="submit" value="Add funds">
		</form>
	</div>
</div>

<?php if ($providers->isEmpty()): ?>
No payment providers enabled
<?php endif; ?>


