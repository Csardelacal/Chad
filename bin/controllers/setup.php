<?php

use spitfire\core\Environment;
use spitfire\exceptions\PublicException;

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

class SetupController extends BaseController
{
	
	public function index() {
		
		$currency = db()->table('currency')->get('default', true)->fetch();
		$sysadmin = false;
		
		if (!$this->user) {
			return $this->response->setBody('Redirecting...')->getHeaders()->redirect(url('user', 'login'));
		}
		
		/*
		 * Check if the user is a system administrator within the organization. 
		 * Otherwise the user will not be qualified to set Chad up.
		 */
		foreach ($this->user->groups as $id => $name) {
			$sysadmin = $this->sso->getGroup($id)->sysadmin? $id : $sysadmin;
		}
		
		if (!$sysadmin) {
			throw new PublicException('Set-up requires system administrator privileges', 403);
		}
		
		/*
		 * Check if the default currency has already been created, this implies that
		 * the set-up would already have been executed.
		 */
		if ($currency) { 
			throw new PublicException('Set-up has already been executed', 403); 
		}
		
		/*
		 * Create a default currency. This will extract it's settings from the 
		 * configuration, or use a base default.
		 */
		$c = db()->table('currency')->newRecord();
		$c->ISO = Environment::get('chad.default.currency.ISO')? : 'USD';
		$c->symbol = Environment::get('chad.default.currency.symbol')? : '$';
		$c->name = Environment::get('chad.default.currency.name')? : 'US Dollar';
		$c->decimals = Environment::get('chad.default.currency.decimals')? : 2;
		$c->conversion = Environment::get('chad.default.currency.conversion')? : 1;
		$c->display = Environment::get('chad.default.currency.display')? : (CurrencyModel::DISPLAY_SYMBOL_BEFORE|CurrencyModel::DISPLAY_DECIMAL_SEPARATOR_STOP|CurrencyModel::DISPLAY_THOUSAND_SEPARATOR_COMMA);
		$c->default = true;
		$c->store();
		
		/*
		 * Set the current user up as a bureaucrat. They should be allowed to perform
		 * any operation on the application - even creating new bureaucrats and
		 * removing themselves if necessary.
		 */
		$b = db()->table('rights\bureaucrat')->newRecord();
		$b->type  = 'user';
		$b->uid   = $this->user->id;
		$b->admin = true;
		$b->grant = true;
		$b->store();
		
		/*
		 * We also define the system administrators group as bureaucrats. These users
		 * are all mighty - so you should selective about who you define as bureaucrats.
		 */
		$g = db()->table('rights\bureaucrat')->newRecord();
		$g->type  = 'group';
		$g->uid   = $sysadmin;
		$g->admin = true;
		$g->grant = true;
		$g->store();
	}
	
}