<?php

function qem_loop() {
    ob_start();
    if (isset($_POST['qemregister'])) {
        $formvalues = $_POST;
        $formerrors = array();
        if (!qem_verify_form($formvalues, $formerrors)) {
            qem_display_form($formvalues, $formerrors);
        } else {
            $formvalues['completed'] = qem_process_form($formvalues);
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

function qem_whoscoming($register) {
    $event = get_the_ID();
    $str='';
    $whoscoming = get_option('qem_messages_'.$event);
    if ($register['whoscoming'] && $whoscoming) {
        $content = '<p id="whoscoming"><b>'.$register['whoscomingmessage'].'</b>';
        foreach($whoscoming as $item) if (empty($item['notattend'])) 
            $str = $str.$item['yourname'].', ';
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

function qem_get_the_numbers($event) {
    $str='';
    $whoscoming = get_option('qem_messages_'.$event);
    if ($whoscoming)
        foreach($whoscoming as $item) 
        $str = $str + $item['yourplaces'];
    if ($str) return $str;
}

function qem_totalcoming($register) {
    $event = get_the_ID();
    $str = qem_get_the_numbers ($event);
    if ($register['numberattending'] && $str) {
        $content = '<p id="whoscoming">'.$register['numberattendingbefore'].' '.$str.' '.$register['numberattendingafter'].'</p>';
        return $content;
    }
}

function qem_numberscoming($register,$event) {
    $number = get_post_meta($event, 'event_number', true);
    $attending = qem_get_the_numbers ($event);
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

function qem_place_number ($event,$values) {
    $attending = qem_get_the_numbers($event);
    $number = get_post_meta($event, 'event_number', true);
    if (!is_numeric($values['yourplaces'])) $values['yourplaces'] = 1;
    $attending = $eventnumber - $values['yourplaces'];
    if ($eventnumber < 1) $eventnumber = 'full';
    update_option( $event.'places', $eventnumber );
}

function qem_display_form( $values, $errors ) {
    $register = qem_get_stored_register();
    $payment = qem_get_stored_payment();
    global $post;
    $event=get_the_ID();
    $check = get_post_meta($post->ID, 'event_counter', true);
    $cost = get_post_meta($post->ID, 'event_cost', true);
    $paypal = get_post_meta($post->ID, 'event_paypal', true);
    if ($check) $num = qem_numberscoming($register,$event);
    $content = qem_totalcoming($register);
    $content .= qem_whoscoming($register);
    
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
    } elseif (!$num && $check) {
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
                    $content .= '<input id="yourplaces" name="yourplaces" type="text" style="'.$errors['yourplaces'].'width:3em;margin-right:5px" value="'.$values['yourplaces'].'" onblur="if (this.value == \'\') {this.value = \''.$values['yourplaces'].'\';}" onfocus="if (this.value == \''.$values['yourplaces'].'\') {this.value = \'\';}" />'.$register['yourplaces'].'<br>';
                else 
                    $content .= '<input type="hidden" name="yourplaces" value="1">';
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
                }
            }
        if ((($payment['paypal'] && !$paypal) || $paypal=='checked') && $cost) {
            $register['qemsubmit'] = $payment['qempaypalsubmit'];
            if ($payment['usecoupon']) {
                $content .= '<input name="couponcode" type="text" value="'.$values['couponcode'].'" onblur="if (this.value == \'\') {this.value = \''.$values['couponcode'].'\';}" onfocus="if (this.value == \''.$values['couponcode'].'\') {this.value = \'\';}" />';
            }
        }
        $content .= '<input type="submit" value="'.$register['qemsubmit'].'" id="submit" name="qemregister" />
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
        if (($register['usedropdown'] && $register['reqdropdown'])) 
            $errors['yourdropdown'] = 'error';
        
        $values['yournumber1'] = filter_var($values['yournumber1'], FILTER_SANITIZE_STRING);
        if (($register['usenumber1'] && $register['reqnumber1']) && (empty($values['yournumber1']) || $values['yournumber1'] == $register['yournumber1'])) 
            $errors['yournumber1'] = 'error';
        
        if ($register['usecaptcha'] && (empty($values['youranswer']) || $values['youranswer'] <> $values['answer'])) 
            $errors['youranswer'] = 'error';
        $values['youranswer'] = filter_var($values['youranswer'], FILTER_SANITIZE_STRING);
        
        if($register['useplaces'] && get_post_meta($event, 'event_counter', true)) {
            $event = get_the_ID();
            $attending = qem_get_the_numbers($event);
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
	$date = date_i18n("d M Y", $date);
	$register = qem_get_stored_register();
    $event = get_the_ID();
    $qem_messages = get_option('qem_messages_'.$event);
    if(!is_array($qem_messages)) $qem_messages = array();
    $sentdate = date_i18n('d M Y');
    $custom = md5(mt_rand());
    $qem_messages[] = array(
        'sentdate'=>$sentdate,
        'yourname' => $values['yourname'] ,
        'youremail' => $values['youremail'] ,
        'notattend' => $values['notattend'] ,
        'yourtelephone' => $values['yourtelephone'],
        'yourplaces' => $values['yourplaces'],
        'yourblank1' => $values['yourblank1'],
        'yourblank2' => $values['yourblank2'],
        'yourdropdown' => $values['yourdropdown'],
        'yournumber1' => $values['yournumber1'],
        'ipn' => $custom
    );
    update_option('qem_messages_'.$event,$qem_messages);
    if (empty($register['sendemail'])) {
        global $current_user;
        get_currentuserinfo();
        $qem_email = $current_user->user_email;
    } else {
        $qem_email = $register['sendemail'];
    }    
    
    $subject = $register['subject'];
    if ($register['subjecttitle']) $subject = $subject.' '.get_the_title();
    if ($register['subjectdate']) $subject = $subject.' '.$date;
    if (empty($subject)) $subject = 'Event Register';
    
    if ($register['usename']) $content .= '<p><b>' . $register['yourname'] . ': </b>' . strip_tags(stripslashes($values['yourname'])) . '</p>';
    if ($register['usemail']) $content .= '<p><b>' . $register['youremail'] . ': </b>' . strip_tags(stripslashes($values['youremail'])) . '</p>';
    if ($register['useattend'] && $values['notattend']) $content .= '<p><b>' . $register['yourattend'] . ': </b></p>';
    if ($register['usetelephone']) $content .= '<p><b>' . $register['yourtelephone'] . ': </b>' . strip_tags(stripslashes($values['yourtelephone'])) . '</p>';
    if ($register['useplaces']  && !$values['notattend']) $content .= '<p><b>' . $register['yourplaces'] . ': </b>' . strip_tags(stripslashes($values['yourplaces'])) . '</p>';
    elseif (!$register['useplaces']  && !$values['notattend']) $values['yourplaces'] = '1'; 
    else $values['yourplaces'] = '';                                               
    if ($register['usemessage']) $content .= '<p><b>' . $register['yourmessage'] . ': </b>' . strip_tags(stripslashes($values['yourmessage'])) . '</p>';
    if ($register['useblank1']) $content .= '<p><b>' . $register['yourblank1'] . ': </b>' . strip_tags(stripslashes($values['yourblank1'])) . '</p>';
    if ($register['useblank2']) $content .= '<p><b>' . $register['yourblank2'] . ': </b>' . strip_tags(stripslashes($values['yourblank2'])) . '</p>';
    if ($register['usedropdown']) $content .= '<p><b>' . $register['yourdropdown'] . ': </b>' . strip_tags(stripslashes($values['yourdropdown'])) . '</p>';
    if ($register['usenumber1']) $content .= '<p><b>' . $register['usenumber1'] . ': </b>' . strip_tags(stripslashes($values['usenumber1'])) . '</p>';

    if ($register['useeventdetails']) {
        if ($register['eventdetailsblurb']) $details .= '<h2>'.$register['eventdetailsblurb'].'</h2>';
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
    wp_mail($qem_email, $subject, $message, $headers);
    
    if ($register['emailmessage']) $register['replyblurb'] = $register['emailmessage'];
    if ($register['sendcopy'] || $values['qem-copy']) {
        $copy .= '<html><p>' . $register['replytitle'] . '</p><p>' . $register['replyblurb'] . '</p>';
         if ($register['useregistrationdetails']) {
             if($register['registrationdetailsblurb']) {
                 $copy .= '<h2>'.$register['registrationdetailsblurb'].'</h2>';
             }
             $copy .= $content;
         }
        if ($register['permalink']) $close .= '<p><a href="' . get_permalink() . '">' . get_permalink() . '</a></p>';
        $message = $copy.$details.$close.'</html>';
        $headers = "From: ".get_option('blogname')." <{$qem_email}>\r\n"
		. "MIME-Version: 1.0\r\n"
		. "Content-Type: text/html; charset=\"utf-8\"\r\n";	
                wp_mail($values['youremail'], $subject, $message, $headers);
    }
    
    if (($payment['paypal'] && !get_post_meta($post->ID, 'event_paypal',true)) || get_post_meta($post->ID, 'event_paypal',true) =='checked') {
        return 'checked';
    }
    $redirect = get_post_meta($post->ID, 'event_redirect', true);
    $redirect_id = get_post_meta($post->ID, 'event_redirect_id', true);
    if (!$redirect && $register['redirectionurl'])
        $redirect = $register['redirectionurl'];
    if ($redirect) {
        if ($redirect_id) {
            if (substr($redirect, -1) != '/') $redirect = $redirect.'/';
            $id = get_the_ID();
            $redirect = $redirect."?event=".$id;
        }
        echo "<meta http-equiv='refresh' content='0;url=$redirect' />";
        exit();
    }
        return 'checked';
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
    if ($register['usemessage']) $dashboard .= '<th>'.$register['yourmessage'].'</th>';
    if ($register['useblank1']) $dashboard .= '<th>'.$register['yourblank1'].'</th>';
    if ($register['useblank2']) $dashboard .= '<th>'.$register['yourblank2'].'</th>';
    if ($register['usedropdown']) $dashboard .= '<th>'.$register['yourdropdown'].'</th>';
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
        if ($register['usemessage']) $content .= '<td>'.$value['yourmessage'].'</td>';
        if ($register['useblank1']) $content .= '<td>'.$value['yourblank1'].'</td>';
        if ($register['useblank2']) $content .= '<td>'.$value['yourblank2'].'</td>';
        if ($register['usedropdown']) $content .= '<td>'.$value['yourdropdown'].'</td>';
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
    if ($check) $dashboard .= qem_numberscoming($register,$event);
    if ($charles) return $dashboard;
}