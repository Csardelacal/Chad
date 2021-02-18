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
				<a href="<?= url() ?>"></a>
			</div>
			
			<div class="right">
				<div class="has-dropdown" data-toggle="app-drawer" style="display: inline-block">
					<span class="app-switcher toggle" data-toggle="app-drawer"></span>
					<div class="dropdown right-bound unpadded" data-dropdown="app-drawer">
						<div class="app-drawer" id="app-drawer"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="auto-extend">
			<div  data-sticky-context>
				<?= $this->content() ?>
			</div>
		</div>
		
		<div class="contains-sidebar">
			<div class="sidebar">
				<div class="navbar">
					<div class="left">
						<a href="<?= url() ?>">CHAD</a>
					</div>
				</div>
				<div class="spacer" style="height: 10px"></div>

				<div class="menu-entry"><a href="<?= url('account', 'index')  ?>">My accounts</a></div>
				<div class="menu-entry"><a href="<?= url('account', 'create') ?>">Create account</a></div>
				<div class="menu-entry"><a href="<?= url('funds', 'retrieve') ?>">Transfer money</a></div>

				<div class="spacer" style="height: 10px"></div>

				<?php if ($privileges && $privileges->isAdmin()): ?>
				<div class="menu-title">Administration</div>
				<div class="menu-entry"><a href="<?= url('provider', 'index')  ?>">Payment providers</a></div>
				<div class="menu-entry"><a href="<?= url('payout', 'index')  ?>">Payouts</a></div>
				<div class="menu-entry"><a href="<?= url('currency', 'index')  ?>">Currency</a></div>
				<?php endif; ?>

				<div class="spacer" style="height: 10px"></div>
				
				<div class="menu-title">Our network</div>
				<div id="appdrawer"></div>
			</div>
		</div>
		
		<footer>
			<div class="row l1">
				<div class="span l1">
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
			
			depend(['m3/core/request'], function (Request) {
				var request = new Request('<?= $sso->getEndpoint() ?>/appdrawer.json');
				request
					.then(JSON.parse)
					.then(function (e) {
						e.forEach(function (i) {
							console.log(i)
							var entry = document.createElement('div');
							var link  = entry.appendChild(document.createElement('a'));
							var icon  = link.appendChild(document.createElement('img'));
							entry.className = 'menu-entry';
							
							link.href = i.url;
							link.appendChild(document.createTextNode(i.name));
							
							icon.src = i.icon.m;
							document.getElementById('appdrawer').appendChild(entry);
						});
					})
					.catch(console.log);
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
