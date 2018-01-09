<?php

$c = isset($book)? $book->currency : $currency;


$payload = [
	'account'  => $account->_id,
	'balance'  => isset($book)? $book->balance() : $account->estimatedBalance($currency),
	'currency' => ['ISO' => $c->ISOCode, 'decimals' => $c->decimals]
];


if (isset($books)) {
	//TODO: Implement
}

if (isset($book)) {
	//TODO: Implement
	
}

echo json_encode(['status' => 'OK', 'payload' => $payload, 'errors'  => ob_get_clean()]);
ob_start();