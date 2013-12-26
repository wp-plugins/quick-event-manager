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
add_action( 'admin_enqueue_scripts', 'qem_admin_pointers_header' );

/* register_deactivation_hook( __FILE__, 'event_delete_options' ); */
register_uninstall_hook(__FILE__, 'event_delete_options');

function qem_init() {
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_style('qem_settings',plugins_url('settings.css', __FILE__));
	return;
	}
function event_delete_options() {
	delete_option('event_settings');
	delete_option('qem_display');
	delete_option('qem_style');
	delete_option('qem_upgrade');
}
function event_page_init() {
	add_options_page( __('Event Manager', 'quick-event-manager'), __('Event Manager', 'quick-event-manager'), 'manage_options', __FILE__, 'qem_tabbed_page');
}

function qem_admin_notice($message) {
	if (!empty( $message)) echo '<div class="updated"><p>'.$message.'</p></div>';
}

function qem_tabbed_page() {
	echo '<div class="wrap">';
	echo '<h1>Quick Event Manager</h1>';
	if ( isset ($_GET['tab'])) {
		qem_admin_tabs($_GET['tab']); 
		$tab = $_GET['tab'];
		} else {
			qem_admin_tabs('setup'); $tab = 'setup';
			}
		switch ($tab) {
			case 'setup' : qem_setup(); break;
			case 'settings' : qem_event_settings(); break;
			case 'display' : qem_display_page(); break;
			case 'calendar' : qem_calendar(); break;
			case 'styles' : qem_styles(); break;
			case 'register' : qem_register(); break;
			case 'help' : qem_help (); break;
			}
		echo '</div>';
}

