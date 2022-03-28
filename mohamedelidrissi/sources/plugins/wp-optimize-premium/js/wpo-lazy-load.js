document.addEventListener('DOMContentLoaded', function() {
	var is_mutation_observer_available = ('undefined' !== typeof MutationObserver);

	WPO_LazyLoad.update();

	if (is_mutation_observer_available) {

		// if MutationObserver available then use it.
		var observer = new MutationObserver(function (mutations) {
			mutations.forEach(function (mutation) {
				WPO_LazyLoad.update(mutation.addedNodes);
			});
		});

		var config = {childList: true, subtree: true},
			target = document.getElementsByTagName('body')[0];

		observer.observe(target, config);
	} else {
		// if MutationObserver isn't available then use events.
		window.addEventListener('load', function() {
			WPO_LazyLoad.deferred_call('update', WPO_LazyLoad.update);
		});

		window.addEventListener('scroll', function() {
			WPO_LazyLoad.deferred_call('update', WPO_LazyLoad.update);
		});

		window.addEventListener('resize', function() {
			WPO_LazyLoad.deferred_call('update', WPO_LazyLoad.update);
		});

		document.getElementsByTagName('body')[0].addEventListener('post-load', function() {
			WPO_LazyLoad.deferred_call('update', WPO_LazyLoad.update);
		});
	}
});

/**
 * Some kind of intersection observer for browsers those don't support IntersectionObserver.
 *
 * @param {function}  load     callback those called when element is visible on screen.
 * @param {object}    settings
 *
 * @return {{observe: observe, unobserve: unobserve}}
 */
var WPO_Intersection_Observer = function(load, settings) {
	var elements = [];

	settings = settings || {offset: 100};

	/**
	 *  Add element into observable list.
	 *
	 * @param {HTMLElement} element
	 *
	 * @return void
	 */
	function observe(element) {
		elements.push(element);
	}

	/**
	 * Delete element form observable list.
	 *
	 * @param {HTMLElement} element
	 *
	 * @return void
	 */
	function unobserve(element) {
		var i;
		for (i in elements) {
			if (!elements.hasOwnProperty(i)) continue;

			if (element == elements[i]) {
				delete elements[i];
				return;
			}
		}
	}

	/**
	 * Callback for events handlers. Check observable elements for visibility and call load() function.
	 *
	 * @return void
	 */
	function check() {
		var i;

		for (i in elements) {
			if (!elements.hasOwnProperty(i)) continue;

			if (is_visible(elements[i])) {
				load(elements[i]);
				unobserve(elements[i]);
			}
		}
	}

	/**
	 * Check if element is visible on screen.
	 *
	 * @param {HTMLElement} element
	 *
	 * @return {boolean}
	 */
	function is_visible(element) {
		var rect = element.getBoundingClientRect(),
			window_height = window.innerHeight
				|| document.documentElement.clientHeight
				|| document.body.clientHeight;

		return (rect.top - settings.offset < window_height) && (rect.bottom + settings.offset > 0);
	}

	window.addEventListener('load', function() {
		WPO_LazyLoad.deferred_call('check', check);
	});

	window.addEventListener('scroll', function() {
		WPO_LazyLoad.deferred_call('check', check);
	});

	window.addEventListener('resize', function() {
		WPO_LazyLoad.deferred_call('check', check);
	});

	return {
		observe: observe,
		unobserve: unobserve
	}
};

/**
 * WP-Optimize lazyload core.
 */
