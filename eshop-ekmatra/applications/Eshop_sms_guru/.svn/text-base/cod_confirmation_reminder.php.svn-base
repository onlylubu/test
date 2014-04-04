<?php
/**
 *cod confirmation reminder file which is called by cron to send cod confirmation reminder email 3 times in certain time interval
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] .'/eshopbox/wp-load.php');
//$codstatus=get_post_meta($order->id ,'_cod_confirm',true);
$args = array(
	'post_type'       => 'shop_order',
        'meta_key'        => 'cod_confirm',
	'meta_value'      => array(0,1,2),
        'post_status'     => 'publish',
	 );
$postonhold=get_posts( $args );
//echo "hello<pre>";print_r($postonhold);
foreach($postonhold as $postob=>$value)
{
    $order_id=$value->ID;
    $i=get_post_meta($order_id ,'cod_confirm',true);
    //echo "cdjskchfdj".$i;exit;
    $i++;
    $_SESSION['cod_order_new']='true';
    $_SESSION['orderid']=$order_id;
    $email=new WC_Emails();
    update_post_meta($order_id,'cod_confirm',$i);//0 for not sent 1 for sent
}
?>