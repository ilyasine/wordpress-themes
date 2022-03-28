
 <?php /* Template Name: audios */ 

get_header();?>

<title>صوتيات  <?php wp_title($sep='|');?>  </title>
   
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
           'posts_per_page'=> 4, 
           'post_type' => 'audio',
           'meta_key'=>'popular_posts',
           'orderby'=>'meta_value_num',
           'post_status' => array( 'publish' ),
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
  

<main dir="ltr" class="audio-main">


<!-- sidebar -->
<div class="sidebar">


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
             $audio_post_category = wp_get_post_terms( $post->ID, 'audio-category');

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
 <object type="image/svg+xml" aria-label="Sidebar separator" data="<?php echo get_theme_file_uri('assets\svgs\sdivider.svg');?>"></object>
</div>

<div class="most-listened">
 <div class="s-title">الأكثر ٱستماعا</div>
  <!-- most -->
  <?php $popular_audios = new WP_Query(array('posts_per_page'=>4, 'post_type' => 'audio', 'meta_key'=>'popular_posts', 'orderby'=>'meta_value_num', 'order'=>'DESC'));
 while ($popular_audios->have_posts()) : $popular_audios->the_post(); ?>
 
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
             $audio_post_category = wp_get_post_terms( $post->ID, 'audio-category');

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
               

</div>

<!-- sidebar -->

