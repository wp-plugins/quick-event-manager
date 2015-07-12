<?php

function qem_process_payment_form($values) {
    $payments = qem_get_stored_payment();
    global $post;
    $page_url = qem_current_page_url();
    $reference = $post->post_title;
    $paypalurl = 'https://www.paypal.com/cgi-bin/webscr';
    $cost = get_post_meta($post->ID, 'event_cost', true);
    $cost = preg_replace ( '/[^.0-9]/', '', $cost);
    $quantity = ($values['yourplaces'] < 1 ? 1 : strip_tags($values['yourplaces']));
    $redirect = get_post_meta($post->ID, 'event_redirect', true);
    
    if (!$redirect && $register['redirectionurl']) {
        $redirect = $register['redirectionurl'];
    }
    $redirect = ($redirect ? $redirect : $page_url);
    
    if ($payments['useprocess'] && $payments['processtype'] == 'processpercent') {
        $percent = preg_replace ( '/[^.,0-9]/', '', $payments['processpercent']) / 100;
        $handling = $cost * $quantity * $percent;
    }
    
    if ($payments['useprocess'] && $payments['processtype'] == 'processfixed') {
        $handling = preg_replace ( '/[^.,0-9]/', '', $payments['processfixed']);
    }
    
    if ($payments['usecoupon']) {
        $coupon = qem_get_stored_coupon();
        for ($i=1; $i<=10; $i++) {
            if ($values['couponcode'] == $coupon['code'.$i]) {
                if ($coupon['coupontype'.$i] == 'percent'.$i) $cost = $cost - ($cost * $coupon['couponpercent'.$i]/100);
                if ($coupon['coupontype'.$i] == 'fixed'.$i) $cost = $cost - $coupon['couponfixed'.$i];
            }
        }
    }
    $content = '<h2 id="qem_reload">'.$payments['waiting'].'</h2>
    <form action="'.$paypalurl.'" method="post" name="qempay" id="qempay">
    <input type="hidden" name="cmd" value="_xclick">
    <input type="hidden" name="item_name" value="'.$reference.'"/>
    <input type="hidden" name="business" value="'.$payments['paypalemail'].'">
    <input type="hidden" name="return" value="'.$redirect.'">
    <input type="hidden" name="cancel_return" value="'.$page_url.'">
    <input type="hidden" name="currency_code" value="'.$payments['currency'].'">
    <input type="hidden" name="item_number" value="' . strip_tags($values['yourname']) . '">
    <input type="hidden" name="quantity" value="' . $quantity . '">
    <input type="hidden" name="amount" value="' . $cost . '">
    <input type="hidden" name="custom" value="' . $values['ipn'] . '">';

    if ($payments['useprocess']) {
        $content .='<input type="hidden" name="handling" value="' . $handling . '">';
    }
    $content .= '</form>
    <script language="JavaScript">document.getElementById("qempay").submit();</script>';
    return $content;
}

function qem_current_page_url() {
    $pageURL = 'http';
    if( isset($_SERVER["HTTPS"]) ) { if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";} }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    else $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    return $pageURL;
}