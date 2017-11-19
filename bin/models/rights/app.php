<?php namespace rights;

use spitfire\Model;
use spitfire\storage\database\Schema;

/**
 * This model allows Chad to keep track of which applications have specific 
 * read/write permissions on certain accounts.
 * 
 * Please note that, due to their nature. Applications are not allowed to search
 * for accounts by tag. This is intended to prevent them from extracting data 
 * that they're not supposed to manage.
 * 
 * When an application creates an account, it can decide whether it wants to give
 * it a tag that it can r/w or whether it wants to write itself down as an executor
 * to that account. It can also decide to make the owner an executor of the 
 * account or even prevent them from reading / writing to the account at all.
 * 
 * @property string        $app     Application being granted rights
 * @property string        $tag     Tag this app has access to. (If the account is set, this must be null)
 * @property \AccountModel $account Account that the app manages
 * @property bool          $write   Indicates whether the app can read or r/w
 * @property int           $created Timestamp of the record's creation
 * @property string        $blame   Id of the source of the permission
 * @property int           $revoked Timestamp when the app was revoked access
 * 
 * When deleting, the record is not immediately deleted. Instead it should stay 
 * revoked for a good amount of time before the application can safely purge the
 * record from the database.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class AppModel extends Model
{
	
	/**
	 * 
	 * @param Schema $schema
	 */
	public function definitions(Schema $schema) {
		$schema->app     = new \StringField(50); #The app id of the remote application
		$schema->tag     = new \StringField(50); #Allows the app to tell which accounts belong to it
		$schema->account = new \Reference('account');
		$schema->write   = new \BooleanField();
		$schema->created = new \IntegerField(true);
		$schema->blame   = new \StringField(50);
		$schema->revoked = new \IntegerField(true);
	}

}