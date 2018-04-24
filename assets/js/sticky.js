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


(function () {
	
	"use strict";
	
	/**
	 * 
	 * @type Array
	 */
	var registered = [];
	
	var Stuck = function (position) {
		var html = document.createElement('div');
		var child = undefined;
		
		this.setChild = function (c, ctx, next) {
			
			if (c) {
				html.parentNode || document.body.appendChild(html);
				html.style.height     = c.getBoundaries().getH() + 'px';
				html.style.width      = c.getBoundaries().getW() + 'px';
				html.style.left       = c.getBoundaries().getX() + 'px';
				html.style.background = ctx.getBackground();
				
				
				if (child !== c) { 
					html.innerHTML = ''; 
					html.appendChild(c.getHTML().cloneNode(true)); 
				}
				
				if (position === 'top') {
					html.style.top = Math.min(
						0, 
						next? (next.getBoundaries().getScreenOffsetTop() - c.getBoundaries().getH()) : 0, 
						ctx.getBoundaries().getY() + ctx.getBoundaries().getH() - c.getBoundaries().getH()
					) + 'px';
				}
				
				if (position === 'bottom') {
					html.style.bottom = Math.min(
						0, 
						next? next.getBoundaries().getScreenOffsetBottom() - c.getBoundaries().getH(): 0, 
						window.innerHeight - ctx.getBoundaries().getScreenOffsetTop() - c.getBoundaries().getH()
					) + 'px';
				}
			}
			else {
				html.parentNode && html.parentNode.removeChild(html);
			}
			
			child = c;
		};
		
		html.style.position  = 'fixed';
		html.style[position] = '0';
	};
	
	/**
	 * 
	 * @type Object
	 */
	var html = {
		top: new Stuck('top'),
		bottom: new Stuck('bottom')
	};
	
	var Sticky = function (element, context, direction) {
		
		this.getElement   = function () { return element; };
		this.getContext   = function () { return context; };
		this.getDirection = function () { return direction || 'top'; };
		
		registered.push(this);
	};
	
	var Context = function (element) {
		
		this.getElement = function () {
			return element;
		};
	};
	
	var Boundaries = function (x, y, h, w) {
		
		this.getX = function () { return x; };
		this.getY = function () { return y; };
		this.getH = function () { return h; };
		this.getW = function () { return w; };
		
		this.onscreen = function () {
			return (window.pageXOffset < x + w && window.pageXOffset + window.innerWidth  > x) &&
			       (window.pageYOffset < y + h && window.pageYOffset + window.innerHeight > y);
		};
		
		this.getScreenOffsetTop = function () {
			return y - window.pageYOffset;
		};
		
		this.getScreenOffsetBottom = function () {
			return window.pageYOffset + window.innerHeight - (y + h);
		};
		
		this.getScreenOffsetLeft = function () {
			return x - window.pageXOffset;
		};
	};
	
	var Element = function (original) {
		
		this.getBoundaries = debounce(function () { 
			var box = original.getBoundingClientRect();
			console.log('recalc');
			
			return new Boundaries(
				box.left + window.pageXOffset, 
				box.top + window.pageYOffset,
				box.height,
				box.width
			);
		}, 2000);
		
		this.getBackground = function() {
			return '#fff'; //TODO: Implement
		};
		
		this.getHTML = function() {
			return original;
		};
	};
	
	/*
	 * This helper allows the application to define listeners that will prevent
	 * the application from hogging system resources when a lot of events are 
	 * fired.
	 * 
	 * @param {type} fn
	 * @returns {Function}
	 */
	var debounce = function (fn, interval) {
	  var timeout = undefined;
	  var returnv = undefined;

	  return function () {
		  if (returnv === undefined) { return returnv = fn.apply(window, arguments); }
		  if (timeout) { return returnv; }
		  
		  var args = arguments;

		  timeout = setTimeout(function () {
			  returnv = fn.apply(window, args) || null;
			  timeout = undefined;
		  }, interval || 50);
		  
		  return returnv;
	  };
	};
	
	var findContext = function (e) {
		if (e === document.body) { return e; }
		if (e.hasAttribute('data-sticky-context')) { return e; }
		
		return findContext(e.parentNode);
	};
	
	
	/*
	 * Export the basic functions and register the necessary listeners.
	 */
	window.sticky = {
		stick : function (element, context, direction) { 
			return new Sticky(new Element(element), new Context(new Element(context)), direction);
		}
	};
	
	/*
	 * Create elements for all the elements defined via HTML
	 */
	var els = document.querySelectorAll('*[data-sticky]');
	
	for (var i = 0; i < els.length; i++) {
		new Sticky(new Element(els[i]), new Context(new Element(findContext(els[i]))), els[i].getAttribute('data-sticky'));
	}
	
	window.addEventListener('scroll', debounce(function (e) {
		var stuck     = { top : undefined, bottom : undefined };
		var runnerups = { top : undefined, bottom : undefined };
		
		for (var i = 0; i < registered.length; i++) {
			if (!registered[i].getContext().getElement().getBoundaries().onscreen() ) {
				continue;
			}
			
			if (registered[i].getDirection() === 'top') {
				if (registered[i].getElement().getBoundaries().getScreenOffsetTop() < 0) {
					if (!stuck.top || stuck.top.getElement().getBoundaries().getScreenOffsetTop() < registered[i].getElement().getBoundaries().getScreenOffsetTop()) {
						stuck.top = registered[i];
					}
				}
				else {
					if (!runnerups.top || runnerups.top.getElement().getBoundaries().getScreenOffsetTop() > registered[i].getElement().getBoundaries().getScreenOffsetTop()) {
						runnerups.top = registered[i];
					}
				}
			}
			
			if (registered[i].getDirection() === 'bottom') {
				if (registered[i].getElement().getBoundaries().getScreenOffsetBottom() < 0) {
					if (!stuck.bottom || stuck.bottom.getElement().getBoundaries().getScreenOffsetBottom() < registered[i].getElement().getBoundaries().getScreenOffsetBottom()) {
						stuck.bottom = registered[i];
					}
				}
				else {
					if (!runnerups.bottom || runnerups.bottom.getElement().getBoundaries().getScreenOffsetBottom() > registered[i].getElement().getBoundaries().getScreenOffsetBottom()) {
						runnerups.bottom = registered[i];
					}
				}
			}
		}
		
		html.top.setChild(stuck.top && stuck.top.getElement(), stuck.top && stuck.top.getContext().getElement(), runnerups.top && runnerups.top.getElement());
		html.bottom.setChild(stuck.bottom && stuck.bottom.getElement(), stuck.bottom && stuck.bottom.getContext().getElement(), runnerups.bottom && runnerups.bottom.getElement());
		
	}, 20), false);
	
}());