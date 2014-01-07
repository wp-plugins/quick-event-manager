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
		<td width="80%"><input type="text" class="qem_input" style="width:100%;border:1px solid #415063;" name="event_desc" value="' . get_event_field("event_desc") . '" /></td>
		</td></tr>
		<tr>
		<td width="20%"><label>'.__('Time', 'quick-event-manager').' <em>(hh:mm): ' . $event['start_label'] . ' </label></td>
		<td width="80%"><input type="text" class="qem_input" style="border:1px solid #415063;"  name="event_start" value="' . get_event_field("event_start") . '" /> ' . $event['finish_label'] . ' <input type="text" style="width:40%;overflow:hidden;border:1px solid #415063;"   name="event_finish" value="' . get_event_field("event_finish") . '" /></td>
		</td></tr>
		<tr>
		<td width="20%"><label>'.__('Location:', 'quick-event-manager').' </label></td>
		<td width="80%"><input type="text" class="qem_input" style="width:100%;border:1px solid #415063;"  name="event_location" value="' . get_event_field("event_location") . '" /></td>
		</td></tr>
		<tr>
		<td width="20%"><label>'.__('Address:', 'quick-event-manager').' </label></td>
		<td width="80%"><input type="text" class="qem_input" style="width:100%;border:1px solid #415063;"  name="event_address" value="' . get_event_field("event_address") . '" /></td>
		</td></tr>
		<tr>
		<td width="20%"><label>'.__('Website:', 'quick-event-manager').' </label></td>
		<td width="80%"><input type="text" class="qem_input" style="border:1px solid #415063;"  name="event_link" value="' . get_event_field("event_link") . '" /><label> '.__('Display As:', 'quick-event-manager').' </label><input type="text" style="width:40%;overflow:hidden;border:1px solid #415063;"  name="event_anchor" value="' . get_event_field("event_anchor") . '" /></td>
		</td></tr>
		<tr>
		<td width="20%"><label>'.__('Cost:', 'quick-event-manager').' </label></td>
		<td width="80%"><input type="text" class="qem_input" style="width:100%;border:1px solid #415063;" name="event_cost" value="' . get_event_field("event_cost") . '" /></td>
		</td></tr>
		</table>';
	echo $output;
	}
function get_event_field($event_field) {
	global $post;
	$custom = get_post_custom($post->ID);
	if (isset($custom[$event_field]))
		return $custom[$event_field][0];
	}
function save_event_details() {
	global $post;
	$event = event_get_stored_options();
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( get_post_type($post) != 'event') return;
	if(isset($_POST["event_date"])) $setdate = $_POST["event_date"];
	update_post_meta($post->ID, "event_date", strtotime($setdate));
	if(isset($_POST["event_end_date"])) $setenddate = $_POST["event_end_date"];
	update_post_meta($post->ID, "event_end_date", strtotime($setenddate));
	save_event_field("event_desc");
	save_event_field("event_start");
	save_event_field("event_finish");
	save_event_field("event_location");
	save_event_field("event_address");
	save_event_field("event_link");
	save_event_field("event_anchor");
	save_event_field("event_cost");
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
	the_editor($post->post_content);
	}
function qem_duplicate_month() {
	qem_duplicate_post($period = '+1month');
	}
function qem_duplicate_week() {
	$period = '+7days';qem_duplicate_post($period);
	}
function qem_duplicate_post($period){
	global $wpdb;
	if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'qem_duplicate_post' == $_REQUEST['action'] ) ) ) {
		wp_die('No post to duplicate has been supplied!');
		}
	$post_id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
	$post = get_post( $post_id );
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
		$taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
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
				else $meta_value = addslashes($meta_info->meta_value);
				$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
			}
			$sql_query.= implode(" UNION ALL ", $sql_query_sel);
			$wpdb->query($sql_query);
		}
		wp_redirect( admin_url( 'edit.php?post_type=event' ) );
		exit;
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