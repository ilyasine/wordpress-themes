<?php 
$pages_types_dfp = array(
	'hp' => array('habillage', 'inread_adikteev', 'interstitiel', 'masthead_haut', 'mpu_haut', 'vignette_haut', 'mpu_milieu', 'mpu_milieu_2', 'mpu_milieu_3', 'mpu_bas', 'vignette_bas' , 'dhtml', 'interstitiel_mobile', 'mobile_1', 'mobile_2', 'mobile_3', 'mobile_4', 'mobile_5', 'mobile_6', 'mobile_7', 'mobile_8', 'mobile_9', 'mobile_10', 'mobile_11', 'mobile_12'),
	'rg'=> array( 'habillage', 'inread_adikteev', 'interstitiel', 'vignette_haut', 'masthead_haut', 'mpu_haut', 'mpu_milieu', 'mpu_milieu_2', 'mpu_milieu_3', 'mpu_bas', 'vignette_bas', 'masthead_bas', 'dhtml' ,'interstitiel_mobile', 'mobile_1', 'mobile_2', 'mobile_3', 'mobile_4', 'mobile_5', 'mobile_6', 'mobile_7', 'mobile_8', 'mobile_9', 'mobile_10', 'mobile_11', 'mobile_12', 'native'),
	'diapo_monetisation'=> array( 'habillage', 'inread_adikteev', 'interstitiel', 'vignette_haut', 'masthead_haut', 'mpu_haut', 'mpu_milieu', 'mpu_milieu_2', 'mpu_milieu_3', 'mpu_bas', 'vignette_bas', 'masthead_bas', 'dhtml' ,'banner_incontent_1', 'banner_incontent_2', 'interstitiel_mobile', 'mobile_1', 'mobile_2', 'mobile_3', 'mobile_4', 'mobile_5', 'mobile_6', 'mobile_7', 'mobile_8', 'mobile_9', 'mobile_10', 'mobile_11', 'mobile_12', 'native'),
);
$plan_tagagge_dfp =  array(
	"hp"	 => ['id' => "home"], 
	"divers" => ['id' => "rg"],
);
  
$partners = array (
	'analytics' => [
		'config' => [
			'google_analytics_id' => 'UA-1658521-76', 
			'test_google_analytics_id' => 'UA-52051220-40',
		]
	],
	'prebid' => array(
		'config' => array(
			'site_folder' => 'homeophyto',
		)
	),

	'dfp_v2' => array (
	 	'config' => array (
	        'dfp_id_account' => '46980923/reponsesphoto-web',
	        'pages_types' => $pages_types_dfp,
	        "plan_tagagge"=> $plan_tagagge_dfp,
	        'formats_lazyloading' => [],
	        'dfp_native_after_image' => true,
	    ),
	),
	'cmp_didomi' => array(),

	'taboola' => array(
		'config' => array(
			'path_loader' => 'mondadori-topsante',
		)
	),

	'taboola_organique' => array(
		'config' => array(
			'path_loader' => 'mondadori-topsante',
		)
	),
	'widget_ops' => array (
	    'config' => array(
	        'univers_name' =>'Univers Féminin (MF, VPF, BIBA)',
	        'style_path' => STYLESHEET_DIR_URI . '/reponses-photo/assets/stylesheets/widget_ops.css',
	        'single_action_show_ops' => 'after_single_article',
    	)
	),
	'dfp_amp' => array (
		'default_activation' => false,
		'config' => array (
			'amp_dfp_id_account' => '46980923/reponsesphoto-amp',
			'sizes' => '320x100,320x50,300x600,300x250,300x100,300x50,160x600',
			'disable_multi_size' => true,
		),
		'callback' => 'init',
	),
);
