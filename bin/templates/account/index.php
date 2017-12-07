

<div class="topbar sticky">
	<span class="toggle-button-target" style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"><span class="toggle-button hidden"></span></span>
	Your accounts
</div>

<?php foreach ($accounts as $account): ?>
<div class="spacer" style="height: 30px"></div>

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
<?php endforeach; ?>
