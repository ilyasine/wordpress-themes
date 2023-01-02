<?php 

$taboola_path_loader = 'mondadori-pleinevie';

$pages_types_dfp =array(
	'hp' =>array('habillage', 'inread_adikteev', 'interstitiel', 'masthead_haut', 'mpu_haut', 'vignette_haut', 'mpu_milieu', 'vignette_bas', 'dhtml', 'interstitiel_mobile', 'mobile_1', 'mobile_2', 'mobile_3', 'mobile_4', 'mobile_5', 'mobile_6', 'mobile_7', 'mobile_8', 'mobile_9', 'mobile_10', 'mobile_11', 'mobile_12'),
	'rg'=> array( 'habillage', 'inread_adikteev', 'interstitiel', 'vignette_haut', 'masthead_haut', 'mpu_haut', 'mpu_milieu', 'mpu_bas', 'vignette_bas', 'masthead_bas', 'dhtml', 'interstitiel_mobile', 'mobile_1', 'mobile_2', 'mobile_3', 'mobile_4', 'mobile_5', 'mobile_6', 'mobile_7', 'mobile_8', 'mobile_9', 'mobile_10', 'mobile_11', 'mobile_12','native'),
	'diapo_monetisation'=> array('habillage', 'inread_adikteev', 'interstitiel','vignette_haut', 'masthead_haut', 'mpu_haut', 'mpu_milieu', 'mpu_milieu_2', 'mpu_milieu_3', 'banner_incontent_1', 'banner_incontent_2', 'mpu_bas', 'masthead_bas', 'dhtml', 'interstitiel_mobile', 'mobile_1', 'mobile_2', 'mobile_3', 'mobile_4', 'mobile_5', 'mobile_6', 'mobile_7', 'mobile_8', 'mobile_9', 'mobile_10', 'mobile_11', 'mobile_12','native'),
  );
$plan_tagagge_dfp =  array(
  "hp" => array( 'id' => "home"  ), 
  "divers" => array( 'id' => "rg"),
);
if(rw_is_mobile()){
	$formats_lazyloading = array('mobile_2', 'mobile_3', 'mobile_4', 'mobile_5', 'mobile_6', 'mobile_7', 'mobile_8', 'mobile_9', 'mobile_10', 'mobile_11', 'mobile_12') ;	
}
  
