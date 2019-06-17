<?php namespace payment\flow\form\html;

/* 
 * The MIT License
 *
 * Copyright 2019 César de la Cal Bretschneider <cesar@magic3w.com>.
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

class Span implements \payment\flow\form\HTMLElementInterface
{
	
	private $element;
	private $weights = ['l' => 1, 'm' => 1, 's' => 1];
	
	public function __construct($element, $l = 1, $m = 1, $s = 1) {
		$this->element = $element;
		$this->weights['l'] = $l;
		$this->weights['m'] = $m;
		$this->weights['s'] = $s;
	}
	
	public function setWeight($class, $val) {
		$this->weights[$class] = $val;
		return $this;
	}
	
	public function getWeight($class) {
		return $this->weights[$class];
	}
	
	public function __toString() {
		return sprintf('<div class="span l%s m%s s%s">%s</div>', 
			$this->weights['l'],
			$this->weights['m'],
			$this->weights['s'],
			is_array($this->element)? implode(' ', $this->element) : $this->element
		);
	}

}
