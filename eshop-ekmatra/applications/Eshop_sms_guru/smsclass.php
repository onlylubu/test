<?php
class Smsclass
{
    public function __construct()
        {
            $this-> exotel_sid = "sabhayata"; // Your Exotel SID
            $this->exotel_token = "c0b3bcaf9f589e3196fd14cc7b2465d81f926c1b"; // Your exotel token
            $this->url = "https://".$this-> exotel_sid .":". $this->exotel_token."@twilix.exotel.in/v1/Accounts/".$this-> exotel_sid."/Sms/send";
            $this->eshopexotel_sid = "boxbeattechnologies"; // eshop Exotel SID
            $this->eshopexotel_token = "8a1b4402d530d035fd298aeac118f52571f5f8dd"; // eshop exotel token
            $this->eshopurl = "https://".$this->eshopexotel_sid .":". $this->eshopexotel_token."@twilix.exotel.in/v1/Accounts/".$this->eshopexotel_sid ."/Sms/send";


        }

    //function to send sms on holding an order means on just placing an order
    public function mysite_hold($order_id)
        {
            $order = new WC_Order( $order_id );
            $orderdetail=$order-> order_custom_fields;
            $frstname=$order->shipping_first_name;
            $lstname=$order->shipping_last_name;
            $fullname=$frstname." ".$lstname;
            $bilphnno=$orderdetail[_billing_phone];
            $ordertotal=$orderdetail[_order_total];
            $ordertotal=$ordertotal[0];
            $ordertotal=intval($ordertotal);
            $bilphnno= $bilphnno[0];
            $bilphnno="+91".$bilphnno;
            $orderid=str_replace('#','',$order_id);
            $randomno=rand(100000,999999);
            $meta_key='_order_confirmation_code';
            $payment_type=get_post_meta($order->id ,'_payment_method',true);
            //$path='http://boxetplace.com/wishlist/';
            if($payment_type == 'cod')
                {
                    add_post_meta($order_id, $meta_key, $randomno);
                    $post_cod_data = array
                        (
                            'From' => '180030103339',
                            'To' => $bilphnno,
                            'Body' => "Hi ".$fullname.", your order with order ID ".$orderid." has been successfully placed. To confirm this order, you need to verify your number. Your mobile verification code is ".$randomno.".",
                        );
                    $ch1 = curl_init();
                    curl_setopt($ch1 , CURLOPT_VERBOSE, 1);
                    curl_setopt($ch1, CURLOPT_URL, $this->url);
                    curl_setopt($ch1 , CURLOPT_POST, 1);
                    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch1 , CURLOPT_FAILONERROR, 0);
                    curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, FALSE);
                    curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, FALSE);
                    curl_setopt($ch1, CURLOPT_POSTFIELDS,http_build_query($post_cod_data));
                    $http_result = curl_exec($ch1);
                    $error = curl_error($ch1);
                    //echo "error is ".$error."error finish";
                    $http_code = curl_getinfo($ch1 ,CURLINFO_HTTP_CODE);

                    curl_close($ch1);
                    // echo "<pre>";print "Response = ";print_r($http_result);exit;
                }
        }

    //function to send sms on pending status  of an order
    public function mysite_pending($order_id)
        {
            $order = new WC_Order( $order_id );
            $orderdetail=$order-> order_custom_fields;
             $frstname=$order->shipping_first_name;
            $lstname=$order->shipping_last_name;
            $fullname=$frstname." ".$lstname;
            $bilphnno=$orderdetail[_billing_phone];
            $ordertotal=$orderdetail[_order_total];

            $ordertotal=$ordertotal[0];
            $ordertotal=intval($ordertotal);
 //echo"<pre>";print_r($orderdetail);echo "ordertotal".$ordertotal;exit;
            $bilphnno= $bilphnno[0];
            $bilphnno="+91".$bilphnno;
            $orderid=str_replace('#','',$order_id);
            $payment_type=get_post_meta($order->id ,'_payment_method',true);
            if($payment_type == 'cod')
                {
                //to buyer
                    $post_data = array
                        (
                            'From' => '180030103339',
                            'To' => $bilphnno,
                            'Body' => "Hi ".$fullname.", your order with order ID ".$orderid." is confirmed. It will be shipped from our warehouse within 48 hours. Check your email for more details. Thank you for shopping at shopsabhyata.com.",

                        );
                    $ch = curl_init();
                    curl_setopt($ch , CURLOPT_VERBOSE, 1);
                    curl_setopt($ch , CURLOPT_URL, $this->url);
                    curl_setopt($ch , CURLOPT_POST, 1);
                    curl_setopt($ch , CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch , CURLOPT_FAILONERROR, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                    curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post_data));
                    $http_result = curl_exec($ch);
                    $error = curl_error($ch);
                    $http_code = curl_getinfo($ch ,CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    //to seller9999631533
                    $post_data_seller= array
                        (
                            'From' => '09266673161',
                            'To' => '9910046199',
                            'Body' =>$fullname." has placed an order with order ID ".$orderid.". Net payable amount is ".$ordertotal.".",
                        );
                    $ch1 = curl_init();
                    curl_setopt($ch1 , CURLOPT_VERBOSE, 1);
                    curl_setopt($ch1 , CURLOPT_URL, $this->eshopurl);
                    curl_setopt($ch1 , CURLOPT_POST, 1);
                    curl_setopt($ch1 , CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch1 , CURLOPT_FAILONERROR, 0);
                    curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, FALSE);
                    curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, 2);
                    curl_setopt($ch1, CURLOPT_POSTFIELDS,http_build_query($post_data_seller));
                    $http_result = curl_exec($ch1);
                    $error = curl_error($ch1);
                    $http_code = curl_getinfo($ch1 ,CURLINFO_HTTP_CODE);
                    curl_close($ch1);
                    update_post_meta($order_id,'cod_confirm',4);
                    $_SESSION['cod_order_confirm']='true';
                    $_SESSION['orderid']=$order_id;
                    $email=new WC_Emails();


                }
        }

    //function to send sms on failing of an order
    function mysite_failed($order_id)
        {
            $order = new WC_Order( $order_id );
            $orderdetail=$order-> order_custom_fields;
            $frstname=$order->shipping_first_name;
            $lstname=$order->shipping_last_name;
            $fullname=$frstname." ".$lstname;
            $bilphnno=$orderdetail[_billing_phone];
            $ordertotal=$orderdetail[_order_total];
            $ordertotal=$ordertotal[0];
            $ordertotal=intval($ordertotal);
            $bilphnno= $bilphnno[0];
            $bilphnno="+91".$bilphnno;
            $orderid=str_replace('#','',$order_id);
             $payment_type=get_post_meta($order->id ,'_payment_method',true);
            $post_data = array
                (
                    'From' => '180030103339',
                    'To' => $bilphnno,
                    'Body' => "Hi ".$fullname.", we are sorry to inform you that the transaction for order ".$order_id." is on hold. Please complete your payment.",
                );
            $ch = curl_init();
            curl_setopt($ch , CURLOPT_VERBOSE, 1);
            curl_setopt($ch , CURLOPT_URL, $this->url);
            curl_setopt($ch , CURLOPT_POST, 1);
            curl_setopt($ch , CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch , CURLOPT_FAILONERROR, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post_data));
            $http_result = curl_exec($ch);
            $error = curl_error($ch);
            $http_code = curl_getinfo($ch ,CURLINFO_HTTP_CODE);
            curl_close($ch);
        }



    //function to send sms when  an order is in processing status
    function mysite_processing($order_id)
        {
            $order = new WC_Order( $order_id );
            $orderdetail=$order-> order_custom_fields;
            $frstname=$order->shipping_first_name;
            $lstname=$order->shipping_last_name;
            $fullname=$frstname." ".$lstname;
            $bilphnno=$orderdetail[_billing_phone];
            $ordertotal=$orderdetail[_order_total];
            $ordertotal=$ordertotal[0];
            $ordertotal=intval($ordertotal);
            $bilphnno= $bilphnno[0];
            $bilphnno="+91".$bilphnno;
            $orderid=str_replace('#','',$orderid);
            $post_data = array
                (
                    'From' => '180030103339',
                    'To' => $bilphnno,
                    'Body' =>"Hi ".$fullname.", you have successfully placed an order with order ID ".$order_id.". The amount paid is Rs. ".$ordertotal.".",
                );
            $ch = curl_init();
            curl_setopt($ch , CURLOPT_VERBOSE, 1);
            curl_setopt($ch , CURLOPT_URL, $this->url);
            curl_setopt($ch , CURLOPT_POST, 1);
            curl_setopt($ch , CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch , CURLOPT_FAILONERROR, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post_data));
            $http_result = curl_exec($ch);
            $error = curl_error($ch);
            $http_code = curl_getinfo($ch ,CURLINFO_HTTP_CODE);
            curl_close($ch);

            //to seller9999631533
                    $post_data_seller= array
                        (
                            'From' => '09266673161',
                            'To' => '9910046199',
                            'Body' =>$fullname." has placed an order with order ID ".$orderid.". Amount paid is ".$ordertotal.".",
                        );
                    $ch1 = curl_init();
                    curl_setopt($ch1 , CURLOPT_VERBOSE, 1);
                    curl_setopt($ch1 , CURLOPT_URL, $this->eshopurl);
                    curl_setopt($ch1 , CURLOPT_POST, 1);
                    curl_setopt($ch1 , CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch1 , CURLOPT_FAILONERROR, 0);
                    curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, FALSE);
                    curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, 2);
                    curl_setopt($ch1, CURLOPT_POSTFIELDS,http_build_query($post_data_seller));
                    $http_result = curl_exec($ch1);
                    $error = curl_error($ch1);
                    $http_code = curl_getinfo($ch1 ,CURLINFO_HTTP_CODE);
                    curl_close($ch1);
        }

    //function to send sms when an order  gets completed
    function mysite_completed($order_id)
        {
            $order = new WC_Order( $order_id );
            $orderdetail=$order-> order_custom_fields;
           // echo "<pre>";print_r($orderdetail);exit;
            $delievery_date=$orderdetail['_date_shipped'][0] ;
            $delievery_date=date('d/m/Y',$delievery_date);
            $frstname=$order->shipping_first_name;
            $lstname=$order->shipping_last_name;
            $fullname=$frstname." ".$lstname;
            $bilphnno=$orderdetail[_billing_phone];
            $ordertotal=$orderdetail[_order_total];

            $ordertotal=$ordertotal[0];
            $ordertotal=intval($ordertotal);
            $bilphnno= $bilphnno[0];
            $bilphnno="+91".$bilphnno;
            $orderid=str_replace('#','',$order_id);
            $tracking_provider = get_post_meta( $orderid, '_tracking_provider', true );
            $tracking_number   = get_post_meta( $orderid, '_tracking_number', true );
            $post_data = array
                (
                    'From' => '180030103339',
                    'To' => $bilphnno,
                    'Body' => "Hi ".$fullname.", your order ".$orderid." is successfully delivered to you on ".$delievery_date.". Thank you for shopping at shopsabhyata.com.",

                );
            $ch = curl_init();
            curl_setopt($ch , CURLOPT_VERBOSE, 1);
            curl_setopt($ch , CURLOPT_URL, $this->url);
            curl_setopt($ch , CURLOPT_POST, 1);
            curl_setopt($ch , CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch , CURLOPT_FAILONERROR, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post_data));
            $http_result = curl_exec($ch);
            $error = curl_error($ch);
            $http_code = curl_getinfo($ch ,CURLINFO_HTTP_CODE);
            curl_close($ch);
        }

    //function to send sms when  an order get refunded
    function mysite_refunded($order_id)
        {
            $order = new WC_Order( $order_id );
            $orderdetail=$order-> order_custom_fields;
             $frstname=$order->shipping_first_name;
            $lstname=$order->shipping_last_name;
            $fullname=$frstname." ".$lstname;
            $bilphnno=$orderdetail[_billing_phone];
            $ordertotal=$orderdetail[_order_total];
            $ordertotal=$ordertotal[0];
            $ordertotal=intval($ordertotal);
            $bilphnno= $bilphnno[0];
            $bilphnno="+91".$bilphnno;
            $orderid=str_replace('#','',$order_id);
            $post_data = array
                (
                    'From' => '180030103339',
                    'To' => $bilphnno,
                    'Body' =>"Hi ".$fullname.", the refund for the payment of order with order ID ".$orderid." is in process. Please contact your bank for the same.",
                );
            $ch = curl_init();
            curl_setopt($ch , CURLOPT_VERBOSE, 1);
            curl_setopt($ch , CURLOPT_URL, $this->url);
            curl_setopt($ch , CURLOPT_POST, 1);
            curl_setopt($ch , CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch , CURLOPT_FAILONERROR, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post_data));
            $http_result = curl_exec($ch);
            $error = curl_error($ch);
            $http_code = curl_getinfo($ch ,CURLINFO_HTTP_CODE);
            curl_close($ch);
        }

    //function to send sms when  an order gets cancelled
    function mysite_cancelled($order_id)
        {   echo $order_id;
            $order = new WC_Order( $order_id );
            $orderdetail=$order-> order_custom_fields;
            $frstname=$order->shipping_first_name;
            $lstname=$order->shipping_last_name;
            $fullname=$frstname." ".$lstname;
            $bilphnno=$orderdetail[_billing_phone];
            $ordertotal=$orderdetail[_order_total];
            $ordertotal=$ordertotal[0];
            $orderid=str_replace('#','',$order_id);
            $ordertotal=intval($ordertotal);
            $bilphnno= $bilphnno[0];
            $bilphnno="+91".$bilphnno;
           //echo "after". $orderid=str_replace('#','',$order_id);exit;

            $post_data = array
                (
                    'From' => '180030103339',
                    'To' => $bilphnno,
                    'Body' =>"Hi ".$fullname.", your order with order ID ".$orderid." has been cancelled. Please check your email for more details.",
                );
            $ch = curl_init();
            curl_setopt($ch , CURLOPT_VERBOSE, 1);
            curl_setopt($ch , CURLOPT_URL, $this->url);
            curl_setopt($ch , CURLOPT_POST, 1);
            curl_setopt($ch , CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch , CURLOPT_FAILONERROR, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post_data));
            $http_result = curl_exec($ch);
            $error = curl_error($ch);
            $http_code = curl_getinfo($ch ,CURLINFO_HTTP_CODE);
            curl_close($ch);
        }

    //function to send sms when payment of an order has been made
    function mysite_payment_complete($order_id)
        {  // echo $order_id;exit;
            $order = new WC_Order( $order_id );
            $orderdetail=$order-> order_custom_fields;
            $frstname=$order->shipping_first_name;
            $lstname=$order->shipping_last_name;
            $fullname=$frstname." ".$lstname;
            $bilphnno=$orderdetail[_billing_phone];
            $ordertotal=$orderdetail[_order_total];
            $ordertotal=$ordertotal[0];
            $ordertotal=intval($ordertotal);
            $bilphnno= $bilphnno[0];
            $bilphnno="+91".$bilphnno;
            $orderid=str_replace('#','',$order_id);
            $payment_type=get_post_meta($order->id ,'_payment_method',true);

                      //to buyer

                    $post_data = array
                        (
                            'From' => '180030103339',
                            'To' => $bilphnno,
                            'Body' =>"Hi ".$fullname.", your order with ".$orderid." is successfully delivered to you soon. Thank you for shopping at shopsabhyata.com.",
                        );
                    $ch = curl_init();
                    curl_setopt($ch , CURLOPT_VERBOSE, 1);
                    curl_setopt($ch , CURLOPT_URL, $this->url);
                    curl_setopt($ch , CURLOPT_POST, 1);
                    curl_setopt($ch , CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch , CURLOPT_FAILONERROR, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                    curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post_data));
                    $http_result = curl_exec($ch);
                    $error = curl_error($ch);
                    $http_code = curl_getinfo($ch ,CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    //to seller9999631533
                    $post_data_seller= array
                        (
                            'From' => '09266673161',
                            'To' => '9910046199',
                            'Body' => $fullname." has placed an order ". $orderid." of amount ".$ordertotal." . The mode of payment is Pay Online.",
                        );
                    $ch1 = curl_init();
                    curl_setopt($ch1 , CURLOPT_VERBOSE, 1);
                    curl_setopt($ch1 , CURLOPT_URL, $this->eshopurl);
                    curl_setopt($ch1 , CURLOPT_POST, 1);
                    curl_setopt($ch1 , CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch1 , CURLOPT_FAILONERROR, 0);
                    curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, FALSE);
                    curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, 2);
                    curl_setopt($ch1, CURLOPT_POSTFIELDS,http_build_query($post_data_seller));
                    $http_result = curl_exec($ch1);
                    $error = curl_error($ch1);
                    $http_code = curl_getinfo($ch1 ,CURLINFO_HTTP_CODE);
                    curl_close($ch1);


        }
