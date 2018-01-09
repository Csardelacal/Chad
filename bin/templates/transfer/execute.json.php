<?php

$payload = [
	'id' => $transfer->_id
];

echo json_encode(['status' => 'OK', 'payload' => $payload]);
