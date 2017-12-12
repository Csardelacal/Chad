<?php

use chad\exceptions\InsufficientFundsException;
use chad\exceptions\NoAccountAuthorizedException;
use chad\exceptions\TxnRequiresAuthException;
use spitfire\exceptions\HTTPMethodException;
use spitfire\exceptions\PrivateException;
use spitfire\exceptions\PublicException;
use spitfire\validation\ValidationError;
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

class TransferController extends BaseController
{
	
	/**
	 * Creates a transaction with the posted settings. Once the transaction has
	 * been created, the application should later try to authorize it (if it wasn't)
	 * or execute it.
	 * 
	 * The create() endpoint will immediately report if the transaction was 
	 * authorized or whether it needs authorization. To do so, the application 
	 * can either provide a known account ID it wishes to bill or a user id.
	 * 
	 * If the application provides no account or user, the application will receive
	 * a link to authorize the payment which will prompt the user to pick a source
	 * for the payment.
	 */
	public function create() {
		
		/*
		 * This is a minor fix for the user interface that the system requires.
		 * Since the user provides the currency for the transfer from a select box,
		 * the system needs to append it to the target
		 */
		if (isset($_POST['currency'])) {
			$_POST['tgt'].= ':' . $_POST['currency'];
		}
		
		if ($this->authapp) {
			$auth = $this->sso->authApp($_GET['signature'], $this->token, ['transfer.create', 'transfer.create.user']);
			
			if (!$auth->getAuthenticated()) { throw new PublicException('Invalid application', 403); }
			
			/*
			 * First we check whether the contexts exist for this application. On
			 * one hand Chad can check whether the application has been banned from
			 * accessing accounts, on the other whether the user has provided access.
			 */
			if (!$auth->getContext('transfer.create')->exists()) { 
				$auth->getContext('transfer.create')->create('Transfer creation', 'Allows the application to create transfers for accounts it manages'); 
			}
			
			if (!$auth->getContext('transfer.create.user')->exists()) { 
				$auth->getContext('transfer.create.user')->create('Transfer creation', 'Allows the application to create transfers from your accounts'); 
			}
			
			/*
			 * Chad is generally lenient with applications that wish to access accounts
			 * they manage, but if the application was banned on PHPAS' side, then
			 * we will no longer service the app.
			 */
			if ($auth->getContext('transfer.create')->isGranted() == 1) {
				throw new PublicException('Application has been banned from creating any transactions', 403);
			}
			
			/*
			 * These should only be tested if the application has a token provided.
			 * Otherwise it's managing accounts that it has been granted access to.
			 */
			if ($this->user && !$auth->getContext('transfer.create.user')->isGranted() == 2) { 
				//The user still needs to authorize this application to access their accounts at all
				//This is separate from the authorization of individual transactions
			}
		}
		
		/*
		 * Sequentially, having this block outside the POST only section feels...
		 * off. But due to the fact that this variables are used in the catch
		 * sections of the code, it feels more natural to put it outside the try
		 */
		$posted = [
			'tgt' => validate($_POST['tgt']?? null)->minLength(20, 'Target must be a valid account ID'),
			'amt' => validate($_POST['amt']?? null)->minLength(1, 'Amount is mandatory'),
			'description' => validate($_POST['description']?? null)->minLength(1, 'Description is mandatory'),
			#Tags are only accepted for applications, so they only can be set by apps
			'tags' => validate($_POST['tags']?? null)->minLength($this->authapp? 255 : 0, 'Tags too long')
		];
		
		list($account, $currency) = explode(':', $_POST['tgt']);
		$tgt = db()->table('book')
			->get('account', db()->table('account')->get('_id', $account))
			->addRestriction('currency', db()->table('currency')->get('ISO', $currency))
			->fetch();
		
		$amt = $posted['amt']->getValue();
		$description = $posted['description']->getValue();
		$tags = $posted['tags'];
		
		try {
			
			if (!$this->request->isPost()) { throw new HTTPMethodException('Not posted', 1711301043); }
			
			if (!$tgt) { throw new PublicException('No target defined', 400);}
			
			/*
			 * The target account must always be unequivocally defined. It is not 
			 * acceptable to provide a username or anything alike.
			 */
			
			validate($posted);
			
			
			/*
			 * At this point Chad needs to determine whether the user has enough 
			 * balance on their account to grant the payment and whether they have
			 * assigned an account that the application can automatically bill.
			 * 
			 * If the user has granted permissions on one of his accounts to the 
			 * application, it will be able to access them. Then the create() method
			 * will be preauthorized and instruct the application to directly call
			 * execute()
			 */
			if ($this->user && $this->authapp) {
				/*
				 * This is a rather tricky query, since it requires the system to 
				 * find an account the user can write to, and that the application
				 * can write to.
				 * 
				 * Chad won't otherwise authorize the transaction at all.
				 */
				$userg = db()->table('rights\user')->get('user', db()->table('user')->get('_id', $this->user->user->id))->addRestriction('write', true);
				$query = db()->table('account')->get('ugrants', $userg);
				$grant = db()->table('rights\app')->get('app', $this->authapp)->addRestriction('account', $query)->addRestriction('write', true)->fetchAll();
				
				if ($grant->isEmpty()) {
					throw new NoAccountAuthorizedException('The user has no accounts that the app is authorized to bill');
				}
				
				/*
				 * Loop through the grants and find if the user has enough currency
				 */
				foreach ($grant as $g) {
					$books = $g->account->getBook($currency);
					if ($book && $tgt->convert($book->balance(), $currency) > $amt) { $billable = $book; }
				}
				
				/*
				 * If a book has been authorized and is already billable, then we can
				 * report this back to the application and it can automatically
				 * execute the transaction.
				 */
				if ($billable) {
					$src = $billable;
				}
				else {
					throw new InsufficientFundsException('Not enough funds on any authorized book', 1711301054);
				}
			}
			elseif ($this->user) {
				
				if (!isset($_POST['src'])) { 
					throw new ValidationException('Missing source account', 1712011720, [new ValidationError('No source account defined')]); 
				}
				
				/*
				 * When a user reaches this endpoint they will already have had to 
				 * select an account to bill. In this case the system will check whether
				 * the user is allowed to perform the operation and check that the 
				 * intent is legitimate.
				 */
				list($srcaccount, $srccurrency) = explode(':', $_POST['src']);
				$query = db()->table('account')->get('_id', $srcaccount);
				
				$userg = db()->table('rights\user')->get('user', db()->table('user')
					->get('_id', $this->user->user->id))
					->addRestriction('write', true)
					->addRestriction('account', $query)
					->fetch();
				
				if (!$userg) {
					throw new PublicException('Not authorized', 403);
				}
				
				//TODO: Generate and verify the signature
				//TODO: Check if the token was authorized by and for Chad alone
				
				$src = $query->fetch()->getBook($srccurrency);
				
				/*
				 * Humans tend to enter the amounts as floats. Just like the currency
				 * is usually presented to them. To avoid any issues, the system will 
				 * automatically correct the amount.
				 */
				$amt = $amt * pow(10, $tgt->currency->decimals);
				
			}
			elseif ($this->authapp) {
				
				/*
				 * When billing an account without a user, the application needs to
				 * properly provide an account ID. Using the system without a unique
				 * account ID does not work in this case.
				 */
				if (!isset($_POST['src'])) { 
					throw new ValidationException('Missing source account', 1712011720, [new ValidationError('No source account defined')]); 
				}
				
				list($srcaccount, $srccurrency) = explode(':', $_POST['src']);
				$query = db()->table('account')->get('_id', $srcaccount);
				$grant = db()->table('rights\app')->get('app', $this->authapp)->addRestriction('account', $query)->addRestriction('write', true)->fetch();
				
				if (!$grant) {
					throw new PublicException('Account unauthorized', 403);
				}
				
				$src = $query->fetch();
			}
			else {
				throw new PublicException('This endpoint requires authentication', 403);
			}
			
			$transfer = db()->table('transfer')->newRecord();
			$transfer->source      = $src;
			$transfer->target      = $tgt;
			$transfer->amount      = $src->currency->convert($amt, $tgt->currency);
			$transfer->received    = $amt;
			$transfer->description = $description;
			$transfer->tags        = $tags;
			$transfer->created     = time();
			$transfer->due         = null;
			$transfer->executed    = null;
			$transfer->store();
			
			$this->view->set('txnid', $transfer->_id);
			$this->view->set('transfer', $transfer);
			return;
			
		}
		catch (TxnRequiresAuthException$ex) {
			$transfer = db()->table('transfer')->newRecord();
			$transfer->source      = null;
			$transfer->target      = $tgt;
			$transfer->amount      = null;
			$transfer->received    = $amt;
			$transfer->description = $description;
			$transfer->tags        = $tags;
			$transfer->created     = time();
			$transfer->due         = null;
			$transfer->executed    = null;
			$transfer->store();
			
			$this->view->set('txnid', $transfer->_id);
			$this->view->set('transfer', $transfer);
			$this->view->set('redirect', strval(url('transfer', 'authorize', $transfer->_id, ['returnto' => _def($_GET['returnto'], '')])->absolute()));
		}
		catch (HTTPMethodException $ex) {
			//Ignore this, just show the appropriate template 
		}
		
		if ($this->user && !$this->authapp) {

			$userg = db()->table('rights\user')
				->get('user', db()->table('user')->get('_id', $this->user->user->id))
				->addRestriction('write', true)
				->addRestriction('listed', true)
				->fetchAll();
			
			$this->view->set('sources', $userg);
		}
	}
	
