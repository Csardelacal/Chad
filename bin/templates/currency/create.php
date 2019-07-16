
<div class="spacer" style="height: 30px"></div>

<?php if (isset($errors)): ?>
<?php var_dump($errors); ?>
<?php endif ; ?>

<form method="POST" action="">
	<div class="frm-group">
		<div class="row l3">
			<div class="span l1">
				<label>Name of the currency</label>
			</div>
			<div class="span l2">
				<input type="text" class="frm-ctrl" name="name">
			</div>
		</div>
	</div>

	<div class="frm-group">
		<div class="row l3">
			<div class="span l1">
				<label>ISO Code</label>
			</div>
			<div class="span l2">
				<input type="text" class="frm-ctrl" name="ISO">
			</div>
		</div>
	</div>
	
	<div class="spacer" style="height: 30px"></div>

	<div class="frm-group">
		<div class="row l6">
			<div class="span l1">
				<label>Symbol</label>
			</div>
			<div class="span l2">
				<input type="text" class="frm-ctrl" name="symbol">
			</div>
			<div class="span l1">
				<label>Decimals</label>
			</div>
			<div class="span l2">
				<input type="text" class="frm-ctrl" name="decimals">
			</div>
		</div>
	</div>

	<div class="frm-group">
		<div class="row l6">
			<div class="span l1">
				<label>Separator</label>
			</div>
			<div class="span l2">
				<span class="styled-select">
					<select  name="separator">
						<option value="<?= CurrencyModel::DISPLAY_THOUSAND_SEPARATOR_COMMA | CurrencyModel::DISPLAY_DECIMAL_SEPARATOR_STOP ?>">Comma for thousands, stop for decimals</option>
						<option value="<?= CurrencyModel::DISPLAY_THOUSAND_SEPARATOR_STOP | CurrencyModel::DISPLAY_DECIMAL_SEPARATOR_COMMA ?>">Stop for thousands, comma for decimals</option>
					</select>
				</span>
			</div>
			<div class="span l1">
				<label>Position</label>
			</div>
			<div class="span l2">
				<span class="styled-select">
					<select  name="position">
						<option value="<?= CurrencyModel::DISPLAY_SYMBOL_AFTER ?>">After</option>
						<option value="<?= CurrencyModel::DISPLAY_SYMBOL_BEFORE ?>">Before</option>
						<option value="<?= CurrencyModel::DISPLAY_SYMBOL_MIDDLE ?>">Middle</option>
					</select>
				</span>
			</div>
		</div>
	</div>

	<div class="frm-group">
		<div class="row l6">
			<div class="span l1">
				<label>Buy (user price)</label>
			</div>
			<div class="span l2">
				<input type="text" class="frm-ctrl" name="buy">
			</div>
			<div class="span l1">
				<label>Sell (user price)</label>
			</div>
			<div class="span l2">
				<input type="text" class="frm-ctrl" name="sell">
			</div>
		</div>
	</div>

	<div class="frm-group">
		<div class="row l3">
			<div class="span l1">
			</div>
			<div class="span l2" style="text-align: right">
				<input type="submit" class="button">
			</div>
		</div>
	</div>
</form>

<div class="spacer" style="height: 30px"></div>