var WPO_LazyLoad = function() {
	var is_intersection_observer_available = ('undefined' !== typeof IntersectionObserver),
		intersection_observer;

	var settings = {
		container: window.document,
		select_class: 'lazyload', 			// used to get elements for lazy loading.
		observe_class: 'lazyload-observe', 	// added to elements currently observable.
		loaded_class: 'lazyload-loaded'		// added to loaded elements.
	};

	if (is_intersection_observer_available) {
		intersection_observer = new IntersectionObserver(load_elements, {
			root: null,
			rootMargin: '0px',
			threshold: [0.1]
		});
	} else {
		intersection_observer = new WPO_Intersection_Observer(load);
	}

	/**
	 * Load lazy load element
	 *
	 * @param {HTMLElement} element
	 *
	 * @return void
	 */
	function load(element) {
		if (has_class(element, settings.loaded_class)) return;
		add_class(element, settings.loaded_class);

		intersection_observer.unobserve(element);
		remove_class(element, settings.observe_class);

		var tag = element.tagName,
			i;

		if ('picture' == tag.toLowerCase()) {
			for (i in element.childNodes) {
				if (!element.childNodes.hasOwnProperty(i)) continue;

				update_lazy_load_attributes(element.childNodes[i]);
			}
		} else {
			update_lazy_load_attributes(element);
		}
	}

	/**
	 * Set attributes for element to show.
	 *
	 * @param {HTMLElement} element
	 *
	 * @return void
	 */
	function update_lazy_load_attributes(element) {
		// exit if element doesn't support getAttribute, for ex. text.
		if ('undefined' == typeof element.getAttribute) return;

		var data_src = element.getAttribute('data-src'),
			data_srcset = element.getAttribute('data-srcset'),
			data_background  = element.getAttribute('data-background'),
			data_background_image  = element.getAttribute('data-background-image');

		if (data_src) {
			element.setAttribute('src', data_src);
			element.removeAttribute('data-src');
		}

		if (data_srcset) {
			element.setAttribute('srcset', data_srcset);
			element.removeAttribute('data-srcset');
		}

		if (data_background) {
			element.style.background = replace_background_url(element.style.background, data_background.split(';'));
			element.removeAttribute('data-background');
		}

		if (data_background_image) {
			element.style.backgroundImage = replace_background_url(element.style.backgroundImage, data_background_image.split(';'));
			element.removeAttribute('data-background-image');
		}
	}

	/**
	 * Replace callback for background and background-image properties.
	 *
	 * @param {string} str
	 * @param {array} urls
	 *
	 * @return {string}
	 */
	function replace_background_url(str, urls) {
		var i = 0;
		return str.replaceAll(/url\([^\)]*\)/ig, function() {
			return ['url(\'', urls[i++], '\')'].join('');
		});
	}

	/**
	 * Callback function for intersection observer.
	 *
	 * @param {object} elements
	 *
	 * @return void
	 */
	function load_elements(elements) {
		var i;

		for (i in elements) {
			if (!elements.hasOwnProperty(i)) continue;

			if (elements[i].isIntersecting) {
				load(elements[i].target);
			}
		}
	}

	/**
	 * Add element to intersection observer.
	 *
	 * @param element
	 *
	 * @return void
	 */
	function handle(element) {
		remove_class(element, settings.select_class);

		if (!has_class(element, settings.observe_class)) {
			add_class(element, settings.observe_class);
			intersection_observer.observe(element);
		}
	}

	/**
	 * Add handlers for new loaded elements.
	 *
	 * @param elements
	 *
	 * @return void
	 */
	function update(elements) {

		var i,
			elements_list = elements || Array.prototype.slice.call(settings.container.getElementsByClassName(settings.select_class));

		for (i in elements_list) {
			if (!elements_list.hasOwnProperty(i)) continue;

			if (has_class(elements_list[i], settings.select_class)) {
				handle(elements_list[i]);
			} else {
				if (elements_list[i].childNodes && elements_list[i].childNodes.length) update(elements_list[i].childNodes);
			}
		}
	}

	var deferred_counter = {},
		deferred_last_call = {};

	/**
	 * Call function callback() not more often than once in timeout period.
	 *
	 * @param {string}   id		  some id for function.
	 * @param {function} callback
	 * @param {int}      timeout
	 *
	 * @return void
	 */
	function deferred_call(id, callback, timeout) {
		timeout = timeout || 200;
		// increase counter.
		deferred_counter[id] = deferred_counter[id] ? deferred_counter[id] + 1 : 1;

		setTimeout(function() {
			var now = new Date().getTime(),
				last = deferred_last_call[id] || 0;

			deferred_counter[id]--;
			if (0 === deferred_counter[id] || last + timeout < now) {
				deferred_last_call[id] = now;
				callback();
			}
		}, timeout);
	}

	/**
	 * Add class to element.
	 *
	 * @param element
	 * @param class_name
	 *
	 * @return void
	 */
	function add_class(element, class_name) {
		if (has_class(element, class_name)) return;

		if (element.className) {
			element.className += ' ' + class_name;
		} else {
			element.className = class_name;
		}
	}

	/**
	 * Remove class in element.
	 *
	 * @param element
	 * @param class_name
	 *
	 * @return void
	 */
	function remove_class(element, class_name) {
		var regexp = new RegExp(['(^|\\s)', class_name, '(\\s|$)'].join(''));

		element.className = element.className.replace(regexp, ' ');
	}

	/**
	 * Check if elementa has class.
	 *
	 * @param element
	 * @param class_name
	 *
	 * @return {boolean}
	 */
	function has_class(element, class_name) {
		var regexp = new RegExp(['(^|\\s)', class_name, '(\\s|$)'].join(''));
		return regexp.test(element.className);
	}

	return {
		update: update,
		deferred_call: deferred_call
	}
}();
