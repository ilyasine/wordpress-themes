<?php 

function audio_filter_ajax() {

  $taxonomy = $_POST['taxonomy'];
  $term = $_POST['term'];
  $pagenb = $_POST['pagenb'];

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
      'post_type'      => 'audio',
      'post_status'    => 'publish',
      'posts_per_page' => 5 ,
      'tax_query'      => $tax_qry
  ];

  $filtred_audios = new WP_Query($args); 
      while ($filtred_audios -> have_posts()) {
      $filtred_audios -> the_post(); ?>


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
                <a href="<?php the_field('link_on_spotify')?>" target="_blank">
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
        <div class="separator"> 
        <object type="image/svg+xml" data="<?php echo get_theme_file_uri('assets\svgs\separator.svg')?>"></object>
        </div>
      </div>

      <!-- *** song 1 **** -->

      
<?php
 
} wp_reset_postdata();
 ?>
      
  </div> 

  <?php
   wp_die();
}

