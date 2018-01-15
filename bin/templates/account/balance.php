

<div class="topbar sticky">
	<span class="toggle-button-target" style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"><span class="toggle-button hidden"></span></span>
	Your account: <?= $account->name ?>
</div>

<div class="spacer" style="height: 20px"></div>

<div class="row3 fluid has-dials">
	<div class="span2">
		<?php foreach ($history as $record): ?>
		<div class="material">
			<?php $incoming = $account->_id === $record->target->account->_id ?>
			<?php $displayname = $incoming? $record->source->account->owner->_id : $record->target->account->owner->_id ?>
			
			<div class="row2 fluid">
				<div class="span1">
					<strong><?= $displayname? $sso->getUser($displayname)->getUsername() : 'External' ?></strong>
				</div>
				<div class="span1" style="text-align: right">
					<span style="<?= $incoming? 'color: #777' : 'color: #900' ?>; font-size: .8em">
					<?= $currencyLocalizer->format($record->amount / pow(10, $book->currency->decimals), $book->currency->sf()) ?>
					</span>
				</div>
			</div>
			<div class="row1 fluid">
				<div class="span1">
					<span style="color: #777; font-size: .8em">
					<?= date('m/d/Y', $record->created) ?>
					</span>
				</div>
			</div>
		</div>
		
		<div class="spacer" style="height: 10px"></div>
		<?php endforeach; ?>

		<?php if ($history->isEmpty()): ?>
		<div style="padding: 100px 0; text-align: center; color: #777; font-style: italic; font-size: .8em;">
			Your account is still new! There's no transfers here.
		</div>
		<?php endif; ?>
	</div>
</div>
	
