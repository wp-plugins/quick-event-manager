<?php
/*
Plugin Name: Quick Event Manager
Plugin URI: http://www.quick-plugins.com/quick-event-manager
Description: A really, really simple event manager. There is nothing to configure, all you need is an event and the shortcode.
Version: 1.6
Author: fisicx
Author URI: http://www.quick-plugins.com
*/

add_shortcode( 'qem', 'event_shortcode' );

add_action( 'init', 'event_register');
add_action( 'save_post', 'save_event_details');
add_action( 'admin_notices', 'event_admin_notice' );
add_action( 'add_meta_boxes', 'action_add_meta_boxes', 0 );
add_action( "manage_posts_custom_column",  "event_custom_columns");
add_filter( "manage_event_posts_columns", "event_edit_columns");
add_filter( "manage_edit-event_sortable_columns", "event_date_column_register_sortable");
add_filter( "request", "event_date_column_orderby" );
add_filter( 'plugin_action_links', 'event_plugin_action_links', 10, 2 );
add_filter('pre_get_posts', 'query_post_type');

$styleurl = plugins_url('quick-event-manager-style.css', __FILE__);
wp_register_style('event_style', $styleurl);
wp_enqueue_style( 'event_style');

/* register_deactivation_hook( __FILE__, 'event_delete_options' ); */
register_uninstall_hook(__FILE__, 'event_delete_options');

function event_delete_options() {
	delete_option('event_settings');
	}

function event_page_init() {
	add_options_page('Event Settings', 'Event Settings', 'manage_options', __FILE__, 'event_settings');
	}

function event_admin_notice($message) {
	if (!empty( $message)) echo '<div class="updated"><p>'.$message.'</p></div>';
	}

function event_plugin_action_links($links, $file) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$event_links = '<a href="'.get_admin_url().'options-general.php?page=quick-event-manager/quick-event-manager.php">'.__('Settings').'</a>';
		array_unshift( $links, $event_links );
		}
	return $links;
	}

