<?php namespace payment\provider;

use spitfire\core\Collection;
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

class PaymentProviderPool extends Collection
{
	
	private static $instance = null;
	
	public function push($element) {
		
		if (!$element instanceof ProviderInterface) {
			throw new PrivateException('Invalid payment provider', 1712051119);
		}
		
		return parent::push($element);
	}
	
	public function configure() {
		$db = db();
		
		return $this->each(function (ProviderInterface$e) use ($db) {
			$config = $db->table('payment\provider\configuration')->get('provider', get_class($e))->fetchAll();
			$computed = [];
			
			foreach ($config as $c) {
				$computed[$c->setting] = $c->value;
			}
			
			$push = $e->makeConfiguration();
			$push->load($computed);
			$e->init($push);
			
			return $e;
		});
	}
	
	public static function getInstance() {
		if (self::$instance) { return self::$instance; }
		else { return self::$instance = new PaymentProviderPool(); }
	}
	
}