<?php

use chad\exceptions\NoAccountAuthorizedException;
use chad\exceptions\TxnRequiresAuthException;
use chad\validation\BookIdValidationRule;
use rights\AccountLock;
use spitfire\exceptions\HTTPMethodException;
use spitfire\exceptions\PrivateException;
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
		 * Create the transfer at start-up. This transfer will not be stored until
		 * the system is sure that the data provided to it is legit. Meanwhile this
		 * is a placeholder.
		 */
		$transfer = db()->table('transfer')->newRecord();
		
		$transfer->created     = time();
		$transfer->executed    = null;
		
		/*
		 * This is a minor fix for the user interface that the system requires.
		 * Since the user provides the currency for the transfer from a select box,
		 * the system needs to append it to the target
		 */
		if (isset($_POST['currency'])) {
			$_POST['tgt'].= ':' . $_POST['currency'];
		}
		
		/*
		 * When a user is represented by an application, then we perform a series
		 * of additional checks to prevent application from abusing a token to 
		 * impersonate a user.
		 */
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
				$this->view->set('status', 'denied');
				$this->view->set('redirect', $auth->getRedirect($this->authapp, ['transfer.create.user'], $_POST['retryurl']?? null));
				return;
			}
		}
		
		try {
			
			if (!$this->request->isPost()) { throw new HTTPMethodException('Not posted', 1711301043); }
			
			/*
			 * I moved this block back into the POST section of the script. PHP seems
			 * to hate the idea that I'm managing data inside the try and the catch block.
			 */
			$posted = [
				'tgt' => validate($_POST['tgt']?? null)->addRule(new BookIdValidationRule(db())),
				'src' => validate($_POST['src']?? null)->addRule(new BookIdValidationRule(db(), true)),
				'amt' => validate($_POST['amt']?? null)->minLength(1, 'Amount is mandatory'),
				'description' => validate($_POST['description']?? null)->minLength(1, 'Description is mandatory'),
				#Tags are only accepted for applications, so they only can be set by apps
				'tags' => validate($_POST['tags']?? null)->maxLength($this->authapp? 255 : 0, 'Tags too long')
			];

			validate($posted);
			
			$transfer->target      = BookModel::getById($posted['tgt']->getValue());
			$transfer->description = $posted['description']->getValue();
			$transfer->tags        = $posted['tags']->getValue();
			
			
			/*
			 * Some applications, and specially users. Will prefer to use organic 
			 * amounts for their payments.
			 * 
			 * Humans tend to enter the amounts as floats. Just like the currency
			 * is usually presented to them. To avoid any issues, the system will 
			 * automatically correct the amount.
			 */
			$transfer->received = $posted['amt']->getValue() * (isset($_POST['decimals'])? pow(10, $transfer->target->currency->decimals) : 1);
			
			/*
			 * Check if there is a source defined.
			 */
			if ($posted['src']->getValue()) { 
				/*
				 * When a user reaches this endpoint they will already have had to 
				 * select an account to bill. In this case the system will check whether
				 * the user is allowed to perform the operation and check that the 
				 * intent is legitimate.
				 */
				$transfer->source = BookModel::getById($posted['src']->getValue());
				$transfer->amount = $transfer->source->currency->convert($transfer->received, $transfer->source->currency);
			}

			
			$transfer->store();
			
			$this->view->set('txnid', $transfer->_id);
			$this->view->set('transfer', $transfer);
			return;
			
		}
		catch (spitfire\validation\ValidationException$e) {
			$messages = $e->getResult();
			foreach ($messages as $message) { echo $message; }
			die();
		}
		catch (TxnRequiresAuthException$ex) {
			
			/*
			 * The transfer can be created, although we cannot give it a source, 
			 * and therefore the application needs to have it authorized first.
			 */
			$transfer->store();
			
			$this->view->set('txnid', $transfer->_id);
			$this->view->set('transfer', $transfer);
			$this->view->set('redirect', strval(url('transfer', 'authorize', $transfer->_id, ['returnto' => $_GET['returnto']])->absolute()));
		}
		catch (HTTPMethodException $ex) {
			//Ignore this, just show the appropriate template 
		}
		
		/*
		 * If there is a user defined, then we extract the accounts the user has
		 * access to and then provide them to the user.
		 */
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
	public function authorize(TransferModel$transfer, $sig = null) {
		$this->view->set('source', null);
		
		try {
			if ($this->user && !$this->authapp) {

				$xsrf = new \spitfire\io\XSSToken();
				$this->view->set('sig', $xsrf->getValue());

				/*
				 * Verify that the token has not been tampered with, and that the request
				 * is actually sent from the user and not via XSS
				 */
				if ($sig && !$xsrf->verify($sig)) {
					throw new PublicException('Malformed XSRF token. Please retry', 403);
				}
				
				/*
				 * If the user is not posting the data or the XSS token has not been
				 * sent, we disregard the data sent
				 */
				if (!$this->request->isPost() || !$sig) { 
					throw new HTTPMethodException('Not POSTed', 1712050927); 
				}
				
				if (!$transfer->source) {
					$transfer->source = BookModel::getById($_POST['source']);
				}

				if (!$transfer->source || $transfer->source->deleted) { 
					throw new PublicException('Invalid source defined', 400); 
				}

				/*
				 * Check the permissions on the account. Whether the user can write.
				 */
				$lock     = new AccountLock($transfer->source->account);
				
				if (!$lock->unlock($this->user->user->id, null)) {
					throw new NoAccountAuthorizedException('Not authorized on the given account');
				}
				
				$transfer->amount = $transfer->source->currency->convert($transfer->received, $transfer->target->currency);
				$transfer->store();

				/*
				 * If the account has no balance to support the transaction, we need to 
				 * redirect the user to the appropriate page to add funds to the account
				 */
				if ($transfer->source->balance() < $transfer->amount) {
					$this->response->setBody('Redirecting...')->getHeaders()->redirect(url('funds', 'add', $transfer->source->account->_id, $transfer->source->currency->ISO, $transfer->amount, ['returnto' => strval(url('transfer', 'authorize', $transfer->_id, ['returnto' => _def($_GET['returnto'], '')]))]));
					return;
				}

				/*
				 * Once the transfer has been authorized, the system records the 
				 * authorization.
				 */
				$transfer->authorized = time();
				$transfer->store();
				
				if (isset($_GET['returnto']) && filter_var($_GET['returnto'], FILTER_VALIDATE_URL)) {
					$this->response->setBody('Redirecting...')->getHeaders()->redirect($_GET['returnto']);
					return;
				}
				else {
					$this->response->setBody('Redirecting...')->getHeaders()->redirect(url('account'));
					return;
				}

			}

			elseif ($this->authapp && !$this->user) {
				
				/*
				 * We need the user to provide a source account first.
				 */
				if (!$transfer->source) {
					throw new NoAccountAuthorizedException('No source selected');
				}

				$lock     = new AccountLock($transfer->source->account);

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
				
				if (!$transfer->authorized && !$lock->unlock(null, $this->authapp)) {
					throw new NoAccountAuthorizedException('Not authorized on the given account');
				}

				$transfer->authorized = time();
				$transfer->store();
			}
			else {
				$rtt = strval(url('transfer', 'authorize', $transfer->_id, ['returnto' => $_GET['returnto']])->absolute());
				return $this->response->setBody('Redirecting...')->getHeaders()->redirect(url('transfer', 'guest', $transfer->_id, ['returnto' => $_GET['returnto']]));
				//return $this->response->setBody('Redirecting...')->getHeaders()->redirect(url('user', 'login', ['returnto' => $rtt]));
			}

		} 
		catch (HTTPMethodException$ex) {/*Do nothing*/}
		
		catch (TxnRequiresAuthException$ex) {
			$this->view->set('redirect', strval(url('transfer', 'authorize', $transfer->_id, ['returnto' => $_GET['returnto']])->absolute()));
		}
		
		
		if ($this->user && $transfer->source) {

			$granted = db()->table('rights\user')->get('user', db()->table('user')
				->get('_id', $this->user->user->id))
				->addRestriction('write', true)
				->addRestriction('account', $transfer->source->account)
				->fetch();

			if (!$granted) { throw new PublicException('You have no access on this account', 403); }

			$this->view->set('source', $transfer->source);
		}
		elseif($this->user && !$this->authapp) {

			$granted = db()->table('rights\user')->get('user', db()->table('user')
				->get('_id', $this->user->user->id))
				->addRestriction('write', true);

			$accounts = db()->table('account')->get('ugrants', $granted)->fetchAll();
			
			if ($accounts->isEmpty()) {
				$this->response->setBody('Redirect...')->getHeaders()->redirect(url('account', 'create', ['returnto' => strval(spitfire\core\http\URL::current())]));
			}

			$this->view->set('accounts', $accounts);
		}
		
		$this->view->set('transfer', $transfer);
		$this->view->set('recipient', $this->sso->getUser($transfer->target->account->owner->_id));
	}
	
	public function guest(TransferModel$transfer) {
		$this->view->set('recipient', $this->sso->getUser($transfer->target->account->owner->_id));
		$this->view->set('transfer',  $transfer);
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
		
		/**
		 * Prevents double execution of the transfer.
		 */
		if ($transfer->executed) {
			throw new PublicException('Already executed', 400);
		}
		
		if ($transfer->source && $transfer->target && !$transfer->cancelled && $transfer->authorized) {
			$transfer->executed = time();
			$transfer->store();
			
			$this->view->set('transfer', $transfer);
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
	
	/**
	 * Creates a transaction that effectively reverts the previous transaction.
	 * 
	 * @param TransferModel $txn
	 */
	public function refund(TransferModel $txn) 
	{
		
		if ($this->user && !$this->authapp) {
			/*
				* Check the permissions on the account. Whether the user can write.
				*/
			$lock = new AccountLock($txn->target->account);
			
			if (!$lock->unlock($this->user->user->id, null)) {
				throw new NoAccountAuthorizedException('Not authorized on the given account');
			}

		}

		elseif ($this->authapp && !$this->user) {

			$lock = new AccountLock($txn->target->account);

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
			
			if (!$lock->unlock(null, $this->authapp)) {
				throw new NoAccountAuthorizedException('Not authorized on the given account');
			}
		}
		else {
			throw new PublicException('Unauthorized', 403);
		}
		
		try {
			if (!$this->request->isPost()) { throw new HTTPMethodException('Not posted'); }
			
			#TODO: Let the user make a partial refund
			#TODO: Introduce relationship table so a payment can be connected as part of a relationship
			
			/*
			 * If the account has no balance to support the transaction, we need to 
			 * fail the refund and let user react to this issue appropriately.
			 */
			if ($txn->target->balance() < $txn->amount) {
				throw new PublicException('Not enough funds for a refund', 400);
			}
			
			$refund = db()->table('transfer')->newRecord();
			$refund->source = $txn->target;
			$refund->target = $txn->source;
			$refund->amount = $txn->received;
			$refund->received = $txn->amount;
			$refund->description = sprintf('Refund of #%s', $txn->_id);
			$refund->created = time();
			$refund->authorized = time();
			$refund->executed = time();
			$refund->cancelled = time();
			$refund->store();
			
			/*
			 * Check if the account the refund is sent to is a payment-provider's source.
			 * 
			 * If that's the case, the software must look up the provider and invoke it's
			 * refund method so the external refund can be initiated.
			 */
			$source = db()->table('payment\provider\source')->get('account', $refund->target)->first();
			$providers = \payment\ProviderPool::payment()->configure();
			
			if ($source) {
				#TODO: This could be a lot nicer written than it is right now
				$provider = $providers->filter(function ($e) use ($source) { return $source->provider === get_class($e); })->rewind();
				$external = db()->table('payment\provider\externalfunds')->get('txn', $txn)->first();
				
				$provider->refund($external->_id, $refund->amount);
			}
			
			$this->response->setBody("Redirect")->getHeaders()->redirect(url('account', 'balance', $refund->source->_id));
			return;
		} 
		catch (HTTPMethodException $ex) {
			//Continue to the form
		}
		
	}
}