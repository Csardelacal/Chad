<?php namespace payment\provider;

use payment\ConfigurationInterface;
use payment\Context;
use spitfire\core\router\Redirection;
use UI\Controls\Form;

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
	 * When the application needs to use this payment provider it will call this
	 * method providing the user configurable data for this payment provider.
	 * 
	 * @param ConfigurationInterface $config
	 */
	function init(ConfigurationInterface$config);
	
	/**
	 * This endpoint creates a payment. The software will keep track of the transfer
	 * to / from the account of the user and will only require the provider to 
	 * create an appropriate transfer from the external source to Chad's managed 
	 * account.
	 * 
	 * @param Context $context
	 * @return int|Redirection|Form|PaymentAuthorization The id for the payment created
	 */
	function authorize(Context$context);
	
	/**
	 * When a payment gets deferred (because, for example, a payment provider
	 * needs a few minutes to process a credit card) the application will call
	 * this method once it receives a return from the server.
	 * 
	 * This may also provide a null value for ID in the event of as webhook, 
	 * some payment providers (in my case GoCardless) will not allow the application
	 * to define a callback URL but instead will send out a webhook regularly to 
	 * notify the application of any changes.
	 * 
	 * @param int $id
	 * @param Context $context
	 */
	function listen($id, Context$context);
	
	/**
	 * When a payment gets deferred this method gets called regularly to supervise
	 * the payment, in case listen() is not called or is not intended to be called.
	 * 
	 * @param int $id
	 */
	function await($id);
	
	/**
	 * If a refund reaches a payment provider, the system will invoke this method.
	 * This allows to perform a refund of the payment with the given id.
	 * 
	 * For this, the payment provider has several options:
	 * 
	 * 1. Use API calls to refund the payment as a refund of the original payment
	 *    this is vendor specific behavior, but it's usually the most cost effective
	 *    and the safest (payment protection on Paypal)
	 * 
	 * 2. Generate a payout through a known channel. This can be something like a
	 *    bank transfer that cannot be refunded but can be sent out through a payout
	 *    provider.
	 * 
	 * 3. Issue a request for a manual action. This will require an operator to
	 *    log into the system and review the refund before then manually sending
	 *    it and closing it.
	 * 
	 * @param int $id
	 * @param int $amt The amount of currency to send back (please note that all refunds
	 * are processed in the same currency the original payment was sent)
	 */
	function refund($id, $amt);
	
	/**
	 * 
	 * @return ConfigurationInterface
	 */
	function makeConfiguration();
	
	/**
	 * 
	 * @return string The name of the provider
	 */
	function getName();
	
	/**
	 * 
	 * @param int $size
	 * @return string The location of the logo file
	 */
	function getLogo();
	
}