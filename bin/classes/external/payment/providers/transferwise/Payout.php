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
	
	/**
	 * Contains the Transferwise API token, this is needed to authenticate the 
	 * calls against the service.
	 *
	 * @var string
	 */
	private $token;
	
	/**
	 * The account holder name. This is required by Transferwise to authenticate
	 * the receiving party as the owner of the account (probably a bank thing).
	 *
	 * @var string
	 */
	private $name;
	
	/**
	 * The email address of the recipient. Since Transferwise has this awesome
	 * transfer-to-email service, our application is not required to deal with 
	 * the user data attached to this transfer.
	 * 
	 * We just ask transferwise to collect it from the provided email recipient.
	 *
	 * @var string
	 */
	private $email;
	
	/**
	 * The amount the user is supposed to receive from our application.
	 *
	 * @var float
	 */
	private $amt;
	
	/**
	 * One of the Transferwise supported currencies. The user should be able to 
	 * select this from a list.
	 *
	 * @var string
	 */
	private $tgtcurrency;
	
	private $transferId;
	
	/**
	 * Creates a new Payout. This payout will use Transferwise as a carrier. 
	 * This uses the transfer-to-email API.
	 * 
	 * @param string $token
	 * @param string $email
	 * @param string $name
	 * @param float  $amt
	 * @param string $tgtcurrency
	 */
	public function __construct($token, $email, $name, $amt, $tgtcurrency) {
		$this->token = $token;
		$this->name  = $name;
		$this->email = $email;
		$this->amt = $amt;
		$this->tgtcurrency = $tgtcurrency;
	}
	
	/**
	 * Once the payout has been processed we do record the email, so we know which
	 * email the money was sent to, and then we forget about it.
	 * 
	 * @return string
	 */
	public function getAdditional() {
		return $this->transferId;
	}
	
	/**
	 * This is the body of the payout, if everything went according to plan, we 
	 * send the request to Transferwise to dispatch the money to the client.
	 * 
	 * @return boolean
	 * @throws PrivateException
	 */
	public function write() {
		
		/*
		 * First request, checks the profiles. We need to perform this to ensure 
		 * we're using the company profile as opposed to the personal one.
		 */
		$request = request('https://api.sandbox.transferwise.tech/v1/profiles');
		$request->header('Authorization', 'Bearer ' . $this->token);
		
		$resp1 = $request->send()->expect(200)->json();
		
		if (!$resp1 || !$resp1[1]) {
			throw new PrivateException('Transferwise error', 1905211248);
		}
		
		/*
		 * The second request requests a quote from Transferwise. This allows the 
		 * application to ensure it's receiving a consistent amount for the requested 
		 * amount.
		 * 
		 * In this case, the quote is not really relevant. Since it's not displayed
		 * to the user for approval.
		 */
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
		
		/*
		 * The third request creates an 'account' to transfer to. This is effectively
		 * the recipient of our transfer.
		 * 
		 * Since Chad uses transfer-to-email, we just need the user's email address
		 * and are good to go.
		 */
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
		
		/*
		 * The fourth request creates the transfer. This connects the recipient
		 * with the quote.
		 */
		$r4 = request('https://api.sandbox.transferwise.tech/v1/transfers');
		$r4->header('Authorization', 'Bearer ' . $this->token);
		$r4->header('Content-Type', 'application/json');
		
		$r4->post(json_encode([
			'targetAccount' => $resp3->id,
			'quote'   => $resp2->id,
			'customerTransactionId' => \UUID::v4(),
			'details' => [ 
				"reference" => "payout", 
				"transferPurpose" => "verification.transfers.purpose.other", 
				"sourceOfFunds" => "verification.source.of.funds.other"
			]
		]));
		
		$resp4 = $r4->send()->expect(200)->json();
		
		if (!$resp4) {
			throw new PrivateException('Transferwise error', 1905211250);
		}
		
		/*
		 * The fifth request funds the transfer. This adds a funding source to the
		 * payment and therefore completes it.
		 */
		$r5 = request('https://api.sandbox.transferwise.tech/v1/transfers/' . $resp4->id . '/payments');
		$r5->header('Authorization', 'Bearer ' . $this->token);
		$r5->header('Content-Type', 'application/json');
		
		$r5->post(json_encode([
			'type' => 'BALANCE'
		]));
		
		#This is the only request where TW will answer with a 201 code.
		$resp5 = $r5->send()->expect(201)->json();
		
		if (!$resp5) {
			throw new PrivateException('Transferwise error', 1905211250);
		}
		
		/*
		 * Record the transfer id, this can be later used to check on the status of
		 * the transfer in case anything went wrong.
		 */
		$this->transferId = $resp4->id;
		
		/*
		 * Return true, the payment has been successfully executed.
		 */
		return true;
	}

}
