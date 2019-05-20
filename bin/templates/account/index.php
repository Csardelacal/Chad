<?php

/*
 * In the special event of the user requesting a HTML based funds report, we
 * don't request the user to select the only currency, but instead redirect them
 * directly.
 */

if (!$authUser) {
	return current_context()->response->setBody('Redirecting...')
		->getHeaders()->redirect(url('user', 'login', ['returnto' => (string)url('account')]));
}

?>

<div class="spacer" style="height: 20px"></div>

<?php $payouts = db()->table('payment\provider\externalfunds')->get('type', \payment\provider\ExternalfundsModel::TYPE_PAYOUT)->where('executed', null)->all() ?>
<?php if (!$payouts->isEmpty()) : ?>

<div class="row l1">
	<div class="span l1">
		<div class="heading topbar" data-sticky>
			In progress
		</div>
	</div>
</div>

<div class="spacer" style="height: 15px"></div>

<div class="row l1">
	<div class="span l1">
		<div class="material unpadded">
			<?php	foreach ($payouts as $payout) : ?>
			<div class="padded">
				<div class="row l5 fluid">
					<div class="span l4">
						from <strong><?= $payout->account->name ?></strong>

						<?php if (!$payout->approved): ?>
						<div>
							<a href="<?= url('funds', 'execute', $payout->_id) ?>" style="color: #900">Incomplete</a>
						</div>
						<?php endif ?>
					</div>
					<div class="span l1" style="text-align: right">
						<?= $currencyLocalizer->format($payout->amt / pow(10, $payout->currency->decimals), $preferences->currency->sf()) ?>
					</div>
				</div>
			</div>
			<div class="separator"></div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
	
<div class="spacer" style="height: 25px"></div>
<?php endif; ?>


<div class="row l1">
	<div class="span l1">
		<div class="heading topbar" data-sticky>
			Your accounts
		</div>
	</div>
</div>

<?php foreach ($accounts as $account): ?>
<div class="spacer" style="height: 15px"></div>

<div class="row l1">
	<div class="span l1">
		<div class="material">
			<div class="row l3 fluid has-dials">
				<div class="span l2">
					<div><strong><?= $account->name ?></strong><span class="not-mobile" style="color: #777; font-size: .8em" title="This is your account ID">::<?= $account->_id ?></span></div>
					<div class="spacer" style="height: 5px"></div>
					<div><span style="color: #777; font-size: .8em" title="This value may include converted currencies">Approx. <?= $currencyLocalizer->format($account->estimatedBalance($preferences->currency) / pow(10, $preferences->currency->decimals), $preferences->currency->sf()) ?></span></div>
				</div>
				<div class="span l1 dials">
					<ul>
						<li>
							<a href="<?= url('funds', 'add', $account->_id) ?>">Add funds</a>
						</li>
						<li>
							<a href="<?= url('account', 'balance', $account->_id) ?>">Show balance</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
<?php endforeach; ?>