function qem_admin_tabs($current = 'settings') { 
	$tabs = array( 
	'setup' 	=> __('Setup', 'quick-event-manager'), 
	'settings'  => __('Event Settings', 'quick-event-manager'), 
	'display'   => __('Event Display', 'quick-event-manager'), 
	'styles'    => __('Event Styling', 'quick-event-manager'),
	'calendar'  => __('Calendar Options', 'quick-event-manager'),
	'register'  => __('Event Registration', 'quick-event-manager'),
	);
	
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
	$content = '<div class="qem-settings"><div class="qem-options" style="margin-right:10px">
	<h2>'.__('Setting up and using the plugin', 'quick-event-manager').'</h2>
	<p><span style="color:red; font-weight:bold;">'. __('Important!', 'quick-event-manager').'</span> '.__('This plugin uses custom posts. For it to work properly you have to resave your <a href="options-permalink.php">permalinks</a>. This is not a bug, it&#146;s how wordpress works. If you don&#146;t resave your permalinks you will get a <em>page not found</em> message on your events.', 'quick-event-manager').'</p>
	<p>'.__('Create new events using the <a href="edit.php?post_type=event">Events</a> link on your dashboard menu.', 'quick-event-manager').'</p>
	<p>'.__('To display a list of events on your posts or pages use the shortcode: <code>[qem]</code>.', 'quick-event-manager').'</p>
	<p>'.__('If you prefer to display your events as a calendar use the shortcode: <code>[qemcalendar]</code>.', 'quick-event-manager').'</p>
	<p>'.__('More shortcodes on the right.', 'quick-event-manager').'</p>
	<p>'.__('That&#39;s pretty much it. All you need to do now is <a href="edit.php?post_type=event">create some events</a>.', 'quick-event-manager').'</p>
	<p>'.__('Help at <a href="http://quick-plugins.com/quick-event-manager/" target="_blank">quick-plugins.com</a> along with a feedback form. Or you can email me at ', 'quick-event-manager').'<a href="mailto:mail@quick-plugins.com">mail@quick-plugins.com</a>.</p>
	</div>
	<div class="qem-options" style="float:right">
	<h2>'.__('Display Settings', 'quick-event-manager').'</h2>
	<h3>'.__('Event Settings', 'quick-event-manager').'</h3>
	<p>'.__('Select which fields are displayed in the event list and event page. Change actions and style of each field', 'quick-event-manager').'</p>
	<h3>'.__('Event Display', 'quick-event-manager').'</h3>
	<p>'.__('Edit event messages and display options', 'quick-event-manager').'</p>
	<h3>'.__('Event Styling', 'quick-event-manager').'</h3>
	<p>'.__('Styling options for the date icon and overall event layout', 'quick-event-manager').'</p>
	<h3>'.__('Calendar Options', 'quick-event-manager').'</h3>
	<p>'.__('Show events as a calendar. Some styling and display options.', 'quick-event-manager').'</p>
	<h3>'.__('Event Registration', 'quick-event-manager').'</h3>
	<p>'.__('Add a simple registration form to your events', 'quick-event-manager').'</p>
	<h2>'.__('All the Shortcodes', 'quick-event-manager').'</h2>
	<table>
	<tbody>
	<tr><td>[qem]</td><td>'.__('Standard event list', 'quick-event-manager').'</td></tr>
	<tr><td>[qemcalendar]</td><td>'.__('Calendar view', 'quick-event-manager').'</td></tr>
	<tr><td>[qem links=\'off\']</td><td>'.__('Removes links from Event title and hides the \'read more\' link', 'quick-event-manager').'</td></tr>
	<tr><td>[qem posts=\'99\']</td><td>'.__('Set the number of events to display', 'quick-event-manager').'</td></tr>
	<tr><td>[qem id=\'archive\']</td><td>'.__('Show old events', 'quick-event-manager').'</td></tr>
	<tr><td>[qem fullevent=\'on\']</td><td>'.__('Show full event details on the list', 'quick-event-manager').'</td></tr>
	<tr><td>[qem category=\'name\']</td><td>'.__('List events by category', 'quick-event-manager').'</td></tr>
	</tbody>
	</table>';
	$content .= qemdonate_loop();
	$content .= '</div></div>';
	echo $content;
}
function qem_event_settings() {
	$active_buttons = array( 
		'field1',
		'field2',
		'field3',
		'field4',
		'field5',
		'field6'
		);	
	 
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
		qem_admin_notice(__('The form settings have been updated', 'quick-event-manager'));
	}
		
	$event = event_get_stored_options();
	$$event['dateformat'] = 'checked'; 
	$$event['date_background'] = 'checked'; 
	$$event['event_order'] = 'checked'; 
	$$style['calender_size'] = 'checked'; 
	
	if ( $event['event_archive'] == "checked") $archive = "checked"; 
	if ($event['show_map'] == 'checked') $map = 'checked';
	$content = '
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
		<div class ="qem-options" style="width:98%">
		<form id="event_settings_form" method="post" action="">
		<p>'.__('Use the check boxes to select which fields to display in the event post and the event list.', 'quick-event-manager').'</p>
		<p>'.__('Drag and drop to change the order of the fields.', 'quick-event-manager').'</p>
		<p>'.__('The fields with the blue border are for optional captions. For example: <span style="border:1px solid blue;">The cost is</span> {cost} will display as <em>The cost is 20 Zlotys</em>. If you leave it blank just <em>20 Zlotys</em> will display.', 'quick-event-manager').'</p>
		<p><b><div style="float:left; margin-left:7px;width:11em;">'.__('Show in event post', 'quick-event-manager').'</div>
		<div style="float:left; width:6em;">'.__('Show in<br>event list', 'quick-event-manager').'</div>
		<div style="float:left; width:9em;">'.__('Colour', 'quick-event-manager').'</div>
		<div style="float:left; width:5em;">'.__('Font<br>size', 'quick-event-manager').'</div>
		<div style="float:left; width:8em;">'.__('Font<br>attributes', 'quick-event-manager').'</div>
		<div style="float:left; width:28em;">'.__('Caption and display options:', 'quick-event-manager').'</div></b></p>
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
				$options = '<input type="text" style="border:1px solid blue; width:10em; padding: 1px; margin:0;" name="description_label" . value ="' . $event['description_label'] . '" /> {'.__('description', 'quick-event-manager').'}';
				break;
			case 'field2':
				$options = '<input type="text" style="border:1px solid blue; width:6em; padding: 1px; margin:0;" name="start_label" . value ="' . $event['start_label'] . '" /> {'.__('start time', 'quick-event-manager').'} <input type="text" style="border:1px solid blue; width:6em; padding: 1px; margin:0;" name="finish_label" . value ="' . $event['finish_label'] . '" /> {'.__('end time', 'quick-event-manager').'}';
				break;
			case 'field3':
				$options = '<input type="text" style="border:1px solid blue; width:6em; padding: 1px; margin:0;" name="location_label" . value ="' . $event['location_label'] . '" /> {'.__('location', 'quick-event-manager').'}';
				break;
			case 'field4':
				$options = '<input type="text" style="border:1px solid blue; width:10em; padding: 1px; margin:0;" name="address_label" . value ="' . $event['address_label'] . '" /> {'.__('address', 'quick-event-manager').'}&nbsp;&nbsp;<input type="checkbox" style="margin: 0; padding: 0; border: none;" name="show_map"' . $event['show_map'] . ' value="checked" /> '.__('Show map (if address is given)', 'quick-event-manager').' ';
				break;
			case 'field5':
				$options = '<input type="text" style="border:1px solid blue; width:10em; padding: 1px; margin:0;" name="url_label" . value ="' . $event['url_label'] . '" /> {url}&nbsp;&nbsp;&nbsp;<input type="checkbox" style="margin: 0; padding: 0; border: none;" name="website_link"' . $event['website_link'] . ' value="checked" /> '.__('Link to website', 'quick-event-manager');
				break;
			case 'field6':
				$options = '<input type="text" style="border:1px solid blue; width:10em; padding: 1px; margin:0;" name="cost_label" . value ="' . $event['cost_label'] . '" /> {'.__('cost', 'quick-event-manager').'}';
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
	<div style="clear:left"></div>
	</li>';
	}
	
	$content .='</ul>
	<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Save Changes', 'quick-event-manager').'" /></p>
	<input type="hidden" id="qem_settings_sort" name="sort" value="'.$event['sort'].'" />
	</form>
	</div>';
	
	echo $content;
}

function qem_display_page() {
	if( isset( $_POST['Submit'])) {
		$option = array('show_end_date','read_more','noevent','event_archive','event_descending','map_width','map_height');
			foreach ($option as $item) $display[$item] = $_POST[$item];
			update_option('qem_display', $display);		
			qem_admin_notice (__('The display settings have been updated.', 'quick-event-manager'));
	}		
	if( isset( $_POST['Reset'])) {
		delete_option('qem_display');
		qem_admin_notice (__('The display settings have been reset.', 'quick-event-manager')) ;
		}
	$display = event_get_stored_display();
	$$display['event_order'] = 'checked';
	$$display['show_end_date'] = 'checked';
	if ( $display['event_archive'] == "checked") $archive = "checked"; 
	$content = '<div class="qem-settings"><div class="qem-options" style="margin-right:10px">
		<form id="event_settings_form" method="post" action="">';	
	$content .= '
		<h2>'.__('End Date Display', 'quick-event-manager').'</h2>
		<p><input type="checkbox" style="border: none; padding: 0; margin:0;" name="show_end_date" value="checked" ' . $display['show_end_date'] . ' /> '.__('Show end date in event list (below start date)', 'quick-event-manager').'<br>
		<h2>'.__('Event Messages', 'quick-event-manager').'</h2>
		<p>'.__('Read more caption:', 'quick-event-manager').' <input type="text" style="width:20em;border:1px solid #415063;" label="read_more" name="read_more" value="' . $display['read_more'] . '" /></p>
		<p>'.__('No events message:', 'quick-event-manager').' <input type="text" style="width:20em;border:1px solid #415063;" label="noevent" name="noevent" value="' . $display['noevent'] . '" /></p>
		<h2>'.__('Event List Options', 'quick-event-manager').'</h2>
		<p><input type="checkbox" style="border: none; padding: 0; margin:0;" name="event_descending" value="checked" ' . $display['event_descending'] . ' /> '.__('List events in reverse order (from future to past)', 'quick-event-manager').'<br>
		<input type="checkbox" style="border: none; padding: 0; margin:0;" name="event_archive" value="checked" ' . $display['event_archive'] . ' /> '.__('Show past events in the events list', 'quick-event-manager').'</p>
		<p>'.__('If you only want to display past events use the shortcode: <code>[qem id="archive"]</code>.', 'quick-event-manager').'</p>
		<h2>'.__('Map Size', 'quick-event-manager').'</h2>
		<p>'.__('Width:', 'quick-event-manager').' <input type="text" style="border:1px solid #415063; width:3em; padding: 1px; margin:0;" name="map_width" . value ="' . $display['map_width'] . '" /> px&nbsp;&nbsp;'.__('Height:', 'quick-event-manager').' <input type="text" style="border:1px solid #415063; width:3em; padding: 1px; margin:0;" name="map_height" . value ="' . $display['map_height'] . '" /> px</p>
		<p>'.__('Note: the map will only display if you have a valid address and the &#146;show map&#146; checkbox is ticked.', 'quick-event-manager').'</p>
		<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Save Changes', 'quick-event-manager').'" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="'.__('Reset', 'quick-event-manager').'" onclick="return window.confirm( \' '.__('Are you sure you want to reset the display settings?', 'quick-event-manager').'\' );"/></p>
		</form>
		</div>
		<div class="qem-options" style="float:right">
<h2>'.__('Event List Preview', 'quick-event-manager').'</h2>';
	$atts = array('posts' => '3');
	$content .= event_shortcode($atts,'');
	$content .= '</div></div>';
	echo $content;
	}
	
function qem_styles() {
	if( isset( $_POST['Submit'])) {
		$options = array(
		'font','font-family','font-size','width','widthtype','event_background','event_backgroundhex','date_colour',
		'date_background','date_backgroundhex','use_custom','custom','date_bold','date_italic','date_border_width',
		'date_border_colour','calender_size','event_border');
		foreach ( $options as $item) $style[$item] = stripslashes($_POST[$item]);
		update_option('qem_style', $style);
		qem_options_css();
		qem_admin_notice (__('The form styles have been updated.', 'quick-event-manager'));
		}
	if( isset( $_POST['Reset'])) {
		delete_option('qem_style');
		qem_options_css();
		qem_admin_notice (__('The style settings have been reset.', 'quick-event-manager'));
		}	
	$style = qem_get_stored_style();
	$$style['font'] = 'checked';
	$$style['widthtype'] = 'checked';
	$$style['background'] = 'checked';
	$$style['event_background'] = 'checked';
	$$style['date_background'] = 'checked'; 
	$$style['calender_size'] = 'checked'; 
	$content = '<div class="qem-settings"><div class="qem-options" style="margin-right:10px">
	<form method="post" action="">
	<div style="float:left; width:48%; margin-right: 1%"> 
	<h2>'.__('Event Width', 'quick-event-manager').'</h2>
	<p>
	<input style="margin:0; padding:0; border:none;" type="radio" name="widthtype" value="percent" ' . $percent . ' /> '.__('100% (fill the available space)', 'quick-event-manager').'<br />
	<input style="margin:0; padding:0; border:none;" type="radio" name="widthtype" value="pixel" ' . $pixel . ' /> '.__('Pixel (fixed)', 'quick-event-manager').'</p>
	<p>'.__('Enter the width in pixels:', 'quick-event-manager').' <input type="text" style="width:4em;border:1px solid #415063;" label="width" name="width" value="' . $style['width'] . '" /> '.__('(Just enter the value, no need to add \'px\').', 'quick-event-manager').'</p>
	</div>
	<div style="float:left; width:48%; margin-right: 1%"> 
	<h2>'.__('Font Options', 'quick-event-manager').'</h2>
	<p><input style="margin:0; padding:0; border:none" type="radio" name="font" value="theme" ' . $theme . ' /> '.__('Use your theme font styles', 'quick-event-manager').'<br />
	<input style="margin:0; padding:0; border:none" type="radio" name="font" value="plugin" ' . $plugin . ' /> '.__('Use Plugin font styles (enter font family and size below)', 'quick-event-manager').'</p>
	<p>'.__('Font Family:', 'quick-event-manager').' <input type="text" style="width:15em;border:1px solid #415063;" label="font-family" name="font-family" value="' . $style['font-family'] . '" /></p>
	<p>'.__('Font Size:', 'quick-event-manager').' <input type="text" style="width:7em;border:1px solid #415063;" label="font-size" name="font-size" value="' . $style['font-size'] . '" /></p>
	</div>
	<div style="clear:left"></div>
	<h2>'.__('Calender Icon', 'quick-event-manager').'</h2>
	<div>
	<div style="float:left; width:48%; margin-right: 1%"> 
	<h3>'.__('Size', 'quick-event-manager').'</h3>
	<p>
	<input style="margin: 0; padding: 0; border: none;" type="radio" name="calender_size" value="small" ' . $small . ' /> '.__('Small', 'quick-event-manager').' (40px)<br />
	<input style="margin: 0; padding: 0; border: none;" type="radio" name="calender_size" value="medium" ' . $medium . ' /> '.__('Medium', 'quick-event-manager').' (60px)<br />
	<input style="margin: 0; padding: 0; border: none;" type="radio" name="calender_size" value="large" ' . $large . ' /> '.__('Large', 'quick-event-manager').'(80px)</p>
	</div>
	<div style="float:left; width:48%; margin-right: 1%"> 
	<h3>'.__('Border Thickeness', 'quick-event-manager').'</h3>
	<p><input type="text" style="width:7em;border:1px solid #415063;" label="calendar border" name="date_border_width" value="' . $style['date_border_width'] . '" /></p>
	<h3>'.__('Border Colour', 'quick-event-manager').'</h3>
	<p><input type="text" style="width:150px;border:1px solid #415063;" label="calendar border" name="date_border_colour" value="' . $style['date_border_colour'] . '" /></p>
	</div>
	<div style="float:left; width:48%; margin-right: 1%"> 
	<h3>'.__('Date Background colour', 'quick-event-manager').'</h3>
	<p>
	<input style="margin: 0; padding: 0; border: none;" type="radio" name="date_background" value="grey" ' . $grey . ' /> '.__('Grey', 'quick-event-manager').'<br />
	<input style="margin: 0; padding: 0; border: none;" type="radio" name="date_background" value="red" ' . $red . ' /> '.__('Red', 'quick-event-manager').'<br />
	<input style="margin: 0; padding: 0; border: none;" type="radio" name="date_background" value="color" ' . $color . ' /> '.__('Set your own (enter HEX code or color name below)', 'quick-event-manager').'</p>
	<p><input type="text" style="width:7em;border:1px solid #415063;" label="background" name="date_backgroundhex" value="' . $style['date_backgroundhex'] . '" /></p>
	</div>
	<div style="float:left; width:48%; margin-right: 1%"> 
	<h3>'.__('Date Text Colour', 'quick-event-manager').'</h3>
	<p><input type="text" style="width:7em;border:1px solid #415063;" label="date colour" name="date_colour" value="' . $style['date_colour'] . '" /></p>
	<h3>'.__('Month Text Style', 'quick-event-manager').'</h3>
	<p><input style="margin: 0; padding: 0; border: none;" type="checkbox" name="date_bold" value="checked" ' . $style['date_bold'] . ' /> '.__('Bold', 'quick-event-manager').'<br />
	<input style="margin: 0; padding: 0; border: none;" type="checkbox" name="date_italic" value="checked" ' . $style['date_italic'] . ' /> '.__('Italic', 'quick-event-manager').'</p>
	</div>
	<div style="clear:left"></div>
	<h2>'.__('Event Content', 'quick-event-manager').'</h2>
	<div style="float:left; width:48%; margin-right: 1%"> 
	<h3>'.__('Event Border', 'quick-event-manager').'</h3>
	<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="event_border"' . $style['event_border'] . ' value="checked" /> '.__('Add a border to the event post', 'quick-event-manager').'</p>
	<p>'.__('Thickness and colour will be the same as the calendar icon.', 'quick-event-manager').'</p>
	</div>
	<div style="float:left; width:48%; margin-right: 1%"> 
	<h3>'.__('Event Background colour', 'quick-event-manager').'</h3>
	<p><input style="margin:0; padding:0; border:none;" type="radio" name="event_background" value="bgwhite" ' . $bgwhite . ' /> '.__('White', 'quick-event-manager').'<br />
	<input style="margin:0; padding:0; border:none;" type="radio" name="event_background" value="bgtheme" ' . $bgtheme . ' /> '.__('Use theme colours', 'quick-event-manager').'<br />
	<input style="margin:0; padding:0; border:none;" type="radio" name="event_background" value="bgcolor" ' . $bgcolor . ' /> '.__('Set your own (enter HEX code or color name below)', 'quick-event-manager').'</p>
	<p><input type="text" style="width:7em;border:1px solid #415063;" label="background" name="event_backgroundhex" value="' . $style['event_backgroundhex'] . '" /></p>
	</div>
	<div style="clear:left;"></div>
	<h2>'.__('Custom CSS', 'quick-event-manager').'</h2>
	<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="use_custom"' . $style['use_custom'] . ' value="checked" /> '.__('Use Custom CSS', 'quick-event-manager').'</p>
	<p><textarea style="width:100%;height:100px;border:1px solid #415063;" name="custom">' . $style['custom'] . '</textarea></p>
	<p>'.__('To see all the styling use the <a href="plugin-editor.php?file=quick-event-manager/quick-event-manager-style.css">CSS editor</a>.', 'quick-event-manager').'</p>
	<p>'.__('The main style wrapper is the <code>.qem</code> class.', 'quick-event-manager').'</p>
	<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Save Changes', 'quick-event-manager').'" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="'.__('Reset', 'quick-event-manager').'" onclick="return window.confirm( \''.__('Are you sure you want to reset the style settings?', 'quick-event-manager').'\' );"/></p>
	</form>
	</div></div>
	<div class="qem-options" style="float:right">
	<h2>'.__('Event List Preview', 'quick-event-manager').'</h2>';
	$atts = array('posts' => '3');
	$content .= event_shortcode($atts,'');
	$content .= '</div></div>';
	
	echo $content;
}
function qem_calendar() {
	if( isset( $_POST['Submit'])) {
		$options = array('calday','day','eventday','oldday','eventhover','eventdaytext','eventlink','connect','calendar_text','calendar_url','eventlist_text','eventlist_url','startday',
		'cata','catatext','cataback','catb','catbtext','catbback','catc','catctext','catcback','catd','catdtext','catdback','cate','catetext','cateback','catf','catftext','catfback');
		foreach ( $options as $item) $calendar[$item] = stripslashes($_POST[$item]);
		update_option('qem_calendar', $calendar);
		qem_options_css();
		qem_admin_notice (__('The calendar settings have been updated.', 'quick-event-manager'));
		}
	if( isset( $_POST['Reset'])) {
		delete_option('qem_calendar');
		qem_admin_notice (__('The calendar settings have been reset.', 'quick-event-manager'));
	}
	$calendar = qem_get_stored_calendar();
	$$calendar['eventlink'] = 'checked';
	$$calendar['startday'] = 'checked';
	$content = '<div class="qem-settings"><div class="qem-options" style="margin-right:10px">
	<p>'.__('To add the calendar to your site use the shortcode: <code>[qemcalendar]</code>.', 'quick-event-manager').'</p>
	<form method="post" action="">
	<h2>'.__('Event Links', 'quick-event-manager').'</h2>
	<p><input style="margin:0; padding:0; border:none;" type="radio" name="eventlink" value="linkpopup" ' . $linkpopup . ' /> '.__('Link opens event summary in a popup', 'quick-event-manager').'<br />
	<input style="margin:0; padding:0; border:none;"
	type="radio" name="eventlink" value="linkpage" ' . $linkpage . ' /> '.__('Link opens event page' ,'quick-event-manager').'<br />
	<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="connect"' . $calendar['connect'] . ' value="checked" /> '.__('Link Event List to Calendar Page (you will need to create a page for the calendar).', 'quick-event-manager').'</p>
	<table width="100%">
	<tr><td width="30%">'.__('Calendar link text', 'quick-event-manager').'</td><td><input type="text" style="border:1px solid #415063;" label="calendar_text" name="calendar_text" value="' . $calendar['calendar_text'] . '" /></td></tr>
	<tr><td width="30%">'.__('Calendar page URL', 'quick-event-manager').'</td><td><input type="text" style="border:1px solid #415063;" label="calendar_url" name="calendar_url" value="' . $calendar['calendar_url'] . '" /></td></tr>
	<tr><td width="30%">'.__('Event list link text', 'quick-event-manager').'</td><td><input type="text" style="border:1px solid #415063;" label="eventlist_text" name="eventlist_text" value="' . $calendar['eventlist_text'] . '" /></td></tr>
	<tr><td width="30%">'.__('Event list page', 'quick-event-manager').' URL</td><td><input type="text" style="border:1px solid #415063;" label="eventlist_url" name="eventlist_url" value="' . $calendar['eventlist_url'] . '" /></td></tr>
	</table>
	<h2>'.__('Calendar Colours', 'quick-event-manager').'</h2>
	<table width="100%">
	<tr><th width="30%">'.__('Items', 'quick-event-manager').'</th><th>'.__('Background', 'quick-event-manager').'</th><th>'.__('Text', 'quick-event-manager').'<th></tr>
	<tr><td width="30%">'.__('Days of the Week', 'quick-event-manager').'</td><td><input type="text" style="border:1px solid #415063;" label="background" name="calday" value="' . $calendar['calday'] . '" /></td>
	<td></td></tr>
	<tr><td width="30%">'.__('Normal Date', 'quick-event-manager').'</td><td><input type="text" style="border:1px solid #415063;" label="background" name="day" value="' . $calendar['day'] . '" /></td><td></td></tr>
	<tr><td width="30%">'.__('Event Date', 'quick-event-manager').'</td><td><input type="text" style="border:1px solid #415063;" label="background" name="eventday" value="' . $calendar['eventday'] . '" /></td>
	<td><input type="text" style="border:1px solid #415063;" label="text" name="eventdaytext" value="' . $calendar['eventdaytext'] . '" /></td></tr>
	<tr><td width="30%">'.__('Past Date', 'quick-event-manager').'</td><td><input type="text" style="border:1px solid #415063;" label="background" name="oldday" value="' . $calendar['oldday'] . '" /></td>
	<td></td></tr>
	<tr><td width="30%">'.__('Event Hover', 'quick-event-manager').'</td><td><input type="text" style="border:1px solid #415063;" label="background" name="eventhover" value="' . $calendar['eventhover'] . '" /></td>
	<td></td></tr>
	</table>
	<h2>'.__('Category Colour', 'quick-event-manager').'</h2>
	<p>'.__('This feature only works if you have one category for your event and you enter the <a href="edit-tags.php?taxonomy=category">category slug</a>.', 'quick-event-manager').'</p>
	<table width="100%">
	<tr><th>'.__('Category Slug', 'quick-event-manager').'</th><th>'.__('Background', 'quick-event-manager').'</th><th>'.__('Text', 'quick-event-manager').'<th></tr>
	<tr>
	<td><input type="text" style="border:1px solid #415063;" label="cata" name="cata" value="' . $calendar['cata'] . '" /></td>
	<td><input type="text" style="border:1px solid #415063;" label="cataback" name="cataback" value="' . $calendar['cataback'] . '" /></td>
	<td><input type="text" style="border:1px solid #415063;" label="catatext" name="catatext" value="' . $calendar['catatext'] . '" /></td>
	</tr>
	<tr>
	<td><input type="text" style="border:1px solid #415063;" label="catb" name="catb" value="' . $calendar['catb'] . '" /></td>
	<td><input type="text" style="border:1px solid #415063;" label="catbback" name="catbback" value="' . $calendar['catbback'] . '" /></td>
	<td><input type="text" style="border:1px solid #415063;" label="catbtext" name="catbtext" value="' . $calendar['catbtext'] . '" /></td>
	</tr>
	<tr>
	<td><input type="text" style="border:1px solid #415063;" label="catc" name="catc" value="' . $calendar['catc'] . '" /></td>
	<td><input type="text" style="border:1px solid #415063;" label="catcback" name="catcback" value="' . $calendar['catcback'] . '" /></td>
	<td><input type="text" style="border:1px solid #415063;" label="catctext" name="catctext" value="' . $calendar['catctext'] . '" /></td>
	</tr>
	<tr>
	<td><input type="text" style="border:1px solid #415063;" label="catd" name="catd" value="' . $calendar['catd'] . '" /></td>
	<td><input type="text" style="border:1px solid #415063;" label="catdback" name="catdback" value="' . $calendar['catdback'] . '" /></td>
	<td><input type="text" style="border:1px solid #415063;" label="catdtext" name="catdtext" value="' . $calendar['catdtext'] . '" /></td>
	</tr>
	<tr>
	<td><input type="text" style="border:1px solid #415063;" label="cate" name="cate" value="' . $calendar['cate'] . '" /></td>
	<td><input type="text" style="border:1px solid #415063;" label="cateback" name="cateback" value="' . $calendar['cateback'] . '" /></td>
	<td><input type="text" style="border:1px solid #415063;" label="catetext" name="catetext" value="' . $calendar['catetext'] . '" /></td>
	</tr>
	<tr>
	<td><input type="text" style="border:1px solid #415063;" label="catf" name="catf" value="' . $calendar['catf'] . '" /></td>
	<td><input type="text" style="border:1px solid #415063;" label="catfback" name="catfback" value="' . $calendar['catfback'] . '" /></td>
	<td><input type="text" style="border:1px solid #415063;" label="catftext" name="catftext" value="' . $calendar['catftext'] . '" /></td>
	</tr>

	</table>
	<h2>'.__('Start the Week', 'quick-event-manager').'</h2>
	<p><input style="margin:0; padding:0; border:none;" type="radio" name="startday" value="sunday" ' . $sunday . ' /> '.__('On Sunday' ,'quick-event-manager').'<br />
	<input style="margin:0; padding:0; border:none;" type="radio" name="startday" value="monday" ' . $monday . ' /> '.__('On Monday' ,'quick-event-manager').'</p>
	<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Save Changes', 'quick-event-manager').'" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="'.__('Reset', 'quick-event-manager').'" onclick="return window.confirm( \''.__('Are you sure you want to reset the calendar settings?', 'quick-event-manager').'\' );"/></p>
	</form>
	</div>
	<div class="qem-options" style="float:right">
	<h2>'.__('Calendar Preview', 'quick-event-manager').'</h2>
	<p>'.__('The <em>prev</em> and <em>next</em> buttons only work on your Posts and Pages - so don&#146;t click on them!', 'quick-event-manager').'</p>';
	$content .= qem_show_calendar();
	$content .= '</div></div>';
	
	echo $content;
}
function qem_register (){
	if( isset( $_POST['Submit'])) {
		$options = array('useform','sendemail','title','blurb','yourname','youremail','qemsubmit','error','replytitle','replyblurb');
		foreach ($options as $item) $register[$item] = stripslashes( $_POST[$item]);
		update_option('qem_register', $register);
		qem_admin_notice(__('The registration form settings have been updated.', 'quick-event-manager'));
		}
	if( isset( $_POST['Reset'])) {
		delete_option('qem_register');
		qem_admin_notice(__('The registration form settings have been reset.', 'quick-event-manager'));
		}
	$register = qem_get_stored_register();
	$content = '<div class="qem-settings"><div class="qem-options" style="margin-right:10px">
		<form id="" method="post" action="">
		<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="useform"' . $register['useform'] . ' value="checked" /> '.__('Add a registration form to your events', 'quick-event-manager').'</p>
		<table width="100%">
		<tr><td width="30%">'.__('Your Email Address', 'quick-event-manager').'</td><td><input type="text" style="border:1px solid #415063;" name="sendemail" value="' . $register['sendemail'] . '" /></td></tr>
		<tr><td colspan="2"><h2>'.__('Registration From', 'quick-event-manager').'</h2></td></tr>
		<tr><td width="30%">'.__('Form title', 'quick-event-manager').'</td><td><input type="text" style="border:1px solid #415063;" name="title" value="' . $register['title'] . '" /></td></tr>
		<tr><td width="30%">'.__('Form blurb', 'quick-event-manager').'</td><td><input type="text" style="border:1px solid #415063;" name="blurb" value="' . $register['blurb'] . '" /></td></tr>
		<tr><td width="30%">'.__('Name Field Label', 'quick-event-manager').'</td><td><input type="text" style="border:1px solid #415063;" name="yourname" value="' . $register['yourname'] . '" /></td></tr>
		<tr><td width="30%">'.__('Email Field Label', 'quick-event-manager').'</td><td><input type="text" style="border:1px solid #415063;" name="youremail" value="' . $register['youremail'] . '" /></td></tr>
		<tr><td width="30%">'.__('Submit Button Label', 'quick-event-manager').'</td><td><input type="text" style="border:1px solid #415063;" name="qemsubmit" value="' . $register['qemsubmit'] . '" /></td></tr>
		<tr><td colspan="2"><h2>'.__('Error and Thank-you messages', 'quick-event-manager').'</h2></td></tr>
		<tr><td width="30%">'.__('Error Message', 'quick-event-manager').'</td><td><input type="text" style="border:1px solid #415063;" name="error" value="' . $register['error'] . '" /></td></tr>
		<tr><td width="30%">'.__('Thank you message title', 'quick-event-manager').'</td><td><input type="text" style="border:1px solid #415063;" name="replytitle" value="' . $register['replytitle'] . '" /></td></tr>
		<tr><td width="30%">'.__('Thank you message blurb', 'quick-event-manager').'</td><td><input type="text" style="border:1px solid #415063;" name="replyblurb" value="' . $register['replyblurb'] . '" /></td></tr>';
		$content .= '</table>
		<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Save Changes', 'quick-event-manager').'" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="'.__('Reset', 'quick-event-manager').'" onclick="return window.confirm( \''.__('Are you sure you want to reset the calendar settings?', 'quick-event-manager').'\' );"/></p>
		</form></div>
		<div class="qem-options" style="float:right">
		<h2>'.__('Example form', 'quick-event-manager').'</h2>
		<p>'.__('This is an example of the form. When it appears on your site it will use your theme styles.', 'quick-event-manager').'</p>';
		$content .= qem_loop();
		$content .= '</div></div>';
	echo $content;		
}
function qemdonate_verify($formvalues) {
	$errors = '';
	if ($formvalues['amount'] == 'Amount' || empty($formvalues['amount'])) $errors = 'first';
	if ($formvalues['yourname'] == 'Your name' || empty($formvalues['yourname'])) $errors = 'second';
	return $errors;
	}
function qemdonate_display( $values, $errors ) {
	$content = "<script>\r\t
	function donateclear(thisfield, defaulttext) {if (thisfield.value == defaulttext) {thisfield.value = '';}}\r\t
	function donaterecall(thisfield, defaulttext) {if (thisfield.value == '') {thisfield.value = defaulttext;}}\r\t
	</script>\r\t
	<div class='qem-style'>\r\t<div id='round'>\r\t";
	if ($errors) $content .= "<h2 class='error'>Feed me...</h2>\r\t<p class='error'>...your donation details</p>\r\t";
	else $content .= "<h2>Make a donation</h2>\r\t<p>Whilst I enjoy creating these plugins they don't pay the bills. So a paypal donation will always be gratefully received</p>\r\t";
	$content .= '
	<form method="post" action="" >
	<p><input type="text" label="Your name" name="yourname" value="Your name" onfocus="donateclear(this, \'Your name\')" onblur="donaterecall(this, \'Your name\')"/></p>
	<p><input type="text" label="Amount" name="amount" value="Amount" onfocus="donateclear(this, \'Amount\')" onblur="donaterecall(this, \'Amount\')"/></p>
	<p><input type="submit" value="Donate" id="submit" name="donate" /></p>
	</form></div>';
	echo $content;
	}
function qemdonate_process($values) {
	$page_url = qemdonate_page_url();
	$content = '<h2>Waiting for paypal...</h2><form action="https://www.paypal.com/cgi-bin/webscr" method="post" name="frmCart" id="frmCart">
	<input type="hidden" name="cmd" value="_xclick">
	<input type="hidden" name="business" value="graham@aerin.co.uk">
	<input type="hidden" name="return" value="' .  $page_url . '">
	<input type="hidden" name="cancel_return" value="' .  $page_url . '">
	<input type="hidden" name="no_shipping" value="1">
	<input type="hidden" name="currency_code" value="">
	<input type="hidden" name="item_number" value="">
	<input type="hidden" name="item_name" value="'.$values['yourname'].'">
	<input type="hidden" name="amount" value="'.preg_replace ( '/[^.,0-9]/', '', $values['amount']).'">
	</form>
	<script language="JavaScript">
	document.getElementById("frmCart").submit();
	</script>';
	echo $content;
	}
function qemdonate_page_url() {
	$pageURL = 'http';
	if( isset($_SERVER["HTTPS"]) ) { if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";} }
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	else $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	return $pageURL;
	}

function qemdonate_loop() {
	ob_start();
	if (isset($_POST['donate'])) {
		$formvalues['yourname'] = $_POST['yourname'];
		$formvalues['amount'] = $_POST['amount'];
		if (qemdonate_verify($formvalues)) qemdonate_display($formvalues,'donateerror');
   		else qemdonate_process($formvalues,$form);
		}
	else qemdonate_display($formvalues,'');
	$output_string=ob_get_contents();
	ob_end_clean();
	return $output_string;
	}