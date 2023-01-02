<?php

// ACTIONS
add_action('side-bar_before-footer', 'add_footer_logo');
add_action('before_last_post','homepage_categories_fils');
add_action('after_single_article', 'add_girandieres_banner_after_post', 1);
add_action('wp_head', 'add_app_declaration_meta_and_links', 1);

// FILTERS
add_filter('large_diapo_cls', '__return_empty_string');
add_filter('block_diapo_cls', '__return_empty_string');
add_filter('widgetLegende_cls', '__return_empty_string');
add_filter('lists_legend_cls', '__return_empty_string');
add_filter('nb_posts_per_categoery','nb_posts_per_category_pleinevie');
add_filter('category_pagination', 'category_pagination_pleinevie');
add_filter('article_top_signature','post_author_name_pleinevie');
add_filter('insert_after_1_paragraph', 'insert_shortcode_sur_lememe_sujet');
// SHORTCODES

function add_footer_logo(){
	echo '<div class="col-xs-12 footer-widget">' . do_shortcode('[logo src="'. STYLESHEET_DIR_URI . '/pleinevie/assets/images/logo_white.png"]') . '</div>';
}

add_action ('init', function(){
    remove_action('after_single','add_block_author', 7);
    remove_action('just_before_author_posts','add_author_posts_title',10);
    remove_action('after_single_article', 'show_block_share_content_single', 1);
});

add_action('after_single_article', 'show_block_share', 10);
add_action('after_single_article', 'show_block_voir_aussi', 10);

function show_block_share(){
    ?>
    <div class="share_box">
        <?php echo do_shortcode("[simple_addthis_single]");?>
    </div>

    <?php
}
function show_block_voir_aussi(){
    echo do_shortcode("[voir_aussi]");
}

function homepage_categories_fils(){
    $cache_key = 'bloc_rubriques_hp';
    echo_from_cache($cache_key, 'last_posts', TIMEOUT_CACHE_LAST_POSTS, function(){
        $menu_items = wp_get_nav_menu_items('menu_header');
        if(!empty($menu_items)){
            foreach($menu_items as $item){
                if($item->object=='category'){
                    $parent_category = get_terms( 'category', array( 'name__like' => $item->title ) );
                    if(!empty($parent_category)){
                        foreach($parent_category as $cat){
                            if($cat->name === $item->title){
                                $parent_category = $cat;
                                break;
                            }
                        }
                        include locate_template('/pleinevie/include/templates/rubrique-bloc.php');
                    }
                }
            }
        }
    });
}

function nb_posts_per_category_pleinevie($posts_per_page){
    global $wp_query;
    $current_cat = $wp_query->queried_object;
    $posts_per_page = 4;
    if($current_cat && $current_cat->category_parent > 0){
        $posts_per_page = 24;
    }
    return $posts_per_page;
}

function category_pagination_pleinevie($enable_pagination){
    global $wp_query;
    $current_cat = $wp_query->queried_object;
    $enable_pagination=false;
    if($current_cat && ($current_cat->category_parent > 0 || $current_cat->slug == 'actualites') ){
        $enable_pagination = true;
    }
    return $enable_pagination;
}

add_shortcode('page_prix_info','show_page_prix_info');
function show_page_prix_info($attrs){
    $html = '';
    ob_start();
    $desc = isset($attrs['desc']) ? $attrs['desc'] : '';
    include (locate_template('pleinevie/include/templates/page_prix_info.php'));
    $html .= ob_get_clean();
    echo $html;    
}
add_shortcode('page_prix_categories','show_page_prix_categories');
function show_page_prix_categories($attrs){
    global $posts_exclude;
    $number_posts_articles = apply_filters('nb_posts_per_categoery', 4);
    if($attrs && !empty($attrs["categories"])){
        $categories_ids = explode(',', $attrs["categories"]);
        $categories = get_categories(["include" => $categories_ids]);
        if(!empty($categories)){
            foreach ($categories as $category){
                $data_posts = get_posts([
                    'category' => $category->term_id,
                    'post__not_in' => $posts_exclude,
                    'posts_per_page' => $number_posts_articles
                ]);

                $_id_selector = $category->slug . '_' . $category->term_id;
                $_target = "items-posts-" . $category->slug . '_' . $category->term_id;
                include (locate_template('pleinevie/include/templates/page_prix_categories.php'));
            }
        }
    }

}
add_filter('nl_page_template', 'pleinevie_change_nl_page_path');
function pleinevie_change_nl_page_path($s){
    $s = '/pleinevie/include/newsletter/newsletter_html.php';
    return $s;
}

