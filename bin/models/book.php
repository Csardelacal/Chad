<?php

use spitfire\Model;
use spitfire\storage\database\Schema;

/**
 * The book model provides basic information on how the account is supposed 
 * to be managed by the system. This model does not include balancing information,
 * this is managed by the appropriate models.
 * 
 * @property string        $account  The id used to identify this account. This ID is only PART of the primary key
 * @property CurrencyModel $currency The currency for this account
 * @property int           $reset    Date of the last reset
 * @property int           $balanced In order for the system to regularly balance an account, we need it to know when it was last balanced.
 * 
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class BookModel extends Model
{
	
	public function definitions(Schema $schema) {
		unset($schema->_id);
		
		/*
		 * The primary key for a book is a combination of account and currency.
		 * This allows an account to hold several balances for different currencies.
		 */
		$schema->account   = new Reference('account');
		$schema->currency  = new Reference('currency');
		
		/*
		 * The minimum allows the application to define whether and how much is 
		 * the balance this account needs to hold before it starts blocking transfers.
		 * 
		 * This property is only used for external authorizations and will not 
		 * prevent system balancing or bureaucrats to override the limit and 
		 * push the account below the defined minimum.
		 */
		$schema->minimum   = new IntegerField();
		
		/*
		 * Housekeeping flags. These are intended for the system to know when a 
		 * book needs to be rebalanced (to prevent the system from dragging along
		 * old records) and when the account expects to be reset.
		 */
		$schema->balanced  = new IntegerField(true);
		
		#Set the id and currency as primary, this is deprecated, but needs to be done for now
		$schema->account->setPrimary(true);
		$schema->currency->setPrimary(true);
		
		#This should find a way more fluent way of writing it
		$schema->index($schema->account, $schema->currency)->setPrimary(true);
	}
	
	public function onbeforesave() {
		/*
		 * If the account has never been balanced, then we should record that it's
		 * balancing date was so far in the past that the cronjob will pick it up
		 * and balance it.
		 */
		if (!$this->balanced) {
			$this->balanced = 0;
		}
	}
	
	public function balance($until = null) {
		$db = $this->getTable()->getDb();
		
		$query = $db->table('balance')->get('book', $this);
		$query->where('timestamp', '<=', $until);
		$query->setOrder('timestamp', 'DESC');
		$record = $query->fetch();
		
		$balance = $record? $record->amount : 0;
		$until   = $until? : time();
		$since   = $record? $record->timestamp : 0;
		
		$incomingq = $db->table('transfer')->get('executed', $record ? $record->timestamp : 0, '>');
		$incomingq->addRestriction('executed', $until, '<=');
		$incomingq->where('executed', '>=', $since);
		$incomingq->addRestriction('target', $this);
		$incoming  = $incomingq->fetchAll();
		
		foreach ($incoming as $txn) {
			$balance+= $txn->received;
		}
		
		$outgoingq = $db->table('transfer')->get('executed', $record ? $record->timestamp : 0, '>');
		$outgoingq->addRestriction('executed', $until, '<=');
		$outgoingq->where('executed', '>=', $since);
		$outgoingq->addRestriction('source', $this);
		$outgoing  = $outgoingq->fetchAll();
		
		foreach ($outgoing as $txn) {
			$balance-= $txn->amount;
		}
		
		return $balance;
	}
	
	public function history($until = null) {
		$db    = $this->getTable()->getDb();
		$until = $until? : time();
		
		$query = $db->table('transfer')->getAll();
		$query->addRestriction('executed', $until, '<=');
		
		$group = $query->group();
		$group->addRestriction('target', $this);
		$group->addRestriction('source', $this);
		
		$query->setOrder('created', 'DESC');
		
		return $query->range(0, 30);
	}
	
	public static function getById($bookid) {
		list($acct, $ISO) = explode(':', $bookid);
		
		$currency = db()->table('currency')->get('ISO', $ISO);
		$account = db()->table('account')->get('_id', $acct);
		
		/*
		 * If the account was deleted, the book can no longer be retrieved. This prevents
		 * handling accounts that would threaten data consistency on the system.
		 */
		if ($account->deleted) {
			return null;
		}
		
		$_return  = db()->table('book')->get('account', $account)->where('currency', $currency)->fetch();
		
		return $_return;
	}
}
