<?php

add_action('init', 'qem_init');
add_action("admin_menu","event_page_init");
add_action("save_post", "save_event_details");
add_action("admin_notices","qem_admin_notice");
add_action("add_meta_boxes","action_add_meta_boxes", 0 );
add_action("manage_posts_custom_column","event_custom_columns");
add_filter("manage_event_posts_columns","event_edit_columns");
add_filter("manage_edit-event_sortable_columns","event_date_column_register_sortable");
add_filter("request","event_date_column_orderby");

/* register_deactivation_hook( __FILE__, 'event_delete_options' ); */
register_uninstall_hook(__FILE__, 'event_delete_options');

function qem_init() {
	wp_enqueue_script('jquery-ui-sortable');
	return;
	}
function event_delete_options() {
	delete_option('event_settings');
	delete_option('qem_display');
	delete_option('qem_style');
	delete_option('qem_upgrade');
	}
function event_page_init() {
	add_options_page('Quick Event Manager', 'Quick Event Manager', 'manage_options', __FILE__, 'qem_tabbed_page');
	}
function qem_admin_notice($message) {
	if (!empty( $message)) echo '<div class="updated"><p>'.$message.'</p></div>';
	}
function qem_tabbed_page() {
	qem_use_custom_css();
	echo '<div class="wrap">';
	echo '<h1>Quick Event Manager</h1>';
	if ( isset ($_GET['tab'])) {qem_admin_tabs($_GET['tab']); $tab = $_GET['tab'];} else {qem_admin_tabs('setup'); $tab = 'setup';}
	switch ($tab) {
		case 'setup' : qem_setup(); break;
		case 'settings' : qem_event_settings(); break;
		case 'display' : qem_display_page(); break;
		case 'styles' : qem_styles(); break;
		case 'help' : qem_help (); break;
		}
	echo '</div>';
	}
function qem_admin_tabs($current = 'settings') { 
	$tabs = array( 'setup' => 'Setup' , 'settings' => 'Event Settings', 'display' => 'Event Display', 'styles' => 'Styling' ); 
	$links = array();  
	echo '<div id="icon-themes" class="icon32"><br></div>';
	echo '<h2 class="nav-tab-wrapper">';
	foreach( $tabs as $tab => $name ) {
		$class = ( $tab == $current ) ? ' nav-tab-active' : '';
		echo "<a class='nav-tab$class' href='?page=quick-event-manager/settings.php&tab=$tab'>$name</a>";
		}
	echo '</h2>';
	}
function qem_setup() {
	$content = '<div class="wrap">
	<div id ="qem-style">
	<h2>Setting up and using the plugin</h2>
	<p><span style="color:red; font-weight:bold;">Important!</span> This plugin uses custom posts. For it to work properly you have to resave your <a href="'.get_admin_url().'options-permalink.php">permalinks</a>. This is not a bug, it&#146;s how wordpress works. If you don&#146;t resave your permalinks you will get a page not found on your events.</p>
	<p>Create new events using the <a href="'.get_admin_url().'edit.php?post_type=event">Events</a> link on your dashboard menu.</p>
	<p>To add an event list to your posts or pages use the shortcode: <code>[qem]</code>. To just display past events use the shortcode: <code>[qem id="archive"]</code>.</p>
	<p>That\'s pretty much it. All you need to do now is create some events.</p>
	<h2>Plugin Options</h2>
	<p>To change what is displayed use the <a href="?page=quick-event-manager/settings.php&tab=settings">Event Settings</a> pages</p>
	<p>To change how events are displayed use the <a href="?page=quick-event-manager/settings.php&tab=display">Event Display</a> page.</p>
	<p>To change the overall look of the events use the <a href="?page=quick-event-manager/settings.php&tab=styles">Styles</a> page.</p>
	<p>There is some development info on <a href="http://quick-plugins.com/quick-paypal-payments/" target="_blank">my plugin page</a> along with a feedback form. Or you can email me at <a href="mailto:mail@quick-plugins.com">mail@quick-plugins.com</a>.</p>
		
	</div></div>';
	echo $content;
	}
