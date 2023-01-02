<?php

require(STYLESHEET_DIR .'/pleinevie/include/functions/omep.php');
require(STYLESHEET_DIR .'/pleinevie/include/functions/site-config.php'); 
require_once (STYLESHEET_DIR .'/pleinevie/include/classes/fiche-post-type.php');

require_once (STYLESHEET_DIR ."/pleinevie/include/functions/ez_import_dossier.php");

if(!is_dev('stop_pv_api_send_leads_18427')) {
	require_once(STYLESHEET_DIR . '/pleinevie/include/functions/ops-residenceseniors-leads.php');
}

add_action('single_after_title', 'change_post_author_date', 10); 
function change_post_author_date(){
	global $post;
	$datef = __( 'j F Y à H:i' ); 
	$date_publish = date_i18n( $datef, strtotime( $post->post_date ) );
	$date_modified = date_i18n( $datef,strtotime( $post->post_modified));

	$time_post_modified = strtotime( $post->post_modified) ;
	$time_post_date = strtotime( $post->post_date) ;
	if( $time_post_modified < $time_post_date){
		$date_modified = $date_publish ;
	}
	
	if (get_post_meta( $post->ID , 'post_auteur_ops' , true ) ) {
		$the_author = get_post_meta( $post->ID , 'post_auteur_ops' , true );
	} else if (get_post_meta( $post->ID , 'post_auteur' , true ) ) {
        $the_author = get_post_meta( $post->ID , 'post_auteur' , true );
    } else {
		$the_author = get_the_author();
	}
	$the_author = apply_filters('article_top_signature', $the_author);
	?>
	<div class="post_intro_info"> 
		<span class="publish_date"> Publié le  <?php  echo $date_publish; ?></span>
		<span class="edited_date"> Mis à jour le  <?php  echo $date_modified; ?></span>

		<span itemprop="author" itemscope="" itemtype="https://schema.org/Person" class="post_author">
			Par <span itemprop="name"><?php echo $the_author ; ?></span>
		</span>
	</div>
	<?php 
}
add_action('wp_enqueue_scripts','sticky_header'); 

function sticky_header(){
	if( !rw_is_mobile() ) {
	wp_enqueue_script('pleinvie_sticky_header', get_stylesheet_directory_uri() . '/pleinevie/assets/javascripts/Desktop_sticky_header.js', NULL, CACHE_VERSION_CDN, true);
	}
}

add_action( 'widgets_init', 'lp_girandiere_register_sidebar' );

function lp_girandiere_register_sidebar(){
	register_sidebar(array(
		'name' => 'LP Girandières',
		'id' => 'form-lp-girandiere',
		'description' => __( 'Pour changer l\'identifant du formulaire Girandière', 'reworldmedia' ),
		'before_widget' => '<div id="form_leads">',
		'after_widget' => '</div>',
		'before_title' => '',
		'after_title' => '',
	));
}
