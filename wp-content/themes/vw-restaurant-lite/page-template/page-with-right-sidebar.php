<?php
/**
 * Template Name:Page with Right Sidebar
 */

get_header(); ?>

<?php do_action( 'vw_restaurant_lite_pageright_top' ); ?>

<div class="container">
    <div class="middle-align row">       
		<div class="col-md-8" id="content-vw">

			<?php while ( have_posts() ) : the_post(); ?>	
                
                <h1><?php the_title();?></h1>
                <img src="<?php the_post_thumbnail_url(); ?>" width="100%">
                
                <?php the_content();

                    //If comments are open or we have at least one comment, load up the comment template
                	if ( comments_open() || '0' != get_comments_number() )
                    	comments_template();
                ?>
            <?php endwhile; // end of the loop. ?>
            
        </div>
        <div id="sidebar" class="col-md-4">
			<?php dynamic_sidebar('sidebar-2'); ?>
		</div>
        <div class="clear"></div>    
    </div>
</div>

<?php do_action( 'vw_restaurant_lite_pageright_bottom' ); ?>

<?php get_footer(); ?>