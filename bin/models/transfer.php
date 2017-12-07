<?php

use spitfire\Model;
use spitfire\storage\database\Schema;

class TransferModel extends Model
{
	
	/**
	 * 
	 * @param Schema $schema
	 */
	public function definitions(Schema $schema) {
		/*
		 * Generate references for the account's books sending and receiving the 
		 * funds.
		 */
		$schema->source      = new Reference(BookModel::class);
		$schema->target      = new Reference(BookModel::class);
		
		/*
		 * Fields for the amounts. Since conversion rates can change on the fly,
		 * we will write down the amount the user received in the transfer. 
		 * Effectively taking a snapshot of the conversion rate at the time.
		 * 
		 * Please note, amounts are integers and positive only. This is because,
		 * as explained in the currencies - all amounts inside of chad are managed
		 * as integers to prevent decimal errors.
		 */
		$schema->amount      = new IntegerField(true);
		$schema->received    = new IntegerField(true);
		
		/*
		 * The description allows the user to understand the reason for the transfer
		 * being made. The tags are not intended for search, and are not indexed,
		 * they do serve the purpose of being able to programatically sort the 
		 * transfer.
		 */
		$schema->description = new StringField(200);
		$schema->tags        = new StringField(200);
		
		/*
		 * Timestamps for the payment. Please note that a transfer is only balanced
		 * as soon as the executed date is provided. The due date is only intended
		 * for reminders for the user to execute the payment.
		 * 
		 * If the account does not provide the ability to be in the red, we will
		 * fail a payment and leave it as overdue.
		 */
		$schema->created     = new IntegerField(true);
		$schema->due         = new IntegerField(true);
		$schema->executed    = new IntegerField(true);
		$schema->cancelled   = new IntegerField(true);
	}

}