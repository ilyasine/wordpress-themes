<?php

global $site_config;
$site_config = array_merge($site_config,
    array(
        'separate_css' => true,
        'separate_mobile_css' => true,
        'not_display_items_posts_hp' => false,
        'wrap_homeMoreArticles_head' => true,
        'hide_category_name_inside_item_thumbnail' => true,
        'category_video' => 'agenda',
        'name_second_category' => 'Portfolios',
        'addthis_bubble_comment' => true,
        'addthis_button_copylink' => true,
        'hide_related_posts_author' => true,
        'hide_comment_template' => true,
        'seo_pagination' => true,
        'posts_per_page_archive'=> 4,
        'posts_per_page_archive_sub_cat' => 24,
        // Social config
        "facebook_url" => SITE_SCHEME."://www.facebook.com/ReponsesPhoto",
        "twitter_url"=> SITE_SCHEME."://twitter.com/reponsesphoto",
        "instagram_url"=> SITE_SCHEME."://www.instagram.com/reponsesphoto/",
        "pinterest_url"=>SITE_SCHEME."://www.pinterest.fr/reponsesphotofr/",
        'sharedcount_apikey' => 'c3a1f81053d94a6846c5067d373202fe2b673cb6',
        'recaptcha_publickey'=> '6LewHPsSAAAAAKbmeJ_fOFtWNwNsmTLcB5F-3ld6',
        'recaptcha_privatekey'=> '6LewHPsSAAAAAI5rq8e_WbDwbq17e1qR2-6ipCJQ',
        'addthis_button_shoopit' => false,
        'addthis_button_share_email' => true,
        'social_links_order' => array(
                array('facebook_url','fb'),
                array('twitter_url','tw'),
                array('instagram_url','cam'),
                array('pinterest_url','pint'),

        ),
        'social_links_footer_custom_order'=> array(
            'facebook_url'=>'fb',
            'twitter_url'=>'twitter',
        ),
        'hide_block_autor_date_single' => true,
        'widget_ops_site_centrale' => is_dev() ? 'mariefrance.rw.webpick.info' : 'www.mariefrance.fr',
        'remove_show_ops_posts_single'=> true,
        'mobile_show_carousel_control'=> true,
        'post_item_date_before_title'=>true,
                'locking' => array(
            'category' => array( 
                'carousel' => array(
                    'desc' => 'Article diapo',
                    'title' => 'SlideShow pour la catégorie %cat%', 
                    'nb_pos' => 6,
                    'args' => array(
                        'post_type'=>['post','article-live']
                    ) 
                ),
            ), 
        ),
        'show_header_magazine' => true,
        'show_magazine_subscription_btn' => true,
        'active_diapo_home_rubrique' => true,
        'hide_description_category' => true,
        'hide_search_breadcrumb' => false,
        'hide_top_populare_infos' => false,
        'show_categories_after_image_most_popular' => true,
        'favicon'=> STYLESHEET_DIR_URI . '/reponses-photo/assets/images/favicon/favicon.ico',    
        'favicon16'=> STYLESHEET_DIR_URI . '/reponses-photo/assets/images/favicon/favicon16.png',
        'favicon32'=> STYLESHEET_DIR_URI . '/reponses-photo/assets/images/favicon/favicon32.png',
        'favicon96'=> STYLESHEET_DIR_URI . '/reponses-photo/assets/images/favicon/favicon96.png',
        'favicon_amp'=> STYLESHEET_DIR_URI . '/reponses-photo/assets/images/favicon/favicon.ico',
        'active_bloc_two_posts'=>true,
        'show_cat_description'=>true,
        'pleinevie_category_template'=>true,
        'show_diapo_only_in_first_page' => true,
        'hide_child_cats' => true,
        'integration_ga_reworld' => true,
        'generic_custom_css_amp' => 'reponses-photo/assets/stylesheets/custom_amp_style.css',
        'facebook_app_id'=> '431184393607560',
        'logo_am_amp' => array(
            'url' => STYLESHEET_DIR_URI . '/reponses-photo/assets/images/logo_white.png',
            'width' => '240',
            'height' => '27',
        ),
        'logo_amp_footer' => STYLESHEET_DIR_URI . '/reponses-photo/assets/images/logo_white.png',
        'custom_amp_fonts' => array(
            'https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;700&display=swap',
            'https://fonts.googleapis.com/css2?family=Open+Sans',
            'https://fonts.googleapis.com/css2?family=Open+Sans:ital@0;1'
        ),
        'cheetah_nl' => array(
			'apiPostId'		=> 141,
			'prefix' => 'reponsesphoto',
			'acronym'=> 'reponsesphoto',
			'optin' 		=> array(),
			'categorie_cheetah' => 'reponsesphoto',
		),
        'image_diaporama_link' => true,
        'hide-block-tv' => true,
        'show_post_item_gallery_tag' => true,
        'activate_header_plus_button' => true,     
    )
);