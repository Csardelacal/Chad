<?php namespace redirection;

use ChildrenField;
use Reference;
use spitfire\Model;
use spitfire\storage\database\Schema;
use StringField;
use TextField;

/**
 * The redirection model provides an id, a name, description and some organizational 
 * data to a redirection.
 * 
 * Redirections are only to be managed by the administration of the software. This
 * is not intended as a end-user accessible feature.
 * 
 * @property AccountModel $account The account affected by the redirection
 * @property string $name The name of the redirection.
 * @property string $description The description of the redirection
 * 
 * @property redirection\RuleModel $rules The rules for this redirection to be applied
 * @property ActionModel $actions The actions executed when the redirection is applied
 */
class RedirectionModel extends Model
{
	
	
	public function definitions(Schema $schema) {
		$schema->account     = new Reference('account');
		$schema->name        = new StringField(100);
		$schema->description = new TextField();
		
		$schema->rules       = new ChildrenField('redirection\rule', 'redirection');
		$schema->actions     = new ChildrenField('redirection\action', 'redirection');
	}
	
	
	public function test($transfer) {
		$rules = collect($this->rules->toArray());
		
		return $rules->reduce(function ($carry, $e) use ($transfer) {
			return $carry && $e->test($transfer);
		}, true);
	}
	
	
	public function redirect($transfer) {
		$rules  = collect($this->actions->toArray());
		$amount = $this->account->_id == $transfer->target->account->_id? $transfer->received : $transfer->amount;
		
		$rules->each(function ($e) use ($transfer, &$amount) {
			$amount -= $e->redirect($transfer, $amount)->received;
		});
	}
	
	public static function get($account) {
		return db()->table('redirection\redirection')->get('account', $account)->fetchAll();
	}

}