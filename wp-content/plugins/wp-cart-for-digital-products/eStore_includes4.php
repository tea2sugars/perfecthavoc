<?php

function eStore_send_notification_email($to_address, $subject, $body, $from_address, $attachment='')
{
    if (get_option('eStore_use_wp_mail'))
    {
        $headers = 'From: '.$from_address . "\r\n";
        wp_mail($to_address, $subject, $body, $headers);
        return true;
    }
    else
    {
       	if(@eStore_send_mail($to_address,$body,$subject,$from_address,$attachment))
       	{
            return true;
       	}
       	else
       	{
            return false;
       	}
    }
}
