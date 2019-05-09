<?php namespace payment\provider;

use EnumField;
use IntegerField;
use Reference;
use spitfire\Model;
use spitfire\storage\database\Schema;
use StringField;
use TextField;

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

class ExternalfundsModel extends Model
{
	
	const TYPE_PAYMENT = 'payment';
	
	const TYPE_PAYOUT = 'payout';
	
	/**
	 * 
	 * @param Schema $schema
	 */
	public function definitions(Schema $schema) {
		/*
		 * An external operation can be any of two kinds. If it's a payment, it's 
		 * a transfer of funds into the system. A payout implies that money has 
		 * been transfered (or is to be transfered) from Chad to an external source.
		 */
		$schema->type     = new EnumField(self::TYPE_PAYMENT, self::TYPE_PAYOUT);
		$schema->user     = new Reference('user');
		$schema->source   = new StringField(50); #Classname of the payment provider
		$schema->amt      = new IntegerField(true);
		$schema->account  = new Reference('account');  #While these technically form a book
		$schema->currency = new Reference('currency'); #it's sometimes convenient to separate them
		$schema->returnto = new StringField(4096);
		$schema->additional = new TextField();
		$schema->txn      = new Reference('transfer');
		$schema->created  = new IntegerField(true);
		$schema->approved = new IntegerField(true);
		$schema->executed = new IntegerField(true);
		$schema->deferred = new IntegerField(true);
	}
	
	public function onbeforesave() {
		if (!$this->created) { $this->created = time(); }
	}

}