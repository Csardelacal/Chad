<?php namespace redirection;

use Reference;
use spitfire\Model;
use spitfire\storage\database\Schema;
use StringField;

/**
 * The action model provides the application with the possibility to define 
 * multiple targets for redirections.
 * 
 * @property RedirectionModel $redirection The redirection this action belongs to.
 * @property \AccountModel $target The account this redirection should send funds to
 * @property string $amt Numeric amount or percentage (followed by %)
 */
class ActionModel extends Model
{
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param Schema $schema
	 */
	public function definitions(Schema $schema) {
		$schema->redirection = new Reference('redirection\redirection');
		$schema->target      = new Reference('account');
		$schema->amt         = new StringField(50);
	}

}
