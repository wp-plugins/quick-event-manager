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
	$content = '
	<div class="qem-options">
	<h2>'.__('Setting up and using the plugin', 'quick-event-manager').'</h2>
	<p><span style="color:red; font-weight:bold;">'. __('Important!', 'quick-event-manager').'</span> '.__('This plugin uses custom posts. For it to work properly you have to resave your <a href="options-permalink.php">permalinks</a>. This is not a bug, it&#146;s how wordpress works. If you don&#146;t resave your permalinks you will get a <em>page not found</em> message on your events.', 'quick-event-manager').'</p>
	<p>'.__('Create new events using the <a href="edit.php?post_type=event">Events</a> link on your dashboard menu.', 'quick-event-manager').'</p>
	<p>'.__('To display a list of events on your posts or pages use the shortcode: <code>[qem]</code>.', 'quick-event-manager').'</p>
	<p>'.__('If you prefer to display your events as a calendar use the shortcode: <code>[qemcalendar]</code>.', 'quick-event-manager').'</p>
	<p>'.__('That&#39;s pretty much it. All you need to do now is <a href="edit.php?post_type=event">create some events</a>.', 'quick-event-manager').'</p>
	<p>'.__('There are more shortcode options and some help at <a href="http://quick-plugins.com/quick-event-manager/" target="_blank">quick-plugins.com</a> along with a feedback form. Or you can email me at ', 'quick-event-manager').'<a href="mailto:mail@quick-plugins.com">mail@quick-plugins.com</a>.</p>
	</div>';
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
		<div class ="qem-options" style="width:90%">
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
	$content = '<div class="qem-options">
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
		<div class="qem-options">
<h2>'.__('Event List Preview', 'quick-event-manager').'</h2>';
	$atts = array('
		posts' => '3');
	
	$content .= event_shortcode($atts,'');
	$content .= '</div>';
	echo $content;
	}
	
