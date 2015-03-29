<?php
add_action('init', 'qem_settings_init');
add_action("admin_menu","event_page_init");
add_action("save_post", "save_event_details");
add_action("admin_notices","qem_admin_notice");
add_action("add_meta_boxes","action_add_meta_boxes", 0 );
add_action("manage_posts_custom_column","event_custom_columns");
add_action( 'admin_menu', 'qem_admin_pages' );
add_filter("manage_event_posts_columns","event_edit_columns");
add_filter("manage_edit-event_sortable_columns","event_date_column_register_sortable");
add_filter("request","event_date_column_orderby");

register_uninstall_hook(__FILE__, 'event_delete_options');

function qem_settings_init() {
    qem_generate_csv();
    qem_add_role_caps();
    return;
}

function qem_settings_scripts() {
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('datepicker-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('qemcolor-script', plugins_url('quick-event-color.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
    wp_enqueue_style('wp-color-picker' );
    wp_enqueue_media();
    wp_enqueue_script('qemmedia-script',plugins_url('quick-event-media.js', __FILE__ ), array( 'jquery' ), false, true );
    wp_enqueue_style( 'qem_settings',plugins_url('settings.css', __FILE__));
    wp_enqueue_style('event_style',plugins_url('quick-event-manager.css', __FILE__));
    wp_enqueue_style('event_custom',plugins_url('quick-event-manager-custom.css', __FILE__));
    wp_enqueue_script('event_script',plugins_url('quick-event-manager.js', __FILE__));
}

add_action('admin_enqueue_scripts', 'qem_settings_scripts');

function qem_admin_pages() {
    add_menu_page('Registration', 'Registration', 'manage_options','quick-event-manager/quick-event-messages.php');
}

function event_page_init() {
    add_options_page( __('Event Manager', 'quick-event-manager'), __('Event Manager', 'quick-event-manager'), 'manage_options', __FILE__, 'qem_tabbed_page');
}

function qem_admin_notice($message) {
    if (!empty( $message)) echo '<div class="updated"><p>'.$message.'</p></div>';
}

function qem_tabbed_page() {
    echo '<div class="wrap"><h1>Quick Event Manager</h1>';
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
        case 'payment' : qem_payment(); break;
        case 'template' : qem_template(); break;
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
        'payment'  => __('Event Payment', 'quick-event-manager'),
        'template'  => __('Event Template', 'quick-event-manager')
	);
    echo '<div id="icon-themes" class="icon32"><br></div>';
    echo '<h2 class="nav-tab-wrapper">';
    foreach( $tabs as $tab => $name ) {
        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
        echo "<a class='nav-tab$class' href='?page=quick-event-manager/settings.php&tab=$tab'>$name</a>";
    }
    echo '</h2>';
}

