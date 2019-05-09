
<?php if (isset($form)): ?>
<div class="row l1">
	<div class="span l1">
		<div class="spacer" style="height: 30px"></div>
		<?= $form; ?>
		<div class="spacer" style="height: 30px"></div>
	</div>
</div>
<?php endif; ?>

<?php if (isset($defer)): ?>
<div class="row l1">
	<div class="span l1">
		<h1>Payment is being processed</h1>
		<p>
			Please wait while your funds are being moved to your account. This may take a 
			few minutes to a few days depending on your payment provider.
		</p>
	</div>
</div>
<?php endif; ?>
