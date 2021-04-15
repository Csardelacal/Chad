<?php namespace defer;

use spitfire\defer\Result;
use spitfire\defer\Task;

/* 
 * The MIT License
 *
 * Copyright 2021 César de la Cal Bretschneider <cesar@magic3w.com>.
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

class BookIncinerateTask extends Task
{
	
	
	public function body(): Result 
	{
		/*
		 * First, locate the book that is to be incinerated
		 */
		list($id, $curr) = explode(':', $this->getSettings());
		
		$account = db()->table('account')->get('_id', $id)->first(true);
		$currency = db()->table('currency')->get('_id', $curr)->first(true);
		
		$book = db()->table('book')->get('account', $account)->where('currency', $currency)->first();
		
		if (!$book) { return new Result('Book was already incinerated'); }
		
		/*
		 * Books cannot be deleted if the account holds balance in any of them. This
		 * would lead to a disasterous state in which the data would be insufficient 
		 * to properly manage the system.
		 * 
		 * Effectively, removing a transfer by cascading it's deletion would revert it
		 * from the ledger and 'refund' it.
		 */
		if (db()->table('transfer')->get('source', $book)->first()) {
			return new Result('Unable to delete book yet. Transfers would be left dangling.');
		}
		
		/*
		 * The exact same (and opposite) is true for transfers that were received
		 * by the book, any transfer that is deleted results in an effective refund
		 * unless the book is properly balanced at that point.
		 */
		if (db()->table('transfer')->get('target', $book)->first()) {
			return new Result('Unable to delete book yet. Transfers would be left dangling.');
		}
		
		$book->delete();
		return new Result('Book has been incinerated correctly');
	}

}
