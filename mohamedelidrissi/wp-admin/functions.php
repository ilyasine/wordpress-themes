<?php

    define('THEME_IMG_PATH', get_stylesheet_directory_uri() . '\assets\images' );
    
    add_theme_support( 'post-thumbnails' );

    if ( has_post_thumbnail() ) {
        the_post_thumbnail();
    }

    // add_theme_support( 'title-tag' ) ;

function main_files() { 
    wp_enqueue_style('styles',get_stylesheet_uri());
    wp_enqueue_style('font-awesome', get_theme_file_uri('assets/css/fontawesome.css'));
    wp_enqueue_script('main-js', get_theme_file_uri('assets/js/main.js'),NULL,'1.0',true);
    wp_enqueue_script('clock-js', get_theme_file_uri('assets/js/clock.js'),NULL,'2.0',true);
}

add_action('wp_enqueue_scripts','main_files');

  
function about()
{
    if ( is_page('about-us') )
    {  
        wp_enqueue_script('about-js', get_theme_file_uri('assets/js/about.js'),NULL,'1.0',true);
        wp_enqueue_style('about-css', get_theme_file_uri('assets/css/about.css'));
    }
}

add_action( 'wp_enqueue_scripts','about'); 

function contact()
{
    if ( is_page('contact') )
    {  
        wp_enqueue_script('contact-js', get_theme_file_uri('assets/js/contact.js'),['jquery'], null, true);
        wp_enqueue_style('contact-css', get_theme_file_uri('assets/css/contact.css'));
        wp_enqueue_script('ajax_form', get_theme_file_uri('assets/js/contact_ajax.js'), ['jquery'], null, true);
        wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js?render=6LdcxhrrmCTdLqs67V5Ps5omaHbbapv5as2jspL-');
    }

    wp_localize_script( 'ajax_contact', 'contact_ajax_script', array(
        'nonce'    => wp_create_nonce( 'contact_ajax_nonce' ),
        'ajax_url' => admin_url( 'admin-ajax.php' )
    ));
}

add_action( 'wp_enqueue_scripts','contact', 1 );

//Define AJAX URL
function register_ajaxurl() {

    echo '<script type="text/javascript">
            var ajaxurl = "' . admin_url('admin-ajax.php') . '";
          </script>';
 }
 add_action('wp_head', 'register_ajaxurl');

// ajax contact form

require_once( get_template_directory() . '/send_form.php' );

add_action('wp_ajax_nopriv_contact_form','ajax_contact_form');
add_action('wp_ajax_contact_form','ajax_contact_form');


function privacy()
{
    if ( is_page('privacy-policy') )
    {          
        wp_enqueue_style('privacy-css', get_theme_file_uri('assets/css/privacy.css'));
    }
}

add_action( 'wp_enqueue_scripts','privacy');

function terms()
{
    if ( is_page('terms-and-conditions') )
    {          
        wp_enqueue_style('terms-css', get_theme_file_uri('assets/css/terms.css'));
    }
}

add_action( 'wp_enqueue_scripts','terms');

function error_page()
{
    if ( is_404() )
    {         
        wp_enqueue_style('error-css', get_theme_file_uri('assets/css/404.css'));
        wp_enqueue_script('error-js', get_theme_file_uri('assets/js/404.js'),NULL,'1.0',true);
    }
}

add_action( 'wp_enqueue_scripts','error_page');

function blog()
{
    if ( is_Home() || is_category() || is_search() )
    {  
        wp_enqueue_script('blog-js', get_theme_file_uri('assets/js/blog.js'),NULL,'1.0',true);
        wp_enqueue_style('blog-css', get_theme_file_uri('assets/css/blog.css'));
        wp_enqueue_script('ajax_filter', get_theme_file_uri('assets/js/ajax_blog_filter.js'), ['jquery'], null, true);

        wp_localize_script( 'ajax_filter', 'blog_filter', array(
            'nonce'    => wp_create_nonce( 'blog_filter' ),
            'ajax_url' => admin_url( 'admin-ajax.php' )
        ));
    } 
}


//blog ajax filter

require_once( get_template_directory() . '/blog_filter.php' );


add_action('wp_ajax_nopriv_filter_blogs','filter_ajax');
add_action('wp_ajax_filter_blogs','filter_ajax');

