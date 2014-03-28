<?php

/**

 * Checkout login form

 *

 * @author 		WooThemes

 * @package 	WooCommerce/Templates

 * @version     2.0.0

 */



if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



//if ( is_user_logged_in()  || ! $checkout->enable_signup ) return;



//$info_message = apply_filters( 'woocommerce_checkout_login_message', __( 'Already have an Account ?', 'woocommerce' ) );

if ( !is_user_logged_in()){ 

?>



<div class="woocommerce-info">

	<?php echo esc_html( $info_message ); ?>

    	<div class="checkout_tabblock">

    	<div class="checkout_guest">You Are Using Checkout As Guest.</div>
			<ul class="checkout_tabs">

    		<li><a href="javascript://" class="showlogin"><?php _e( 'Sign In', 'woocommerce' ); ?></a></li>

    		<li><a href="javascript://" class="showre"><?php _e( 'Create Account', 'woocommerce' ); ?></a></li>

       	</ul>

        </div>
        
        <div class="securecheckout_block">
        	<div class="pull-left">Secure Checkout</div>
            <div class="pull-right">Need Help ?  Mail us on <a href="mailto:support@ekmatra.com">support@ekmatra.com</a> or call <span>+91 84759621450</span></div>
        </div>

        </div>

    	

<?php

	woocommerce_login_form(

		array(

			'message'  => __( 'If you have an account on shopsabhyata.com, please enter your details to login.', 'woocommerce' ),

			'redirect' => get_permalink( woocommerce_get_page_id( 'checkout') ),

			'hidden'   => false

		)

	);

} else {

    $current_user = wp_get_current_user();



//echo '<pre>';

//print_r($current_user);

$euserEmail = $current_user->data->user_email;

$userDisplayName = $current_user->data->display_name;

?>

<div class="woocommerce-info">

	<div class="checkout_tabblock">

    	<div class="checkout_guest">You have been successfully logged in.</div>

        <div class="checkout_afterlogin">You are logged In as : <b><?php echo $euserEmail;  ?></b> <br /><a href="/logout">Logout</a></div>

    	

        </div>
        
         <div class="securecheckout_block">
        	<div class="pull-left">Secure Checkout</div>
            <div class="pull-right">Need Help ?  Mail us on <a href="mailto:support@ekmatra.com">support@ekmatra.com</a> or call <span>+91 84759621450</span></div>
        </div>


        </div>







<?php } ?>

