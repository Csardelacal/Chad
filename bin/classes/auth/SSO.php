<?php namespace auth;

use Exception;

class SSO
{
	
	private $endpoint;
	private $appId;
	private $appSecret;
	
	public function __construct($endpoint, $appId, $appSecret) {
		$this->endpoint  = rtrim($endpoint, '/');
		$this->appId     = $appId;
		$this->appSecret = $appSecret;
	}
	
	/**
	 * Creates a new SSO Token. This allows your application to request a single
	 * user's token and manage it.
	 */
	public function createToken($expires = null) {
		/*
		 * Fetch the JSON message from the endpoint. This should tell us whether 
		 * the request was a success.
		 */
		$get = Array('appID' => $this->appId, 'appSecret' => $this->appSecret);
		if ($expires !== null) { $get['expires'] = $expires; }
		
		$response = file_get_contents($this->endpoint . '/token/create.json?' . 
				  http_build_query($get));
		
		if (!strstr($http_response_header[0], '200')) { throw new Exception('SSO rejected the token with ' . $http_response_header[0], 1605201109); }

		$data = json_decode($response);

		if (json_last_error() !== JSON_ERROR_NONE) { throw new Exception('SSO sent invalid json response - ' . json_last_error_msg(), 1608012100); }
		
		return new Token($this, $data->token, $data->expires, $data->location);
	}
	
	/**
	 * Instances a token. As opposed to the createToken method, this token cannot
	 * be authorized afterwards. 
	 * 
	 * @param string $token
	 * @return \auth\Token
	 */
	public function makeToken($token) {
		return new Token($this, $token, null, null);
	}
	
	public function getUser($username, Token$token = null) {
		
		if (!$username) { throw new Exception('Valid user id needed'); }
		
		/*
		 * Assemble the request we need to retrieve the data. Please note that if
		 * there is no token we pass no parameters.
		 */
		$request = new Request(
			$this->endpoint . '/user/detail/' . $username . '.json',
			$token && $token->isAuthenticated()? Array('token' => $token->getTokenInfo()->token) : null
		);
		
		/*
		 * Fetch the JSON message from the endpoint. This should tell us whether 
		 * the request was a success.
		 */
		$resp = $request->send();
		$data = json_decode($resp)->payload;
		
		return new User($data->id, $data->username, $data->aliases, $data->groups, $data->verified, $data->registered_unix, $data->attributes, $data->avatar);
	}
	
	public function authApp($id, $secret) {		
		$request = new Request(
			$this->endpoint . '/auth/app.json',
			Array('appId' => $id, 'appSec' => $secret)
		);
		
		$response = $request->send();
		
		$json = json_decode($response);
		return $json->authenticated;
	}
	
	public function sendEmail($userid, $subject, $body) {
		
		$request = new Request(
			$this->endpoint . '/email/send/' . $userid . '.json',
			Array('appId' => $this->appId, 'appSecret' => $this->appSecret)
		);
		
		$response = $request->send(Array('body' => $body, 'subject' => $subject));
		$data = json_decode($response)->payload;
		
		return $data;
	}
	
	public function getEndpoint() {
		return $this->endpoint;
	}
	
	public function getAppId() {
		return $this->appId;
	}
	
	public function makeSignature($target = null) {
		$salt = str_replace(['+', '/'], '', base64_encode(random_bytes(30)));
		$hash = hash('sha512', implode('.', array_filter([$this->appId, $target, $this->appSecret, $salt])));
		
		return implode(':', array_filter(['sha512', $this->appId, $target, $salt, $hash]));
	}
	
	public function getGroupList() {
		$url  = $this->endpoint . '/group/index.json';
		$resp = file_get_contents($url);
		
		if (!strstr($http_response_header[0], '200')) { 
			throw new Exception('SSO rejected the request with ' . $http_response_header[0], 201605201109);
		}
		
		$data = json_decode($resp);
		return $data->payload;
	}
	
	public function getGroup($id) {
		$url  = $this->endpoint . '/group/detail/' . $id . '.json';
		$resp = file_get_contents($url);
		
		if (!strstr($http_response_header[0], '200')) { 
			throw new Exception('SSO rejected the request with ' . $http_response_header[0], 201605201109);
		}
		
		$data = json_decode($resp);
		return $data->payload;
	}
	
}

