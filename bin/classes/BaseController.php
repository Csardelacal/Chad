<?php

use auth\SSOCache;
use auth\Token;
use spitfire\cache\MemcachedAdapter;
use spitfire\core\Environment;
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
	
	protected $session;
	
	/**
	 *
	 * @var Token
	 */
	protected $token;
	protected $sso;
	protected $user;
	
	public function _onload() {
		$environment   = Environment::get();
		$memcached     = new MemcachedAdapter();
		$this->sso     = new SSOCache($environment->get('SSO.endpoint'), $environment->get('SSO.appID'), $environment->get('SSO.appSec'));
		
		/**
		 * Get the session information.
		 */
		$this->session = Session::getInstance();
		$this->token   = $this->session->getUser()? : $this->sso->getSSO()->makeToken($_GET['token']);
		
		/*
		 * Extract the token information for the logged in user. The token may be 
		 * from another application, in which case we require the user to identify
		 * themselves.
		 * 
		 * @todo The token does not contain application information. Therefore, currently,
		 * Chad cannot validate it's the source of the token.
		 */
		if ($this->token && $this->token instanceof Token) {
			$this->user = $memcached->get('chad_auth_' . $this->token->getId(), function () { return $this->token->getTokenInfo(); });
		}
		
		
	}
	
}