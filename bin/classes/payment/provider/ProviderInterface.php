<?php namespace payment\provider;

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

/**
 * The provider interface allows to create and integrate external payment providers
 * into Chad and to quickly implement new sources for the application to allow
 * users to pay for goods provided by the software.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
interface ProviderInterface
{
	
	/*
	 * These constants allow the application to determine whether a payment has
	 * been authorized or not.
	 */
	const AUTH_REJECTED   = -1;
	const AUTH_PENDING    = 0;
	const AUTH_AUTHORIZED = 1;
	
	/**
	 * The set up method will be automatically called when the user activates a 
	 * payment provider which has not yet been properly configured.
	 */
	function setUp();
	
	/**
	 * When the application needs to use this payment provider it will call this
	 * method providing the user configurable data for this payment provider.
	 * 
	 * @param \payment\provider\Configuration $config
	 */
	function init(Configuration$config);
	
	/**
	 * This endpoint creates a payment. The software will keep track of the transfer
	 * to / from the account of the user and will only require the provider to 
	 * create an appropriate transfer from the external source to Chad's managed 
	 * account.
	 * 
	 * @param PaymentAuthorization $context
	 * @return int|Redirection|Form|PaymentAuthorization The id for the payment created
	 */
	function authorize(PaymentAuthorization$context);
	
	/**
	 * Executes a payment. For this to be executable, it needs to have been
	 * previously authorized by authorize()
	 * 
	 * @param PaymentAuthorization $auth
	 * @param string $id
	 * @param string $amt
	 * @return string Payment ID
	 */
	function execute(PaymentAuthorization$auth, $id, $amt);
	
	/**
	 * Cancels a payment. Receives an ID provided by execute().
	 * 
	 * @param string $id
	 */
	function cancel($id);
	
	/**
	 * When a payment gets deferred (because, for example, a payment provider
	 * needs a few minutes to process a credit card) the application will call
	 * this method once it receives a return from the server.
	 * 
	 * @param int $id
	 * @param \payment\provider\PaymentAuthorization $context
	 */
	function listen($id, PaymentAuthorization$context);
	
	/**
	 * Retrieves information about a payment. Receives an ID provided by execute()
	 * 
	 * @param int $id
	 */
	function getStatus($id);
	
}