<?php if (isset($deleted) && $deleted) { current_context()->response->setBody('Redirect')->getHeaders()->redirect(url()); } ?>

<div class="spacer" style="height: 20px"></div>

<div class="row l1">
	<div class="span l1">
		<div class="heading topbar sticky">
			Confirm deletion
		</div>
	</div>
</div>

<div class="spacer" style="height: 20px"></div>

<div class="row l3">
	<div class="span l2">
			<p class="secondary">
				Are you sure you wish to close this account? Closing this account permanently
				revokes access so you're no longer able to access the account. For this to be
				possible the account needs to be balanced.
			</p>
			
			<div class="spacer medium"></div>
			
			<a class="button" href="<?= url('account', 'close', $account->_id, $token) ?>">Close this account</a>
		</div>
	</div>
</div>