function qem_event_settings() {
	$active_buttons = array( 'field1' , 'field2' , 'field3' , 'field4' , 'field5' , 'field6' ,);	
	if( isset( $_POST['Submit'])) {
		foreach ( $active_buttons as $item) {
			$event['active_buttons'][$item] = (isset( $_POST['event_settings_active_'.$item]) and $_POST['event_settings_active_'.$item] == 'on' ) ? true : false;
			$event['summary'][$item] = (isset( $_POST['summary_'.$item]) );
			$event['bold'][$item] = (isset( $_POST['bold_'.$item]) );
			$event['italic'][$item] = (isset( $_POST['italic_'.$item]) );
			$event['colour'][$item] = $_POST['colour_'.$item];
			$event['size'][$item] = $_POST['size_'.$item];
			if (!empty ( $_POST['label_'.$item])) $event['label'][$item] = $_POST['label_'.$item];
			}
		$option = array('sort','description_label','address_label','url_label','cost_label','start_label','finish_label','location_label','show_map','read_more','noevent','dateformat','address_style','website_link','date_background','background_hex','event_order','event_archive','event_descending','calender_size','map_width','map_height','date_bold','date_italic','styles','custom','number_of_posts');
		foreach ($option as $item)$event[$item] = $_POST[$item];
		update_option( 'event_settings', $event);
		qem_admin_notice("The form settings have been updated.");
		}
	$event = event_get_stored_options();
	$$event['dateformat'] = 'checked'; 
	$$event['date_background'] = 'checked'; 
	$$event['event_order'] = 'checked'; 
	$$event['calender_size'] = 'checked'; 
	if ( $event['event_archive'] == "checked") $archive = "checked"; 
	if ($event['show_map'] == 'checked') $map = 'checked';
	$content = '<div class="wrap">
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
	<p>Use the check boxes to select which fields to display in the event post and the event list. Drag and drop to change the order of the fields.<br>
	The fields with the blue border are for optional captions. For example: <span style="color:blue">The cost is</span> {cost} will display as <em>The cost is 20 Zlotys</em>. If you leave it blank just <em>20 Zlotys</em> will display.</p>
	<p><b><div style="float:left; margin-left:7px;width:11em;">Show in event post</div><div style="float:left; width:6em;">Show in<br>event list</div><div style="float:left; width:9em;">Colour</div><div style="float:left; width:5em;">Font<br>size</div><div style="float:left; width:8em;">Font<br>attributes</div><div style="float:left; width:28em;">Caption and display options:</div></b></p>
	<div style="clear:left"></div>
	<ul id="qem_sort">';
	$sort = explode(",", $event['sort']); 
	foreach (explode( ',',$event['sort']) as $name) {
		$checked = ( $event['active_buttons'][$name]) ? 'checked' : '';
		$summary = ( $event['summary'][$name]) ? 'checked' : '';
		$bold = ( $event['bold'][$name]) ? 'checked' : '';
		$italic = ( $event['italic'][$name]) ? 'checked' : '';
		$options = '';
		switch ( $name ) {
			case 'field1':
				$options = '<input type="text" style="border:1px solid blue; width:10em; padding: 1px; margin:0;" name="description_label" . value ="' . $event['description_label'] . '" /> {descriptiion}';
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
	}
	$content .='</ul>
	<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /></p>
	<input type="hidden" id="qem_settings_sort" name="sort" value="'.$event['sort'].'" />
	</form>
	</div></div>';
	echo $content;
	}
function qem_display_page() {
	if( isset( $_POST['Submit'])) {
		$option = array('read_more','noevent','event_archive','event_descending','map_width','map_height');
		foreach ($option as $item) $display[$item] = $_POST[$item];
		update_option('qem_display', $display);
		qem_admin_notice("The display settings have been updated.");
		}
	if( isset( $_POST['Reset'])) {
		delete_option('qem_display');
		qem_admin_notice("The display settings have been reset.");
		}
	$display = event_get_stored_display();
	$$display['event_order'] = 'checked'; 
	if ( $display['event_archive'] == "checked") $archive = "checked"; 
	$content = '<div class="wrap">
	<div id ="qem-style">
		<form id="event_settings_form" method="post" action="">';	
	$content .= '
		<h2>Event Messages</h2>
		<p>Read more caption: <input type="text" style="width:20em;border:1px solid #415063;" label="read_more" name="read_more" value="' . $display['read_more'] . '" /></p>
		<p>No events message: <input type="text" style="width:20em;border:1px solid #415063;" label="noevent" name="noevent" value="' . $display['noevent'] . '" /></p>
		<h2>Event List Options</h2>
		<p><input type="checkbox" style="border: none; padding: 0; margin:0;" name="event_descending" value="checked" ' . $display['event_descending'] . ' /> List events in reverse order (from future to past)<br>
		<input type="checkbox" style="border: none; padding: 0; margin:0;" name="event_archive" value="checked" ' . $display['event_archive'] . ' /> Show past events in the events list</p>
		<p>If you only want to display past events use the shortcode: <code>[qem id="archive"]</code>.</p>
		<h2>Map Size</h2>
		<p>Width: <input type="text" style="border:1px solid #415063; width:3em; padding: 1px; margin:0;" name="map_width" . value ="' . $display['map_width'] . '" /> px&nbsp;&nbsp;Height: <input type="text" style="border:1px solid #415063; width:3em; padding: 1px; margin:0;" name="map_height" . value ="' . $display['map_height'] . '" /> px</p>
		<p>Note: the map will only display if you have a valid address and the &#146;show map&#146; checkbox is ticked.</p>
		<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the display settings?\' );"/></p>
		</form>
		</div></div>';
	echo $content;
	}
function qem_styles() {
	if( isset( $_POST['Submit'])) {
		$options = array( 'font','font-family','font-size','width','widthtype','event_background','event_backgroundhex','date_colour','date_background','date_backgroundhex','use_custom','custom','date_bold','date_italic','date_border_width','date_border_colour','calender_size','event_border');
		foreach ( $options as $item) $style[$item] = stripslashes($_POST[$item]);
		update_option('qem_style', $style);
		qem_admin_notice("The form styles have been updated.");
		}
	if( isset( $_POST['Reset'])) {
		delete_option('qem_style');
		qem_admin_notice("The style settings have been reset.");
		}
	$style = qem_get_stored_style();
	$$style['font'] = 'checked';
	$$style['widthtype'] = 'checked';
	$$style['background'] = 'checked';
	$$style['event_background'] = 'checked';
	$$style['date_background'] = 'checked'; 
	$$style['calender_size'] = 'checked'; 
	qem_use_custom_css();
	$content = '<div class="wrap">
	<div id ="qem-style">
	<form method="post" action="">
	<div style="float:left; width:200px; margin-right: 10px"> 
	<h2>Event Width</h2>
	<p>
	<input style="margin:0; padding:0; border:none;" type="radio" name="widthtype" value="percent" ' . $percent . ' /> 100% (fill the available space)<br />
	<input style="margin:0; padding:0; border:none;" type="radio" name="widthtype" value="pixel" ' . $pixel . ' /> Pixel (fixed)</p>
	<p>Enter the width in pixels: <input type="text" style="width:4em;border:1px solid #415063;" label="width" name="width" value="' . $style['width'] . '" /> (Just enter the value, no need to add \'px\').</p>
	</div>
	<div style="float:left; width:200px; margin-right: 10px">
	<h2>Font Options</h2>
	<p><input style="margin:0; padding:0; border:none" type="radio" name="font" value="theme" ' . $theme . ' /> Use your theme font styles<br />
	<input style="margin:0; padding:0; border:none" type="radio" name="font" value="plugin" ' . $plugin . ' /> Use Plugin font styles (enter font family and size below)</p>
	<p>Font Family: <input type="text" style="width:15em;border:1px solid #415063;" label="font-family" name="font-family" value="' . $style['font-family'] . '" /></p>
	<p>Font Size: <input type="text" style="width:7em;border:1px solid #415063;" label="font-size" name="font-size" value="' . $style['font-size'] . '" /></p>
</div>
	<div style="clear:left"></div>
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
	<h3>Border Thickeness</h3>
	<p><input type="text" style="width:7em;border:1px solid #415063;" label="calendar border" name="date_border_width" value="' . $style['date_border_width'] . '" /></p>
	<h3>Border Colour</h3>
	<p><input type="text" style="width:150px;border:1px solid #415063;" label="calendar border" name="date_border_colour" value="' . $style['date_border_colour'] . '" /></p>
	</div>
	<div style="float:left; width:200px; margin-right: 10px">
	<h3>Date Background colour</h3>
	<p>
	<input style="margin: 0; padding: 0; border: none;" type="radio" name="date_background" value="grey" ' . $grey . ' /> Grey<br />
	<input style="margin: 0; padding: 0; border: none;" type="radio" name="date_background" value="red" ' . $red . ' /> Red<br />
	<input style="margin: 0; padding: 0; border: none;" type="radio" name="date_background" value="color" ' . $color . ' /> Set your own (enter HEX code or color name below)</p>
	<p><input type="text" style="width:7em;border:1px solid #415063;" label="background" name="date_backgroundhex" value="' . $style['date_backgroundhex'] . '" /></p>
	<h3>Date Text Colour</h3>
	<p><input type="text" style="width:7em;border:1px solid #415063;" label="date colour" name="date_colour" value="' . $style['date_colour'] . '" /></p>
	</div>
	<div style="float:left; width:150px; margin-right: 10px">
	<h3>Month Text Style</h3>
	<p><input style="margin: 0; padding: 0; border: none;" type="checkbox" name="date_bold" value="checked" ' . $style['date_bold'] . ' /> Bold<br />
	<input style="margin: 0; padding: 0; border: none;" type="checkbox" name="date_italic" value="checked" ' . $style['date_italic'] . ' /> Italic</p>
	</div>
	<div style="float:left; width:200px; margin-right: 10px">
	<h3>Calendar Icon Preview</h3>';
	$content .= get_event_calendar_icon();
	$content .= '</div>
	<div style="clear:left"></div>
	<h2>Event Content</h2>
	<div style="float:left; width:200px; margin-right: 10px">
	<h3>Event Border</h3>
	<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="event_border"' . $style['event_border'] . ' value="checked" /> Add a border to the event post</p>
	<p>Thickness and colour will be the same as the calendar icon</p>
	</div>
	<div style="float:left; width:200px; margin-right: 10px">
	<h3>Event Background colour</h3>
	<p><input style="margin:0; padding:0; border:none;" type="radio" name="event_background" value="bgwhite" ' . $bgwhite . ' /> White<br />
	<input style="margin:0; padding:0; border:none;" type="radio" name="event_background" value="bgtheme" ' . $bgtheme . ' /> Use theme colours<br />
	<input style="margin:0; padding:0; border:none;" type="radio" name="event_background" value="bgcolor" ' . $bgcolor . ' /> Set your own (enter HEX code or color name below)</p>
	<p><input type="text" style="width:7em;border:1px solid #415063;" label="background" name="event_backgroundhex" value="' . $style['event_backgroundhex'] . '" /></p>
	</div>
	<div style="clear:left;"></div>
	<h2>Custom CSS</h2>
	<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="use_custom"' . $style['use_custom'] . ' value="checked" /> Use Custom CSS</p>
	<p><textarea style="width:100%;height:100px;border:1px solid #415063;" name="custom">' . $style['custom'] . '</textarea></p>
	<p>To see all the styling use the <a href="'.get_admin_url().'plugin-editor.php?file=quick-event-manager/quick-event-manager-style.css">CSS editor</a>.</p>
	<p>The main style wrapper is the <code>.qem</code> class.</p>
	<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the style settings?\' );"/></p>
	</form>
	</div>';
	echo $content;
	}
function event_custom_columns($column) {
	global $post;
	$custom = get_post_custom();
	switch ($column) {
		case "event_date":
			$date = $custom["event_date"][0];
			echo date("d", $date).' '.date("M", $date).' '.date("Y", $date);
			break;
		case "event_time":
			echo $custom["event_start"][0] . ' - ' . $custom["event_finish"][0];
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
		$vars = array_merge( $vars, array('meta_key' => 'event_date','orderby' => 'meta_value_num') );
		}
	return $vars;
	}
function event_edit_columns($columns) {
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
function event_details_meta() {

	global $post;
	$event = event_get_stored_options();
	$date = get_event_field('event_date');
	if (empty($date)) $date = time();
	$date = date_i18n("d M Y", $date);
	$output = '
	<p><em>Empty fields are not displayed. See the plugin <a href="'.get_admin_url().'options-general.php?page=quick-event-manager/settings.php">settings</a> page for options.</em></p>
	<p><label>Date: </label>
	<input type="text" style="border:1px solid #415063;" id="qemdate" name="event_date" value="' . $date . '" /> <em>(Errors will reset to today&#146;s date.)</em>.</p>
	<script type="text/javascript">jQuery(document).ready(function() {jQuery(\'#qemdate\').datepicker({dateFormat : \'dd M yy\'});});</script>
	<p><label>Short Description: </label><input type="text" style="border:1px solid #415063;" size="100" name="event_desc" value="' . get_event_field("event_desc") . '" /></p>
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
	if(isset($_POST["event_date"])) $setdate = $_POST["event_date"];
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
function action_add_meta_boxes() {
	add_meta_box('event_sectionid','Event Details','event_details_meta','event', 'normal', 'high');
	global $_wp_post_type_features;
	if (isset($_wp_post_type_features['event']['editor']) && $_wp_post_type_features['event']['editor']) {
		unset($_wp_post_type_features['event']['editor']);
		add_meta_box('description_section',	__('Event Description'),'inner_custom_box','event', 'normal', 'high');
		}
	}
function inner_custom_box( $post ) {
	the_editor($post->post_content);
	}