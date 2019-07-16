<?php

use spitfire\exceptions\HTTPMethodException;
use spitfire\exceptions\PublicException;
use spitfire\io\renderers\SimpleFieldRenderer;
use spitfire\validation\ValidationException;

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

class CurrencyController extends BaseController
{
	
	public function _onload() {
		parent::_onload();
		
		if (!$this->privileges->isAdmin()) { 
			throw new PublicException('Not permitted', 403); 
		}
	}
	
	public function index() {
		$currencies = db()->table('currency')->getAll()->all();
		$this->view->set('currencies', $currencies);
	}
	
	/**
	 * 
	 * @validate >> POST#ISO (string required length[1, 5])
	 * @validate >> POST#symbol (string required length[1, 5])
	 * @validate >> POST#name (string required length[1, 20])
	 * @validate >> POST#decimals (number required)
	 * @validate >> POST#buy (number required)
	 * @validate >> POST#sell (number required)
	 * 
	 * @throws HTTPMethodException
	 */
	public function create() {
		
		try {
			
			if (!$this->request->isPost()) { throw new HTTPMethodException('Not posted'); }
			
			if (!$this->validation->isEmpty()) { throw new ValidationException('Validation failed', 1907081220, $this->validation->toArray()); }
			
			$record = db()->table('currency')->newRecord();
			$record->ISO = $_POST['ISO'];
			$record->symbol = $_POST['symbol'];
			$record->name = $_POST['name'];
			$record->decimals = $_POST['decimals'];
			$record->display  = $_POST['separator'] | $_POST['position'];
			$record->buy  = $_POST['buy'];
			$record->sell  = $_POST['sell'];
			$record->store();
			
			$this->response->setBody('Redirect...')->getHeaders()->redirect(url('currency', 'edit', $record->_id));
		}
		catch (HTTPMethodException$e) {
			//It's okay
		}
		catch (ValidationException$e) {
			$this->view->set('errors', $e->getResult());
		}
		
	}
	
	/**
	 * 
	 * @validate >> POST#ISO (string required length[1, 5])
	 * @validate >> POST#symbol (string required length[1, 5])
	 * @validate >> POST#name (string required length[1, 20])
	 * @validate >> POST#decimals (number required)
	 * @validate >> POST#buy (number required)
	 * @validate >> POST#sell (number required)
	 * 
	 * @throws HTTPMethodException
	 */
	public function edit(CurrencyModel$record) {
		
		
		try {
			
			if (!$this->request->isPost()) { throw new HTTPMethodException('Not posted'); }
			
			if (!$this->validation->isEmpty()) { throw new ValidationException('Validation failed', 1907081220, $this->validation->toArray()); }
			
			$record->ISO = $_POST['ISO'];
			$record->symbol = $_POST['symbol'];
			$record->name = $_POST['name'];
			$record->decimals = $_POST['decimals'];
			$record->display  = $_POST['separator'] | $_POST['position'];
			$record->buy  = $_POST['buy'];
			$record->sell  = $_POST['sell'];
			$record->store();
			
			$this->response->setBody('Redirect...')->getHeaders()->redirect(url('currency', 'edit', $record->_id));
		}
		catch (HTTPMethodException$e) {
			//It's okay
		}
		catch (ValidationException$e) {
			$this->view->set('errors', $e->getResult());
		}
		
		$this->view->set('record', $record);
	}
	
	public function delete($cid) {
		
	}
	
}