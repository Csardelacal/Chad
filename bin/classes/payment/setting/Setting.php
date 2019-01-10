<?php namespace payment\setting;

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

abstract class Setting implements \spitfire\validation\ValidatorInterface
{
	
	private $name;
	private $label;
	private $value;
	private $default;
	
	public function __construct($name, $label, $default, $value = null) {
		$this->name = $name;
		$this->label = $label;
		$this->default = $default;
		$this->value   = $value;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getValue() {
		return $this->value;
	}
	
	public function getDefault() {
		return $this->default;
	}
	
	public function setName($name) {
		$this->name = $name;
		return $this;
	}
	
	public function setValue($value) {
		$this->value = $value;
		return $this;
	}
	
	public function setDefault($default) {
		$this->default = $default;
		return $this;
	}
	
	public function getLabel() {
		return $this->label;
	}
	
	public function setLabel($label) {
		$this->label = $label;
		return $this;
	}
	
	abstract public function getFormComponent();
	
}