
<div class="spacer" style="height: 20px"></div>

<?php foreach($payouts as $payout): ?>
<div class="row l4">
	<div class="span l1">
		<?= $sso->getUser($payout->account->owner->_id)->getUsername() ?>
	</div>
	<div class="span l1">
		<?= $currencyLocalizer->format($payout->amt / pow(10, $payout->currency->decimals), $payout->currency->sf()) ?>
	</div>
	<div class="span l1">
		<?= __($payout->additional) ?>
	</div>
</div>
<div class="spacer" style="height: 20px"></div>
<?php endforeach; ?>
