<?php

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

class ExternalDirector extends \spitfire\mvc\Director
{
	
	public function cleanup() {
		$pending = db()->table('payment\provider\externalfunds')->get('created', time() - 86400 * 90, '<')->where('approved', null)->all();
		
		foreach($pending as $remove) {
			console()->info('Cleaning up payout ' . $remove->_id)->ln();
			$remove->delete();
		}
	}
	
	public function payout() {
		$pending = db()->table('payment\provider\externalfunds')->get('type', payment\provider\ExternalfundsModel::TYPE_PAYOUT)->where('deferred', '!=', null)->where('processed', null)->where('approved', '!=', null)->all();
		$providers = \payment\ProviderPool::payouts()->configure();
		
		foreach($pending as $job) {
			
			$provider = $providers-filter(function ($e) use ($job) {
				return $job->source === get_class($e);
			});
			
			$r = $provider->run($job);
			
			if ($r instanceof \payment\payout\PayoutInterface) {
				$r->charge();
				
				$job->executed = time();
				$job->txn->executed = time();
				
				$job->store();
				$job->txn->store();
			}
		}
	}
	
	public function payment() {
		$pending = db()->table('payment\provider\externalfunds')->get('type', payment\provider\ExternalfundsModel::TYPE_PAYMENT)->where('deferred', '!=', null)->where('processed', null)->where('approved', '!=', null)->all();
		$providers = \payment\ProviderPool::payouts()->configure();
		
		foreach($pending as $job) {
			
			$provider = $providers-filter(function ($e) use ($job) {
				return $job->source === get_class($e);
			});
			
			/*@var $provider payment\provider\ProviderInterface */
			$r = $provider->await($job);
			
			if ($r instanceof \payment\payout\PaymentInterface) {
				$r->charge();
				
				$job->executed = time();
				$job->txn->executed = time();
				
				$job->store();
				$job->txn->store();
			}
		}
	}
	
}