//function to send the confirmation code
    function resend_cod_code($order_id)
        {
            $order = new WC_Order($order_id);
            $orderdetail=$order-> order_custom_fields;
            $frstname=$order->shipping_first_name;
            $lstname=$order->shipping_last_name;
            $fullname=$frstname." ".$lstname;
            $bilphnno=$orderdetail[_billing_phone];
            $ordertotal=$orderdetail[_order_total];
            $ordertotal=$ordertotal[0];
            $ordertotal=intval($ordertotal);
            $bilphnno= $bilphnno[0];
            $bilphnno="+91".$bilphnno;
            $orderid=str_replace('#','',$order_id);
            $meta_key='_order_confirmation_code';
            $temp=get_post_meta($order->id ,'_order_confirmation_code',true);
            $post_cod_data = array
                (
                    'From' => '180030103339',
                    'To' => $bilphnno,
                    'Body' => "Hi ".$fullname.", your order with order ID ".$orderid." has been successfully placed. To confirm this order, you need to verify your number. Your mobile verification code is ".$temp.".",
                );
            $ch1 = curl_init();
            curl_setopt($ch1 , CURLOPT_VERBOSE, 1);
            curl_setopt($ch1, CURLOPT_URL, $this->url);
            curl_setopt($ch1 , CURLOPT_POST, 1);
            curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch1 , CURLOPT_FAILONERROR, 0);
            curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch1, CURLOPT_POSTFIELDS,http_build_query($post_cod_data));
            $http_result = curl_exec($ch1);
            $error = curl_error($ch1);
            $http_code = curl_getinfo($ch1 ,CURLINFO_HTTP_CODE);
            curl_close($ch1);
        }