function event_settings() {
	$active_buttons = array( 'field1' , 'field2' , 'field3' , 'field4' , 'field5' , 'field6' ,);	
	if( isset( $_POST['Submit']))
		{
		foreach ( $active_buttons as $item)
			{
			$event['active_buttons'][$item] = (isset( $_POST['event_settings_active_'.$item]) and $_POST['event_settings_active_'.$item] == 'on' ) ? true : false;
			$event['summary'][$item] = (isset( $_POST['summary_'.$item]) );
			$event['bold'][$item] = (isset( $_POST['bold_'.$item]) );
			$event['italic'][$item] = (isset( $_POST['italic_'.$item]) );
			$event['colour'][$item] = $_POST['colour_'.$item];
			$event['size'][$item] = $_POST['size_'.$item];
			if (!empty ( $_POST['label_'.$item])) $event['label'][$item] = $_POST['label_'.$item];
			}
		$event['sort'] = $_POST['qem_settings_sort'];
		$event['description_label'] = $_POST['description_label'];
		$event['address_label'] = $_POST['address_label'];
		$event['url_label'] = $_POST['url_label'];
		$event['cost_label'] = $_POST['cost_label'];
		$event['start_label'] = $_POST['start_label'];
		$event['finish_label'] = $_POST['finish_label'];
		$event['location_label'] = $_POST['location_label'];
		$event['show_map'] = $_POST['show_map'];
		$event['read_more'] = $_POST['read_more'];
		$event['dateformat'] = $_POST['dateformat'];
		$event['address_style'] = $_POST['address_style'];
		$event['website_link'] = $_POST['website_link'];
		$event['date_background'] = $_POST['date_background'];
		$event['background_hex'] = $_POST['background_hex'];
		$event['event_order'] = $_POST['event_order'];
		$event['event_archive'] = $_POST['event_archive'];
		$event['calender_size'] = $_POST['calender_size'];
		$event['map_width'] = $_POST['map_width'];
		$event['map_height'] = $_POST['map_height'];
		$event['date_bold'] = $_POST['date_bold'];
		$event['date_italic'] = $_POST['date_italic'];
		update_option( 'event_settings', $event);
		event_admin_notice("The form settings have been updated.");
		}
	$event = event_get_stored_options();
	$$event['dateformat'] = 'checked'; 
	$$event['date_background'] = 'checked'; 
	$$event['event_order'] = 'checked'; 
	$$event['calender_size'] = 'checked'; 
	if ( $event['event_archive'] == "checked") $archive = "checked"; 
	if ($event['show_map'] == 'checked') $map = 'checked';
	$content = '<div class="wrap">
	<h1>Quick Events List</h1>
		<script>
		jQuery(function() {
			var qem_sort = jQuery( "#qem_sort" ).sortable({ axis: "y" ,
			update:function(e,ui) {
				var order = qem_sort.sortable("toArray").join();
				jQuery("#qem_settings_sort").val(order);
				}
			});
		});
		</script>
		<div id ="qem-style">
		<form id="event_settings_form" method="post" action="">
		<h2>Using the plugin</h2>
		<p>Create new events using the <a href="'.get_admin_url().'edit.php?post_type=event">Events</a> link on your dashboard menu. To add an event list to your posts or pages use the short code: <code>[qem]</code>.<br />
		<p><span style="color:red; font-weight:bold;">Important!</span> This plugin uses custom posts. For it to work properly you have to resave your <a href="'.get_admin_url().'options-permalink.php">permalinks</a>. This is not a bug, it&#146;s how wordpress works. If you don&#146;t resave your permalinks you will get a page not found on your events.</p>
		<h2>Event Display</h2>
		<p>Use the check boxes to select which fields to display in the event post and the event list. Drag and drop to change the order of the fields.</p>
		<p>The fields with the blue border are for optional captions. For example: <span style="color:blue">The cost is</span> {cost} will display as <em>The cost is 20 Zlotys</em>. If you leave it blank just <em>20 Zlotys</em> will display.</p>
		<p><b><div style="float:left; margin-left:7px;width:11em;">Show in post</div><div style="float:left; width:6em;">Show in<br>event list</div><div style="float:left; width:9em;">Colour</div><div style="float:left; width:5em;">Font<br>size</div><div style="float:left; width:8em;">Font<br>attributes</div><div style="float:left; width:28em;">Caption and display options:</div><div style="float:left;">Position:</div></b></p>
		<div style="clear:left"></div>
		<ul id="qem_sort">';
		$first = 'first';
		$sort = explode(",", $event['sort']); 
		$last = array_pop($sort);
		foreach (explode( ',',$event['sort']) as $name)
			{
			if ($name == $last && $event['active_buttons'][$name] == 'checked') $first = 'last';
			$checked = ( $event['active_buttons'][$name]) ? 'checked' : '';
			$summary = ( $event['summary'][$name]) ? 'checked' : '';
			$bold = ( $event['bold'][$name]) ? 'checked' : '';
			$italic = ( $event['italic'][$name]) ? 'checked' : '';
			$options = '';
			switch ( $name )
				{
				case 'field1':
					$options = '<input type="text" style="border:1px solid blue; width:10em; padding: 1px; margin:0;" name="description_label" . value ="' . $event['description_label'] . '" /> {summary}';
					break;
				case 'field2':
					$options = '<input type="text" style="border:1px solid blue; width:6em; padding: 1px; margin:0;" name="start_label" . value ="' . $event['start_label'] . '" /> {start time} <input type="text" style="border:1px solid blue; width:6em; padding: 1px; margin:0;" name="finish_label" . value ="' . $event['finish_label'] . '" /> {end time}';
					break;	
				case 'field3':
					$options = '<input type="text" style="border:1px solid blue; width:6em; padding: 1px; margin:0;" name="location_label" . value ="' . $event['location_label'] . '" /> {location} ';
					break;
				case 'field4':
					$options = '<input type="text" style="border:1px solid blue; width:10em; padding: 1px; margin:0;" name="address_label" . value ="' . $event['address_label'] . '" /> {address}&nbsp;&nbsp;<input type="checkbox" style="margin: 0; padding: 0; border: none;" name="show_map"' . $event['show_map'] . ' value="checked" /> Show map (if address is given)';
					break;
				case 'field5':
					$options = '<input type="text" style="border:1px solid blue; width:10em; padding: 1px; margin:0;" name="url_label" . value ="' . $event['url_label'] . '" /> {url}&nbsp;&nbsp;&nbsp;<input type="checkbox" style="margin: 0; padding: 0; border: none;" name="website_link"' . $event['website_link'] . ' value="checked" />Link to website';
					break;
				case 'field6':
					$options = '<input type="text" style="border:1px solid blue; width:10em; padding: 1px; margin:0;" name="cost_label" . value ="' . $event['cost_label'] . '" /> {cost}';
					break;
				}
			$li_class = ( $checked) ? 'button_active' : 'button_inactive';
		$content .= '<li class="ui-state-default '.$li_class.' '.$first.'" id="' . $name . '">
		<div style="float:left; width:11em; overflow:hidden;">
		<input type="checkbox" class="button_activate" style="border: none; padding: 0; margin:0;" name="event_settings_active_' . $name . '" ' . $checked . ' />
		<b>' . $event['label'][$name] . '</b>
		</div>
		<div style="float:left; width:6em; overflow:hidden;">
		<input type="checkbox" style="border: none; padding: 0; margin:0;" name="summary_' . $name . '" ' . $summary . ' />
		</div>
		<div style="float:left; width:9em; overflow:hidden;">
		<input type="text" style="border:1px solid #415063; width:8em; padding: 1px; margin:0;" name="colour_' . $name . '" . value ="' . $event['colour'][$name] . '" />
		</div>
		<div style="float:left; width:5em; overflow:hidden;">
		<input type="text" style="border:1px solid #415063; width:3em; padding: 1px; margin:0;" name="size_' . $name . '" . value ="' . $event['size'][$name] . '" />%
		</div>
		<div style="float:left; width:8em; overflow:hidden;">
		<input type="checkbox" style="border: none; padding: 0; margin:0;" name="bold_' . $name . '" ' . $bold . ' /> Bold
		<input type="checkbox" style="border: none; padding: 0; margin:0;" name="italic_' . $name . '" ' . $italic . ' /> Italic
		</div>
		<div style="float:left; width:32em; overflow:hidden;">
		' . $options . '</div>
		</li>';
	$first = '';
	}
	$content .= '
		</ul>
		<p>Read more caption: <input type="text" style="width:20em;border:1px solid #415063;" label="read_more" name="read_more" value="' . $event['read_more'] . '" /></p>
		<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /></p>
		<h2>Date Format</h2>
		<p>
			<input style="margin: 0; padding: 0; border: none;" type="radio" name="dateformat" value="usa" ' . $usa . ' /> US Format (MM/DD/YYYY)<br />
			<input style="margin: 0; padding: 0; border: none;" type="radio" name="dateformat" value="world" ' . $world . ' /> Everybody else in the world (DD/MM/YYYY)</p>
		<h2>Calender Icon</h2>
		<div>
		<div style="float:left; width:150px; margin-right: 10px">
		<h3>Size</h3>
		<p>
			<input style="margin: 0; padding: 0; border: none;" type="radio" name="calender_size" value="small" ' . $small . ' /> Small (40px)<br />
			<input style="margin: 0; padding: 0; border: none;" type="radio" name="calender_size" value="medium" ' . $medium . ' /> Medium (60px)<br />
			<input style="margin: 0; padding: 0; border: none;" type="radio" name="calender_size" value="large" ' . $large . ' /> Large (80px)</p>
		</div>
		<div style="float:left; width:200px; margin-right: 10px">
		<h3>Background colour</h3>
		<p>
			<input style="margin: 0; padding: 0; border: none;" type="radio" name="date_background" value="grey" ' . $grey . ' /> Grey<br />
			<input style="margin: 0; padding: 0; border: none;" type="radio" name="date_background" value="red" ' . $red . ' /> Red<br />
			<input style="margin: 0; padding: 0; border: none;" type="radio" name="date_background" value="color" ' . $color . ' /> Set your own (enter HEX code or color name below)</p>
			<p><input type="text" style="width:7em;border:1px solid #415063;" label="background" name="background_hex" value="' . $event['background_hex'] . '" /></p>
		</div>
		<div style="float:left; width:200px;">
		<h3>Month Style</h3>
		<p>
			<input style="margin: 0; padding: 0; border: none;" type="checkbox" name="date_bold" value="checked" ' . $event['date_bold'] . ' /> Bold<br />
			<input style="margin: 0; padding: 0; border: none;" type="checkbox" name="date_italic" value="checked" ' . $event['date_italic'] . ' /> Italic</p>
			</div>
		</div>
		<div style="clear:left"></div>
		<h2>Event List Options</h2>
		<p><input type="checkbox" style="border: none; padding: 0; margin:0;" name="event_archive" value="checked" ' . $archive . ' /> Show past events</p>
		<h2>Map Size</h2>
		<p>Width: <input type="text" style="border:1px solid #415063; width:3em; padding: 1px; margin:0;" name="map_width" . value ="' . $event['map_width'] . '" /> px&nbsp;&nbsp;Height: <input type="text" style="border:1px solid #415063; width:3em; padding: 1px; margin:0;" name="map_height" . value ="' . $event['map_height'] . '" /> px</p>
		<p>Note: the map will only display if you have a valid address and the &#146;show map&#146; checkbox is ticked.</p>
		<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /></p>
		<input type="hidden" id="qem_settings_sort" name="qem_settings_sort" value="'.stripslashes( $event['sort']).'" />
		</form>
		</div></div>';
	echo $content;
	}

