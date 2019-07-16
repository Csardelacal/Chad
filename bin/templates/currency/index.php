<div class="spacer" style="height: 30px"></div>

<?php foreach ($currencies as $currency): ?>
<div class="row">
	<div class="span">
		<div class="material">
			<a href="<?= url('currency', 'edit', $currency->_id) ?>"><?= $currency->name ?></a>
		</div>
	</div>
</div>

<div class="spacer" style="height: 30px"></div>
<?php endforeach ?>