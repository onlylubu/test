jQuery(document).ready(function()
    {
        var order_status = jQuery('#order_status').val();

        if( order_status != 4)
        {
        jQuery('.tickicon_orange').show();
        }
        else
        {
        jQuery('.tickicongreen').show();
         jQuery('.confirm_msg').show();
        }

        jQuery('#cod_submit').click(function()
            {
                var cod_code_entered=jQuery('#cod_code').val();
                var cod_code_saved=jQuery('#cod_code_gen').val();
                var path=jQuery('#path').val();
                var order_id=jQuery('#order_id').val();
                if(cod_code_entered  == cod_code_saved)
                    {   jQuery('.tickicongreen').show();
                         jQuery('.confirm_msg').show();
                        jQuery('#cod_form').hide();
                        jQuery('.tickicon_orange').hide();
                        jQuery('#cod_confirm_msg').show();
                        jQuery('#cod_error_msg').hide();
                        jQuery('#cod_resend_msg').hide();
                        jQuery('#cod_code').val('');
                        jQuery.ajax(
                            {
                                type: 'POST',
                                url: path +'/Eshop_sms_guru/smsclass.php',
                                data: 'cod_confirm=comfirmed'+'&order_id='+order_id,
                                success: function (data)
                                    {
                                    }
                            });
                    }
                else
                    {
                        jQuery('#cod_error_msg').show();
                        jQuery('#cod_resend_msg').hide();
                        jQuery('#cod_code').val('');
                    }
            });

        jQuery('#cod_resend').click(function()
            {
                var order_id=jQuery('#order_id').val();
                var path=jQuery('#path').val();
                jQuery('#cod_confirm_msg').hide();
				jQuery('#cod_resend').hide();
				jQuery('#cod_resent_icon').show();
                jQuery('#cod_error_msg').hide();
                jQuery('#cod_resend_msg').show();
                jQuery('#cod_code').val('');
                jQuery.ajax(
                    {
                        type: 'POST',
                        url: path +'/Eshop_sms_guru/smsclass.php',
                        data: 'cod_resend=resent'+'&order_id='+order_id,
                        success: function (data)
                            {
                            }
                    });
            });

    });