function action_add_meta_boxes() {
	add_meta_box( 
        'event_sectionid',
       'Event Details',
        'event_details_meta',
        'event', 'normal', 'high'
		);
	global $_wp_post_type_features;
	if (isset($_wp_post_type_features['event']['editor']) && $_wp_post_type_features['event']['editor']) {
		unset($_wp_post_type_features['event']['editor']);
		add_meta_box(
			'description_section',
			__('Event Description'),
			'inner_custom_box',
			'event', 'normal', 'low'
		);
		}
	}

function inner_custom_box( $post ) {
	the_editor($post->post_content);
	}

function event_register() {
	$labels = array(
		'name' => _x('Events', 'post type general name'),
		'singular_name' => _x('Event', 'post type singular name'),
		'add_new' => _x('Add New', 'event'),
		'add_new_item' => __('Add New Event'),
		'edit_item' => __('Edit Event'),
		'new_item' => __('New Event'),
		'view_item' => __('View Event'),
		'search_items' => __('Search event'),
		'not_found' =>  __('Nothing found'),
		'not_found_in_trash' => __('Nothing found in Trash'),
		'parent_item_colon' => ''
	);
	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => true,
		'show_in_menu' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'has_archive' => true,
		'menu_position' => null,
		'taxonomies' => array('category','post_tag',),
		'supports' => array('title','editor','thumbnail','comments')
	  );
	register_post_type( 'event' , $args );
	}

