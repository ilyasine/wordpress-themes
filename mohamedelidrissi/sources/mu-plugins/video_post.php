 <?php  

 //video custom post
 
 function video_post() {
  register_post_type('video', array(

   'public' => true ,
   'rewrite' => array('slug' => 'videos',
                      'with_front' => true,
                       'pages' => true,
                        'feeds' => true,
 
 
 ),
   'labels' => array (
     'has_archive' => true,
     'hierarchical' => true,
     'name' => 'Videos',
     'add_new_item' => 'Add New Video',
     'edit_item' => 'Edit Video',
     'view_item' => 'View Video',
     'view_items' => 'View Videos',
     'search_items' => 'Search Videos',
     'all_items' => 'All Videos', 
     'singular_name' => 'Video',
     'insert_into_item' => 'Insert Into Video',
     'featured_image' => 'Video Cover',
     'set_featured_image' => 'Set Video Cover',
     'remove_featured_image' => 'Remove Video Cover',
     'use_featured_image' => 'Use As Video Cover',
     'items_list' => 'Videos List',
     'item_updated' => 'Video Updated',
     'archives' => 'Video Archives',
    
   ),
   'menu_icon' => 'dashicons-video-alt3',
   'menu_position' => 5 ,
   'has_archive' => true,
   'hierarchical'  => true,
   'nav_menu_item' => true,
   'supports' => array( 'title','thumbnail','comments','custom-fields'),
       
  ));
}

add_action('init', 'video_post');


//hook into the init action and call create_book_taxonomies when it fires
 
add_action( 'init', 'video_taxonomy', 0 );
 
//create a custom taxonomy name it subjects for audio posts
 
function video_taxonomy() {
 
// Add new taxonomy, make it hierarchical like categories
//first do the translations part for GUI
 
  $labels = array(
    'name' => _x( 'Video Category', 'taxonomy general name' ),
    'singular_name' => _x( 'Video Category', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search for Video Categories' ),
    'all_items' => __( 'All Video Categories' ),
    'parent_item' => __( 'Parent Subject' ),
    'parent_item_colon' => __( 'Parent Subject:' ),
    'edit_item' => __( 'Edit Video Category' ), 
    'update_item' => __( 'Update Video Category' ),
    'add_new_item' => __( 'Add New Video Category' ),
    'new_item_name' => __( 'New Video Category Name' ),
    'menu_name' => __( 'Video Categories' ),
  );    
 
// Now register the taxonomy
  register_taxonomy('video-category',array('video'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_in_rest' => true,
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'video-categories' ),
    'default_term' => array(
      'name'=> 'غير مصنف',
      'slug'=> 'uncategorized',
      'description'=> '—', 
    )
  ));
 
}


