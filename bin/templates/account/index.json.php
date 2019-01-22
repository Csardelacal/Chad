<?php

$payload = [];

foreach ($accounts as $account) {
	$payload[] = [
		'id' => $account->_id,
		'name' => $account->name
	];
}

echo json_encode([
	 'payload' => $payload
]);