function event_edit_columns($columns)
	{
	$columns = array(
		"cb" => "<input type=\"checkbox\" />",
		"title" => "Event",
		"event_date" => "Event Date",
		"event_time" => "Event Time",
		"event_location" => "Location",
		"event_address" => "Address",
		"event_website" => "Website",
		"event_cost" => "Cost",
		);
	return $columns;
	}

function event_custom_columns($column) {
	global $post;
	$custom = get_post_custom();
	switch ($column)
		{
		case "event_date":
			$date = $custom["event_date"][0];
			echo date("d", $date).' '.date("M", $date).' '.date("Y", $date);
			break;
		case "event_time":
			echo $custom["event_start"][0] . ' - ' .
			$custom["event_finish"][0];
			break;
		case "event_location":
			echo $custom["event_location"][0];
			break;
		case "event_address":
			echo $custom["event_address"][0];
			break;
		case "event_website":
			echo $custom['event_link'][0];
			break;
		case "event_cost":
			echo $custom["event_cost"][0];
			break;
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
		'meta_key' => 'event_date',
		'orderby' => 'meta_value_num'
		) );
		}
	return $vars;
	}

function event_details_meta() {
	global $post;
	$event = event_get_stored_options();
	$date = get_event_field('event_date');
	if ($event['dateformat'] == 'world') { $datef = date("d", $date) . '/' . date("m", $date) . '/' . date("Y", $date); $format = "dd/mm/yyyy";}
	if ($event['dateformat'] == 'usa') { $datef = date("m", $date) . '/' . date("d", $date) . '/' . date("Y", $date); $format = "mm/dd/yyyy";}
	$output = '
	<p><em>Empty fields are not displayed. See the plugin <a href="'.get_admin_url().'options-general.php?page=quick-event-manager/quick-event-manager.php">settings</a> page for options.</em></p>
	<p><label>Date: </label><input type="text" style="border:1px solid #415063;" name="event_date" value="' . $datef . '" /> <em>(Current format is ' . $format . '. Errors will reset to today&#146;s date.)</em>.</p>
	<p><label>Summary: </label><input type="text" style="border:1px solid #415063;" size="100" name="event_desc" value="' . get_event_field("event_desc") . '" /></p>
	<p><label>Time <em>(hh:mm)</em>: ' . $event['start_label'] . ' </label><input type="text" style="border:1px solid #415063;"  name="event_start" value="' . get_event_field("event_start") . '" /> ' . $event['finish_label'] . ' <input type="text" style="border:1px solid #415063;"  name="event_finish" value="' . get_event_field("event_finish") . '" /></p>
	<p><label>Location: </label><input type="text" style="border:1px solid #415063;" size="70" name="event_location" value="' . get_event_field("event_location") . '" /></p>
	<p><label>Address: </label><input type="text" style="border:1px solid #415063;" size="100" name="event_address" value="' . get_event_field("event_address") . '" /></p>
	<p><label>Website: </label><input type="text" style="border:1px solid #415063;" size="70" name="event_link" value="' . get_event_field("event_link") . '" /></p>
	<p><label>Cost: </label><input type="text" style="border:1px solid #415063;" size="70" name="event_cost" value="' . get_event_field("event_cost") . '" /></p>';
	echo $output;
	}

function get_event_field($event_field) {
	global $post;
	$custom = get_post_custom($post->ID);
	if (isset($custom[$event_field])) return $custom[$event_field][0];
	}

function save_event_details() {
	global $post;
	$event = event_get_stored_options();
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( get_post_type($post) != 'event') return;
	$date_array = getdate();
		$month = $date_array[mon];
		$day = $date_array[mday];
		if (strlen($day) < 2) $day = '0'.$day;
		if (strlen($month) < 2) $month = '0'.$month;
		$us_date  = $month . "/" . $day . "/" . $date_array[year];
		$uk_date  = $day . "/" . $month . "/" . $date_array[year];	
	if(isset($_POST["event_date"])) $setdate = $_POST["event_date"];
	if ($event['dateformat'] == 'world') {
		if (!preg_match("/^(0[1-9]|[12][0-9]|3[01])[.-\/](0[1-9]|1[012])[.-\/](19|20)\d\d$/" , $setdate )) $setdate = $uk_date;
		$setdate = str_replace('/' , '.' , $setdate);
		}
	if ($event['dateformat'] == 'usa') {
		if (!preg_match("/^(0[1-9]|1[012])[.-\/](0[1-9]|[12][0-9]|3[01])[\/](19|20)\d\d$/" , $setdate )) $setdate = $us_date;
		}
	update_post_meta($post->ID, "event_date", strtotime($setdate));
	save_event_field("event_desc");
	save_event_field("event_start");
	save_event_field("event_finish");
	save_event_field("event_location");
	save_event_field("event_address");
	save_event_field("event_link");
	save_event_field("event_cost");
	}

function save_event_field($event_field) {
	global $post;
	if(isset($_POST[$event_field])) update_post_meta($post->ID, $event_field, $_POST[$event_field]);
	}

function event_shortcode($atts) {
	global $post;
	$event = event_get_stored_options();
	if ($event['calender_size'] == 'small') $width = 'style="margin-left: 60px"';
	if ($event['calender_size'] == 'medium') $width = 'style="margin-left: 80px"';
	if ($event['calender_size'] == 'large') $width = 'style="margin-left: 100px"';
	extract( shortcode_atts( array(	'daterange' => 'current', ), $atts ) );
	ob_start();
	$args = array(
		'post_type'	=> 'event',
		'orderby'  	=> 'meta_value_num',
		'meta_key'	=> 'event_date',
		'order'	=> 'ASC'
		);
	query_posts( $args );
	$event_found = false;
	$today = time();
	if ( have_posts() )
		{
		while ( have_posts() )
			{
			the_post();
				$link = get_post_meta($post->ID, 'event_link', true);
				$endtime = get_post_meta($post->ID, 'event_end_time', true);
				$unixtime = get_post_meta($post->ID, 'event_date', true);
				if ($unixtime > $today || $event['event_archive'] == 'checked') {
				$content = '<div class="qem">' . 
				get_event_calendar_icon() . 
				'<div class="qem-details" ' . $width . '>' . 
				get_event_summary() . '</div>';
    			echo $content;
				
				$event_found = true;
				}
			}
		}
	wp_reset_query();
	if (!$event_found) echo "<p>No event found.</p>";
	$output_string = ob_get_contents();
	ob_end_clean();
	return $output_string;
	}

function get_event_calendar_icon() {
	global $post;
	$event = event_get_stored_options();
	setlocale(LC_TIME,get_locale().'.UTF8');
	setlocale(LC_TIME,'de_DE');
	if ($event['calender_size'] == 'small') $width = 'small';
	if ($event['calender_size'] == 'medium') $width = 'medium';
	if ($event['calender_size'] == 'large') $width = 'large';
	if ($event['date_background'] == 'color') $color = $event['background_hex'];
	if ($event['date_background'] == 'grey') $color = '#343838';
	if ($event['date_background'] == 'red') $color = 'red';
	if ($event['date_bold']) {$boldon = '<b>'; $boldoff = '</b>';} else {$boldon = ''; $boldoff = '';}
	if ($event['date_italic']) {$italicon = '<em>'; $italicoff = '</em>';} else {$italicon = ''; $italicoff = '';}
	$background = ' style="background:' . $color . ';border:1px solid ' . $color . ';"';
	$unixtime = get_post_meta($post->ID, 'event_date', true);
    $month = date_i18n("M", $unixtime);
    $day = date_i18n("d", $unixtime);
    $year = date_i18n("Y", $unixtime);
	return '<div class="qem-calendar-' . $width . '"><span class="day"' . $background . '>'.$day.'</span><span class="month">'.$boldon.$italicon.$month.$italicoff.$boldoff.'</span>'.$year.'</div>';
	}

function get_event_summary() {
	global $post;
	$event = event_get_stored_options();
	$custom = get_post_custom();
	$output = '
	<h2><a href="' . get_permalink() . '">' . $post->post_title . '</a></h2>';
	foreach (explode( ',',$event['sort']) as $name)
		if ($event['summary'][$name] == 'checked') {
			$output .= build_event($name,$event,$custom);
			}
	$output .= '<p><a href="' . get_permalink() . '">' . $event['read_more'] . '</a></p></div>';
	return $output;
	}

function get_event_details() {
	global $post;
	$event = event_get_stored_options();
	$custom = get_post_custom();
	if ($event['calender_size'] == 'small') $width = 'style="margin-left:60px"';
	if ($event['calender_size'] == 'medium') $width = 'style="margin-left:80px"';
	if ($event['calender_size'] == 'large') $width = 'style="margin-left:100px"';
	$output = '<div class="qem-details" ' . $width . '>' . get_event_map() .
	$output .= '<div>';
	foreach (explode( ',',$event['sort']) as $name)
		if ($event['active_buttons'][$name]) {
			$output .= build_event($name,$event,$custom);
			}
			$output .= '</div>';
			return $output;
	}

function build_event ($name,$event,$custom) {
	$style = '';
	if ($event['bold'][$name] == 'checked') $style .= 'font-weight: bold; ';
	if ($event['italic'][$name] == 'checked') $style .= 'font-style: italic; ';
	if (!empty($event['colour'][$name])) $style .= 'color: '. $event['colour'][$name] . '; ';
	if (!empty($event['size'][$name])) $style .= 'font-size: ' . $event['size'][$name] . '%; ';
	if (!empty ($style)) $style = 'style="' . $style . '" ';
		switch ( $name ) {
			case 'field1':
					if (!empty($event['description_label'])) $caption = $event['description_label'].' ';
					if (!empty ( $custom['event_desc'][0] )) $output .= '<p ' . $style . '>' . $caption . $custom['event_desc'][0] . '</p>';
					break;
			case 'field2':
					if (!empty ( $custom['event_start'][0] )) {
						$output .= '<p ' . $style . '>' . $event['start_label'] . ' ' . $custom['event_start'][0];
						if ( !empty ( $custom['event_finish'][0] )) $output .= ' ' . $event['finish_label'] . ' ' . $custom['event_finish'][0];
					 	$output .= '</p>';
						}
					break;
				case 'field3':
					if (!empty($event['location_label'])) $caption = $event['location_label'].' ';
					if (!empty ( $custom['event_location'][0] )) $output .= '<p ' . $style . '>' . $caption . $custom['event_location'][0]  . '</p>';
					break;
				case 'field4':
					if (!empty($event['address_label'])) $caption = $event['address_label'].' ';
					if (!empty ( $custom['event_address'][0] )) $output .= '<p ' . $style . '>' . $caption . $custom['event_address'][0]  . '</p>';
					break;
				case 'field5':
					if (!empty($event['url_label'])) $caption = $event['url_label'].' ';
					if (!preg_match("~^(?:f|ht)tps?://~i", $custom['event_link'][0])) $url = 'http://' . $custom['event_link'][0]; else  $url = $custom['event_link'][0];
					if (!empty ( $custom['event_link'][0] )) $output .= '<p ' . $style . '>' . $caption .  '<a href="' . $url . '">' . $custom['event_link'][0]  . '</a></p>';
					break;
				case 'field6':
					if (!empty($event['cost_label'])) $caption = $event['cost_label'].' ';
					if (!empty ( $custom['event_cost'][0] )) $output .= '<p ' . $style . '>' . $caption . $custom['event_cost'][0]  . '</p>';
					break;
				}
				return $output;
				}


function get_event_content($content) {
	global $post;
    if (is_singular ('event') ) {
		$content = '<div class="event">' . 
		get_event_calendar_icon() . 
		get_event_details() . $content .
		'</div></div><div style="clear:both"></div>';
    	}
	return $content;
	}

function get_event_map() {
	global $post;
	$event = event_get_stored_options();
	$custom = get_post_custom();
	if (($event['show_map'] == 'checked') && (!empty($custom['event_address'][0])))
		{
		$map = str_replace(' ' ,'+',$custom['event_address'][0]);
		$geocode=file_get_contents('http://maps.google.com/maps/geo?output=json&q=' . $map);
		$output= json_decode($geocode);
		$lat = $output->Placemark[0]->Point->coordinates[1];
		$long = $output->Placemark[0]->Point->coordinates[0];
		$mapurl .= '
		<div style="float:right; margin: 0 0 10px 10px;">
		<a href="http://maps.google.co.uk/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q=' . $map . '&amp;t=m">
		<img src="http://maps.googleapis.com/maps/api/staticmap?center=' . $map . '&size=' . $event['map_width'] . 'x' . $event['map_height'] . '&markers=color:blue%7C' . $lat . ',' . $long . '&sensor=false" />
		</a>
		</div>';
		}
	return $mapurl;
	}

function query_post_type($query) {
  if(is_category() || is_tag()) {
    $post_type = get_query_var('post_type');
	if($post_type)
	    $post_type = $post_type;
	else
	    $post_type = array('nav_menu_item','post','event');
    $query->set('post_type',$post_type);
	return $query;
    }
}

class qem_widget extends WP_Widget {
	function qem_widget() {
		$widget_ops = array('classname' => 'qem_widget', 'description' => 'Add Quick Events to your sidebar');
		$this->WP_Widget('qem_widget', 'Quick Events', $widget_ops);
		}

	function form($instance) {
		echo '<p>All options for the quick events manager are changed on the plugin <a href="'.get_admin_url().'options-general.php?page=quick-event-manager/quick-event-manager.php">Settings</a> page.</p>';
		}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['email'] = $new_instance['email'];
		return $instance;
		}
 
	function widget($args, $instance) {
 	   	extract($args, EXTR_SKIP);
		echo event_shortcode('');
		}
	}

