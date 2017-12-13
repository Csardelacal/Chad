<?php

use spitfire\exceptions\HTTPMethodException;
use spitfire\exceptions\PublicException;
use spitfire\validation\ValidationException;

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

class FundsController extends BaseController
{
	
	
	public function add($acctid, $currencyISO = null, $amtParam = null) {
		
		/*
		 * Prepare the provider list
		 */
		$providers = payment\provider\PaymentProviderPool::getInstance()->configure(); // Prepares the providers by loading their configuration
		$currency  = $currencyISO? db()->table('currency')->get('ISO', _def($_POST['currency'], $currencyISO))->fetch() : db()->table('currency')->get('default', true)->fetch();
		
		try {
			/*
			 * First thing we need to do is check whether the request was posted,
			 * if this is not the case, we need to show the user the source select 
			 * screen.
			 */
			if (!$this->request->isPost()) { throw new HTTPMethodException('Not POSTed', 1712051108); }
			
			$account  = db()->table('account')->get('_id', $acctid)->fetch();
			$amt      = _def($_POST['amt'], $amtParam);
			
			
			/* @var $provider \payment\provider\ProviderInterface */
			$provider = $providers->filter(function ($e) {
				return !!(_def($_POST['provider'], null) === get_class($e));
			})->rewind();
			
			if ($amt < 0)   { throw new ValidationException('Invalid amount', 1712051113); }
			if (!$currency) { throw new PublicException('No currency found', 404); }
			if (!$provider) { throw new PublicException('No provider found', 404); }
			
			$record = db()->table('payment\provider\externalfunds')->newRecord();
			$record->source   = get_class($provider);
			$record->amt      = $amt;
			$record->account  = $account;
			$record->currency = $currency;
			$record->returnto = _def($_GET['returnto'], strval(url('account')->absolute()));
			$record->store();
			
			$this->response->setBody('Redirecting...')->getHeaders()->redirect(url('funds', 'execute', $record->_id));
			return;
		} 
		catch (HTTPMethodException $ex) {
			/*
			 * Do nothing, just show the appropriate screen
			 */
		}
		
		$this->view->set('currency', $currency);
		$this->view->set('providers', $providers);
	}
	
	public function execute($fid) {
		
		/*
		 * Prepare the provider list
		 */
		$providers = payment\provider\PaymentProviderPool::getInstance()->configure(); // Prepares the providers by loading their configuration
		
		$job       = db()->table('payment\provider\externalfunds')->get('_id', $fid)->fetch();
		$account   = $job->account;
		$amt       = $job->amt;
		$currency  = $job->currency;
		
		/*
		 * Get the appropriate book to add the funds to
		 */
		try                  { $book = $account->getBook($currency); }
		catch (\Exception$e) { $book = $account->addBook($currency); }
			
		/* @var $provider \payment\provider\ProviderInterface */
		$provider = $providers->filter(function ($e) use ($job) {
			return !!($job->source === get_class($e));
		})->rewind();
		
		/*
		 * Create the context to manage the payment authorization.
		 */
		$context = new payment\provider\PaymentAuthorization();
		$context->setAmt($amt);
		$context->setCurrency($currency);
		$context->setSuccessURL(url('funds', 'execute', $fid)->absolute());
		$context->setFailureURL(url('funds', 'failed', $fid)->absolute());
		$context->setFormData($_REQUEST);
		
		/*
		 * Try and authorize the payment, pulling funds from the external source
		 */
		$return = $provider->authorize($context);
		
		/*
		 * If the payment requires further authorization, we redirect the user to 
		 * the url the payment provider directed us.
		 */
		if ($return instanceof \payment\provider\Redirection) {
			$this->response->setBody('redirecting...')->getHeaders()->redirect($return->getTarget());
			return;
		}
		
		/*
		 * Once the payment provider has authorized the payment, we direct the 
		 * user to the URL that we were indicated by the source app.
		 */
		if ($provider->execute($context, time(), $amt)) {
			
			$source   = db()->table('payment\provider\source')->get('provider', get_class($provider))->fetch();
			
			if ($source) {
				$srcaccount = $source->account;
			}
			else {
				$srcaccount = db()->table('account')->newRecord();
				$srcaccount->name      = get_class($provider);
				$srcaccount->owner     = null;
				$srcaccount->taxID     = null;
				$srcaccount->resets    = AccountModel::RESETS_MONTHLY | AccountModel::RESETS_ABSOLUTE;
				$srcaccount->resetDate = 1;
				$srcaccount->store();
				
				$source  = db()->table('payment\provider\source')->newRecord();
				$source->provider = get_class($provider);
				$source->account  = $srcaccount;
				$source->store();
			}
			
			$srcbook = $srcaccount->getBook($book->currency)? : $srcaccount->addBook($book->currency);
			
			$transfer = db()->table('transfer')->newRecord();
			$transfer->source = $srcbook;
			$transfer->target = $book;
			$transfer->amount = $amt;
			$transfer->received = $amt;
			$transfer->description = get_class($provider);
			$transfer->created  = time();
			$transfer->executed = time();
			$transfer->store();
			$transfer->notify();
		}
		
		$this->response->setBody('Redirecting...')->getHeaders()->redirect($job->returnto);
		return;
		
	}
	
}