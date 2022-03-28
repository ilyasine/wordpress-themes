<?php

    if (isset($_SERVER['HTTP_USER_AGENT']) && ( (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false ) || (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident/7.0; rv:11.0') !== false) ) ){ 
        // write you code here for IE   
        echo "<script>
        if(/MSIE \d|Trident.*rv:/.test(navigator.userAgent)) {
          window.location = 'microsoft-edge:' + window.location;
          setTimeout(function() {
            window.location = 'https://go.microsoft.com/fwlink/?linkid=2135547';
          }, 1);
        }
      </script>";
        echo '<div class="iemsg">هذا المتصفح غير مدعوم المرجو ٱستعمال متصفح آخر </div>';
    }

    define('THEME_IMG_PATH', get_stylesheet_directory_uri() . '\assets\images' );
    define('favicon_dir', get_stylesheet_directory_uri() . '/assets/favicon_package' );
    
    add_theme_support( 'post-thumbnails' );

    if ( has_post_thumbnail() ) {
        the_post_thumbnail();
    }

    add_filter('wp_get_attachment_image_attributes', function($attr){
        $attr['alt'] = get_the_title();
        $attr['title'] = get_the_title();
        return $attr;
    });

    // add_theme_support( 'title-tag' ) ;

function main_files() { 
    wp_enqueue_style('styles',get_stylesheet_uri());
    wp_enqueue_style('font-awesome', get_theme_file_uri('assets/css/fontawesome.css'));
    wp_enqueue_script('main-js', get_theme_file_uri('assets/js/main.js'),NULL,'1.0',true);
    wp_enqueue_script('clock-js', get_theme_file_uri('assets/js/clock.js'),NULL,'2.0',true);
    wp_deregister_script( 'wp-embed' );
}

add_action('wp_enqueue_scripts','main_files');

remove_action('wp_head', 'wp_generator');

// preload css 

add_filter( 'style_loader_tag',  'preload_css', 10, 2 );

function preload_css( $html, $handle ){
          
        $html = str_replace("rel='stylesheet'", "rel='preload stylesheet' as='style' ", $html);
              
    return $html;
}

// preload js 

add_action( 'wp_head',  'preload_js', 1 );

function preload_js() {

    global $wp_scripts;

    $jquery_core = $wp_scripts->registered["jquery-core"] ;
    $jquery_migrate = $wp_scripts->registered["jquery-migrate"] ;

    if ( is_Home() || is_page('contact') || is_post_type_archive('audio') ) :

        if( $jquery_core || $jquery_migrate) {

            $jcoresource = $jquery_core->src . ($jquery_core->ver ? "?ver={$jquery_core->ver}" : "");

            $jmigratesource = $jquery_migrate->src . ($jquery_migrate->ver ? "?ver={$jquery_migrate->ver}" : "");

            echo "<link rel='preload' href='{$jcoresource}' as='script'/>\n";

            echo "<link rel='preload' href='{$jmigratesource}' as='script'/>\n";
        }  

    foreach ($wp_scripts->queue as $handle) {
      $script = $wp_scripts->registered[$handle];

      $source = $script->src . ($script->ver ? "?ver={$script->ver}" : "");
  
      echo "<link rel='preload' href='{$source}' as='script'/>\n";
    }
    endif;
  }


/* ====================================================
==================== responsive images ===================
==================================================== */

    update_option( 'thumbnail_size_w', 100 );
    update_option( 'thumbnail_size_h', 80 );
    update_option( 'medium_large_size_w', 768 );
    update_option( 'medium_large_size_h', 768 );

    remove_image_size('1536x1536');
    remove_image_size('2048x2048');

  add_filter('intermediate_image_sizes', function($sizes) {
    return array_diff($sizes, ['medium','large']);
});

/* var_dump( get_intermediate_image_sizes() ); */

  function hide_large_media_size() {

    $screen = get_current_screen();

    // Media Options Page Only
    if( 'options-media' !== $screen->id ) {
        return;
    }
  ?>
    <style>
         table.form-table, h2.title, h2.title+p {
             display: none;
            }
    </style>

  <?php

}
add_action( 'admin_print_styles', 'hide_large_media_size' );

/* ====================================================
==================== responsive images ===================
==================================================== */


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
    }
}

