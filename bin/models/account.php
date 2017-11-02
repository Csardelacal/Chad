<?php

use redirection\RedirectionModel;
use spitfire\Model;
use spitfire\model\Index;
use spitfire\storage\database\Schema;

/**
 * The account model provides basic information on how the account is supposed 
 * to be managed by the system. This model does not include balancing information,
 * this is managed by the appropriate models.
 * 
 * @property string        $_id      The id used to identify this account. This ID is only PART of the primary key
 * @property CurrencyModel $currency The currency for this account
 * @property UserModel     $owner    The user that this account belongs to. This does not imply access.
 * @property string        $taxID    Allows to name an account with a code pertaining to tax information
 * @property int           $resets   Indicates an account that automatically balances itself to 0 at a given point
 * @property int           $balanced In order for the system to regularly balance an account, we need it to know when it was last balanced.
 * @property string        $tags     Tags do allow application and group permissions to target big amounts of accounts at once
 * 
 * @property RedirectionModel $redirects A collection of redirects for this account.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class AccountModel extends Model
{
	
	const RESETS_NONE      = 0x000;
	const RESETS_DAILY     = 0x001;
	const RESETS_WEEKLY    = 0x002;
	const RESETS_MONTHLY   = 0x004;
	const RESETS_QUARTERLY = 0x008;
	const RESETS_YEARLY    = 0x010;
	
	const RESETS_ABSOLUTE  = 0x100;
	const RESETS_RELATIVE  = 0x200;
	
	public function definitions(Schema $schema) {
		#This is due to a bug in SF - should be fixed soon and then this can be removed
		unset($schema->_id);
		
		#Set the fields
		$schema->_id       = new StringField(25);
		$schema->currency  = new Reference('currency');
		$schema->owner     = new Reference('user');
		$schema->taxID     = new StringField(25); #This allows the system to export accounting data to external agents.
		$schema->resets    = new IntegerField(true);
		$schema->balanced  = new IntegerField(true);
		$schema->tags      = new TextField();
		
		$schema->redirects = new ChildrenField(RedirectionModel::class, 'account');
		
		#Set the id and currency as primary, this is deprecated, but needs to be done for now
		$schema->_id->setPrimary(true);
		$schema->currency->setPrimary(true);
		
		#This should find a way more fluent way of writing it
		$schema->index($schema->_id, $schema->currency)->setPrimary(true);
	}

}
