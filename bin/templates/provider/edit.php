
<div class="heading topbar sticky">
	Editing <?= $provider->getName() ?>
</div>

<div class="row l1">
	<div class="span l1">
		<form class="regular" method="POST">

			<div class="spacer" style="height: 30px"></div>

			<div class="row l3 fluid">
				<div class="span l2">
					<?php $options  = $settings->getOptions() ?>

					<?php foreach ($options as $option): ?>
					<div class="field">
						<div class="spacer" style="height: 10px"></div>
						<?= $option->getFormComponent() ?>
					</div>
					<?php endforeach; ?>
					
					<div class="spacer" style="height: 10px"></div>
					<div style="text-align: right">
						<input type="submit" value="Save settings">
					</div>
				</div>
			</div>
		</form>
	</div>
</div>