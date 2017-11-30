<?php

use spitfire\locale\CurrencyLocalizer;
use spitfire\Model;
use spitfire\storage\database\Schema;

/**
 * The user model allows the app to store user AND app specific preferences to
 * customize the behavior for the given user.
 * 
 * @property CurrencyModel $currency The user's preferred currency.
 * @property int           $display  The user's preferred display settings. Masked just like the currency
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class UserModel extends Model
{
	
	public function definitions(Schema $schema) {
		
		/*
		 * This is the user's default currency.
		 */
		$schema->currency = new Reference(CurrencyModel::class);
		$schema->display  = new IntegerField(true);
	}
	
	public function localizer() {
		$decimals = $this->display & CurrencyModel::DISPLAY_DECIMAL_SEPARATOR_COMMA? ',' : '.';
		$thousands = $this->display & CurrencyModel::DISPLAY_THOUSAND_SEPARATOR_COMMA? ',' : '.';
		
		if ($this->display & CurrencyModel::DISPLAY_SYMBOL_MIDDLE) { $pos = CurrencyLocalizer::SYMBOL_DECIMALSEP; }
		elseif ($this->display & CurrencyModel::DISPLAY_SYMBOL_BEFORE) { $pos = CurrencyLocalizer::SYMBOL_BEFORE; }
		else { $pos = CurrencyLocalizer::SYMBOL_AFTER; }
		
		$cl = new CurrencyLocalizer($decimals, $thousands, $pos);
		//TODO: Special translations need to be added here
		return $cl;
	}

}

