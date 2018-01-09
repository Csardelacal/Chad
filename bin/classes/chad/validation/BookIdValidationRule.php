<?php namespace chad\validation;

use BookModel;
use spitfire\storage\database\DB;
use spitfire\validation\ValidationError;
use spitfire\validation\ValidationRule;

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

class BookIdValidationRule implements ValidationRule
{
	
	/**
	 *
	 * @var DB
	 */
	private $db;
	
	private $acceptNull;
	
	public function __construct($db, $acceptNull = false) {
		$this->db = $db;
		$this->acceptNull = $acceptNull;
	}
	
	public function test($value) {
		
		/*
		 * Sometimes the validation rule
		 */
		if ($this->acceptNull && $value === null) {
			return false;
		}
		
		$record = BookModel::getById($value);
		
		if (!$record) {
			return new ValidationError(
				'Invalid book ID provided',
				'The book ID needs to be provided as acct:currency'
			);
		}
		
		return false;
	}

}