function pleinevie_load_main_js() {

    wp_enqueue_script('pleinevie-main', get_stylesheet_directory_uri() . '/pleinevie/assets/javascripts/main.js', ['jquery'], CACHE_VERSION_CDN, true);
}


add_action('wp_enqueue_scripts', 'pleinevie_load_main_js');

add_action('init', 'send_data_senior_adom');

function send_data_senior_adom() {
    if(!empty($_GET['action']) && $_GET['action'] == 'senior_adom_send_leads') {
        //initialization of variables
        $authentication = 'brahim@webpick.info:prfjyqGEeM';
        $data = [];
        // prepare data
        if (!is_dev()) {
            $data['agency'] = '5fda4524d56caa1bea63169b';
            $data['distributor'] = '5d307d69942df63c850779b0';
            $api_url = 'https://gateway-v2.senioradom.com/api/4/leads';
            $data['source'] = '611e58eae25a904b490bf691';
            $data['campaign'] = get_param_global('adom_data_compaign', 'e7a2dad0-c3b5-491b-9699-89ad1c6c95f6');
        }else{
            $data['agency'] = '5fbf73b8f9dcd168d0b3b615';
            $data['distributor'] = '5d28a6aa11df4567539b6506';
            $api_url = 'https://gateway-pp.senioradom.com/api/4/leads';
            $data['source'] = '610bfb14b90adb165ee43d68';
            $data['campaign'] = 'c898e9d1-4ba7-48be-8af6-24dfa4069abe';
        }
        $data['tags'] = ['Reworld Media'];
        $data['applicant']['firstname'] = $_GET['firstname'] ;
        $data['applicant']['lastname'] = $_GET['lastname'] ;
        $data['applicant']['email'] = $_GET['mail'] ;
        $data['applicant']['phoneNumbers'][] = ['countryCode' => 33,'nationalNumber' => $_GET['phone'] ];
        $requestParams = json_encode($data);
        //send data
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $api_url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $requestParams,
          CURLOPT_HTTPHEADER => array(
            "Authorization: Basic ".base64_encode($authentication),
            "Content-Type: application/json",
          ),
        ));
        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);
        echo $httpcode;
        echo $response;
        die;
    }
}

add_action('wp_enqueue_scripts', 'senior_adom_js');

function senior_adom_js() {
    if (is_single()) {
        wp_enqueue_script('senioradom', STYLESHEET_DIR_URI . '/pleinevie/assets/javascripts/senior-adom-ajax.js', ['jquery'], CACHE_VERSION_CDN, true);
    }
}

add_action('init', 'girandiere_rewrite_url');
function girandiere_rewrite_url() {
    add_rewrite_rule('(?=(residenceseniors)$)', 'index.php?page_name=residenceseniors', 'top');
}

add_filter('query_vars', 'register_custom_query_vars', 1 );
function register_custom_query_vars( $query_vars ) {
    $query_vars = array_merge($query_vars, array('page_name'));
    return $query_vars;
}

add_action('wp', 'init_lp_girandiere_template', 2001);
function init_lp_girandiere_template() {
    $page_name = get_query_var('page_name');
    if (!empty($page_name) && $page_name == 'residenceseniors'){
        add_filter('class_container_replace','make_full_width_content');
        wp_enqueue_style('lp_girandiere_css', STYLESHEET_DIR_URI . '/pleinevie/assets/stylesheets/lp_girandiere.css', array(), CACHE_VERSION_CDN);
        wp_enqueue_script('lp_girandiere_js', STYLESHEET_DIR_URI . '/pleinevie/assets/javascripts/leads.js', array(), CACHE_VERSION_CDN, true);
        ob_start();
        include locate_template('pleinevie/include/templates/lp-girandiere.php');
        $content = ob_get_contents();
        ob_end_clean() ;
        echo $content;
        exit();
    }
}

