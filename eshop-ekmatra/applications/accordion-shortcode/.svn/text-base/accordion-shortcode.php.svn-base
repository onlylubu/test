<?php
/*
Plugin Name: Accordion Shortcode
Plugin URI: http://wordpress.org/extend/plugins/accordion-shortcode/
Description: Adds shortcode that enables you to create accordions
Author: CTLT
Version: 1.2.1
Author URI: http://ctlt.ubc.ca
*/

/**
 * OLT_Accordion_Shortcode class.
 */
class OLT_Accordion_Shortcode {
	
	static $add_script;
	static $shortcode_count;
	static $shortcode_js_data;
	
	/**
	 * init function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	static function init() {

		add_shortcode('accordion', array(__CLASS__, 'accordion_shortcode'));
		add_shortcode('accordions', array(__CLASS__, 'accordions_shortcode'));

		add_action('init', array(__CLASS__, 'register_script'));
		add_action('wp_footer', array(__CLASS__, 'print_script'));
		
		self::$shortcode_count = 0;

	}
	
	/**
	 * accordion_shortcode function.
	 * 
	 * @access public
	 * @static
	 * @param mixed $atts
	 * @param mixed $content
	 * @return void
	 */
	public static  function accordion_shortcode( $atts, $content ) {
		global $post;
		
		extract(shortcode_atts(array(
					'title' => null,
					'class' => null,
				), $atts) );

		ob_start();

		if($title): ?>
			<h3 id="<?php echo ereg_replace("[^A-Za-z0-9]", "", $title)."-".self::$shortcode_count; ?>" ><a href="#<?php echo ereg_replace("[^A-Za-z0-9]", "", $title)."-".self::$shortcode_count; ?>"><?php echo $title; ?></a></h3>

			<div class="accordian-shortcode-content <?php echo $class; ?>" >
				<?php echo do_shortcode( $content ); ?>
			</div>
		<?php elseif($post->post_title): ?>
			<h3 id="<?php echo ereg_replace("[^A-Za-z0-9]", "", $post->post_title)."-".self::$shortcode_count; ?>"><a href="#<?php echo ereg_replace("[^A-Za-z0-9]", "", $post->post_title)."-".self::$shortcode_count; ?>"><?php echo $post->post_title; ?></a></h3>

			<div class="accordion-shortcode-content <?php echo $class; ?>">
				<?php echo do_shortcode( $content ); ?>
			</div>
	    <?php else: ?>
			<span style="color:red">Please enter a title attribute like [accordion title="title name"]accordion content[accordion]</span>
		<?php endif;

		self::$shortcode_count++;

		return ob_get_clean();
	}
	
	/**
	 * eval_bool function.
	 * 
	 * @access public
	 * @static
	 * @param mixed $item
	 * @return void
	 */
	static function eval_bool( $item ) {
		
		return ( (string) $item == 'false' || (string)$item == 'null'  || (string)$item == '0' || empty($item)   ? false : true );
	}
	
	/**
	 * accordions_shortcode function.
	 * 
	 * @access public
	 * @static
	 * @param mixed $atts
	 * @param mixed $content
	 * @return void
	 */
	public static function accordions_shortcode( $atts, $content ) {
		
		self::$add_script = true;
		if( is_string($atts) )
			$atts = array();
			
		$attr['autoHeight'] = self::eval_bool( $atts['autoheight'] ); 		
		$attr['disabled']  	= self::eval_bool( $atts['disabled'] );
		$attr['active']  	= (int)$atts['active'];
		$attr['clearStyle'] = self::eval_bool( $atts['clearstyle']);
		$attr['collapsible']= self::eval_bool( $atts['collapsible']);
		$attr['fillSpace'] 	= self::eval_bool( $atts['fillspace']);
		
		$query_atts = shortcode_atts( array(
				'autoHeight'  => false,
				'disabled'   => false,
				'active'  => 0,
				'animated'   => 'slide',
				'clearStyle'  => false,
				'collapsible'  => false,
				'event'   => 'click',
				'fillSpace'  => false
			), $attr);
		$id = "random-accordion-id-".rand(0,1000);

		$content = str_replace( "]<br />","]", ( substr( $content, 0 , 6 ) == "<br />" ? substr( $content, 6 ): $content ) );

		self::$shortcode_js_data[$id] = $query_atts;

		return str_replace("\r\n", '', '<div id="'.$id.'" class="accordions-shortcode">'.do_shortcode( $content ).'</div>');

	}
	
	/**
	 * register_script function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	static function register_script() {
		wp_register_script( 'accordion-shortcode' , plugins_url('accordion.js', __FILE__), array('jquery', 'jquery-ui-core', 'jquery-ui-accordion'), '1.0', true );
	}
	
	/**
	 * print_script function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	static function print_script() {
		
		if ( ! self::$add_script )
			return;
		
		wp_enqueue_script( 'accordion-shortcode' );
		wp_localize_script( 'accordion-shortcode', 'accordion_shortcode', self::$shortcode_js_data );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-accordion' );
	}
}
// lets play
OLT_Accordion_Shortcode::init();