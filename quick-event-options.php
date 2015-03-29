<?php

function event_get_stored_options () {	
    $event = get_option('event_settings');
    if(!is_array($event)) $event = array();
    $option_default = event_get_default_options();
    $event = array_merge($option_default, $event);
    if (!strpos($event['sort'],'7')) {
        $event['sort'] = $event['sort'].',field7';$event['label']['field7'] = 'Organiser';
        update_option('event_settings',$event);
        qem_create_css_file ('update');
    }
    return $event;
}

function event_get_default_options () {
    $event = array();
    $event['active_buttons'] = array('field1'=>'on','field2'=>'on','field3'=>'on','field4'=>'on','field5'=>'on','field6'=>'on','field7'=>'on');
    $event['summary'] = array(
        'field1'=>'checked',
        'field2'=>'checked',
        'field3'=>'checked'
    );
    $event['label'] = array(
        'field1'=> __('Short Description', 'quick-event-manager'),
        'field2' => __('Event Time', 'quick-event-manager'),
        'field3' => __('Venue', 'quick-event-manager'),
        'field4' => __('Address', 'quick-event-manager'),
        'field5' => __('Event Website', 'quick-event-manager'),
        'field6' => __('Cost', 'quick-event-manager'),
        'field7' => __('Organiser', 'quick-event-manager')
    );
    $event['sort'] = 'field1,field2,field3,field4,field5,field6,field7';
    $event['bold'] = array('field2'=>'checked');
    $event['italic'] = array('field4'=>'checked',);
    $event['colour'] = array('field2'=>'#343838','field6'=>'#008C9E');
    $event['size'] = array('field1'=>'110','field2'=>'120','field6'=>'120');
    $event['address_label'] = '';
    $event['url_label'] = '';
    $event['description_label'] = '';
    $event['cost_label'] = '';
    $event['start_label'] = 'From';
    $event['finish_label'] = 'until';
    $event['location_label'] = 'At';
    $event['show_map'] = 'checked';
    $event['address_style'] = 'italic';
    $event['website_link'] = 'checked';
    $event['show_telephone'] = 'checked';
    $version = get_option('qem_version');
    return $event;
}

function event_get_stored_display () {
    $display = get_option('qem_display');
    if(!is_array($display)) $display = array();
    $default = qem_get_default_display();
    $display = array_merge($default, $display);
    return $display;
}

function qem_get_default_display () {
    $display = array();
    $display['read_more'] = 'Find out more...';
    $display['noevent'] = 'No event found';
    $display['event_image'] = '';
    $display['monthheading'] = '';
    $display['back_to_list_caption'] = 'Return to Event list';
    $display['image_width'] = '300';
    $display['event_image_width'] = '300';
    $display['event_order'] = 'newest';
    $display['event_archive'] = '';
    $display['map_width'] = '200';
    $display['map_height'] = '200';
    $display['useics'] = '';
    $display['useicsbutton'] = 'Download Event to Calendar';
    $display['usetimezone'] ='';
    $display['timezonebefore'] = 'Timezone:';
    $display['timezoneafter'] = 'time';
    $display['map_and_image'] = 'checked';
$display['localization'] = '';
    return $display;
}

function qem_get_stored_style() {
    $style = get_option('qem_style');
    if (!$style['keycaption']) {
        $cal = get_option('qem_calendar');
        $style['keycaption'] = $cal['keycaption'];
        $style['showkeyabove'] = $cal['showkeyabove'];
        $style['showkeybelow'] = $cal['showkeybelow'];
    }
    if(!is_array($style)) $style = array();
    $default = qem_get_default_style();
    $style = array_merge($default, $style);
    return $style;
}

