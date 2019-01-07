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

<div class="heading topbar" data-sticky>
	Your accounts
</div>

<?php foreach ($accounts as $account): ?>
<div class="spacer" style="height: 30px"></div>

<div class="row1">
	<div class="span1">
		<div class="material">
			<div class="row3 fluid has-dials">
				<div class="span2">
					<div><strong><?= $account->name ?></strong><span style="color: #777; font-size: .8em" title="This is your account ID">::<?= $account->_id ?></span></div>
					<div class="spacer" style="height: 5px"></div>
					<div><span style="color: #777; font-size: .8em" title="This value may included converted currencies">Approx. <?= $currencyLocalizer->format($account->estimatedBalance($preferences->currency) / pow(10, $preferences->currency->decimals), $preferences->currency->sf()) ?></span></div>
				</div>
				<div class="span1 dials">
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