//function for sending sms for shipment of order
function mysite_shipment_sms($post_id)
{
//echo $_POST['wc_order_action'];
//echo $_POST['_billing_email'];
//echo"post is"."<pre>";  print_r($_POST);exit;
//echo "post_id"."<pre>";  print_r($post_id);exit;
if($_POST['wc_order_action']== 'send_email_order_shipped')
{
    if ( isset( $_POST['tracking_number'] ) )
    {
        $tracking_provider =$_POST['tracking_provider'];
        $tracking_number=$_POST['tracking_number'];
        $firstname=$_POST['_billing_first_name'];
        $lastname=$_POST['_billing_last_name'];
        $fullname=$firstname." ".$lastname;
        $order_id=$_POST['post_ID'];
        $order = new WC_Order($order_id);
        $orderdetail=$order-> order_custom_fields;
        $ordertotal=$orderdetail['_order_total'];
        $ordertotal=$ordertotal[0];
        $ordertotal=intval($ordertotal);
        $bilphnno=$orderdetail['_billing_phone'];
        $bilphnno= $bilphnno[0];
        $bilphnno="+91".$bilphnno;
        $orderid=str_replace('#','',$order_id);
        $post_cod_data = array
        (
        'From' => '09223183143',
        'To' => $bilphnno,
        'Body' =>"Hi ".$fullname.", your shipment with shipment ID ".$tracking_number." and order ID ".$orderid." has been shipped via ".$tracking_provider." logistics and will be delivered to you soon. Please have Rs. ".$ordertotal." cash to pay for the order.",
        );
        $ch1 = curl_init();
        curl_setopt($ch1 , CURLOPT_VERBOSE, 1);
        curl_setopt($ch1, CURLOPT_URL, $this->url);
        curl_setopt($ch1 , CURLOPT_POST, 1);
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch1 , CURLOPT_FAILONERROR, 0);
        curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch1, CURLOPT_POSTFIELDS,http_build_query($post_cod_data));
        $http_result = curl_exec($ch1);
        $error = curl_error($ch1);
        $http_code = curl_getinfo($ch1 ,CURLINFO_HTTP_CODE);
        curl_close($ch1);
    }
}
}


}
$sms=new Smsclass;
//handle order cofirmation method call
if($_POST['cod_confirm'] == 'comfirmed')
    {
        if(!class_exists('WC_Order'))
            {
                require($_SERVER['DOCUMENT_ROOT'].'/eshopbox/wp-load.php');
            }
        $order_id=$_POST['order_id'];
        $order = new WC_Order($order_id);
        $order-> update_status('pending');
    }
//handle cod confirmation code resending
if($_POST['cod_resend'] == 'resent')
    {
        if(!class_exists('WC_Order'))
            {
                require($_SERVER['DOCUMENT_ROOT'].'/eshopbox/wp-load.php');
            }
        $order_id=$_POST['order_id'];
        $sms->resend_cod_code($order_id);
    }
?>