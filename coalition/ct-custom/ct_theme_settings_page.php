<?php // Theme Settings Page



// disable direct file access
if ( ! defined( 'ABSPATH' ) ) {
	
	exit;
	
}



// display the theme settings page
function ct_theme_display_settings_page() {
	
	// check if user is allowed access
	if ( ! current_user_can( 'manage_options' ) ) return;
	
	?>
	
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<?php settings_errors('ct_theme_options'); ?>
		<form id="ct_theme_form" action="options.php" method="post">
			<!-- register options saved with ajax request -->
			<!-- <input type="hidden" name="_wp_http_referer" value="<?php echo admin_url( 'options-general.php?page=ct_theme_settings_page' ); ?>" /> -->
			<?php		
			
			// output security fields
			settings_fields( 'ct_theme_options' );
			
			
			// output setting sections
			do_settings_sections( 'ct_theme_settings_page' );
			
			// submit button
			submit_button();
			
			?>
			
		</form>
	</div>

	<?php
	
}