<?php
/**
 * CT Custom functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package CT_Custom
 */

if ( ! function_exists( 'ct_custom_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function ct_custom_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on CT Custom, use a find and replace
		 * to change 'ct-custom' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'ct-custom', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus( array(
			'menu-1' => esc_html__( 'Primary', 'ct-custom' ),
		) );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		// Set up the WordPress core custom background feature.
		add_theme_support( 'custom-background', apply_filters( 'ct_custom_custom_background_args', array(
			'default-color' => 'ffffff',
			'default-image' => '',
		) ) );

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support( 'custom-logo', array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		) );
	}
endif;
add_action( 'after_setup_theme', 'ct_custom_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function ct_custom_content_width() {
	// This variable is intended to be overruled from themes.
	// Open WPCS issue: {@link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/issues/1043}.
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$GLOBALS['content_width'] = apply_filters( 'ct_custom_content_width', 640 );
}
add_action( 'after_setup_theme', 'ct_custom_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function ct_custom_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'ct-custom' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'ct-custom' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'ct_custom_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function ct_custom_scripts() {
	wp_enqueue_style( 'ct-custom-style', get_stylesheet_uri() );

	wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css' );

	wp_enqueue_style( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css' );

	wp_enqueue_script( 'ct-custom-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '20151215', true );

	wp_enqueue_script( 'ct-custom-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20151215', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'ct_custom_scripts' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

/**
 * Load WooCommerce compatibility file.
 */
if ( class_exists( 'WooCommerce' ) ) {
	require get_template_directory() . '/inc/woocommerce.php';
}

/* Load the theme settings page */

require_once 'ct_theme_settings_page.php';

/**
 * Create the theme settings menu.
 */

function ct_theme_settings_menu(){

	add_menu_page(
		'Ct theme Settings',
		'Ct theme Settings',
		'manage_options',
		'ct_theme_settings_page',
		'ct_theme_display_settings_page',
		'dashicons-admin-generic',
		null
	);

}

add_action('admin_menu', 'ct_theme_settings_menu');

/**
 * Settings Sections.
 */

 	function ct_theme_settings_sections () {

		add_settings_section (
			'main_section',
			'Theme Inputs', 
			'reading_section_description',
			'ct_theme_settings_page',
		);

		add_settings_section (
			'social_media_section',
			'Social Media Links', 
			'social_media_section_description',
			'ct_theme_settings_page'
		);

		// logo image field
		add_settings_field (
			'logo_image_option',
			'Image Upload for Logo', 
			'logo_image_callback',
			'ct_theme_settings_page',
			'main_section'
		);
		register_setting(	
			'ct_theme_options',
			'ct_theme_logo_image_option',
		);


		// Phone Number field
		add_settings_field (
			'phone_number_option',
			'Phone Number',
			'phone_number_callback',
			'ct_theme_settings_page',
			'main_section'
		);
		register_setting(	
			'ct_theme_options',
			'ct_theme_phone_number_option',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default' => '00000000000'
			)
		);


		// Address Information field
		add_settings_field (
			'adress_information_option',
			'Address Information',
			'adress_information_callback',
			'ct_theme_settings_page',
			'main_section'
		);
		register_setting(	
			'ct_theme_options',
			'ct_theme_adress_information_option',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default' => '535 La Plata Street 4200 Argentina'
			)
		);

		// Fax Number field
		add_settings_field (
			'fax_number_option',
			'Fax Number',
			'fax_number_callback',
			'ct_theme_settings_page',
			'main_section'
		);
		register_setting(	
			'ct_theme_options',
			'ct_theme_fax_number_option',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default' => '000000000000'
			)
		);

		// Add settings fields for Social Media links

		// Facebook
		add_settings_field (
			'facebook_link_option',
			'Facebook link',
			'facebook_link_callback',
			'ct_theme_settings_page',
			'social_media_section'
		);
		register_setting(	
			'ct_theme_options',
			'ct_theme_facebook_link_option',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default' => 'https://www.facebook.com/'
			)
		);

		// Twitter
		add_settings_field (
			'twitter_link_option',
			'Twitter link',
			'twitter_link_callback',
			'ct_theme_settings_page',
			'social_media_section'
		);
		register_setting(	
			'ct_theme_options',
			'ct_theme_twitter_link_option',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default' => 'https://www.twitter.com/'
			)
		);


		// LinkedIn
		add_settings_field (
			'linkedIn_link_option',
			'LinkedIn link',
			'linkedIn_link_callback',
			'ct_theme_settings_page',
			'social_media_section'
		);
		register_setting(
			'ct_theme_options',
			'ct_theme_linkedIn_link_option',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default' => 'https://www.linkedin.com/'
			)
		);


		// Pinterest
		add_settings_field (
			'pinterest_link_option',
			'Pinterest link',
			'pinterest_link_callback',
			'ct_theme_settings_page',
			'social_media_section'
		);
		register_setting(
			'ct_theme_options',
			'ct_theme_pinterest_link_option',		
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default' => 'https://www.pinterest.com/'
			)
		);

	}
	//callback for displaying setting description

	function reading_section_description(){
	/* 	echo '<p>Settings</p>'; */
	}

	//callback for displaying setting description

	function social_media_section_description(){
		/* echo '<p>Settings</p>'; */
	}

	 // for logo image
	function logo_image_callback(){ ?>
	<div class="img-select-container" style="display: flex; align-items: center; gap: 40px;">
		<input id="upload_image" type="hidden" size="36" name="ct_theme_logo_image_option" value=<?php echo get_option('ct_theme_logo_image_option'); ?> /> 
		<input type="button"  name="ct_theme_logo_image_option" class="button button-secondary upload-button" value="Upload Logo Image" data-group="1">
		<img id="file-preview"  name="ct_theme_logo_image_option" class="img-preview" alt="Logo Image" src="<?php echo get_option('ct_theme_logo_image_option'); ?>"/>
	</div>
		<?php 

		wp_enqueue_media(); ?>

		<script>
			jQuery(document).ready( function($) {

				// Uploading files
				var mediaUploader;

				$('.upload-button').on('click', function( event ){

					event.preventDefault();

					var buttonID = $(this).data('id');

					// If the media frame already exists, reopen it.
					if ( mediaUploader ) {
						// Open frame
						mediaUploader.id = buttonID;
						mediaUploader.open();
						return;
					}

					// Create the media frame.
					mediaUploader = wp.media.frames.file_frame = wp.media({
						id: buttonID,
						title: 'Select a file to upload',
						button: {
							text: 'Select',
						},
						multiple: false // Set to true to allow multiple files to be selected
					});

					// When an image is selected, run a callback.
					mediaUploader.on( 'select', function() {
						
						attachment = mediaUploader.state().get('selection').first().toJSON();
				
						$( '#file-preview' ).attr( 'src', attachment.url ).css( 'width', 'auto' );

						var img_logo = attachment.url;
						console.log(attachment.url );

						$.ajax({
							url: ajaxurl,	
							data: {
								img_logo: img_logo,
								action: 'ct_theme_img_logo_save',								
							},
							type: 'post',
				
							success: function (result, textstatus) {
								console.log(result);
								console.log('sucess');
								
							},
							error: function (result) {
								console.log(result);
								console.log('fail');
							},
				
						})

					});


					// Finally, open the modal
					mediaUploader.open();
				});

/* 				$('#ct_theme_form').submit( function(e){

					}) */

			});
		</script>
		

<?php
	}

	/**
	 * Ajax actions
	 */

	add_action('wp_ajax_ct_theme_img_logo_save', 'ct_theme_img_logo_save_option' );
	


	function ct_theme_img_logo_save_option(){

		$img_logo = $_POST['img_logo'];

	   update_option('ct_theme_logo_image_option' , $img_logo);

	   wp_die();
	}

	// for phone number
	function phone_number_callback(){
		echo '<input type="tel" name="ct_theme_phone_number_option"  placeholder="+212624863845" value="'. get_option('ct_theme_phone_number_option') .'">';
	}

	// for adress information
	function adress_information_callback(){
		echo '<input type="text" name="ct_theme_adress_information_option" placeholder="Casablanca" value="'. get_option('ct_theme_adress_information_option') .'">';
	}

	// for phone number
	function fax_number_callback(){
		echo '<input type="tel"  name="ct_theme_fax_number_option" placeholder="+212624863845" value="'. get_option('ct_theme_fax_number_option') .'">';
	}

	/**
	 * callbacks for displaying the setting fields of Social Media Sections
	 */

	// facebook
	function facebook_link_callback(){
		echo '<input type="url" name="ct_theme_facebook_link_option" placeholder="https://www.facebook.com/" value="'. get_option('ct_theme_facebook_link_option') .'">';
	}
	// Twitter
	function twitter_link_callback(){
		echo '<input type="url" name="ct_theme_twitter_link_option" placeholder="https://www.twitter.com/" value="'. get_option('ct_theme_twitter_link_option') .'">';
	}
	// LinkedIn
	function linkedIn_link_callback(){
		echo '<input type="url" name="ct_theme_linkedIn_link_option" placeholder="https://www.linkedIn.com/" value="'. get_option('ct_theme_linkedIn_link_option') .'">';
	}
	// Pinterest
	function pinterest_link_callback(){
		echo '<input type="url" name="ct_theme_pinterest_link_option" placeholder="https://www.pinterest.com/" value="'. get_option('ct_theme_pinterest_link_option') .'">';
	}


	add_action('admin_init', 'ct_theme_settings_sections' );
	