function qem_setup() {
    $content = '<div class="qem-settings"><div class="qem-options">
    <h2>'.__('Setting up and using the plugin', 'quick-event-manager').'</h2>
    <p><span style="color:red; font-weight:bold;">'. __('Important!', 'quick-event-manager').'</span> '.__('If you get an error when trying to view events, resave your <a href="options-permalink.php">permalinks</a>.', 'quick-event-manager').'</p>
    <p>'.__('Create new events using the <a href="edit.php?post_type=event">Events</a> link on your dashboard menu.', 'quick-event-manager').'</p>
    <p>'.__('To display a list of events on your posts or pages use the shortcode: <code>[qem]</code>.', 'quick-event-manager').'</p>
    <p>'.__('If you prefer to display your events as a calendar use the shortcode: <code>[qemcalendar]</code>.', 'quick-event-manager').'</p>
    <p>'.__('More shortcodes on the right.', 'quick-event-manager').'</p>
    <p>'.__('That&#39;s pretty much it. All you need to do now is <a href="edit.php?post_type=event">create some events</a>.', 'quick-event-manager').'</p>
    <p>'.__('Help at <a href="http://quick-plugins.com/quick-event-manager/" target="_blank">quick-plugins.com</a> along with a feedback form. Or you can email me at ', 'quick-event-manager').'<a href="mailto:mail@quick-plugins.com">mail@quick-plugins.com</a>.</p>'.qemdonate_loop().'</div></div>
    <div class="qem-options" style="float:right">
    <h2>'.__('Settings', 'quick-event-manager').'</h2>
    <h3><a href="?page=quick-event-manager/settings.php&tab=settings">'.__('Event Settings', 'quick-event-manager').'</a></h3>
    <p>'.__('Select which fields are displayed in the event list and event page. Change actions and style of each field', 'quick-event-manager').'</p>
    <h3><a href="?page=quick-event-manager/settings.php&tab=display">'.__('Event Display', 'quick-event-manager').'</a></h3>
    <p>'.__('Edit event messages and display options', 'quick-event-manager').'</p>
    <h3><a href="?page=quick-event-manager/settings.php&tab=styles">'.__('Event Styling', 'quick-event-manager').'</a></h3>
    <p>'.__('Styling options for the date icon and overall event layout', 'quick-event-manager').'</p>
    <h3><a href="?page=quick-event-manager/settings.php&tab=calendar">'.__('Calendar Options', 'quick-event-manager').'</a></h3>
    <p>'.__('Show events as a calendar. Some styling and display options.', 'quick-event-manager').'</p>
    <h3><a href="?page=quick-event-manager/settings.php&tab=register">'.__('Event Registration', 'quick-event-manager').'</a></h3>
    <p>'.__('Add a simple registration form to your events', 'quick-event-manager').'</p>
    <h3><a href="?page=quick-event-manager/settings.php&tab=payment">'.__('Event Payments', 'quick-event-manager').'</a></h3>
    <p>'.__('Event payment form', 'quick-event-manager').'</p>
    <h3><a href="?page=quick-event-manager/quick-event-messages.php">'.__('Registration Report', 'quick-event-manager').'</a></h3>
    <p>'.__('View, edit and download event registrations.', 'quick-event-manager').' '.__('Access using the <b>Registration</b> link on your dahboard menu.', 'quick-event-manager').'</p>
    <h2>'.__('Primary Shortcodes', 'quick-event-manager').'</h2>
    <table>
    <tbody>
    <tr><td>[qem]</td><td>'.__('Standard event list', 'quick-event-manager').'</td></tr>
    <tr><td>[qemcalendar]</td><td>'.__('Calendar view', 'quick-event-manager').'</td></tr>
    <tr><td>[qem posts=\'99\']</td><td>'.__('Set the number of events to display', 'quick-event-manager').'</td></tr>
    <tr><td>[qem id=\'archive\']</td><td>'.__('Show old events', 'quick-event-manager').'</td></tr>
    <tr><td>[qem category=\'name\']</td><td>'.__('List events by category', 'quick-event-manager').'</td></tr>
    </tbody>
    </table>
    <p>There are loads more shortcode options listed on the <a href="http://quick-plugins.com/quick-event-manager/all-the-shortcodes/" target="_blank">Plugin Website</a> (link opens in a new tab)';
    $content .= '</div></div>';
    echo $content;
}

function qem_event_settings() {
    $active_buttons = array('field1','field2','field3','field4','field5','field6','field7');	
    if( isset( $_POST['Submit'])) {
        foreach ( $active_buttons as $item) {
            $event['active_buttons'][$item] = (isset($_POST['event_settings_active_'.$item]) and $_POST['event_settings_active_'.$item] =='on') ? true : false;
            $event['summary'][$item] = (isset( $_POST['summary_'.$item]) );
            $event['bold'][$item] = (isset( $_POST['bold_'.$item]) );
            $event['italic'][$item] = (isset( $_POST['italic_'.$item]) );
            $event['colour'][$item] = $_POST['colour_'.$item];
            $event['size'][$item] = $_POST['size_'.$item];
            if (!empty ( $_POST['label_'.$item])) $event['label'][$item] = stripslashes($_POST['label_'.$item]);
        }
        $option = array(
            'sort',
            'description_label',
            'address_label',
            'url_label',
            'cost_label',
            'start_label',
            'finish_label',
            'location_label',
            'organiser_label',
            'show_telephone',
            'show_map',
            'read_more',
            'noevent',
            'dateformat',
            'address_style',
            'website_link',
            'date_background',
            'background_hex',
            'event_order',
            'event_archive',
            'event_descending',
            'calender_size',
            'map_width',
            'map_height',
            'date_bold',
            'date_italic',
            'styles',
            'custom',
            'number_of_posts',
            'target_link',
            'external_link'
        );
        foreach ($option as $item)$event[$item] = stripslashes($_POST[$item]);
        update_option( 'event_settings', $event);
        qem_admin_notice(__('The form settings have been updated', 'quick-event-manager'));
    }
    if( isset( $_POST['Reset'])) {
        delete_option('event_settings');
        qem_create_css_file ('update');
        qem_admin_notice (__('The event settings have been reset.', 'quick-event-manager')) ;
    }
    $event = event_get_stored_options();
    $$event['dateformat'] = 'checked'; 
    $$event['date_background'] = 'checked';
    $$event['event_order'] = 'checked';
    $$style['calender_size'] = 'checked'; 
    if ( $event['event_archive'] == "checked") $archive = "checked"; 
    if ($event['show_map'] == 'checked') $map = 'checked';
    $content = '<script>
    jQuery(function() {var qem_sort = jQuery( "#qem_sort" ).sortable({axis: "y",update:function(e,ui) {var order = qem_sort.sortable("toArray").join();jQuery("#qem_settings_sort").val(order);}});});
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
    <script>
    jQuery(function() 
    {var qem_rsort = jQuery( "#qem_sort" ).sortable(
    {axis: "y",cursor: "move",opacity:0.8,update:function(e,ui)
    {var order = qem_sort.sortable("toArray").join();jQuery("#qem_settings_sort").val(order);}});});
    </script>
    <table id="sorting">
    <thead>
    <tr>
    <th width="15%">'.__('Show in event post', 'quick-event-manager').'</th>
    <th width="10%">'.__('Show in<br>event list', 'quick-event-manager').'</th>
    <th width="15%">'.__('Colour', 'quick-event-manager').'</th>
    <th width="5%">'.__('Font<br>size', 'quick-event-manager').'</th>
    <th width="10%">'.__('Font<br>attributes', 'quick-event-manager').'</th>
    <th>'.__('Caption and display options:', 'quick-event-manager').'</th>
    </tr>
    </thead><tbody id="qem_sort">';
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
            $options = '<input type="text" style="border:1px solid blue; width:6em; padding: 1px; margin:0;" name="location_label" . value ="' . $event['location_label'] . '" /> {'.__('venue', 'quick-event-manager').'}';
            break;
            case 'field4':
            $options = '<input type="text" style="border:1px solid blue; width:10em; padding: 1px; margin:0;" name="address_label" . value ="' . $event['address_label'] . '" /> {'.__('address', 'quick-event-manager').'}&nbsp;<input type="checkbox" style="margin: 0; padding: 0; border: none;" name="show_map"' . $event['show_map'] . ' value="checked" /> '.__('Show map (if address is given)', 'quick-event-manager').' ';
            break;
            case 'field5':
            $options = '<input type="text" style="border:1px solid blue; width:10em; padding: 1px; margin:0;" name="url_label" . value ="' . $event['url_label'] . '" /> {url}&nbsp;<input type="checkbox" style="margin: 0; padding: 0; border: none;" name="target_link"' . $event['target_link'] . ' value="checked" />'.__('Open in new page', 'quick-event-manager');
            break;
            case 'field6':
            $options = '<input type="text" style="border:1px solid blue; width:10em; padding: 1px; margin:0;" name="cost_label" . value ="' . $event['cost_label'] . '" /> {'.__('cost', 'quick-event-manager').'}';
            break;
            case 'field7':
            $options = '<input type="text" style="border:1px solid blue; width:10em; padding: 1px; margin:0;" name="organiser_label" . value ="' . $event['organiser_label'] . '" /> {'.__('organiser', 'quick-event-manager').'}&nbsp;<input type="checkbox" style="margin: 0; padding: 0; border: none;" name="show_telephone"' . $event['show_telephone'] . ' value="checked" /> '.__('Show orgainser\'s telephone', 'quick-event-manager').' ';;
            break;
        }
        $li_class = ( $checked) ? 'button_active' : 'button_inactive';
        $content .= '<tr class="ui-state-default '.$li_class.' '.$first.'" id="' . $name . '"><td>
        <input type="checkbox" class="button_activate" style="border: none; padding: 0; margin:0;" name="event_settings_active_'.$name.'" '.$checked.' />
        <b>' . $event['label'][$name] . '</b></td>
        <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="summary_'.$name.'" '.$summary.' /></td>
        <td><input type="text" class="qem-color" name="colour_'.$name.'" value ="' . $event['colour'][$name].'" /></td>
        <td><input type="text" style="border:1px solid #415063; width:3em; padding: 1px; margin:0;" name="size_'.$name.'" value ="'.$event['size'][$name].'" />%</td>
        <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="bold_'.$name.'" '.$bold.' /> Bold <input type="checkbox" style="border: none; padding: 0; margin:0;" name="italic_'.$name.'" '.$italic.' /> Italic</td>
        <td>'.$options.'</td>
        </tr>';
    }
	$content .='</tbody></table>
	<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Save Changes', 'quick-event-manager').'" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="'.__('Reset', 'quick-event-manager').'" onclick="return window.confirm( \' '.__('Are you sure you want to reset the display settings?', 'quick-event-manager').'\' );"/></p>
	<input type="hidden" id="qem_settings_sort" name="sort" value="'.$event['sort'].'" />
	</form>
    <h2>Shortcode Selection</h2>
    <p>If you want a custom layout for a specific list you can use the shortcode [qem fields=1,2,5].<p>
    <p>The numbers correspond to the fields like this:<p>
    <ol>
    <li>Short description</li>
    <li>Event Time</li>
    <li>Cost</li>
    <li>Venue</li>
    <li>Address</li>
    <li>Website</li>
    </ol>
    <p>The order of the fields and other options is set using the drag and drop selectors above</p></div>';
    echo $content;
}

function qem_display_page() {
    if( isset( $_POST['Submit'])) {
        $option = array(
            'show_end_date',
            'read_more',
            'noevent',
            'event_archive',
            'event_descending',
            'external_link',
            'external_link_target',
            'linkpopup',
            'recentposts',
            'event_image',
            'back_to_list',
            'back_to_list_caption',
            'back_to_url',
            'map_width',
            'map_height',
            'map_in_list',
            'map_and_image',
            'map_and_image_size',
            'map_target',
            'event_image_width',
            'image_width',
            'combined',
            'monthheading',
            'useics',
            'useicsbutton',
            'usetimezone',
            'timezonebefore',
            'timezoneafter',
            'amalgamated',
            'vertical',
        );
        foreach ($option as $item) $display[$item] = $_POST[$item];
        update_option('qem_display', $display);	
        qem_create_css_file ('update');
        qem_admin_notice (__('The display settings have been updated.', 'quick-event-manager'));
    }		
	if( isset( $_POST['Reset'])) {
		delete_option('qem_display');
        qem_create_css_file ('update');
		qem_admin_notice (__('The display settings have been reset.', 'quick-event-manager')) ;
    }
    $display = event_get_stored_display();
    $$display['event_order'] = 'checked';
    $$display['show_end_date'] = 'checked';
    $$display['localization'] = 'selected';
    if ( $display['event_archive'] == "checked") $archive = "checked"; 
    $content = '<style>'.qem_generate_css().'</style>
    <div class="qem-settings">
    <div class="qem-options">
    <form id="event_settings_form" method="post" action="">	
    <table>
    <tr>
    <td colspan="2"><h2>'.__('End Date Display', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="border: none; padding: 0; margin:0;" name="show_end_date" value="checked" ' . $display['show_end_date'] . ' /></td><td width="95%"> '.__('Show end date in event list', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="combined" value="checked" ' . $display['combined'] . ' /></td><td> '.__('Combine Start and End dates into one box', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="amalgamated" value="checked" ' . $display['amalgamated'] . ' /></td><td> '.__('Show combined Start and End dates if in the same month', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="vertical" value="checked" ' . $display['vertical'] . ' /></td><td> '.__('Show dates above one another', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td colspan="2"><h2>'.__('Event Messages', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td colspan="2">'.__('Read more caption:', 'quick-event-manager').' <input type="text" style="width:20em;border:1px solid #415063;" label="read_more" name="read_more" value="' . $display['read_more'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2">'.__('No events message:', 'quick-event-manager').' <input type="text" style="width:20em;border:1px solid #415063;" label="noevent" name="noevent" value="' . $display['noevent'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2"><h2>'.__('Event List Options', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="event_descending" value="checked" ' . $display['event_descending'] . ' /></td>
    <td> '.__('List events in reverse order (from future to past)', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="event_archive" value="checked" ' . $display['event_archive'] . ' /></td>
    <td> '.__('Show past events in the events list', 'quick-event-manager').'<br><span class="description">'.__('If you only want to display past events use the shortcode: <code>[qem id="archive"]</code>.', 'quick-event-manager').'</span></td>
    </tr>
    <tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="monthheading" value="checked" ' . $display['monthheading'] . ' /></td>
    <td> '.__('Split the list into month/year sections', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="external_link"' . $display['external_link'] . ' value="checked" /></td>
    <td>'.__('Link to external website', 'quick-event-manager').'<br><span class="description">'.__('Use this to link from the list to the event website rather than the event post', 'quick-event-manager').'</span></td>
    </tr>
    <tr>
    <td><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="external_link_target"' . $display['external_link_target'] . ' value="checked" /></td>
    <td>'.__('Open link in new tab/page', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="linkpopup"' . $display['linkpopup'] . ' value="checked" /></td>
    <td>'.__('Open event in lightbox', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="recentposts"' . $display['recentposts'] . ' value="checked" /></td>
    <td>'.__('Show events in recent posts list', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td colspan="2">Add an Event key to your list using the settings on the <a href="?page=quick-event-manager/settings.php&tab=styles">Event Styling</a> page.</td>
    </tr>
    <tr>
    <td colspan="2"><h2>Download to Calendar</h2></td>
    </tr>
    <tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="useics" value="checked" ' . $display['useics'] . ' /></td>
    <td> '.__('Add a button to download event as a calender file', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td colspan="2">Button text: <input type="text" style="width:50%;border:1px solid #415063;" label="useicsbutton" name="useicsbutton" value="' . $display['useicsbutton'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2"><h2>Event Linking Options</h2></td>
    </tr>
    <tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="back_to_list" value="checked" ' . $display['back_to_list'] . ' /></td>
    <td> '.__('Add a link to events to go back to the event list', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td colspan="2">Enter URL to link to a specific page. Leave blank to just go back one page:<br>
    <input type="text" style="border:1px solid #415063;" label="back_to_url" name="back_to_url" value="' . $display['back_to_url'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2">'.__('Link caption:', 'quick-event-manager').' <input type="text" style="width:50%;border:1px solid #415063;" label="back_to_list_caption" name="back_to_list_caption" value="' . $display['back_to_list_caption'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2"><h2>'.__('Images and Maps', 'quick-event-manager').'</h2><td>
    </tr>
    <tr>
    <td colspan="2">The map will only display if you have a valid address and the &#146;show map&#146; checkbox is ticked on the <a href="?page=quick-event-manager/settings.php&tab=settings">Event Settings</a> page. If you add an image on the event editor it will replace the map unless you use the option to display both.</td>
    </tr>
    <tr>
    <td colspan="2">'.__('Map Width:', 'quick-event-manager').' <input type="text" style="border:1px solid #415063; width:3em; padding: 1px; margin:0;" name="map_width" . value ="' . $display['map_width'] . '" /> px&nbsp;&nbsp;'.__('Map Height:', 'quick-event-manager').' <input type="text" style="border:1px solid #415063; width:3em; padding: 1px; margin:0;" name="map_height" . value ="' . $display['map_height'] . '" /> px</td>
    </tr>
    <tr>
    <td colspan="2">'.__('Event Image Max Width:', 'quick-event-manager').' <input type="text" style="border:1px solid #415063; width:3em; padding: 1px; margin:0;" name="event_image_width" . value ="' . $display['event_image_width'] . '" /> px</td>
    </tr>
    <tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="map_in_list" value="checked" ' . $display['map_in_list'] . ' /></td>
    <td>'.__('Show map in event list', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="event_image" value="checked" ' . $display['event_image'] . ' /></td>
    <td>'.__('Show event image in event list', 'quick-event-manager').'.&nbsp;&nbsp;'.__('Max Width:', 'quick-event-manager').' <input type="text" style="border:1px solid #415063; width:3em; padding: 1px; margin:0;" name="image_width" . value ="' . $display['image_width'] . '" /> px</td>
    </tr>
    <tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="map_target" value="checked" ' . $display['map_target'] . ' /></td>
    <td>'.__('Open map in new tab/window', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="map_and_image" value="checked" ' . $display['map_and_image'] . ' /></td>
    <td>'.__('Show event map and image', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="map_and_image_size" value="checked" ' . $display['map_and_image_size'] . ' /></td>
    <td>'.__('Make image the same width as the map', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td colspan="2"><h2>Timezones</h2></td>
    </tr>
    <tr>
    <td><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="usetimezone"' . $display['usetimezone'] . ' value="checked" /></td>
    <td>'.__('Show timeszones on your events', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td colspan="2"><input type="text" style="width:40%;border:1px solid #415063;" name="timezonebefore" value="' . $display['timezonebefore'] . '" /> {timezone} <input type="text" style="width:40%;border:1px solid #415063;" name="timezoneafter" value="' . $display['timezoneafter'] . '" /><br>
    <span class="description">This doesn\'t change the time of the event, it just shows the name of the local timeszone. Set the event timezone in the event editor.</span></td>
    </tr>
    <tr>
    <td colspan="2"><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Save Changes', 'quick-event-manager').'" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="'.__('Reset', 'quick-event-manager').'" onclick="return window.confirm( \' '.__('Are you sure you want to reset the display settings?', 'quick-event-manager').'\' );"/></td>
    </tr>
    </table>
    </form>
    </div>
    <div class="qem-options" style="float:right">
    <h2>'.__('Event List Preview', 'quick-event-manager').'</h2>';
    $atts = array('posts' => '3');
    $content .= qem_event_shortcode($atts,'');
    $content .= '</div></div>';
    echo $content;
}

function qem_styles() {
    if( isset( $_POST['Submit'])) {
        $options = array(
            'use_head',
            'font',
            'font-family',
            'font-size',
            'header-size',
            'header-colour',
            'width',
            'widthtype',
            'event_background',
            'event_backgroundhex',
            'date_colour',
            'date_background',
            'date_backgroundhex',
            'month_background',
            'month_backgroundhex',
            'month_colour',
            'use_custom',
            'custom',
            'date_bold',
            'date_italic',
            'date_border_width',
            'date_border_colour',
            'calender_size',
            'event_border',
            'icon_corners',
            'event_margin',
            'line_margin',
            'use_dayname',
            'use_dayname_inline',
            'iconorder',
            'cat_border',
            'vanilla',
            'vanillawidget','linktocategories','showuncategorised','showkeyabove','showkeybelow','keycaption','showcategory','showcategorycaption'
        );
        foreach ( $options as $item) $style[$item] = stripslashes($_POST[$item]);
        update_option('qem_style', $style);
        qem_create_css_file ('update');
        qem_admin_notice (__('The form styles have been updated.', 'quick-event-manager'));
    }
    if( isset( $_POST['Reset'])) {
        delete_option('qem_style');
        qem_create_css_file ('update');
        qem_admin_notice (__('The style settings have been reset.', 'quick-event-manager'));
    }	
    $style = qem_get_stored_style();
    $$style['font'] = 'checked';
    $$style['widthtype'] = 'checked';
    $$style['background'] = 'checked';
    $$style['event_background'] = 'checked';
    $$style['date_background'] = 'checked'; 
    $$style['month_background'] = 'checked'; 
    $$style['icon_corners'] = 'checked';
    $$style['iconorder'] = 'checked'; 
    $$style['calender_size'] = 'checked'; 
    $content = '<style>'.qem_generate_css().'</style>
    <div class="qem-settings"><div class="qem-options">
    <form method="post" action="">
    <table>
    <tr>
    <td colspan="2"><h2>'.__('Event Width', 'quick-event-manager').'</h2></td></tr>
    <tr>
    <td colspan="2"><input style="margin:0; padding:0; border:none;" type="radio" name="widthtype" value="percent" ' . $percent . ' /> '.__('100% (fill the available space)', 'quick-event-manager').'<br />
    <input style="margin:0; padding:0; border:none;" type="radio" name="widthtype" value="pixel" ' . $pixel . ' /> '.__('Pixel (fixed)', 'quick-event-manager').'<br />
    '.__('Enter the width in pixels:', 'quick-event-manager').' <input type="text" style="width:4em;border:1px solid #415063;" label="width" name="width" value="' . $style['width'] . '" /> '.__('(Just enter the value, no need to add \'px\').', 'quick-event-manager').'</td></tr>
    <tr>
    <td colspan="2"><h2>'.__('Font Options', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td colspan="2"><input style="margin:0; padding:0; border:none" type="radio" name="font" value="theme" ' . $theme . ' /> '.__('Use your theme font styles', 'quick-event-manager').'<br />
	<input style="margin:0; padding:0; border:none" type="radio" name="font" value="plugin" ' . $plugin . ' /> '.__('Use Plugin font styles (enter font family and size below)', 'quick-event-manager').'</td></tr>
    <tr>
    <td>'.__('Font Family:', 'quick-event-manager').'</td>
    <td><input type="text" style="border:1px solid #415063;" label="font-family" name="font-family" value="' . $style['font-family'] . '" /></td></tr>
    <tr>
    <td>'.__('Font Size:', 'quick-event-manager').'</td>
    <td><input type="text" style="width:7em;border:1px solid #415063;" label="font-size" name="font-size" value="' . $style['font-size'] . '" /><br><span class="description">This is the base font size, you can set the sizes of each part of the listing in the Event Settings.</span></td></tr>
    <tr>
    <td>'.__('Header Size:', 'quick-event-manager').'</td>
    <td><input type="text" style="width:7em;border:1px solid #415063;" label="header-size" name="header-size" value="' . $style['header-size'] . '" /> This the size of the title in the event list.</td>
    </tr>
    <tr>
    <td>'.__('Header Colour:', 'quick-event-manager').'</td>
    <td><input type="text" class="qem-color" label="header-colour" name="header-colour" value="' . $style['header-colour'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2"><h2>'.__('Calender Icon', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td>Remove styles</td>
    <td><input type="checkbox" style="margin:0; padding: 0; border: none" name="vanilla"' . $style['vanilla'] . ' value="checked" /> '.__('Do not style the calendar icon', 'quick-event-manager').'<br><input type="checkbox" style="margin:0; padding: 0; border: none" name="vanillawidget"' . $style['vanillawidget'] . ' value="checked" /> '.__('Remove widget styles', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td style="vertical-align:top;">'.__('Size', 'quick-event-manager').'</td>
    <td>
	<input style="margin: 0; padding: 0; border: none;" type="radio" name="calender_size" value="small" ' . $small . ' /> '.__('Small', 'quick-event-manager').' (40px)<br />
	<input style="margin: 0; padding: 0; border: none;" type="radio" name="calender_size" value="medium" ' . $medium . ' /> '.__('Medium', 'quick-event-manager').' (60px)<br />
	<input style="margin: 0; padding: 0; border: none;" type="radio" name="calender_size" value="large" ' . $large . ' /> '.__('Large', 'quick-event-manager').'(80px)</td>
    </tr>
    <tr>
    <td>'.__('Corners', 'quick-event-manager').'</td>
    <td>
    <input style="margin: 0; padding: 0; border: none;" type="radio" name="icon_corners" value="square" ' . $square . ' /> '.__('Square', 'quick-event-manager').'&nbsp;
    <input style="margin: 0; padding: 0; border: none;" type="radio" name="icon_corners" value="rounded" ' . $rounded . ' /> '.__('Rounded', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td>'.__('Border Thickness', 'quick-event-manager').'</td>
    <td><input type="text" style="width:7em;border:1px solid #415063;" label="calendar border" name="date_border_width" value="' . $style['date_border_width'] . '" /> px</td>
    </tr>
    <tr>
    <td>'.__('Border Colour:', 'quick-event-manager').'</td>
    <td><input type="text" class="qem-color" label="calendar border" name="date_border_colour" value="' . $style['date_border_colour'] . '" /></td>
    </tr>
    <tr>
    <td>'.__('Calendar Icon Order', 'quick-event-manager').'</td>
    <td>
    <input style="margin: 0; padding: 0; border: none;" type="radio" name="iconorder" value="default" ' . $default . ' /> '.__('DMY', 'quick-event-manager').'&nbsp;<input style="margin: 0; padding: 0; border: none;" type="radio" name="iconorder" value="month" ' . $month . ' /> '.__('MDY', 'quick-event-manager').'&nbsp;
    <input style="margin: 0; padding: 0; border: none;" type="radio" name="iconorder" value="year" ' . $year . ' /> '.__('YDM', 'quick-event-manager').'&nbsp;
    <input style="margin: 0; padding: 0; border: none;" type="radio" name="iconorder" value="dm" ' . $dm . ' /> '.__('DM', 'quick-event-manager').'&nbsp;<input style="margin: 0; padding: 0; border: none;" type="radio" name="iconorder" value="md" ' . $md . ' /> '.__('MD', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td>Day Name</td>
    <td><input type="checkbox" style="margin:0; padding: 0; border: none" name="use_dayname"' . $style['use_dayname'] . ' value="checked" /> '.__('Show day name', 'quick-event-manager').'<br>
    <input type="checkbox" style="margin:0; padding: 0; border: none" name="use_dayname_inline"' . $style['use_dayname_inline'] . ' value="checked" /> '.__('Show day name inline with date', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td style="vertical-align:top;">'.__('Date Background colour', 'quick-event-manager').'</td>
    <td>
	<input style="margin: 0; padding: 0; border: none;" type="radio" name="date_background" value="grey" ' . $grey . ' /> '.__('Grey', 'quick-event-manager').'<br />
	<input style="margin: 0; padding: 0; border: none;" type="radio" name="date_background" value="red" ' . $red . ' /> '.__('Red', 'quick-event-manager').'<br />
	<input style="margin: 0; padding: 0; border: none;" type="radio" name="date_background" value="color" ' . $color . ' /> '.__('Set your own', 'quick-event-manager').'<br />
    <input type="text" class="qem-color" label="background" name="date_backgroundhex" value="' . $style['date_backgroundhex'] . '" /></td>
    </tr>
	<tr>
    <td>'.__('Date Text Colour', 'quick-event-manager').'</td>
    <td><input type="text" class="qem-color" label="date colour" name="date_colour" value="' . $style['date_colour'] . '" /></td>
    </tr>
    <tr>
    <td style="vertical-align:top;">'.__('Month Background colour', 'quick-event-manager').'</td>
    <td>
	<input style="margin: 0; padding: 0; border: none;" type="radio" name="month_background" value="mwhite" ' . $mwhite . ' /> '.__('White', 'quick-event-manager').'<br />
	<input style="margin: 0; padding: 0; border: none;" type="radio" name="month_background" value="colour" ' . $colour . ' /> '.__('Set your own', 'quick-event-manager').'<br />
    <input type="text" class="qem-color" name="month_backgroundhex" value="' . $style['month_backgroundhex'] . '" /></td>
    </tr>
    <tr>
    <td>'.__('Month Text Colour', 'quick-event-manager').'</td>
    <td><input type="text" class="qem-color" label="month colour" name="month_colour" value="' . $style['month_colour'] . '" /></td>
    </tr>
    <tr>
    <td>'.__('Month Text Style', 'quick-event-manager').'</td>
    <td><input style="margin: 0; padding: 0; border: none;" type="checkbox" name="date_bold" value="checked" ' . $style['date_bold'] . ' /> '.__('Bold', 'quick-event-manager').'&nbsp;
	<input style="margin: 0; padding: 0; border: none;" type="checkbox" name="date_italic" value="checked" ' . $style['date_italic'] . ' /> '.__('Italic', 'quick-event-manager').'</td>
    </tr>
	<tr>
    <td colspan="2"><h2>'.__('Event Content', 'quick-event-manager').'</h2></td>
    </tr>
	<tr>
    <td style="vertical-align:top;">'.__('Event Border', 'quick-event-manager').'</td>
    <td><input type="checkbox" style="margin:0; padding: 0; border: none" name="event_border"' . $style['event_border'] . ' value="checked" /> '.__('Add a border to the event post', 'quick-event-manager').'<br /><span class="description">'.__('Thickness and colour will be the same as the calendar icon.', 'quick-event-manager').'</span></td>
    </tr>
    <tr>
    <td style="vertical-align:top;">'.__('Event Background Colour', 'quick-event-manager').'</td>
    <td><input style="margin:0; padding:0; border:none;" type="radio" name="event_background" value="bgwhite" ' . $bgwhite . ' /> '.__('White', 'quick-event-manager').'<br />
	<input style="margin:0; padding:0; border:none;" type="radio" name="event_background" value="bgtheme" ' . $bgtheme . ' /> '.__('Use theme colours', 'quick-event-manager').'<br />
	<input style="margin:0; padding:0; border:none;" type="radio" name="event_background" value="bgcolor" ' . $bgcolor . ' /> '.__('Set your own', 'quick-event-manager').'<br />
	<input type="text" class="qem-color" label="background" name="event_backgroundhex" value="' . $style['event_backgroundhex'] . '" /></td>
    </tr>
    <tr>
    <td style="vertical-align:top;">Margins and Padding</td>
    <td><span class="description">Set the margins and padding of each bit using CSS shortcodes:</span><br><input type="text" label="line margin" name="line_margin" value="' . $style['line_margin'] . '" /></td>
    </tr>
    <tr>
    <td style="vertical-align:top;">Event Margin</td>
    <td><span class="description">Set the margin or each event using CSS shortcodes:</span><br>
    <input type="text" label="margin" name="event_margin" value="' . $style['event_margin'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2"><h2>'.__('Categories', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td width="30%">Display category key</td><td><input type="checkbox" style="margin:0; padding: 0; border: none" name="showkeyabove" ' . $style['showkeyabove'] . ' value="checked" /> '.__('Show above event list', 'quick-event-manager').'<br>
    <input type="checkbox" style="margin:0; padding: 0; border: none" name="showkeybelow" ' . $style['showkeybelow'] . ' value="checked" /> '.__('Show below event list', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="30%">Caption</td>
    <td><input type="text" style="border:1px solid #415063;" label="text" name="keycaption" value="' . $style['keycaption'] . '" /></td>
    </tr>
    <tr>
    <td style="vertical-align:top;">'.__('Category Colours', 'quick-event-manager').'</td><td><input type="checkbox" style="margin:0; padding: 0; border: none" name="cat_border"' . $style['cat_border'] . ' value="checked" /> '.__('Use category colours for the event border', 'quick-event-manager').'<br />
    <span class="description">'.__('Options are set on the <a href="?page=quick-event-manager/settings.php&tab=calendar">Calendar Settings</a> page.', 'quick-event-manager').'</span></td>
    </tr>
    <tr>
    <td width="30%"></td><td><input type="checkbox" style="margin:0; padding: 0; border: none" name="showcategory" ' . $style['showcategory'] . ' value="checked" /> '.__('Show name of current category', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="30%"></td>
    <td><input type="text" style="border:1px solid #415063;" label="text" name="showcategorycaption" value="' . $style['showcategorycaption'] . '" /></td>
    </tr>
    <tr>
    <td>Linking</td><td><input type="checkbox" style="margin:0; padding: 0; border: none" name="linktocategories" ' . $style['linktocategories'] . ' value="checked" /> '.__('Link keys to categories', 'quick-event-manager').'<br>
    <input type="checkbox" style="margin:0; padding: 0; border: none" name="showuncategorised" ' . $style['showuncategorised'] . ' value="checked" /> '.__('Show uncategorised key', 'quick-event-manager').'</td>
    </tr>
    </table>
    <h2>'.__('Custom CSS', 'quick-event-manager').'</h2>
    <p><input type="checkbox" style="margin:0; padding: 0; border: none" name="use_head"' . $style['use_head'] . ' value="checked" /> '.__('Add styles to document head', 'quick-event-manager').'. '.__('Use this option if you are unable to created a stylesheet for the plugin', 'quick-event-manager').'</p>
    <p><input type="checkbox" style="margin:0; padding: 0; border: none" name="use_custom"' . $style['use_custom'] . ' value="checked" /> '.__('Use Custom CSS', 'quick-event-manager').'</p>
    <p><textarea style="width:100%;height:100px;border:1px solid #415063;" name="custom">' . $style['custom'] . '</textarea></p>
    <p>'.__('To see all the styling use the <a href="plugin-editor.php?file=quick-event-manager/quick-event-manager.css">CSS editor</a>.', 'quick-event-manager').'</p>
    <p>'.__('The main style wrapper is the <code>.qem</code> class.', 'quick-event-manager').'</p>
    <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Save Changes', 'quick-event-manager').'" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="'.__('Reset', 'quick-event-manager').'" onclick="return window.confirm( \''.__('Are you sure you want to reset the style settings?', 'quick-event-manager').'\' );"/></p>
    </form>
    </div>
    </div>
    <div class="qem-options" style="float:right">
    <h2>'.__('Event List Preview', 'quick-event-manager').'</h2>
    <p>'.__('Check the event list in your site as the Wordpress Dashboard can do funny things with styles', 'quick-event-manager').'</p>';
    $atts = array('posts' => '3');
    $content .= qem_event_shortcode($atts,'');
    $content .= '</div>';
    echo $content;
}

function qem_calendar() {
    if( isset( $_POST['Submit'])) {
        $options = array(
            'calday',
            'caldaytext',
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
            'startday',
            'eventlength',
            'archive',
            'archivelinks',
            'cata',
            'catatext',
            'cataback',
            'catb',
            'catbtext',
            'catbback',
            'catc',
            'catctext',
            'catcback',
            'catd',
            'catdtext',
            'catdback',
            'cate',
            'catetext',
            'cateback',
            'catf',
            'catftext',
            'catfback',
            'smallicon',
            'unicode',
            'eventbold',
            'eventitalic',
            'eventbackground',
            'eventtext',
            'eventborder',
            'showmultiple',
            'keycaption',
            'showkeyabove',
            'showkeybelow',
            'prevmonth',
            'nextmonth',
            'navicon',
            'leftunicode',
            'rightunicode',
            'linktocategories',
            'showuncategorised'
        );
        foreach ( $options as $item) $cal[$item] = stripslashes($_POST[$item]);
        update_option('qem_calendar', $cal);
        qem_create_css_file ('update');
        qem_admin_notice (__('The calendar settings have been updated.', 'quick-event-manager'));
    }
    if( isset( $_POST['Reset'])) {
        delete_option('qem_calendar');
        qem_create_css_file ('update');
        qem_admin_notice (__('The calendar settings have been reset.', 'quick-event-manager'));
    }
    $calendar = qem_get_stored_calendar();
    $$calendar['eventlink'] = 'checked';
    $$calendar['startday'] = 'checked';
    $$calendar['smallicon'] = 'checked';
    $$calendar['navicon'] = 'checked';
    if ($cal['navicon'] == 'arrows') {
        $leftnavicon = '&#9668; ';
        $rightnavicon = ' &#9658;';
    }
    if ($cal['navicon'] == 'unicodes') {
        $leftnavicon = $cal['leftunicode'].' ';
        $rightnavicon = ' '.$cal['rightunicode'];
    }
    $content = '<style>'.qem_generate_css().'</style> 
    <div class="qem-settings"><div class="qem-options">
    <h2>Using the Calendar</h2>
    <p>'.__('To add the calendar to your site use the shortcode: <code>[qemcalendar]</code>.', 'quick-event-manager').'</p>
    <form method="post" action="">
    <table width="100%">
    <tr>
    <td colspan="2"><h2>'.__('General Settings', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td style="vertical-align:top;">Linking to Events</td>
    <td><input style="margin:0; padding:0; border:none;" type="radio" name="eventlink" value="linkpopup" ' . $linkpopup . ' /> '.__('Link opens event summary in a popup', 'quick-event-manager').'<br />
    <input style="margin:0; padding:0; border:none;" type="radio" name="eventlink" value="linkpage" ' . $linkpage . ' /> '.__('Link opens event page' ,'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="30%">Old Events</td>
    <td><input type="checkbox" style="margin:0; padding: 0; border: none" name="archive" ' . $calendar['archive'] . ' value="checked" /> '.__('Show past events in the calendar.', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="30%">Linking Calendar to the Event List</td>
    <td><input type="checkbox" style="margin:0; padding: 0; border: none" name="connect"' . $calendar['connect'] . ' value="checked" /> '.__('Link Event List to Calendar Page).', 'quick-event-manager').'<br>
    <span class="description">'.__('You will need to create pages for the calendar and the event list).', 'quick-event-manager').'</span>
    </td>
    </tr>
    <tr>
    <td width="30%">'.__('Calendar link text', 'quick-event-manager').'</td><td><input type="text" style="border:1px solid #415063;" label="calendar_text" name="calendar_text" value="' . $calendar['calendar_text'] . '" /></td></tr>
    <tr><td width="30%">'.__('Calendar page URL', 'quick-event-manager').'</td><td><input type="text" style="border:1px solid #415063;" label="calendar_url" name="calendar_url" value="' . $calendar['calendar_url'] . '" /></td></tr>
    <tr><td width="30%">'.__('Event list link text', 'quick-event-manager').'</td><td><input type="text" style="border:1px solid #415063;" label="eventlist_text" name="eventlist_text" value="' . $calendar['eventlist_text'] . '" /></td></tr>
    <tr><td width="30%">'.__('Event list page', 'quick-event-manager').' URL</td><td><input type="text" style="border:1px solid #415063;" label="eventlist_url" name="eventlist_url" value="' . $calendar['eventlist_url'] . '" /></td></tr>
    <tr><td width="30%">Navigation</td><td><input type="text" style="border:1px solid #415063;width:50%;" label="text" name="prevmonth" value="' . $calendar['prevmonth'] . '" /><input type="text" style="text-align:right;border:1px solid #415063;width:50%;" label="text" name="nextmonth" value="' . $calendar['nextmonth'] . '" /></td>
    </tr>
    <tr>
    <td width="30%">Navigation Icons</td>
    <td>
    <input style="margin:0; padding:0; border:none;" type="radio" name="navicon" value="none" ' . $none . ' /> '.__('None', 'quick-event-manager').' 
    <input style="margin:0; padding:0; border:none;" type="radio" name="navicon" value="arrows" ' . $arrows . ' /> &#9668; &#9658; 
    <input style="margin:0; padding:0; border:none;" type="radio" name="navicon" value="unicodes" ' . $unicodes . ' />'.__('Other (enter <a href="http://character-code.com/arrows-html-codes.php" target="_blank">hex code</a> below)', 'quick-event-manager').'<br />
    Left: <input type="text" style="border:1px solid #415063;width:6em;" label="text" name="leftunicode" value="' . $calendar['leftunicode'] . '" /> Right: <input type="text" style="border:1px solid #415063;width:6em;" label="text" name="rightunicode" value="' . $calendar['rightunicode'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2"><h2>'.__('Event Options', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td width="30%">Multi-day Events</td><td><input type="checkbox" style="margin:0; padding: 0; border: none" name="showmultiple" ' . $calendar['showmultiple'] . ' value="checked" /> '.__('Show event on all days', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="30%">Character Number</td><td><input type="text" style="border:1px solid #415063;width:4em;" label="text" name="eventlength" value="' . $calendar['eventlength'] . '" /><span class="description"> Number of characters to display in event box</span></td>
    </tr>
    <tr>
    <td style="vertical-align:top;">Small Screens</td><td><span class="description">What to display on small screens:</span><br>
    <input style="margin:0; padding:0; border:none;" type="radio" name="smallicon" value="trim" ' . $trim . ' /> '.__('Full message', 'quick-event-manager').' <input style="margin:0; padding:0; border:none;" type="radio" name="smallicon" value="arrow" ' . $arrow . ' /> '.__('&#9654;', 'quick-event-manager').' <input style="margin:0; padding:0; border:none;" type="radio" name="smallicon" value="box" ' . $box . ' /> '.__('&#9633;', 'quick-event-manager').' <input style="margin:0; padding:0; border:none;" type="radio" name="smallicon" value="square" ' . $square . ' /> '.__('&#9632;', 'quick-event-manager').' <input style="margin:0; padding:0; border:none;" type="radio" name="smallicon" value="asterix" ' . $asterix . ' /> '.__('&#9733;', 'quick-event-manager').' 
    <input style="margin:0; padding:0; border:none;" type="radio" name="smallicon" value="blank" ' . $blank . ' /> '.__('Blank', 'quick-event-manager').' 
    <input style="margin:0; padding:0; border:none;" type="radio" name="smallicon" value="other" ' . $other . ' /> '.__('Other (enter escaped <a href="http://www.fileformat.info/info/unicode/char/search.htm" target="blank">unicode</a> or hex code below)', 'quick-event-manager').'<br />
    <input type="text" style="border:1px solid #415063;width:6em;" label="text" name="unicode" value="' . $calendar['unicode'] . '" /></td>
    </tr>		
    <tr><td width="30%">'.__('Background', 'quick-event-manager').'</td><td><input type="text" class="qem-color" label="background" name="eventbackground" value="' . $calendar['eventbackground'] . '" /><br><span class="description">Select clear to use day colour</span></td>
    </tr>
    <tr>
    <td width="30%">'.__('Text', 'quick-event-manager').'</td><td><input type="text" class="qem-color" label="text" name="eventtext" value="' . $calendar['eventtext'] . '" /></td>
    </tr>
    <tr>
    <td width="30%">Text Styles</td><td><input type="checkbox" style="margin:0; padding: 0; border: none" name="eventbold" ' . $calendar['eventbold'] . ' value="checked" /> '.__('Bold', 'quick-event-manager').'<input type="checkbox" style="margin:0; padding: 0; border: none" name="eventitalic" ' . $calendar['eventitalic'] . ' value="checked" /> '.__('Italic', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="30%">'.__('Event Hover', 'quick-event-manager').'</td><td><input type="text" class="qem-color" label="background" name="eventhover" value="' . $calendar['eventhover'] . '" /></td>
    </tr>
    <tr>
    <td width="30%">Event Border</td><td><input type="text" style="width:12em;border:1px solid #415063;" label="eventborder" name="eventborder" value="' . $calendar['eventborder'] . '" /> enter \'none\' to remove border</td>
    </tr>
    </table>
    <h2>'.__('Calendar Colours', 'quick-event-manager').'</h2>
    <div class="qem-calcolor">
    <p style="font-weight:bold"><span style="float:left;width:10em;">'.__('Items', 'quick-event-manager').'</span>'.__('Background', 'quick-event-manager').' / '.__('Text', 'quick-event-manager').'</p>
    <p><span style="float:left;width:10em">'.__('Days of the Week', 'quick-event-manager').'</span>&nbsp;<input type="text" class="qem-color" label="background" name="calday" value="' . $calendar['calday'] . '" /><input type="text" class="qem-color" label="text" name="caldaytext" value="' . $calendar['caldaytext'] . '" /></p>
    <p><span style="float:left;width:10em">'.__('Normal Day', 'quick-event-manager').'</span>&nbsp;<input type="text" class="qem-color" label="background" name="day" value="' . $calendar['day'] . '" /></p>
    <p><span style="float:left;width:10em">'.__('Event Day', 'quick-event-manager').'</span>&nbsp;<input type="text" class="qem-color" label="background" name="eventday" value="' . $calendar['eventday'] . '" /><input type="text" class="qem-color" label="text" name="eventdaytext" value="' . $calendar['eventdaytext'] . '" /></p>
    <p><span style="float:left;width:10em">'.__('Past Day', 'quick-event-manager').'</span>&nbsp;<input type="text" class="qem-color" label="background" name="oldday" value="' . $calendar['oldday'] . '" /></p>
    </div>
    <h2>'.__('Event Category Colours', 'quick-event-manager').'</h2>
    <p style="font-weight:bold"><span style="float:left;width:8em;">'.__('Category', 'quick-event-manager').'</span>'.__('Background', 'quick-event-manager').' / '.__('Text', 'quick-event-manager').'</p>
    <div class="qem-calcolor">';
    $content .= '<p>'.qem_categories ('cata',$calendar['cata']).'&nbsp;
    <input type="text" class="qem-color" label="cataback" name="cataback" value="' . $calendar['cataback'] . '" />&nbsp;
    <input type="text" class="qem-color" label="catatext" name="catatext" value="' . $calendar['catatext'] . '" /></p>
    <p>'.qem_categories ('catb',$calendar['catb']).'&nbsp;
    <input type="text" class="qem-color" label="catbback" name="catbback" value="' . $calendar['catbback'] . '" />&nbsp;
    <input type="text" class="qem-color" label="catbtext" name="catbtext" value="' . $calendar['catbtext'] . '" /></p>
    <p>'.qem_categories ('catc',$calendar['catc']).'&nbsp;
    <input type="text" class="qem-color" label="catcback" name="catcback" value="' . $calendar['catcback'] . '" />&nbsp;
    <input type="text" class="qem-color" label="catctext" name="catctext" value="' . $calendar['catctext'] . '" /></p>
    <p>'.qem_categories ('catd',$calendar['catd']).'&nbsp;
    <input type="text" class="qem-color" label="catdback" name="catdback" value="' . $calendar['catdback'] . '" />&nbsp;
    <input type="text" class="qem-color" label="catdtext" name="catdtext" value="' . $calendar['catdtext'] . '" /></p>
    <p>'.qem_categories ('cate',$calendar['cate']).'&nbsp;
    <input type="text" class="qem-color" label="cateback" name="cateback" value="' . $calendar['cateback'] . '" />&nbsp;
    <input type="text" class="qem-color" label="catetext" name="catetext" value="' . $calendar['catetext'] . '" /></p>
    <p>'.qem_categories ('catf',$calendar['catf']).'&nbsp;
    <input type="text" class="qem-color" label="catfback" name="catfback" value="' . $calendar['catfback'] . '" />&nbsp;
    <input type="text" class="qem-color" label="catftext" name="catftext" value="' . $calendar['catftext'] . '" /></p>
    </div>
    <table width="100%">
    <tr>
    <td width="30%">Display category key</td><td><input type="checkbox" style="margin:0; padding: 0; border: none" name="showkeyabove" ' . $calendar['showkeyabove'] . ' value="checked" /> '.__('Show above calendar', 'quick-event-manager').'<br>
    <input type="checkbox" style="margin:0; padding: 0; border: none" name="showkeybelow" ' . $calendar['showkeybelow'] . ' value="checked" /> '.__('Show below calendar', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="30%">Caption:</td>
    <td><input type="text" style="border:1px solid #415063;" label="text" name="keycaption" value="' . $calendar['keycaption'] . '" /></td>
    </tr>
    <tr>
    <td width="30%"></td><td><input type="checkbox" style="margin:0; padding: 0; border: none" name="linktocategories" ' . $calendar['linktocategories'] . ' value="checked" /> '.__('Link keys to categories', 'quick-event-manager').'<br>
    <input type="checkbox" style="margin:0; padding: 0; border: none" name="showuncategorised" ' . $calendar['showuncategorised'] . ' value="checked" /> '.__('Show uncategorised key', 'quick-event-manager').'</td>
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
	$content .= qem_show_calendar('');
	$content .= '</div></div>';
	echo $content;
}

function qem_categories ($catxxx,$thecat) {
    $arr = get_categories();
    $content .= '<select name="'.$catxxx.'" style="width:8em;">';
    $content .= '<option value=""></option>';
    foreach($arr as $option){
        if ($thecat == $option->slug) $selected = 'selected'; else $selected = '';
        $content .= '<option value="'.$option->slug.'" '.$selected.'>'.$option->name.'</option>';
    }
    $content .= '</select>';
    return $content;
}

function qem_register (){
    if( isset( $_POST['Submit'])) {
        $options = array(
            'useform',
            'notarchive',
            'useqpp',
            'usename',
            'usemail',
            'usetelephone',
            'useplaces',
            'usemessage',
            'useattend',
            'usecaptcha',
            'useblank1',
            'useblank2',
            'usedropdown',
            'usenumber1',
            'reqname',
            'reqmail',
            'reqtelephone',
            'reqmessage',
            'reqblank1',
            'reqblank2',
            'reqdropdown',
            'reqnumber1',
            'formborder',
            'sendemail',
            'subject',
            'subjecttitle',
            'subjectdate',
            'title',
            'blurb',
            'yourname',
            'youremail',
            'yourtelephone',
            'yourplaces',
            'yourmessage',
            'yourcaptcha',
            'yourattend',
            'yourblank1',
            'yourblank2',
            'yourdropdown',
            'yournumber1',
            'qemsubmit',
            'error',
            'replytitle',
            'replyblurb',
            'whoscoming',
            'whosavatar',
            'whoscomingmessage',
            'placesbefore',
            'placesafter',
            'eventfull',
            'eventfullmessage',
            'eventlist',
            'showuser',
            'linkback',
            'sendcopy',
            'usecopy',
            'copyblurb',
            'alreadyregistered',
            'sort',
            'registeredusers',
            'paypal',
            'paypalemail',
            'currency',
            'useprocess',
            'processtype',
            'processpercent',
            'processfixed',
            'paypalmessage'
        );
        foreach ($options as $item) $register[$item] = stripslashes( $_POST[$item]);
        update_option('qem_register', $register);
        qem_create_css_file ('update');
        qem_admin_notice(__('The registration form settings have been updated.', 'quick-event-manager'));
    }
    if( isset( $_POST['Reset'])) {
        delete_option('qem_register');
        qem_admin_notice(__('The registration form settings have been reset.', 'quick-event-manager'));
    }
    if( isset( $_POST['Validate'])) {
        $apikey = $_POST['qem_apikey'];
        $blogurl = get_site_url();
        $akismet = new qem_akismet($blogurl, $apikey);
        if($akismet->isKeyValid()) {
            qem_admin_notice("Valid Akismet API Key. All messages will now be checked against the Akismet database.");update_option('qem-akismet', $apikey);
        } else qcf_admin_notice("Your Akismet API Key is not Valid");
    }
    if( isset( $_POST['Delete'])) {
        delete_option('qem-akismet');
        qcf_admin_notice("Akismet validation is no longer active on the Quick Event Manager");
    }
    
    $register = qem_get_stored_register();
    $$register['processtype'] = 'checked';
    $content = '<div class="qem-settings"><div class="qem-options">
    <form id="" method="post" action="">
    <table width="100%">
    <tr>
    <td colspan="3"><h2>'.__('General Settings', 'quick-event-manager').'</h2></td></tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="useform"' . $register['useform'] . ' value="checked" /></td>
    <td colspan="2">'.__('Add a registration form to ALL your events', 'quick-event-manager').'<br>
    <span class="description">To add a registration form to individual events use the event editor.</span></td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="notarchive"' . $register['notarchive'] . ' value="checked" /></td>
    <td colspan="2">'.__('Do not display registration form on old events', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="formborder"' . $register['formborder'] . ' value="checked" /></td>
    <td colspan="2">'.__('Add a border to the form', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="showuser"' . $register['showuser'] . ' value="checked" /></td>
    <td colspan="2">'.__('Pre-fill user name if logged in', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="registeredusers"' . $register['registeredusers'] . ' value="checked" /></td>
    <td colspan="2">'.__('Only users who have logged in can register', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td colspan="2">'.__('Your Email Address', 'quick-event-manager').'</td>
    <td><input type="text" style="border:1px solid #415063;" name="sendemail" value="' . $register['sendemail'] . '" /><br><span class="description">This is where registration notifications will be sent.</span></td>
    </tr>
    <tr>
    <td colspan="3"><h2>'.__('Registration Form', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td colspan="2">'.__('Form title', 'quick-event-manager').'</td>
    <td><input type="text" style="border:1px solid #415063;" name="title" value="' . $register['title'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2">'.__('Form blurb', 'quick-event-manager').'</td>
    <td><input type="text" style="border:1px solid #415063;" name="blurb" value="' . $register['blurb'] . '" /></td>
    </tr>
    <td colspan="2">'.__('Submit Button', 'quick-event-manager').'</td>
    <td><input type="text" style="border:1px solid #415063;" name="qemsubmit" value="' . $register['qemsubmit'] . '" /></td>
    </tr>
    </table>
    <p>Check those fields you want to use. Drag and drop to change the order.</p>
    <style>table#sorting{width:100%;}
    #sorting tbody tr{outline: 1px solid #888;background:#E0E0E0;}
    #sorting tbody td{padding: 2px;vertical-align:middle;}
    #sorting{border-collapse:separate;border-spacing:0 5px;}</style>
    <script>
    jQuery(function() 
    {var qem_rsort = jQuery( "#qem_rsort" ).sortable(
    {axis: "y",cursor: "move",opacity:0.8,update:function(e,ui)
    {var order = qem_rsort.sortable("toArray").join();jQuery("#qem_register_sort").val(order);}});});
    </script>
    <table id="sorting">
    <thead>
    <tr>
    <th width="5%">U</th>
    <th width="5%">R</th>
    <th width="20%">'.__('Field', 'quick-event-manager').'</th>
    <th>'.__('Label', 'quick-event-manager').'</th>
    </tr>
    </thead><tbody id="qem_rsort">';
    $sort = explode(",", $register['sort']);
    foreach ($sort as $name) {
        switch ( $name ) {
            case 'field1':
            $use = 'usename';
            $req = 'reqname';
            $label = __('Name', 'quick-event-manager');
            $input = 'yourname';
            break;
            case 'field2':
            $use = 'usemail';
            $req = 'reqmail';
            $label = __('Email', 'quick-event-manager');
            $input = 'youremail';
            break;
            case 'field3':
            $use = 'useattend';
            $req = '';
            $label = __('Not Attending', 'quick-event-manager');
            $input = 'yourattend';
            break;
            case 'field4':
            $use = 'usetelephone';
            $req = 'reqtelephone';
            $label = __('Telephone', 'quick-event-manager');
            $input = 'yourtelephone';
            break;
            case 'field5':
            $use = 'useplaces';
            $req = '';
            $label = __('Places', 'quick-event-manager');
            $input = 'yourplaces';
            break;
            case 'field6':
            $use = 'usemessage';
            $req = 'reqmessage';
            $label = __('Message', 'quick-event-manager');
            $input = 'yourmessage';
            break;
            case 'field7':
            $use = 'usecaptcha';
            $req = '';
            $label = __('Captcha', 'quick-event-manager');
            $input = 'Displays a simple maths captcha to confuse the spammers.';
            break;
            case 'field8':
            $use = 'usecopy';
            $req = '';
            $label = __('Copy Message', 'quick-event-manager');
            $input = 'copyblurb';
            break;
            case 'field9':
            $use = 'useblank1';
            $req = 'reqblank1';
            $label = __('User defined', 'quick-event-manager');
            $input = 'yourblank1';
            break;
            case 'field10':
            $use = 'useblank2';
            $req = 'reqblank2';
            $label = __('User defined', 'quick-event-manager');
            $input = 'yourblank2';
            break;
            case 'field11':
            $use = 'usedropdown';
            $req = 'reqdropdown';
            $label = __('Dropdown', 'quick-event-manager');
            $input = 'yourdropdown';
            break;
            case 'field12':
            $use = 'usenumber1';
            $req = 'reqnumber1';
            $label = __('Number', 'quick-event-manager');
            $input = 'yournumber1';
            break;
        }
        $content .= '<tr id="'.$name.'">
        <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="'.$use.'"' . $register[$use] . ' value="checked" /></td>
        <td width="5%">';
        if ($req) $content .= '<input type="checkbox" style="margin:0; padding: 0; border: none" name="'.$req.'"' . $register[$req] . ' value="checked" />';
        $content .= '</td><td width="20%">'.$label.'</td><td>';
        if ($name=='field7') $content .= $input;
        else $content .= '<input type="text" style="border:1px solid #415063;" name="'.$input.'" value="' . $register[$input] . '" />';
        $content .= '</td></tr>';
    }
    $content .='</tbody>
    </table>
    <input type="hidden" id="qem_register_sort" name="sort" value="'.$register['sort'].'" />
    <table>
    <tr>
    <td colspan="3"><h2>'.__('Error and Thank-you messages', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td colspan="2">'.__('Error Message', 'quick-event-manager').'</td>
    <td><input type="text" style="border:1px solid #415063;" name="error" value="' . $register['error'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2">'.__('Already Registered', 'quick-event-manager').'</td>
    <td><input type="text" style="border:1px solid #415063;" name="alreadyregistered" value="' . $register['alreadyregistered'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2">'.__('Thank you message title', 'quick-event-manager').'</td>
    <td><input type="text" style="border:1px solid #415063;" name="replytitle" value="' . $register['replytitle'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2">'.__('Thank you message blurb', 'quick-event-manager').'</td>
    <td><textarea style="width:100%;height:100px;border:1px solid #415063;" name="replyblurb">' . $register['replyblurb'] . '</textarea></td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="sendcopy"' . $register['sendcopy'] . ' value="checked" /></td>
    <td width="20%">'.__('Confirmation', 'quick-event-manager').'</td>
    <td>Send a copy of the registration details message to the registrant. This is an automated message, if you want the visitor to choose to get a message use the form option above.</td>
    </tr>
    <tr>
    <td colspan="3"><h2>'.__('Email Subject', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td colspan="2">'.__('Subject', 'quick-event-manager').'</td>
    <td><input type="text" style="border:1px solid #415063;" name="subject" value="' . $register['subject'] . '" /></td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="subjecttitle"' . $register['subjecttitle'] . ' value="checked" /></td>
    <td colspan="3">'.__('Show event title', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="subjectdate"' . $register['subjectdate'] . ' value="checked" /></td>
    <td colspan="3">'.__('Show date', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td colspan="3"><h2>'.__('Show Attendees', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="whoscoming"' . $register['whoscoming'] . ' value="checked" /></td>
    <td colspan="2">'.__('List attendees', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="whosavatar"' . $register['whosavatar'] . ' value="checked" /></td>
    <td colspan="2">'.__('Show avatars', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td colspan="2">'.__('Message', 'quick-event-manager').'</td>
    <td><input type="text" style="border:1px solid #415063;" name="whoscomingmessage" value="' . $register['whoscomingmessage'] . '" /></td>
    </tr>
    <tr>
    <td colspan="3"><h2>'.__('Places Available Counter', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td colspan="3">'.__('Show how many places are left for an event.', 'quick-event-manager').' '.__('Set the number of places in the event editor.', 'quick-event-manager').'<br>
    <input type="text" style="width:40%;border:1px solid #415063;" name="placesbefore" value="' . $register['placesbefore'] . '" /> {number} <input type="text" style="width:40%;border:1px solid #415063;" name="placesafter" value="' . $register['placesafter'] . '" />
    </td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="eventlist"' . $register['eventlist'] . ' value="checked" /></td>
    <td colspan="2">Show places available on event list - this only works if you have selected \'Add an attendee counter to this form\' on the event editor.</td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="eventfull"' . $register['eventfull'] . ' value="checked" /></td>
    <td colspan="2">Hide registration form when event is full</td>
    </tr> 
    <tr>
    <td colspan="2">Message to display:</td>
    <td><input type="text" style="border:1px solid #415063;" name="eventfullmessage" value="' . $register['eventfullmessage'] . '" /></td>
    </tr>
    <tr>
    <td colspan="3"><h2>'.__('Payments', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="paypal"' . $register['paypal'] . ' value="checked" /></td>
    <td colspan="2">Link to paypal on registration.<br><span class="description">This only works if you have a simple cost for your event.</span></td>
    </tr>
    <tr>
    <td colspan="2">'.__('Your PayPal Email Address', 'quick-event-manager').'</td>
    <td><input type="text" style="border:1px solid #415063;" name="paypalemail" value="' . $register['paypalemail'] . '" /></td>
    </tr>
    <tr>
    <td colspan="3">Enter currency code: <input type="text" style="width:3em" label="new_curr" name="currency" value="'.$register['currency'].'" />&nbsp;(For example: GBP, USD, EUR)<br>
    <span class="description">Allowed Paypal Currency codes are given <a href="https://developer.paypal.com/webapps/developer/docs/classic/api/currency_codes/" target="blank">here</a>.</span><td>
</tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="useprocess"' . $register['useprocess'] . ' value="checked" /></td>
    <td colspan="2">Add processing fee</td>
    </tr>
    <tr>
    <td colspan="3">
    <input style="margin:0; padding:0; border:none;" type="radio" name="processtype" value="processpercent" ' . $processpercent . ' /> Percentage of the total: <input type="text" style="width:4em;padding:2px" label="processpercent" name="processpercent" value="' . $register['processpercent'] . '" /> %<br>
    <input style="margin:0; padding:0; border:none;" type="radio" name="processtype" value="processfixed" ' . $processfixed . ' /> Fixed amount: <input type="text" style="width:4em;padding:2px" label="processfixed" name="processfixed" value="' . $register['processfixed'] . '" /> '.$register['currency'].'</td>
    </tr>
    <tr>
    <td colspan="2">'.__('Submit Label', 'quick-event-manager').'</td>
    <td><input type="text" style="border:1px solid #415063;" name="qempaypalsubmit" value="' . $register['qempaypalsubmit'] . '" /></td>
    </tr>
    </table>
    <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Save Changes', 'quick-event-manager').'" />
    <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="'.__('Reset', 'quick-event-manager').'" onclick="return window.confirm( \''.__('Are you sure you want to reset the registration form?', 'quick-event-manager').'\' );"/></p>
    <h2>Use Akismet Validation</h2>
    <p>Enter your API Key to check all messages against the Akismet database.</p> 
    <p><input type="text" label="akismet" name="qem_apikey" value="'.$qem_apikey.'" /></p>
    <p><input type="submit" name="Validate" class="button-primary" style="color: #FFF;" value="Activate Akismet Validation" /> <input type="submit" name="Delete" class="button-secondary" value="Deactivate Aksimet Validation" onclick="return window.confirm( \'This will delete the Akismet Key.\nAre you sure you want to do this?\' );"/></p>
    </form>
    </div>
    <div class="qem-options" style="float:right">
    <h2>'.__('Example form', 'quick-event-manager').'</h2>
    <p>'.__('This is an example of the form. When it appears on your site it will use your theme styles.', 'quick-event-manager').'</p>';
    $content .= qem_loop();
	$content .= '</div></div>';
	echo $content;		
}

function qem_payment (){
    if( isset( $_POST['Submit'])) {
        $options = array('useqpp','qppform','qppcost','qppcounter');
        foreach ($options as $item) $payment[$item] = stripslashes( $_POST[$item]);
        update_option('qem_payment', $payment);
        qem_admin_notice(__('The payment form settings have been updated.', 'quick-event-manager'));
    }
    if( isset( $_POST['Reset'])) {
        delete_option('qem_payment');
        qem_admin_notice(__('The payment form settings have been reset.', 'quick-event-manager'));
    }
    $payment = qem_get_stored_payment();
    $$payment['useqpp'] = 'checked';
    $content = '<div class="qem-settings"><div class="qem-options">
    <form id="" method="post" action="">';
    if (function_exists('qpp_loop')) {
        $content .= '<p>The registration form has a built in option for simple payments. If you need more options you can add a payment form to your events.</p>
        <p><input type="checkbox" style="margin:0; padding: 0; border: none" name="useqpp" ' . $useqpp . ' value="useqpp" /> '.__('Add a payment form to ALL your events.', 'quick-event-manager').'<br>
        <input type="checkbox" style="margin:0; padding: 0; border: none" name="qppcost" ' . $payment['qppcost'] . ' value="checked" /> '.__('Only add a payment form to events with a cost.', 'quick-event-manager').'</p>
        <p class="description">To add a payment form to individual events use the event editor.</p>';
        $qpp_setup = qpp_get_stored_setup();
        if ($qpp_setup['alternative']) {
            $content .= '<p>Select the form you want to use on your events:</p><p>';
            $arr = explode(",",$qpp_setup['alternative']);
            foreach ($arr as $item) {
                $formname = ($item == '' ? 'default' : $item);
                $checked = ($payment['qppform'] == $item ? 'checked' :  '');
                $content .='<input style="margin:0; padding:0; border:none" type="radio" name="qppform" value="' .$item . '" ' .$checked . ' /> '.$formname.'<br>';
            }
            $content .= '</p>';
        }
        $content .='<p><input type="checkbox" name="qppcounter" value="checked" '.$payment['qppcouter'].'> Use payment quantity in the event counter.</p>
        <p>The payment form uses the event name as the reference and the event cost as the amount to pay. If the event cost is left blank, the visitor will need to fill in the amount themsleves (unless you have set the checkbox box above). All other options are set in the <a href="options-general.php?page=quick-paypal-payments/settings.php">payment plugin setting pages</a>.</p>
        <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Save Changes', 'quick-event-manager').'" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="'.__('Reset', 'quick-event-manager').'" onclick="return window.confirm( \''.__('Are you sure you want to reset the registration form?', 'quick-event-manager').'\' );"/></p>
        </form></div>
        <div class="qem-options" style="float:right">
        <h2>'.__('Example Payment Form', 'quick-event-manager').'</h2>
        <p>'.__('This is an example of the form. When it appears on your site it will use your theme styles and <a href="options-general.php?page=quick-paypal-payments/settings.php">your form settings</a>', 'quick-event-manager').'</p>';
        $atts = array('form'=>$payment['qppform'],'id'=>'The Event','amount'=>'£10');
        $content .= qpp_loop($atts);
        $content .= '</div></div>';
    } else {
        $content = '<div class="qem-settings"><div class="qem-options"><p>To add a payment form to your events:<br>
        1. Install the <a href="http://wordpress.org/plugins/quick-paypal-payments/">quick paypal payments</a> plugin.<br>
        2. Configure the payment form.<br>
        3. Return to this page to change how the form is displayed on your events.</p><p>This a newish feature of the plugin and will be developed in response to user feedback. If you have a feature you would like to see then contact me through <a href="http://quick-plugins.com/quick-event-manager/" target="_blank">quick-plugins.com</a> or email me at <a href="mailto:mail@quick-plugins.com">mail@quick-plugins.com</a>.</p></div></div>';
    }
    echo $content;
}

function qem_template () {
	if( isset( $_POST['Submit'])) {
        $theme_data = get_theme_data(get_stylesheet_uri()); 
        $templateIdentifier = '<?php
/*
Template Name: Single Event
*/
?>
';
        $templateDirectory = get_template_directory(). '/single.php';
        $newFilePath = get_stylesheet_directory(). '/single-event.php';
        $currentFile = fopen($templateDirectory,"r");
        $pageTemplate = fread($currentFile,filesize($templateDirectory));
        fclose($currentFile);
        $newTemplateFile = fopen($newFilePath,"w");
        fwrite($newTemplateFile, $templateIdentifier);
        $written = fwrite($newTemplateFile, $pageTemplate);
        fclose($newTemplateFile);
        if ( $written != false ) {
            qem_admin_notice('The template has been created. <a href="'.admin_url('theme-editor.php?file=single-event.php').'">Edit Template</a>.');
        } else { 
            qem_admin_notice(__('<strong>ERROR: Unable to create new theme file</strong>', 'quick-event-manager'));
        } 	
    }
    $content = '<div class="qem-settings"><div class="qem-options">
    <h2>The Automated Option</h2>
    <p>If your theme adds the date you posted an event to the event page you can set up and edit a template for single events.</p>
    <p>The function clones your \'single.php\' theme file and saves it as \'single-event.php\'.</p><p>Once created you can edit the file in your appearance editor.</p>
    <p>If you aren\'t confident editing theme files it may be prudent to read the <a href="http://codex.wordpress.org/Page_Templates">WordPress documentation</a> first.</p>';
    $newFilePath = get_stylesheet_directory(). '/single-event.php';
    if (file_exists($newFilePath)) $content .= '<p style="color:red">An Event Template already exists. Clicking the button below will overwrite the existing file. <a href="'.admin_url('theme-editor.php?file=single-event.php').'">View Template file</a>.</p>';
    $content .= '<form id="" method="post" action="">
    <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Create Event Template', 'quick-event-manager').'" /></p>
    </form></div>
    <div class="qem-options" style="float:right">
    <h2>The DIY Option</h2>
    <p>It\'s very easy to create your own template if you have FTP access to your theme.</p>
    <p>1. Connect to your domain using FTP.</p>
    <p>2. Navigate to the theme directory. Normally wp-content/themes/your theme.</p>
    <p>3. Download the file called single.php to your computer.</p>
    <p>4. Open the file using a text editor.</p>
    <p>5. Add the following to the very top of the file:
    <code><&#063;php
    /*
    Template Name: Single Event
    */
    &#063;>
    </code>
    </p>
    <p>6. Save as: <code>single-event.php</code>.</p>
    <p>7. Upload the file to your theme directory.</p>
    <p>The event manager will detect the new template and use if for single events.</p>
    </div>
    </div>';        
    echo $content;		
}

function event_delete_options() {
    delete_option('event_settings');
    delete_option('qem_display');
    delete_option('qem_style');
    delete_option('qem_upgrade');
    delete_option('widget_qem_widget');
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
    <div class='qem-style' style='width:50%'>\r\t<div id='round'>\r\t";
    if ($errors) $content .= "<h2 class='error'>Feed me...</h2>\r\t<p class='error'>...your donation details</p>\r\t";
    else $content .= "<h2>Make a donation</h2>\r\t<p>Whilst I enjoy creating these plugins they don't pay the bills. So a paypal donation will always be gratefully received</p>\r\t";
    $content .= '<form method="post" action="" >
    <p><input type="text" label="Your name" name="yourname" value="Your name" onfocus="donateclear(this, \'Your name\')" onblur="donaterecall(this, \'Your name\')"/></p>
    <p><input type="text" label="Amount" name="amount" value="Amount" onfocus="donateclear(this, \'Amount\')" onblur="donaterecall(this, \'Amount\')"/></p>
    <p><input type="submit" value="Donate" id="submit" name="donate" /></p>
    </form>
    </div>';
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