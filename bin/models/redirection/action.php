<?php namespace redirection;

use Reference;
use spitfire\Model;
use spitfire\storage\database\Schema;
use StringField;

class ActionModel extends Model
{
	
	
	public function definitions(Schema $schema) {
		$schema->redirection = new Reference('redirection\redirection');
		$schema->target      = new Reference('account');
		$schema->amt         = new StringField(50);
	}

}
