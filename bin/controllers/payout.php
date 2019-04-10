<?php

use payment\provider\ExternalfundsModel;
use spitfire\exceptions\PublicException;

/* 
 * The MIT License
 *
 * Copyright 2019 César de la Cal Bretschneider <cesar@magic3w.com>.
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

class PayoutController extends BaseController
{
	
	public function index() {
		
		if (!$this->privileges->isAdmin()) {
			throw new PublicException('Forbidden', 403);
		}
		
		$this->view->set('payouts', db()->table('payment\provider\externalfunds')->get('type', ExternalfundsModel::TYPE_PAYOUT)->where('executed', null)->all());
	}
	
	public function complete() {
		
		if (!$this->privileges->isAdmin()) {
			throw new PublicException('Forbidden', 403);
		}
		
		if (!is_array($_POST['payout'])) {
			throw new PublicException('Forbidden', 403);
		}
		
		foreach ($_POST['payout'] as $id => $ign) {
			$payout = db()->table('payment\provider\externalfunds')->get('type', ExternalfundsModel::TYPE_PAYOUT)->where('_id', $id)->first();
			$payout->executed = time();
			$payout->store();
			
			$account   = $payout->account;
			$amt       = $payout->amt;
			$currency  = $payout->currency;
			
			/*
			 * Get the appropriate book to add the funds to
			 */
			try                  { $book = $account->getBook($currency); }
			catch (\Exception$e) { $book = $account->addBook($currency); }
			
			/*
			 * Once the amount has been charged, the application must proceed to 
			 * record the transaction.
			 */
			$source   = db()->table('payment\provider\source')->get('provider', $payout->source)->fetch();

			if ($source) {
			  $srcaccount = $source->account;
			}
			else {
			  $srcaccount = db()->table('account')->newRecord();
			  $srcaccount->name      = $payout->source;
			  $srcaccount->owner     = null;
			  $srcaccount->taxID     = null;
			  $srcaccount->store();

			  $source  = db()->table('payment\provider\source')->newRecord();
			  $source->provider = $payout->source;
			  $source->account  = $srcaccount;
			  $source->store();
			}
			
			$srcbook = $srcaccount->getBook($book->currency)? : $srcaccount->addBook($book->currency);
			
			$transfer = db()->table('transfer')->newRecord();
			$transfer->source = $book;
			$transfer->target = $srcbook;
			$transfer->amount = $amt;
			$transfer->received = $amt;
			$transfer->description = $payout->source;
			$transfer->created  = time();
			$transfer->executed = time();
			$transfer->store();
			$transfer->notify();
		}
		
		$this->response->setBody('Redirect...')->getHeaders()->redirect(url('payout'));
		
	}
}