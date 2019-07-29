
<div class="spacer" style="height: 20px"></div>

<div class="row l1">
	<div class="span l1">
		<form method="POST" action="<?= url('payment', 'complete') ?>" class="regular">
			<?php foreach($payouts as $payout): ?>
			<div class="row l4 fluid">
				<div class="span l1">
					<input type="checkbox" name="payment[<?= $payout->_id ?>]">
					<?= $sso->getUser($payout->account->owner->_id)->getUsername() ?> 
				</div><!--
				--><div class="span l1">
					<?= $payout->source ?> 
				</div><!--
				--><div class="span l1">
					<?= $currencyLocalizer->format($payout->amt / pow(10, $payout->currency->decimals), $payout->currency->sf()) ?> 
				</div><!--
				--><div class="span l1">
					<?= __($payout->additional) ?> 
				</div>
			</div>
			<div class="spacer" style="height: 20px"></div>
			<?php endforeach; ?>
			<div class="form-footer">
				<input type="submit" value="Mark as done">
			</div>
		</form>
	</div>
</div>