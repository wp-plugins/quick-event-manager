<?php
function event_custom_columns($column) {
	global $post;
	$custom = get_post_custom();
	switch ($column) {
		case "event_date":$date = $custom["event_date"][0];echo date("d", $date).' '.date("M", $date).' '.date("Y", $date);	break;
		case "event_time":echo $custom["event_start"][0] . ' - ' . $custom["event_finish"][0];break;
		case "event_location":echo $custom["event_location"][0];break;
		case "event_address":echo $custom["event_address"][0];break;
		case "event_website":echo $custom['event_link'][0];	break;
		case "event_cost":echo $custom["event_cost"][0];break;
		}
	}
function event_date_column_register_sortable( $columns ) {
	$columns['event_date'] = 'event_date';
	$columns['event_time'] = 'event_time';
	$columns['event_location'] = 'event_location';
	$columns['event_address'] = 'event_address';
	return $columns;
	}
function event_date_column_orderby($vars) {
	if ( isset( $vars['orderby'] ) && 'event_date' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
		'meta_key'	=> 'event_date',
		'orderby'	=> 'meta_value_num') );
		}
	return $vars;
	}
function event_edit_columns($columns) {
	$columns = array(
		"cb" => "<input type=\"checkbox\" />",
		"title" 			=> __('Event', 'quick-event-manager'),
		"event_date" 		=> __('Event Date', 'quick-event-manager'),
		"event_time" 		=> __('Event Time', 'quick-event-manager'),
		"event_location" 	=> __('Location', 'quick-event-manager'),
		"event_address" 	=> __('Address', 'quick-event-manager'),
		);
	return $columns;
	}
