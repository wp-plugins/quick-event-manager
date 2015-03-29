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
    $calendar['day'] = '#EBEFC9';
    $calendar['calday'] = '#EBEFC9';
    $calendar['eventday'] = '#EED1AC';
    $calendar['oldday'] = '#CCC';
    $calendar['eventhover'] = '#F2F2E6';
    $calendar['eventdaytext'] = '#343838';
    $calendar['eventbackground'] = '#FFF';
    $calendar['eventtext'] = '#343838';
    $calendar['eventlink'] = 'linkpopup';
    $calendar['calendar_text'] = 'View as calendar';
    $calendar['calendar_url'] = '';
    $calendar['eventlist_text'] = 'View as a list of events';
    $calendar['eventlist_url'] = '';
    $calendar['eventlength'] = '20';
    $calendar['connect'] = '';
    $calendar['startday'] = 'sunday';
    $calendar['archive'] = 'checked';
    $calendar['archivelinks'] = 'checked';
    $calendar['prevmonth'] = 'Prev';
    $calendar['nextmonth'] = 'Next';
    $calendar['smallicon'] = 'arrow';
    $calendar['unicode'] = '\263A';
    $calendar['eventtext'] = '#343838';
    $calendar['eventbackground'] = '#FFF';
    $calendar['eventhover'] = '#EED1AC';
    $calendar['eventborder'] = '1px solid #343838';
    $calendar['keycaption'] = 'Event Key:';
    $calendar['navicon'] = 'arrows';
    $calendar['linktocategory'] = 'checked';
    $calendar['showuncategorised'] ='';
    return $calendar;
}

function qem_get_stored_register () {
    $register = get_option('qem_register');
    if(!is_array($register)) $register = array();
    $default = qem_get_default_register();
    $register = array_merge($default, $register);
    if (!strpos($register['sort'],'9')) {$register['sort'] = $register['sort'].',field9';$update='checked';}
    if (!strpos($register['sort'],'10')) {$register['sort'] = $register['sort'].',field10';$update='checked';}
    if (!strpos($register['sort'],'11')) {$register['sort'] = $register['sort'].',fiel11';$update='checked';}
    if (!strpos($register['sort'],'12')) {$register['sort'] = $register['sort'].',fiel12';$update='checked';}
    if ($update) {update_option('qem_register',$register);}
    return $register;
}

function qem_get_default_register () {
    $register = array();
    $register['sort'] = 'field1,field2,field3,field4,field5,field6,field7,field8,field9,field10,field11,field12';
    $register['usename'] = 'checked';
    $register['usemail'] = 'checked';
    $register['useblank1'] = '';
    $register['useblank2'] = '';
    $register['usedropdown'] = '';
    $register['usenumber1'] = '';
    $register['reqname'] = 'checked';
    $register['reqmail'] = 'checked';
    $register['reqblank1'] = '';
    $register['reqblank2'] = '';
    $register['reqdropdown'] = '';
    $register['reqnumber1'] = '';
    $register['formborder'] = '';
    $register['title'] = 'Register for this event';
    $register['blurb'] = 'Enter your details below';
    $register['replytitle'] = 'Thank you for registering';
    $register['replyblurb'] = 'We will be in contact soon';
    $register['yourname'] = 'Your Name';
    $register['youremail'] = 'Email Address';
    $register['yourtelephone'] = 'Telephone Number';
    $register['yourplaces'] = 'Number of places required';
    $register['yourmessage'] = 'Message';
    $register['yourattend'] = 'I will not be attending this event';
    $register['yourblank1'] = 'More Information';
    $register['yourblank2'] = 'More Information';
    $register['yourdropdown'] = 'Separate,With.Commas';
    $register['yournumber1'] = 'Number';
    $register['error'] = 'Please complete the form';
    $register['subject'] = 'Registration for:';
    $register['qemsubmit'] = 'Register';
    $register['subjecttitle'] = 'checked';
    $register['subjectdate'] = 'checked';
    $register['whoscoming'] = '';
    $register['whoscomingmessage'] = 'Look who\'s coming: ';
    $register['placesbefore'] = 'There are';
    $register['placesafter'] = 'places available.';
    $register['eventfull'] = '';
    $register['eventfullmessage'] = 'Registration is closed';
    $register['read_more'] = 'Return to the event page';
    $register['sendcopy'] = '';
    $register['usecopy'] = '';
    $register['completed'] ='';
    $register['copyblurb'] = 'Send registration details to your email address';
    $register['alreadyregistered'] = 'You are already registered for this event';
    $register['spam'] = 'Your Details have been flagged as spam';
    $register['thanksurl'] = '';
    $register['cancelurl'] = '';
    $register['currency'] = 'USD';
    $register['paypal'] = '';
    $register['paypalemail'] = '';
    $register['waiting'] = 'Waiting for PayPal...';
    $register['processtype'] = 'processfixed';
    $register['processpercent'] = '5';
    $register['processfixed'] = '2';
    $register['qempaypalsubmit'] = 'Register and Pay';
    return $register;
}

function qem_get_stored_payment () {
    $payment = get_option('qem_payment');
    if(!is_array($payment)) $payment = array();
    $default = qem_get_default_payment();
    $payment = array_merge($default, $payment);
    return $payment;
}

function qem_get_default_payment () {
    $payment = array();
    $payment['useqpp'] = '';
    $payment['qppform'] = '';
    $payment['qppcost'] = 'checked';
    return $payment;
}