
<?php /* Template Name: videos */ 
get_header();
?>

<?php 
$video_categories = get_terms('video-category', array('hide_empty' => false)) ;
?>
    
<div class="newsticker">
       <div class="newstitle">
       <i class="far fa-tv-music" style="margin-left: 5px;  font-size: 22px;text-shadow: none;"></i>
           الأكثر مشاهدة
           <div class="newsbarrier"></div>
       </div>
        <div class="post-container">
         <div class="post-items">

         <?php 
         $popular_videos = new WP_Query(array(
           'posts_per_page'=>4, 
           'post_type' => 'video',
           'meta_key'=>'popular_posts',
           'orderby'=>'meta_value_num',
           'order'=>'DESC'));

         while ($popular_videos -> have_posts()) {
           $popular_videos -> the_post(); ?>
          <div class="post-item"><i class="fad fa-bookmark"></i><a href="<?php the_permalink();?>"><?php the_title() ;?></a></div>

         <?php } 
         wp_reset_postdata();
          ?>

         </div>
        </div>
        </div>
  
  <main dir="ltr" class="blogs-main">

<div class="page">
  <!-- sidebar -->
    <div class="sidebar">

    <div class="side-social">
    <div class="s-title"> تواصل</div>
    <?php include('social-sidebar.php'); ?>
    </div>

    <div class="s-diviver">
    <object type="image/svg+xml" data="<?php echo get_theme_file_uri('assets\svgs\sdivider.svg');?>"></object>
    </div>

    <div class="last-added">
    <div class="s-title">آخر الإضافات</div>
      
    <!-- latest -->
    <?php $latestposts = new WP_Query(array(
                'posts_per_page' => 4,
                'post_type' => 'video',
                )); 
                  while ( $latestposts->have_posts()) {
                    $latestposts->the_post() ;?> 

      <div class="side-item">  
      <div class="side-thumb"><a href="<?php the_permalink();?>"><?php the_post_thumbnail('thumbnail'); ?></a></div>
            <div class="side-txt">
              <div class="side-title">
              <a href="<?php the_permalink();?>"><?php the_title() ;?></a>
            </div>
            <div class="side-meta-tags">  
              <div class="side-meta-category">
                <i class="fad fa-tags"></i>
                <div class="side-meta-category-title">
                <?php
                $video_post_category = wp_get_post_terms( $post->ID, 'video-category');

                    foreach ($video_post_category as $video_post_cat) {  ?>   

                      <a href="<?php echo get_term_link($video_post_cat)?>"><?php  echo $video_post_cat->name; ?></a>
                    
                        <?php }  ?>
                      
                </div>
              </div>
            </div>      
          </div>
        </div>

    <?php
      } wp_reset_postdata();
    ?>

    <!-- latest -->
    </div>

    <div class="s-diviver">
    <object type="image/svg+xml" data="<?php echo get_theme_file_uri('assets\svgs\sdivider.svg');?>"></object>
    </div>

    <div class="most-listened">
    <div class="s-title"> الأكثر مشاهدة</div>
      <!-- most -->
      <?php $popular_videos = new WP_Query(array('posts_per_page'=>4, 'post_type' => 'video', 'meta_key'=>'popular_posts', 'orderby'=>'meta_value_num', 'order'=>'DESC'));
    while ($popular_videos->have_posts()) : $popular_videos->the_post(); ?>
    
      <div class="side-item">
            <div class="side-thumb"><a href="<?php the_permalink();?>"><?php the_post_thumbnail('thumbnail'); ?></a></div>
            <div class="side-txt">
              <div class="side-title">
              <a href="<?php the_permalink();?>"><?php the_title() ;?></a>
            </div>
            <div class="side-meta-tags">  
              <div class="side-meta-category">
                <i class="fad fa-tags"></i>
                <div class="side-meta-category-title">
                <?php
                $video_post_category = wp_get_post_terms( $post->ID, 'video-category');

                    foreach ($video_post_category as $video_post_cat) {
                      ?>     
                      <a href="<?php echo get_term_link($video_post_cat)?>"><?php  echo $video_post_cat->name; ?></a>
                      <?php
                        }  
                      ?>
                </div>
              </div>
            </div>      
          </div>
        </div>
      <?php endwhile; wp_reset_postdata(); ?>
    <!-- most -->
    </div>
                  
    <div class="s-diviver">
    <object type="image/svg+xml" aria-label="Divider"  data="<?php echo get_theme_file_uri('assets\svgs\sdivider.svg');?>"></object>
    </div>
    <div id="fb-root"></div>
    <script async="async" defer="defer" crossorigin="anonymous"
      src="https://connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v6.0"></script>
    <div class="fb-page" data-href="https://www.facebook.com/medeldrissi" data-tabs="timeline" data-width=""
    data-height="700" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false"
    data-show-facepile="true" >
    <blockquote cite="https://www.facebook.com/medeldrissi" class="fb-xfbml-parse-ignore"><a
        href="https://www.facebook.com/medeldrissi">‎الإدريسي محمد - El Idrissi Mohamed‎</a></blockquote>
    </div>

    </div>

    <!-- sidebar -->
     <div class="main">

       <div class="categories">

         <?php         
         $video_categories = get_terms( 'video-category' , array (
           'hide_empty' => false ,
           'exclude' => 36
         ));

         foreach ($video_categories as $vid_cat) { 
          /* print_r($vid_cat); */
           ?>
         
           <a class="btn_video_filter" data-taxonomy="<?php echo $vid_cat->taxonomy ; ?>" data-term="<?php echo $vid_cat->slug; ?>"  href="<?php echo get_term_link($vid_cat)?>"><?php  echo $vid_cat->name; ?></a>

        <?php }
         
         ?>
       </div>
       <div id="categories-title">
       <a class="btn_video_filter" data-taxonomy="<?php echo $vid_cat->taxonomy ; ?>" data-term="all-terms" href="#"><i class="fad fa-home"></i></a>

         <div class="cat-text">مَرئِيّات</div>

         <div class="cat-icon">

           <div class="mariyat-ico"><i class="far fa-tv-music"></i></div>
           <div class="adan-ico" style="display: none;"><i class="fad fa-mosque"></i></div>
           <div class="quran-ico" style="display: none;"><img src="<?php echo THEME_IMG_PATH; ?>\quran ico.webp" width="70px" draggable="false">
           </div>
           <div class="mawal-ico" style="display: none;"><i class="fad fa-microphone-stand"></i></div>
           <div class="nachid-ico" style="display: none;"><i class="fad fa-list-music"></i></div>
           <div class="saharat-ico" style="display: none;"><i class="fad fa-gramophone"></i></div>

         </div>
       </div>

       <!-- *****  video  container ***** -->

     <div class="video-container">
 
       <?php 
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        
        $videos = new WP_Query(array(
               'paged' => $paged,
               'posts_per_page' => 5,
               'post_type' => 'video',
          )); 
       while ($videos -> have_posts()) {
           $videos -> the_post();
 
          $youtube_url=get_field('video_url');
          $fetch=explode("v=", $youtube_url);  
          $videoid=$fetch[1];  ?>

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

      
       <!-- *** song 1 **** -->

       <?php
  } wp_reset_postdata();
 ?>
       <!-- *****  video  container ***** -->
         </div>
         <div class="pagination">
           <?php 
           echo paginate_links( array(
             'post_type' => 'video',
             'total' => $videos -> max_num_pages , 
             'prev_text' => __( '<i class="fad fa-angle-double-right"></i>السابق', 'textdomain' ),
             'next_text' => __( 'التالي<i class="fad fa-angle-double-left"></i>', 'textdomain' ),
             ) ); ?>
       </div>
        </div>
       </div>
     </main>
       <?php 
get_footer();
?>

