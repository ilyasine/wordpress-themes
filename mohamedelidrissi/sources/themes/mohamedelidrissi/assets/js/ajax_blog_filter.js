(function ($) {

	$(document).ready(function () {

		var $loading = $('.loader.ajax').hide();
/* 
		$('.main').on('click', '.pagination a.page-numbers', function (event) {
			event.preventDefault();
			console.log('pagination clicked');

			var pagenb = parseInt($(this).attr('href').replace(/\D/g, ''));

			console.log(pagenb);

			$.ajax({
				url: blog_filter.ajax_url,
				data: {
					action: 'filter_blogs',
					nonce: blog_filter.nonce,
					pagenb: pagenb,

				},
				type: 'post',

				success: function (result, textstatus) {
					// $('.pagination').html(result);
					// console.log('sucess');


				},
				error: function (result) {
					// console.log(result);
					// console.log('fail');
				},

			})

		}) */

		$('.main').on('click', '.btn_blog_filter', function (event) {
			event.preventDefault();
			//  console.log('prevent default') ;

			$('.btn_blog_filter.active_post').removeClass('active_post');
			$(this).addClass('active_post');

			var category = $(this).data('category');
			var page = $(this).data('page');

			$this = $(this);

			$.ajax({
				url: blog_filter.ajax_url,
				data: {
					action: 'filter_blogs',
					nonce: blog_filter.nonce,
					category: category,
					page: page,
					beforeSend: function () {
						$loading.show();
					}
				},
				type: 'post',

				success: function (result, textstatus) {
					$('.blog-container').html(result);
					$loading.show();
					$page = parseInt($this.attr('href').replace(/\D/g, ''));

					// console.log($page);
				},
				error: function (result) {
					// console.log(result);
					// console.log('fail') ;
				},
				complete: function () {
					$loading.hide();
					share_post();
				}
			})
		});
	});



})(jQuery);


