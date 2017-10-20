<?php

/**
 * The user model
 */
class UserModel extends \spitfire\Model
{
	
	public function definitions(\spitfire\storage\database\Schema $schema) {
		
		/*
		 * This is the user's default currency.
		 */
		$schema->currency = new Reference(CurrencyModel::class);
	}

}

