/**
 * Source: http://tablesorter.com/addons/pager/jquery.tablesorter.pager.js
 *
 * Updated for using AJAX sources to display table data.
 */
(function($) {
	$.extend({
		tablesorterPager: new function() {

			/**
			 * Show current page number in pager.
			 *
			 * @param {object} c
			 *
			 * @return {void}
			 */
			function updatePageDisplay(c) {
				$(c.cssPageDisplay, c.container).val(c.page+1);
				$(c.cssPageDisplayCount, c.container).text(c.seperator + c.totalPages);
			}

			/**
			 * Set page size for displaying table data.
			 *
			 * @param {object} table
			 * @param {number} size
			 *
			 * @return {void}
			 */
			function setPageSize(table,size) {
				var c = table.config;
				c.size = size;
				c.totalPages = Math.ceil(c.totalRows / c.size);
				c.pagerPositionSet = false;
				moveToPage(table);
				fixPosition(table);
			}

			/**
			 * Fixes pager position.
			 *
			 * @param {object} table
			 *
			 * @return {void}
			 */
			function fixPosition(table) {
				var c = table.config;
				if (!c.pagerPositionSet && c.positionFixed) {
					var c = table.config, o = $(table);
					if (o.offset) {
						c.container.css({
							top: o.offset().top + o.height() + 'px',
							position: 'absolute'
						});
					}
					c.pagerPositionSet = true;
				}
			}

			/**
			 * Switch to the first page.
			 *
			 * @param {object} table
			 *
			 * @return {void}
			 */
			function moveToFirstPage(table) {
				var c = table.config;
				c.page = 0;
				moveToPage(table);
			}

			/**
			 * Switch to the last page.
			 *
			 * @param {object} table
			 *
			 * @return {void}
			 */
			function moveToLastPage(table) {
				var c = table.config;
				c.page = (c.totalPages-1);
				moveToPage(table);
			}

			/**
			 * Switch to the next page.
			 *
			 * @param {object} table
			 *
			 * @return {void}
			 */
			function moveToNextPage(table) {
				var c = table.config;
				c.page++;

				if (c.page >= (c.totalPages-1)) {
					c.page = (c.totalPages-1);
				}

				moveToPage(table);
			}

			/**
			 * Switch to the previous page.
			 *
			 * @param {object} table
			 *
			 * @return {void}
			 */
			function moveToPrevPage(table) {
				var c = table.config;
				c.page--;

				if (c.page <= 0) {
					c.page = 0;
				}

				moveToPage(table);
			}

			/**
			 * Switch to page defined in table.config.page and render table.
			 *
			 * @param {object} table
			 *
			 * @return void
			 */
			function moveToPage(table) {
				var c = table.config;

				if (c.page >= c.totalPages) {
					// set last page if current page larger than count of pages.
					c.page = c.totalPages - 1;
				}

				if (c.page < 0) {
					c.page = 0;
				}

				if (hasDataSource(table)) {
					loadTableData(table);
				} else {
					renderTable(table,c.rowsCopy);
				}
			}

			/**
			 * Render table content and display it.
			 *
			 * @param {object} table
			 * @param {array}  rows
			 *
			 * @return {void}
			 */
			function renderTable(table,rows) {

				var c = table.config;
				var tableBody = $(table.tBodies[0]);

				// clear the table body

				$.tablesorter.clearTableBody(table);

				if (hasDataSource(table)) {
					for (var i in rows.data) {
						tableBody.append(renderRow(rows.id_key, rows.data[i], rows.columns));
					}
				} else {
					var l = rows.length;
					var s = (c.page * c.size);
					var e = (s + c.size);
					if (e > rows.length ) {
						e = rows.length;
					}

					for (var i = s; i < e; i++) {

						// tableBody.append(rows[i]);

						var o = rows[i];
						var l = o.length;
						for (var j=0; j < l; j++) {
							tableBody[0].appendChild(o[j]);
						}
					}
				}

				fixPosition(table,tableBody);

				$(table).trigger("applyWidgets");

				if (c.page >= c.totalPages && c.totalPages > 0) {
					moveToLastPage(table);
				}

				updatePageDisplay(c);
			}

			/**
			 * Returns true if data source defined.
			 *
			 * @return {boolean}
			 */
			function hasDataSource(table) {
				var c = table.config;

				return ('object' == typeof c.dataSource && c.dataSource.hasOwnProperty('fetch'));
			}

			/**
			 * Load table data from data source defined in table settings and display it.
			 *
			 * @param table
			 *
			 * @return void
			 */
			function loadTableData(table) {
				var c = table.config;

				$(table).trigger('load_start');

				c.dataSource
					.fetch(c.page * c.size, c.size)
					.done(function(response) {

						try {
							response = JSON.parse(response);
						} catch (e) {
							alert(wpoptimize.error_unexpected_response);
							return;
						}

						$(table).trigger('load_end', response);

						if (response && response.hasOwnProperty('errors') && response.errors.length) {
							alert(wpoptimize.error_unexpected_response);
						} else {
							c.totalRows = parseInt(response.result.total);
							c.totalPages = Math.ceil(c.totalRows / c.size);
							renderTable(table,response.result);
						}
					})
					.fail(function() {
						alert(wpoptimize.error_unexpected_response);
					});
			}

			/**
			 * Render html for displaying row by data object returned from optimization preview command.
			 *
			 * @param {object} data    row values description
			 * @param {object} columns columns description
			 *
			 * @return {string}
			 */
			function renderRow(id_key, data, columns) {
				var i, row = [], value = '';

				row.push(['<td><input type="checkbox" value="',data[id_key],'"></td>'].join(''));

				for (i in columns) {
					if (!columns.hasOwnProperty(i)) continue;

					if (data.hasOwnProperty(i)) {
						if ('object' == typeof(data[i])) {
							value = ['<a href="', data[i].url,'" target="_blank">',data[i].text,'</a>'].join('');
						} else {
							value = data[i];
						}
					} else {
						value = '';
					}

					row.push(['<td>',value,'</td>'].join(''));
				}

				return ['<tr>', row.join(), '</tr>'].join('');
			}

			this.appender = function(table,rows) {

				var c = table.config;

				if (hasDataSource(table)) {
					loadTableData(table);
				} else {
					c.rowsCopy = rows;
					c.totalRows = rows.length;
					c.totalPages = Math.ceil(c.totalRows / c.size);

					renderTable(table,rows);
				}

			};

			this.defaults = {
				size: 10,
				page: 0,
				totalRows: 0,
				totalPages: 0,
				container: null,
				cssNext: '.next',
				cssPrev: '.prev',
				cssFirst: '.first',
				cssLast: '.last',
				cssPageDisplay: '.pagedisplay',
				cssPageDisplayCount: '.pagedisplay-count',
				cssPageSize: '.pagesize',
				seperator: "/",
				positionFixed: true,
				appender: this.appender,
				dataSource: null
			};

			/**
			 * Constructor function.
			 *
			 * @param settings
			 */
			this.construct = function(settings) {

				return this.each(function() {

					config = $.extend(this.config, $.tablesorterPager.defaults, settings);

					var table = this, pager = config.container;

					$(this).trigger("appendCache");

					config.size = parseInt($(".pagesize",pager).val());

					/**
					 * Handle reload trigger. Used to update content after rows removed.
					 */
					$(this).on('reload', function() {
						moveToPage(table);
					});

					/**
					 * Handle change page number.
					 */
					$(config.cssPageDisplay, config.container).on('change paste keyup', function(e) {
						e.preventDefault();

						var input = $(this),
							page = 0;

						// if wrong value in input then exit.
						if ('' == input.val() || isNaN(parseInt(input.val()))) return;

						page = parseInt(input.val());
						// pages numbered from zero thatswhy decrease value.
						table.config.page = page-1;
						moveToPage(table);
					});

					/**
					 * Add event handlers for action buttons.
					 */
					$(config.cssFirst,pager).on('click', function() {
						moveToFirstPage(table);
						return false;
					});
					$(config.cssNext,pager).on('click', function() {
						moveToNextPage(table);
						return false;
					});
					$(config.cssPrev,pager).on('click', function() {
						moveToPrevPage(table);
						return false;
					});
					$(config.cssLast,pager).on('click', function() {
						moveToLastPage(table);
						return false;
					});
					$(config.cssPageSize,pager).on('change', function() {
						setPageSize(table,parseInt($(this).val()));
						return false;
					});
				});
			};
		}
	});

	// extend plugin scope
	$.fn.extend({
		tablesorterPager: $.tablesorterPager.construct
	});

})(jQuery);

/**
 * Defines method fetch(offset, limit) to load data by pages.
 *
 * @param options
 *
 * @return {{fetch: fetch}}
 */
TableSorter_DataSource = function(options) {

	/**
	 * Set option.
	 *
	 * @param option
	 * @param value
	 *
	 * @return {void}
	 */
	function set_option(option, value) {
		options[option] = value;
	}

	/**
	 * Call ajax preview command and return deferred object.
	 *
	 * @param {integer} offset
	 * @param {integer} limit
	 *
	 * @return {JSON}
	 */
	function fetch(offset, limit) {
		options.offset = 'undefined' == typeof offset ? options.offset : offset;
		options.limit = 'undefined' == typeof limit ? options.limit : limit;

		return wp_optimize.send_command('preview', options);
	}

	return {
		set_option: set_option,
		fetch: fetch
	}
};