$partners = array (
	'analytics' => array(
		'config' => array(
			'google_analytics_id' => get_param_global("google_analytics_id"),
			'test_google_analytics_id' => get_param_global("test_google_analytics_id"),
			'new_ga_lp_girendiere' => get_param_global("new_ga_lp_girendiere"),
			'other_google_analytics_ids' => get_param_global("other_google_analytics_ids"),
		),
	),
	'widget_ops' => array (
	    'config' => array(
	        'univers_name' =>'Univers Féminin (MF, VPF, BIBA)',
	        'style_path' => STYLESHEET_DIR_URI . '/pleinevie/assets/stylesheets/widget_ops.css',
	        'single_action_show_ops' => '',

    	)
	),      
  	'Avant_widget_OPS' => array(),	
	'dfp_v2' => array (
	 	'config' => array (
	        'dfp_id_account' => '46980923/pleinevie-web',
	        'pages_types' => $pages_types_dfp,
	        "plan_tagagge"=> $plan_tagagge_dfp,
	        'formats_lazyloading' => [],
	        'dfp_native_after_image' => true,
            'condition_to_insert_dfp_mobile_pub' =>  function($index) {
				return $index%3 == 0 ;
			},
	    ),
	),
	'cmp_didomi' => array(),
	'prebid' => array(
		'config' => array(
			'site_folder' => 'pleinevie.fr',
		)
	),
	'taboola' => array(
		'config' => array(
			'path_loader' => $taboola_path_loader,
		)
	),

	'taboola_organique' => array(
		'config' => array(
			'path_loader' => $taboola_path_loader,
		)
	),

	'bliink_article_desktop' => array(
		'config' => array (
			'tag_id'	=> '14fb2d4e-85d2-11e8-a615-0242ac120007',
			'new_tag_id'	=> 'd65099ec-b235-11eb-969f-4a89bc079017',
			'without_site_id' => true,
			'all_articles' => true,
		),
	),
	'bliink_article_mobile' => array(
		'config' => array (
			'tag_id'	=> '14fb2d4e-85d2-11e8-a615-0242ac120007',
			'new_tag_id'	=> 'd65099ec-b235-11eb-969f-4a89bc079017',
			'without_site_id' => true,
			'all_articles' => true,
		),
	),
	'bliink_diapo_desktop' => array(
		'config' => array (
			'tag_id'	=> '14fb2d4e-85d2-11e8-a615-0242ac120007',
			'new_tag_id'	=> 'd65099ec-b235-11eb-969f-4a89bc079017',
			'without_site_id' => true,
			'all_articles' => true,
		),
	),
	'bliink_diapo_mobile' => array(
		'config' => array (
			'tag_id'	=> '14fb2d4e-85d2-11e8-a615-0242ac120007',
			'new_tag_id'	=> 'd65099ec-b235-11eb-969f-4a89bc079017',
			'without_site_id' => true,
			'all_articles' => true,
		),
	),
	'player_sticky' => array(),
	'edisound' => array(),
	'captify' => array (
		'config' => array (
			'captify_id' => '12872',
		),
	),
	'dfp_amp' => array (
		'default_activation' => false,
		'config' => array (
			'amp_dfp_id_account' => '/46980923/pleinevie-amp',
			'sizes' => '320x100,320x50,300x600,300x250,300x100,300x50,160x600',
			'disable_multi_size' => true,
		),
		'callback' => 'init',
	),
	'taboola_amp' => array (
		'default_activation' => false,
		'config' => array (
			'publisher' => $taboola_path_loader,
		),
	),
	'FacebookPixel' => array (
		'default_activation' => 0,
		'action' => array('wp_footer',100, 1),
		'config' => array(
			'facebook_pixel_init_id' => '410004889600609',
			'fbq_pixel_tracks' => array('PageView'),
		),
	),
	'art19' => array(),
	'wysistat' => array(
		'config' => array (
			'wysistat_account' => 'pleinevie',
			'wysistat_amp_account' => 'pleinevie_amp',
		)
	),
	'etx' => array(
		'config' => array(
			'etx_api_key' => 'AhN3WlRgsg6Kim9AAneBmmkv1rDILWS2a6YidPNf',
		)
	),
	'batch' => array (
		'config' => array(
			'apiKey' => '25504BF3C993431183A50C4DDA2F88CF',
			'subdomain' => 'pleinevie1',
			'authKey' => '2.uuYsd4WTe1AAqi71PwLuv6iVLxsFfg5B6PBRpyNIuMg=',
			'vapidPublicKey' => 'BEDbJCPifs/8mH7mp5DE26pNklF4ZUpiabhgKVeCh7IvCVwELa07XiCznCnC8IGzbCoZIelxthDxvNqGcZ1toGw=',
			'defaultIcon' => STYLESHEET_DIR_URI . '/pleinevie/assets/images/logo-batch.png',
			'smallIcon' => STYLESHEET_DIR_URI . '/pleinevie/assets/images/logo-batch.png',
			'backgroundColor' => '#e1160c',
			'hoverBackgroundColor' => '#e1160c',
			'textColor' => 'white',
			'preregistration' => true,
		)
	),
	'widget_prefs' => array(),
	'pubstack' => array(
  	'config' => array (
  		'id' => '8e124908-00ef-48da-ac21-1d5f0d022949',
  	),
  ),
	
	'poool' => [
		'config' => ['poool_application_id' => 'FO4DB-J9RWN-9MQ4Q-6PPQW', 'disable_content_height_calculation' => true,
		],
	],
	'adrenalead' => array (
		'config' => array(
			'set_ids' =>'934c10fc92fa877a',
			'set_pk' =>'BEDbJCPifs/8mH7mp5DE26pNklF4ZUpiabhgKVeCh7IvCVwELa07XiCznCnC8IGzbCoZIelxthDxvNqGcZ1toGw=',
			'set_template_id' =>'optinboxperso',
		)
	),
);