function qem_get_default_style() {
    $style['font'] = 'theme';
    $style['font-family'] = 'arial, sans-serif';
    $style['font-size'] = '1em';
    $style['header-size'] = '100%';
    $style['width'] = 600;
    $style['widthtype'] = 'percent';
    $style['event_border'] = '';
    $style['event_background'] = 'bgtheme';
    $style['event_backgroundhex'] = '#FFF';
    $style['date_colour'] = '#FFF';
    $style['month_colour'] = '#343838';
    $style['date_background'] = 'grey';
    $style['date_backgroundhex'] = '#FFF';
    $style['month_background'] = 'white';
    $style['month_backgroundhex'] = '#FFF';
    $style['date_border_width'] = '2';
    $style['date_border_colour'] = '#343838';
    $style['date_bold'] = '';
    $style['date_italic'] = 'checked';
    $style['calender_size'] = 'medium';
    $style['icon_corners'] = 'rounded';
    $style['styles'] = '';
    $style['uselabels'] = '';
    $style['startlabel'] = 'Starts';
    $style['finishlabel'] = 'Ends';
    $style['event_margin'] = 'margin: 0 0 20px 0;';
    $style['line_margin'] = 'margin: 0 0 8px 0;padding: 0 0 0 0';
    $style['custom'] = ".qem {\r\n}\r\n.qem h2{\r\n}";
    $style['combined'] = 'checked';
    $style['iconorder'] = 'default';
    $style['vanillaw'] ='';
    $style['vanillawidget'] ='';
    $style['use_head'] ='';
    $style['linktocategory'] = 'checked';
    $style['showuncategorised'] ='';
    $style['keycaption'] = 'Event Categories:';
    $style['showkeyabove'] = '';
    $style['showkeybelow'] = '';
    $style['showcategory'] = '';
    $style['showcategorycaption'] = 'Current Category:';
    $style['dayborder'] = 'checked';
    return $style;
}

function qem_get_stored_calendar() {
    $calendar = get_option('qem_calendar');
    if(!is_array($calendar)) $calendar = array();
    $default = qem_get_default_calendar();
    $calendar = array_merge($default, $calendar);
    return $calendar;
}

function qem_get_default_calendar() {
    $calendar = array(
        'day' => '#EBEFC9',
        'calday' => '#EBEFC9',
        'eventday' => '#EED1AC',
        'oldday' => '#CCC',
        'eventhover' => '#F2F2E6',
        'eventdaytext' => '#343838',
        'eventbackground' => '#FFF',
        'eventtext' => '#343838',
        'eventlink' => 'linkpopup',
        'calendar_text' => 'View as calendar',
        'calendar_url' => '',
        'eventlist_text' => 'View as a list of events',
        'eventlist_url' => '',
        'eventlength' => '20',
        'connect' => '',
        'startday' => 'sunday',
        'archive' => 'checked',
        'archivelinks' => 'checked',
        'prevmonth' => 'Prev',
        'nextmonth' => 'Next',
        'smallicon' => 'arrow',
        'unicode' => '\263A',
        'eventtext' => '#343838',
        'eventbackground' => '#FFF',
        'eventhover' => '#EED1AC',
        'eventborder' => '1px solid #343838',
        'keycaption' => 'Event Key:',
        'navicon' => 'arrows',
        'linktocategory' => 'checked',
        'showuncategorised' => '',
        'tdborder' => '',
        'cellspacing' => 3
    );
    return $calendar;
}

function qem_get_stored_register () {
    $register = get_option('qem_register');
    if(!is_array($register)) $register = array();
    $default = qem_get_default_register();
    $register = array_merge($default, $register);
    $update='';
    if (!strpos($register['sort'],'9')) {$register['sort'] = $register['sort'].',field9';$update='checked';}
    if (!strpos($register['sort'],'10')) {$register['sort'] = $register['sort'].',field10';$update='checked';}
    if (!strpos($register['sort'],'11')) {$register['sort'] = $register['sort'].',fiel11';$update='checked';}
    if (!strpos($register['sort'],'12')) {$register['sort'] = $register['sort'].',fiel12';$update='checked';}
    if ($update) update_option('qem_register',$register);
    return $register;
}

