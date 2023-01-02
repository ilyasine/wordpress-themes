<?php

global $site_config,$site_config_js;
$site_config = array_merge($site_config,
	array(
		'has_post_push_by_cat_mise_en_avant' => true,
		'locking_posts_types'=>['post','article-magazine'],
		'popular_posts_locking' => true,
		'has_products' => true,
		'separate_css' => true,
		'separate_mobile_css' => true,
		"facebook_url" => SITE_SCHEME."://fr-fr.facebook.com/PleineVie/",
		"twitter_url" => SITE_SCHEME."://twitter.com/pleine_vie?ref_src=twsrc%5Egoogle%7Ctwcamp%5Eserp%7Ctwgr%5Eauthor",
		'mobile_show_carousel_control' => true,
		'enable_censhare_export' => true,
		'post_item_date_before_title' => true,
		'category_tourisme' => 'tourisme',
		'category_loisirs' => 'loisirs',
		'widget_ops_univers_slug' =>'univers_feminin',
		'hide_block_autor_date_single' => true,
		'hide_comment_template' => false ,
		'addthis_bubble_comment' => true ,
		'widget_ops_site_centrale' => is_dev() ? 'mariefrance.rw.webpick.info' : 'www.mariefrance.fr',		
		'addthis_button_copylink' => true ,
		'add_clipboard_js' => true,
		'hide_related_posts_author' => true,
		'favicon'=> get_bloginfo('stylesheet_directory') . '/pleinevie/assets/images/favicon/favicon.ico?v=1', 
		 'favicon32'=> get_bloginfo('stylesheet_directory') . '/pleinevie/assets/images/favicon/favicon-32x32.png?v=2', 
		 'favicon48'=> get_bloginfo('stylesheet_directory') . '/pleinevie/assets/images/favicon/favicon-48x48.png?v=2', 
		 'favicon64'=> get_bloginfo('stylesheet_directory') . '/pleinevie/assets/images/favicon/favicon-64x64.png?v=2', 
		 'favicon128'=> get_bloginfo('stylesheet_directory') . '/pleinevie/assets/images/favicon/favicon-128x128.png?v=2', 
		'posts_per_page_archive' => 4,
		'posts_per_page_archive_sub_cat' => 12,
		'option_tags_metabox' => true,
		'pleinevie_category_template' => true,
		'show_popular_date_above_title' => true,
		'is_pleinevie' => true,
		"google_analytics_id" => "UA-1658521-9",
		"test_google_analytics_id" => "UA-52051220-33",
		"other_google_analytics_ids" => ["UA-192639368-1"],
		'new_ga_lp_girendiere' => "AW-304653915",
		'cheetah_nl' => array(
			'apiPostId'		=> 141,
			'prefix' => 'pleinevie',
			'acronym'=> 'pleinevie',
			'optin' 		=> array(),
			'categorie_cheetah' => 'pleinevie'
		),
		'hide_breadcrumb_in_page'=>true,
		'remove_show_ops_posts_single'=> true,
		'hide_homeMoreArticles_title' => true,
		'hide-block-tv' => true,
		'hide_youtube_case'=> true,
		'hide_last_posts' => true,
		'show_header_sub_menu' => true,
		'lire_aussi_widget_title_clickable' => true,
		"ga_api_id" => 'ga:3038960',
		"test_ga_api_id" => 'ga:252685336',
	    'generic_custom_css_amp' => 'pleinevie/assets/stylesheets/custom_amp_style.css',
	    'facebook_app_id'=> '139068506246475',
	    'logo_amp_footer' => STYLESHEET_DIR_URI . '/pleinevie/assets/images/logo_white.png',
	    'logo_am_amp' => array(
			'url' => STYLESHEET_DIR_URI . '/pleinevie/assets/images/logo_white.png',
		),
		'custom_amp_fonts' => array(
			'https://fonts.googleapis.com/css2?family=Roboto+Condensed&display=swap',
			'https://fonts.googleapis.com/css2?family=Open+Sans',
			'https://fonts.googleapis.com/css2?family=Open+Sans:ital@0;1'
		),

		'activate_mobilefeed' => true, // activer le flus app mobile
		'feed_like_ezpublish' => true, // activer le identique au ezpublish
		'hide_feed_mobile_car_attribute' => true,
		// Dailymotion nouveau player
		'nouveau_daily_player_desktop'=>'https://geo.dailymotion.com/libs/player/x2v1t.js',
		'nouveau_daily_player_mobile'=>'https://geo.dailymotion.com/libs/player/x2v1s.js',
		'validate_limit_characters_field_ninjaform' => true,
		'adom_data_compaign' => 'b33f9663-c112-448f-b0f3-dad1c3e40eb7',
		'poool_analytics_event' => is_dev() ? 'UA-52051220-38' : 'UA-212282907-1',
		'ez_redirect_old_posts' => true,
	)
);

$site_config_js['poool_analytics_event'] = $site_config['poool_analytics_event'];

$site_config_js['new_ga_lp_girendiere'] = array('ID'=>'AW-304653915', 'text_ID'=>'mLv8CLKqyvICENvMopEB');

if ( !defined('_LOCKING_ON_')){
	define('_LOCKING_ON_',true);	
}

if ( !defined('_LOCKING_ON_'))
	define('_LOCKING_ON_',true);

$site_config['locking']= array(
	'home' => array(
		'carousel' => array(
			'desc' => 'Article diapo',
			'title' => 'SlideShow', 
			'nb_pos' => 5,
			'args' => array(
				'post_type'=>'post'
			) 
		),

		'acceuil' => array(
			'desc' => 'Accueil',
			'title' => 'Accueil sur la home',
			'nb_pos' => 4,
			'args' => array(
				'post_type'=> apply_filters('post_type_filter', array('post')),
			) 
		),
		'title'=>"Page d'accueil" //Titre de la page doit être un String
	),
	'widget' => array(
		'popular_posts' => array(
			'desc' => 'Post position ',
			'title' => 'Les articles les + lus', 
			'nb_pos' => 5,
			'args' => array(
				'post_type'=> $site_config['locking_posts_types'],
			) 
		),
		'title'=>"Sidebar général",
	),
);

$menu_items = get_data_from_cache('Header_nav', 'block_header', 3600, function(){
	return wp_get_nav_menu_items( 'menu_header' );
});

if ($menu_items){
    foreach ($menu_items as $menu_item) {
        if ($menu_item->type=='taxonomy' && $menu_item->post_parent== 0 ) {
            $nb_pos =  4;
            $site_config['locking']['last_post_by_category']['bloc_rubrique_'.$menu_item->object_id] = 
            [
                'desc' => 'Bloc rubrique '.$menu_item->title,
                'title' => 'BLOC RUBRIQUE '.$menu_item->title,
                'nb_pos' => $nb_pos,
                'args' => array(
                    'post_type'=> $site_config['locking_posts_types'],
                ) 
            ];
        }
    }
}