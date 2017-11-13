<?php namespace rights;

use BooleanField;
use Reference;
use spitfire\Model;
use spitfire\storage\database\Schema;

class UserModel extends Model
{
	
	
	/**
	 * 
	 * @param Schema $schema
	 */
	public function definitions(Schema $schema) {
		$schema->user    = new Reference('user');
		$schema->account = new Reference('account');
		$schema->write   = new BooleanField();
		$schema->listed  = new BooleanField();
	}

}