<?php namespace payment\provider;

use EnumField;
use IntegerField;
use Reference;
use spitfire\Model;
use spitfire\storage\database\Schema;
use StringField;
use TextField;
use UserModel;

/* 
 * The MIT License
 *
 * Copyright 2018 César de la Cal Bretschneider <cesar@magic3w.com>.
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
 * An authorization represents a payment method that allows the user to use it 
 * multiple times without requiring them to renew the source. This doesn't mean
 * that CHAD does recurring payments - at least not without the help of third 
 * party components.
 * 
 * For example, when authorizing a bank account, the user can reuse the bank 
 * account later without requiring them to reenter the details.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class AuthorizationModel extends Model
{
	
	/**
	 * If the authorization has been requested but has not yet been granted.
	 */
	const PENDING = 'pending';
	
	/**
	 * The payment has been granted and can be used. On single use payment providers,
	 * the system will set the payment to consumed or expired as soon as it has
	 * been used.
	 */
	const AVAILABLE = 'available';
	
	/**
	 * A consumed authorization indicates that the payment provider has used this 
	 * the amount of times it was supposed to be used or it has exceeded it's 
	 * validity.
	 * 
	 * Consumed records are only held for book-keeping purposes and are no longer 
	 * provided by Chad to the payment providing extension.
	 */
	const CONSUMED  = 'consumed';
	
	/**
	 * Is basically an alias for consumed. A bit of syntactical sugar for recurring
	 * transactions.
	 */
	const EXPIRED  = 'consumed';
	
	/**
	 * 
	 * @param Schema $schema
	 * @return Schema
	 */
	public function definitions(Schema $schema) {
		$schema->user = new Reference(UserModel::class);
		$schema->provider = new StringField(255);
		
		/*
		 * This field contains data that the payment provider can write to / read 
		 * from to identify the payer, provide credentials, etc.
		 */
		$schema->data = new TextField();
		
		/*
		 * Provides a human readable string that does not contain user-identifiable
		 * information when the user has multiple recorded payment providers
		 */
		$schema->human  = new StringField(100);
		$schema->status = new EnumField(self::PENDING, self::AVAILABLE, self::CONSUMED);
		$schema->expires = new IntegerField(true);
		$schema->created = new IntegerField(true);
	}

}