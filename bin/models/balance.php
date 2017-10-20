<?php

use spitfire\Model;
use spitfire\storage\database\Schema;

/**
 * Balances are a data type that helps the system capture the amount in an 
 * account to prevent it from having to aggregate all the account's movements.
 * 
 * When the app wants to generate the current balance it only needs to retrieve
 * the latest balance and all movements that occurred since.
 * 
 * To ensure that no transfer is left unchecked, the system will always balance
 * with time()-1 which will then be written into a variable and remain constant
 * throughout the request's lifespan.
 * 
 * @property AccountModel $account   Account this entry balances
 * @property int          $amount    Amount the account contains
 * @property int          $timestamp Number of seconds since unix epoch this balance was made.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class BalanceModel extends Model
{
	
	/**
	 * 
	 * @param Schema $schema
	 */
	public function definitions(Schema $schema) {
		$schema->account   = new Reference(AccountModel::class);
		$schema->amount    = new IntegerField();
		$schema->timestamp = new IntegerField(true);
	}

}
