<?php

use payment\Context;
use payment\flow\PaymentInterface;
use payment\flow\Redirection;
use payment\provider\ExternalfundsModel;
use payment\provider\ProviderInterface;
use payment\ProviderPool;
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
		$providers = ProviderPool::payment()->configure(); // Prepares the providers by loading their configuration
		$currency  = $currencyISO? db()->table('currency')->get('ISO', _def($_POST['currency'], $currencyISO))->fetch() : db()->table('currency')->get('default', true)->fetch();
		$account   = db()->table('account')->get('_id', _def($_POST['account'], $acctid))->fetch();
		
		$amt = _def($_POST['amt'], $amtParam);

		if (isset($_POST['decimals']) && $_POST['decimals'] === 'natural') {
			$amt = $amt * pow(10, $currency->decimals);
		}
		
		try {
			/*
			 * First thing we need to do is check whether the request was posted,
			 * if this is not the case, we need to show the user the source select 
			 * screen.
			 */
			if (!$this->request->isPost()) { throw new HTTPMethodException('Not POSTed', 1712051108); }
			
			
			
			/* @var $provider ProviderInterface */
			$provider = $providers->filter(function ($e) {
				return !!(_def($_POST['provider'], null) === get_class($e));
			})->rewind();
			
			$granted  = db()->table('rights\user')->get('user', db()->table('user')->get('_id', $this->user->user->id))->where('account', $account)->first();

			if (!$granted) {
				throw new PublicException('Not permitted', 403);
			}
			
			if ($amt < 0)   { throw new ValidationException('Invalid amount', 1712051113); }
			if (!$amt)      { throw new PublicException('No amount provided', 400); }
			if (!$currency) { throw new PublicException('No currency found', 404); }
			if (!$provider) { throw new PublicException('No provider found', 404); }
			
			$record = db()->table('payment\provider\externalfunds')->newRecord();
			$record->type     = ExternalfundsModel::TYPE_PAYMENT;
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
		
		$this->view->set('amt', $amt);
		$this->view->set('account', $account);
		$this->view->set('currency', $currency);
		$this->view->set('providers', $providers);
	}
	
	public function retrieve($acctid = null, $currencyISO = null, $amtParam = null) {
		
		
		/*
		 * Prepare the provider list
		 */
		$providers = ProviderPool::payouts()->configure(); // Prepares the providers by loading their configuration
		$currency  = $currencyISO? db()->table('currency')->get('ISO', _def($_POST['currency'], $currencyISO))->fetch() : db()->table('currency')->get('default', true)->fetch();
		$account   = db()->table('account')->get('_id', _def($_POST['account'], $acctid))->fetch();
		
		try {
			/*
			 * First thing we need to do is check whether the request was posted,
			 * if this is not the case, we need to show the user the source select 
			 * screen.
			 */
			if (!$this->request->isPost()) { throw new HTTPMethodException('Not POSTed', 1712051108); }
			
			$amt      = _def($_POST['amt'], $amtParam);
			
			if (isset($_POST['decimals']) && $_POST['decimals'] === 'natural') {
				$amt = $amt * pow(10, $currency->decimals);
			}
			
			
			/* @var $provider ProviderInterface */
			$provider = $providers->filter(function ($e) {
				return !!(_def($_POST['provider'], null) === get_class($e));
			})->rewind();
			
			$granted  = db()->table('rights\user')->get('user', db()->table('user')->get('_id', $this->user->user->id))->where('account', $account)->first();

			if (!$granted) {
				throw new PublicException('Not permitted', 403);
			}
			
			if ($amt < 0)   { throw new ValidationException('Invalid amount', 1712051113); }
			if (!$amt)      { throw new PublicException('No amount provided', 400); }
			if (!$currency) { throw new PublicException('No currency found', 404); }
			if (!$provider) { throw new PublicException('No provider found', 404); }
			
			$record = db()->table('payment\provider\externalfunds')->newRecord();
			$record->type     = ExternalfundsModel::TYPE_PAYOUT;
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
		
		$this->view->set('amt', $amt);
		$this->view->set('account', $account);
		$this->view->set('currency', $currency);
		$this->view->set('providers', $providers);
	}
	
	public function execute($fid) {
		
		$job       = db()->table('payment\provider\externalfunds')->get('_id', $fid)->fetch();
		$account   = $job->account;
		$amt       = $job->amt;
		$currency  = $job->currency;
		
		/*
		 * Prepare the provider list
		 */
		$providers = $job->type == ExternalfundsModel::TYPE_PAYMENT? ProviderPool::payment()->configure() : ProviderPool::payouts()->configure();  // Prepares the providers by loading their configuration
		
		/*
		 * Get the appropriate book to add the funds to
		 */
		try                  { $book = $account->getBook($currency); }
		catch (\Exception$e) { $book = $account->addBook($currency); }
		
		
		$granted  = db()->table('rights\user')->get('user', db()->table('user')->get('_id', $this->user->user->id))->where('account', $job->account)->first();
		
		if (!$granted) {
			throw new PublicException('Not permitted', 403);
		}
			
		/* @var $provider ProviderInterface */
		$provider = $providers->filter(function ($e) use ($job) {
			return !!($job->source === get_class($e));
		})->rewind();
		
		/*
		 * Create the context to manage the payment authorization.
		 */
		$context = new Context();
		$context->setId($fid);
		$context->setAmt($amt);
		$context->setCurrency($currency);
		$context->setSuccessURL(url('funds', 'execute', $fid)->absolute());
		$context->setFailureURL(url('funds', 'failed', $fid)->absolute());
		$context->setFormData($_REQUEST);
		
			
		/*
		 * Once the amount has been charged, the application must proceed to 
		 * record the transaction.
		 */
		$source   = db()->table('payment\provider\source')->get('provider', get_class($provider))->fetch();
		
		if ($source) {
			$srcaccount = $source->account;
		}
		else {
			$srcaccount = db()->table('account')->newRecord();
			$srcaccount->name      = get_class($provider);
			$srcaccount->owner     = null;
			$srcaccount->taxID     = null;
			$srcaccount->store();

			$source  = db()->table('payment\provider\source')->newRecord();
			$source->provider = get_class($provider);
			$source->account  = $srcaccount;
			$source->store();
		}
		
		/*
		 * Try and authorize the payment, pulling funds from the external source
		 */
		$flow = $provider->authorize($context);
		
		/*
		 * If the payment requires further authorization, we redirect the user to 
		 * the url the payment provider directed us.
		 */
		if ($flow instanceof Redirection) {
			$this->response->setBody('Redirecting...')->getHeaders()->redirect($flow->getTarget());
			return;
		}
		
		/*
		 * The payment provider requires the user to add information for the payment
		 * to succeed.
		 */
		if ($flow instanceof \payment\flow\Form) {
			$this->view->set('form', $flow);
			return;
		}
		
		/*
		 * The payment provider cannot yet confirm or deny whether the payment has
		 * been successful. The application is therefore required to wait until the
		 * payment has been cleared.
		 */
		if ($flow instanceof \payment\flow\Defer) {
			/*
			 * When the charge is deferred, we need to record the fact that it has 
			 * been deferred and record that it has been approved. This way it will
			 * show up to the administrator(s) to manually approve if the payment 
			 * provider doesn't automatically.
			 */
			$job->approved = time();
			$job->deferred = time();
			$job->additional = $flow->getAdditional();
			$job->store();
			
			$this->view->set('defer', $flow);
			return;
		}
		
		/*
		 * Once the payment provider has authorized the payment, we direct the 
		 * user to the URL that we were indicated by the source app.
		 */
		if ($flow instanceof PaymentInterface && $job->type == ExternalfundsModel::TYPE_PAYMENT) {
			
			/**
			 * Execute the payment - if the payment fails at this point the user 
			 * should be presented with an appropriate error screen.
			 */
			$flow->charge();
			
			/*
			 * 
			 */
			$job->approved = time();
			$job->executed = time();
			$job->store();
			
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
		
		/*
		 * Once the payment provider has authorized the payment, we direct the 
		 * user to the URL that we were indicated by the source app.
		 */
		if ($flow instanceof \payment\flow\PayoutInterface && $job->type == ExternalfundsModel::TYPE_PAYOUT) {
			
			/*
			 * This is the last safeguard before the charge gets executed, if the 
			 * balance is not enough to cover the transaction, we stop the user from
			 * performing it.
			 */
			if ($book->balance() < $amt) {
				throw new PublicException('Not permitted', 403);
			}
			
			/*
			 * Payouts are only recorded, they get executed in batches and after a
			 * bureaucrat has approved the transaction.
			 */
			$flow->write();
			
			/*
			 * Record that the payout has been approved by the payment processor, and
			 * needs to be released by an administrator.
			 */
			$job->approved = time();
			$job->additional = $flow->getAdditional();
			$job->store();
			
			$srcbook = $srcaccount->getBook($book->currency)? : $srcaccount->addBook($book->currency);
			
			$transfer = db()->table('transfer')->newRecord();
			$transfer->source = $book;
			$transfer->target = $srcbook;
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