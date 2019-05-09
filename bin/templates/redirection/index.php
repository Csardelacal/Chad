

<div class="heading topbar sticky">
	Redirections
</div>

<div class="spacer" style="height: 30px"></div>

<?php foreach ($redirections as $redirection): ?>
<div class="row l1">
	<div class="span l1">
		<div class="material">
			<div class="row l3 fluid has-dials">
				<div class="span l2">
					<div><strong><?= $redirection->name ?></strong></div>
					<div class="spacer" style="height: 5px"></div>
					<div><span style="color: #777; font-size: .8em"></span></div>
				</div>
				<div class="span l1 dials">
					<ul>
						<li>
							<a href="<?= url('redirection', 'rules', $redirection->_id) ?>">Edit rules</a>
							<a href="<?= url('redirection', 'targets', $redirection->_id) ?>">Edit targets</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
<?php endforeach; ?>