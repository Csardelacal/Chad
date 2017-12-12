<?php namespace rights;

use spitfire\Model;
use spitfire\storage\database\Schema;

/* 
 * The MIT License
 *
 * Copyright 2017 César de la Cal Bretschneider <cesar@magic3w.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * The bureaucrats class allows the application to determine whether certain users
 * have certain extraordinary permissions. These are:
 * 
 * * Ability to request access to accounts for a user
 * * Change the minimum balance of an account below 0
 * * Ability to create / edit redirections on accounts [admin only]
 * * Ability to create / edit resetting accounts [admin only]
 * 
 * The system will never accept applications or users represented by an application
 * as bureaucrats. Tokens that are not generated and authenticated by Chad will
 * also be rejected.
 * 
 * While this may seem like a overzealous measure, in normal usage cases, these
 * features are barely useful, but they provide powerful attack vectors for 
 * malicious users to exploit the system and abuse resetting accounts.
 * 
 * Basically, if a user sets up a sourcing redirection from an account that resets
 * itself, the user will be able to retrieve funds from the account without the
 * resetting account ever draining.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class BureaucratModel extends Model
{
	
	/**
	 * 
	 * @param Schema $schema
	 */
	public function definitions(Schema $schema) {
		$schema->type    = new \EnumField('user', 'group'); #Apps cannot be bureaucrats
		$schema->uid     = new \IntegerField(true); #The user or group id
		$schema->admin   = new \BooleanField(); #Whether the user is admin (can create resetting accounts)
		$schema->grant   = new \BooleanField(); #Whether the user can grant his status to others
		$schema->revoked = new \IntegerField(true); #Whether this status has been revoked
	}

}