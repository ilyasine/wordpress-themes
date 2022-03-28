(function($) {

	wp_optimize.asset_preload = wp_optimize.asset_preload || {};

	// The backbone views
	var Views = {
		/**
		 * Main view
		 */
		app: Backbone.View.extend({
			initialize: function() {
				this.$no_items = this.$el.find('.nothing');
				this.$list = $('.asset-preload-list tbody');
				if ('function' === typeof URL) {
					this.home_url = new URL(wp_optimize_minify_premium.home_url);
				}
				this.listenTo(wp_optimize.asset_preload.items, 'add', this.add_item);
				this.listenTo(wp_optimize.asset_preload.items, 'remove', this.check_contents);
				this.render();
			},
			events: {
				'click .add-asset': 'on_add_asset_press'
			},
			render: function() {
				if (!wp_optimize.asset_preload.items.length) {
					this.$no_items.show();
				} else {
					this.$no_items.hide();
					wp_optimize.asset_preload.items.each(function(item) {
						this.add_item(item);
					}, this);
				}
			},
			check_contents: function() {
				if (!wp_optimize.asset_preload.items.length) {
					this.$no_items.show();
				} else {
					this.$no_items.hide();
				}

			},
			on_add_asset_press: function(e) {
				e.preventDefault();
				this.show_form('add', '.asset-preload-list tbody', new Backbone.Model({href:'', type:'', crossorigin:''}));
			},
			show_form: function(action, where, model) {
				var options = {
					action: action,
					where: where
				};
				if (model) options.model = model;
				if (this.form) this.form.remove();
				this.form = new Views.form(options);
				this.$el.find('.add-asset, .asset-edit button').hide();
				$(where).after(this.form.$el);
			},
			hide_form: function() {
				this.$el.find('.add-asset, .asset-edit button').show();
				this.form.remove();
			},
			add_item: function(item) {
				this.$no_items.hide();
				var v = new Views.item({model: item});
				this.$list.append(v.$el);
			}

		}),
		/**
		 * Form view
		 */
		form: Backbone.View.extend({
			tagName: 'tr',
			className: 'form',
			template: wp.template('wpo-asset-preload--form'),
			events: {
				'click .cancel': 'cancel',
				'click .add-item': 'add_item',
				'change #preload_href': 'detect_values',
				'keypress #preload_href': 'on_keypress'
			},
			initialize: function(options) {
				this.options = options;
				this.render();
				this.$href = this.$el.find('#preload_href');
				this.$type = this.$el.find('#preload_type');
				this.$crossorigin = this.$el.find('#preload_crossorigin');
				if (this.model && this.model.get('type')) {
					this.$type.val(this.model.get('type'));
				}
			},
			render: function() {
				this.$el.append(this.template(this.model.attributes));
				if ('edit' === this.options.action) {
					this.$el.find('.add-item').text(this.$el.find('.add-item').data('alt-label'));
				}
			},
			cancel: function() {
				wp_optimize.asset_preload.app.hide_form();
				$(this.options.where).trigger('cancelled-edit');
			},
			add_item: function() {
				if ('' == this.$href.val().trim()) return;
				if ('add' === this.options.action) {
					wp_optimize.asset_preload.items.add({
						href: this.$href.val(),
						type: this.$type.val(),
						crossorigin: this.$crossorigin.is(':checked')
					});
				} else {
					this.model.set({
						href: this.$href.val(),
						type: this.$type.val(),
						crossorigin: this.$crossorigin.is(':checked')
					});
				}
				wp_optimize.asset_preload.app.hide_form();
			},
			detect_values: function(e) {
				var url = this.$href.val();
				// Autodetect the type for the most common assets
				if (type = detect_asset_type(url)) {
					this.$type.val(type);
				}

				// Autodetect the crossorigin value
				if (wp_optimize.asset_preload.app.home_url) {
					var parsed_url = new URL(url, wp_optimize.asset_preload.app.home_url.origin);
					if (parsed_url.host != wp_optimize.asset_preload.app.home_url.host) {
						this.$crossorigin.prop('checked', true);
					}
				}

			},
			on_keypress: function(e) {
				if ('Enter' == e.code) {
					e.preventDefault();
					if (this.$href.val().length) {
						e.target.blur();
						this.add_item();
					}
				}
			}
		}),
		/**
		 * Single item view
		 */
		item: Backbone.View.extend({
			tagName: 'tr',
			template: wp.template('wpo-asset-preload--item'),
			events: {
				'click .wpo-asset--edit': 'edit',
				'click .wpo-asset--delete': 'delete_item',
				'cancelled-edit': 'cancel'
			},
			initialize: function() {
				this.render();
				this.listenTo(this.model, 'change', this.render);
				this.listenTo(this.model, 'destroy', this.remove);
			},
			render: function() {
				this.$el.removeClass('editing');
				this.$el.html(this.template(this.model.attributes));
			},
			edit: function() {
				this.$el.addClass('editing');
				wp_optimize.asset_preload.app.show_form('edit', this.$el, this.model);
			},
			delete_item: function() {
				// wp_optimize.asset_preload.items.remove(this.model);
				this.model.destroy();
			},
			cancel: function() {
				this.$el.removeClass('editing');
			}
		})
	}

	/**
	 * Run when the collection changes
	 * (Updates the input)
	 *
	 * @param {object} model
	 * @param {array}  collection
	 */
	function on_collection_change(model, collection) {
		// Update the input field with every change
		$('#hpreload').val(JSON.stringify(collection));
		$('#hpreload').trigger('change');
	}

	/**
	 * Detect the type of asset (for the main types)
	 *
	 * @param {string} resource
	 */
	function detect_asset_type(resource) {
		switch (get_url_extension(resource)) {
			case 'js':
			case 'json':
				return 'script';
			case 'jpg':
			case 'jpeg':
			case 'png':
			case 'gif':
			case 'webp':
				return 'image';
			case 'css':
				return 'style';
			case 'eot':
			case 'ttf':
			case 'woff':
			case 'woff2':
				return 'font';
		}
		return false;
	}

	/**
	 * Cross-browser file name extension extraction
	 *
	 * @param {string} url
	 */
	function get_url_extension(url) {
		return url.split(/[#?]/)[0].split('.').pop().trim().toLowerCase();
	}

	// DOM ready
	$(function () {
		var raw_data = $('#hpreload').val();

		if (raw_data) {
			try {
				data = JSON.parse(raw_data);
			} catch(e) {
				data = [];
				console.log('There was an error parsing the data:', e, raw_data);
			}
		} else {
			data = [];
		}

		/**
		 * Store the items in a collection
		 */
		var Items = new Backbone.Collection(data);

		/**
		 * Add event hanglers on the collection
		 */
		Items.on('change', on_collection_change);
		Items.on('add', on_collection_change);
		Items.on('remove', on_collection_change);

		wp_optimize.asset_preload.raw_data = raw_data;
		wp_optimize.asset_preload.items = Items;

		/**
		 * Initialize the main view
		 */
		wp_optimize.asset_preload.app = new Views.app({
			el: $('.asset-preload-main')
		});
	});
})(jQuery);