add_action( 'widgets_init', create_function('', 'return register_widget("qem_widget");') );

function event_get_stored_options () {
	$event = get_option('event_settings');
	if(!is_array($event)) $event = array();
	$option_default = event_get_default_options();
	$event = array_merge($option_default, $event);
	return $event;
	}

function event_get_default_options () {
	$event = array();
	$event['active_buttons'] = array( 'field1'=>'on' , 'field2'=>'on' , 'field3'=>'on' , 'field4'=>'on' , 'field5'=>'on' , 'field6'=>'on');	

	$event['summary'] = array('field1'=>'checked' , 'field2'=>'checked' , 'field3'=>'checked' , 'field4'=>'' , 'field5'=>'' , 'field6'=>'');
	$event['label'] = array( 'field1'=>'Event Description' , 'field2'=>'Event Time' , 'field3'=>'Location' , 'field4'=>'Address' ,  'field5'=>'Event Website' , 'field6'=>'Cost' );
	$event['sort'] = implode(',',array('field1', 'field2' , 'field3' , 'field4' , 'field5' , 'field6'));
	$event['bold'] = array('field1'=>'' , 'field2'=>'checked' , 'field3'=>'' , 'field4'=>'' , 'field5'=>'' , 'field6'=>'');
	$event['italic'] = array('field1'=>'' , 'field2'=>'' , 'field3'=>'' , 'field4'=>'checked' , 'field5'=>'' , 'field6'=>'');
	$event['colour'] = array('field1'=>'' , 'field2'=>'#343838' , 'field3'=>'' , 'field4'=>'' , 'field5'=>'' , 'field6'=>'#008C9E');
	$event['size'] = array('field1'=>'110' , 'field2'=>'120' , 'field3'=>'' , 'field4'=>'' , 'field5'=>'' , 'field6'=>'120');
	$event['address_label'] = '';
	$event['url_label'] = '';
	$event['description_label'] = '';
	$event['cost_label'] = '';
	$event['start_label'] = 'From';
	$event['finish_label'] = 'until';
	$event['location_label'] = 'At';
	$event['show_map'] = '';
	$event['dateformat'] = 'world';
	$event['read_more'] = 'Find out more...';
	$event['address_style'] = 'italic';
	$event['website_link'] = 'checked';
	$event['date_background'] = 'grey';
	$event['background_hex'] = '#FFF';
	$event['event_order'] = 'newest';
	$event['event_archive'] = '';
	$event['calender_size'] = 'medium';
	$event['map_width'] = '200';
	$event['map_height'] = '200';
	$event['date_bold'] = '';
	$event['date_italic'] = 'checked';
	return $event;
	}

add_action('admin_menu', 'event_page_init');
add_filter('the_content', 'get_event_content');

