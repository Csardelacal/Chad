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

class PaymentController extends BaseController
{
	
	public function index() {
		
		if (!$this->privileges->isAdmin()) {
			throw new PublicException('Forbidden', 403);
		}
		
		$this->view->set('payouts', db()->table('payment\provider\externalfunds')->get('type', ExternalfundsModel::TYPE_PAYMENT)->where('approved', '<>', null)->where('executed', null)->all());
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
			
			$payout->txn->authorized = time();
			$payout->txn->executed = time();
			$payout->store();
		}
		
		$this->response->setBody('Redirect...')->getHeaders()->redirect(url('payout'));
		
	}
}