add_action( 'wp_enqueue_scripts','blog');


//audio ajax filter

require_once( get_template_directory() . '/audio_filter.php' );

add_action('wp_ajax_nopriv_filter_audios','audio_filter_ajax');
add_action('wp_ajax_filter_audios','audio_filter_ajax');


//video ajax filter

require_once( get_template_directory() . '/video_filter.php' );

add_action('wp_ajax_nopriv_filter_videos','video_filter_ajax');
add_action('wp_ajax_filter_videos','video_filter_ajax');


function frontpage()
{
    if ( is_front_page() )
    {  
        wp_enqueue_script('front-page-js', get_theme_file_uri('assets/js/front-page.js'),NULL,'1.0',true);
        wp_enqueue_style('front-page-css', get_theme_file_uri('assets/css/front-page.css'));        
    }
}

add_action( 'wp_enqueue_scripts','frontpage');

//     Remove TEXT EDITOR form page 

add_action('init', 'init_remove_support',100);
function init_remove_support(){
    $post_type = 'page';
    remove_post_type_support( $post_type, 'editor');
}

function audios()
{
    if ( is_post_type_archive('audio') || is_tax('audio-category'))
    {         
        wp_enqueue_style('audio-css', get_theme_file_uri('assets/css/audio.css'));
        wp_enqueue_script('audio-js', get_theme_file_uri('assets/js/audio.js'),NULL,'1.0',true);
        wp_enqueue_script('ajax-audio-js', get_theme_file_uri('assets/js/ajax_audio_filter.js'),['jquery'] ,NULL ,true);      
    }
}

add_action( 'wp_enqueue_scripts','audios');

function singlepost()
{
    if ( is_single() )
    {       
        wp_enqueue_style('post-css', get_theme_file_uri('assets/css/post.css'));
        // wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js');
    }
}

add_action( 'wp_enqueue_scripts','singlepost');

function singleaudio()
{
    if ( is_singular('audio') )
    {         
        wp_enqueue_script('audio-js', get_theme_file_uri('assets/js/single-audio.js'),NULL,'1.0',true);
        wp_enqueue_style('audio-css', get_theme_file_uri('assets/css/single-audio.css'));
        // wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js');       
    }
}

add_action( 'wp_enqueue_scripts','singleaudio');

function singlevideo()
{
    if ( is_singular('video') )
    {        
        wp_enqueue_script('video-js', get_theme_file_uri('assets/js/single-video.js'),NULL,'1.0',true);
        wp_enqueue_style('video-css', get_theme_file_uri('assets/css/single-video.css'));
        // wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js');
    }
}

add_action( 'wp_enqueue_scripts','singlevideo');

function videos()
{
    if ( is_post_type_archive('video') || is_tax('video-category'))
    {  
        wp_enqueue_script('video-js', get_theme_file_uri('assets/js/video.js'),NULL,'1.0',true);
        wp_enqueue_style('video-css', get_theme_file_uri('/assets/css/video.css')); 
        wp_enqueue_script('ajax-video-js', get_theme_file_uri('assets/js/ajax_video_filter.js'),['jquery'] ,NULL ,true);       
    }
}

add_action( 'wp_enqueue_scripts','videos');

// edit admin dashboard 

function remove_wp_optimize_notif(){
    echo'
    <style>
    .plugin-version-author-uri , #udmupdater_not_connected , .updraft-ad-container , .plugin-update-tr.active , #toplevel_page_edit-post_type-acf-field-group > ul > li:nth-child(5) , #wpcontent > div.acf-admin-toolbar > a:nth-child(4) {
          display:none ;
      }
    </style>
    ';
}
add_action('admin_head', 'remove_wp_optimize_notif');


// resposive facebook widget

function resposive_facebook_widget(){
   if( is_post_type_archive('audio') || is_tax('audio-category') 
   || is_post_type_archive('video') || is_tax('video-category')
   || is_archive() || is_category() ) :
    echo"
    <script type='text/javascript'>
    let TIMEOUT = null;
    window.onresize = () => {
      if (TIMEOUT === null) {
        TIMEOUT = window.setTimeout(() => {
          TIMEOUT = null;
          //fb_iframe_widget class is added after first FB.FXBML.parse()
          //fb_iframe_widget_fluid is added in same situation, but only for mobile devices (tablets, phones)
          //By removing those classes FB.XFBML.parse() will reset the plugin widths.

          document.querySelector('.fb-page').classList.remove('fb_iframe_widget');
          document.querySelector('.fb-page').classList.remove('fb_iframe_widget_fluid')
          FB.XFBML.parse();
        }, 300);
      }
    }
    </script>
    ";
    endif;
    
}
add_action('wp_footer', 'resposive_facebook_widget');


