<?php

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

class UserController extends BaseController
{
	
	/**
	 * The login endpoint allows a user to authenticate themselves against the 
	 * PHPAuthServer instance backing this software.
	 */
	public function login() {
		$session  = $this->session;
		$sso      = $this->sso;
		$returnto = $_GET['returnto']?? strval(url('account'));
		
		
		if (isset($_GET['code'])) {
			
			if ($_GET['state'] !== $session->get('state')) {
				throw new PublicException('OAuth error: State did not match', 403);
			}
			
			$request = request('http://localhost/Auth/token/create.json');
			$request->post('code', $_GET['code']);
			$request->post('type', 'code');
			$request->post('client', $this->sso->getAppId());
			$request->post('secret', $this->sso->getSecret());
			$request->post('verifier', $session->get('verifier'));
			$response = $request->send()->expect(200)->json();
			
			$token = $this->sso->makeToken($response->tokens->access->token);
			$session->lock($token);
			
			$this->response->setBody('Redirect')->getHeaders()->redirect(url());
			return;
			
		}
		
		$state = base64_encode(random_bytes(10));
		$verifier = base64_encode(random_bytes(20));
		
		$session->set('state', $state);
		$session->set('verifier', $verifier);
		
		$url = sprintf('http://localhost/Auth/auth/oauth/?%s', http_build_query([
			'response_type' => 'code',
			'client' => $this->sso->getAppId(),
			'state'  => $state,
			'redirect' => strval(url('account', 'login')->absolute()),
			'challenge' => sprintf('%s:%s', 'sha256', hash('sha256', $verifier))
		]));
		
		header('location: ' . $url);
		die();
	}
	
	
	public function logout() {
		$session = $this->session;
		$session->destroy();
		
		$this->response->setBody('Redirecting...')->getHeaders()->redirect(url('account'));
	}
	
	
}