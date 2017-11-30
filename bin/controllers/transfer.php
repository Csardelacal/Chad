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
		
		try {
			
			if (!$this->request->isPost()) { throw new HTTPMethodException('Not posted', 1711301043); }
			
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
				$grant = db()->table('rights\app')->get('app', $this->authapp)->get('account', $query)->addRestriction('write', true)->fetchAll();
				
				if ($grant->isEmpty()) {
					throw new \chad\exceptions\NoAccountAuthorizedException('The user has no accounts that the app is authorized to bill');
				}
				
				/*
				 * Loop through the grants and find if the user has enough currency
				 */
				foreach ($grant as $g) {
					$book = $g->account->getBook($currency);
					if ($book && $book->balance() > $amt) { $billable = $book; }
				}
				
				/*
				 * If a book has been authorized and is already billable, then we can
				 * report this back to the application and it can automatically
				 * execute the transaction.
				 */
				if ($billable) {
					$this->view->set('book', $billable);
				}
				else {
					throw new \chad\exceptions\InsufficientFundsException('Not enough funds on any authorized book', 1711301054);
				}
			}
			elseif ($this->user) {
				//TODO: Implement
			}
			elseif ($this->authapp) {
				//TODO: Implement
			}
		} 
		catch (HTTPMethodException $ex) {

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
	public function authorize($txn) {
		
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
		
	}
	
	/**
	 * Allows an application / user to cancel a transaction that was not meant to
	 * be executed.
	 * 
	 * @param string $txn
	 */
	public function cancel($txn) {
		
	}
	
}