add_action( 'wp_enqueue_scripts','singlepost');

function singleaudio()
{
    if ( is_singular('audio') )
    {         
        wp_enqueue_script('audio-js', get_theme_file_uri('assets/js/single-audio.js'),NULL,'1.0',true);
        wp_enqueue_style('audio-css', get_theme_file_uri('assets/css/single-audio.css'));
    }
}

add_action( 'wp_enqueue_scripts','singleaudio');

function singlevideo()
{
    if ( is_singular('video') )
    {        
        wp_enqueue_script('video-js', get_theme_file_uri('assets/js/single-video.js'),NULL,'1.0',true);
        wp_enqueue_style('video-css', get_theme_file_uri('assets/css/single-video.css'));
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
    .plugin-version-author-uri ,
     #udmupdater_not_connected ,
      .updraft-ad-container ,
       .plugin-update-tr.active ,
        #toplevel_page_edit-post_type-acf-field-group > ul > li:nth-child(5) ,
         #wpcontent > div.acf-admin-toolbar > a:nth-child(4),
         #wpbody-content #rpm-no-handler-notice,
         .update-nag
          {
          display:none ;
      }
    </style>
    ';
}
add_action('admin_head', 'remove_wp_optimize_notif');

//======================================================================
// Add post state to audio and videos pages
//======================================================================

add_filter( 'display_post_states', 'ecs_add_post_state', 10, 2 );

function ecs_add_post_state( $post_states, $post ) {

	if( $post->post_name == 'audios' ) {
		$post_states[] = 'Audios Pages';
	}
    if( $post->post_name == 'videos' ) {
		$post_states[] = 'Videos Pages';
	}

   /*  print_r($post->post_name); */

	return $post_states;
}


// Remove update notifications

function remove_update_notifications( $value ) {

    if ( isset( $value ) && is_object( $value ) ) {

        unset( $value->response[ 'real-media-library/index.php' ] );
        unset( $value->response[ 'real-physical-media/index.php' ] );
        unset( $value->response[ 'advanced-custom-fields-pro/acf.php' ] );
        unset( $value->response[ 'wp-optimize-premium/wp-optimize.php' ] );
        /* unset( $value->response[ 'akismet/akismet.php' ] ); */
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

$default_image=" ". get_theme_file_uri('/assets/images/open_graph_image.jpg') ." "; //replace this with a default image on your server or an image in your media library

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
        echo '<title>محمد الإدريسي | الموقع الرسمي</title>';
    }
    if ( is_Home() ) {
        echo '<meta property="og:title" content="محمد الإدريسي | أخبار و مواعيد"/>';
        echo '<title>أخبار و مواعيد</title>';
    }
    if ( is_post_type_archive('audio') ) {
        echo '<meta property="og:title" content=" محمد الإدريسي | صوتيات"/>';
        echo '<title> صوتيات </title>';
    }
    if ( is_post_type_archive('video') ) {
        echo '<meta property="og:title" content=" محمد الإدريسي | مرئيات"/>';
        echo '<title> مرئيات </title>';
    }
    if ( is_page('contact')) {
        echo '<meta property="og:title" content="تواصل مع محمد الإدريسي"/>';
        echo '<title>كن على تواصل</title>';
    }
    if ( is_page('about-us')) {
        echo '<meta property="og:title" content="محمد الإدريسي | السيرة الذاتية"/>';
        echo '<title>عن محمد الإدريسي</title>';
    }
    if (is_singular('post')){
        echo "<title>". wp_title($sep='أخبار و مواعيد | ', false) . "</title>";
    }
    if (is_singular('audio')){
        echo "<title>". wp_title($sep='صوتيات | ', false) . "</title>";
    }
    if (is_singular('video')){
        echo "<title>". wp_title($sep='مرئيات | ', false) . "</title>";
    }
    if(is_tax('audio-category')){
        echo "<title>" . single_term_title('صوتيات | ' , false) . "</title>";
    }
    if(is_tax('video-category')){
        echo "<title>" . single_term_title('مرئيات | ' , false) . "</title>";
    }
    if(is_page('terms-and-conditions')){
        echo '<title>Terms and Conditions</title>';
    }
    if(is_page('privacy-policy')){
        echo '<title>سياسة الخصوصية</title>';
    }
    if(is_category()){
        echo "<title>". wp_title($sep = '', false) ."</title>";
    }


echo "
";
}
add_action( 'wp_head', 'insert_fb_in_head', 5 );

add_filter('body_class', 'remove_body_classes');
function remove_body_classes( $classes ) {
    $classes = array_diff($classes, ['page']);
    return $classes;
}


function mohamedelidrissi_login_logo() { 
    ?> 
    <style type="text/css"> 
        body{ 
            background: linear-gradient(90deg, #0c8601, #258606) !important;
            position: relative; 
            scroll-behavior: smooth;
            min-height: 100vh;
            overflow-x: hidden !important;  
        }
        body:before, body:after {
            --p: 0;
            --s: calc(1 - 2*var(--p));
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: -100000;
            -webkit-mask: var(--m);
            mask: var(--m);
            content: "";
            background: linear-gradient(calc(var(--s)*45deg), transparent calc(50% - 1px), #6cb953 0, #004c0900 calc(50% + 1px), transparent 0) 0 0/ 0.5em 0.5em;
            --m: linear-gradient(red 50%, transparent 0) 0 0/ 4em 4em, linear-gradient(90deg, red 50%, transparent 0) calc(var(--p)*2em) 0/ 4em 4em;
            -webkit-mask-composite: xor;
            mask-composite: exclude;           
        }
        body:after {
            --p: 1 ;
        }
        body.login div#login h1 a {
            background-image: url("<?php echo content_url('/themes/mohamedelidrissi/assets/images/logoname.webp') ?>");
            background-size: 90px;
            box-shadow: none;
            width: auto;
            height: 90px;
        } 
        .login form {
            border-radius: 7px;
        }
        #login a:focus{
            box-shadow:none ;
        }
        .login #backtoblog a, .login #nav a, a.privacy-policy-link {
            color: white !important;
            outline:none;
        }
        .login #backtoblog a:hover, .login #nav a:hover, .login h1 a:hover , a.privacy-policy-link:hover {
            color: wheat !important;
        }
        .login .privacy-policy-page-link {
            text-align: center;
            width: 100%;
            margin: 5em 0 0em !important;
        }
        #login {
            padding-top: 7vh !important;
            width: auto !important;
            max-width: 320px;
        }
        #login input{
            border-color: #418f14;    
        }
        #login input:focus, #login button:focus{
            box-shadow: 0 0 0 1px #459720; 
            border-color: #418f14;
        }
        #login .submit input, .login .admin-email__actions .button-primary, .login .admin-email__actions .button-primary:hover{
            background-color: #418f14;
            border-color: #418f14;    
        }
        #login .admin-email__actions-primary>a.button{
            color: #418f14;
            border-color: #418f14; 
        }
        #login a{
            color: #418f14;
        }
        #login .submit input:focus{
            box-shadow: 0 0 0 1px #fff, 0 0 0 3px #459720;   
        }
        #login .submit input:hover{
            background-color: #459720;
            border-color: #459720;    
        }
        #login button{
            color: #418f14;
        }
        #login input[type=checkbox]:checked::before {
            filter: invert(42%) sepia(93%) saturate(1352%) hue-rotate(87deg) brightness(77%) contrast(119%);
        }
        .login #login_error, .login .message, .login .success {
            border-left: 4px solid #42cc04 !important;
        }
    
    </style>
     <?php 
    } 
    
add_action( 'login_enqueue_scripts', 'mohamedelidrissi_login_logo' );

add_filter( 'login_headerurl', 'mohamedelidrissi_login_url');

function mohamedelidrissi_login_url($url) {

    return  get_site_url(); 

}
    
add_filter('site_status_tests', function (array $test_type) {
    unset(
      /* $test_type['direct']['php_version'], */
      $test_type['direct']['theme_version'],
      $test_type['direct']['plugin_version'],
      $test_type['async']['background_updates']
    );
    return $test_type;
  }, 10, 1);

 




