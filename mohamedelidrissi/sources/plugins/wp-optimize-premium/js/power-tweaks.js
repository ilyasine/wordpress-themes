(function($, send_command) {
	/**
	 * Store the tweaks
	 */
	var tweaks = [];

	/**
	 * Tweak object
	 *
	 * @param {jQuery object} $el - The tweak container
	 */
	var Tweak = function( $el ) {

		var tweak = $el.data('tweak');

		/**
		 * Run the tweak
		 *
		 * @param {string} action THe action: run, activate, deactivate
		 * @return {jQuery.ajax}
		 */
		this.run = function(action) {
			$el.addClass('running');
			return send_command(
				'power_tweak',
				{
					sub_action: action,
					data: {
						tweak: tweak
					}
				},
				this.on_run_complete
			);
		};

		/**
		 *
		 * @param {object} response The ajax query response, JSON-decoded
		 */
		this.on_run_complete = function(response) {
			var updated;
			$el.removeClass('running');
			// display message
			if (response.success && response.message) {
				updated = $('<div class="notice updated"><p>' + response.message + '</p></div>').insertAfter($el.find('h4'));
			} else if (!response.success && response.errors) {
				updated = $('<div class="notice notice-error"></div>').insertAfter($el.find('h4'));
				$.each(response.errors, function(index, error) {
					updated.append($('<p>' + error + '</p>'));
				});
			}

			if (updated) {
				setTimeout(function() {
					updated.slideUp(300, function() {
						updated.remove()
					});
				}, 2000);
			}

			// Show last update status
			if (response.hasOwnProperty('last_updated')) {
				$el.find('.last-updated .date').html(response.last_updated);
				$el.find('button.run-tweak').prop('disabled', true).text(wpoptimize.post_meta_tweak_completed);
			}
		};
	
		/**
		 * Event handler
		 *
		 * @param {Event} e
		 */
		this.on_run_action = function(e) {
			var action;
			if (!tweak) alert('No tweak ID found');
			if ($(e.target).is('.run-tweak')) action = 'run';
			if ($(e.target).is('.enable-tweak')) action = $(e.target).is(':checked') ? 'activate' : 'deactivate';
			this.run(action);
		}

		// Add the events
		$el.on('click', 'button.run-tweak', this.on_run_action.bind(this));
		$el.on('change', 'input.enable-tweak', this.on_run_action.bind(this));

		$el.on('click', '.show-details', function(e) {
			e.preventDefault();
			$el.find('.details').toggleClass('hidden');
		})
	}

	// Document ready
	$(function () {
		$('.wpo-power-tweak').each(function(index, el) {
			tweaks.push(new Tweak($(el)));
		});
	});

	wp_optimize.power_tweaks = {
		tweaks: tweaks
	};

})(jQuery, wp_optimize.send_command);