<?php  /* Template Name: home */ 
get_header();
?>

<div class="newsticker">
        <div class="welcometxt"></div><span class="cursor">&nbsp;</span>
</div>
     
 <main dir="ltr" class="blogs-main">
   
       <div class="page">
         <div class="main" dir="rtl">
           <div class="slideshow-galery">
             <div class="s-arrows">
              <i class="fad fa-chevron-double-right"></i>
              <i class="fad fa-chevron-double-left"></i>
             </div>
              
             <div class="slider">
  
             <?php foreach ( get_field('gallery_image','options') as $image) : ?> 
                  <img src = "<?php echo $image ?>" loading="lazy"/>
               <?php endforeach;?>

             </div>
           </div>

       </div>

       <!-- sidebar -->
       <div class="sidebar">
        <div class="side-social">
          <div class="s-title"> تواصل</div>
            <?php include('social-sidebar.php'); ?>
        </div>
       </div>
       <!-- sidebar -->
        </div>
                                          
   <section class="about-index">
     <h2 class="about-title"><i class="fad fa-user-circle"></i>عن محمد الإدريسي</h2>
     <div class="about-index-inner" >
           <div class="about_img">
               <img src="<?php echo get_theme_file_uri('/assets/images/aboutindex.webp')?>" alt="فرقة محمد الإدريسي الموسيقية" width="500px" class="placeholder" >    
           </div>
           <div class="about_text"> 
               <div class="gradient-border">
                   <div class="about-pragraph">
                       <p>محمد الإدريسي، هو فنان، منشد، مقرئ ، ومؤلف موسيقي مغربي ، ٱشتهر بأغانيه الإسلامية و خصوصا الأمداح النبوية.</p>
                       <p>  ذو صوت شجي وجميل، يستعين في إنشاده بذلك الإحساس بالنغم والجرس الموسيقي للحن والزمن 
                           الإيقاعي للجملة والمقطع وانتقاء الكلمات الهادفة، إضافة لأسلوب رائع وأداء مميز فيصبح المنتج الأخير لذلك أداء رائعًا ينال القبول والإعجاب .</p>
                       <p>ٱلتحق بالمعهد الموسيقي في سن 13 . تمكن ، بعد أربع سنوات ، من تطوير مهاراته في الموشحات العربية و الغناء الطربي .عندئذ  ..
                       </p>	
                   </div>		
                   <div class="learn-more" type="button"><a href="<?php echo site_url('/about');?>" aria-label="قراءة المزيد حول السيرة الذاتية لمحمد الإدريسي"><b>قراءة المزيد</b><i class="fal fa-external-link-alt" id="learn-more-link"></i></a></div>
               </div>
           </div>
     </div>
   </section>

    <!-- audio -->
        
       <div class="section-header">
         <span class="section-title"><i class="fad fa-list-music"></i><span class="title-text">صوتيات</span></span>
         <span class="more"><a href="<?php echo site_url('/audios');?>" aria-label="more audios"><span class="title-text">المزيد</span><i class="fad fa-angle-double-left"></i></a></span>
       </div>
       <div class="section audio">
         <div class="thumbnail-slider"> 
           <div class="thumbnail-audio-container">     
              <?php $latestposts = new WP_Query(array(
                   'posts_per_page' => 10,
                   'post_type' => 'audio',
                       )); 
              while ($latestposts-> have_posts()) {
               $latestposts -> the_post() ;?> 
          
              <div class="post-item">
               <div class="post-thumb">
                 <a href="<?php the_permalink();?>" aria-label="audio post">
                  <?php the_post_thumbnail(); ?>
                  <i class="fab fa-itunes-note" id="audio-icon"></i> 
                 </a>
               </div>
               <div class="post-txt">
                   <div class="post-title">
                     <a href="<?php the_permalink();?>" aria-label="audio post"><?php the_title() ;?></a>
                   </div>
                   <div class="post-meta-tags">  
                       <div class="post-meta-category">
                        <i class="fad fa-tags"></i>
                        <div class="post-meta-category-title custom">
                          <?php
                            $audio_post_category = wp_get_post_terms($post->ID ,'audio-category');
                                foreach ($audio_post_category as $audio_post_cat) {
                            ?>     
                            <a href="<?php echo get_term_link($audio_post_cat)?>" aria-label="audio category page" ><?php  echo $audio_post_cat->name; ?></a>    
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
           </div>
           
         </div>

       </div>

     <!-- video -->

       <div class="section-header">
         <span class="section-title"><i class="fad fa-outdent"></i><span class="title-text">مرئيات</span></span>
         <span class="more"><a href="<?php echo site_url('/videos');?>" aria-label="more videos"><span class="title-text">المزيد</span><i class="fad fa-angle-double-left"></i></a></span>
       </div>
       <div class="section video">
        <div class="thumbnail-slider"> 
           <div class="thumbnail-video-container">
         <?php $latestposts = new WP_Query(array(
                   'posts_per_page' => 10,
                   'post_type' => 'video',
                       )); 
             while ($latestposts-> have_posts()) {
               $latestposts -> the_post() ;?> 

             <div class="post-item">
               <div class="post-thumb">
                  <a href="<?php the_permalink();?>" aria-label="video post">
                    <?php the_post_thumbnail(); ?>
                    <i class="fal fa-play-circle" id="video-icon"></i>
                  </a>
               </div>
               <div class="post-txt">
                   <div class="post-title">
                     <a href="<?php the_permalink();?>" aria-label="video post"><?php the_title() ;?></a>
                   </div>
                   <div class="post-meta-tags">  
                       <div class="post-meta-category">
                        <i class="fad fa-tags"></i>
                        <div class="post-meta-category-title custom">
                          <?php
                            $video_post_category = wp_get_post_terms($post->ID ,'video-category');
                                foreach ($video_post_category as $video_post_cat) {
                            ?>     
                            <a href="<?php echo get_term_link($video_post_cat)?>" aria-label="video category page"><?php  echo $video_post_cat->name; ?></a>    
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

           </div>
      
        </div>
 
     </div>


  <!-- blog -->
  
       <div class="section-header">
         <span class="section-title"><i class="fad fa-signal-stream"></i><span class="title-text">أخبار و مواعيد</span></span>
         <span class="more"><a href="<?php echo site_url('/blog');?>" aria-label="more blogs"><span class="title-text">المزيد</span><i class="fad fa-angle-double-left"></i></a></span>
       </div>
       <div class="section blog">
       <div class="thumbnail-slider"> 
           <div class="thumbnail-blog-container">     

            <?php $latestposts = new WP_Query(array(
             'posts_per_page' => 10
           )); 
            while ($latestposts-> have_posts()) {
             $latestposts -> the_post() ;?>

             <div class="post-item">
               <div class="post-thumb">
                 <a href="<?php the_permalink();?>" aria-label="blog post">
                  <?php the_post_thumbnail(); ?>
                  <i class="fal fa-external-link-alt" id="blog-icon"></i>
                 </a>
               </div>
               <div class="post-txt">
                   <div class="post-title">
                     <a href="<?php the_permalink();?>" aria-label="blog post"><?php the_title() ;?></a>
                   </div>
                   <div class="post-meta-tags">  
                       <div class="post-meta-category">
                        <i class="fad fa-tags"></i>
                        <div class="post-meta-category-title"><?php the_category(); ?></div>
                       </div>
                   </div> 
               </div>
             </div>

<?php
 } wp_reset_postdata();
?>

             </div>
           
           </div>
 
         </div>

     </main>

 
    
<?php 
get_footer();
?>

