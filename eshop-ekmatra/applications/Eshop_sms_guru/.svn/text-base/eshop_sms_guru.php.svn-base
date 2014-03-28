<?php

    /*

    Plugin Name:Eshop_Sms_Guru

    Plugin URI:

    Description:Declares a plugin that will be visible in the WordPress admin interface and help for sms integeration on your website.

    Version:1.4

    Author:Shalu

    Author URI: Eshopbox.com

    License:GPLv2

    */

global $woocommerce;

if ( !defined('TWILIOSMS_URL') )

define( 'TWILIOSMS_URL', plugin_dir_url( __FILE__ ) );

if ( !defined('TWILIOSMS_PATH') )

define( 'TWILIOSMS_PATH', plugin_dir_path( __FILE__ ) );

if ( !defined('TWILIOSMS_BASENAME') )

define( 'TWILIOSMS_BASENAME', plugin_basename( __FILE__ ) );

if ( !defined('TWILIOSMS_themeNAME') )

define( 'TWILIOSMS_THEMENAME', get_theme_root());

define( 'TWILIOSMS_FILE', __FILE__ );

if(!class_exists('Smsclass'))

{

    require TWILIOSMS_PATH.'smsclass.php' ;

}



//action hooks for order status

$allstatus=array('failed','pending','processing','completed','refunded','cancelled');



//action hooks for  status change of an order

foreach($allstatus as $key)

    {

        add_action( 'woocommerce_order_status_'.$key, array($sms,'mysite_'.$key));

    }

add_action( 'woocommerce_order_status_on-hold',array($sms,'mysite_hold') );

add_action( 'woocommerce_payment_complete', array($sms,'mysite_payment_complete'));



//function to create ui on thank you page in case of cod payment mode

function cod_confirmation_block($order_id)

    {

		//echo $order_id;exit;

		$order = new WC_Order($order_id );



		$orderdetail=$order-> order_custom_fields;

		$bilphnno=$orderdetail['_billing_phone'];

		$bilphnno= $bilphnno[0];

                 $billemail=$orderdetail['_billing_email'][0];

                $smsstatus=get_post_meta($order->id ,'cod_confirm',true);

                $bilphnno="0".$bilphnno;

		//echo "phn no".$bilphnno;exit;



       $cod_form='<span id="cod_confirm_msg" style="display: none">Your Order has been confirmed successfully</span>';

                $cod_form.='<div class="thanks_content">Your order has been placed sucessfully and right now is on-hold.</div>';

                $cod_form.='<div class="thanks_content">You can track order from your account page. A confirmation mail regarding this order has been send to <strong>'.$billemail.'</strong></div>';

                $cod_form.='<div>';

        $cod_form.='<form id="cod_form" method="post" action="" >

		<div class="confirm_text">To confirm your order you have to verify your Mobile No.</div>

		<div class="mobileno_block2">

			

			<div class="mobile_text">

				 <span class="mobile_title3">Mobile No:</span>

				 <span class="mobile_value"><strong>'.$bilphnno.'</strong></span>

                <span class="mobile_title2">Verify Code:</span>

				 <input type="hidden" id="cod_code_gen" value="'.get_post_meta($order_id ,'_order_confirmation_code',true).'"/>

				<input type="hidden" id="path" value="'.plugins_url().'"/>

				<input type="hidden" id="order_id" value="'.$order_id.'"/>

                <input type="hidden" id="order_status"  value="'.$smsstatus.'"/>

				<input type="text" id="cod_code"/>

				<input type="button" id="cod_submit" value="Verify Now!"/>

				 

				

		<div class="verify_text">Please enter the verification code sent to <strong>'.$bilphnno.'</strong>. If you have not recieved the verification

        code, please click on ‘Resend’ link.</div>

		</div>	

		<div class="clear"></div>

		<div class="courier_block">

        	<input type="button" id="cod_resend" value="Resend"/>

			<span id="cod_resent_icon" style="display: none">Resent</span>

		</div>

		</div>
		<span id="cod_confirm_msg" style="display: none">Your Order has been confirmed successfully</span>

        <span id="cod_error_msg" style="display: none">Please enter the valid confirmation code.</span>

        <span id="cod_resend_msg" style="display: none">The confirmation code has been resent to your number.</span>


        <div class="clear"></div>

		<div class="instext">*&nbsp;Our  Courier Partners will collect the order amount at the time of delivery.</div>

		<div class="instext">*&nbsp;Keep the cash ready at the time of receiving your order.</div>

		
		<div>





        </div>

        </form>

        </div> ';

    if($smsstatus == 4)

        {

          $cod_form = ' <input type="hidden" id="order_status"  value="'.$smsstatus.'"/>';

        }

        echo $cod_form;

    }



//action hook of thank you page

add_action('woocommerce_thankyou_cod', 'cod_confirmation_block', 30);

add_action( 'woocommerce_process_shop_order_meta',array($sms,'mysite_shipment_sms'));

//function to js file

function load_sms_js()

    {

        wp_enqueue_script('mysmsscript',TWILIOSMS_URL. 'sms_handling.js');

         wp_enqueue_style( 'my_style',  TWILIOSMS_URL .'sms.css');

    }

add_action('wp_enqueue_scripts','load_sms_js');// load js in non-admin pages

//add_options_page('sms_connect','sms_connect', 'manage_options', $menu_slug, $function);



function eshop_cod_cofirm_email($classobj)

{

    include_once(TWILIOSMS_PATH.'class-wc-email-cod-cofirm-order.php');

$classobj['WC_Email_Cod_Confirm_Order'] = new WC_Email_Cod_Confirm_Order();

return $classobj;

}



add_filter('woocommerce_email_classes','eshop_cod_cofirm_email',1,3);

?>