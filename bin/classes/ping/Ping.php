<?php namespace ping;

class Ping
{
	
	private $endpoint;
	private $appId;
	private $appSecret;
	
	public function __construct($endpoint, $appId, $appSecret) {
		$this->endpoint  = rtrim($endpoint, '/');
		$this->appId     = $appId;
		$this->appSecret = $appSecret;
	}
	
	public function push($src, $target, $content, $url = null, $media = null, $explicit = false) {
		
		$curl = $this->endpoint . '/ping/push.json?' . http_build_query(Array(
			 'appId'  => $this->appId,
			 'appSec' => $this->appSecret
		));
		
		/*
		 * Fetch the JSON message from the endpoint. This should tell us whether 
		 * the request was a success.
		 */
		$ch = curl_init($curl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(Array(
			 'src'      => $src,
			 'target'   => $target,
			 'content'  => $content,
			 'url'      => $url,
			 'media'    => $media,
			 'explicit' => $explicit? 1 : 0
		)));
		
		$response = curl_exec($ch);

		$http_response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($http_response_code !== 200) {
			throw new \Exception('Ping rejected the request' . $response, 1605141533);
		}
	}
	
	public function activity($src, $target, $content, $url = null) {
		
		$curl = $this->endpoint . '/activity/push.json?' . http_build_query(Array(
			 'appId'  => $this->appId,
			 'appSec' => $this->appSecret
		));
		
		/*
		 * Fetch the JSON message from the endpoint. This should tell us whether 
		 * the request was a success.
		 */
		$ch = curl_init($curl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(Array(
			 'src'      => $src,
			 'target'   => $target,
			 'content'  => $content,
			 'url'      => $url
		)));
		
		$response = curl_exec($ch);

		$http_response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($http_response_code !== 200) {
			throw new \Exception('Ping rejected the request' . $response, 1605141533);
		}
	}
	
}