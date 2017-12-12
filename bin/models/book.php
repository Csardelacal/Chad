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
 * @property int           $reset    Date of the last reset
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

}
