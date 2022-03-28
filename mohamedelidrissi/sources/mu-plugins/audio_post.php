 <?php  

 //audio custom post
 
 function audio_post() {
     register_post_type('audio', array(

      'public' => true ,
      'rewrite' => array('slug' => 'audios',
                         'with_front' => true,
                          'pages' => true,
                         'feeds' => true,
    
    
    ),
      'labels' => array (
        'has_archive' => true,
        'hierarchical' => true,
        'name' => 'Audios',
        'add_new_item' => 'Add New Audio',
        'edit_item' => 'Edit Audio',
        'view_item' => 'View Audio',
        'view_items' => 'View Audios',
        'search_items' => 'Search Audios',
        'all_items' => 'All Audios', 
        'singular_name' => 'Audio',
        'insert_into_item' => 'Insert Into Audio',
        'featured_image' => 'Audio Cover',
        'set_featured_image' => 'Set Audio Cover',
        'remove_featured_image' => 'Remove Audio Cover',
        'use_featured_image' => 'Use As Audio Cover',
        'items_list' => 'Audios List',
        'item_updated' => 'Audio Updated',
        'archives' => 'Audio Archives',
       
      ),
      'show_in_rest'=> true,
      'rest_base' => 'audio',
      'menu_icon' => 'dashicons-format-audio',
      'menu_position' => 4 ,
      'has_archive' => true,
      'hierarchical'  => true,
      'nav_menu_item' => true,
      'supports' => array( 'title','thumbnail','comments','custom-fields'),
          
     ));
 }

 

add_action('init', 'audio_post');


//hook into the init action and call create_book_taxonomies when it fires
 
add_action( 'init', 'audio_taxonomy', 0 );
 
//create a custom taxonomy name it subjects for audio posts
 
function audio_taxonomy() {
 
// Add new taxonomy, make it hierarchical like categories
//first do the translations part for GUI
 
  $labels = array(
    'name' => _x( 'Audio Category', 'taxonomy general name' ),
    'singular_name' => _x( 'Audio Category', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search for Audio Categories' ),
    'all_items' => __( 'All Audio Categories' ),
    'parent_item' => __( 'Parent Subject' ),
    'parent_item_colon' => __( 'Parent Subject:' ),
    'edit_item' => __( 'Edit Audio Category' ), 
    'update_item' => __( 'Update Audio Category' ),
    'add_new_item' => __( 'Add New Audio Category' ),
    'new_item_name' => __( 'New Audio Category Name' ),
    'menu_name' => __( 'Audio Categories' ),
 
  );    
 
// Now register the taxonomy
  register_taxonomy('audio-category',array('audio'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_in_rest' => true,
    'show_admin_column' => true,
    'query_var' => true,
    'default_term' => array(
      'name'=> 'غير مصنف',
      'slug'=> 'uncategorized',
      'description'=> '—', 
    ) 
    
  ));
 
}
