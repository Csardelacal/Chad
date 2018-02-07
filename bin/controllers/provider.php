<?php

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

class ProviderController extends BaseController
{
	
	public function index() {
		$providers = \payment\provider\PaymentProviderPool::getInstance();
		
		$this->view->set('providers', $providers);
	}
	
	public function edit($id) {
		
		$provider = \payment\provider\PaymentProviderPool::getInstance()->filter(function ($e) use ($id) {
			return str_replace('\\', '-', get_class($e)) === $id;
		})->rewind();
		
		$config = db()->table('payment\provider\configuration')->get('provider', get_class($provider))->fetchAll();
		$computed = [];

		foreach ($config as $c) {
			$computed[$c->setting] = $c->value;
		}

		$settings = $provider->makeConfiguration();
		$settings->load($computed);
		
		try {
			if (!$this->request->isPost()) { throw new \spitfire\exceptions\HTTPMethodException('Not posted'); }
			
			$settings->readOptions($_POST);
			$raw = $settings->save();
			
			foreach ($raw as $option => $value) {
				$record = db()->table('payment\provider\configuration')->get('provider', get_class($provider))->where('setting', $option)->fetch()? : 
						    db()->table('payment\provider\configuration')->newRecord();
				
				$record->provider = get_class($provider);
				$record->setting  = $option;
				$record->value    = $value;
				$record->store();
			}
		} 
		catch (\spitfire\exceptions\HTTPMethodException $ex) {
			//Nothing happens
		}
		
		$this->view->set('provider', $provider);
		$this->view->set('settings', $settings);
	}
	
}