function qem_styles() {
	if( isset( $_POST['Submit'])) {
		$options = array(
		'font',
		'font-family',
		'font-size',
		'width',
		'widthtype',
		'event_background',
		'event_backgroundhex',
		'date_colour',
		'date_background',
		'date_backgroundhex',
		'use_custom',
		'custom',
		'date_bold',
		'date_italic',
		'date_border_width',
		'date_border_colour',
		'calender_size',
		'event_border');
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
	$content = '<div class="qem-options">
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
</div>
	</div><div class="qem-options">
<h2>'.__('Event List Preview', 'quick-event-manager').'</h2>';
	$atts = array('
		posts' => '3');
	
	$content .= event_shortcode($atts,'');
	$content .= '</div>';
	
	echo $content;
}
	
function qem_calendar() {
	if( isset( $_POST['Submit'])) {
		$options = array(
		'calday',
		'day',
		'eventday',
		'oldday',
		'eventhover',
		'eventdaytext',
		'eventlink',
		'connect',
		'calendar_text',
		'calendar_url',
		'eventlist_text',
		'eventlist_url',
		'startday');
		
			foreach ( $options as $item) $calendar[$item] = stripslashes($_POST[$item]);
			update_option('qem_calendar', $calendar);
			qem_admin_notice (__('The calendar settings have been updated.', 'quick-event-manager'));
	}
	
	if( isset( $_POST['Reset'])) {
		delete_option('qem_calendar');
		qem_admin_notice (__('The calendar settings have been reset.', 'quick-event-manager'));
	}
	
	$calendar = qem_get_stored_calendar();
	$$calendar['eventlink'] = 'checked';
	$$calendar['startday'] = 'checked';
	$content = '<div class="qem-options">
	<p>'.__('This is a new feature in the plugin. It does work but may need some tweaks. If you want something just let me know:', 'quick-event-manager').' <a href="mailto:mail@quick-plugins.com">mail@quick-plugins.com</a></p>
	<p>'.__('To add the calendar to your site use the shortcode: <code>[qemcalendar]</code>.', 'quick-event-manager').'</p>
	<form method="post" action="">
	<h2>'.__('Event Links', 'quick-event-manager').'</h2>
	<p><input style="margin:0; padding:0; border:none;" type="radio" name="eventlink" value="linkpopup" ' . $linkpopup . ' /> '.__('Link opens event summary in a popup', 'quick-event-manager').'<br />
	<input style="margin:0; padding:0; border:none;"
	type="radio" name="eventlink" value="linkpage" ' . $linkpage . ' /> '.__('Link opens event page' ,'quick-event-manager').'<br />
	<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="connect"' . $calendar['connect'] . ' value="checked" /> '.__('Link Event List to Calendar Page (you will need to create a page for the calendar).', 'quick-event-manager').'</p>
	<table>
	<tr><td>'.__('Calendar link text', 'quick-event-manager').'</td><td><input type="text" style="width:20em;border:1px solid #415063;" label="calendar_text" name="calendar_text" value="' . $calendar['calendar_text'] . '" /></td></tr>
	<tr><td>'.__('Calendar page URL', 'quick-event-manager').'</td><td><input type="text" style="width:20em;border:1px solid #415063;" label="calendar_url" name="calendar_url" value="' . $calendar['calendar_url'] . '" /></td></tr>
	<tr><td>'.__('Event list link text', 'quick-event-manager').'</td><td><input type="text" style="width:20em;border:1px solid #415063;" label="eventlist_text" name="eventlist_text" value="' . $calendar['eventlist_text'] . '" /></td></tr>
	<tr><td>'.__('Event list page', 'quick-event-manager').' URL</td><td><input type="text" style="width:20em;border:1px solid #415063;" label="eventlist_url" name="eventlist_url" value="' . $calendar['eventlist_url'] . '" /></td></tr>
	</table>
	<h2>'.__('Calendar Colours', 'quick-event-manager').'</h2>
	<table>
	<tr><td>'.__('Items', 'quick-event-manager').'</td><td>'.__('Background', 'quick-event-manager').'</td><td>'.__('Text', 'quick-event-manager').'<td></tr>
	<tr><td>'.__('Days of the Week', 'quick-event-manager').'</td><td><input type="text" style="width:7em;border:1px solid #415063;" label="background" name="calday" value="' . $calendar['calday'] . '" /></td>
	<td></td></tr>
	<tr><td>'.__('Normal Date', 'quick-event-manager').'</td><td><input type="text" style="width:7em;border:1px solid #415063;" label="background" name="day" value="' . $calendar['day'] . '" /></td><td></td></tr>
	<tr><td>'.__('Event Date', 'quick-event-manager').'</td><td><input type="text" style="width:7em;border:1px solid #415063;" label="background" name="eventday" value="' . $calendar['eventday'] . '" /></td>
	<td><input type="text" style="width:7em;border:1px solid #415063;" label="text" name="eventdaytext" value="' . $calendar['eventdaytext'] . '" /></td></tr>
	<tr><td>'.__('Past Date', 'quick-event-manager').'</td><td><input type="text" style="width:7em;border:1px solid #415063;" label="background" name="oldday" value="' . $calendar['oldday'] . '" /></td>
	<td></td></tr>
	<tr><td>'.__('Event Hover', 'quick-event-manager').'</td><td><input type="text" style="width:7em;border:1px solid #415063;" label="background" name="eventhover" value="' . $calendar['eventhover'] . '" /></td>
	<td></td></tr>
	</table>
	<h2>Start the Week</h2>
	<p><input style="margin:0; padding:0; border:none;" type="radio" name="startday" value="sunday" ' . $sunday . ' /> '.__('On Sunday' ,'quick-event-manager').'<br />
	<input style="margin:0; padding:0; border:none;" type="radio" name="startday" value="monday" ' . $monday . ' /> '.__('On Monday' ,'quick-event-manager').'</p>
	<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Save Changes', 'quick-event-manager').'" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="'.__('Reset', 'quick-event-manager').'" onclick="return window.confirm( \''.__('Are you sure you want to reset the calendar settings?', 'quick-event-manager').'\' );"/></p>
	</form>
	</div>
	<div class="qem-options">
	<h2>'.__('Calendar Preview', 'quick-event-manager').'</h2>';

	$content .= qem_show_calendar();
	$content .= '</div>';
	
	echo $content;
}
function qem_register (){
	if( isset( $_POST['Submit'])) {
		$options = array('useform','sendemail','title','blurb','yourname','youremail','qemsubmit','error','replytitle','replyblurb');
		foreach ($options as $item) $register[$item] = stripslashes( $_POST[$item]);
		update_option('qem_register', $register);
		qem_admin_notice("The registration form settings have been updated.");
		}
	if( isset( $_POST['Reset'])) {
		delete_option('qem_register');
		qem_admin_notice("The registration form settings have been reset.");
		}
	$register = qem_get_stored_register();
	$content = '<div class ="qem-options">
		<form id="" method="post" action="">
		<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="useform"' . $register['useform'] . ' value="checked" /> '.__('Add a registration form to your events', 'quick-event-manager').'</p>
		<table>
		<tr><td>Your Email Address</td><td><input type="text" style="width:20em;border:1px solid #415063;" name="sendemail" value="' . $register['sendemail'] . '" /></td></tr>
		<tr><td colspan="2"><h2>Registration From</h2></td></tr>
		<tr><td>Form title</td><td><input type="text" style="width:20em;border:1px solid #415063;" name="title" value="' . $register['title'] . '" /></td></tr>
		<tr><td>Form blurb</td><td><input type="text" style="width:20em;border:1px solid #415063;" name="blurb" value="' . $register['blurb'] . '" /></td></tr>
		<tr><td>Name Field Label</td><td><input type="text" style="width:20em;border:1px solid #415063;" name="yourname" value="' . $register['yourname'] . '" /></td></tr>
		<tr><td>Email Field Label</td><td><input type="text" style="width:20em;border:1px solid #415063;" name="youremail" value="' . $register['youremail'] . '" /></td></tr>
		<tr><td>Submit Button Label</td><td><input type="text" style="width:20em;border:1px solid #415063;" name="qemsubmit" value="' . $register['qemsubmit'] . '" /></td></tr>
		<tr><td colspan="2"><h2>Error and Thank-you messages</h2></td></tr>
		<tr><td>Error Message</td><td><input type="text" style="width:20em;border:1px solid #415063;" name="error" value="' . $register['error'] . '" /></td></tr>
		<tr><td>Thank you message title</td><td><input type="text" style="width:20em;border:1px solid #415063;" name="replytitle" value="' . $register['replytitle'] . '" /></td></tr>
		<tr><td>Thank you message blurb</td><td><input type="text" style="width:20em;border:1px solid #415063;" name="replyblurb" value="' . $register['replyblurb'] . '" /></td></tr>';
		$content .= '</table>
		<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Save Changes', 'quick-event-manager').'" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="'.__('Reset', 'quick-event-manager').'" onclick="return window.confirm( \''.__('Are you sure you want to reset the calendar settings?', 'quick-event-manager').'\' );"/></p>
		</form></div><div class ="qem-options"><h2>Example form</h2><p>This is an example of the form. When it appears on your site it will use your theme styles.</p>';
		$content .= qem_loop();
		$content .= '</div>';
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
		
		case "event_time":echo $custom["event_start"][0] . ' - ' . $custom["event_finish"][0];
		break;
		
		case "event_location":echo $custom["event_location"][0];
		break;
		
		case "event_address":echo $custom["event_address"][0];
		break;
		
		case "event_website":echo $custom['event_link'][0];
		break;
		
		case "event_cost":echo $custom["event_cost"][0];
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
	
	$output = '
	<p><em>'.__('Empty fields are not displayed. See the plugin <a href="options-general.php?page=quick-event-manager/settings.php">settings</a> page for options.', 'quick-event-manager').'</em></p>
	<p><label>'.__('Date:', 'quick-event-manager').' </label>
	<input type="text" style="border:1px solid #415063;" id="qemdate" name="event_date" value="' . $date . '" /> <em>'.__('(Errors will reset to today&#146;s date.)', 'quick-event-manager').'</em>.</p>
	<script type="text/javascript">jQuery(document).ready(function() {jQuery(\'#qemdate\').datepicker({dateFormat : \'dd M yy\'});});</script>
	
	<p><label>'.__('End Date:', 'quick-event-manager').' </label>
	<input type="text" style="border:1px solid #415063;" id="qemenddate" name="event_end_date" value="' . $enddate . '" /> <em>'.__('(Leave blank for one day events.)', 'quick-event-manager').'</em>.</p>
	<script type="text/javascript">jQuery(document).ready(function() {jQuery(\'#qemenddate\').datepicker({dateFormat : \'dd M yy\'});});</script>
	<p><label>'.__('Short Description:', 'quick-event-manager').' </label><input type="text" style="border:1px solid #415063;" size="100" name="event_desc" value="' . get_event_field("event_desc") . '" /></p>
	<p><label>'.__('Time', 'quick-event-manager').' <em>(hh:mm): ' . $event['start_label'] . ' </label><input type="text" style="border:1px solid #415063;"  name="event_start" value="' . get_event_field("event_start") . '" /> ' . $event['finish_label'] . ' <input type="text" style="border:1px solid #415063;"  name="event_finish" value="' . get_event_field("event_finish") . '" /></p>
	<p><label>'.__('Location:', 'quick-event-manager').' </label><input type="text" style="border:1px solid #415063;" size="70" name="event_location" value="' . get_event_field("event_location") . '" /></p>
	<p><label>'.__('Address:', 'quick-event-manager').' </label><input type="text" style="border:1px solid #415063;" size="100" name="event_address" value="' . get_event_field("event_address") . '" /></p>
	<p><label>'.__('Website:', 'quick-event-manager').' </label><input type="text" style="border:1px solid #415063;" size="50" name="event_link" value="' . get_event_field("event_link") . '" /><label> '.__('Display As:', 'quick-event-manager').' </label><input type="text" style="border:1px solid #415063;" size="40" name="event_anchor" value="' . get_event_field("event_anchor") . '" /></p>
	<p><label>'.__('Cost:', 'quick-event-manager').' </label><input type="text" style="border:1px solid #415063;" size="70" name="event_cost" value="' . get_event_field("event_cost") . '" /></p>';
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
function inner_custom_box( $post ) {the_editor($post->post_content);}

function qem_duplicate_month() {qem_duplicate_post($period = '+1month');}
function qem_duplicate_week() {$period = '+7days';qem_duplicate_post($period);}
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

function qem_admin_pointers_header() {
	if ( qem_admin_pointers_check() ) {
		add_action( 'admin_print_footer_scripts', 'qem_admin_pointers_footer' );
		wp_enqueue_script( 'wp-pointer' );
		wp_enqueue_style( 'wp-pointer' );
		}
	}
function qem_admin_pointers_check() {
	$admin_pointers = qem_admin_pointers();
	foreach ( $admin_pointers as $pointer => $array ) {
		if ( $array['active'] ) return true;
		}
	}
function qem_admin_pointers_footer() {
	$admin_pointers = qem_admin_pointers();
	?>
	<script type="text/javascript">
	/* <![CDATA[ */
	( function($) {
   	<?php
	foreach ( $admin_pointers as $pointer => $array ) {
		if ( $array['active'] ) {
		?>
		$( '<?php echo $array['anchor_id']; ?>' ).pointer( {
			content: '<?php echo $array['content']; ?>',
			position: {
			edge: '<?php echo $array['edge']; ?>',
			align: '<?php echo $array['align']; ?>'
			},
		close: function() {
		$.post( ajaxurl, {pointer: '<?php echo $pointer; ?>',action: 'dismiss-wp-pointer'} );
		}
	} ).pointer( 'open' );
	<?php } } ?>
	} )(jQuery);
	/* ]]> */
	</script>
	<?php
	}
function qem_admin_pointers() {
	$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
	$version = '5_0';
	$prefix = 'qem_admin_pointers' . $version . '_';
	$new_pointer_content = '<h3>Event Manager</h3>';
	$new_pointer_content .= '<h4 style="margin:3px 15px">Upgrades from V4.2</h4><p style="margin:5px 0">All new <a href="options-general.php?page=quick-event-manager/settings.php&tab=register">Event Registration Form</a><br>Event Start and End Date fields<br>More <a href="options-general.php?page=quick-event-manager/settings.php&tab=calendar">Calendar</a> options</p><h4 style="margin:3px 15px">Get Started Guide</h4><p style="margin:5px 0">If you are new to the plugin then start <a href="options-general.php?page=quick-event-manager/settings.php">here</a>.</p>';
	return array(
		$prefix . 'new_items' => array(
		'content' => $new_pointer_content,
		'anchor_id' => '#menu-posts-event',
		'edge' => 'left',
		'align' => 'left',
		'active' => ( ! in_array( $prefix . 'new_items', $dismissed ) )
		),);
	}

add_filter( 'post_row_actions', 'duplicate_post_month', 10, 2 );
add_filter( 'post_row_actions', 'duplicate_post_week', 10, 2 );