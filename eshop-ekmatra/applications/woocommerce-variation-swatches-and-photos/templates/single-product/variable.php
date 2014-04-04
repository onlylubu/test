
<?php
/**
 * Variable Product Add to Cart
 */
global $woocommerce, $product, $post;

$variation_params = woocommerce_swatches_get_variation_form_args();

?>
<script type="text/javascript">
    var product_variations = <?php echo json_encode($variation_params['available_variations']) ?>;
    var product_attributes = <?php echo json_encode($variation_params['attributes_renamed']); ?>;
    var product_variations_flat = <?php echo json_encode($variation_params['available_variations_flat']); ?>;
</script>
<script type="text/javascript">
jQuery(document).ready(function(){
    jQuery('button[type="submit"]').bind('click',function(){
  //  jQuery('button[type="submit"]').attr("disabled", "disabled");
  //  jQuery(this).hide();
  $(this).html('Processing');
    jQuery('#proces').css('visibility','visible');
   // jQuery('.ejsclick').after('<img src="<?php echo bloginfo("template_url")  ?>/images/processing.gif"/>');

   // return false;
   });
});


</script>


<?php do_action('woocommerce_before_add_to_cart_form'); ?>
<form action="<?php echo esc_url($product->add_to_cart_url()); ?>" class="variations_form cart" method="post" enctype='multipart/form-data'>
    
    <div class="variation_form_section">
        <?php
        $woocommerce_variation_control_output = new WC_Swatch_Picker($product->id, $attributes, $selected_attributes);
        $woocommerce_variation_control_output->picker();
        ?>
    </div>
    
 	<?php do_action('woocommerce_before_add_to_cart_button'); ?>

	<div class="single_variation_wrap" style="display:none;">
		<div class="single_variation"></div>
		<div class="variations_button">
			<input type="hidden" name="variation_id" value="" />
			<div class="qtyblock">
            	<div class="qtytitle">Quantity :</div>
                <div class="qtytitlecontent">
				<?php  woocommerce_quantity_input(); ?>
                </div>
            </div>
            <div class="mainshippingblock">
            	<div itemprop="price" class="priceblock"><?php echo $product->get_price_html(); ?></div>
                <div class="plusicon">+</div>
                <div class="shippingblock">Rs. 75  Shipping</div>
                <div class="knowmorblock"><a href="#">know more</a></div>
            </div>
			<button type="submit" class="single_add_to_cart_button button alt"><?php echo apply_filters('single_add_to_cart_text', __( 'Add to cart', 'woocommerce' ), $product->product_type); ?></button>
                         <div id="proces" style="height:40px; min-width:68px; width:40%; display:inline-block; line-height:40px; text-align:center; font-weight:bold; visibility:hidden;">Processing....</div>
                </div>
	</div>
	<div><input type="hidden" name="product_id" value="<?php echo esc_attr( $post->ID ); ?>" /></div>

	<?php // do_action('woocommerce_after_add_to_cart_button'); ?>

</form>

<?php do_action('woocommerce_after_add_to_cart_form'); ?>
