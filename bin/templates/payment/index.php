
<div class="spacer" style="height: 20px"></div>

<div class="row1">
	<div class="span1">
		<form method="POST" action="<?= url('payout', 'complete') ?>" class="regular">
			<?php foreach($payouts as $payout): ?>
			<div class="row4 fluid">
				<div class="span1">
					<input type="checkbox" name="payout[<?= $payout->_id ?>]">
					<?= $sso->getUser($payout->account->owner->_id)->getUsername() ?> 
				</div><!--
				--><div class="span1">
					<?= $payout->source ?> 
				</div><!--
				--><div class="span1">
					<?= $currencyLocalizer->format($payout->amt / pow(10, $payout->currency->decimals), $payout->currency->sf()) ?> 
				</div><!--
				--><div class="span1">
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