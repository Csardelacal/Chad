<?php namespace payment;

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

/**
 * This object provides a series of context metadata for a payment, for example, 
 * the URL the application wishes to be redirected to once it's done.
 */
class Context
{
	
	/*
	 * Chad will also provide success and failure URLs as well as passthrough POST
	 * / GET data when receiving a payment request. Some payment providers require
	 * these to work.
	 * 
	 * As opposed to most other payment systems, Chad will never expose a URL that
	 * gives direct access to the payment provider.
	 */
	private $successURL = null;
	private $failureURL = null;
	private $formData = null;
	
	/*
	 * For some absurd reason, many payment providers will require that the system
	 * provides an amount for the authorization of payments before they can be
	 * executed.
	 * 
	 * It's more often than not misleading to the customer since the application 
	 * can charge any amount, any amount of times, regardless of the amount authorized.
	 */
	private $amt = null;
	private $currency = null;

	public function getSuccessURL() {
		return $this->successURL;
	}

	public function getFailureURL() {
		return $this->failureURL;
	}

	public function getFormData() {
		return $this->formData;
	}

	public function getAmt() {
		return $this->amt;
	}

	public function getCurrency() {
		return $this->currency;
	}

	public function setSuccessURL($successURL) {
		$this->successURL = $successURL;
		return $this;
	}

	public function setFailureURL($failureURL) {
		$this->failureURL = $failureURL;
		return $this;
	}

	public function setFormData($formData) {
		$this->formData = $formData;
		return $this;
	}

	public function setAmt($amt) {
		$this->amt = $amt;
		return $this;
	}

	public function setCurrency($currency) {
		$this->currency = $currency;
		return $this;
	}
}