<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive.
 *
 * Override this template by copying it to yourtheme/woocommerce/archive-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

get_header('shop'); ?>

<div id="listingbanner">
	<div class="leftbanner"><a href="#"><img src="<?php echo bloginfo('template_url'); ?>/images/listingleftbanner.jpg" /></a></div>
    <div class="rightbanner">
    	<a href="#"><div class="title">Cotton<br/><span>kurta</span></div><div class="imageblock"><img src="<?php echo bloginfo('template_url'); ?>/images/listingsubbanner1.jpg" /></div></a>
        <a href="#"><div class="title">Cotton<br/><span>kurta</span></div><div class="imageblock"><img src="<?php echo bloginfo('template_url'); ?>/images/listingsubbanner2.jpg" /></div></a>
        <a href="#"><div class="title">Cotton<br/><span>kurta</span></div><div class="imageblock"><img src="<?php echo bloginfo('template_url'); ?>/images/listingsubbanner3.jpg" /></div></a>
        <a href="#"><div class="title">Cotton<br/><span>kurta</span></div><div class="imageblock"><img src="<?php echo bloginfo('template_url'); ?>/images/listingsubbanner4.jpg" /></div></a>
    </div>
</div>

<div class="widgetblock" id="options">
	<?php if ( ! dynamic_sidebar( 'listingpagewomencategorywidget' )) : ?><?php endif; ?>
   </div>
	
	<?php
		/**
		 * woocommerce_before_main_content hook
		 *
		 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
		 * @hooked woocommerce_breadcrumb - 20
		 */
		do_action('woocommerce_before_main_content');
	?>

		<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>

			<h1 class="page-title"><?php woocommerce_page_title(); ?></h1>

		<?php endif; ?>

		<?php do_action( 'woocommerce_archive_description' ); ?>

		<?php if ( have_posts() ) : ?>

			<?php
				/**
				 * woocommerce_before_shop_loop hook
				 *
				 * @hooked woocommerce_result_count - 20
				 * @hooked woocommerce_catalog_ordering - 30
				 */
				do_action( 'woocommerce_before_shop_loop' );
			?>

			<?php woocommerce_product_loop_start(); ?>

				<?php woocommerce_product_subcategories(); ?>

				<?php while ( have_posts() ) : the_post(); ?>

					<?php woocommerce_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

			<?php
				/**
				 * woocommerce_after_shop_loop hook
				 *
				 * @hooked woocommerce_pagination - 10
				 */
				do_action( 'woocommerce_after_shop_loop' );
			?>

		<?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

			<?php woocommerce_get_template( 'loop/no-products-found.php' ); ?>

		<?php endif; ?>

	<?php
		/**
		 * woocommerce_after_main_content hook
		 *
		 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		do_action('woocommerce_after_main_content');
	?>

	<?php
		/**
		 * woocommerce_sidebar hook
		 *
		 * @hooked woocommerce_get_sidebar - 10
		 */
		//do_action('woocommerce_sidebar');
	?>

<?php get_footer('shop'); ?>
