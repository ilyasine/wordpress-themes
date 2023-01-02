<?php
add_action('side-bar_before-footer', 'add_footer_logo');

///////////////////////////////////////////////////////////////////////////
/////////////////////////////// ACTIONS ///////////////////////////////////
///////////////////////////////////////////////////////////////////////////
add_action('after_single_article', 'show_block_lieu_organisateur_after_content_single', 1);
add_action('after_single_article', 'show_block_voir_aussi', 10);
add_action('single_after_title', 'change_post_author_date_rp', 10); 
add_action('init', function() {
    remove_action('after_header_search','msg_page_search');
    add_filter('search_thumb_class', 'add_class_to_identify_diapos_in_search_page');
    remove_action('before_home_actus', 'add_title_recents_posts');
});
add_action('wp',function(){
	if(is_singular('evenement')){
		global $site_config;
		$site_config['show_copyright_image'] = true;
	}
});
add_action('wp_enqueue_scripts', 'load_rp_main_js');
add_action('before_result_search', 'searchform', 10);
add_action('before_result_search', 'change_page_search_message', 11);
add_action("pre_get_posts", "filter_search_found_posts");
add_action('wp_head', 'remove_barretopinfo_mobile'); 
add_filter('show_under_menu_footer_page', 'add_menu_under_footerpage'); 
///////////////////////////////////////////////////////////////////////////
/////////////////////////////// FILTERS ///////////////////////////////////
///////////////////////////////////////////////////////////////////////////
add_filter('breadcrumb_rewo','change_breadcumb_evenement_page');
add_filter('size_thumb_top_img','change_thumb_top_img_evenement_page');
add_filter('template_post_empty_search_result','change_template_post_empty_search_result');
add_filter('show_cat_name_and_date_after_image', 'show_cat_name_and_date_after_image', 10);
add_filter('large_diapo_cls', '__return_empty_string');
add_filter('block_diapo_cls', '__return_empty_string');
add_filter('after_homeMoreArticles_title', 'add_homeMoreArticles_cat_link');
add_filter('args_bloc_video','change_args_function_bloc_agenda');
add_filter('video_bloc_title','change_bloc_title_agenda');
add_filter('video_bloc_title_voir_toutes','change_bloc_title_voir_toutes_agenda');
add_filter('posts_show_author',__return_false(), 11);
add_filter('share_bloc_title', 'share_bloc_title_rp');
add_filter('excerpt_number_of_characters', 'change_excerpt_characters_count', 10, 1);
add_filter('excerpt_number_of_lines', 'change_excerpt_lines_count', 10, 1);
add_filter('nl_page_template', 'rp_edit_nl_page_path');
add_filter('date_after_cat_link_text', 'add_text_to_date_after_cat_link');
add_filter('title_home_h2', 'set_title_home_h2_empty_agenda', 100, 1);
add_filter('category_pagination', 'category_pagination_rp');
add_filter('header_magazine_img','reponses_photo_magazine_img');
add_filter('header_magazine_img_position','reponses_photo_magazine_img_position');
add_filter('magazine_subscription_title','reponses_photo_subscription_title');
add_filter('magazine_subscription_link','reponses_photo_subscription_link');
add_filter('class_div_content', 'change_class_content_hp');
add_filter('sidebar_classes', 'change_class_sidebar');
add_filter('change_content_rubrique_class', 'change_class_rubrique');
add_action('bloc_agenda_de_la_semaine','add_category_event');
add_filter('default_logo_site_white', 'reponses_photo__logo_mobile',11);
add_filter('class_post_item_bloc_agenda', 'change_name_class_post_item_bloc_agenda');
add_filter('search_item_class', 'add_class_to_search_item_class');
///////////////////////////////////////////////////////////////////////////
////////////////////////////// FUNCTIONS //////////////////////////////////
///////////////////////////////////////////////////////////////////////////

function category_pagination_rp($enable_pagination){
    global $wp_query;
    $current_cat = $wp_query->queried_object;
    $enable_pagination=false;
    if($current_cat && $current_cat->category_parent > 0 ){
        $enable_pagination = true;
    }
    return $enable_pagination;
}