function make_full_width_content ($class){
    $class="container-fluid";
    return $class;
}

add_filter('ninja_forms_labels/req_field_error', 'change_msg_ninja_forms_front_end');
function change_msg_ninja_forms_front_end($msg) {
    $msg = "Ce champ est obligatoire. Merci de le renseigner.";
    return $msg;
}

function post_author_name_pleinevie($the_author){
    $the_author =  !empty($the_author) ? $the_author : 'La rédaction';
    return $the_author;
 }

function add_girandieres_banner_after_post(){
    global $post;
    $post_cats = wp_get_post_categories( $post->ID, array( 'fields' => 'slugs' ) );
    $girandieres_cats = ['logement', 'retraite', 'vie-quotidienne', 'nos-parents'];
    $display_banner = false;
    foreach($post_cats as $cat){
        if(in_array($cat, $girandieres_cats)){
            $display_banner = true;
            break;
        }
    }
    if($display_banner){
        echo '<img class="img-responsive" src="'.STYLESHEET_DIR_URI.'/pleinevie/assets/images/girandieres.jpg">';
    }
}


add_action('wp','override_template_category_ma_retraite_facile',20);
function override_template_category_ma_retraite_facile(){
    global $wp_query;
    if(is_category()){
        $cat_parent = rw_get_category_by_slug('ma-retraite-facile');
        $current_cat = $wp_query->queried_object;
        $category = get_category($current_cat->term_id);
        if( is_object($cat_parent) && ($current_cat->term_id == $cat_parent->term_id || $category->category_parent == $cat_parent->term_id)){
            add_filter('category_template','templates_archive_ma_retraite_facile',1000);
        }
    }
}

function templates_archive_ma_retraite_facile($temp){
    return locate_template("/pleinevie/include/templates/archive_ma_retraite_facile.php");
}

add_filter('the_menu_header','header_menu_pleinvie');

function header_menu_pleinvie(){
    $menu = '';
    $menu_id = apply_filters('get_menu_name', 'menu_header');
    $menu_element = wp_nav_menu(array('menu' => $menu_id, 'menu_class' => 'nav navbar-nav menu-site hidden-xs', 'child_of' => '$PARENT' )); 
    $menu .= '<div class="row"><div class="">'.$menu_element.'</div></div>';
    return $menu;
}
function insert_shortcode_sur_lememe_sujet($p){
    global $post;
    $same_subject_id = get_post_meta($post->ID, 'same_subject_wp_id', true);
    if(!empty( $same_subject_id)){
        $p .= do_shortcode("[lire_aussi_widget id='".$same_subject_id."']");
    }
    return $p;
}

function add_app_declaration_meta_and_links() {
    $stylesheet_dir_uri = STYLESHEET_DIR_URI;    
    $meta_and_links = <<<META_LINKS

        <meta property="fb:pages" content="106376722789823"/>
        <meta property="fb:app_id" content="139068506246475"/>
        <meta name="msvalidate.01" content="20766C0EA5A3E5E2536F98C3F55B9009"/>
        <meta name="google-play-app" content="app-id=com.mondadori.pleinevie"/>
        <meta name="apple-itunes-app" content="app-id=1086130180"/>
        <link rel="apple-touch-icon" sizes="57x57" href="$stylesheet_dir_uri/pleinevie/assets/images/favicon/apple-icon-57x57.png"/>
        <link rel="apple-touch-icon" sizes="60x60" href="$stylesheet_dir_uri/pleinevie/assets/images/favicon/apple-icon-60x60.png"/>
        <link rel="apple-touch-icon" sizes="72x72" href="$stylesheet_dir_uri/pleinevie/assets/images/favicon/apple-icon-72x72.png"/>
        <link rel="apple-touch-icon" sizes="76x76" href="$stylesheet_dir_uri/pleinevie/assets/images/favicon/apple-icon-76x76.png"/>
        <link rel="apple-touch-icon" sizes="114x114" href="$stylesheet_dir_uri/pleinevie/assets/images/favicon/apple-icon-114x114.png"/>
        <link rel="apple-touch-icon" sizes="120x120" href="$stylesheet_dir_uri/pleinevie/assets/images/favicon/apple-icon-120x120.png"/>
        <link rel="apple-touch-icon" sizes="144x144" href="$stylesheet_dir_uri/pleinevie/assets/images/favicon/apple-icon-144x144.png"/>
        <link rel="apple-touch-icon" sizes="152x152" href="$stylesheet_dir_uri/pleinevie/assets/images/favicon/apple-icon-152x152.png"/>
        <link rel="apple-touch-icon" sizes="180x180" href="$stylesheet_dir_uri/pleinevie/assets/images/favicon/apple-icon-180x180.png"/>
        <link rel="manifest" href="$stylesheet_dir_uri/pleinevie/assets/images/favicon/manifest.json"/>
        <meta name="msapplication-TileImage" content="$stylesheet_dir_uri/pleinevie/assets/images/favicon/ms-icon-144x144.png"/>
        <meta name="msapplication-TileColor" content="#d12027"/>
        <meta name="theme-color" content="#d12027"/>
        <meta name="apple-mobile-web-app-capable" content="yes"/>
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
        <meta name="apple-mobile-web-app-title" content="Pleinevie.fr"/>
      
META_LINKS;

    echo $meta_and_links;
}

