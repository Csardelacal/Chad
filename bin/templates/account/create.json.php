<?php

$payload = [];

/*
 * If the redirect has been set, we report to the target application that the 
 * account could not be created due to missing permissions. If this is the case,
 * redirecting to this URL should fix the issue.
 */
if (isset($redirect)) {
	$payload['redirect'] = $redirect;
}

/*
 * In the event of an account being created, we will report to the remote app
 * the information about the account. Including the book(s) tha were created
 * with it.
 */
if (isset($account)) {
	$payload['account']          = [];
	$payload['account']['id']    = $account->_id;
	$payload['account']['books'] = [];
	
	/*
	 * Loop over the books and extract core information from them.
	 */
	foreach ($account->books as $book) {
		$payload['account']['books'][] = [
			'currency' => $book->currency->ISO
		];
	}
}

echo json_encode([
	'status' => isset($success) && $success? 'OK' : 'ERR',
	'payload' => $payload
]);