function event_details_meta() {
	global $post;
	$event = event_get_stored_options();
	$date = get_event_field('event_date');
	if (empty($date)) $date = time();
	$date = date_i18n("d M Y", $date);
	$enddate = get_event_field('event_end_date');
	if (!empty($enddate)) $enddate = date_i18n("d M Y", $enddate);
	$output = '<p><em>'.__('Empty fields are not displayed. See the plugin <a href="options-general.php?page=quick-event-manager/settings.php">settings</a> page for options.', 'quick-event-manager').'</em></p>
		<table width="100%">
		<tr>
		<td width="20%"><label>'.__('Date:', 'quick-event-manager').' </label></td>
		<td width="80%"><input type="text" class="qem_input" style="border:1px solid #415063;" id="qemdate" name="event_date" value="' . $date . '" /> <em>'.__('(Errors will reset to today&#146;s date.)', 'quick-event-manager').'</em>.</td>
		<script type="text/javascript">jQuery(document).ready(function() {jQuery(\'#qemdate\').datepicker({dateFormat : \'dd M yy\'});});</script>
		</tr>
		<tr>
		<td width="20%"><label>'.__('End Date:', 'quick-event-manager').' </label></td>
		<td width="80%"><input type="text" class="qem_input" style="border:1px solid #415063;"  id="qemenddate" name="event_end_date" value="' . $enddate . '" /> <em>'.__('(Leave blank for one day events.)', 'quick-event-manager').'</em>.</td>
		<script type="text/javascript">jQuery(document).ready(function() {jQuery(\'#qemenddate\').datepicker({dateFormat : \'dd M yy\'});});</script>
		</tr>
		<tr>
		<td width="20%"><label>'.__('Short Description:', 'quick-event-manager').' </label></td>
		<td width="80%"><input type="text" class="qem_input" style="width:100%;border:1px solid #415063;" name="event_desc" value="' . get_event_field("event_desc") . '" />
		</td></tr>
		<tr>
		<td width="20%"><label>'.__('Time', 'quick-event-manager').'</label></td>
		<td width="80%">' . $event['start_label'] . ' <input type="text" class="qem_input" style="border:1px solid #415063;"  name="event_start" value="' . get_event_field("event_start") . '" /> ' . $event['finish_label'] . ' <input type="text" style="width:40%;overflow:hidden;border:1px solid #415063;"   name="event_finish" value="' . get_event_field("event_finish") . '" /><br>
<span class="description">Start times in the format 8.23 am/pm, 8.23, 8:23 and 08:23 will be used to order events by time and date. All other formats will display but won\'t contribute to the event ordering.</span> 
		</td></tr>
		<tr>
		<td width="20%"><label>'.__('Location:', 'quick-event-manager').' </label></td>
		<td width="80%"><input type="text" class="qem_input" style="width:100%;border:1px solid #415063;"  name="event_location" value="' . get_event_field("event_location") . '" />
		</td></tr>
		<tr>
		<td width="20%"><label>'.__('Address:', 'quick-event-manager').' </label></td>
		<td width="80%"><input type="text" class="qem_input" style="width:100%;border:1px solid #415063;"  name="event_address" value="' . get_event_field("event_address") . '" />
		</td></tr>
		<tr>
		<td width="20%"><label>'.__('Website:', 'quick-event-manager').' </label></td>
		<td width="80%"><input type="text" class="qem_input" style="border:1px solid #415063;"  name="event_link" value="' . get_event_field("event_link") . '" /><label> '.__('Display As:', 'quick-event-manager').' </label><input type="text" style="width:40%;overflow:hidden;border:1px solid #415063;"  name="event_anchor" value="' . get_event_field("event_anchor") . '" />
		</td></tr>
		<tr>
		<td width="20%"><label>'.__('Cost:', 'quick-event-manager').' </label></td>
		<td width="80%"><input type="text" class="qem_input" style="width:100%;border:1px solid #415063;" name="event_cost" value="' . get_event_field("event_cost") . '" /></td></tr>
        <tr>
		<td width="20%"><label>'.__('Event forms:', 'quick-event-manager').' </label></td>
        <td width="80%"><input type="checkbox" style="" name="event_register" value="checked" ' . get_event_field("event_register") . '> Add registration form to this event. <a href="options-general.php?page=quick-event-manager/settings.php&tab=register">Registration form settings</a><br>
        <input type="checkbox" style="" name="event_counter" value="checked" ' . get_event_field("event_counter") . '> Add an attendee counter to this form. Number of places available: <input type="text" class="qem_input" style="width:3em;border:1px solid #415063;" name="event_number" value="' . get_event_field("event_number") . '" /><br>
        <input type="checkbox" style="" name="event_pay" value="checked" ' . get_event_field("event_pay") . '> Add payment form to this event. <a href="options-general.php?page=quick-event-manager/settings.php&tab=payment">Payment form settings</a>
		</td></tr>
		<tr><td width="20%">Event Image (replaces the event map)</td><td><input id="event_image" type="text" class="qem_input" style="border:1px solid #415063;" name="event_image" value="' . get_event_field("event_image") . '" />&nbsp;
   		<input id="upload_event_image" class="button" type="button" value="Upload Image" /></td></tr>';
    if (get_event_field("event_image")) $output .= '<tr><td></td><td><img class="qem-image" src=' . get_event_field("event_image") . '></td></tr>';
       $output .= '<tr><td style="vertical-align:top">Repeat Event:</td>
    <td><span style="color:red;font-weight:bold;">Warning:</span> Only use once or you will get lots of duplicated events<br />
    <input style="margin:0; padding:0; border:none" type="radio" name="event_repeat" value="repeatweekly" /> '.__('Weekly', 'quick-event-manager').'<br />
	<input style="margin:0; padding:0; border:none" type="radio" name="event_repeat" value="repeatmonthly" /> '.__('Monthly', 'quick-event-manager').'<br>
    Number of repetitions: <input type="text" class="qem_input" style="width:3em;border:1px solid #415063;" name="repeatnumber" value="12" /> (maximum 52)</td></tr>';
    
    $event = get_the_ID();
    $whoscoming = get_option($event);
    if ($whoscoming){
        foreach(array_keys($whoscoming) as $item) $event_names = $event_names.$item.', ';
        $event_names = substr($event_names, 0, -2); 
        $output .= '<tr><td>Attendees (names and emails collected from the <a href="options-general.php?page=quick-event-manager/settings.php&tab=register">registration form</a>)</td><td><input type="text" class="qem_input" style="width:100%;border:1px solid #415063;" name="event_names" value="' . $event_names.'" />
    </td></tr>';}
    $output .='</table>';
	echo $output;
	}

function qem_time ($starttime) {
    $starttime = str_replace('AM','',strtoupper($starttime));
	if (strpos($starttime,':')) $needle = ':';
	if (strpos($starttime,'.')) $needle = '.';
	if (strpos($starttime,' ')) $needle = ' ';
    if (strpos(strtoupper($starttime),'PM')) $afternoon = 49680;
	if ($needle) list($hours, $minutes) = explode($needle, $starttime);
    else $hours = $starttime;
    if (strlen($starttime) == 4 && is_numeric($starttime)) {$hours = substr($starttime, 0, 2);$minutes = substr($starttime, 3);}
    $seconds=$hours*3600+$minutes*60+$afternoon;
	return $seconds;
	}

function get_event_field($event_field) {
	global $post;
	$custom = get_post_custom($post->ID);
	if (isset($custom[$event_field])) return $custom[$event_field][0];
	}
function save_event_details() {
	global $post;
    $event = get_the_ID();
    $whoscoming = get_option($event);
    $number = get_option($event.'places');
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( get_post_type($post) != 'event') return;
$startdate = strtotime($_POST["event_date"]);
	$starttime = qem_time($_POST["event_start"]);
$newdate = $startdate+$starttime;
	if(isset($_POST["event_date"])) update_post_meta($post->ID, "event_date", $newdate);
	if(isset($_POST["event_end_date"])) update_post_meta($post->ID, "event_end_date", strtotime($_POST["event_end_date"]));
	save_event_field("event_desc");
	save_event_field("event_start");
	save_event_field("event_finish");
	save_event_field("event_location");
	save_event_field("event_address");
	save_event_field("event_link");
	save_event_field("event_anchor");
	save_event_field("event_cost");
    save_event_field("event_image");
    
    $old = get_event_field("event_number");
    $new = $_POST["event_number"];
    if ($new && $new != $old) {
        $number = $new - $old + $number; update_option($event.'places',$number);
        update_post_meta($post->ID, "event_number", $new);}
    elseif ('' == $new && $old) delete_post_meta($post->ID, "event_number", $old);
    
    $old = get_event_field("event_register");
    $new = $_POST["event_register"];
    if ($new && $new != $old) update_post_meta($post->ID, "event_register", $new);
    elseif ('' == $new && $old) delete_post_meta($post->ID, "event_register", $old);
    
    $old = get_event_field("event_counter");
    $new = $_POST["event_counter"];
    if ($new && $new != $old) update_post_meta($post->ID, "event_counter", $new);
    elseif ('' == $new && $old) delete_post_meta($post->ID, "event_counter", $old);
    
    $old = get_event_field("event_pay");
    $new = $_POST["event_pay"];
    if ($new && $new != $old) update_post_meta($post->ID, "event_pay", $new);
    elseif ('' == $new && $old) delete_post_meta($post->ID, "event_pay", $old);
    
    if(isset($_POST["event_names"])) {
        $event_names = $_POST['event_names'];
        foreach(array_keys($whoscoming) as $item) {if (!strrchr($event_names,$item)) $whoscoming[$item] = '';}
        $whoscoming = array_filter($whoscoming);
        update_option( $event, $whoscoming );
        }
    $harry = $_POST["repeatnumber"];
    $number =  (($harry > 52 || $harry == 0) ? 52 :  $harry);
    if ($_POST["event_repeat"] == 'repeatmonthly') {$_POST["event_repeat"] = ''; qem_duplicate_new_post($event,$number,'months');}
    if ($_POST["event_repeat"] == 'repeatweekly') {$_POST["event_repeat"] = ''; qem_duplicate_new_post($event,$number,'weeks');}
    }

function save_event_field($event_field) {
	global $post;
	if(isset($_POST[$event_field])) update_post_meta($post->ID, $event_field, $_POST[$event_field]);
	}

function action_add_meta_boxes() {
	add_meta_box('event_sectionid',__('Event Details', 'quick-event-manager'),'event_details_meta','event', 'normal', 'high');
	global $_wp_post_type_features;
	if (isset($_wp_post_type_features['event']['editor']) && $_wp_post_type_features['event']['editor']) {
		unset($_wp_post_type_features['event']['editor']);
		add_meta_box('description_section', __('Event Description', 'quick-event-manager'),'inner_custom_box','event', 'normal', 'high');
		}
}

function inner_custom_box( $post ) {
    $settings = array('wpautop'=>false);
    wp_editor($post->post_content, 'post_content', $settings);
	}

function qem_duplicate_month() {
	qem_duplicate_post('+1month');
	}

function qem_duplicate_week() {
	qem_duplicate_post('+7days');
	}

function qem_duplicate_post($period) {
    global $wpdb;
    if (!(isset( $_GET['post']) || isset($_POST['post'])  || (isset($_REQUEST['action']) && 'qem_duplicate_post' == $_REQUEST['action'])))
        wp_die('No post to duplicate has been supplied!');
	$post_id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
	$post = get_post( $post_id );
    qem_create_duplicate_post($period,$post_id,$post);
    wp_redirect( admin_url( 'edit.php?post_type=event' ) );
    exit;
    }

function qem_duplicate_new_post($post_id,$number,$word) {
    global $wpdb;
	$post = get_post( $post_id );
    for ($i=1;$i<=$number;$i++) qem_create_duplicate_post('+'.$i.$word,$post_id,$post);
    }

function qem_create_duplicate_post($period,$post_id,$post) {
	global $wpdb;
	$current_user = wp_get_current_user();
	$new_post_author = $current_user->ID;
	if (isset( $post ) && $post != null) {
		$args = array(
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'post_author'    => $new_post_author,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_name'      => $post->post_name,
			'post_parent'    => $post->post_parent,
			'post_password'  => $post->post_password,
			'post_status'    => 'publish',
			'post_title'     => $post->post_title,
			'post_type'      => $post->post_type,
			'to_ping'        => $post->to_ping,
			'menu_order'     => $post->menu_order
		  );
		$new_post_id = wp_insert_post( $args );
		$taxonomies = get_object_taxonomies($post->post_type);
		foreach ($taxonomies as $taxonomy) {
			$post_terms = wp_get_object_terms($post_id, $taxonomy);
			for ($i=0; $i<count($post_terms); $i++) {
				wp_set_object_terms($new_post_id, $post_terms[$i]->slug, $taxonomy, true);
                }
        }
        $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
        if (count($post_meta_infos)!=0) {
			$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
			foreach ($post_meta_infos as $meta_info) {
				$meta_key = $meta_info->meta_key;
				if ($meta_key == 'event_date') {$meta_value = strtotime($period, $meta_info->meta_value);}
				elseif ($meta_key == 'event_end_date'  && $meta_info->meta_value) {$meta_value = strtotime($period, $meta_info->meta_value);}
                else $meta_value = addslashes($meta_info->meta_value);
				$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
                }
			$sql_query.= implode(" UNION ALL ", $sql_query_sel);
			$wpdb->query($sql_query);
            }
        } else {
            wp_die('Post creation failed, could not find original post: ' . $post_id);
        }
    }
add_action( 'admin_action_qem_duplicate_month', 'qem_duplicate_month' );
add_action( 'admin_action_qem_duplicate_week', 'qem_duplicate_week' );
 
function duplicate_post_month( $actions, $post ) {
	if (current_user_can('edit_posts') && 'event' == get_post_type() ) {
		$actions['duplicate'] = '<a href="admin.php?action=qem_duplicate_month&amp;post=' . $post->ID . '" title="Duplicate this item" rel="permalink">Monthly</a>';
		}
	return $actions;
	}

function duplicate_post_week( $actions, $post ) {
	if (current_user_can('edit_posts') && 'event' == get_post_type() ) {
		$actions['duplicate2'] = '<a href="admin.php?action=qem_duplicate_week&amp;post=' . $post->ID . '" title="Duplicate this item" rel="permalink">Weekly</a>';
		}
	return $actions;
	}

add_filter( 'post_row_actions', 'duplicate_post_month', 10, 2 );
add_filter( 'post_row_actions', 'duplicate_post_week', 10, 2 );