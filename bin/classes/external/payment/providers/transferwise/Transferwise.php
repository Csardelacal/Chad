<?php namespace external\payment\providers\transferwise;

use external\payment\providers\bank\BankConfiguration;
use payment\ConfigurationInterface;
use payment\Context;
use payment\flow\Form;
use payment\flow\form\html\Row;
use payment\flow\form\html\Span;
use payment\flow\form\StringField;
use payment\flow\form\TextBlock;
use payment\Logo;
use payment\payout\PayoutInterface;

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
			$amt = $context->getAmt() / pow(10, $context->getCurrency()->decimals);
			return new Payout($token, $context->getFormData()['email'],  $context->getFormData()['name'], $amt, $context->getFormData()['currency']);
		}
		
		$form = new Form();
		
		$form->add(new Row([
			new Span(new StringField('name', 'Your legal name', 'Used to verify your bank account'), 1, 1, 1),
			new Span(new TextBlock('All the data is transferred to Transferwise, we do not keep any of your personal data on our servers. Please refer to their documentation for details on data retention.'), 1, 1, 1)
		]));
		
		$form->add(new Row([
			new Span(new StringField('email', 'Your email address', 'Instructions will be sent to this address'), 3, 1, 1),
			new Span(new StringField('currency', 'Currency of choice', 'Select your currency'), 1, 1, 1)
		]));
		
		$form->add(new Row([
			new Span(new TextBlock('Transferwise only supports payment to USD within the US. International accounts cannot receive USD. Still selecting it may result in a delay of your payout.'), 3, 1, 1),
		]));
		
		$form->add(new Row([
			new Span(new TextBlock('After requesting your payout, Transferwise will send you an email to collect your banking details. Please contact an administrator if this email does not get to you in time. After 2 weeks uncollected, the payment will be refunded and the balance added to your account.'), 3, 1, 1),
		]));
		
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