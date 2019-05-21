<?php namespace external\payment\providers\transferwise;

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

class Payout implements \payment\flow\PayoutInterface
{
	
	private $token;
	
	private $name;
	private $email;
	
	private $amt;
	
	private $tgtcurrency;
	
	public function __construct($token, $email, $name, $amt, $tgtcurrency) {
		$this->token = $token;
		$this->name  = $name;
		$this->email = $email;
		$this->amt = $amt;
		$this->tgtcurrency = $tgtcurrency;
	}

	public function getAdditional() {
		return null;
	}

	public function write() {
		
		
		$request = request('https://api.sandbox.transferwise.tech/v1/profiles');
		$request->header('Authorization', 'Bearer ' . $this->token);
		
		$resp1 = $request->send()->expect(200)->json();
		
		if (!$resp1 || !$resp1[1]) {
			throw new PrivateException('Transferwise error', 1905211248);
		}
		
		$r2 = request('https://api.sandbox.transferwise.tech/v1/quotes');
		$r2->header('Authorization', 'Bearer ' . $this->token);
		$r2->header('Content-Type', 'application/json');
		
		$r2->post(json_encode([
			'profile' => $resp1[1]->id,
			'source' => 'USD',
			'target' => $this->tgtcurrency,
			'rateType' => 'FIXED',
			'sourceAmount' => $this->amt,
			'type' => 'BALANCE_PAYOUT'
		]));
		
		$resp2 = $r2->send()->expect(200)->json();
		
		if (!$resp2 || !$resp2->id) {
			throw new PrivateException('Transferwise error', 1905211249);
		}
		
		$r3 = request('https://api.sandbox.transferwise.tech/v1/accounts');
		$r3->header('Authorization', 'Bearer ' . $this->token);
		$r3->header('Content-Type', 'application/json');
		
		$r3->post(json_encode([
			'profile' => $resp1[1]->id,
			'accountHolderName' => $this->name,
			'type' => 'email',
			'currency' => $this->tgtcurrency,
			'details' => ['email' => $this->email]
		]));
		
		$resp3 = $r3->send()->expect(200)->json();
		
		if (!$resp3 || !$resp3->id) {
			throw new PrivateException('Transferwise error', 1905211250);
		}
		
		$r4 = request('https://api.sandbox.transferwise.tech/v1/transfers');
		$r4->header('Authorization', 'Bearer ' . $this->token);
		$r4->header('Content-Type', 'application/json');
		
		$r4->post(json_encode([
			'targetAccount' => $resp3->id,
			'quote'   => $resp2->id,
			'customerTransactionId' => \UUID::v4(),
			'details' => [ 
				"reference" => "payout", 
				"transferPurpose" => "verification.transfers.purpose.other", "sourceOfFunds" => "verification.source.of.funds.other"]
		]));
		
		$resp4 = $r4->send()->expect(200)->json();
		
		if (!$resp4) {
			throw new PrivateException('Transferwise error', 1905211250);
		}
		
		
		
		$r5 = request('https://api.sandbox.transferwise.tech/v1/transfers/' . $resp4->id . '/payments');
		$r5->header('Authorization', 'Bearer ' . $this->token);
		$r5->header('Content-Type', 'application/json');
		
		$r5->post(json_encode([
			'type' => 'BALANCE'
		]));
		
		$resp5 = $r5->send()->expect(200)->json();
		
		if (!$resp5) {
			throw new PrivateException('Transferwise error', 1905211250);
		}
		
		return true;
	}

}
