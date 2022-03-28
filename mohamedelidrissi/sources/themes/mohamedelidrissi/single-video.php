
 <?php /* Template Name: audios */ 



get_header();

?>


<title>مرئيات  <?php wp_title($sep='|');?>  </title>

      

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
  
    



<main dir="ltr" class="audio-main">



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
               

</div>

<!-- sidebar -->

<div class="page">


     <div class="main">
       <div class="categories-title">
        <a href="<?php echo site_url('videos');?>"><i class="fad fa-home"></i></a>
        <div class='cat-text'>
        <?php 
                
         $video_categories = wp_get_post_terms($post->ID, 'video-category' );

         foreach ($video_categories as $vid_cat) { 
             echo $vid_cat->name; 
         }
         
         ?>
        </div>
        <img src="<?php echo THEME_IMG_PATH; ?>\quran ico.png" width="70px" draggable="false" style="display:none;" class="quran-ico">
     </div>  

    



       <!-- *****  video  container ***** -->

  <div class="video-container">
      
    <?php 

    while ( have_posts()) {
      the_post(); 
     
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
     

    </div>



 <!-- *** song 1 **** -->

 <?php
} wp_reset_postdata();
?>
 <!-- *****  video  container ***** -->
       
       <div class="s-diviver">
                   <object type="image/svg+xml" data="<?php echo get_theme_file_uri('assets\svgs\sdivider.svg');?>"></object>
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
              <?php
                $youtube_url=get_field('video_url', $next_post->ID);;
                $fetch=explode("v=", $youtube_url);  
                $next_videoid=$fetch[1];
              ?>
            
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
                
                $video_categories = wp_get_post_terms($next_post->ID, 'video-category' );
       
                foreach ($video_categories as $vid_cat) { ?>                  
                    <a href="<?php echo get_term_link($vid_cat)?>"><?php  echo $vid_cat->name; ?></a>   
                <?php } ?>        
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
          <?php
                $youtube_url=get_field('video_url', $prev_post->ID);;
                $fetch=explode("v=", $youtube_url);  
                $prev_videoid=$fetch[1];
              ?>
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
                
                $video_categories = wp_get_post_terms($prev_post->ID, 'video-category' );
       
                foreach ($video_categories as $vid_cat) { ?>                  
                  <a href="<?php echo get_term_link($vid_cat)?>"><?php  echo $vid_cat->name; ?></a>   
              <?php } ?>   
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

 
 


