<?php namespace payment\payout;

use payment\ConfigurationInterface;
use payment\Context;

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
 * The provider interface allows to create and integrate external payment providers
 * into Chad and to quickly implement new sources for the application to allow
 * users to pay for goods provided by the software.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
interface PayoutInterface
{
	
	/**
	 * When the application needs to use this payment provider it will call this
	 * method providing the user configurable data for this payment provider.
	 * 
	 * @param \payment\provider\ConfigurationInterface $config
	 */
	function init(ConfigurationInterface$config);
	
	/**
	 * 
	 * @param Context $ctx
	 */
	function authorize(Context$ctx);
	
	/**
	 * 
	 * @param type $job
	 */
	function run($job);
	
	/**
	 * 
	 * @return ConfigurationInterface
	 */
	function makeConfiguration();
	
	/**
	 * 
	 * @return string The name of the provider
	 */
	function getName();
	
	/**
	 * 
	 * @param int $size
	 * @return string The location of the logo file
	 */
	function getLogo();
	
}