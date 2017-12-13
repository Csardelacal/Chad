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
		 * 
		 * Please note, that the account reset mechanism does not guarantee that
		 * the account will be reset "on time". It will, though, always reset the
		 * proper times. 
		 * 
		 * For example, an account resetting on the 1st may be reset on the 2nd if
		 * the system is extremely busy. But, when resetting on the 2nd, all transfers
		 * after that date will be ignored.
		 */
		$schema->balanced  = new IntegerField(true);
		$schema->reset     = new IntegerField(true);
		$schema->nextReset = new IntegerField(true);
		
		#Set the id and currency as primary, this is deprecated, but needs to be done for now
		$schema->account->setPrimary(true);
		$schema->currency->setPrimary(true);
		
		#This should find a way more fluent way of writing it
		$schema->index($schema->account, $schema->currency)->setPrimary(true);
	}
	
	public function onbeforesave() {
		if ($this->account->reset) {
			$this->nextReset = $this->nextReset();
		}
		
		if (!$this->balanced) {
			$this->balanced = 0;
		}
	}
	
	public function balance($until = null) {
		$db = $this->getTable()->getDb();
		
		$query = $db->table('balance')->get('book', $this);
		$query->setOrder('timestamp', 'DESC');
		$record = $query->fetch();
		
		$balance = $record? $record->amount : 0;
		$until   = $until? : time();
		
		$incomingq = $db->table('transfer')->get('executed', $record ? $record->timestamp : 0, '>');
		$incomingq->addRestriction('executed', $until, '<=');
		$incomingq->addRestriction('target', $this);
		$incoming  = $incomingq->fetchAll();
		
		foreach ($incoming as $txn) {
			$balance+= $txn->received;
		}
		
		$outgoingq = $db->table('transfer')->get('executed', $record ? $record->timestamp : 0, '>');
		$outgoingq->addRestriction('executed', $until, '<=');
		$outgoingq->addRestriction('source', $this);
		$outgoing  = $outgoingq->fetchAll();
		
		foreach ($outgoing as $txn) {
			$balance-= $txn->amount;
		}
		
		return $balance;
	}
	
	public function nextReset() {
		
		if ($this->account->resets === AccountModel::RESETS_NONE) {
			return false;
		}
		
		$b = $this->reset === null? $this->account->created : $this->reset;
		$r = $this->account->resetDate;
		
		if ($this->account->resets & AccountModel::RESETS_DAILY) {
			$hour = $this->account->resets & AccountModel::RESETS_ABSOLUTE? $r : date('H', $b);
			$day  = date('d', $b);
		}
		else {
			$hour = $this->account->resets & AccountModel::RESETS_ABSOLUTE? 0 : date('H', $b);
			$day  = $this->account->resets & AccountModel::RESETS_ABSOLUTE? $r : date('d', $b);
		}
		
		switch($this->account->resets & 0xFF) {
			case AccountModel::RESETS_YEARLY:
				return mktime($hour, 0, 0, 1, $day, date('Y', $b) + 1);
			case AccountModel::RESETS_QUARTERLY:
				return mktime($hour, 0, 0, date('m', $b) + 3, $day, date('Y', $b));
			case AccountModel::RESETS_MONTHLY:
				return mktime($hour, 0, 0, date('m', $b) + 1, $day, date('Y', $b));
			case AccountModel::RESETS_WEEKLY:
				$woy = date('W', $b);
				return mktime($hour, 0, 0, 1, ($woy > 52? $woy : $woy + 52) * 7 + $day, date('Y', $b) - 1);
			case AccountModel::RESETS_DAILY:
			default:
				return mktime($hour, 0, 0, date('m', $b), $day + 1, date('Y', $b));
		}
		
	}
	
	public static function getById($bookid) {
		list($acct, $ISO) = explode(':', $bookid);
		
		$currency = db()->table('currency')->get('ISO', $ISO);
		$_return  = db()->table('book')->get('account__id', $acct)->where('currency', $currency)->fetch();
		
		return $_return;
	}
}
