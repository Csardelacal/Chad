<?php namespace external\payment\providers\bank;

use payment\ConfigurationInterface;
use payment\Context;
use payment\Logo;
use payment\provider\ProviderInterface;

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

class Bank implements ProviderInterface
{
	
	/**
	 *
	 * @var BankConfiguration
	 */
	private $config;
	
	public function __construct() {
		
	}
	
	public function authorize(Context $context) {
		
		$posted = $context->getFormData();
		
		if (isset($posted['transactionid'])) {
			return new \payment\flow\Defer($context->getFormData()['transactionid']);
		}
		
		$amt = $context->getAmt() * pow(10, 0 - $context->getCurrency()->decimals);
		
		$form = new \payment\flow\Form();
		$form->add(new \payment\flow\form\TextBlock($this->config->getInstructions()));
		$form->add(new \payment\flow\form\TextBlock('USD' . number_format($amt, 2) . PHP_EOL . 'EUR' . number_format($amt / 1.17, 2)));
		$form->add(new \payment\flow\form\TextBlock('Subject: trx' . $context->getId()));
		$form->add(new \payment\flow\form\StringField('transactionid', 'Transaction id'));
		return $form;
	}

	public function init(ConfigurationInterface $config) {
		$this->config = $config;
	}

	public function listen($id, Context $context) {
		return false; //TODO: Implement
	}

	public function getLogo() {
		return new Logo(rtrim(dirname(__FILE__), '\/') . '/bank-logo.png');
	}
	
	public function getName() {
		return 'Bank';
	}

	public function makeConfiguration() {
		return new BankConfiguration();
	}

	public function await($id) {
		return false;
	}

}