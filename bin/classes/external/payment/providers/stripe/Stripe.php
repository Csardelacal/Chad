<?php namespace external\payment\providers\stripe;

use Exception;
use payment\ConfigurationInterface;
use payment\Context;
use payment\flow\Redirection;
use payment\Logo;
use payment\provider\ProviderInterface;
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

class Stripe implements ProviderInterface
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
		if (!class_exists(\Stripe\Transfer::class)) {
			throw new PrivateException('Stripe enabled. Library not found.');
		}
	}
	
	public function authorize(Context $context) {
		\Stripe\Stripe::setApiKey($this->config->getSecret());
		\Stripe\Stripe::setVerifySslCerts(false);
		
		if ($context->getFormData()['success']) {
			return new \payment\flow\Defer($context->getAdditional());
		}
		
		if ($context->getAdditional()) {
			$sid = $context->getAdditional();
		}
		else {
			/* @var $session Stripe\Checkout\Session */
			$session = \Stripe\Checkout\Session::create([
				'payment_method_types' => ['card'],
				'line_items' => [[
					'name' => 'Chad Invoice',
					'description' => 'Billed with Chad',
					'amount' => $context->getAmt(),
					'currency' => $context->getCurrency()->ISO,
					'quantity' => 1,
				]],
				'success_url' => $context->getSuccessURL() . '?success=true',
				'cancel_url' => $context->getFailureURL(),
			]);

			$sid = $session->id;
		}
		
		
		$form = new \payment\flow\Form();
		$form->add(new \payment\flow\form\HTMLBlock(
			  '<script src="https://js.stripe.com/v3/"></script>'
			. '<script type="text/javascript">'
			. 'var stripe = Stripe("' . $this->config->getPublic() . '");'
			. 'stripe.redirectToCheckout({sessionId: "' . $sid . '"}).then(function (result) {});'
			. '</script>'
		));
		
		$form->setAdditional($sid);
		
		return $form;
	}

	public function init(ConfigurationInterface $config) {
		$this->config = $config;
	}

	public function listen($id, Context $context) {
		return false; //TODO: Implement
	}
	
	public function refund($id, $amt) {
		#TODO: Implement
		throw new Exception('Not yet implemented');
	}

	public function getLogo() {
		return new Logo('file://' . rtrim(dirname(__FILE__), '\/') . '/logo.jpeg');
	}
	
	public function getName() {
		return 'Stripe';
	}

	public function makeConfiguration() {
		return new StripeConfiguration();
	}

	public function await($job) {
		\Stripe\Stripe::setApiKey($this->config->getSecret());
		\Stripe\Stripe::setVerifySslCerts(false);
		
		$sid = $job->additional;

		$events = \Stripe\Event::all([
		  'type' => 'checkout.session.completed',
		  'created' => [
			 // Check for events created in the last 24 hours.
			 'gte' => time() - 24 * 60 * 60,
		  ],
		]);

		foreach ($events->autoPagingIterator() as $event) {
			$session = $event->data->object;
		  
		  if ($session->id === $sid) {
			  return new StripePayment();
		  }
		}
	}

}