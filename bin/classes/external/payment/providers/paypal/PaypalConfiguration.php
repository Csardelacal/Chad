<?php namespace external\payment\providers\paypal;

use payment\ConfigurationInterface;
use payment\setting\StringSetting;

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

class PaypalConfiguration implements ConfigurationInterface
{
	
	private $client;
	private $secret;
	private $mode;
	
	public function load($data) {
		$this->client = $data['client'];
		$this->secret = $data['secret'];
		$this->mode   = $data['mode'];
	}

	public function save() {
		return [
			'client' => $this->client,
			'secret' => $this->secret,
			'mode'   => $this->mode
		];
	}
	
	public function getOptions() {
		return [
			new StringSetting('client', 'Client ID', '', $this->client),
			new StringSetting('secret', 'Client secret', '', $this->secret),
			new StringSetting('mode', 'Paypal mode', 'live', $this->mode)
		];
	}

	public function readOptions($sent) {
		$this->client = $sent['client'];
		$this->secret = $sent['secret'];
		$this->mode   = $sent['mode'];
	}
	
	public function getClient() {
		return $this->client;
	}
	
	public function getSecret() {
		return $this->secret;
	}
	
	public function getMode() {
		return $this->mode;
	}

}