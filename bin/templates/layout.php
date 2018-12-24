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
		<meta name="_scss" content="<?= \spitfire\SpitFire::baseUrl() ?>/assets/scss/_/js/">
	</head>
	<body>
		<div class="navbar">
			<div class="left">
				<span class="toggle-button dark"></span>
				<a href="<?= url() ?>">CHAD</a>
			</div>
			
			<div class="right">
				<div class="has-dropdown" data-toggle="app-drawer" style="display: inline-block">
					<span class="app-switcher toggle" data-toggle="app-drawer"></span>
					<div class="dropdown right-bound unpadded" data-dropdown="app-drawer">
						<div class="app-drawer" id="app-drawer"></div>
					</div>
				</div>
				<a href="<?= url('account') ?>">My accounts</a>
				<a href="<?= url('user', 'logout') ?>">Logout</a>
			</div>
		</div>
		<div class="auto-extend">
			<div class="contains-sidebar">
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
				<div  data-sticky-context>
<<<<<<< HEAD
					<?= $content_for_layout ?>
					<div class="spacer" style="height: 3000px"></div>

					<div class="heading topbar" data-sticky="bottom">
						Test
					</div>
				</div>
				
				<div  data-sticky-context>
					<div class="heading topbar" data-sticky="top">
						Test top
					</div>
					<div class="spacer" style="height: 3000px"></div>
					
					<div class="heading topbar" data-sticky="top">
						Test top 3
					</div>
					<div class="spacer" style="height: 3000px"></div>

					<div class="heading topbar" data-sticky="bottom">
						Test 2
					</div>
=======
					<?= $this->content() ?>
>>>>>>> origin/master
				</div>
			</div>
			
		</div>
		
		<footer>
			<div class="row1">
				<div class="span1">
					<span style="font-size: .8em; color: #777">
						&copy; <?= date('Y') ?> Magic3W - This software is licensed under MIT License
					</span>
				</div>
			</div>
		</footer>
		
		<script type="text/javascript">
		(function () {
			var ae = document.querySelector('.auto-extend');
			var wh = window.innerheight || document.documentElement.clientHeight;
			var dh = document.body.clientHeight;
			
			ae.style.minHeight = Math.max(ae.clientHeight + (wh - dh), 0) + 'px';
		}());
		</script>
		
		<script src="<?= spitfire\core\http\URL::asset('js/m3/depend.js') ?>" type="text/javascript"></script>
		<script src="<?= spitfire\core\http\URL::asset('js/m3/depend/router.js') ?>" type="text/javascript"></script>
		<script type="text/javascript">
		(function () {
			depend(['m3/depend/router'], function(router) {
				router.all().to(function(e) { return '<?= \spitfire\SpitFire::baseUrl() . '/assets/js/' ?>' + e + '.js'; });
				router.equals('phpas/app/drawer').to( function() { return '<?= $sso->getAppDrawerJS() ?>'; });
				router.equals('_scss').to( function() { return '<?= \spitfire\SpitFire::baseUrl() ?>/assets/scss/_/js/_.scss.js'; });
			});
			
			depend(['ui/dropdown'], function (dropdown) {
				dropdown('.app-switcher');
			});
			
			depend(['phpas/app/drawer'], function (drawer) {
				console.log(drawer);
			});
			
			depend(['_scss'], function() {
				console.log('Loaded _scss');
			});
			
			depend(['sticky'], function (sticky) {
				
				/*
				 * Create elements for all the elements defined via HTML
				 */
				var els = document.querySelectorAll('*[data-sticky]');

				for (var i = 0; i < els.length; i++) {
					sticky.stick(els[i], sticky.context(els[i]), els[i].getAttribute('data-sticky'));
				}
			});
		}());
		</script>
		<script src="<?= spitfire\core\http\URL::asset('js/dials.js') ?>" type="text/javascript"></script>
		<script src="<?= url('cron')->setExtension('js') ?>" type="text/javascript"></script>
	</body>
</html>