function add_menu_under_footerpage(){
	$menu_items = wp_get_nav_menu_items('sub-menu-footer');	
		
	$under_menu_html = '<ul class="sub-menu-footer menu-footer list-inline">' ;
	if (is_array($menu_items)){
		foreach ($menu_items as $menu_item){
			if($menu_item->menu_item_parent == 0){
				$under_menu_html .='<li class="'. (isset($menu_item->classes)? implode(" ", $menu_item->classes):"") .' ' .' menu-item menu-item-type-post_type menu-item-object-page menu-item-'.$menu_item->ID.'">
						<a href="'.$menu_item->url.'">'.$menu_item->title.'</a>
					</li>' ;
			}		
		}					
	}
	$under_menu_html .='</ul>';

	return $under_menu_html;
}

function add_class_to_search_item_class($class){
	return $class .' row';
}

function change_template_post_empty_search_result($temp){
	remove_filter('search_item_class', 'add_class_to_search_item_class');
	return 'include/templates/list-item-2col.php';
}

function show_cat_name_and_date_after_image($show){
	if(is_category()){
		$show = true;
	}
	return $show;
}

function rp_edit_nl_page_path($s){
    $s = '/reponses-photo/include/newsletter/newsletter_html.php';
    return $s;
}

function add_homeMoreArticles_cat_link($bloc){
	$cat_name = get_cat_name($bloc['category']);
    if(!rw_is_mobile() && $cat_name !== 'Agenda')
        echo !empty($bloc['url']) ? '<a href="'. $bloc['url'] .'" class="view_all">Tout voir</a>' : '';
}

function change_args_function_bloc_agenda ($args){
	$args =  array(
		'showposts' => 12, 
		'post_type' => 'evenement',
		'orderby' => 'post_date', 
		'order' => 'DESC'
	);
	return $args;
}

function change_bloc_title_agenda($title){
	return "Agenda de la semaine";
}

function change_bloc_title_voir_toutes_agenda($title){
	return "Tout voir";
}

function show_block_voir_aussi(){
    echo do_shortcode("[voir_aussi]");
}

function add_footer_logo(){
	echo '<div class="col-xs-12 footer-widget">' . do_shortcode('[logo src="'. STYLESHEET_DIR_URI . '/reponses-photo/assets/images/logo_white.png"]') . '</div>';
}

