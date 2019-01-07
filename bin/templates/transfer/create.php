<?php

if (current_context()->request->isPost() && $transfer) {
	//Now we can flag this as redirection to the authorize page.
	current_context()->response->setBody('redirecting...')->getHeaders()->redirect(url('transfer', 'authorize', $transfer->_id));
	return;
}

?>

<div class="heading topbar sticky">
	<span class="toggle-button-target" style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"><span class="toggle-button hidden"></span></span>
	Create a new transfer
</div>

<div class="spacer" style="height: 30px"></div>

<form method="POST" action="" class="regular">
	
	<!-- Source account -->
	<div class="field">
		<div class="row1 fluid">
			<div class="span1">
				<label for="src">Source account</label>
				<span class="styled-select">
					<select name="src">
						<?php foreach($sources as $source): ?>
							<?php $books = $source->account->getBooks(); ?>
							<?php foreach ($books as $book): ?>
							<option value="<?= $source->account->_id ?>:<?= $book->currency->ISO ?>">
								<?= $source->account->name ?> (<?= $currencyLocalizer->format($book->balance() / pow(10, $book->currency->decimals), $book->currency->sf()) ?>)
							</option>
							<?php endforeach; ?>
						<?php endforeach; ?>
					</select>
				</span>
			</div>
		</div>
	</div>
	
	
	<div class="field">
		<div class="row1 fluid">
			<div class="span1">
				<label for="tgt">Target account</label>
				<input type="text" name="tgt" placeholder="Target account ID...">
			</div>
		</div>
	</div>
	
	
	<div class="field">
		<div class="row1 fluid">
			<div class="span1">
				<label for="amt">Amount to be transferred</label>
			</div>
		</div>
		<div class="row4 fluid">
			<div class="span3">
				<input type="text" name="amt" placeholder="Amount...">
			</div>
			<div class="span1">
				<?php $currencies = db()->table('currency')->get('removed', null, 'IS')->fetchAll(); ?>
				<span class="styled-select">
					<select name="currency">
						<?php foreach($currencies as $currency): ?>
						<option value="<?= $currency->ISO ?>"><?= $currency->ISO ?></option>
						<?php endforeach; ?>
					</select>
				</span>
			</div>
		</div>
	</div>
	
	
	<div class="field">
		<div class="row1 fluid">
			<div class="span1">
				<label for="description">Description</label>
				<input type="text" name="description" placeholder="A brief description...">
			</div>
		</div>
	</div>
	
	<div class="spacer" style="height: 20px"></div>
	
	<div style="text-align: right">
		<input class="button" type="submit" value="Transfer">
	</div>
</form>
	
<?php if ($sources->isEmpty()): ?>
Empty
<?php endif; ?>

