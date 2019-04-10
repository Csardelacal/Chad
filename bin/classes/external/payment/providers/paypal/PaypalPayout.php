<?php namespace external\payment\providers\paypal;

use payment\ConfigurationInterface;
use payment\Context;
use payment\flow\Defer;
use payment\flow\Form;
use payment\flow\form\StringField;
use payment\Logo;
use payment\payout\PayoutInterface;
use PayPal\Api\Payment;
use spitfire\exceptions\PrivateException;

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

class PaypalPayout implements PayoutInterface
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
	
	public function authorize(Context $context) {
		if ($context->getFormData()['email']) {
			return new Defer($context->getFormData()['email']);
		}
		
		$form = new Form();
		$form->add(new StringField('email', 'Email', 'Email address'));
		
		return $form;
	}

	public function init(ConfigurationInterface $config) {
		$this->config = $config;
	}

	public function getLogo() {
		return new Logo(rtrim(dirname(__FILE__), '\/') . '/paypal-logo.jpg');
	}
	
	public function getName() {
		return 'Paypal';
	}

	public function makeConfiguration() {
		return new PaypalConfiguration();
	}

	public function run($jobs) {
		
	}

}