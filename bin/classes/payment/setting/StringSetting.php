<?php namespace payment\setting;

use spitfire\validation\ValidationRule;
use spitfire\validation\Validator;

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

class StringSetting extends Setting
{
	
	private $validation;
	
	public function __construct($name, $label, $default, $value) {
		parent::__construct($name, $label, $default, $value);
		$this->validation = new Validator();
	}

	public function getFormComponent() {
		return sprintf(
			'<label for="%s">%s</label><input type="text" name="%s" value="%s" id="%s">',
			'fc-' . $this->getName(),
			$this->getLabel(),
			$this->getName(),
			$this->getValue()? : $this->getDefault(),
			'fc-' . $this->getName()
		);
	}
	
	public function addRule(ValidationRule $rule) {
		return $this->validation->addRule($rule);
	}
	
	public function getMessages() {
		return $this->validation->getMessages();
	}
	
	public function isOk() {
		return $this->validation->isOk();
	}
	
	public function validate() {
		return $this->validation->validate();
	}
	
	public function setValue($value) {
		$this->validation->setValue($value);
		return parent::setValue($value);
	}

}