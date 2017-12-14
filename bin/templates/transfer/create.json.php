<?php

$payload = [
	'id' => $transfer->_id
];

if (isset($redirect)) {
	$payload['redirect'] = $redirect;
}

echo json_encode([
	'status'  => isset($status)? $status : 'OK',
	'payload' => $payload
]);