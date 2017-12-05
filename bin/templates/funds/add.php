
<div class="topbar sticky">
	<span class="toggle-button-target" style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"><span class="toggle-button hidden"></span></span>
	Add funds to your account
</div>

<form method="POST">
	<?php foreach ($providers as /*@var $provider \payment\provider\ProviderInterface*/$provider): ?>
	<input type="radio" name="provider" value="<?= get_class($provider) ?>"><?= get_class($provider); ?>
	<?php endforeach; ?>
	
	<input type="submit" value="Add funds">
</form>

<?php if ($providers->isEmpty()): ?>
No payment providers enabled
<?php endif; ?>