function qem_get_default_register () {
    $register = array(
        'sort' => 'field1,field2,field3,field4,field5,field6,field7,field8,field9,field10,field11,field12',
        'usename' => 'checked',
        'usemail' => 'checked',
        'useblank1' => '',
        'useblank2' => '',
        'usedropdown' => '',
        'usenumber1' => '',
        'reqname' => 'checked',
        'reqmail' => 'checked',
        'reqblank1' => '',
        'reqblank2' => '',
        'reqdropdown' => '',
        'reqnumber1' => '',
        'formborder' => '',
        'title' => 'Register for this event',
        'blurb' => 'Enter your details below',
        'replytitle' => 'Thank you for registering',
        'replyblurb' => 'We will be in contact soon',
        'yourname' => 'Your Name',
        'youremail' => 'Email Address',
        'yourtelephone' => 'Telephone Number',
        'yourplaces' => 'Number of places required',
        'yourmessage' => 'Message',
        'yourattend' => 'I will not be attending this event',
        'yourblank1' => 'More Information',
        'yourblank2' => 'More Information',
        'yourdropdown' => 'Separate,With.Commas',
        'yournumber1' => 'Number',
        'notattend' => '',
        'error' => 'Please complete the form',
        'subject' => 'Registration for:',
        'qemsubmit' => 'Register',
        'subjecttitle' => 'checked',
        'subjectdate' => 'checked',
        'whoscoming' => '',
        'whoscomingmessage' => 'Look who\'s coming: ',
        'placesbefore' => 'There are',
        'placesafter' => 'places available.',
        'numberattendingbefore' => 'There are',
        'numberattendingafter' => 'people coming.',
        'eventfull' => '',
        'eventfullmessage' => 'Registration is closed',
        'read_more' => 'Return to the event',
        'useread_more' => '',
        'sendcopy' => '',
        'usecopy' => '',
        'completed' => '',
        'copyblurb' => 'Send registration details to your email address',
        'alreadyregistered' => 'You are already registered for this event',
        'nameremoved' => 'You have been removed from the list',
        'checkremoval' => '',
        'spam' => 'Your Details have been flagged as spam',
        'thanksurl' => '',
        'cancelurl' => '',
        'paypal' => '',
        'allowmultiple' => ''
    );
    return $register;
}

function qem_get_stored_payment () {
    $payment = get_option('qem_payment');
    if(!is_array($payment)) $payment = array();
    $register = get_option('qem_register');
    if ($register['paypalemail'] && !$payment['paypalemail']) {
        $payment['currency'] = ($register['currency'] ? $register['currency'] : 'USD');
        $payment['paypalemail'] = $register['paypalemail'];
        $payment['useprocess'] = ($register['useprocess'] ? $register['useprocess'] : '');
        $payment['processtype'] = ($register['processtype'] ? $register['processtype'] : 'processfixed');
        $payment['processpercent'] = ($register['processpercent'] ? $register['processpercent'] : '5');
        $payment['processfixed'] = ($register['processfixed'] ? $register['processfixed'] : '2');
        $payment['qempaypalsubmit'] = ($register['qempaypalsubmit'] ? $register['qempaypalsubmit'] : 'Register and Pay');
        update_option('qem_payment',$payment);
    }
    $default = qem_get_default_payment();
    $payment = array_merge($default, $payment);
    return $payment;
}

function qem_get_default_payment () {
    $payment = array(
        'useqpp' => '',
        'qppform' => '',
        'qppcost' => 'checked',
        'currency' => 'USD',
        'paypalemail' => '',
        'useprocess' => '',
        'waiting' => 'Waiting for PayPal...',
        'processtype' => 'processfixed',
        'processpercent' => '5',
        'processfixed' => '2',
        'qempaypalsubmit' => 'Register and Pay'
    );
    return $payment;
}