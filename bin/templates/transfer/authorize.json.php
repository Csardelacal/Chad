<?php

$payload = [];

/*
 * When the software is provided a collection, it assumes that it receives a set
 * of accounts that you need to select from.
 */
if ($source instanceof spitfire\core\Collection) {
	/*
	 * Define the source as an array, so the applications can loop through it.
	 */
	$payload['source'] = [];
	
	foreach ($source as $s) {
		$payload['source'][] = [
			'id'      => $s->_id,
			'name'    => $s->name,
			'balance' => $s->balance()
		];
	}
}

/*
 * 
 */
elseif ($source instanceof AccountModel) {
	
}

elseif ($source instanceof BookModel) {
	
}

if (isset($redirect)) {
	$payload['redirect'] = $redirect;
}


echo json_encode([
	'status'  => 'OK',
	'payload' => $payload
]);