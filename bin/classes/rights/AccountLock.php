<?php namespace rights;

use AccountModel;

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

class AccountLock
{
	
	private $account;
	
	public function __construct(AccountModel$account) {
		$this->account = $account;
	}
	
	public function unlock($user, $app) {
		$db = $this->account->getTable()->getDb();
		
		/*
		 * Standardize the input, so it can be operated properly.
		 */
		if ($user instanceof \UserModel) { $userId = $user->_id; }
		else                             { $userId = $user; }
		
		/*
		 * Create a query for the account that connects the other two queries.
		 */
		$accountQ = $db->table('account')->get('_id', $this->account->_id);
		
		/*
		 * If the user was provided, then we include it in our test
		 */
		if ($userId) {
			$userQ = $db->table('rights\user')->get('user__id', $userId);
			$userQ->addRestriction('write', true);
			
			$accountQ->addRestriction('ugrants', $userQ);
		}
		
		/*
		 * If the user was provided, then we include it in our test
		 */
		if ($app) {
			$appQ = $db->table('rights\app')->get('app', $app);
			$appQ->addRestriction('write', true);
			
			$accountQ->addRestriction('agrants', $appQ);
		}
		
		return !!$accountQ->fetch();
	}
}