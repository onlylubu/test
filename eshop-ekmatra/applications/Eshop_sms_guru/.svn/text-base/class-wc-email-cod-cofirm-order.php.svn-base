<?php

/**
 * Customer cod confirmation Order Email
 *
 * An email sent to the customer when a cod order gets confirmed.
 *
 * @class 		WC_Email_Cod_Confirm_Order
 * @version		1.4
 * @package		Eshop_sms_guru/
 * @author 		WooThemes
 * @extends 	WC_Email
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class WC_Email_Cod_Confirm_Order extends WC_Email {

	/**
	 * Constructor
	 */
	function __construct() {

		$this->id 				= 'cod_order_confirm';
		$this->title 			= __( 'Cod Order Confirm', 'woocommerce' );
		$this->description		= __( 'This is an order notification sent to the customer after order is placed and confirmation code is succesfully entered.', 'woocommerce' );

		$this->heading 			= __( 'Thank you for your order', 'woocommerce' );
		$this->subject      	= __( 'Your {blogname} order receipt from {order_date}', 'woocommerce' );

		$this->template_html 	= 'emails/cod_cofirm_order.php';
		$this->template_plain 	= 'emails/plain/cod_confirm_order.php';

		// Triggers for this email
		//add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $this, 'trigger' ) );
		//add_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $this, 'trigger' ) );



		// Call parent constructor
		parent::__construct();

                if($_SESSION['cod_order_confirm'] =='true')
                {
                    $order_id=$_SESSION['orderid'];
                    $this->trigger($order_id);
                    unset($_SESSION['cod_order_confirm']);
                    unset($_SESSION['orderid']);
                }
	}

	/**
	 * trigger function.
	 *
	 * @access public
	 * @return void
	 */
	function trigger( $order_id ) {
		global $woocommerce;

		if ( $order_id ) {

			$this->object 		= new WC_Order( $order_id );
			$this->recipient	= $this->object->billing_email;

			$this->find[] = '{order_date}';
			$this->replace[] = date_i18n( woocommerce_date_format(), strtotime( $this->object->order_date ) );

			$this->find[] = '{order_number}';
			$this->replace[] = $this->object->get_order_number();
		}
                //if ( ! $this->get_recipient() )
                if ( ! $this->is_enabled() || ! $this->get_recipient() )                   
			return;

		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        }

	/**
	 * get_content_html function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_html() {
		ob_start();
		woocommerce_get_template( $this->template_html, array(
			'order' 		=> $this->object,
			'email_heading' => $this->get_heading()
		) );
		return ob_get_clean();
	}

	/**
	 * get_content_plain function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_plain() {
		ob_start();
		woocommerce_get_template( $this->template_plain, array(
			'order' 		=> $this->object,
			'email_heading' => $this->get_heading()
		) );
		return ob_get_clean();
	}
}