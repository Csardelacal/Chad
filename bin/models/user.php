<?php

/**
 * The user model allows the app to store user AND app specific preferences to
 * customize the behavior for the given user.
 * 
 * @property CurrencyModel $currency The user's preferred currency.
 * @property int           $display  The user's preferred display settings. Masked just like the currency
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class UserModel extends \spitfire\Model
{
	
	public function definitions(\spitfire\storage\database\Schema $schema) {
		
		/*
		 * This is the user's default currency.
		 */
		$schema->currency = new Reference(CurrencyModel::class);
		$schema->display  = new IntegerField(true);
	}

}

