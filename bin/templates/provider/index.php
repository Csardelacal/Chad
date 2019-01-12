

<div class="heading topbar sticky">
	Payment providers
</div>

<div class="spacer" style="height: 30px"></div>

<?php foreach ($providers as $provider): ?>
<div class="row1">
	<div class="span1">
		<div class="material">
			<div class="row3 fluid has-dials">
				<div class="span2">
					<div>
						<strong><?= $provider->getName() ?></strong>
						<span style="color: #777; font-size: .9em;"><?= $provider instanceof payment\provider\ProviderInterface? 'Payment provider' : 'Payout provider' ?></span>
					</div>
					<div class="spacer" style="height: 5px"></div>
					<div><span style="color: #777; font-size: .8em"></span></div>
				</div>
				<div class="span1 dials">
					<ul>
						<li>
							<a href="<?= url('provider', 'edit', str_replace('\\', '-', get_class($provider))) ?>">Edit settings</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
		
		<div class="spacer" style="height: 20px"></div>
	</div>
</div>
<?php endforeach; ?>