<?php

use redirection\RedirectionModel;
use spitfire\Model;
use spitfire\storage\database\Schema;

/**
 * The book model provides basic information on how the account is supposed 
 * to be managed by the system. This model does not include balancing information,
 * this is managed by the appropriate models.
 * 
 * @property string        $account  The id used to identify this account. This ID is only PART of the primary key
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
class BookModel extends Model
{
	
	public function definitions(Schema $schema) {
		#This is due to a bug in SF - should be fixed soon and then this can be removed
		unset($schema->_id);
		
		#Set the fields
		$schema->account   = new Reference('account');
		$schema->currency  = new Reference('currency');
		$schema->balanced  = new IntegerField(true);
		
		#Set the id and currency as primary, this is deprecated, but needs to be done for now
		$schema->account->setPrimary(true);
		$schema->currency->setPrimary(true);
		
		#This should find a way more fluent way of writing it
		$schema->index($schema->account, $schema->currency)->setPrimary(true);
	}
	
	public function balance() {
		$db = $this->getTable()->getDb();
		
		$query = $db->table('balance')->get('book', $this);
		$query->setOrder('timestamp', 'DESC');
		$record = $query->fetch();
		
		$balance = $record? $record->amount : 0;
		
		$incomingq = $db->table('transfer')->get('executed', $balance->timestamp, '>');
		$incomingq->addRestriction('target', $this);
		$incoming  = $incomingq->fetchAll();
		
		foreach ($incoming as $txn) {
			$balance+= $txn->received;
		}
		
		$outgoingq = $db->table('transfer')->get('executed', $balance->timestamp, '>');
		$outgoingq->addRestriction('source', $this);
		$outgoing  = $outgoingq->fetchAll();
		
		foreach ($outgoing as $txn) {
			$balance-= $txn->amount;
		}
		
		return $balance;
	}

}
