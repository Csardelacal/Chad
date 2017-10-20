<?php

use spitfire\Model;
use spitfire\storage\database\Schema;

/**
 * This model represents a currency within the system. It intends the system to 
 * be able to convert balances between currencies, it also allows the application
 * to properly localize the currency.
 * 
 * @property string $ISO         International code for the currency
 * @property string $symbol      The symbol to display next to the amounts
 * @property string $name        The name given to the currency
 * @property int    $decimals    The decimal count for the currency
 * @property int    $conversion  Conversion rate (normalized) for the currency
 * @property string $collissions If the currency shares a symbol with another currency they should be added here
 * @property int    $display     Contains a masked series of settings for display
 * @property bool   $default     Makes the currency the default for new accounts
 * @property int    $removed     The timestamp of this currency no longer being active
 * @property CurrencyModel $deprecatedBy The currency intended to replace this in the event of it being removed
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class CurrencyModel extends Model
{
	
	/**
	 * 
	 * @param Schema $schema
	 */
	public function definitions(Schema $schema) {
		$schema->ISO         = new StringField(10);
		$schema->symbol      = new StringField(5);
		$schema->name        = new StringField(50);
		$schema->decimals    = new IntegerField(true);
		$schema->conversion  = new IntegerField(true);
		
		#Localization based info
		$schema->collissions  = new TextField();
		$schema->display      = new IntegerField(true);
		
		#Mark one currency as default
		$schema->default      = new BooleanField();
		
		#Remove a currency and deprecate it with something else
		#If the user has balances with a deprecated currency, the system should 
		#auto update them by transferring them to a new account.
		$schema->removed      = new IntegerField();
		$schema->deprecatedBy = new Reference(CurrencyModel::class);
	}

}