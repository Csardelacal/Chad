<?php namespace payment;

/* 
 * The MIT License
 *
 * Copyright 2018 César de la Cal Bretschneider <cesar@magic3w.com>.
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

class Logo
{
	
	private $file;
	
	private $tempdir = 'app://bin/usr/uploads/';
	
	/**
	 * 
	 * @param string $file
	 */
	public function __construct($file) {
		$this->file = $file;
	}
	
	/**
	 * 
	 * @param type $size
	 */
	public function getEncoded($size = 128) {
		
		$icon = $this->file;
		
		/*
		 * Define the filename of the target, we store the thumbs for the objects
		 * inside the same directory they get stored to.
		 */
		$dir = storage()->dir(rtrim($this->tempdir, '\/'));
		
		if ($dir->contains($size . '_' . basename($icon))) {
			$file = $dir->open($size . '_' . basename($icon));
		}
		else {
			$file = $dir->make($size . '_' . basename($icon));
			
			try {
				$img = media()->load(storage()->get($icon));
			}
			catch (PrivateException$e){
				return null;
			}
			
			$img->scale($size, \spitfire\io\media\MediaManipulatorInterface::HEIGHT);
			$img->store($file);
		}
		
		return sprintf('data:%s;base64,%s', $file->mime(), base64_encode($file->read()));
	}
}