<?php
/**
 * Template Name: Custom Home Page
 */

get_header(); ?>

<?php /** slider section **/ ?>
  <?php
    // Get pages set in the customizer (if any)
    $pages = array();
    for ( $count = 1; $count <= 5; $count++ ) {
    $mod = absint( get_theme_mod( 'vw_restaurant_lite_slidersettings-page-' . $count ));
    if ( 'page-none-selected' != $mod ) {
      $pages[] = $mod;
    }
    }
    if( !empty($pages) ) :
      $args = array(
        'posts_per_page' => 5,
        'post_type' => 'page',
        'post__in' => $pages,
        'orderby' => 'post__in'
      );
      $query = new WP_Query( $args );
      if ( $query->have_posts() ) :
        $count = 1;
        ?>
      <div class="slider-main">
          <div id="slider" class="nivoSlider">
            <?php
              $vw_restaurant_lite_n = 0;
          while ( $query->have_posts() ) : $query->the_post();
            
            $vw_restaurant_lite_n++;
            $vw_restaurant_lite_slideno[] = $vw_restaurant_lite_n;
            $vw_restaurant_lite_slidetitle[] = get_the_title();
            $vw_restaurant_lite_slidelink[] = esc_url(get_permalink());
            ?>
             <img src="<?php the_post_thumbnail_url('full'); ?>" title="#slidecaption<?php echo esc_attr( $vw_restaurant_lite_n ); ?>" />
            <?php
          $count++;
          endwhile;
          wp_reset_postdata();
            ?>
          </div>

          <?php
          $vw_restaurant_lite_k = 0;
            foreach( $vw_restaurant_lite_slideno as $vw_restaurant_lite_sln )
            { ?>
            <div id="slidecaption<?php echo esc_attr( $vw_restaurant_lite_sln ); ?>" class="nivo-html-caption">
              <div class="slide-cap  ">
                <div class="container">
                  <h2><?php echo esc_html($vw_restaurant_lite_slidetitle[$vw_restaurant_lite_k]); ?></h2>
                  <a class="read-more" href="<?php echo esc_url($vw_restaurant_lite_slidelink[$vw_restaurant_lite_k] ); ?>"><?php esc_html_e( 'Learn More','vw-food-corner' ); ?></a>
                </div>
              </div>
            </div>
              <?php $vw_restaurant_lite_k++;
          } ?>
      </div>
      <?php else : ?>
          <div class="header-no-slider"></div>
        <?php
      endif;
    endif;
  ?>

<?php /** second section **/ ?>
<section id="we_belive">
  <div class="container">
    <?php
    $args = array( 'name' => get_theme_mod('vw_restaurant_lite_belive_post_setting',''));
    $query = new WP_Query( $args );
    if ( $query->have_posts() ) :
      while ( $query->have_posts() ) : $query->the_post(); ?>
      <div class="row">
        <?php if(has_post_thumbnail()){ 
          $thumb_col = 'col-md-5 col-sm-5';
          $desc_col = 'col-md-7 col-sm-7';
          }else{
            $desc_col = 'col-md-12';
        } ?>
        <div class="<?php echo esc_attr($thumb_col); ?>">
          <img src="<?php the_post_thumbnail_url('full'); ?>"/>
        </div>
        <div class="<?php echo esc_attr($desc_col); ?>">
          <h3><?php the_title(); ?></h3>
          <p><?php the_content(); ?></p>
          <div class="clearfix"></div>
          <div><a class="button hvr-sweep-to-right"  href="<?php the_permalink(); ?>"><?php esc_html_e('ABOUT US','vw-food-corner'); ?></a>
          </div>
        </div>
      </div>
      <?php endwhile; 
      wp_reset_postdata();?>
      <?php else : ?>
         <div class="no-postfound"></div>
       <?php
      endif; ?>
      <div class="clearfix"></div>
  </div> 
</section>

<?php /*--OUR SERVICES--*/?>
<section id="our-services">
  <div class="container">
    <?php if( get_theme_mod('vw_food_corner_service_title') != ''){ ?>
      <h3><?php echo esc_html(get_theme_mod('vw_food_corner_service_title',__('Look Our Services','vw-food-corner'))); ?></h3>
    <?php }?>
    <?php if( get_theme_mod('vw_food_corner_service_text_line') != ''){ ?>
      <p><?php echo esc_html(get_theme_mod('vw_food_corner_service_text_line',__('Lorem Ipsum has been the industry standard dummy text ever since the 1500s','vw-food-corner'))); ?></p>
    <?php }?>
    <div class="row">
      <?php $pages = array();
        for ( $count = 0; $count <= 3; $count++ ) {
          $mod = intval( get_theme_mod( 'vw_food_corner_service_page' . $count ));
          if ( 'page-none-selected' != $mod ) {
            $pages[] = $mod;
          }
        }
        if( !empty($pages) ) :
          $args = array(
            'post_type' => 'page',
            'post__in' => $pages,
            'orderby' => 'post__in'
          );
          $query = new WP_Query( $args );
          if ( $query->have_posts() ) :
            $count = 0;
            while ( $query->have_posts() ) : $query->the_post(); ?>
              <div class="col-md-3 col-sm-3">
                <div class="page-box">
                  <div class="image-div">
                    <img src="<?php the_post_thumbnail_url('full'); ?>"/>
                  </div>
                  <h4><?php the_title(); ?></h4>
                  <p><?php the_excerpt(); ?></p>
                </div>
              </div>
            <?php $count++; endwhile; 
            wp_reset_postdata();?>
          <?php else : ?>
              <div class="no-postfound"></div>
          <?php endif;
      endif;?>
    </div>
    <div class="clearfix"></div>
  </div> 
</section>

<div class="container">
  <?php while ( have_posts() ) : the_post(); ?>
    <?php the_content(); ?>
  <?php endwhile; // end of the loop. ?>
</div>

<?php get_footer(); ?>