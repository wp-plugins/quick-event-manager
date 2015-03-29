<?php
global $_GET;
$event = $_GET["event"];
$title = $_GET["title"];
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
    for($i = 0; $i <= 10; $i++) {
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
    $content = qem_build_registration_table ($register,$message,'','','');
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

$messageoptions = qem_get_stored_msg();
$$messageoptions['showevents'] = "checked";
$message = get_option('qem_messages_'.$event);
$places = get_option($event.'places');
$check = get_post_meta($event, 'event_counter', true);
if(!is_array($message)) $message = array();
$dashboard .= '<div class="wrap">
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
$content = qem_build_registration_table ($register,$message,$places,$check,'');
if ($content) {
    $dashboard .= '<h2>'.$title.' | '.$date.'</h2>';
    if ($event) $dashboard .= '<p>Event ID: '.$event.'</p>';
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

function qem_get_eventlist ($event,$register,$messageoptions,$thecat) {
    global $post;
    $arr = get_categories();
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
                $content .= '<option value="'.$id.'" '.$selected.'>'.$title.' | '.$date.'</option>';
        }
        $content .= '</select>
        <noscript><input type="submit" name="select_event" class="button-primary" value="Select Event" /></noscript>';
    }
    return $content;
}

function qem_message_categories ($thecat) {
    $arr = get_categories();
    $content .= '<select name="category" onchange="this.form.submit()">';
    $content .= '<option value="">All Categories</option>';
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