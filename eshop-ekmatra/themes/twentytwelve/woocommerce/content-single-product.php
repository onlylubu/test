<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * Override this template by copying it to yourtheme/woocommerce/content-single-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<?php
	/**
	 * woocommerce_before_single_product hook
	 *
	 * @hooked woocommerce_show_messages - 10
	 */
	 do_action( 'woocommerce_before_single_product' );
?>

<div itemscope itemtype="http://schema.org/Product" id="product-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div id="detailmain">
    	<div class="breadcrumbblock">
    	<?php if ( function_exists('yoast_breadcrumb') ) {
			yoast_breadcrumb('<p id="breadcrumbs">','</p>');
		} ?>
        <div class="backtocategory"><a href="#">Back to Women</a></div>
        </div>
    	<div class="leftblock">
        	<div class="thumbblock"><?php do_action( 'woocommerce_product_thumbnails' ); ?></div>
            <div class="imageblock">
            <?php
            /**
             * woocommerce_show_product_images hook
             *
             * @hooked woocommerce_show_product_sale_flash - 10
             * @hooked woocommerce_show_product_images - 20
             */
            do_action( 'woocommerce_before_single_product_summary' );
        ?>
        <div class="hoverzoomblock">Hover over image to zoom</div>
        </div>
       		
     	 </div>
		<div class="rightblock">
	<div class="summary entry-summary">

		<?php
			/**
			 * woocommerce_single_product_summary hook
			 *
			 * @hooked woocommerce_template_single_title - 5
			 * @hooked woocommerce_template_single_price - 10
			 * @hooked woocommerce_template_single_excerpt - 20
			 * @hooked woocommerce_template_single_add_to_cart - 30
			 * @hooked woocommerce_template_single_meta - 40
			 * @hooked woocommerce_template_single_sharing - 50
			 */
			do_action( 'woocommerce_single_product_summary' );
		?>

	</div><!-- .summary -->
    <div class="socialiconblock">
    	<ul>
        	<li><a href="http://www.facebook.com/sharer.php?u=<?php the_permalink(); ?>&t=<?php the_title(); ?>" target="_blank" class="facebookicon" title="Facebook"></a></li>
            <li><a href="http://twitter.com/share?url=<?php the_permalink(); ?>&text=<?php the_title(); ?>&via=TWITTER-HANDLE" target="_blank" class="twittericon" title="Twitter"></a></li>
            <li><a href="http://pinterest.com/pin/create/button/?url=<?php the_permalink(); ?>&amp;media=<?php echo $image->guid;?>&amp;description=<?php echo rawurlencode(get_the_title()); ?>" target="_blank" class="pinteresticon" title="Pinterest"></a></li>
        </ul>
    </div>
	<div class="accordion_div">

	<?php

    	$post_id = $post->ID;     // Your Page ID Here

        $queried_post = get_post($post_id);

        $content = $queried_post->post_content;

        $title = $queried_post->post_title;

        $content = apply_filters('the_content', $content);

        $des_content = str_replace(']]>', ']]&gt;', $content);	

       //echo $des_content;

    ?>

<?php

	$post_id = 127;     // Your Page ID Here

	$queried_post_shipping = get_post($post_id);

	$content_shipping = $queried_post_shipping->post_content;

	$title = $queried_post->post_title;

	$content_shipping1 = apply_filters('the_content', $content_shipping);

	$ship_content = str_replace(']]>', ']]&gt;', $content_shipping1);

	

	 //echo $ship_content;



?>
<?php
$fitdetails = wp_get_post_terms($post->ID,'pa_fit-details');
$washcare = wp_get_post_terms($post->ID,'pa_metarial-care');
?>

<?php echo do_shortcode( '[accordions]

[accordion title="Description"]'.$post->post_excerpt.'[/accordion]
[accordion title="Fit Details "]<p>'.$fitdetails[0]->name.'</p>[/accordion]
[accordion title="Material & care"]<p>'.$washcare[0]->name.'</p>[/accordion]

[/accordions]' )  ?>
</div>
	<?php
		/**
		 * woocommerce_after_single_product_summary hook
		 *
		 * @hooked woocommerce_output_product_data_tabs - 10
		 * @hooked woocommerce_output_related_products - 20
		 */
		do_action( 'woocommerce_after_single_product_summary' );
	?>
    </div>
</div>
</div><!-- #product-<?php the_ID(); ?> -->

<?php do_action( 'woocommerce_after_single_product' ); ?>