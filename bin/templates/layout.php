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
		<div style="max-width: 960px; margin: 0 auto; min-height: 100%;">
			<div class="contains-sidebar">
				<div class="sidebar">
					<div class="topbar" style="color: #999; padding-left: 10px">Chad</div>
					
					<div class="spacer" style="height: 20px"></div>
					
					<div class="menu-entry"><a href="<?= url('account', 'index')  ?>">My accounts</a></div>
					<div class="menu-entry"><a href="<?= url('account', 'create') ?>">Create account</a></div>
				</div>
			</div>
			<div class="content">
				<?= $content_for_layout ?>
			</div>
			<div style="clear: both; display: table"></div>
		</div>
		
		<script src="<?= spitfire\core\http\URL::asset('js/ui-layout.js') ?>" type="text/javascript"></script>
		<script src="<?= spitfire\core\http\URL::asset('js/dials.js') ?>" type="text/javascript"></script>
		<script src="<?= url('cron')->setExtension('js') ?>" type="text/javascript"></script>
	</body>
</html>