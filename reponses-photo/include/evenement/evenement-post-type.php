<?php

class evenement_Post_Type {

	private static $_instance;
	public static function get_instance() {
		if(is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {
		add_action('init', [$this, 'create_post_type_evenement']);
		add_action('init', [$this, 'create_post_type_lieu']);
		add_action('init', [$this, 'create_post_type_organisateur']);
		add_action('add_meta_boxes', [$this, 'add_lieu_properties_meta_box']);
		add_action('add_meta_boxes', [$this, 'add_organisateur_properties_meta_box']);
		add_action('save_post_lieu', [$this, 'save_lieu_meta_box']);
		add_action('save_post_organisateur', [$this, 'save_organisateur_meta_box']);
		add_action('add_meta_boxes', [$this, 'add_event_properties_meta_box']);
		add_action('save_post_evenement', [$this, 'save_evenement_meta_box']);
	}

	public function add_lieu_properties_meta_box() {
		add_meta_box(
			'lieu-properties',
			"Propriétés du lieu de l'événement",
			[$this, 'gen_lieu_properties_meta_box'], 
			'lieu',
			'normal',
			'high'
		);
	}
	public function add_organisateur_properties_meta_box() {
		add_meta_box(
			'organiser-properties',
			"Propriétés de l'organisateur de l'événement",
			[$this, 'gen_organisateur_properties_meta_box'], 
			'organisateur',
			'normal',
			'high'
		);
	}
	public function add_event_properties_meta_box() {
		add_meta_box(
			'event-properties',
			"Propriétés de l'événement",
			[$this, 'gen_event_properties_meta_box'], 
			'evenement',
			'normal',
			'high'
		);
	}

	public function create_post_type_evenement() {
		$labels = [
			'name' => __( 'Evénement' ),
			'singular_name' => __( 'evenement' ),
			'add_new' => 'Ajouter evénement',
			'add_new_item' => 'Ajouter evénement',
			'edit_item' => 'Editer evénement',
			'new_item' => 'Nouvelle evénement',
			'view_item' => 'Voir evénement',
			'search_items' => 'Chercher evénement',
			'not_found' => 'Pas de evénement trouvée',	
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
			'supports' => array('title', 'editor', 'thumbnail', 'custom-fields', 'comments','excerpt'),
			'taxonomies' => array('category','post_tag', 'marque')
		);
		// Register post type
		register_post_type('evenement', $args);
	}

	public function create_post_type_lieu() {
		$labels = [
			'name' => __( 'Lieu' ),
			'singular_name' => __( 'lieu' ),
			'add_new' => 'Ajouter Lieu',
			'add_new_item' => 'Ajouter Lieu',
			'edit_item' => 'Editer Lieu',
			'new_item' => 'Nouvelle Lieu',
			'view_item' => 'Voir Lieu',
			'search_items' => 'Chercher Lieu',
			'not_found' => 'Pas de Lieu trouvée',	
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
			'taxonomies' => array('category','post_tag', 'marque')
		);
		// Register post type
		register_post_type('lieu', $args);
	}
	public function create_post_type_organisateur() {
		$labels = [
			'name' => __( 'Organisateur' ),
			'singular_name' => __( 'organisateur' ),
			'add_new' => 'Ajouter Organisateur',
			'add_new_item' => 'Ajouter Organisateur',
			'edit_item' => 'Editer Organisateur',
			'new_item' => 'Nouvelle Organisateur',
			'view_item' => 'Voir Organisateur',
			'search_items' => 'Chercher Organisateur',
			'not_found' => 'Pas de Organisateur trouvée',	
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
			'taxonomies' => array('category','post_tag', 'marque')
		);
		// Register post type
		register_post_type('organisateur', $args);
	}

	public function gen_lieu_properties_meta_box(){
		global $post;
		$metasPost = get_post_custom($post->ID);
		$dates = isset($metasPost["dates"]) ? $metasPost["dates"][0] : "";
		$adress = isset($metasPost["adress"]) ? $metasPost["adress"][0] : "";
		$codepostal = isset($metasPost["codepostal"]) ? $metasPost["codepostal"][0] : "";
		$city = isset($metasPost["city"]) ? $metasPost["city"][0] : "";
		$phone = isset($metasPost["phone"]) ? $metasPost["phone"][0] : "";
		$pays = isset($metasPost["pays"]) ? $metasPost["pays"][0] : "";
		$latitude = isset($metasPost["latitude"]) ? $metasPost["latitude"][0] : "";
		$longitude = isset($metasPost["longitude"]) ? $metasPost["longitude"][0] : "";
		$siteweb = isset($metasPost["siteweb"]) ? $metasPost["siteweb"][0] : "";
		$informations = isset($metasPost["informations"]) ? $metasPost["informations"][0] : "";
		?>
		<p>
			<label for="dates"><?php _e('Dates :' ,  REWORLDMEDIA_TERMS ); ?></label>
			<br>
			<input type="text" name="dates" id="dates" value="<?php echo $dates ; ?>"/>
		</p>
		<p>
			<label for="adress"><?php _e('adress :' ,  REWORLDMEDIA_TERMS ); ?></label>
			<br>
			<input type="text" name="adress" id="adress" value="<?php echo $adress ; ?>"/>
		</p>
		<p>
			<label for="codepostal"><?php _e('Code postal :' ,  REWORLDMEDIA_TERMS ); ?></label>
			<br>
			<input type="text" name="codepostal" id="codepostal" value="<?php echo $codepostal ; ?>"/>
		</p>
		<p>
			<label for="city"><?php _e('Ville :' ,  REWORLDMEDIA_TERMS ); ?></label>
			<br>
			<input type="text" name="city" id="city" value="<?php echo $city ; ?>"/>
		</p>
		<p>
			<label for="phone"><?php _e('Téléphone :' ,  REWORLDMEDIA_TERMS ); ?></label>
			<br>
			<input type="text" name="phone" id="phone" value="<?php echo $phone ; ?>"/>
		</p>
		<p>
			<label for="pays"><?php _e('Pays :' ,  REWORLDMEDIA_TERMS ); ?></label>
			<br>
			<input type="text" name="pays" id="pays" value="<?php echo $pays ; ?>"/>
		</p>
		<p>
			<label for="latitude"><?php _e('latitude :' ,  REWORLDMEDIA_TERMS ); ?></label>
			<br>
			<input type="text" name="latitude" id="latitude" value="<?php echo $latitude ; ?>"/>
		</p>
		<p>
			<label for="longitude"><?php _e('longitude :' ,  REWORLDMEDIA_TERMS ); ?></label>
			<br>
			<input type="text" name="longitude" id="longitude" value="<?php echo $longitude ; ?>"/>
		</p>
		<p>
			<label for="siteweb"><?php _e('Site internet :' ,  REWORLDMEDIA_TERMS ); ?></label>
			<br>
			<input type="text" name="siteweb" id="siteweb" value="<?php echo $siteweb ; ?>"/>
		</p>

		<p>
			<label for="informations"><?php _e('Informations :' ,  REWORLDMEDIA_TERMS ); ?></label>
			<br>
			<input type="text" name="informations" id="informations" value="<?php echo $informations ; ?>"/>
		</p>
		<?php
	}
	public function gen_organisateur_properties_meta_box(){
		global $post;
		$metasPost = get_post_custom($post->ID);
		$phoneOrganisateur = isset($metasPost["phoneOrganisateur"]) ? $metasPost["phoneOrganisateur"][0] : "";
		$sitewebOrganisateur = isset($metasPost["sitewebOrganisateur"]) ? $metasPost["sitewebOrganisateur"][0] : "";
		$informationsOrganisateur = isset($metasPost["informationsOrganisateur"]) ? $metasPost["informationsOrganisateur"][0] : "";
		?>
		<p>
			<label for="phoneOrganisateur"><?php _e('Téléphone :' ,  REWORLDMEDIA_TERMS ); ?></label>
			<br>
			<input type="text" name="phoneOrganisateur" id="phoneOrganisateur" value="<?php echo $phoneOrganisateur ; ?>"/>
		</p>
		<p>
			<label for="sitewebOrganisateur"><?php _e('Site internet:' ,  REWORLDMEDIA_TERMS ); ?></label>
			<br>
			<input type="text" name="sitewebOrganisateur" id="sitewebOrganisateur" value="<?php echo $sitewebOrganisateur ; ?>"/>
		</p>

		<p>
			<label for="informationsOrganisateur"><?php _e('Informations :' ,  REWORLDMEDIA_TERMS ); ?></label>
			<br>
			<input type="text" name="informationsOrganisateur" id="informationsOrganisateur" value="<?php echo $informationsOrganisateur ; ?>"/>
		</p>
		<?php
	}

	public function gen_event_properties_meta_box(){
		global $post;
		$metasPost = get_post_custom($post->ID);
		$current_lieu = isset($metasPost['event_lieu']) ? $metasPost['event_lieu'][0] : '';
		$event_start_date = isset($metasPost['event_start_date']) ? $metasPost['event_start_date'][0] : '';
		$event_end_date = isset($metasPost['event_end_date']) ? $metasPost['event_end_date'][0] : '';
		$current_organisateur = isset($metasPost['event_organisateur']) ? $metasPost['event_organisateur'][0] : '';
		$event_dates = isset($metasPost['event_dates']) ? $metasPost['event_dates'][0] : '';
		$lieu = '';
		if(!empty($current_lieu)){
			$lieu = get_post($current_lieu);
		}
		$organiser = '';
		if(!empty($current_organisateur)){
			$organisateur = get_post($current_organisateur);
		}
		?>
		<form class="event_form" action ="#" method="POST">
			<div class="choose_lieu" data-lieu="<?php echo $post->ID; ?>">
				<p>
					<script type="text/javascript">
						function lieuFilter() {	
							var keywords_lieu = jQuery('#keywords_lieu').val();
							if( keywords_lieu.length < 3 ) return;
							var data = {
								action: 'event_init_lieu',
								keywords_lieu: keywords_lieu,
							};
							jQuery.post('/', data, function(response) {
								if(typeof response.html !== 'undefined' && response.html) {
									jQuery("#event_lieu").html(response.html).show('normal');
								}
							}, 'json');
						}
					</script>
					<p>
						<label for="event_start_date"><?php _e('Date de début de l\'événement:' ,  REWORLDMEDIA_TERMS ); ?></label>
						<br>
						<input type="date" id="event_start_date" name="event_start_date" value="<?php echo $event_start_date ; ?>">
					</p>
					<p>
						<label for="event_end_date"><?php _e('Date de fin de l\'événement:' ,  REWORLDMEDIA_TERMS ); ?></label>
						<br>
						<input type="date" id="event_end_date" name="event_end_date" value="<?php echo $event_end_date ; ?>">
					</p>
					<p>
						<label for="event_dates"><?php _e('Dates :' ,  REWORLDMEDIA_TERMS ); ?></label>
						<br>
						<input type="text" id="event_dates" name="event_dates" value="<?php echo $event_dates ; ?>">
					</p>
					<label for="event_lieu">Lieu : </label>
					<input type="text" id="keywords_lieu" name="keywords_lieu" size="20"  onkeyup="lieuFilter();" value="" placeholder="Veuillez saisir un lieu" />
					<select classe="mdb-select md-form" name="event_lieu"  id="event_lieu" searchable="Search here..">
						<?php 
						$selected = '';
						if(!empty($lieu)) {
							$selected = 'selected';
							
							?>
							<option 
							value="<?php echo $lieu->ID; ?>" 
							<?php echo "$selected" ?> >
							<?php echo $lieu->post_title; ?>	
						</option>
						<?php	
					}
					?>
				</select>
			</p>
		</div>
		<div class="choose_organisateur" data-organisateur="<?php echo $post->ID; ?>">
			<p>
				<script type="text/javascript">
					function organisateurFilter() {	
						var keywords_organisateur = jQuery('#keywords_organisateur').val();
						if( keywords_organisateur.length < 3 ) return;
						var data = {
							action: 'event_init_organisateur',
							keywords_organisateur: keywords_organisateur,
						};
						jQuery.post('/', data, function(response) {
							if(typeof response.html !== 'undefined' && response.html) {
								jQuery("#event_organisateur").html(response.html).show('normal');
							}
						}, 'json');
					}
				</script>
				<label for="event_organisateur">organisateur : </label>
				<input type="text" id="keywords_organisateur" name="keywords_organisateur" size="20"  onkeyup="organisateurFilter();" value="" placeholder="Veuillez saisir un organisateur" />
				<select classe="mdb-select md-form" name="event_organisateur"  id="event_organisateur" searchable="Search here..">
					<?php 
					$selected = '';
					if(!empty($organisateur)) {
						$selected = 'selected';
						
						?>
						<option 
						value="<?php echo $organisateur->ID; ?>" 
						<?php echo "$selected" ?> >
						<?php echo $organisateur->post_title; ?>	
					</option>
					<?php	
				}
				?>
			</select>
		</p>
	</div>
</form>
<?php
}

public function save_lieu_meta_box($post_id){
	if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){ 
		return $post_id;
	}

	reworld_save_meta($post_id, 'dates');
	reworld_save_meta($post_id, 'adress');
	reworld_save_meta($post_id, 'codepostal');
	reworld_save_meta($post_id, 'city');
	reworld_save_meta($post_id, 'phone');
	reworld_save_meta($post_id, 'pays');
	reworld_save_meta($post_id, 'latitude');
	reworld_save_meta($post_id, 'longitude');
	reworld_save_meta($post_id, 'siteweb');
	reworld_save_meta($post_id, 'informations');
}
public function save_organisateur_meta_box($post_id){
	if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){ 
		return $post_id;
	}

	reworld_save_meta($post_id, 'phoneOrganisateur');
	reworld_save_meta($post_id, 'sitewebOrganisateur');
	reworld_save_meta($post_id, 'informationsOrganisateur');
}
public function save_evenement_meta_box($post_id){
	if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){ 
		return $post_id;
	}
	reworld_save_meta($post_id, 'event_lieu');
	reworld_save_meta($post_id, 'event_organisateur');
	reworld_save_meta($post_id, 'event_start_date');
	reworld_save_meta($post_id, 'event_end_date');
	reworld_save_meta($post_id, 'event_dates');
}
}

evenement_Post_Type::get_instance();