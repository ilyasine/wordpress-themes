<?php 

function video_filter_ajax() {

  $taxonomy = $_POST['taxonomy'];
  $term = $_POST['term'];
  $pagenb = $_POST['pagenb'];
  echo $pagenb ;
    /**
     * Tax query
     */
    if ($term == 'all-terms') : 

      $tax_qry[] = [
          'taxonomy' => $taxonomy,
          'field'    => 'slug',
          'terms'    => $term,
          'operator' => 'NOT IN'
      ];
  else :

      $tax_qry[] = [
          'taxonomy' => $taxonomy,
          'field'    => 'slug',
          'terms'    => $term,
      ];
  endif;

  /**
   * Setup query
   */
  $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

  $args = [
      'paged'          => $pagenb,
      'post_type'      => 'video',
      'post_status'    => 'publish',
      'posts_per_page' => 5 ,
      'tax_query'      => $tax_qry
  ];

  $filtred_videos = new WP_Query($args); 
      while ($filtred_videos -> have_posts()) {
      $filtred_videos -> the_post();

          $youtube_url=get_field('video_url');;
          $fetch=explode("v=", $youtube_url);  
          $videoid=$fetch[1];
               ?>

          <div class="video-grid">

          <div class="box" data-embed=<?php echo $videoid ?>>

            <?php the_post_thumbnail();?>
            <div class="icon-play"><i class="fad fa-play"></i></div>
          </div>

            <h3 class="video-title"><a href="<?php the_permalink();?>">“<?php the_title() ;?>”</a></h3>
          
          <div class="separator"> 
          <object type="image/svg+xml" aria-label="Video separator" data="<?php echo get_theme_file_uri('assets\svgs\separator.svg')?>"></object>
          </div>
          </div>

       <?php
  } wp_reset_postdata();
 ?>

         <div class="pagination">
           <?php 
           echo paginate_links( array(
             'post_type' => 'video',
             'total' => $filtred_videos -> max_num_pages , 
             'prev_text' => __( '<i class="fad fa-angle-double-right"></i>السابق', 'textdomain' ),
             'next_text' => __( 'التالي<i class="fad fa-angle-double-left"></i>', 'textdomain' ),
             ) ); ?>
       </div>
   <?php        
   wp_die();
}

