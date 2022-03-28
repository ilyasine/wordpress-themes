jQuery(function($) {
	var send_command = wp_optimize.send_command;

	/**
	 * Handle disable/enable post caching on a single post edit page.
	 */
	$('#wpo_disable_single_post_caching').on('change', function () {
		var checkbox = $(this),
			post_id = checkbox.data('id'),
			disable = checkbox.prop('checked');

		checkbox.prop('disabled', true);

		send_command('change_post_disable_option', {
			post_id: post_id,
			meta_key: '_wpo_disable_caching',
			disable: disable
		}, function (response) {
			if (response.result) {
				checkbox.prop('checked', response.disabled);
			}
		})
		.always(function () {
			checkbox.prop('disabled', false);
		});
	});


	/**
	 * Handle disable/enable lazy-load on a single post edit page.
	 */
	$('#wpo_disable_single_post_lazyload').on('change', function () {
	var checkbox = $(this),
		post_id = checkbox.data('id'),
		disable = checkbox.prop('checked');

		checkbox.prop('disabled', true);

		send_command('change_post_disable_option', {
			post_id: post_id,
			meta_key: '_wpo_disable_lazyload',
			disable: disable
		}, function (response) {
			if (response.result) {
				checkbox.prop('checked', response.disabled);
			}
		})
		.always(function () {
			checkbox.prop('disabled', false);
		});
	});

});