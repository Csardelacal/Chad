<?php

use spitfire\exceptions\HTTPMethodException;
use spitfire\exceptions\PublicException;

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

/**
 * 
 */
class AccountController extends BaseController
{
	
	public function index() {
		
		if ($this->user) {
			$ugrants  = db()->table('rights\user')->get('user', db()->table('user')->get('_id', $this->user->user->id));
			$accounts = db()->table('account')->get('ugrants', $ugrants)->fetchAll();
		}
		elseif ($this->authapp) {
			$accounts = collect(); //Accounts are not listabe for apps.
		}
		
		$this->view->set('accounts', $accounts);
	}
	
	public function create() {
		$rights = [];
		
		/*
		 * First, the application needs to check what kind of privileges the user
		 * has. In the event of the user being an application, they have a different
		 * set of them.
		 */
		if ($this->authapp) {
			/*
			 * The authorization app needs to be allowed by the user to manage and
			 * or create accounts that they then shall own.
			 */
			$auth = $this->sso->authApp($_GET['signature'], $this->token, ['account.create', 'account.resets']);
			
			if (!$auth->getAuthenticated())                     { throw new PublicException('Invalid app', 403); }
			if (!$auth->getContext('account.create')->exists()) { $auth->getContext('account.create')->create('Account creation', 'Allows the remote application to create accounts in Chad on your behalf.'); }
			if (!$auth->getContext('account.resets')->exists()) { $auth->getContext('account.resets')->create('Account resetting', 'Allows the remote application to create accounts in Chad that automatically reset.'); }
			
			if (!$auth->getContext('account.create')->isGranted()) {
				//TODO: This should not redirect, instead it should send a redirect link to the application requesting the data
				return $this->response->setBody('redirect...')->getHeaders()->redirect($auth->getRedirect(['account.create']) . '&returnto=' . rawurlencode(strval(url('account', 'create', ['signature' => $_GET['signature']])->absolute())));
			}
			
			$rights['create'] = $auth->getContext('account.create')->isGranted() == 2;
			$rights['reset']  = $auth->getContext('account.resets')->isGranted() == 2;
			$rights['tags']   = true;
			$rights['grant']  = true;
			
		}
		/*
		 * Without an application backing the user, they receive a limited set of 
		 * permissions. These should be lifted for administrators.
		 */
		elseif ($this->user) {
			$rights['create'] = true;
			$rights['reset']  = false;
			$rights['tags']   = false;
			$rights['grant']  = false;
		}
		else {
			throw new PublicException('Not authorized - #1711191310', 403);
		}
		
		try {
			/*
			 * In the event of the request not being posted, we will just abort
			 * creating an account. Instead we'll direct the user to the account
			 * creation form.
			 */
			if (!$this->request->isPost()) { throw new HTTPMethodException('Was not posted', 1711141027); }
			
			/*
			 * Once the data has been transferred to us, we can then check that 
			 * the data is properly formatted.
			 */
			$v = [
				'owner'  => validate($this->user? $this->user->user->id : $_POST['owner']), 
				'name'   => validate($_POST['name']),
				'resets' => validate($_POST['resets']),
				'tags'   => validate($_POST['tags'])
			];
			
			validate($v);
			
			/*
			 * Create a database record for the account to be stored in.
			 */
			$account = db()->table('account')->newRecord();
			$account->name   = $v['name']->getValue();
			$account->owner  = db()->table('user')->get('_id', $v['owner']->getValue())->fetch();
			$account->resets = $v['resets']->getValue();
			$account->tags   = $v['tags']->getValue();
			$account->store();
			
			/*
			 * If a user created the account they wish to be automatically be granted
			 * access to said account. Otherwise they would be permanently locked
			 * out of it.
			 * 
			 * On the other hand, applications may require access to an account they
			 * created to be restricted to the user.
			 */
			if ($this->authapp) {
				$grant = db()->table('rights\app')->newRecord();
				$grant->app     = $this->authapp;
				$grant->account = $account;
				$grant->write   = true;
				$grant->blame   = $this->user->user->id . '@' . $this->authapp;
				$grant->store();
			} 
			else {
				$grant = db()->table('rights\user')->newRecord();
				$grant->user    = db()->table('user')->get('_id', $this->user->user->id)->fetch();
				$grant->account = $account;
				$grant->write   = true;
				$grant->listed  = true;
				$grant->store();
			}
			
			/*
			 * Mark the creation as success. The user can now use the account to
			 * manage their money.
			 */
			$this->view->set('success', true);
		} 
		/*
		 * If the request was not posted, it means that a user is accessing this 
		 * via GET and wishes to create an account. If that's the case, we just 
		 * show them a form, providing the proper options.
		 */
		catch (HTTPMethodException $ex) {
			
		}
		/*
		 * Sometimes the issue will be validation failing
		 */
		catch (spitfire\validation\ValidationException$ex) {
			$this->view->set('messages', $ex->getResult());
			$this->view->set('success', false);
		}
		
		$this->view->set('rights', $rights);
	}
	
	public function balance($acctid, $currencyid) {
		
	}
	
	public function close() {
		
	}
}
