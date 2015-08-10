<?php

function qem_loop() {
    ob_start();
    if (isset($_POST['qemregister']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $formvalues = $_POST;
        $formerrors = array();
        if (!qem_verify_form($formvalues, $formerrors)) {
            qem_display_form($formvalues, $formerrors);
        } else {
            qem_process_form($formvalues);
            $formvalues['completed'] = 'checked';
            qem_display_form($formvalues, null);
        }
    } else {
        $values = qem_get_stored_register();
        $payment = qem_get_stored_payment(); 
        if ( is_user_logged_in() && $values['showuser']) {
            $current_user = wp_get_current_user();
            $values['yourname'] = $current_user->user_login;
            $values['youremail'] = $current_user->user_email;
        }
        $values['yourplaces'] = '1';
        $values['yournumber1'] = '';
        $values['couponcode'] = $payment['couponcode'];
        $values['ipn'] = md5(mt_rand());
        $digit1 = mt_rand(1,10);
        $digit2 = mt_rand(1,10);
        if( $digit2 >= $digit1 ) {
            $values['thesum'] = "$digit1 + $digit2";
            $values['answer'] = $digit1 + $digit2;
        } else {
            $values['thesum'] = "$digit1 - $digit2";
            $values['answer'] = $digit1 - $digit2;
        }
    if ( (is_user_logged_in() && $values['registeredusers']) || !$values['registeredusers'] ) 
        qem_display_form( $values ,null);}
    $output_string=ob_get_contents();
    ob_end_clean();
    return $output_string;
}


function qem_display_form( $values, $errors ) {
    $register = qem_get_stored_register();
    $payment = qem_get_stored_payment();
    global $post;
    $event=get_the_ID();
    $check = get_post_meta($post->ID, 'event_counter', true);
    $cost = get_post_meta($post->ID, 'event_cost', true);
    $paypal = get_post_meta($post->ID, 'event_paypal', true);
    if ($paypal && $cost) $payment['paypal'] = 'checked';
    $number = get_post_meta($event, 'event_number', true);
    if ($check) $num = qem_numberscoming($register,$event,$payment);
    $content = qem_totalcoming($register,$payment);
    $content .= qem_whoscoming($register,$payment);
    
    if ($errors['spam']) {
        $errors['alreadyregistered'] = 'checked';
        $register['alreadyregistered'] = $register['spam'];
    } elseif ($values['completed']) {
        if (!empty($register['replytitle'])) {
            $register['replytitle'] = '<h2>' . $register['replytitle'] . '</h2>';
        }
        if (!empty($register['replyblurb'])) {
            $register['replyblurb'] = '<p>' . $register['replyblurb'] . '</p>';
        }
        $content .= $register['replytitle'].$register['replyblurb'];
        
        if (function_exists('qpp_loop') && $cost && $payment['useqpp']) {
            $id = $payment['qppform'];
            $title = get_the_title();
            $args = array('form' => $id, 'id' => $title, 'amount' => $cost);
            $content .= qpp_loop($args);
        } elseif ((($payment['paypal'] && !$paypal) || $paypal=='checked') && $cost) {
            $content .=  '<a id="qem_reload"></a>';
            $content .= '<script type="text/javascript" language="javascript">
        document.querySelector("#qem_reload").scrollIntoView();
        </script>';
            $content .= qem_process_payment_form($values);
        } elseif ($register['useread_more']) {
            $content .= '<p><a href="' . get_permalink() . '">' . $register['read_more'] . '</a></p>';
        }
        $content .=  '<a id="qem_reload"></a>';
    } elseif (!$num && $check && $number) {
        $content .= '<h2>' . $register['eventfullmessage'] . '</h2>';
        $content .=  '<a id="qem_reload"></a>';
    } elseif ($errors['alreadyregistered'] == 'checked') {
        $content .= $num.'<h2>' . $register['alreadyregistered'] . '</h2>';
        if ($register['useread_more']) $content .= '<p><a href="' . get_permalink() . '">' . $register['read_more'] . '</a></p>';
        $content .=  '<a id="qem_reload"></a>';
    } elseif ($errors['alreadyregistered'] == 'removed') {
        $content .= $num.'<h2>' . $register['nameremoved'] . '</h2>';
        if ($register['useread_more']) $content .= '<p><a href="' . get_permalink() . '">' . $register['read_more'] . '</a></p>';
        $content .=  '<a id="qem_reload"></a>';
    } else {
        if (!empty($register['title'])) {
            $register['title'] = '<h2>' . $register['title'] . '</h2>';
        }
        if (!empty($register['blurb'])) {
            $register['blurb'] = '<p>' . $register['blurb'] . '</p>';
        }
        $content .= '<div class="qem-register">';
        if (count($errors) > 0) {
            $content .= "<h2 style='color:red'>" . $register['error'] . "</h2>\r\t";
            $arr = array('yourname','youremail','yourtelephone','yourplaces','yourmessage','youranswer','yourblank1','yourblank2','yourdropdown');
            foreach ($arr as $item) if ($errors[$item] == 'error') $errors[$item] = ' style="border:1px solid red;" ';
            if ($errors['yourplaces']) $errors['yourplaces'] = 'border:1px solid red;';
            if ($errors['yournumber1']) $errors['yournumber1'] = 'border:1px solid red;';
            if ($errors['youranswer']) $errors['youranswer'] = 'border:1px solid red;';
        } else {
            $content .= $register['title'] . $register['blurb'];
        }
        $content .= $num;
        $content .= '<form action="" method="POST" enctype="multipart/form-data">';
        
        foreach (explode( ',',$register['sort']) as $name) {
            switch ( $name ) {
                case 'field1':
                if ($register['usename'])
                    $content .= '<input id="yourname" name="yourname" '.$errors['yourname'].' type="text" value="'.$values['yourname'].'"onblur="if (this.value == \'\') {this.value = \''.$values['yourname'].'\';}" onfocus="if (this.value == \''.$values['yourname'].'\') {this.value = \'\';}" />'."\n";
                break;
                case 'field2':
                if ($register['usemail']) 
                    $content .= '<input id="email" name="youremail" '.$errors['youremail'].' type="text" value="'.$values['youremail'].'" onblur="if (this.value == \'\') {this.value = \''.$values['youremail'].'\';}" onfocus="if (this.value == \''.$values['youremail'].'\') {this.value = \'\';}" />';
                break;
                case 'field3':        
                if ($register['useattend']) 
                $content .= '<p><input type="checkbox" name="notattend" value="checked" '.$values['notattend'].' /> '.$register['yourattend'].'</p>';
                break;
                case 'field4':
                if ($register['usetelephone']) 
                    $content .= '<input id="email" name="yourtelephone" '.$errors['yourtelephone'].' type="text" value="'.$values['yourtelephone'].'" onblur="if (this.value == \'\') {this.value = \''.$values['yourtelephone'].'\';}" onfocus="if (this.value == \''.$values['yourtelephone'].'\') {this.value = \'\';}" />';
                break;
                case 'field5':
                if ($register['useplaces']) 
                    $content .= '<p><input id="yourplaces" name="yourplaces" type="text" style="'.$errors['yourplaces'].'width:3em;margin-right:5px" value="'.$values['yourplaces'].'" onblur="if (this.value == \'\') {this.value = \''.$values['yourplaces'].'\';}" onfocus="if (this.value == \''.$values['yourplaces'].'\') {this.value = \'\';}" />'.$register['yourplaces'].'</p>';
                else 
                    $content .= '<input type="hidden" name="yourplaces" value="1">';
                if ($register['usemorenames']) 
                    $content .= '<div id="morenames" hidden="hidden"><p>'.$register['morenames'].'</p>
                    <textarea rows="4" label="message" name="morenames"></textarea></div>';
                break;
                case 'field6':
                if ($register['usemessage']) 
                    $content .= '<textarea rows="4" label="message" name="yourmessage" '.$errors['yourmessage'].' onblur="if (this.value == \'\') {this.value = \''.$values['yourmessage'].'\';}" onfocus="if (this.value == \''.$values['yourmessage'].'\') {this.value = \'\';}" />' . stripslashes($values['yourmessage']) . '</textarea>';
                break;
                case 'field7':
                if ($register['usecaptcha']) 
                    $content .= $values['thesum'].' = <input id="youranswer" name="youranswer" type="text" style="'.$errors['youranswer'].'width:3em;"  value="'.$values['youranswer'].'" onblur="if (this.value == \'\') {this.value = \''.$values['youranswer'].'\';}" onfocus="if (this.value == \''.$values['youranswer'].'\') {this.value = \'\';}" /><input type="hidden" name="answer" value="' . strip_tags($values['answer']) . '" />
<input type="hidden" name="thesum" value="' . strip_tags($values['thesum']) . '" />';
                break;
                case 'field8':
                if ($register['usecopy']) 
                    $content .= '<p><input type="checkbox" name="qem-copy" value="checked" '.$values['qem-copy'].' /> '.$register['copyblurb'].'</p>';
                break;
                case 'field9':
                if ($register['useblank1']) 
                    $content .= '<input id="yourblank1" name="yourblank1" '.$errors['yourblank1'].' type="text" value="'.$values['yourblank1'].'" onblur="if (this.value == \'\') {this.value = \''.$values['yourblank1'].'\';}" onfocus="if (this.value == \''.$values['yourblank1'].'\') {this.value = \'\';}" />';
                break;
                case 'field10':
                if ($register['useblank2']) 
                    $content .= '<input id="yourblank2" name="yourblank2" '.$errors['yourblank2'].' type="text" value="'.$values['yourblank2'].'" onblur="if (this.value == \'\') {this.value = \''.$values['yourblank2'].'\';}" onfocus="if (this.value == \''.$values['yourblank2'].'\') {this.value = \'\';}" />';
                break;
                case 'field11':
                if ($register['usedropdown']) {
                    $content .= '<select '.$errors['yourdropdown'].' name="yourdropdown">';
                    $arr = explode(",",$register['yourdropdown']);
                    foreach ($arr as $item) {
                        $selected = '';
                        if ($values['yourdropdown'] == $item) $selected = 'selected';
                        $content .= '<option value="' .  $item . '" ' . $selected .'>' .  $item . '</option>';
                    }
                    $content .= '</select>';
                }
                break;
                case 'field12':
                if ($register['usenumber1']) 
                    $content .= $register['yournumber1'].'&nbsp;<input id="yournumber1" name="yournumber1" '.$errors['yournumber1'].' type="text" style="'.$errors['yournumber1'].'width:3em;margin-right:5px" value="'.$values['yournumber1'].'" value="'.$values['yournumber1'].'" onblur="if (this.value == \'\') {this.value = \''.$values['yournumber1'].'\';}" onfocus="if (this.value == \''.$values['yournumber1'].'\') {this.value = \'\';}" />';
                break;
                case 'field13';
                if ($register['useaddinfo'])
                    $content .= '<p>'.$register['addinfo'].'</p>';
                break;
                case 'field14':
                if ($register['useselector']) {
                    $content .= '<select '.$errors['yourselector'].' name="yourselector">';
                    $arr = explode(",",$register['yourselector']);
                    foreach ($arr as $item) {
                        $selected = '';
                        if ($values['yourselector'] == $item) $selected = 'selected';
                        $content .= '<option value="' .  $item . '" ' . $selected .'>' .  $item . '</option>';
                    }
                    $content .= '</select>';
                }
                break;
                }
            }
        if ($register['useterms']) {
            if ($errors['terms']) {
                $termstyle = ' style="border:1px solid red;"';
                $termslink = ' style="color:red;"';
            }
            if ($register['termstarget']) $target = ' target="_blank"';
            $content .= '<p><input type="checkbox" name="terms" value="checked" '.$termstyle.$values['terms'].' /> <a href="'.$register['termsurl'].'"'.$target.$termslink.'>'.$register['termslabel'].'</a></p>';
        }        
        if ((($payment['paypal'] && !$paypal) || $paypal=='checked') && $cost) {
            $register['qemsubmit'] = $payment['qempaypalsubmit'];
            if ($payment['usecoupon']) {
                $content .= '<input name="couponcode" type="text" value="'.$values['couponcode'].'" onblur="if (this.value == \'\') {this.value = \''.$values['couponcode'].'\';}" onfocus="if (this.value == \''.$values['couponcode'].'\') {this.value = \'\';}" />';
            }
        }
        $content .= '<input type="hidden" name="ipn" value="'.$values['ipn'].'">
<input type="submit" value="'.$register['qemsubmit'].'" id="submit" name="qemregister" />
        </form>
        <div style="clear:both;"></div></div>';
    }
    $content .= '<script type="text/javascript" language="javascript">
        document.querySelector("#qem_reload").scrollIntoView();
        </script>';
    echo $content;
}

function qem_search_array($needle, $haystack) {
     if(in_array($needle, $haystack)) {
          return true;
     }
     foreach($haystack as $element) {
          if(is_array($element) && qem_search_array($needle, $element))
               return 'error';
     }
}

function qem_verify_form(&$values, &$errors) {
    $event = get_the_ID();
    $whoscoming = get_option('qem_messages_'.$event);
    if (!$whoscoming) $whoscoming = array();
    $register = qem_get_stored_register();
    $payment = qem_get_stored_payment();
    $apikey = get_option('qem-akismet');
    if ($apikey) {
        $blogurl = get_site_url();
        $akismet = new qem_akismet($blogurl ,$apikey);
        $akismet->setCommentAuthor($values['yourname']);
        $akismet->setCommentAuthorEmail($values['youremail']);
        $akismet->setCommentContent($values['yourmessage']);
        if($akismet->isCommentSpam()) $errors['spam'] = $register['spam'];
    }
    if (!$register['usemail'] && $register['usename'] && !$register['allowmultiple']) {
        $alreadyregistered = qem_search_array($values['yourname'], $whoscoming);
    } elseif ($register['usemail'] && !$register['allowmultiple']) {
        $alreadyregistered = qem_search_array($values['youremail'], $whoscoming);
    }
    if ($alreadyregistered) {
        if ($register['checkremoval'] && $values['notattend'] && $values['youremail']) {
            $message = get_option('qem_messages_'.$event);
            for($i = 0; $i <= count($message); $i++) {
                if ($message[$i]['youremail'] == $values['youremail']) {
                    unset($message[$i]);
                    $errors['alreadyregistered'] = 'removed';
                }
            }
            $message = array_values($message);
            update_option('qem_messages_'.$event, $message );
        } else {
            $errors['alreadyregistered'] = 'checked';
        }
    } else {
        if ($register['usemail'] && !filter_var($values['youremail'], FILTER_VALIDATE_EMAIL))
        $errors['youremail'] = 'error';
        
        $values['yourname'] = filter_var($values['yourname'], FILTER_SANITIZE_STRING);
        if (($register['usename'] && $register['reqname']) && (empty($values['yourname']) || $values['yourname'] == $register['yourname']))
            $errors['yourname'] = 'error';
        $values['youremail'] = filter_var($values['youremail'], FILTER_SANITIZE_STRING);
        if (($register['usemail'] && $register['reqmail']) && (empty($values['youremail']) || $values['youremail'] == $register['youremail']))
            $errors['youremail'] = 'error';
    
        $values['yourtelephone'] = filter_var($values['yourtelephone'], FILTER_SANITIZE_STRING);
        if (($register['usetelephone'] && $register['reqtelephone']) && (empty($values['yourtelephone']) || $values['yourtelephone'] == $register['yourtelephone'])) 
            $errors['yourtelephone'] = 'error';
    
        $values['yourplaces'] = preg_replace ( '/[^0-9]/', '', $values['yourplaces']);
        if ($register['useplaces'] && empty($values['yourplaces'])) 
            $values['yourplaces'] = '1';
    
        $values['morenames'] = filter_var($values['morenames'], FILTER_SANITIZE_STRING);
        
        $values['yourmessage'] = filter_var($values['yourmessage'], FILTER_SANITIZE_STRING);
        if (($register['usemessage'] && $register['reqmessage']) && (empty($values['yourmessage']) || $values['yourmessage'] == $register['yourmessage'])) 
            $errors['yourmessage'] = 'error';
        
        $values['yourblank1'] = filter_var($values['yourblank1'], FILTER_SANITIZE_STRING);
        if (($register['useblank1'] && $register['reqblank1']) && (empty($values['yourblank1']) || $values['yourblank1'] == $register['yourblank1'])) 
            $errors['yourblank1'] = 'error';
    
        $values['yourblank2'] = filter_var($values['yourblank2'], FILTER_SANITIZE_STRING);
        if (($register['useblank2'] && $register['reqblank2']) && (empty($values['yourblank2']) || $values['yourblank2'] == $register['yourblank2'])) 
            $errors['yourblank2'] = 'error';
        
        $values['yourdropdown'] = filter_var($values['yourdropdown'], FILTER_SANITIZE_STRING);
        $values['yourselector'] = filter_var($values['yourselector'], FILTER_SANITIZE_STRING);

        $values['yournumber1'] = filter_var($values['yournumber1'], FILTER_SANITIZE_STRING);
        if (($register['usenumber1'] && $register['reqnumber1']) && (empty($values['yournumber1']) || $values['yournumber1'] == $register['yournumber1'])) 
            $errors['yournumber1'] = 'error';
        
        if ($register['useterms'] && (empty($values['terms']))) 
            $errors['terms'] = 'error';

        if ($register['usecaptcha'] && (empty($values['youranswer']) || $values['youranswer'] <> $values['answer'])) 
            $errors['youranswer'] = 'error';
        $values['youranswer'] = filter_var($values['youranswer'], FILTER_SANITIZE_STRING);
        
        if($register['useplaces'] && get_post_meta($event, 'event_counter', true)) {
            $event = get_the_ID();
            $attending = qem_get_the_numbers($event,$payment);
            $number = $attending + $values['yourplaces'];
            $places = get_post_meta($event, 'event_number', true);
            if ($places < $number && $attending) 
                $errors['yourplaces'] = 'error';
        }
    }
    return (count($errors) == 0);	
}

function qem_process_form($values) {
    global $post;
    $date = get_post_meta($post->ID, 'event_date', true);
    $enddate = get_post_meta($post->ID, 'event_end_date', true);
    $content='';
    $places = get_post_meta($post->ID, 'event_number', true);
    $rcm = get_post_meta($post->ID, 'event_registration_message', true);
	$date = date_i18n("d M Y", $date);
	$register = qem_get_stored_register();
    $auto = qem_get_stored_autoresponder();
    $event = get_the_ID();
    $qem_messages = get_option('qem_messages_'.$event);
    if(!is_array($qem_messages)) $qem_messages = array();
    $sentdate = date_i18n('d M Y');
    $newmessage = array();
    $arr = array(
        'yourname',
        'youremail',
        'notattend',
        'yourtelephone',
        'yourplaces',
        'yourblank1',
        'yourblank2',
        'yourdropdown',
        'yourselector',
        'yournumber1',
        'morenames');
    
    foreach ($arr as $item) {
        if ($values[$item] != $register[$item]) $newmessage[$item] = $values[$item];
    }
    $newmessage['sentdate'] = $sentdate;
    $newmessage['ipn'] = $values['ipn'];
    $qem_messages[] = $newmessage;
    
    update_option('qem_messages_'.$event,$qem_messages);
    if (empty($register['sendemail'])) {
        global $current_user;
        get_currentuserinfo();
        $qem_email = $current_user->user_email;
    } else {
        $qem_email = $register['sendemail'];
    }    
    
    $subject = $auto['subject'];
    if ($auto['subjecttitle']) $subject = $subject.' '.get_the_title();
    if ($autor['subjectdate']) $subject = $subject.' '.$date;
    if (empty($subject)) $subject = 'Event Registration';
    $notificationsubject = 'New Registration for '.get_the_title().' on '.$date;
    
    if ($register['usename']) $content .= '<p><b>' . $register['yourname'] . ': </b>' . strip_tags(stripslashes($values['yourname'])) . '</p>';
    if ($register['usemail']) $content .= '<p><b>' . $register['youremail'] . ': </b>' . strip_tags(stripslashes($values['youremail'])) . '</p>';
    if ($register['useattend'] && $values['notattend']) $content .= '<p><b>' . $register['yourattend'] . ': </b></p>';
    if ($register['usetelephone']) $content .= '<p><b>' . $register['yourtelephone'] . ': </b>' . strip_tags(stripslashes($values['yourtelephone'])) . '</p>';
    if ($register['useplaces']  && !$values['notattend']) $content .= '<p><b>' . $register['yourplaces'] . ': </b>' . strip_tags(stripslashes($values['yourplaces'])) . '</p>';
    elseif (!$register['useplaces']  && !$values['notattend']) $values['yourplaces'] = '1'; 
    else $values['yourplaces'] = '';
    if ($register['usemorenames']) $content .= '<p><b>' . $register['morenames'] . ': </b>' . strip_tags(stripslashes($values['morenames'])) . '</p>';
    if ($register['usemessage']) $content .= '<p><b>' . $register['yourmessage'] . ': </b>' . strip_tags(stripslashes($values['yourmessage'])) . '</p>';
    if ($register['useblank1']) $content .= '<p><b>' . $register['yourblank1'] . ': </b>' . strip_tags(stripslashes($values['yourblank1'])) . '</p>';
    if ($register['useblank2']) $content .= '<p><b>' . $register['yourblank2'] . ': </b>' . strip_tags(stripslashes($values['yourblank2'])) . '</p>';
    if ($register['usedropdown']) {
        $arr = explode(",",$register['yourdropdown']);
        $content .= '<p><b>' . $arr[0] . ': </b>' . strip_tags(stripslashes($values['yourdropdown'])) . '</p>';
    }
    if ($register['useselector']) {
        $arr = explode(",",$register['yourselector']);
        $content .= '<p><b>' . $arr[0] . ': </b>' . strip_tags(stripslashes($values['yourselector'])) . '</p>';
    }
    if ($register['usenumber1']) $content .= '<p><b>' . $register['usenumber1'] . ': </b>' . strip_tags(stripslashes($values['usenumber1'])) . '</p>';

    if ($auto['useeventdetails']) {
        if ($auto['eventdetailsblurb']) $details .= '<h2>'.$auto['eventdetailsblurb'].'</h2>';
        $details .= '<p>'.get_the_title().'</p><p>'.$date;
        if ($enddate) {
            $enddate = date_i18n("d M Y", $enddate);
            $details .= ' - '.$enddate;
        }
        $details .= '</p>';
        $event = event_get_stored_options();
        $display = event_get_stored_display();
        $custom = get_post_custom();
        foreach (explode( ',',$event['sort']) as $name) {
            if ($event['active_buttons'][$name]) {
                $details .= qem_build_event($name,$event,$display,$custom,'');
            }
        }
    }

    $headers = "From: {$values['yourname']} <{$values['youremail']}>\r\n"
    . "MIME-Version: 1.0\r\n"
    . "Content-Type: text/html; charset=\"utf-8\"\r\n";	
    $message = '<html>'.$content.'</html>';
    wp_mail($qem_email, $notificationsubject, $message, $headers);
    
    if ($auto['enable'] || $values['qem-copy']) {
        
        $msg = ($rcm ? $rcm : $auto['message']);
        
        $copy .= '<html>' . $msg;
         if ($auto['useregistrationdetails']) {
             if($auto['registrationdetailsblurb']) {
                 $copy .= '<h2>'.$auto['registrationdetailsblurb'].'</h2>';
             }
             $copy .= $content;
         }
        if ($auto['permalink']) $close .= '<p><a href="' . get_permalink() . '">' . get_permalink() . '</a></p>';
        $message = $copy.$details.$close.'</html>';
        
        $headers = "From: ".$auto['fromname']." <{$auto['fromemail']}>\r\n"
		. "MIME-Version: 1.0\r\n"
		. "Content-Type: text/html; charset=\"utf-8\"\r\n";	
        wp_mail($values['youremail'], $subject, $message, $headers);
    }
    
    if (($payment['paypal'] && !get_post_meta($post->ID, 'event_paypal',true)) || get_post_meta($post->ID, 'event_paypal',true) =='checked') {
        return 'checked';
    }
    $redirect = get_post_meta($post->ID, 'event_redirect', true);
    $redirect_id = get_post_meta($post->ID, 'event_redirect_id', true);
    if ($redirect) {
        if ($redirect_id) {
            if (substr($redirect, -1) != '/') $redirect = $redirect.'/';
            $id = get_the_ID();
            $redirect = $redirect."?event=".$id;
        }
        echo "<meta http-equiv='refresh' content='0;url=$redirect' />";
        exit();
    }
}

function qem_registration_report($atts) {
    extract(shortcode_atts(array('event'=>''),$atts));
    $message = get_option('qem_messages_'.$event);
    $check = get_post_meta($event, 'event_counter', true);
    $register = qem_get_stored_register();
    ob_start();
    $content ='<div id="qem-widget">
    <h2><a href="'.get_permalink($event).'">'.get_the_title($event).'</a></h2>';
    $content .= qem_build_registration_table ($register,$message,$check,'report',$event);
    $content .='</div>';
    echo $content;
    $output_string=ob_get_contents();
    ob_end_clean();
    return $output_string;
}

function qem_build_registration_table ($register,$message,$check,$report,$event) {
    $payment = qem_get_stored_payment();
    $charles=$content='';$delete=array();$i=0;
    $dashboard = '<table cellspacing="0">
    <tr>';
    if ($register['usename']) $dashboard .= '<th>'.$register['yourname'].'</th>';
    if ($register['usemail']) $dashboard .= '<th>'.$register['youremail'].'</th>';
    if ($register['useattend']) $dashboard .= '<th>'.$register['yourattend'].'</th>';
    if ($register['usetelephone']) $dashboard .= '<th>'.$register['yourtelephone'].'</th>';
    if ($register['useplaces']) $dashboard .= '<th>'.$register['yourplaces'].'</th>';
    if ($register['usemorenames']) $dashboard .= '<th>'.$register['morenames'].'</th>';
    if ($register['usemessage']) $dashboard .= '<th>'.$register['yourmessage'].'</th>';
    if ($register['useblank1']) $dashboard .= '<th>'.$register['yourblank1'].'</th>';
    if ($register['useblank2']) $dashboard .= '<th>'.$register['yourblank2'].'</th>';
    if ($register['usedropdown']) {
        $arr = explode(",",$register['yourdropdown']);
        $dashboard .= '<th>'.$arr[0].'</th>';
    }
    if ($register['useselector']) {
        $arr = explode(",",$register['yourselector']);
        $dashboard .= '<th>'.$arr[0].'</th>';
    }
    if ($register['usenumber1']) $dashboard .= '<th>'.$register['yournumber1'].'</th>';
    $dashboard .= '<th>Date Sent</th>';
    if ($payment['ipn']) $dashboard .= '<th>'.$payment['title'].'</th>';
    if (!$report) $dashboard .= '<th>Delete</th>';
    $dashboard .= '</tr>';
	
    foreach($message as $value) {
        $content .= '<tr>';
        if ($register['usename']) $content .= '<td>'.$value['yourname'].'</td>';
        if ($register['usemail']) $content .= '<td>'.$value['youremail'].'</td>';
        if ($register['useattend']) $content .= '<td>'.$value['notattend'].'</td>';
        if ($register['usetelephone']) $content .= '<td>'.$value['yourtelephone'].'</td>';
        if ($register['useplaces'] && empty($value['notattend'])) $content .= '<td>'.$value['yourplaces'].'</td>';
        else $content .= '<td></td>';
        if ($register['usemorenames']) $content .= '<td>'.$value['morenames'].'</td>';
        if ($register['usemessage']) $content .= '<td>'.$value['yourmessage'].'</td>';
        if ($register['useblank1']) $content .= '<td>'.$value['yourblank1'].'</td>';
        if ($register['useblank2']) $content .= '<td>'.$value['yourblank2'].'</td>';
        if ($register['usedropdown']) $content .= '<td>'.$value['yourdropdown'].'</td>';
        if ($register['useselector']) $content .= '<td>'.$value['yourselector'].'</td>';
        if ($register['usenumber1']) $content .= '<td>'.$value['yournumber1'].'</td>';
        if ($value['yourname']) $charles = 'messages';
        $content .= '<td>'.$value['sentdate'].'</td>';
        if ($payment['ipn']) {
            $ipn = ($payment['sandbox'] ? $value['ipn'] : '');
            $content .= ($value['ipn'] == "Paid" ? '<td>'.$payment['paid'].'</td>' : '<td>'.$ipn.'</td>');
        }
        if (!$report)  $content .= '<td><input type="checkbox" name="'.$i.'" value="checked" /></td>';
        $content .= '</tr>';
        $i++;
    }	
    $dashboard .= $content.'</table>';
    if ($check) $dashboard .= qem_numberscoming($register,$event,$payment);
    if ($charles) return $dashboard;
}

function qem_whoscoming($register,$payment) {
    $event = get_the_ID();
    $str='';
    $whoscoming = get_option('qem_messages_'.$event);
    if ($register['whoscoming'] && $whoscoming) {
        $content = '<p id="whoscoming"><b>'.$register['whoscomingmessage'].'</b>';
        foreach($whoscoming as $item)
            if (empty($item['notattend']) && (!qem_check_ipnblock($payment,$item))) {
            $str = $str.$item['yourname'].', ';
        }
        $content .= substr($str, 0, -2);
        $content .= '</p>';
        if ($register['whosavatar']) {
            $content .= '<p>';
            foreach($whoscoming as $item)
                if (!$item['notattend']) $content .= '<img title="'.$item['yourname'].'" src="http://www.gravatar.com/avatar/' . md5($item['youremail']) . '?s=40&&d=identicon" />&nbsp;';
                $content .= '</p>';
        }
        return $content;
    }
}

function qem_check_ipnblock($payment,$item) {
    if	($payment['paypal'] && $payment['ipn'] && $payment['ipnblock'] && $item['ipn'] && $item['ipn'] != 'Paid') {
        return 'checked';
    }
}

function qem_get_the_numbers($event,$payment) {
    $str='';
    $whoscoming = get_option('qem_messages_'.$event);
    if ($whoscoming)
        foreach($whoscoming as $item) {
        if (!qem_check_ipnblock($payment,$item)) {
            $str = $str + $item['yourplaces'];
        }
    }
    if ($str) return $str;
}

function qem_totalcoming($register,$payment) {
    $event = get_the_ID();
    $str = qem_get_the_numbers ($event,$payment);
    if ($register['numberattending'] && $str) {
        $content = '<p id="whoscoming">'.$register['numberattendingbefore'].' '.$str.' '.$register['numberattendingafter'].'</p>';
        return $content;
    }
}

function qem_numberscoming($register,$event,$payment) {
    $number = get_post_meta($event, 'event_number', true);
    $attending = qem_get_the_numbers ($event,$payment);
    $places = $number - $attending;
    if ($places > 0) {
        return '<p id="whoscoming">'.$register['placesbefore'].' '.$places.' '.$register['placesafter'].'<p>';
    }
}

function qem_qpp_places () {
    global $post;
    $payment = qem_get_stored_payment();
    if ($payment['qppcounter']) {
        $event = get_the_ID();
        $values = array('yourplaces' => 1);
        qem_place_number ($event,$values);
    }
}

function qem_place_number ($event,$values,$payment) {
    $attending = qem_get_the_numbers($event,$payment);
    $number = get_post_meta($event, 'event_number', true);
    if (!$number) return;
    if (!is_numeric($values['yourplaces'])) $values['yourplaces'] = 1;
    $attending = $eventnumber - $values['yourplaces'];
    if ($eventnumber < 1) $eventnumber = 'full';
    update_option( $event.'places', $eventnumber );
}


function qem_messages(){
    $event=$title='';
    global $_GET;
    $event = (isset($_GET["event"]) ? $_GET["event"] : null);
    $title = (isset($_GET["title"]) ? $_GET["title"] : null);
    $unixtime = get_post_meta($event, 'event_date', true);
    $date = date_i18n("d M Y", $unixtime);
    $noregistration = '<p>No event selected</p>';
    $register = qem_get_stored_register();
    $category = 'All Categories';
    if( isset( $_POST['qem_reset_message'])) {
        $event= $_POST['qem_download_form'];
        $title = get_the_title($event);
        delete_option('qem_messages_'.$event);
        delete_option($event);
        qem_admin_notice('Registrants for '.$title.' have been deleted.');
        $eventnumber = get_post_meta($event, 'event_number', true);
        update_option($event.'places',$eventnumber);
    }
    
    if( isset( $_POST['category']) ) {
        $category = $_POST["category"];
    }
    
    if( isset( $_POST['select_event'])  || isset( $_POST['eventid'])) {
        $event = $_POST["eventid"];
        if ($event) {
            $unixtime = get_post_meta($event, 'event_date', true);
            $date = date_i18n("d M Y", $unixtime);
            $title = get_the_title($event);
            $noregistration = '<h2>'.$title.' | '.$date.'</h2><p>Nobody has registered for '.$title.' yet</p>';
        } else {
            $noregistration = '<p>No event selected</p>';
        }
    }
    
    if( isset( $_POST['changeoptions'])) {
        $options = array( 'showevents','category');
        foreach ( $options as $item) $messageoptions[$item] = stripslashes($_POST[$item]);
        $category = $messageoptions['category'];
        update_option( 'qem_messageoptions', $messageoptions );
    }
    
    if( isset($_POST['qem_delete_selected'])) {
        $event = $_POST["qem_download_form"];
        $message = get_option('qem_messages_'.$event);
        $eventnumber = get_option($event.'places');
        $check = get_post_meta($event, 'event_counter', true);
        for($i = 0; $i <= 100; $i++) {
            if ($_POST[$i] == 'checked') {
                $num = ($message[$i]['yourplaces'] ? $message[$i]['yourplaces'] : 1);
                if ($check) $eventnumber = $eventnumber + $num;
                unset($message[$i]);
            }
        }
        $message = array_values($message);
        update_option('qem_messages_'.$event, $message ); 
        if ($check) update_option($event.'places',$eventnumber);
        qem_admin_notice('Selected registrations have been deleted.');
    }

if( isset($_POST['qem_emaillist'])) {
    $event = $_POST["qem_download_form"];
    $title = $_POST["qem_download_title"];
    $message = get_option('qem_messages_'.$event);
    $register = qem_get_stored_register();
    $content='';
    $content = qem_build_registration_table ($register,$message,'','','','');
    global $current_user;
    get_currentuserinfo();
    $qem_email = $current_user->user_email;
    $headers = "From: {<{$qem_email}>\r\n"
. "MIME-Version: 1.0\r\n"
. "Content-Type: text/html; charset=\"utf-8\"\r\n";	
wp_mail($qem_email, $title, $content, $headers);
    qem_admin_notice('Registration list has been sent to '.$qem_email.'.');
}

qem_generate_csv();

$content=$current=$all='';
$messageoptions = qem_get_stored_msg();
$$messageoptions['showevents'] = "checked";
$message = get_option('qem_messages_'.$event);
$places = get_option($event.'places');
$check = get_post_meta($event, 'event_counter', true);
if(!is_array($message)) $message = array();
$dashboard = '<div class="wrap">
<h1>Event Registation Report</h1>
<p><form method="post" action="">'.
qem_message_categories($category).'
&nbsp;&nbsp;'.
qem_get_eventlist ($event,$register,$messageoptions,$category).'
&nbsp;&nbsp;<b>Show:</b> <input style="margin:0; padding:0; border:none;" type="radio" name="showevents" value="all" ' . $all . ' /> All Events <input style="margin:0; padding:0; border:none;" type="radio" name="showevents" value="current" ' . $current . ' /> Current Events&nbsp;&nbsp;<input type="submit" name="changeoptions" class="button-secondary" value="Update options" />
</form>
</p>
<div id="qem-widget">
<form method="post" id="qem_download_form" action="">';
$content = qem_build_registration_table ($register,$message,$check,'',$event);
if ($content) {
    $dashboard .= '<h2>'.$title.' | '.$date.'</h2>';
    $dashboard .= '<p>Event ID: '.$event.'</p>';
    $dashboard .= $content;
    $dashboard .='<input type="hidden" name="qem_download_form" value = "'.$event.'" />
    <input type="hidden" name="qem_download_title" value = "'.$title.'" />
    <input type="submit" name="qem_download_csv" class="button-primary" value="Export to CSV" />
    <input type="submit" name="qem_emaillist" class="button-primary" value="Email List" />
    <input type="submit" name="qem_reset_message" class="button-secondary" value="Delete All Registrants" onclick="return window.confirm( \'Are you sure you want to delete all the registrants for '.$title.'?\' );"/>
    <input type="submit" name="qem_delete_selected" class="button-secondary" value="Delete Selected" onclick="return window.confirm( \'Are you sure you want to delete the selected registrants?\' );"/>
    </form>';
}
else $dashboard .= $noregistration;
$dashboard .= '</div></div>';		
echo $dashboard;
}
function qem_get_eventlist ($event,$register,$messageoptions,$thecat) {

    global $post;
    $arr = get_categories();
    $content=$slug='';
    foreach($arr as $option) if ($thecat == $option->slug) $slug = $option->slug;
    $content .= '<select name="eventid" onchange="this.form.submit()"><option value="">Select an Event</option>'."\r\t";
    $args = array('post_type'=> 'event','orderby'=>'title','order'=>'ASC','posts_per_page'=> -1,'category_name'=>$slug);
    $today = strtotime(date('Y-m-d'));
    query_posts( $args );
    if ( have_posts()){
        while (have_posts()) {
            the_post();
            $title = get_the_title();
            $id = get_the_id();
            $unixtime = get_post_meta($post->ID, 'event_date', true);
            $date = date_i18n("d M Y", $unixtime);
            if ($register['useform'] || get_event_field("event_register") && ($messageoptions['showevents'] == 'all' || $unixtime >= $today) ) 
                $content .= '<option value="'.$id.'">'.$title.' | '.$date.'</option>';
        }
        $content .= '</select>
        <noscript><input type="submit" name="select_event" class="button-primary" value="Select Event" /></noscript>';
    }
    return $content;
}

function qem_message_categories ($thecat) {
    $arr = get_categories();
    $content = '<select name="category" onchange="this.form.submit()">
<option value="">All Categories</option>';
    foreach($arr as $option) {
        if ($thecat == $option->slug) $selected = 'selected'; else $selected = '';
        $content .= '<option value="'.$option->slug.'" '.$selected.'>'.$option->name.'</option>';
    }
    $content .= '</select>';
    return $content;
}

function qem_get_stored_msg () {
    $messageoptions = get_option('qem_messageoptions');
    if(!is_array($messageoptions)) $messageoptions = array();
    $default = qem_get_default_msg();
    $messageoptions = array_merge($default, $messageoptions);
    return $messageoptions;
}

function qem_get_default_msg () {
    $messageoptions = array();
    $messageoptions['showevents'] = 'current';
    $messageoptions['messageorder'] = 'newest';
    return $messageoptions;
}
