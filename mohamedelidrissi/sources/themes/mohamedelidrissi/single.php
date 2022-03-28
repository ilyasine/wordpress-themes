
<?php 
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
              'posts_per_page' => 4
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
              <?php $popular = new WP_Query(array('posts_per_page'=>4, 'meta_key'=>'popular_posts', 'orderby'=>'meta_value_num', 'order'=>'DESC'));
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
        <div class="main" dir="rtl">
            <?php 
              while (have_posts())
              {
                  the_post(); ?>
            <div class="blog-item">
              <div class="post-thumb"><?php the_post_thumbnail(); ?></div>
              <div class="post-txt">
                  <h2><a href="<?php the_permalink();?>"><?php the_title() ;?></a></h2>
                  <div class="hr-line"></div>
                  <div class="meta-top">
                    <div class="meta-tags">
                        <div class="meta-poster"><i class="fad fa-user-music"></i>محمد الإدريسي</div>
                        <div class="meta-category"><i class="fad fa-tags"></i><?php the_category(); ?></div>
                        <div class="meta-date">
                          <i class="fad fa-clock"></i>
                          <div class="text-date" style="word-break: break-all;"><?php the_date(); ?></div>
                        </div>
                    </div>
                  </div>
                  <div class="main-post">   
                    <?php the_content() ;?>
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
              </div>
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
                    <div class="pag-post-side-thumb">
                        <a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>"><?php echo get_the_post_thumbnail( $next_post->ID, 'thumbnail' );?>
                        <a>
                    </div>
                    <div class="pag-post-side-txt">
                    <div class="pag-post-side-title">
                    <a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>"><?php echo esc_attr( $next_post->post_title ); ?></a>
                    </div>
                    <div class="pag-post-side-meta-tags">  
                    <div class="pag-post-side-meta-category">
                    <i class="fad fa-tags"></i>
                    <div class="pag-post-side-meta-category-title">
                    <?php 
                        $blog_category = get_the_category($next_post) ;
                        foreach ( $blog_category as $blog_cat) { ?>
                    <a href="<?php echo get_term_link($blog_cat) ; ?>"><?php echo $blog_cat->name ; ?></a>
                    <?php }
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
                    <div class="pag-post-side-thumb">
                        <a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>"><?php echo get_the_post_thumbnail( $prev_post->ID, 'thumbnail' );?>
                        <a>
                    </div>
                    <div class="pag-post-side-txt">
                    <div class="pag-post-side-title">
                    <a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>"><?php echo esc_attr( $prev_post->post_title ); ?></a>
                    </div>
                    <div class="pag-post-side-meta-tags">  
                    <div class="pag-post-side-meta-category">
                    <i class="fad fa-tags"></i>
                    <div class="pag-post-side-meta-category-title">
                    <?php 
                        $blog_category = get_the_category($prev_post) ;
                        foreach ( $blog_category as $blog_cat) { ?>
                    <a href="<?php echo get_term_link($blog_cat) ; ?>"><?php echo $blog_cat->name ; ?></a>
                    <?php }
                        ?>
                    </div>
                    </div>
                    </div>      
                    </div>
                  </div>
              </div>
              <?php endif; ?>
              <?php if (empty( $prev_post )) { echo '</div>' ; } ?>
            </div>
            <!-- post pagination -->
            <div class="s-diviver">
              <object type="image/svg+xml" data="<?php echo get_theme_file_uri('assets\svgs\sdivider.svg');?>"></object>
            </div>
                <?php
                  if (comments_open()){
                    comments_template();
                        }
                  }  ?>
          </div>
        </div>
      </main>
 
 <?php 
get_footer();
?>