function change_post_author_date_rp(){
	global $post;
	$datef = __( 'j F Y à H:i' ); 
	$date_publish = date_i18n( $datef, strtotime( $post->post_date ) );
	$date_modified = date_i18n( $datef,strtotime( $post->post_modified));

	$time_post_modified = strtotime( $post->post_modified) ;
	$time_post_date = strtotime( $post->post_date) ;
	if( $time_post_modified < $time_post_date){
		$date_modified = $date_publish ;
	}
	

	if (get_post_meta( get_the_ID() , 'post_auteur_ops' , true ) ) {
		$the_author = get_post_meta( get_the_ID() , 'post_auteur_ops' , true );
	}elseif ($custom_author= get_post_meta( get_the_ID(),'post_auteur')) 
	{
		$the_author=$custom_author[0];
	} else {
		$the_author = get_the_author();
	}
	$the_author = apply_filters('article_top_signature', $the_author);
	if (is_singular('evenement')) {
		$event_start_date = get_post_meta($post->ID, 'event_start_date', true);
		$event_end_date = get_post_meta($post->ID, 'event_end_date', true);

		$start_date = date_i18n('d F Y', strtotime($event_start_date));
		$date_to_show = 'Du ' .$start_date;
		
		if (!empty($event_end_date)) 
		{
			$end_date = date_i18n('d F Y', strtotime($event_end_date));
			$date_to_show .= ' au ' . $end_date ;
		}
		?>
			<span class="event_Date"><?php  echo $date_to_show; ?></span>
		<?php 
	}else{
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
}

function change_breadcumb_evenement_page($breadcrumb){
	if (is_singular('evenement')){
		$page_url = get_site_url().'/evenement';
		if(BREADCRUMB_MICRO_DONNEES_HTML){
			$breadcrumb .= '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
			                    <a href="'.$page_url.'" ><span itemprop="name">Evènement</span></a>
			                    <meta itemprop="position" content="CNT_COUNT" />
			                </li>';
		}else{
			$breadcrumb .= '<li><a href="'.$page_url.'"><span>Evènement</span></a><meta content="CNT_COUNT" /></li>';
		}
	}
	return $breadcrumb;	
}
function change_thumb_top_img_evenement_page($size){
	if (is_singular('evenement')){
		if(!rw_is_mobile()){
			$size = 'rw_full';
		}
	}
	return $size;
}

function share_bloc_title_rp($title){
	$title = '';
	return $title;
}


add_filter('nav_menu_logo_rp','rp_svg_white_logo');

function rp_svg_white_logo($li_logo){
	$logo = STYLESHEET_DIR_URI . '/reponses-photo/assets/images/RP_sticked.svg';
	$url = esc_url(apply_filters('logo_home_url', home_url('/')));
	$title = get_bloginfo('name');
	$li_logo = '<li class="nav_logo">
		<a href="'. $url .'">
		<img src="' . $logo . '" title="'. $title .'" alt="'. $title .'">
		</a>
		</li>';
	return $li_logo;
}

add_filter( 'forcer_affichage_top_img_single', 'afficher_top_img_gallery_rp');
function afficher_top_img_gallery_rp($force){
	if( is_singular('evenement') ){
		$force = true;
	}
	return $force;
}

function show_block_lieu_organisateur_after_content_single (){
	if (is_singular('evenement')) {
		global $post;
		$lieu_id = get_post_meta($post->ID, 'event_lieu', true);
		$organisateur_id = get_post_meta($post->ID, 'event_organisateur', true);;

		//Lieux
		$titre  =  get_post_meta($lieu_id, 'Titre_court', true);
		$dates  =  get_post_meta($lieu_id, 'dates', true);
		$adress  =  get_post_meta($lieu_id, 'adress', true);
		$city  =  get_post_meta($lieu_id, 'city', true);
		$phone  =  get_post_meta($lieu_id, 'phone', true);
		$pays  =  get_post_meta($lieu_id, 'pays', true);
		$latitude  =  get_post_meta($lieu_id, 'latitude', true);
		$longitude  =  get_post_meta($lieu_id, 'longitude', true);
		$siteweb  =  get_post_meta($lieu_id, 'siteweb', true);
		$informations  =  get_post_meta($lieu_id, 'informations', true);

		// Organisateur 
		$titreOrganisateur  =  get_post_meta($organisateur_id, 'Titre_court', true);
		$phoneOrganisateur  =  get_post_meta($organisateur_id, 'phoneOrganisateur', true);
		$sitewebOrganisateur  =  get_post_meta($organisateur_id, 'sitewebOrganisateur', true);
		$informationsOrganisateur  =  get_post_meta($organisateur_id, 'informationsOrganisateur', true);
		if (!empty($lieu_id)) {
			$html_lieu = '<div class="bloc_container row">';
			$html_lieu .= '<div class="lieu col-md-6">';
			$html_lieu .= '<div class="border">';
			$html_lieu .= '<p> Lieux </p>';
			$html_lieu .= '<ul>';
			if(!empty($titre)) $html_lieu .= '<li><strong>' . $titre . '</strong></li>';
			if(!empty($dates)) $html_lieu .= '<li>' . $dates . '</li>';
			if(!empty($adress)) $html_lieu .= '<li>' . $adress . '</li>';
			if(!empty($city)) $html_lieu .= '<li>' . $city . '</li>';
			if(!empty($phone)) $html_lieu .= '<li>' . $phone . '</li>';
			if(!empty($pays)) $html_lieu .= '<li>' . $pays . '</li>';
			if(!empty($latitude)) $html_lieu .= '<li>' . $latitude . '</li>';
			if(!empty($longitude)) $html_lieu .= '<li>' . $longitude . '</li>';
			if(!empty($siteweb)) $html_lieu .= '<li>' . $siteweb . '</li>';
			if(!empty($informations)) $html_lieu .= '<li>' . $informations . '</li>';
			$html_lieu .= '</ul></div></div>';

			echo $html_lieu;
		}
		if (!empty($organisateur_id)) {
			$org_html = '<div class="organisateur col-md-6">';
			$org_html .= '<div class="border">';
			$org_html .= '<p> Organisateur </p>';
			$org_html .= '<ul>';
			if(!empty($titreOrganisateur)) $org_html .= '<li><strong>' . $titreOrganisateur . '</strong></li>';
			if(!empty($phoneOrganisateur)) $org_html .= '<li>' . $phoneOrganisateur . '</li>';
			if(!empty($sitewebOrganisateur)) $org_html .= '<li>' . $sitewebOrganisateur . '</li>';
			if(!empty($informationsOrganisateur)) $org_html .= '<li>' . $informationsOrganisateur . '</li>';
			$org_html .= '</ul></div></div></div>';

			echo $org_html;
		}
	}
	
}

add_action('init', 'init_event_lieu');

function init_event_lieu(){
	if(!empty($_POST['action']) && $_POST['action'] == 'event_init_lieu' && !empty($_POST['keywords_lieu']) ) {
		global $wpdb;
		$posts = [];
		$keywords_lieu = $_POST['keywords_lieu'];
		$posts  = $wpdb->get_results("SELECT SQL_CACHE * FROM $wpdb->posts WHERE post_title LIKE '%$keywords_lieu%' AND post_type='lieu' AND post_status='publish'");
		if(count($posts)){
			ob_start();
			foreach($posts as $post) {
				?>
				<option
				value="<?php echo $post->ID; ?>">
				<?php echo $post->post_title; ?>
			</option>
			<?php 
		}
		$html = ob_get_clean();
		$response = ['html' => $html];
		echo json_encode($response);
		die;
		}
	}
	if(!empty($_POST['action']) && $_POST['action'] == 'event_init_organisateur' && !empty($_POST['keywords_organisateur']) ) {
		global $wpdb;
		$organisateurs = [];
		$keywords_organisateur = $_POST['keywords_organisateur'];
		$organisateurs  = $wpdb->get_results("SELECT SQL_CACHE * FROM $wpdb->posts WHERE post_title LIKE '%$keywords_organisateur%' AND post_type='organisateur' AND post_status='publish'");
		if(count($organisateurs)){
			ob_start();
			foreach($organisateurs as $organisateur) {
				?>
				<option
				value="<?php echo $organisateur->ID; ?>">
				<?php echo $organisateur->post_title; ?>
			</option>
			<?php 
		}
		$html = ob_get_clean();
		$response = ['html' => $html];
		echo json_encode($response);
		die;
		}
	}
}

add_action('after_desc_category','add_category_event');
function add_category_event (){
	ob_start();
	include(locate_template('include/templates/search-block-template.php'));
	$html = ob_get_clean();
	echo $html;
}
add_action('pre_get_posts','searchfilter_events');
function searchfilter_events($query) {
	if (!is_admin() && $query->is_main_query() && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'evenement' && isset($_GET['search']) ) {
		$query->set( 'post_type','evenement' );
		$query->query_vars['s'] = $query->query_vars['search'];
		if (!empty($_GET['type'])) {
			$types = array(
				'Concours' => 'concours',
				'Exposition' => 'exposition',
				'Festivals' => 'festivals',
				'Salon' => 'salon',
				'Stages et formations' => 'stages-et-formations',
			);
			$cat = rw_get_category_by_slug($types[$_GET['type']]);
			$query->set( 'category__in', $cat->term_id );	
		}
		if (!empty($_GET['date'])) {
			$query->set('meta_key', 'event_start_date');
		    $query->set('meta_value', $_GET['date']);
		}
	}
	return $query;
}

add_filter('class_item_post','change_class_item_post_rp',99,2);

function change_class_item_post_rp($classe,$i){
	if(!is_home() && !is_front_page()){
		$classe = 'col-xs-12 col-sm-4 item-post';
		if (($i -1 )%5 == 0 || ($i-2)%5 == 0 ) {
			$classe = 'col-xs-12 col-sm-6 item-post';
		}
	}
	return $classe;
}

function change_class_content_hp($cls){
	$cls = "col-xs-12 col-sm-8 col-md-8 col-lg-8 pull-left";
	return $cls;
}

function change_class_sidebar($cls){	
	$cls = "widget-area col-xs-12 col-sm-4 col-md-4 col-lg-4 pull-right";
	return $cls;
}

function change_class_rubrique($cls){
	$cls = 'col-xs-12 col-sm-8 col-md-8';
	return $cls;
}

add_action('after_form_rubrique_evenement','message_after_form_rubrique_evenement');
function message_after_form_rubrique_evenement(){
	echo '<p>Vous souhaitez nous signaler une exposition, un festival, un concours ou un stage ?</p> 
	<p>Merci de communiquer toutes les informations utiles à Françoise Bensaid (01 41 86 17 12) : </p><p>francoise.bensaid@mondadori.fr</p> ';
}

function load_rp_main_js() {
    wp_enqueue_script('rp-main', STYLESHEET_DIR_URI . '/reponses-photo/assets/javascripts/main.js', ['jquery'], CACHE_VERSION_CDN, true);
}

function searchform($s) { 
	?>
	<div class="blockSearch">
		<div id="filter_search_text">Affinez votre recherche :</div>
		<form class="row form-inline" id="search_content_form" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" role="search">
			  <div class="form-group col-md-4 search_txt_input">
				   <input type="text" class="form-control" placeholder="<?php _e("Votre recherche ici ...", REWORLDMEDIA_TERMS ); ?>" name="s" value="<?php echo $s;?>"/>
				   <button type="submit">Search</button>
			  </div>
			  <input type="hidden" id="hidden_content_date" value="<?php echo $_GET['content_date']; ?>">
			  <input type="hidden" id="hidden_content_type" value="<?php echo $_GET['content_type']; ?>">
			  <div class="form-group col-md-4 search_select_input">
			  	<div class="select-wrapper">
				  <select name="content_date" id="search_content_date" class="form-control">
					<option value="" selected>Date de publication</option>
					<option value="today">Aujourd'hui</option>
					<option value="one_week_ago">Depuis 1 semaine</option>
					<option value="one_month_ago">Depuis 1 mois</option>
					<option value="six_months_ago">Depuis 6 mois</option>
					<option value="one_year_ago">Depuis 1 an</option>
					<option value="one_to_five_years_ago">De 1 à 5 ans</option>
					<option value="more_than_five_years_ago">De plus de 5 ans</option>
				  </select>
				</div>
			  </div>
			  <div class="form-group col-md-4 search_select_input">
				<div class="select-wrapper">
				  <select name="content_type" id="search_content_type" class="form-control">
					<option value="" selected>Type de contenu</option>
					<option value="post">Articles</option>
					<option value="video">Vidéos</option>
					<option value="diapo">Photos</option>
				  </select>
				</div>
			  </div>
		</form>
	</div>
	<?php
}

function change_page_search_message($s) {
	global $wp_query;
	echo '<div class="bar_result_search">';
	    if ($wp_query->have_posts()) {
		    echo '<h2>'. sprintf( __( '%s - ', REWORLDMEDIA_TERMS ), '<span class="search_query" >' . $s . '</span>' ).'<span class="count-search">'. $wp_query->found_posts . '</span> Résultats  </h2>'; 
	    } else {
		    echo '<h2>'.__('Désolé, il n\'y a pas de résultat correspondant à votre recherche',REWORLDMEDIA_TERMS).'</h2>';
		    echo'<h3>'.__('Vous trouverez surement votre bonheur dans nos derniers articles',REWORLDMEDIA_TERMS).'</h3>'; 
	    } 
	echo '</div>';
}

function filter_search_found_posts($query) {
	if (!is_admin() && $query->is_main_query() && is_search()) {
		$date_query = array();
		$today = getdate();
		$content_date = $_GET["content_date"] ?? '';
		if (!empty($content_date)) {
			switch ($content_date) {
				case 'today':
					$date_query = array( 
						array(
							'year'  =>  $today['year'],
							'month' =>  $today['mon'],
							'day'   =>  $today['mday'],
						),
					);
				break;
				case 'one_week_ago':
					$date_query = array (
						array (
							'after' => '1 week ago'
						)  
					);
				break;
				case 'one_month_ago':
					$date_query = array(
						'column' => 'post_date_gmt',
						'after' => '1 month ago',
					);
				break;
				case 'six_months_ago':
					$date_query = array (
						'column' => 'post_date_gmt',
						'after' => '6 months ago',
					);
				break;
				case 'one_year_ago':
					$date_query = array (
						'column' => 'post_date_gmt',
						'after' => '1 year ago',
					);
				break;
				case 'one_to_five_years_ago':
					$date_query = array(
						'year' => array($today['year'] - 5, $today['year'] - 1),
						'compare' => 'BETWEEN',
					);   
				break;
				case 'more_than_five_years_ago':
					$date_query = array (
						'column' => 'post_date_gmt',
						'before' => '5 years ago',
					);
				break;
			}
		}
		if (count($date_query)) {
			$query->set('date_query', $date_query);
		}
		$content_type = $_GET["content_type"] ?? '';
		if (!empty($content_type)) {
			if ($content_type == 'post') {
				$query->set('post_type', $content_type);
			}
			if ($content_type == 'video') {
				$query->set('tag', 'has_video');
			}
			if ($content_type == 'diapo') {
				$query->set('tag', 'has_diapo');
			}
		}
	}
	return $query;
}

function change_excerpt_characters_count($nb_chars) {
	$nb_chars = 80;
	return $nb_chars;
}

function change_excerpt_lines_count($nb_lines) {
	$nb_lines = 3;
	return $nb_lines;
}

function add_text_to_date_after_cat_link($text) {
	$text = "Le ";
	return $text;
}

function add_class_to_identify_diapos_in_search_page($classes) {
	global $post;
	$classes = "col-xs-12 col-sm-5";
	if (!is_admin() && is_main_query() && is_search()) {
		if(has_tag('has_diapo', $post)) {
			$classes .= " item-diapo"; 
		}
	}
	return $classes;
}

function set_title_home_h2_empty_agenda($title){
	if($title == 'Agenda'){
		$title = '';
	}
	return $title;
}

function remove_barretopinfo_mobile() {
    global $site_config;
    if (rw_is_mobile()) {
        $site_config['hide_barre_top_info'] = true;
    }
}
function reponses_photo_magazine_img($img_path){
	$img_path = STYLESHEET_DIR_URI . '/reponses-photo/assets/images/kiosquemag.webp';
	return $img_path;
}

function reponses_photo_magazine_img_position(){
	return '-86px';
}

function reponses_photo_subscription_title($title){
	$title = "S'ABONNER";
	return $title;
}

function reponses_photo_subscription_link($link){
	$link = "https://clk.tradedoubler.com/click?p=303669&a=3137727&g=24715292&url=https://www.kiosquemag.com/titres/reponses-photo/offres?utm_source=header&utm_medium=site-reponses-photo";
	return $link;
}

add_filter('post_item_cat_before_title','show_post_item_category_before_title');

function show_post_item_category_before_title($val){
	$val = true;
	return $val;
}

function reponses_photo__logo_mobile($path) {
	return STYLESHEET_DIR_URI . '/reponses-photo/assets/images/RP_sticked.svg';
}
add_filter('custom_bloc_classes_v3','add_class_container_img');
function add_class_container_img($class){
    $class .= " hover-title";
	return $class;
}

add_action('liste_plus_artiles_rubriques', 'display_bloc_agenda', 10, 2);

function display_bloc_agenda($index_bloc, $last_posts) {
    if(is_home() && $index_bloc == 1) { 
        include(locate_template('include/templates/bloc-video.php'));
    }
}

function change_name_class_post_item_bloc_agenda($class){
	if(is_front_page() || is_home()){
		$class = "col-xs-12 col-md-4 col-sm-6";
	}
	return $class;
}