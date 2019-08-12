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
	
	public function redirect($transfer, $leftover) {
		$pull   = $this->redirection->account->_id == $transfer->source->account->_id;
		
		$source = $pull? ($this->target->getBook($transfer->target->currency)?: $this->target->addBook($transfer->target->currency)) : $transfer->target;
		$target = $pull? $transfer->source : $this->target->getBook($transfer->target->currency)?: $this->target->addBook($transfer->target->currency);
		
		$dec    = pow(10, $source->currency->decimals);
		$amount = floor($this->calculate($leftover / $dec, $source->currency->ISO) * $dec);
		
		$db     = $transfer->getTable()->getDb();
		
		$redir  = $db->table('transfer')->newRecord();
		$redir->source      = $source;
		$redir->target      = $target;
		$redir->amount      = $amount;
		$redir->received    = $amount;
		$redir->description = substr('Redirected: ' . $transfer->description, 0, 200);
		$redir->tags        = $transfer->tags;
		$redir->previous    = $transfer;
		$redir->created     = time();
		$redir->executed    = time();
		$redir->store();
		
		$pull? $source->account->notify($redir) : $target->account->notify($redir);
		
		return $redir;
	}
	
	public function calculate($amount, $currency) {
		
		$rules    = explode(',', $this->amt);
		$fallback = reset($rules);
		
		foreach ($rules as $rule) {
			if (!preg_match('/^([0-9\.\,]+)\s*([a-zA-Z\%]*)\s*$/', $rule, $matches)){ continue; }
			$modifier = $matches[1];
			$ISOCode  = $matches[2];
			
			if ($ISOCode === '%')           { return $amount * $modifier / 100; }
			elseif ($ISOCode === $currency) { return $modifier; }
			elseif (empty($ISOCode))        { return $modifier; }
		}
		
		//TODO: Implement
		return $fallback;
		
	}

}
