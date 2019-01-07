<?php

$payload = [
];

if (isset($transfer) && !empty($transfer)) {
	$payload['id'] = $transfer->_id;
}
else {
	$payload['id'] = null;
}

if (isset($redirect)) {
	$payload['redirect'] = $redirect;
}

echo json_encode([
	'status'  => isset($status)? $status : 'OK',
	'payload' => $payload
]);