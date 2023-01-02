<?php

class fiche_Post_Type {

	private static $_instance;

	public static function get_instance() {
		if(is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {
		add_action('init', [$this, 'create_post_type_fiche']);


	}

	public function create_post_type_fiche() {
		$labels = [
			'name' => __( 'Fiche' ),
			'singular_name' => __( 'fiche' ),
			'add_new' => 'Ajouter fiche',
			'add_new_item' => 'Ajouter fiche',
			'edit_item' => 'Editer fiche',
			'new_item' => 'Nouvelle fiche',
			'view_item' => 'Voir fiche',
			'search_items' => 'Chercher fiche',
			'not_found' => 'Pas de fiche trouvée',	
		];
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true, 
			'show_in_menu' => true, 
			'exclude_from_search' => true,
			'query_var' => true,
			'capability_type' => 'post',
			'has_archive' => true, 
			'hierarchical' => false,
			'supports' => array('title', 'editor', 'thumbnail', 'custom-fields', 'comments'),
			'taxonomies' => array('category','post_tag', 'marque'),
			'description' => 'Parents, grands-parents, enfants, petits-enfants, les générations se côtoient, s’entraident et parfois se disputent. Les familles sont de plus en plus variées, classiques, recomposées ou monoparentales, parfois homoparentales. Pleine Vie vous aide à vous repérer dans les règles. Comment se passe un divorce par consentement mutuel ou pour faute ? Pension alimentaire ou prestation compensatoire, qui y a droit après une séparation ? Mariage, pacs, concubinage : qu’est-ce que cela change ? L’obligation alimentaire est-elle la seule entraide ? Tutelle, curatelle, comment protéger une personne âgée vulnérable ?',
		);
		// Register post type
		register_post_type('fiche', $args);
	}



}

fiche_Post_Type::get_instance();