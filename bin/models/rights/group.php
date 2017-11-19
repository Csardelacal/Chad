<?php namespace rights;

use spitfire\Model;
use spitfire\storage\database\Schema;

/**
 * This model allows Chad to keep track of which groups have specific 
 * read/write permissions on certain accounts.
 * 
 * Just like applications, groups are not allowed to search the database for 
 * specific tags. Unlike apps, if they provide a meaningful part of the account's
 * ID - Chad will allow querying them.
 * 
 * Meaningful is something along the lines of the first 6 characters of the 
 * account ID. Since these can be extremely long and tedious to type out.
 * 
 * @property string        $group   Group being granted rights
 * @property string        $tag     Tag this group has access to. (If the account is set, this must be null)
 * @property \AccountModel $account Account that the app manages
 * @property bool          $write   Indicates whether the group can read or r/w
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
class GroupModel extends Model
{
	
	/**
	 * 
	 * @param Schema $schema
	 */
	public function definitions(Schema $schema) {
		$schema->group   = new \StringField(50); #The group id of the remote application
		$schema->tag     = new \StringField(50); #Allows the app to tell which accounts belong to it
		$schema->account = new \Reference('account');
		$schema->write   = new \BooleanField();
		$schema->created = new \IntegerField(true);
		$schema->blame   = new \StringField(50);
		$schema->revoked = new \IntegerField(true);
	}

}