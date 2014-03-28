<?php
/*
  Plugin Name: WooCommerce Variation Swatches and Photos
  Plugin URI: http://woothemes.com/woocommerce/
  Description: WooCommerce Swatches and Photos allows you to configure colors and photos for shoppers on your site to use when picking variations. Requires WooCommerce 1.5.7+
  Version: 1.2
  Author: Lucas Stark
  Author URI: http://lucasstark.com
  Requires at least: 3.1
  Tested up to: 3.3

  Copyright: © 2009-2012 Lucas Stark.
  License: GNU General Public License v3.0
  License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) )
	require_once( 'woo-includes/woo-functions.php' );

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), '37bea8d549df279c8278878d081b062f', '18697' );

if (is_woocommerce_active()) {

    /**
     * Localisation
     * */
    load_plugin_textdomain('wc_swatches_and_photos', false, dirname(plugin_basename(__FILE__)) . '/');

    /**
     * woocommerce_product_addons class
     * */
    if (!class_exists('WC_SwatchesAndPhotos')) {

        class WC_SwatchesPlugin {

            private $product_attribute_images;

            public function __construct() {
                require 'woocommerce-swatches-template-functions.php';

                require 'classes/class-wc-swatch-term.php';
                require 'classes/class-wc-swatches-product-attribute-images.php';
                require 'classes/class-wc-ex-product-data-tab.php';
                require 'classes/class-wc-swatches-product-data-tab.php';
                require 'classes/class-wc-swatch-picker.php';

                add_action('init', array(&$this, 'on_init'));
                add_action('wp_enqueue_scripts', array(&$this, 'on_enqueue_scripts'));
                add_action('admin_head', array(&$this, 'on_enqueue_scripts'));

                add_action('woocommerce_locate_template', array(&$this, 'locate_template'), 10, 3);

                $this->product_attribute_images = new WC_Swatches_Product_Attribute_Images('swatches_id', 'swatches_image_size');
                $this->product_data_tab = new WC_Swatches_Product_Data_Tab();

                //Swatch Image Size Settings
                add_filter('woocommerce_catalog_settings', array(&$this, 'swatches_image_size_setting'));
            }

            public function on_init() {
                global $woocommerce;

                $swatch_image_crop = (get_option('swatches_image_size_crop') == 1) ? true : false;
                $swatch_width = get_option('swatches_image_size_width');
                $swatch_height = get_option('swatches_image_size_height');

                add_image_size('swatches_image_size', apply_filters('woocommerce_swatches_size_width_default', $swatch_width ? $swatch_width : 32), apply_filters('woocommerce_swatches_size_height_default', $swatch_height ? $swatch_height : 32), $swatch_image_crop);
            }

            public function on_enqueue_scripts() {
                global $pagenow, $wp_scripts;

                global $product;
                if (!is_admin() && is_product()) {
                    if (get_post_meta(get_the_ID(), '_swatch_type', true) == 'pickers') {
                        wp_enqueue_style('swatches-and-photos', $this->plugin_url() . '/assets/css/swatches-and-photos.css');
                        wp_enqueue_script('swatches-and-photos', $this->plugin_url() . '/assets/js/swatches-and-photos.js', array('jquery'), '1.0', true);
                    }
                } elseif (is_admin() && ( $pagenow == 'post-new.php' || $pagenow == 'post.php' || $pagenow == 'edit.php' || 'edit-tags.php')) {
                    wp_enqueue_style('swatches-and-photos', $this->plugin_url() . '/assets/css/swatches-and-photos.css');
                    wp_enqueue_script('swatches-and-photos-admin', $this->plugin_url() . '/assets/js/swatches-and-photos-admin.js', array('jquery'), '1.0', true);

                    wp_enqueue_style( 'colourpicker', $this->plugin_url() . '/assets/css/colorpicker.css' );
                    wp_enqueue_script( 'colourpicker', $this->plugin_url() . '/assets/js/colorpicker.js', array( 'jquery' ) );

                }
            }

            public function locate_template($template, $template_name, $template_path) {
                global $product;

                if (strstr($template, 'variable.php') && get_post_meta($product->id, '_swatch_type', true) == 'pickers') {
                    $template = $this->plugin_dir() . '/templates/single-product/variable.php';
                }

                return $template;
            }

            public function plugin_url() {
                return plugin_dir_url(__FILE__);
            }

            public function plugin_dir() {
                return plugin_dir_path(__FILE__);
            }

            public function swatches_image_size_setting($settings) {

                $setting = array(
                    'name' => __('Swatches and Photos', 'wc_swatches_and_photos'),
                    'desc' => __('The default size for color swatches and photos.', 'wc_swatches_and_photos'),
                    'id' => 'swatches_image_size',
                    'css' => '',
                    'type' => 'image_width',
                    'std' => '32',
                    'desc_tip' => true,
                );

                $index = count($settings) - 1;

                $settings[$index + 1] = $settings[$index];
                $settings[$index] = $setting;
                return $settings;
            }

        }

    }

    $GLOBALS['woocommerce_swatches'] = new WC_SwatchesPlugin();
}
?>