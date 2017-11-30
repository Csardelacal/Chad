

<div class="topbar sticky">
	<span class="toggle-button-target" style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"><span class="toggle-button hidden"></span></span>
	Your account: <?= $account->name ?>
</div>

<div class="spacer" style="height: 20px"></div>

<div class="row3 fluid has-dials">
	<div class="span2">
		<?php foreach ($books as $book): ?>
		<div class="material">
			<div><strong><a href="<?= url('account', 'balance', $account->_id, $book->currency->ISO) ?>"><?= $book->currency->ISO ?></a></strong></div>
			<div><span style="color: #777; font-size: .8em">Balance: <?= $currencyLocalizer->format($book->balance() / pow(10, $book->currency->decimals), $book->currency->sf()) ?></span></div>
		</div>
		<?php endforeach; ?>

		<?php if ($books->isEmpty()): ?>
		<div style="padding: 100px 0; text-align: center; color: #777; font-style: italic; font-size: .8em;">
			No currencies added to this account.
		</div>
		<?php endif; ?>
	</div>
</div>
	