<div class="page">
     <div class="main">

   
       <div class="categories-title">
        <a href="<?php echo site_url('audios');?>"><i class="fad fa-home"></i></a>
        <div class='cat-text'>
        <?php
             $audio_post_category = wp_get_post_terms( $post->ID, 'audio-category');

                foreach ($audio_post_category as $audio_post_cat) {
                      
                   echo $audio_post_cat->name; 
                  
                    }  
                  ?>
        </div>
        <img src="<?php echo THEME_IMG_PATH; ?>\quran ico.webp" width="70px" draggable="false" style="display:none;" alt="quran" class="quran-ico">
       
    </div>


       <!-- *****  audio  container ***** -->

     <div class="audio-container">
       <!-- *** song 1 **** -->
       
       <?php 
       
       while ( have_posts()) {
            the_post(); ?>

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
                   <i class="fab fa-spotify"></i>
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
         
       </div>
   
       <!-- *** song 1 **** -->

       <?php
  } 
 ?>
       <!-- *****  audio  container ***** -->
       
       <div class="s-diviver">
                   <object type="image/svg+xml" aria-label="Audio separator" data="<?php echo get_theme_file_uri('assets\svgs\sdivider.svg');?>"></object>
       </div>
       
       <div class="topic-share">
                          <ul class="share2social">
                             <li class="share2-facebook" onclick="shareOnfacebook()"><i class="fab fa-facebook-f"></i><b>نشر</b><span>Facebook</span></li>
                             <li class="share2-twitter" onclick="shareOntwitter()"><i class="fab fa-twitter"></i><b>تغريد</b><span>Twitter</span></li>
                             <li class="share2-linkedin" onclick="shareOnlinkedin()"><i class="fab fa-linkedin-in"></i><b>مشاركة</b><span>LinkedIn</span></li>
                             <li class="share2-pinterest" onclick="shareOnpinterest()"><i class="fab fa-pinterest-p"></i><b>حفظ</b><span>Pinterest</span></li>
                             <li class="share2-whatsapp" onclick="shareOnwhatsapp()"><i class="fab fa-whatsapp"></i><b>مشاركة</b><span>Whatsapp</span></li>
                             <li class="share2-email" onclick="shareOnemail()"><i class="fas fa-envelope"></i><b>إرسال</b><span>Email</span></li>
                             <li class="print" onclick="printpage()"><i class="fad fa-print"></i><b>طباعة</b><span>Print</span></li>
                          </ul>
                          <script>
                                  function shareOnfacebook(){
                                   window.open("https://www.facebook.com/sharer/sharer.php?u=<?php the_permalink();?> &text=<?php the_title() ;?> | محمد الإدريسي", "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=100,left=50,width=450,height=500");
                                    }
                                  function shareOntwitter(){
                                   window.open("https://twitter.com/intent/tweet?url=<?php the_permalink();?> &text=  محمد الإدريسي", "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=100,left=50,width=450,height=500");
                                    }
                                  function shareOnpinterest(){
                                   window.open("https://pinterest.com/pin/create/button/?url=<?php the_permalink();?> &description= <?php the_title() ;?> | محمد الإدريسي", "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=100,left=50,width=450,height=500");
                                    }
                                  function shareOnlinkedin(){
                                   window.open("https://www.linkedin.com/sharing/share-offsite/?url=<?php the_permalink(); ?>", "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=100,left=50,width=450,height=500");
                                    }
                                  function shareOnwhatsapp(){
                                   window.open("https://api.whatsapp.com/send?text= <?php the_title() ;?> | محمد الإدريسي <?php the_permalink(); ?> ", "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=100,left=50,width=450,height=500");
                                    }
                                  function shareOnemail(){                        
                                   window.open("https://mail.google.com/mail/?view=cm&fs=1&to=&su=<?php the_title() ;?>&body=<?php the_title() ; echo "| محمد الإدريسي    🌐 ▶  " ; the_permalink();?>", "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=100,left=50,width=450,height=500");
                                    }
                                    function printpage(){                        
                                     window.print();
                                    }     

                           </script>
                        </div> 
      
            <!-- post pagination -->
    <div class="post-pagination">

     <?php
      $prev_post = get_previous_post();
      $next_post = get_next_post();
     ?> 
   
        <div class="pag-post next">
            
          <div class="pag-post-text"><i class="fas fa-angle-double-left"></i>التالي</div> 
            <?php if (!empty( $next_post )): ?> 
            <div class="pag-post-side-item">
                <div class="pag-post-side-thumb"><a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>"><?php echo get_the_post_thumbnail( $next_post->ID, 'thumbnail' );?><a></div>
                <div class="pag-post-side-txt">
                    <div class="pag-post-side-title">
                       <a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>"><?php echo esc_attr( $next_post->post_title ); ?></a>
                    </div>
                    <div class="pag-post-side-meta-tags">  
                      <div class="pag-post-side-meta-category">
                        <i class="fad fa-tags"></i>
                        <div class="pag-post-side-meta-category-title">
                        <?php
             $audio_post_category = wp_get_post_terms( $next_post->ID, 'audio-category');

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
            <?php endif; ?>
          </div>
       
        
        <div class="post-pagination-line"></div>
        
        <div class="pag-post previous">
         <?php if (!empty( $prev_post )): ?>
          <div class="pag-post-text">السابق<i class="fas fa-angle-double-right" style="margin-left:5px"></i></div>
            <div class="pag-post-title"></div>
            <div class="pag-post-side-item">
              <div class="pag-post-side-thumb"><a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>"><?php echo get_the_post_thumbnail( $prev_post->ID, 'thumbnail' );?><a></div>
                      <div class="pag-post-side-txt">
                        <div class="pag-post-side-title">
                         <a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>"><?php echo esc_attr( $prev_post->post_title ); ?></a>
                        </div>
                        <div class="pag-post-side-meta-tags">  
                        <div class="pag-post-side-meta-category">
                          <i class="fad fa-tags"></i>
                        <div class="pag-post-side-meta-category-title">
                        <?php
             $audio_post_category = wp_get_post_terms( $prev_post->ID, 'audio-category');

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
          </div>
         <?php endif; ?>
       </div>
    <!-- post pagination --> 
    </div>
      
         <?php 
          if (comments_open()){
            comments_template();
        }
         ?>
        </div>
        
       </div>
 
     </main>
    

       <?php 
        get_footer();
       ?>

 
 


