
 <?php /* Template Name: audios */ 

get_header();

?>

<?php

$audio_id = get_the_ID();

$audio_post_category = get_the_terms( $audio_id, 'audio-category');

while (have_posts()) {
     the_post();
}   
    ?>

<div class="newsticker">
       <div class="newstitle">
           <i class="fad fa-headphones-alt" style="margin-left: 5px;  font-size: 22px;"></i>   
           الأكثر ٱستماعا
           <div class="newsbarrier"></div>
       </div>
        <div class="post-container">
         <div class="post-items">

         <?php 
         $popular_audios = new WP_Query(array(
           'posts_per_page'=>4, 
           'post_type' => 'audio',
           'meta_key'=>'popular_posts',
           'orderby'=>'meta_value_num',
           'order'=>'DESC'));

         while ($popular_audios -> have_posts()) {
           $popular_audios -> the_post(); ?>
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
      <object type="image/svg+xml" aria-label="Divider" data="<?php echo get_theme_file_uri('assets\svgs\sdivider.svg');?>"></object>
      </div>

      <div class="last-added">
      <div class="s-title">آخر الإضافات</div>
        
      <!-- latest -->
      <?php $latestposts = new WP_Query(array(
                  'posts_per_page' => 4,
                  'post_type' => 'audio',
                  )); 
                    while ($latestposts-> have_posts()) {
                      $latestposts -> the_post() ;?>

        <div class="side-item">
        <div class="side-thumb"><a href="<?php the_permalink();?>"><?php the_post_thumbnail('thumbnail');?></a></div>
              <div class="side-txt">
                <div class="side-title">
                <a href="<?php the_permalink();?>"><?php the_title() ;?></a>
              </div>
              <div class="side-meta-tags">  
                <div class="side-meta-category">
                  <i class="fad fa-tags"></i>
                  <div class="side-meta-category-title">
                      <?php
                        $audio_post_category = wp_get_post_terms($post->ID ,'audio-category');
                          foreach ($audio_post_category as $audio_post_cat) {
                        ?>     
                        <a href="<?php echo get_term_link($audio_post_cat)?>"><?php  echo $audio_post_cat->name; ?></a>    
                        <?php
                          } 
                      ?>
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
      <div class="s-title">الأكثر ٱستماعا</div>
        <!-- most -->
        <?php 
        $popular_audios = new WP_Query(array(
          'posts_per_page'=>4, 
          'post_type' => 'audio',
          'meta_key'=>'popular_posts',
          'orderby'=>'meta_value_num',
          'order'=>'DESC'));

        while ($popular_audios -> have_posts()) :
          $popular_audios -> the_post(); ?>
      
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
                        $audio_post_category = wp_get_post_terms($post->ID ,'audio-category');
                          foreach ($audio_post_category as $audio_post_cat) {
                        ?>     
                        <a href="<?php echo get_term_link($audio_post_cat)?>"><?php  echo $audio_post_cat->name; ?></a>    
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
      <object type="image/svg+xml" aria-label="Divider" data="<?php echo get_theme_file_uri('assets\svgs\sdivider.svg');?>"></object>
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
    <?php
     $audio_categories = get_terms( 'audio-category',
           array(
             'hide_empty' => false,
             'exclude' => 37
           ) );
           ?>
          
        <div class="categories">

         <?php
          foreach ($audio_categories as $audio_cat) {
           /*  print_r($audio_cat); */
            ?>     
            <a class="btn_audio_filter" data-taxonomy="<?php echo $audio_cat->taxonomy ; ?>" data-term="<?php echo $audio_cat->slug; ?>"  href="<?php echo get_term_link($audio_cat)?>"><?php  echo $audio_cat->name; ?></a>         
            <?php
             }
             ?>
        </div>
       <div id="categories-title">
       <a class="btn_audio_filter" data-taxonomy="<?php echo $audio_cat->taxonomy ; ?>" data-term="all-terms" href="#"><i class="fad fa-home"></i></a>

         <div class="cat-text">صَوتِيات</div>

         <div class="cat-icon">

           <div class="sawtiyat-ico"><i class="fad fa-music-alt"></i></div>
           <div class="adan-ico" style="display: none;"><i class="fad fa-mosque"></i></div>
           <div class="quran-ico" style="display: none;"><img src="<?php echo THEME_IMG_PATH; ?>\quran ico.webp" width="70px" draggable="false">
           </div>
           <div class="mawal-ico" style="display: none;"><i class="fad fa-microphone-stand"></i></div>
           <div class="nachid-ico" style="display: none;"><i class="fad fa-list-music"></i></div>

         </div>
       </div>

       <!-- *****  audio  container ***** -->

     <div class="audio-container">
   
       <?php 
         
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        
        $audios = new WP_Query(array(
               'paged' => $paged,
               'posts_per_page' => 5,
               'post_type' => 'audio',
          )); 
       while ($audios -> have_posts()) {
           $audios -> the_post(); ?>
     

       <div class="grid">
         <div class="music-player-container">
           <div class="mscontainer" style="background-image: url(<?php echo get_theme_file_uri('/assets/images/white-pattern.webp')?>);">
             <div class="player-content-container">
               <h1 class="artist-name">محمد الإدريسي</h1>

               <!-- change song-title Here -->
               <h3 class="song-title"><a href="<?php the_permalink();?>">“<?php the_title() ;?>”</a></h3>
               <!-- change song-title Here -->

               <!-- Progress bar -->
               <div class="track-time">
                 <div class="start-time">00:00</div>
                 <div class="end-time">00:00</div>
               </div>
               <div class="seek-bar">
                 <div class="fill"></div>
                 <div class="handle"></div>
               </div>

               <!-- controls -->
               <div class="music-player-controls">
                 <i class="fad fa-stop"></i>
                 <i class="fad fa-play fa-flip-horizontal"></i>
                 <i class="fad fa-pause"></i>
                 <i class="fad fa-repeat-alt"></i>
                 <div class="volumeslider">
                  <input type="range" min="0" max="1" value="0.5" step="0.01">
                  <i class="fad fa-volume-up"></i>
                  <i class="fad fa-volume-slash"></i>
                 </div>
               </div>
               
               <div class="music-plateform-listen">
                 <div class="listen">: ٱستمع على</div>
                 <div class="listen-spotify" title="Spotify">
                   <a href="<?php the_field('link_on_spotify')?>" target="_blank" rel="noopener noreferrer">
                   <i class="fab fa-spotify" alt="Spotify"></i>
                   </a>
                 </div>
                 <div class="listen-anghami" title="Anghami">
                 <a href="<?php the_field('link_on_anghami')?>" target="_blank" rel="noopener noreferrer">
                    <object type="image/svg+xml" aria-label="Anghami" data="<?php echo get_theme_file_uri('assets\svgs\anghami.svg');?>"></object>
                 </a>                    
                 </div>
               </div>
             </div>

             <div class="album">

               <?php the_post_thumbnail();?>
               <!-- change song Here -->
                 <?php
                 $song = get_field('song_file');
                 if( $song ): ?>
                   <audio class="song" src="<?php echo $song['url']; ?>" preload='none' type="audio/mpeg"></audio>
                <?php endif; ?>
               <!-- change song Here -->

               <div class="vinyl"></div>
               <style>
                 .vinyl {
                    background-image:url(<?php echo get_theme_file_uri('/assets/images/cd.webp')?>) !important;
                        }
               </style>
               
             </div>
           </div>
         </div>
         <div class="separator"> 
          <object type="image/svg+xml" aria-label="Audio separator" data="<?php echo get_theme_file_uri('assets\svgs\separator.svg')?>"></object>
         </div>
       </div>
   
       <!-- *** song 1 **** -->

       <?php
  } wp_reset_postdata();

 
 ?>
       <!-- *****  audio  container ***** -->

         </div>
            <div class="pagination">
             <?php 
                echo paginate_links( array(
                 'post_type' => 'audio',
                 'current'   => max( 1, $paged ),
                 'total' => $audios -> max_num_pages , 
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

