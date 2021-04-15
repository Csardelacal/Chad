
<div class="row l1">
	<div class="span l1">
		<div class="heading topbar sticky">
			<span class="toggle-button-target" style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"><span class="toggle-button hidden"></span></span>
			Authorize the transaction for <?= $recipient->getUsername() ?>
		</div>

		<div class="spacer" style="height: 10px"></div>

		<p style="font-size: .8em; color: #777">
			This page allows you to authorize a payment to "<?= $recipient->getUsername() ?>",
			please confirm the transaction details carefully before clicking "authorize".
			You are currently not logged in, you can either log in or continue as a guest
		</p>

		<div class="spacer" style="height: 30px"></div>
		<div class="separator"></div>
		<div class="spacer" style="height: 30px"></div>
		
		<a  href="<?= url('funds', 'add', $transfer->target->account->_id, ['trx' => $transfer->_id, 'returnto' => $_GET['returnto']]) ?>">Continue as a guest</a>
		<div class="h-spacer" style="display: inline-block; width: 20px"></div>
		<a class="button" href="<?= url('user', 'login', ['returnto' => $_GET['returnto']]) ?>">Login</a>
	</div>
</div>