// Remove update notifications

function remove_update_notifications( $value ) {

    if ( isset( $value ) && is_object( $value ) ) {

        unset( $value->response[ 'wp-media-folder/wp-media-folder.php' ] );
        unset( $value->response[ 'advanced-custom-fields-pro/acf.php' ] );
        unset( $value->response[ 'wp-optimize-premium/wp-optimize.php' ] );
    }

    return $value;
}
add_filter( 'site_transient_update_plugins', 'remove_update_notifications' );

// comments

 function mytheme_comment($comment, $args, $depth) {
    if ( 'div' === $args['style'] ) {
        $tag       = 'div';
        $add_below = 'comment';
    } else {
        $tag       = 'li';
        $add_below = 'div-comment';
    }
    ?>
<div class="comment-div">
    <div class="comment-author vcard">
        <?php if ( $args['avatar_size'] != 0 ) echo get_avatar( $comment, $args['avatar_size'] ); ?>
        <?php printf( __( '<cite class="fn">%s</cite> <span class="says">علق:</span>' ), get_comment_author_link() ); ?>
    </div>
     <div class="meta-top-comment">
    <?php if ( $comment->comment_approved == '0' ) : ?>
         <em class="comment-awaiting-moderation"><?php _e( 'تعليقك في ٱنتظار الموافقة عليه .' ); ?></em>
         
    <?php endif; ?>

    <div class="comment-meta commentmetadata"><a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ); ?>">
        <?php
        /* translators: 1: date, 2: time */
        printf( __(' يوم %1$s عند %2$s'), get_comment_date(),  get_comment_time() ); ?></a><?php edit_comment_link( __( '(تعديل)' ), '  ', '' );
        ?>
    </div>
    </div>
   
    <?php comment_text(); ?>

    <div class="reply">
        <?php comment_reply_link( array_merge( $args, array( 'add_below' => $add_below, 'reply_text' => 'رد', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
    </div>
    <?php if ( 'div' != $args['style'] ) : ?>
       
    </div>

    <?php endif; 
    }


    /**
 * Google recaptcha add before the submit button
 */
/* function add_google_recaptcha($submit_field) {
    $submit_field['submit_field'] = '<div class="g-recaptcha" data-sitekey="6LdcxhrrmCTdLqs67V5Ps5omaHbbapv5as2jspL-"></div><br>' . $submit_field['submit_field'];
    return $submit_field;
}
if (!is_user_logged_in()) {
    add_filter('comment_form_defaults','add_google_recaptcha');
} */
 
/**
 * Google recaptcha check, validate and catch the spammer
 */
/* function is_valid_captcha($captcha) {
$captcha_postdata = http_build_query(array(
                            'secret' => '6LfFkt4aAAAAALLjZXcCVVhasOymHG-EixnbeRjm',
                            'response' => $captcha,
                            'remoteip' => $_SERVER['REMOTE_ADDR']));
$captcha_opts = array('http' => array(
                      'method'  => 'POST',
                      'header'  => 'Content-type: application/x-www-form-urlencoded',
                      'content' => $captcha_postdata));
$captcha_context  = stream_context_create($captcha_opts);
$captcha_response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify" , false , $captcha_context), true);
if ($captcha_response['success'])
    return true;
else
    return false;
}
 
function verify_google_recaptcha() {
$recaptcha = $_POST['g-recaptcha-response'];
if (empty($recaptcha))
    wp_die( __("<b>ERROR:</b> please select <b>I'm not a robot!</b><p><a href='javascript:history.back()'>« Back</a></p>"));
else if (!is_valid_captcha($recaptcha))
    wp_die( __("<b>Go away SPAMMER!</b>"));
}
if (!is_user_logged_in()) {
    add_action('pre_comment_on_post', 'verify_google_recaptcha');
} */


    // popular posts

function global_popular_posts($post_id) {
	$count_key = 'popular_posts';
	$count = get_post_meta($post_id, $count_key, true);
	if ($count == '') {
		$count = 0;
		delete_post_meta($post_id, $count_key);
		add_post_meta($post_id, $count_key, '0');
	} else {
		$count++;
		update_post_meta($post_id, $count_key, $count);
	}
}


    //To keep the count accurate, lets get rid of prefetching
    remove_action( 'wp_head','adjacent_posts_rel_link_wp_head', 10, 0);

function wpb_set_post_views($post_id) {
	
	if (empty($post_id)) {
		global $post;
		$post_id = $post->ID;
	}
	global_popular_posts($post_id);
}
add_action('wp_head', 'wpb_set_post_views');

function wp_api_encode_acf($data,$post,$context){
	$data['meta'] = array_merge($data['meta'],get_fields($post['ID']));
	return $data;
}

//Blog Gallery


if( function_exists('acf_add_options_page') ) {
	
    acf_add_options_page(array (

        'page_title'  => 'Gallery',
        'menu_title'  => 'Gallery',
        'menu_slug'  => 'gallery',
        'capabilitty'  => 'edit_posts',
        'icon_url' => 'dashicons-format-gallery', 
        'position' => 4 ,
        
    ));
	
}

//Adding the Open Graph in the Language Attributes
function add_opengraph_doctype( $output ) {
    return $output . ' xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml"';
}
add_filter('language_attributes', 'add_opengraph_doctype');

//Lets add Open Graph Meta Info

function insert_fb_in_head() {
global $post;
// if ( is_single() || is_singular('audio') || is_singular('video') ) //if it is not a post 
//     return;
$default_image=" ". get_theme_file_uri('/assets/images/open_graph_image.jpg') ." "; //replace this with a default image on your server or an image in your media library
echo '<meta name="theme-color" content="#0c8601">';
echo '<meta property="og:site_name" content="الموقع الرسمي للفنان محمد الإدريسي"/>';
echo '<meta property="og:description" content="محمد الإدريسي،  فنان، منشد، مقرئ ، ومؤلف موسيقي مغربي متميز في مجال الأغاني الدينية و الأمداح النبوية"/>';
echo '<meta property="og:type" content="article"/>';
if ( !is_single() || !is_singular()) {
    
    echo '<meta property="og:title" content="' . get_the_title() . '"/>';

    echo '<meta property="og:url" content="' . $_SERVER['REQUEST_URI'] . '"/>';
   
   
    echo '<meta property="og:image" content="' . $default_image . '"/>';
}
else

  {


    $thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium' );
    echo '<meta property="og:image" content="' . esc_attr( $thumbnail_src[0] ) . '"/>';

    }

    if( is_front_page() ) {
        echo '<meta property="og:title" content="محمد الإدريسي | الرئيسية "/>';
    }
    if ( is_Home() ) {
        echo '<meta property="og:title" content="محمد الإدريسي | أخبار و مواعيد"/>';
    }
    if ( is_post_type_archive('audio') ) {
        echo '<meta property="og:title" content=" محمد الإدريسي | صوتيات"/>';
    }
    if ( is_post_type_archive('video') ) {
        echo '<meta property="og:title" content=" محمد الإدريسي | مرئيات"/>';
    }
    if ( is_page('contact')) {
        echo '<meta property="og:title" content="تواصل مع محمد الإدريسي"/>';
    }
    if ( is_page('about-us')) {
        echo '<meta property="og:title" content="محمد الإدريسي | السيرة الذاتية"/>';
    }


echo "
";
}
add_action( 'wp_head', 'insert_fb_in_head', 5 );

function mohamedelidrissi_login_logo() { 
    ?> 
    <style type="text/css"> 
    body.login div#login h1 a {
        background-image: url("<?php get_theme_file_uri('wp-content/themes/mohamedelidrissi/assets/images/logoname.png') ?>");
        padding-bottom: 30px; 
    } 
    </style>
     <?php 
    } 
    
add_action( 'login_enqueue_scripts', 'mohamedelidrissi_login_logo' );


add_filter( 'login_headerurl', 'mohamedelidrissi_login_url');

function mohamedelidrissi_login_url($url) {

    return  get_site_url(); 

}
    

 




