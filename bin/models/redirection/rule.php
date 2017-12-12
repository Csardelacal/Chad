<?php namespace redirection;

use chad\redirection\RedirectionRule;
use InvalidArgumentException;
use Reference;
use ReflectionClass;
use spitfire\Model;
use spitfire\storage\database\Schema;
use StringField;
use TextField;

/**
 * A rule should provide the redirection with the necessary means to check whether
 * a transfer should be redirected. 
 * 
 * Since rules can be rather complicated, this will just provide a type variable
 * that will name a class to delegate this checking too. This way we can provide
 * plug-in like extension of the rules when needed.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class RuleModel extends Model
{
	
	public function definitions(Schema $schema) {
		$schema->redirection = new Reference(RedirectionModel::class);
		$schema->type        = new StringField(200);
		$schema->parameters  = new TextField();
	}
	
	public function test($transfer) {
		$reflection = new ReflectionClass(str_replace('.', '\\', $this->type));
		
		if ($reflection->isAbstract() || !$reflection->isSubclassOf(RedirectionRule::class)) {
			throw new InvalidArgumentException('Redirection references illegal class');
		}
		
		$reflection->load($this->parameters);
		return $reflection->test($transfer);
	}
}
