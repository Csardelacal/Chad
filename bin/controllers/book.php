<?php

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

class BookController extends BaseController
{
	
	public function create($acctid, $currencyISO) {
		
		$ugrants  = db()->table('rights\user')->get('user', db()->table('user')->get('_id', $this->user->user->id))->addRestriction('write', true);
		$account  = db()->table('account')->get('ugrants', $ugrants)->addRestriction('_id', $acctid)->fetch();
		
		$currency = db()->table('currency')->get('ISO', $currencyISO)->addRestriction('removed', null, 'IS')->fetch();
		$book     = $account->getBook($currencyISO);
		
		if (!$account)  { throw new PublicException('User has no access to the account', 403); }
		if (!$currency) { throw new PublicException('Currency not available', 404); }
		if ($book)      { throw new PublicException('Book already exists', 400); }
		
		$record = db()->table('book')->newRecord();
		$record->account = $account;
		$record->currency = $currency;
		$record->balanced = 0;
		$record->store();
		
		$this->view->set('book', $record);
	}
	
}