	/**
	 * If the user has not yet authorized a payment, the application will report
	 * this here. If the payment was previously authorized, by the fact that the 
	 * did pre-authorize the application to execute changes to their account, this
	 * endpoint will always return true.
	 * 
	 * The authorization requires that the user has the right to have r/w access
	 * to the source account.
	 * 
	 * @param string $txn
	 */
	public function authorize($txn, $sig = null) {
		
		$transfer = db()->table('transfer')->get('_id', $txn)->fetch();
		
		if ($sig) {
			list($rand, $hash) = explode(':', $sig);
			if (hash('sha512', implode(':', [$txn, $rand, $this->token->getId()])) !== $hash) { throw new PrivateException('Invalid signature'); } 
			$sigcheck = true;
		}
		else {
			$rand = str_replace(['+', '/', '='], '', base64_encode(random_bytes(20)));
			$sig  = implode(':', [$rand, hash('sha512', implode(':', [$txn, $rand, $this->token->getId()]))]);
			$this->view->set('sig', $sig);
		}
		
		//TODO: Check if the token is local or provided via GET
		if (!$transfer) { throw new PublicException('No transaction found', 404); }
		
		try {
			if (!$this->request->isPost()) { throw new HTTPMethodException('Not POSTed', 1712050927); }
			
			$source = $transfer->source? $transfer->source : _def($_POST['source'], null);
			
			if (!$source) { throw new PublicException('Invalid source defined'); }
			
			if (is_string($source)) { 
				list($account, $currency) = explode(':', $source);
				$source = db()->table('book')
					->get('account', db()->table('account')->get('_id', $account))
					->addRestriction('currency', db()->table('currency')->get('ISO', $currency))
					->fetch(); 
			}
			
			/*
			 * Check the permissions on the account. Whether the user can write.
			 */
			$granted = db()->table('rights\user')->get('user', db()->table('user')
				->get('_id', $this->user->user->id))
				->addRestriction('write', true)
				->addRestriction('account', $source->account)
				->fetch();
			
			if (!$granted) { throw new PublicException('You have no access on this account', 403); }
			if (!isset($sigcheck) || !$sigcheck) { throw new PrivateException('Failed signature'); }
			
			$transfer->source = $source;
			$transfer->amount = $source->currency->convert($transfer->received, $transfer->target->currency);
			$transfer->store();
			
			/*
			 * If the account has no balance to support the transaction, we need to 
			 * redirect the user to the appropriate page to add funds to the account
			 */
			if ($source->balance() < $transfer->amount) {
				$this->response->setBody('Redirecting...')->getHeaders()->redirect(url('funds', 'add', $source->account->_id, $source->currency->ISO, $transfer->amount, ['returnto' => strval(url('transfer', 'authorize', $transfer->_id, ['returnto' => _def($_GET['returnto'], '')]))]));
				return;
			}
			
			if (isset($_GET['returnto']) && filter_var($_GET['returnto'], FILTER_VALIDATE_URL)) {
				$this->response->setBody('Redirecting...')->getHeaders()->redirect($_GET['returnto']);
				return;
			}
			else {
				$this->response->setBody('Redirecting...')->getHeaders()->redirect(url('account'));
				return;
			}
			
		} 
		catch (HTTPMethodException$ex) {/*Do nothing*/}
		
		if ($transfer->source) {
			
			$granted = db()->table('rights\user')->get('user', db()->table('user')
				->get('_id', $this->user->user->id))
				->addRestriction('write', true)
				->addRestriction('account', $transfer->source->account)
				->fetch();
			
			if (!$granted) { throw new PublicException('You have no access on this account', 403); }
			
			$this->view->set('source', $transfer->source);
		}
		else {
			
			$granted = db()->table('rights\user')->get('user', db()->table('user')
				->get('_id', $this->user->user->id))
				->addRestriction('write', true);
			
			$accounts = db()->table('account')->get('ugrants', $granted)->fetchAll();
			
			$this->view->set('source', $accounts);
		}
		
		$this->view->set('transfer', $transfer);
		$this->view->set('recipient', $this->sso->getUser($transfer->target->account->owner->_id));
	}
	
	/**
	 * This endpoint allows the remote application to attempt to execute the 
	 * transaction. If the transaction was not authorized or the account not 
	 * properly funded, it will fail - allowing the remote application to
	 * respond appropriately.
	 * 
	 * @param string $txn
	 */
	public function execute($txn) {
		
		$transfer = db()->table('transfer')->get('_id', $txn)->fetch();
		
		if ($transfer->source && $transfer->target && !$transfer->cancelled) {
			$transfer->executed = time();
			$transfer->store();
			$transfer->notify();
		}
	}
	
	/**
	 * Allows an application / user to cancel a transaction that was not meant to
	 * be executed.
	 * 
	 * @param string $txn
	 */
	public function cancel($txn) {
		
		$transfer = db()->table('transfer')->get('_id', $txn)->fetch();
		
		if ($transfer->executed) {
			throw new PublicException('Transaction is completed. Cannot be cancelled', 403);
		}
		
		$transfer->cancelled = time();
		$this->view->set('transfer', $transfer);
	}
	
}