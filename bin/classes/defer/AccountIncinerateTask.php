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

class AccountIncinerateTask extends Task
{
	
	
	public function body(): Result 
	{
		$account = db()->table('account')->get('_id', $this->getSettings())->first();
		
		/*
		 * It's not entirely uncommon that a job gets queued by several different
		 * systems. If the account does not exist, this job has nothing else to do.
		 */
		if (!$account) {
			return new Result('The account has already been incinerated');
		}
		
		/*
		 * Check if the account still has books that could be left dangling. These
		 * need to be cleaned up to prevent leaving them dangling.
		 * 
		 * In a perfect world, our DBMS should collect these up and clean them because
		 * they are connected by a reference. But this may leave further data on the
		 * table on other ends.
		 */
		$books = db()->table('book')->get('account', $account)->all();
		
		$books->each(function (BookModel $book) {
			defer(new BookIncinerateTask(sprintf('%s:%s', $book->_id, $book->currency->_id)));
		});
		
		/*
		 * If the books had to be queued for incineration, we cannot continue.
		 */
		if (!$books->isEmpty()) {
			defer($this, 86400);
			return new Result('Book was found, deferred the task for 24 hours');
		}
		
		$account->delete();
		return new Result('Account was incinerated');
	}

}
