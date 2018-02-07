<?php namespace rights;

use spitfire\core\Collection;

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

class BureaucratPrivileges
{
	
	const GRANT_NOTHING    = 0x0;
	
	const GRANT_BUREAUCRAT = 0x1;
	const GRANT_ADMIN      = 0x2;
	
	private $bureaucrat;
	
	private $admin;
	
	private $grant;
	
	/**
	 * 
	 * @param bool $bureaucrat
	 * @param bool $admin
	 * @param int  $grant
	 */
	public function __construct($bureaucrat, $admin, $grant) {
		$this->bureaucrat = $bureaucrat;
		$this->admin = $admin;
		$this->grant = $grant;
	}
	
	public function isBureaucrat() {
		return !!$this->bureaucrat;
	}
	
	public function isAdmin() {
		return !!$this->admin;
	}
	
	public function canGrant($rights) {
		return $rights & $this->grant;
	}
	
	public static function import(Collection$dbresult) {
		$bureaucrat = false;
		$admin      = false;
		$grant      = self::GRANT_NOTHING;
		
		foreach ($dbresult as $record) {
			$bureaucrat = true;
			$admin      = $record->admin || $admin;
			$grant      = ($record->admin && $record->grant? self::GRANT_ADMIN : 0) | ($record->grant? self::GRANT_BUREAUCRAT : 0) | $grant;
		}
		
		return new BureaucratPrivileges($bureaucrat, $admin, $grant);
	}
	
}