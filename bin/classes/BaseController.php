<?php

use auth\SSO;
use auth\SSOCache;
use auth\Token;
use spitfire\cache\MemcachedAdapter;
use spitfire\core\Environment;
use spitfire\exceptions\PublicException;
use spitfire\io\session\Session;

/* 
 * The MIT License
 *
 * Copyright 2017 César de la Cal Bretschneider <cesar@magic3w.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class BaseController extends Controller
{
	
	/**
	 *
	 * @var Session
	 */
	protected $session;
	
	/**
	 *
	 * @var Token
	 */
	protected $token;
	
	protected $authapp;
	
	protected $preferences;
	
	/**
	 *
	 * @var SSO
	 */
	protected $sso;
	protected $user;
	
	public function _onload() {
		$environment   = Environment::get();
		$memcached     = new MemcachedAdapter();
		
		$this->sso     = new SSOCache($environment->get('SSO'));
		
		/**
		 * Get the session information.
		 */
		$this->session = Session::getInstance();
		$this->token   = $this->session->getUser()? : $this->sso->getSSO()->makeToken($_GET['token']);
		
		/*
		 * Extract the token information for the logged in user. The token may be 
		 * from another application, in which case we require the user to identify
		 * themselves.
		 */
		if ($this->token && $this->token instanceof Token) {
			$this->user    = $memcached->get('chad_auth_' . $this->token->getId(), function () { return $this->token->getTokenInfo()->authenticated? $this->token->getTokenInfo() : null; });
		}
		
		/*
		 * Sometimes, there's not only a token, but also a signature, indicating 
		 * that an application may be requesting data in the name of a user.
		 */
		if (isset($_GET['signature']) && $app = $this->sso->authApp($_GET['signature'], $this->token)) {
			//TODO: Validate whether the app granting access is the same providing the token
			$this->authapp = $app->getSrc()->getId();
		} 
		elseif ($this->user && $this->user->authenticated && $this->user->app->id != $this->sso->getAppId()) {
			throw new PublicException('Unprivileged token or signature missmatch', 403);
		}
		
		/*
		 * If the default currency doesn't exist. We need to create it.
		 */
		$c = db()->table('currency')->get('default', true)->fetch();
		
		if (!$c) {
			$c = db()->table('currency')->newRecord();
			$c->ISO = Environment::get('chad.default.currency.ISO')? : 'USD';
			$c->symbol = Environment::get('chad.default.currency.symbol')? : '$';
			$c->name = Environment::get('chad.default.currency.name')? : 'US Dollar';
			$c->decimals = Environment::get('chad.default.currency.decimals')? : 2;
			$c->conversion = Environment::get('chad.default.currency.conversion')? : 1;
			$c->display = Environment::get('chad.default.currency.display')? : (CurrencyModel::DISPLAY_SYMBOL_BEFORE|CurrencyModel::DISPLAY_DECIMAL_SEPARATOR_STOP|CurrencyModel::DISPLAY_THOUSAND_SEPARATOR_COMMA);
			$c->default = true;
			$c->store();
		}
		
		/*
		 * If the user is registered, then we check whether we have an entry in the
		 * local user setting table.
		 */
		if ($this->user && !db()->table('user')->get('_id', $this->user->user->id)->fetch()) {
			$r = db()->table('user')->newRecord();
			
			$r->_id = $this->user->user->id;
			$r->currency = $c;
			$r->display = $c->display;
			$r->store();
		}
		
		if ($this->user) {
			$this->preferences = db()->table('user')->get('_id', $this->user->user->id)->fetch();
			
			$currencyLocalizer = $this->preferences->localizer();
			$this->view->set('currencyLocalizer', $currencyLocalizer);
		}
		
		$this->view->set('preferences', $this->preferences);
	}
	
}