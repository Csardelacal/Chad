<?php namespace external\payment\providers\paypal;

use Exception;
use payment\provider\ConfigurationInterface;
use payment\provider\PaymentAuthorization;
use payment\provider\ProviderInterface;
use payment\provider\Redirection;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
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

class Paypal implements ProviderInterface
{
	
	/**
	 *
	 * @var PaypalConfiguration
	 */
	private $config;
	
	public function __construct() {
		
		/*
		 * Check if Paypal is enabled in the first place. The system needs the 
		 * appropriate SDK to interact with Paypal's API and if it isn't installed
		 * it will fail.
		 */
		if (!class_exists(Payment::class)) {
			throw new PrivateException('Paypal enabled. Library not found.');
		}
	}
	
	public function authorize(PaymentAuthorization $context) {
		
		if (isset($context->getFormData()['PayerID'])) {
			return true;
		}
		
		$amt = $context->getAmt() / pow(10, $context->getCurrency()->decimals);
		$currency = $context->getCurrency()->ISO;
		
		#The user should be using Paypal to pay
		$payer = new Payer();
		$payer->setPaymentMethod("paypal");
		
		#Create the auction item that we wanna bill for
		$item = new Item();
		$item->setName("Invoice")
		     ->setCurrency($currency)
		     ->setQuantity(1)
		     ->setSku('invoice')
		     ->setPrice($amt);
		
		$itemList = new ItemList();
		$itemList->setItems(Array($item));
		
		#Calculate the amount
		$amount = new Amount();
		$amount->setCurrency($currency)->setTotal($amt);
		
		#Start the transaction
		$transaction = new Transaction();
		$transaction->setAmount($amount)
				  ->setItemList($itemList)
				  ->setDescription('Chad Payment')
				  ->setInvoiceNumber(uniqid());
		
		$redirectUrls = new RedirectUrls();
		$redirectUrls->setReturnUrl(strval($context->getSuccessURL()))
				  ->setCancelUrl(strval($context->getFailureURL()));
		
		$apicontext = new ApiContext(new OAuthTokenCredential($this->config->getClient(), $this->config->getSecret()));
		$apicontext->setConfig(Array('mode' => $this->config->getMode()));
		
		
		
		try {
			$input = new \PayPal\Api\InputFields();
			$input->setNoShipping(1);
			$input->setAddressOverride(1);
			
			$experience = new \PayPal\Api\WebProfile();
			$experience->setName('Chad - Digital Goods');
			$experience->setInputFields($input);
			$experience->create($apicontext);
		} 
		catch (\PayPal\Exception\PayPalConnectionException $ex) {
			list($experience) = \PayPal\Api\WebProfile::get_list($apicontext);
		}
		
		try {
			$payment = new Payment();
			$payment->setIntent("sale")
					  ->setPayer($payer)
					  ->setRedirectUrls($redirectUrls)
					  ->setTransactions(array($transaction))
					  ->setExperienceProfileId($experience->getId());
			
			$payment->create($apicontext);
		} 
		
		catch (Exception $ex) {
			throw new PublicException('Error communicating with paypal.', 500, $ex);
		}
		
		return new Redirection($payment->getApprovalLink());
	}

	public function cancel($id) {
		return false; //TODO: Implement
	}

	public function execute(PaymentAuthorization $auth, $id, $amt) {
		
		$apicontext = new ApiContext(new OAuthTokenCredential($this->config->getClient(), $this->config->getSecret()));
		$apicontext->setConfig(Array('mode' => $this->config->getMode()));
		
		$paymentId = $auth->getFormData()['paymentId'];
		$payment = Payment::get($paymentId, $apicontext);
		
		$execution = new \PayPal\Api\PaymentExecution();
		$execution->setPayerId($auth->getFormData()['PayerID']);
		
		try {
			$payment->execute($execution, $apicontext);
			return true;
		} 
		catch (Exception $ex) {
			return false;
		}
	}

	public function getStatus($id) {
		return false; //TODO: Implement
	}

	public function init(ConfigurationInterface $config) {
		$this->config = $config;
	}

	public function listen($id, PaymentAuthorization $context) {
		return false; //TODO: Implement
	}

	public function setUp() {
		return; //Paypal does not need extra set up
	}

	public function getLogo() {
		return new \payment\provider\PaymentLogo(rtrim(dirname(__FILE__), '\/') . '/paypal-logo.jpg');
	}

	public function makeConfiguration() {
		return new PaypalConfiguration();
	}

}