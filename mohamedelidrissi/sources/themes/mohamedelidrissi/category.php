
<?php   /* Template Name: blog */ 
 get_header();

?>

 <div class="newsticker">
        <div class="newstitle">
            <i class="fad fa-rss" style="margin-left: 5px;"></i>   
            آخِر الأَخْبَار
            <div class="newsbarrier"></div>
        </div>
         <div class="post-container">
          <div class="post-items">
          <?php $latestposts = new WP_Query(array(
              'posts_per_page' => 5
              )); 
               while ($latestposts-> have_posts()) {
                 $latestposts -> the_post() ;?> 
            <div class="post-item"><i class="fad fa-bookmark"></i><a href="<?php the_permalink();?>"><?php the_title() ;?></a></div>
                 <?php
                   }wp_reset_postdata();
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
 <div class="s-title"> أحدث الأخبار</div>

 <!-- latest -->
 <?php $latestposts = new WP_Query(array(
   'posts_per_page' => 4
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
             <div class="side-meta-category-title"><?php the_category(); ?></div>
           </div>
         </div>      
       </div>
     </div>

<?php
  } wp_reset_postdata();
 ?>
</div>
 <!-- latest -->


<div class="s-diviver">
 <object type="image/svg+xml" data="<?php echo get_theme_file_uri('assets\svgs\sdivider.svg');?>"></object>
</div>

<div class="most-visited">
 <div class="s-title">الأكثر زيارة</div>
  <!-- most -->
  <?php $popular = new WP_Query(array('posts_per_page'=> 4, 'meta_key'=>'popular_posts', 'orderby'=>'meta_value_num', 'order'=>'DESC'));
 while ($popular->have_posts()) : $popular->the_post(); ?>
 
 <div class="side-item">
         <div class="side-thumb"><a href="<?php the_permalink();?>"><?php the_post_thumbnail('thumbnail'); ?></a></div>
         <div class="side-txt">
          <div class="side-title">
           <a href="<?php the_permalink();?>"><?php the_title() ;?></a>
         </div>
         <div class="side-meta-tags">  
           <div class="side-meta-category">
             <i class="fad fa-tags"></i>
             <div class="side-meta-category-title"><?php the_category(); ?></div>
           </div>
         </div>      
       </div>
     </div>
 <?php endwhile; wp_reset_postdata(); ?>
 <!-- most -->
</div>

<div class="s-diviver">
 <object type="image/svg+xml" data="<?php echo get_theme_file_uri('assets\svgs\sdivider.svg');?>"></object>
</div>

<div class="fb-page" data-href="https://www.facebook.com/medeldrissi" data-tabs="timeline" data-width=""
 data-height="700" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false"
 data-show-facepile="true" >
 <blockquote cite="https://www.facebook.com/medeldrissi" class="fb-xfbml-parse-ignore"><a
     href="https://www.facebook.com/medeldrissi">‎الإدريسي محمد - El Idrissi Mohamed‎</a></blockquote>
</div>

</div>
<!-- sidebar -->
          <div class="main" dir="rtl">
           
            <!-- *****  blog  container ***** -->
           <div id="categories-title">
              <div class="cat-text"><?php echo single_term_title(); ?></div>
           </div>

           <div class="blog-container">
              <!-- *** blog 1 **** -->
            <?php  while (have_posts()) {
               the_post(); ?>
              <div class="grid-post">
                    <div class="blog-item"> 
                      <div class="post-thumb"><a href="<?php the_permalink();?>"><i class="fal fa-external-link-alt" id="thumb-link"></i><?php the_post_thumbnail(); ?></a></div>
                      <div class="post-txt">
                        <h2><a href="<?php the_permalink();?>"><?php the_title() ;?></a></h2>
                        <div class="meta-top">                         
                           <div class="meta-tags">
                            <div class="meta-poster"><i class="fad fa-user-music"></i>محمد الإدريسي</div>
                            <div class="meta-category"><i class="fad fa-tags"></i><?php the_category(); ?></div>
                            <div class="meta-date"><i class="fad fa-clock"></i><?php the_date(); ?></div>
                           </div>                          
                        </div>
                            
                        <?php the_excerpt() ;?>
                        
                        <div class="meta-bottom"> 
                         <div class="learn-more" type="button"><a href="<?php the_permalink();?>" target="blank"><b>قراءة المزيد</b><i class="fal fa-external-link-alt" id="learn-more-link"></i></a></div>
                            <div class="share-post">
                             <div class="share-btn">
                               <i class="fas fa-share-alt-square"></i>
                               <div class="share-arrow"></div>
                            </div>
                             <div class="share-list">
                              <a onClick="shareOnpinterest()"><i class="fab fa-pinterest"></i></a>
                              <a onClick="shareOntwitter()" ><i class="fab fa-twitter"></i></a>
                              <a onClick="shareOnFB()"><i class="fab fa-facebook"></i></a>
                             </div>
                               <script>
                                  function shareOnFB(){
                                   window.open("https://www.facebook.com/sharer/sharer.php?u=<?php the_permalink();?> &text=<?php the_title() ;?> | محمد الإدريسي", "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=100,left=50,width=450,height=500");
                                    }
                                  function shareOntwitter(){
                                   window.open("https://twitter.com/intent/tweet?url=<?php the_permalink();?> &text=  محمد الإدريسي", "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=100,left=50,width=450,height=500");
                                    }
                                  function shareOnpinterest(){
                                   window.open("https://pinterest.com/pin/create/button/?url=<?php the_permalink();?> &description= <?php the_title() ;?> | محمد الإدريسي", "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=100,left=50,width=450,height=500");
                                    }
                               </script>
                            </div>  
                        </div>
                        
                      </div>
                    </div>
               <!-- *** blog 1 **** -->
               <div class="separator">
                 <object type="image/svg+xml" data="<?php echo get_theme_file_uri('assets\svgs\separator.svg')?>"></object>
                 
                </div>
              </div>
            <?php  }
            ?>

            <!-- *****  blog  container ***** -->
            <div class="pagination">
            <?php echo paginate_links( array(

            	'prev_text' => __( '<i class="fad fa-angle-double-right"></i>السابق', 'textdomain' ),
            	'next_text' => __( 'التالي<i class="fad fa-angle-double-left"></i>', 'textdomain' ),
              ) ); ?>
           </div>
              

               
          </div>
         </div>
        </div>

  </main>

  
     
 <?php 
get_footer();
?>

