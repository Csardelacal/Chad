<!DOCTYPE html>
<html>
	<head>
		
		<?php if (isset(${'page.title'})): ?> 
		<title><?= ${'page.title'} ?></title>
		<?php else: ?> 
		<title>Chad - Account management</title>
		<?php endif; ?> 
		
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" type="text/css" href="<?= spitfire\core\http\URL::asset('css/app.css') ?>">
		<link rel="stylesheet" type="text/css" href="<?= spitfire\core\http\URL::asset('css/ui-layout.css') ?>">
	</head>
	<body>
		<div class="navbar">
			<div class="left">
				<span class="toggle-button dark mobile-only"></span>
				<a href="<?= url() ?>">CHAD</a>
			</div>
			
			<div class="right">
				<div class="has-dropdown" data-toggle="app-drawer" style="display: inline-block">
					<span class="app-switcher toggle"></span>
					<div class="dropdown right-bound app-drawer" data-dropdown="app-drawer">
						<div class="row3 fluid"><!--
							--><div class="span1">
								<a class="app-entry">
									<img class="app-icon" src="<?= \spitfire\core\http\URL::asset('img/user.png') ?>">
									<span class="app-name">YCH</span>
								</a>
							</div><!--
							--><div class="span1">
								<a class="app-entry">
									<img class="app-icon" src="<?= \spitfire\core\http\URL::asset('img/user.png') ?>">
									<span class="app-name">YCH</span>
								</a>
							</div><!--
							--><div class="span1">
								<a class="app-entry">
									<img class="app-icon" src="<?= \spitfire\core\http\URL::asset('img/user.png') ?>">
									<span class="app-name">YCH</span>
								</a>
							</div>
						</div>
						<div class="row3 fluid"><!--
							--><div class="span1">
								<a class="app-entry">
									<img class="app-icon" src="<?= \spitfire\core\http\URL::asset('img/user.png') ?>">
									<span class="app-name">YCH</span>
								</a>
							</div><!--
							--><div class="span1">
								<a class="app-entry">
									<img class="app-icon" src="<?= \spitfire\core\http\URL::asset('img/user.png') ?>">
									<span class="app-name">YCH</span>
								</a>
							</div><!--
							--><div class="span1">
								<a class="app-entry">
									<img class="app-icon" src="<?= \spitfire\core\http\URL::asset('img/user.png') ?>">
									<span class="app-name">YCH</span>
								</a>
							</div>
						</div>
					</div>
				</div>
				<a href="<?= url('account') ?>">My accounts</a>
				<a href="<?= url('user', 'logout') ?>">Logout</a>
			</div>
		</div>
		<div>
			<div class="contains-sidebar collapsable">
				<div class="sidebar">
					<div class="heading topbar" style="padding-left: 10px"><span class="mobile-only">Menu</span></div>
					
					<div class="spacer" style="height: 10px"></div>
					
					<div class="menu-entry"><a href="<?= url('account', 'index')  ?>">My accounts</a></div>
					<div class="menu-entry"><a href="<?= url('account', 'create') ?>">Create account</a></div>
					
					<div class="spacer" style="height: 10px"></div>
					
					<?php if ($privileges && $privileges->isAdmin()): ?>
					<div class="heading" style="color: #999; padding-left: 10px">Administration</div>
					<div class="menu-entry"><a href="<?= url('redirection', 'index')  ?>">Redirections</a></div>
					<div class="menu-entry"><a href="<?= url('provider', 'index')  ?>">Payment providers</a></div>
					<?php endif; ?>
				</div>
			</div><!--
			--><div class="content">
				<?= $content_for_layout ?>
				<div class="spacer" style="height: 30px"></div>
			</div>
			
		</div>
		
		<div class="bottom">
			<div class="row1">
				<div class="span1">
					<span style="font-size: .8em; color: #777">
						&copy; <?= date('Y') ?> Magic3W - This software is licensed under MIT License
					</span>
				</div>
			</div>
		</div>
		
		<script src="<?= spitfire\core\http\URL::asset('js/depend.js') ?>" type="text/javascript"></script>
		<script type="text/javascript">
		(function () {
			window.depend.setBaseURL('<?= \spitfire\SpitFire::baseUrl() . '/' . ASSET_DIRECTORY . '/js/' ?>');
			
			depend(['ui/dropdown'], function (dropdown) {
				dropdown('.has-dropdown');
			});
		}());
		</script>
		<script src="<?= spitfire\core\http\URL::asset('js/ui-layout.js') ?>" type="text/javascript"></script>
		<script src="<?= spitfire\core\http\URL::asset('js/dials.js') ?>" type="text/javascript"></script>
		<script src="<?= url('cron')->setExtension('js') ?>" type="text/javascript"></script>
	</body>
</html>