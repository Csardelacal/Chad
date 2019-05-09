

<div class="heading topbar sticky">
	Your account: <?= $account->name ?>
</div>

<div class="spacer" style="height: 20px"></div>

<div class="row l3 has-dials">
	<div class="span l2">
		<?php foreach ($history as $record): ?>
		<div class="material">
			<?php $incoming = $account->_id === $record->target->account->_id ?>
			<?php $user  = $incoming? $record->source->account->owner : $record->target->account->owner; ?>
			<?php $displayname = $user? $user->_id : null ?>
			
			<div class="row l2 fluid">
				<div class="span l1">
					<div>
						<?php try { ?>
						<strong><?= $displayname? $sso->getUser($displayname)->getUsername() : 'External' ?></strong>
						<?php } catch (\Exception$e) { ?>
						<strong><?= $displayname ?></strong>
						<?php } ?>
					</div>
					<div>
						<span style="color: #777; font-size: .8em">
						<?= __($record->description) ?>
						</span>
					</div>
				</div>
				<div class="span l1" style="text-align: right">
					<div>
						<span style="color: #777; font-size: .8em">
						<?= date('m/d/Y', $record->created) ?>
						</span>
					</div>
					<div>
						<span style="<?= $incoming? 'color: #777' : 'color: #900' ?>; font-size: .8em">
						<?= $currencyLocalizer->format($record->amount / pow(10, $book->currency->decimals), $book->currency->sf()) ?>
						</span>
					</div>
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
	
