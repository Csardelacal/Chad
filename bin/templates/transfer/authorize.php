<?php

if (current_context()->request->isPost() && $transfer) {
	//Now we can flag this as redirection to the authorize page.
	current_context()->response->setBody('redirecting...')->getHeaders()->redirect(url('transfer', 'authorize', $transfer->_id));
	return;
}

?>

<div class="heading topbar sticky">
	<span class="toggle-button-target" style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"><span class="toggle-button hidden"></span></span>
	Authorize the transaction for <?= $recipient->getUsername() ?>
</div>

<div class="spacer" style="height: 10px"></div>

<p style="font-size: .8em; color: #777">
	This page allows you to authorize a payment to "<?= $recipient->getUsername() ?>",
	please confirm the transaction details carefully before clicking "authorize".
	Once the payment has been authorized it cannot be undone.
</p>

<div class="spacer" style="height: 30px"></div>

<form method="POST" action="<?= url('transfer', 'authorize', $transfer->_id, $sig, ['returnto' => isset($_GET['returnto'])? $_GET['returnto'] : strval(url('transfer', 'execute', $transfer->_id)->absolute())]) ?>" class="regular">
	
	<?php if ($source instanceof BookModel): ?>
	<div style="text-align: center">
		<img class="avatar" style="border-radius: 50%" src="<?= $recipient->getAvatar(128) ?>">
		<h2><?= ucfirst($recipient->getUsername()) ?> - <?= $currencyLocalizer->format($transfer->amount / pow(10, $transfer->source->currency->decimals), $transfer->source->currency->sf()) ?></h2>
	</div>
	<?php else: ?>
	<div style="text-align: center">
		<img class="avatar" style="border-radius: 50%" src="<?= $recipient->getAvatar(128) ?>">
		<h2><?= ucfirst($recipient->getUsername()) ?> - <?= $currencyLocalizer->format($transfer->received / pow(10, $transfer->target->currency->decimals), $transfer->target->currency->sf()) ?></h2>
		
		<span class="styled-select">
			<select name="source">
				<?php foreach($accounts as $s): ?>
					<?php $books = $s->getBooks(); ?>
					<?php foreach ($books as $book): ?>
					<option value="<?= $s->_id ?>:<?= $book->currency->ISO ?>">
						<?= $s->name ?> (<?= $currencyLocalizer->format($book->balance() / pow(10, $book->currency->decimals), $book->currency->sf()) ?>)
					</option>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</select>
		</span>
	</div>

	<?php endif; ?>

	<div class="spacer" style="height: 30px"></div>
	<div class="separator"></div>

	<div style="text-align: right">
		<a href="<?= url('transfer', 'cancel', $transfer->_id) ?>">Cancel</a>
		<input type="submit" value="Authorize">
	</div>
</form>