add_filter('nb_posts_per_categoery', 'nb_posts_per_category_actualite');

function nb_posts_per_category_actualite($nb){
    global $wp_query;
    $current_cat = $wp_query->queried_object;
    if($current_cat && $current_cat->slug == 'actualites' ){
        $nb = 12;
    }
    return $nb;
}

add_action('init', 'lp_rewrite_url');
function lp_rewrite_url() {
    add_rewrite_rule('lp\/(edito|sea|email|rs)', 'index.php?page_name=$matches[1]', 'top');
}

add_action('wp', 'init_lp_template', 2000);
function init_lp_template() {
    $page_name = get_query_var('page_name');
    $lps = ['edito','sea','email','rs'];
    $telephone = "";
    if (!empty($page_name) && in_array($page_name,$lps)){
        // Switch on LP type 
        switch ($page_name) {
            case 'edito':
                $telephone = "0974350594";
                break;
            case 'sea':
                $telephone = "0974350590";
                break;
            case 'email':
                $telephone = "0974350591";
                break;
            case 'rs':
                $telephone = "0974350593";
                break;
        }

        add_filter('class_container_replace','make_full_width_content');
        wp_enqueue_style('lp_css', STYLESHEET_DIR_URI . '/pleinevie/assets/stylesheets/lp.css', array());
        ob_start();
        include locate_template('pleinevie/include/templates/lp-template.php');
        $content = ob_get_contents();
        ob_end_clean() ;
        echo $content;
        exit();
    }
}

function get_default_post_thumbnail($post_thumbnail) {
    if (strpos($post_thumbnail,"<img") === false) { 
        $default_post_thumbnail_url = STYLESHEET_DIR_URI .'/pleinevie/assets/images/default-post-img.jpg';
        $post_thumbnail = <<<POSTIMAGE
        <img width="365" height="200" 
        src="$default_post_thumbnail_url" 
        data-src="$default_post_thumbnail_url" 
        class="lazy-load img-responsive zoom-it wp-post-image" loading="lazy" />
POSTIMAGE;
    }
    return $post_thumbnail;
}
add_filter("post_item_image","get_default_post_thumbnail");

add_filter('cat_description_core_type_lcf','add_blocks_to_cat_description');

function add_blocks_to_cat_description($desc_cat){
    if (!current_cat_is_child_of('mes-droits') && !empty($desc_cat)) {
        return '<div class="cat_excerpt">' . $desc_cat . '</div>';
    }else{
        global $wp_query;
        $current_cat = $wp_query->queried_object;
        $image = RW_Category::get_image_category( $current_cat->term_id,'rw_medium','');
        $image = !empty($image) ? $image : '';
        return '<div class="rubrique_info">
            <div class="category_image col-sm-4 col-xs-12">
                '. $image .'
            </div>
            <div class="cat_excerpt col-sm-8 col-xs-12">'.
            get_the_archive_description().'
            </div>
        </div>';
    }
}

add_filter('custom_bloc_classes_v3','add_class_container_img', 11, 3);
function add_class_container_img($class){
    return 'thumbnail-visu';
}
