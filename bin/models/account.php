<?php

use db\TagField;
use redirection\RedirectionModel;
use rights\AppModel;
use rights\UserModel;
use spitfire\exceptions\PrivateException;
use spitfire\Model;
use spitfire\storage\database\Schema;

/**
 * The account model provides basic information on how the account is supposed 
 * to be managed by the system. This model does not include balancing information,
 * this is managed by the appropriate models.
 * 
 * @property string        $_id      The id used to identify this account. This ID is only PART of the primary key
 * @property UserModel     $owner    The user that this account belongs to. This does not imply access.
 * @property string        $taxID    Allows to name an account with a code pertaining to tax information
 * @property int           $resets   Indicates an account that automatically balances itself to 0 at a given point
 * @property int           $balanced In order for the system to regularly balance an account, we need it to know when it was last balanced.
 * @property int|null      $deleted  Whether and when this account was flagged as deleted
 * @property string        $tags     Tags do allow application and group permissions to target big amounts of accounts at once
 * @property BookModel[]   $books    The books this account manages
 * 
 * @property RedirectionModel $redirects A collection of redirects for this account.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class AccountModel extends Model
{
	
	public function definitions(Schema $schema) {
		#This is due to a bug in SF - should be fixed soon and then this can be removed
		unset($schema->_id);
		
		#Set the fields
		$schema->_id       = new StringField(25);
		$schema->name      = new StringField(50);
		$schema->owner     = new Reference('user');
		$schema->taxID     = new StringField(25); #This allows the system to export accounting data to external agents.
		$schema->tags      = new TagField();
		
		$schema->books     = new ChildrenField(BookModel::class, 'account');
		$schema->redirects = new ChildrenField(RedirectionModel::class, 'account');
		
		#For the permissions system
		$schema->ugrants   = new ChildrenField(UserModel::class, 'account');
		$schema->agrants   = new ChildrenField(AppModel::class, 'account');
		
		#Accounts can be flagged as deleted
		$schema->deleted   = new IntegerField(true);
		
		#Set the id and currency as primary, this is deprecated, but needs to be done for now
		$schema->_id->setPrimary(true);
		
		#This should find a way more fluent way of writing it
		$schema->index($schema->_id)->setPrimary(true);
	}
	
	public function onbeforesave() {
		
		/*
		 * Create a random ID.
		 */
		if ($this->_id === null) {
			$this->_id = substr(str_replace(['/', '=', '-', '_', '+'], '', base64_encode(random_bytes(100))), 0, 25);
		}
	}
	
	public function getBook($currency) {
		$db = $this->getTable()->getDb();
		$c  = $currency instanceof CurrencyModel? $currency : $db->table('currency')->get('ISO', $currency)->fetch();
		
		if (!$c) { throw new PrivateException('No such currency', 1711301114); }
		
		$q  = $db->table('book')->get('account', $this)->addRestriction('currency', $c);
		return $q->fetch();
	}
	
	public function getBooks() {
		$db = $this->getTable()->getDb();
		
		$q  = $db->table('book')->get('account', $this);
		return $q->fetchAll();
	}
	
	public function addBook($currency) {
		$db = $this->getTable()->getDb();
		
		if (!$currency instanceof CurrencyModel) { 
			$currency = $db->table('currency')->get('ISO', $currency)->addRestriction('removed', null, 'IS')->fetch(); 
		}
		
		if ($this->getBook($currency->ISO)) {
			throw new PrivateException('Book already exists', 1711302039);
		}
		
		$book = $db->table('book')->newRecord();
		$book->account = $this;
		$book->currency = $currency;
		$book->balanced = 0;
		$book->minimum  = 0;
		$book->store();
		
		return $book;
	}
	
	public function estimatedBalance($currency) {
		$books = $this->getBooks();
		$balance = 0;
		
		foreach ($books as $book) {
			$balance += $currency->convert($book->balance(), $book->currency);
		}
		
		return $balance;
	}
	
	public function notify($transfer) {
		
		/*
		 * Redirections listen here. Once the data has been stored, we can trigger
		 * a redirection and make things happen.
		 */
		RedirectionModel::get($this)->reduce(function ($carry, $e) use($transfer) {
			if (!$carry && $e->test($transfer)) { $e->redirect($transfer); return true; }
			else                                { return false; }
		}, false);
		
	}

}
