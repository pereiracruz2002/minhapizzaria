<?php
/**
 * The template part for displaying slider
 *
 * @package VW Restaurant Lite 
 * @subpackage vw_restaurant_lite
 * @since VW Restaurant Lite 1.0
 */
?>

<?php
  $content = apply_filters( 'the_content', get_the_content() );
  $audio = false;

  // Only get audio from the content if a playlist isn't present.
  if ( false === strpos( $content, 'wp-playlist-script' ) ) {
    $audio = get_media_embedded_in_content( $content, array( 'audio' ) );
  }

?>

<div id="post-<?php the_ID(); ?>" <?php post_class('inner-service'); ?>>    
  <h3 class="section-title"><a href="<?php echo esc_url( get_permalink() ); ?>" title="<?php the_title_attribute(); ?>"><?php the_title();?></a></h3>
  <div class="date-box"><?php the_time( get_option( 'date_format' ) ); ?></div>
  <div class="box-image">
    <?php
      if ( ! is_single() ) {

        // If not a single post, highlight the audio file.
        if ( ! empty( $audio ) ) {
          foreach ( $audio as $audio_html ) {
            echo '<div class="entry-audio">';
              echo $audio_html;
            echo '</div><!-- .entry-audio -->';
          }
        };

      };
    ?>
  </div>
  <div class="new-text">
    <?php the_excerpt();?>
  </div>  
  <div class="cat-box">
    <?php foreach((get_the_category()) as $category) { echo esc_html($category->cat_name) . ' '; } ?>
  </div>  
  <div class="clearfix"></div>
</div>