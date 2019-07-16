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
	
	const DISPLAY_SYMBOL_BEFORE = 0x0001;
	const DISPLAY_SYMBOL_AFTER  = 0x0002;
	const DISPLAY_SYMBOL_MIDDLE = 0x0004;
	
	const DISPLAY_DECIMAL_SEPARATOR_COMMA  = 0x0010;
	const DISPLAY_DECIMAL_SEPARATOR_STOP   = 0x0020;
	const DISPLAY_THOUSAND_SEPARATOR_COMMA = 0x0100;
	const DISPLAY_THOUSAND_SEPARATOR_STOP  = 0x0200;
	
	/**
	 * 
	 * @param Schema $schema
	 */
	public function definitions(Schema $schema) {
		$schema->ISO         = new StringField(10);
		$schema->symbol      = new StringField(5);
		$schema->name        = new StringField(50);
		$schema->decimals    = new IntegerField(true);
		
		$schema->buy         = new FloatField(true);
		$schema->sell        = new FloatField(true);
		
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
	
	public function sf() {
		$currency = new \spitfire\locale\Currency($this->symbol, $this->ISO, $this->decimals);
		return $currency;
	}
	
	public function convert($amt, $from) {
		return floor($amt / ($from->sell?: 1) / ($this->buy?: 1));
	}

}