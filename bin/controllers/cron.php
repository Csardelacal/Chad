<?php

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

class CronController extends Controller
{
	
	public function index() {
		$lock = 'bin/usr/.cron.lock';
		$fh = fopen($lock, file_exists($lock)? 'r' : 'w+');
		$ts = time() - 1;
		
		if (flock($fh, LOCK_EX|LOCK_NB)) {
			#Get the accounts to be balanced
			$pending = db()->table('book')->get('balanced', time() - 86400 * 30, '<')->fetch();
			
			if ($pending) {
				$transfers = db()->table('transfer')->getAll();
				$transfers->group()->addRestriction('source', $pending)->addRestriction('target', $pending);
				
				if ($transfers->count() > 0) {
					$balance = db()->table('balance')->newRecord();
					$balance->book = $pending;
					$balance->amount    = $pending->balance($ts);
					$balance->timestamp = $ts;
					$balance->store();
				}

				$pending->balanced = $ts;
				$pending->store();
			}
			
			flock($fh, LOCK_UN);
		}
		
		die('//OK');
	}
	
	
}