<?php namespace payment;

use BooleanField;
use ChildrenField;
use ManyToManyField;
use spitfire\Model;
use spitfire\storage\database\Schema;
use StringField;

/* 
 * The MIT License
 *
 * Copyright 2019 César de la Cal Bretschneider <cesar@magic3w.com>.
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
 * 
 * @property bool $enabled Indicates whether the provider is available
 * @property bool $default In the absence of rules, should this 
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class ProviderModel extends Model
{
	
	/**
	 * 
	 * @param Schema $schema
	 * @return Schema
	 */
	public function definitions(Schema $schema) {
		
		/*
		 * Allows an adminsitrator to disable the provider from being used in the
		 * system. This can allow the administrator to use the interface to stop
		 * fraud if the implementation is faulty.
		 */
		$schema->enabled = new BooleanField();
		
		/*
		 * If no rules define this provider's state, it will use the default to 
		 * determine whether a user can indeed use this provider.
		 * 
		 * E.g. Paypal can be used in all countries except a select few, we would
		 * default to indicating the customer can use Paypal, and remove the countries
		 * that the system is not available in.
		 * 
		 * On the other hand, Sofort is only available in Germany, so we would set this
		 * to default to false, and enable the system for users in Germany with a rule.
		 */
		$schema->default = new \BooleanField();
		
		/*
		 * This maps the model to the class that provides the logic for the payment
		 * provider.
		 */
		$schema->provider = new StringField(255);
		
		/*
		 * A payment provider can accept one (or several) currencies to be pushed
		 * into or out of the system.
		 * 
		 * e.G. SEPA Payouts only can be made in EUR, and therefore need to be 
		 * converted before they can be sent to the payout provider.
		 * 
		 * On the other hand, Paypal manages pretty much every currency under the
		 * sun, therefore allowing the user to request a payout in USD or EUR freely.
		 * 
		 * This also allows chad to send "fake currencies', where the system can 
		 * manage the user's balances in something like tokens, diamonds or points
		 * and can top up their account using currencies that we understand.
		 */
		$schema->accepts = new ManyToManyField('currency');
		$schema->rules = new ChildrenField('\payment\provider\rule', 'provider');
	}

}
