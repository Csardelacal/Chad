<?php namespace external\payment\providers\transferwise;

use payment\ConfigurationInterface;
use payment\Context;
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

class Transferwise implements PayoutInterface
{
	
	/**
	 *
	 * @var BankConfiguration
	 */
	private $config;
	
	public function __construct() {
	}
	
	public function authorize(Context $context) {
		$token = $this->config->token();
		
		if ($context->getFormData()['email']) {
			return new Payout($token, $context->getFormData()['email'],  $context->getFormData()['name'], $context->getAmt(), $context->getFormData()['currency']);
		}
		
		$form = new Form();
		$form->add(new StringField('name', 'Your legal name', 'Used to verify your bank account'));
		$form->add(new StringField('email', 'Your email address', 'Instructions will be sent to this address'));
		$form->add(new StringField('currency', 'Currency of choice', 'Select your currency'));
		
		return $form;
	}

	public function init(ConfigurationInterface $config) {
		$this->config = $config;
	}

	public function getLogo() {
		return new Logo(rtrim(dirname(__FILE__), '\/') . '/logo.png');
	}
	
	public function getName() {
		return 'Transferwise';
	}

	public function makeConfiguration() {
		return new Configuration();
	}

	public function run($jobs) {
		
	}

}