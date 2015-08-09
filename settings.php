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
add_action('plugin_row_meta', 'qem_plugin_row_meta', 10, 2 );


function qem_tabbed_page() {
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
        case 'payment' : qem_payment(); break;
        case 'template' : qem_template(); break;
        case 'coupon' : qem_coupon_codes(); break;
        case 'donate' : qem_donate_page(); break;
        case 'auto' : qem_autoresponse_page(); break;
    }
}

function qem_admin_tabs($current = 'settings') {
    $tabs = array( 
        'setup' 	=> __('Setup', 'quick-event-manager'), 
        'settings'  => __('Event Settings', 'quick-event-manager'), 
        'display'   => __('Event Display', 'quick-event-manager'), 
        'styles'    => __('Styling', 'quick-event-manager'),
        'calendar'  => __('Calendar', 'quick-event-manager'),
        'register'  => __('Registration', 'quick-event-manager'),
        'auto'  => __('Auto Responder', 'quick-event-manager'),
        'payment'  => __('Payments', 'quick-event-manager'),
        'template'  => __('Template', 'quick-event-manager')
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
    <p><span style="color:red; font-weight:bold;">'. __('Important!', 'quick-event-manager').'</span> '.__('If you get an error when trying to view events, resave your', 'quick-event-manager').' <a href="options-permalink.php">permalinks</a>.</p>
    <p>'.__('Create new events using the', 'quick-event-manager').' <a href="edit.php?post_type=event">Events</a> '.__('link on your dashboard menu', 'quick-event-manager').'.</p>
    <p>'.__('To display a list of events on your posts or pages use the shortcode: [qem]', 'quick-event-manager').'.</p>
    <p>'.__('If you prefer to display your events as a calendar use the shortcode', 'quick-event-manager').': [qemcalendar].</p>
    <p>'.__('More shortcodes on the right', 'quick-event-manager').'.</p>
    <p>'.__('That&#39;s pretty much it. All you need to do now is', 'quick-event-manager').' <a href="edit.php?post_type=event">'.__('create some events', 'quick-event-manager').'</a>.</p>
    
    <h2>'.__('Help and Support', 'quick-event-manager').'</h2>
    <p>'.__('Help at', 'quick-event-manager').' <a href="http://quick-plugins.com/quick-event-manager/" target="_blank">quick-plugins.com</a> '.__('along with a feedback form. Or you can email me at ', 'quick-event-manager').'<a href="mailto:mail@quick-plugins.com">mail@quick-plugins.com</a>.</p>'.qemdonate_loop().'
    
    <h2>'.__('Translations', 'quick-event-manager').'</h2>
    <p>Brazilian: Julias  - <a href ="http://www.juliusmiranda.com/">juliusmiranda.com</a></p>
    <p>Czech: Augustin - <a href ="http://zidek.eu/">zidek.eu</a></p>
    <p>French: Bernard - <a href ="http://sorties-en-creuse.fr/">sorties-en-creuse.fr</a></p>
    <p>German: Tameer - <a href ="bloc-rockers-eifel.de">bloc-rockers-eifel.de</a></p>
    <p>Russian: Alexey - <a href ="http://hakuna-matata.spb.ru/">hakuna-matata.spb.ru</a></p>
    </div>
    <div class="qem-options" style="float:right">
    
    <h2>'.__('Event Manager Role', 'quick-event-manager').'</h2>
    <p>'.__('There is a user role called <em>Event Manager</em>. Users with this role only have access to events, they cannot edit posts or pages.', 'quick-event-manager').'</p>
    
    <h2>'.__('Settings', 'quick-event-manager').'</h2>
    <h3><a href="?page=quick-event-manager/settings.php&tab=settings">'.__('Event Settings', 'quick-event-manager').'</a></h3>
    <p>'.__('Select which fields are displayed in the event list and event page. Change actions and style of each field', 'quick-event-manager').'</p>
    <h3><a href="?page=quick-event-manager/settings.php&tab=display">'.__('Event Display', 'quick-event-manager').'</a></h3>
    <p>'.__('Edit event messages and display options', 'quick-event-manager').'</p>
    <h3><a href="?page=quick-event-manager/settings.php&tab=styles">'.__('Event Styling', 'quick-event-manager').'</a></h3>
    <p>'.__('Styling options for the date icon and overall event layout', 'quick-event-manager').'</p>
    <h3><a href="?page=quick-event-manager/settings.php&tab=calendar">'.__('Calendar Options', 'quick-event-manager').'</a></h3>
    <p>'.__('Show events as a calendar. Some styling and display options', 'quick-event-manager').'.</p>
    <h3><a href="?page=quick-event-manager/settings.php&tab=register">'.__('Event Registration', 'quick-event-manager').'</a></h3>
    <p>'.__('Add a registration form and attendee reports to your events', 'quick-event-manager').'.</p>
    <h3><a href="?page=quick-event-manager/settings.php&tab=payment">'.__('Event Payments', 'quick-event-manager').'</a></h3>
    <p>'.__('Configure event payments', 'quick-event-manager').'</p>
    <h3><a href="?page=quick-event-manager/quick-event-messages.php">'.__('Registration Report', 'quick-event-manager').'</a></h3>
    <p>'.__('View, edit and download event registrations', 'quick-event-manager').'. '.__('Access using the <b>Registration</b> link on your dashboard menu', 'quick-event-manager').'.</p>
    
    <h2>'.__('Primary Shortcodes', 'quick-event-manager').'</h2>
    <table>
    <tbody>
    <tr>
    <td>[qem]</td>
    <td>'.__('Standard event list', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td>[qemcalendar]</td>
    <td>'.__('Calendar view', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td>[qem posts=\'99\']</td>
    <td>'.__('Set the number of events to display', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td>[qem id=\'archive\']</td>
    <td>'.__('Show old events', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td>[qem category=\'name\']</td>
    <td>'.__('List events by category', 'quick-event-manager').'</td>
    </tr>
    </tbody>
    </table>
    <p>'.__('There are loads more shortcode options listed on the', 'quick-event-manager').' <a href="http://quick-plugins.com/quick-event-manager/all-the-shortcodes/" target="_blank">'.__('Plugin Website', 'quick-event-manager').'</a> ('.__('link opens in a new tab', 'quick-event-manager').').';
    $content .= '</div></div>';
    echo $content;
}

function qem_event_settings() {
    $active_buttons = array('field1','field2','field3','field4','field5','field6','field7');	
    if( isset( $_POST['Submit']) && check_admin_referer("save_qem")) {
        foreach ( $active_buttons as $item) {
            $event['active_buttons'][$item] = (isset($_POST['event_settings_active_'.$item]) and $_POST['event_settings_active_'.$item] =='on') ? true : false;
            $event['summary'][$item] = (isset( $_POST['summary_'.$item]) );
            $event['bold'][$item] = (isset( $_POST['bold_'.$item]) );
            $event['italic'][$item] = (isset( $_POST['italic_'.$item]) );
            $event['colour'][$item] = filter_var($_POST['colour_'.$item],FILTER_SANITIZE_STRING);
            $event['size'][$item] = filter_var($_POST['size_'.$item],FILTER_SANITIZE_STRING);
            if (!empty ( $_POST['label_'.$item])) {
                $event['label'][$item] = stripslashes($_POST['label_'.$item]);
                filter_var($event['label'][$item],FILTER_SANITIZE_STRING);
            }
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
            'target_link',
            'publicationdate'
        );
        foreach ($option as $item) {
            $event[$item] = stripslashes($_POST[$item]);
            $event[$item] = filter_var($event[$item],FILTER_SANITIZE_STRING);
        }   
        update_option( 'event_settings', $event);
        qem_admin_notice(__('The form settings have been updated', 'quick-event-manager'));
    }
    if( isset( $_POST['Reset']) && check_admin_referer("save_qem")) {
        delete_option('event_settings');
        qem_create_css_file ('update');
        qem_admin_notice (__('The event settings have been reset', 'quick-event-manager')) ;
    }
    $event = event_get_stored_options();
    $$event['dateformat'] = 'checked'; 
    $$event['date_background'] = 'checked';
    $$event['event_order'] = 'checked';
    $$event['publicationdate'] = 'checked'; 
    if ($event['show_map'] == 'checked') $map = 'checked';
    $content = '<script>
    jQuery(function() {var qem_sort = jQuery( "#qem_sort" ).sortable({axis: "y",update:function(e,ui) {var order = qem_sort.sortable("toArray").join();jQuery("#qem_settings_sort").val(order);}});});
    </script>
    <div class ="qem-options" style="width:98%">
    <form id="event_settings_form" method="post" action="">
    <p>'.__('Use the check boxes to select which fields to display in the event post and the event list', 'quick-event-manager').'.</p>
    <p>'.__('Drag and drop to change the order of the fields', 'quick-event-manager').'.</p>
    <p>'.__('The fields with the blue border are for optional captions. For example: <span style="border:1px solid blue;">The cost is</span> {cost} will display as <em>The cost is 20 Zlotys</em>. If you leave it blank just <em>20 Zlotys</em> will display', 'quick-event-manager').'.</p>
    <table id="sorting">
    <thead>
    <tr>
    <th width="15%">'.__('Show in event post', 'quick-event-manager').'</th>
    <th width="10%">'.__('Show in<br>event list', 'quick-event-manager').'</th>
    <th width="15%">'.__('Colour', 'quick-event-manager').'</th>
    <th width="5%">'.__('Font<br>size', 'quick-event-manager').'</th>
    <th width="10%">'.__('Font<br>attributes', 'quick-event-manager').'</th>
    <th>'.__('Caption and display options', 'quick-event-manager').':</th>
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
            $options = '<input type="text" style="border:1px solid blue; width:10em; padding: 1px; margin:0;" name="address_label" . value ="' . $event['address_label'] . '" /> {'.__('address', 'quick-event-manager').'}&nbsp;<input type="checkbox" name="show_map"' . $event['show_map'] . ' value="checked" /> '.__('Show map (if address is given)', 'quick-event-manager').' ';
            break;
            case 'field5':
            $options = '<input type="text" style="border:1px solid blue; width:10em; padding: 1px; margin:0;" name="url_label" . value ="' . $event['url_label'] . '" /> {url}';
            break;
            case 'field6':
            $options = '<input type="text" style="border:1px solid blue; width:10em; padding: 1px; margin:0;" name="cost_label" . value ="' . $event['cost_label'] . '" /> {'.__('cost', 'quick-event-manager').'}';
            break;
            case 'field7':
            $options = '<input type="text" style="border:1px solid blue; width:10em; padding: 1px; margin:0;" name="organiser_label" . value ="' . $event['organiser_label'] . '" /> {'.__('organiser', 'quick-event-manager').'}&nbsp;<input type="checkbox" name="show_telephone"' . $event['show_telephone'] . ' value="checked" /> '.__('Show orgainser\'s contact details', 'quick-event-manager').' ';;
            break;
        }
        $li_class = ( $checked) ? 'button_active' : 'button_inactive';
        $content .= '<tr class="ui-state-default '.$li_class.' '.$first.'" id="' . $name . '"><td>
        <input type="checkbox" class="button_activate" style="border: none; padding: 0; margin:0;" name="event_settings_active_'.$name.'" '.$checked.' />
        <b>' . $event['label'][$name] . '</b></td>
        <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="summary_'.$name.'" '.$summary.' /></td>
        <td><input type="text" class="qem-color" name="colour_'.$name.'" value ="' . $event['colour'][$name].'" /></td>
        <td><input type="text" style="padding:1px;width:3em;border: 1px solid #343838;" name="size_'.$name.'" value ="'.$event['size'][$name].'" />%</td>
        <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="bold_'.$name.'" '.$bold.' /> Bold <input type="checkbox" style="border: none; padding: 0; margin:0;" name="italic_'.$name.'" '.$italic.' /> Italic</td>
        <td>'.$options.'</td>
        </tr>';
    }
	$content .='</tbody></table>
    <h2>Publication Date</h2>
    <p><input type="checkbox" style="border: none; padding: 0; margin:0;" name="publicationdate" value="checked" ' . $event['publicationdate'] . ' /></td><td> '.__('Make publication date the same as the event date', 'quick-event-manager').'</p>
    <input type="hidden" id="qem_settings_sort" name="sort" value="'.$event['sort'].'" />
	<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Save Changes', 'quick-event-manager').'" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="'.__('Reset', 'quick-event-manager').'" onclick="return window.confirm( \' '.__('Are you sure you want to reset the display settings?', 'quick-event-manager').'\' );"/></p>';
    $content .= wp_nonce_field("save_qem");
    $content .= '</form>
    <h2>'.__('Shortcode and Widget Field Selection', 'quick-event-manager').'</h2>
    <p>'.__('If you want a custom layout for a specific list you can use the shortcode [qem fields=1,2,5].', 'quick-event-manager').' '.__('On the <a href="/wp-admin/widgets.php">widget</a> just enter the field numbers seperated with commas.', 'quick-event-manager').'<p>
    <p>'.__('The numbers correspond to the fields like this', 'quick-event-manager').': <p>
    <ol>
    <li>'.__('Short description', 'quick-event-manager').'</li>
    <li>'.__('Event Time', 'quick-event-manager').'</li>
    <li>'.__('Cost', 'quick-event-manager').'</li>
    <li>'.__('Venue', 'quick-event-manager').'</li>
    <li>'.__('Address', 'quick-event-manager').'</li>
    <li>'.__('Website', 'quick-event-manager').'</li>
<li>'.__('Organiser', 'quick-event-manager').'</li>
    </ol>
    <p>'.__('The order of the fields and other options is set using the drag and drop selectors above', 'quick-event-manager').'</p></div>';
    echo $content;
}

function qem_display_page() {
    if( isset( $_POST['Submit']) && check_admin_referer("save_qem")) {
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
            'uselistics',
            'useicsbutton',
            'usetimezone',
            'timezonebefore',
            'timezoneafter',
            'amalgamated',
            'vertical',
            'norepeat',
            'monthtype',
            'categorylocation',
            'showcategory',
            'readmorelink',
            'titlelink'
        );
        foreach ($option as $item) {
            $display[$item] = stripslashes($_POST[$item]);
            $display[$item] = filter_var($display[$item],FILTER_SANITIZE_STRING);
        }
        update_option('qem_display', $display);	
        qem_create_css_file ('update');
        qem_admin_notice (__('The display settings have been updated', 'quick-event-manager'));
    }		
	if( isset( $_POST['Reset']) && check_admin_referer("save_qem")) {
		delete_option('qem_display');
        qem_create_css_file ('update');
		qem_admin_notice (__('The display settings have been reset', 'quick-event-manager')) ;
    }
    $short = $full = $title = $date = '';
    $display = event_get_stored_display();
    $$display['event_order'] = 'checked';
    $$display['show_end_date'] = 'checked';
    $$display['localization'] = 'selected';
    $$display['monthtype'] = 'checked';
    $$display['categorylocation'] = 'checked';
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
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="norepeat" value="checked" ' . $display['norepeat'] . ' /></td><td> '.__('Only show icon on first event if more than one event on that day', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="vertical" value="checked" ' . $display['vertical'] . ' /></td><td> '.__('Show start and end dates above one another', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td colspan="2"><h2>'.__('Event Messages', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td colspan="2">'.__('Read more caption', 'quick-event-manager').': <input type="text" style="width:20em;" label="read_more" name="read_more" value="' . $display['read_more'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2">'.__('No events message', 'quick-event-manager').': <input type="text" style="width:20em;" label="noevent" name="noevent" value="' . $display['noevent'] . '" /></td>
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
    <td> '.__('Show past events in the events list', 'quick-event-manager').'<br><span class="description">'.__('If you only want to display past events use the shortcode: [qem id="archive"]', 'quick-event-manager').'.</span></td>
    </tr>
    <tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="monthheading" value="checked" ' . $display['monthheading'] . ' /></td>
    <td> '.__('Split the list into month/year sections', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td></td>
    <td><input type="radio" name="monthtype" value="short" ' . $short . ' /> '.__('Short (Aug)', 'quick-event-manager').' <input type="radio" name="monthtype" value="full" ' . $full . ' /> '.__('Full (August)', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td><input type="checkbox" name="recentposts"' . $display['recentposts'] . ' value="checked" /></td>
    <td>'.__('Show events in recent posts list', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="showcategory" value="checked" ' . $display['showcategory'] . ' /></td>
    <td> '.__('Show category', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td></td>
    <td><input type="radio" name="categorylocation" value="title" ' . $title . ' /> '.__('Next to title', 'quick-event-manager').' <input type="radio" name="categorylocation" value="date" ' . $date . ' /> '.__('Next to date (if no icon styling)', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td></td>
    <td>Add an Event Category key to your list using the settings on the <a href="?page=quick-event-manager/settings.php&tab=styles">Event Styling</a> page.</td>
    </tr>
    <tr>
    <td colspan="2"><h2>Download to Calendar</h2>
    <p>Download event as a calender file.</p></td>
    </tr>
    <tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="useics" value="checked" ' . $display['useics'] . ' /></td>
    <td> '.__('Add download button to event', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="uselistics" value="checked" ' . $display['uselistics'] . ' /></td>
    <td> '.__('Add download button to event list', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td></td>
    <td>'.__('Button text:', 'quick-event-manager').' <input type="text" style="width:50%;" label="useicsbutton" name="useicsbutton" value="' . $display['useicsbutton'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2"><h2>Event Linking Options</h2></td>
    </tr>
<tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="external_link" value="checked" ' . $display['external_link'] . ' /></td>
    <td> '.__('Link to external website from event list', 'quick-event-manager').'</td>
    </tr>
<tr>
    <td><input type="checkbox" name="external_link_target"' . $display['external_link_target'] . ' value="checked" /></td>
    <td>'.__('Open external links in new tab/page', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td><input type="checkbox" name="linkpopup"' . $display['linkpopup'] . ' value="checked" /></td>
    <td>'.__('Open event in lightbox', 'quick-event-manager').' ('.__('Warning: doesn\'t always behave as expected on small screens', 'quick-event-manager').').</td>
    </tr>
    <tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="titlelink" value="checked" ' . $display['titlelink'] . ' /></td>
    <td> '.__('Remove link from event title and event image', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="readmorelink" value="checked" ' . $display['readmorelink'] . ' /></td>
    <td> '.__('Hide Read More link', 'quick-event-manager').'</td>
    </tr>
<tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="back_to_list" value="checked" ' . $display['back_to_list'] . ' /></td>
    <td> '.__('Add a link to events to go back to the event list', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td></td>
    <td>'.__('Enter URL to link to a specific page. Leave blank to just go back one page', 'quick-event-manager').':<br>
    <input type="text" style="" label="back_to_url" name="back_to_url" value="' . $display['back_to_url'] . '" /></td>
    </tr>
    <tr>
    <td></td>
    <td>'.__('Link caption', 'quick-event-manager').': <input type="text" style="width:50%;" label="back_to_list_caption" name="back_to_list_caption" value="' . $display['back_to_list_caption'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2"><h2>'.__('Maps and Images', 'quick-event-manager').'</h2><td>
    </tr>
    <tr>
    <td colspan="2">'.__('The map will only display if you have a valid address and the &#146;show map&#146; checkbox is ticked on the <a href="?page=quick-event-manager/settings.php&tab=settings">Event Settings</a> page. If you add an image to the event it will replace the map unless you use the option to display both.', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td colspan="2">'.__('Map Width', 'quick-event-manager').': <input type="text" style=" width:3em; padding: 1px; margin:0;" name="map_width" . value ="' . $display['map_width'] . '" /> px&nbsp;&nbsp;'.__('Map Height', 'quick-event-manager').': <input type="text" style=" width:3em; padding: 1px; margin:0;" name="map_height" . value ="' . $display['map_height'] . '" /> px</td>
    </tr>
    <tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="map_in_list" value="checked" ' . $display['map_in_list'] . ' /></td>
    <td>'.__('Show map in event list', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="map_target" value="checked" ' . $display['map_target'] . ' /></td>
    <td>'.__('Open map in new tab/window', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td colspan="2">'.__('Event Image Max Width', 'quick-event-manager').': <input type="text" style=" width:3em; padding: 1px; margin:0;" name="event_image_width" . value ="' . $display['event_image_width'] . '" /> px</td>
    </tr>
    <tr>
    <td><input type="checkbox" style="border: none; padding: 0; margin:0;" name="event_image" value="checked" ' . $display['event_image'] . ' /></td>
    <td>'.__('Show event image in event list', 'quick-event-manager').'.&nbsp;&nbsp;'.__('Max Width', 'quick-event-manager').': <input type="text" style=" width:3em; padding: 1px; margin:0;" name="image_width" . value ="' . $display['image_width'] . '" /> px</td>
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
    <td colspan="2"><h2>'.__('Timezones', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td><input type="checkbox" name="usetimezone"' . $display['usetimezone'] . ' value="checked" /></td>
    <td>'.__('Show timeszones on your events', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td colspan="2"><input type="text" style="width:40%;" name="timezonebefore" value="' . $display['timezonebefore'] . '" /> {timezone} <input type="text" style="width:40%;" name="timezoneafter" value="' . $display['timezoneafter'] . '" /><br>
    <span class="description">'.__('This doesn\'t change the time of the event, it just shows the name of the local timeszone. Set the event timezone in the event editor.', 'quick-event-manager').'</span></td>
    </tr>
    <tr>
    <td colspan="2"><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Save Changes', 'quick-event-manager').'" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="'.__('Reset', 'quick-event-manager').'" onclick="return window.confirm( \' '.__('Are you sure you want to reset the display settings?', 'quick-event-manager').'\' );"/></td>
    </tr>
    </table>';
    $content .= wp_nonce_field("save_qem");
    $content .= '</form>
    </div>
    <div class="qem-options" style="float:right">
    <h2>'.__('Event List Preview', 'quick-event-manager').'</h2>';
    $atts = array('posts' => '3');
    $content .= qem_event_shortcode($atts,'');
    $content .= '</div></div>';
    echo $content;
}

function qem_styles() {
    if( isset( $_POST['Submit']) && check_admin_referer("save_qem")) {
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
            'vanillawidget',
            'linktocategories',
            'showuncategorised',
            'showkeyabove',
            'showkeybelow',
            'keycaption',
            'showcategory',
            'showcategorycaption',
            'uselabels',
            'startlabel',
            'finishlabel',
            'catallevents',
            'catalleventscaption'
        );
        foreach ( $options as $item) {
            $style[$item] = stripslashes($_POST[$item]);
            $style[$item] = filter_var($style[$item],FILTER_SANITIZE_STRING);
        }
        update_option('qem_style', $style);
        qem_create_css_file ('update');
        qem_admin_notice (__('The form styles have been updated', 'quick-event-manager'));
    }
    if( isset( $_POST['Reset']) && check_admin_referer("save_qem")) {
        delete_option('qem_style');
        qem_create_css_file ('update');
        qem_admin_notice (__('The style settings have been reset', 'quick-event-manager'));
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
    <div class="qem-settings">
    <div class="qem-options">
    <form method="post" action="">
    <table>
    <tr>
    <td colspan="2"><h2>'.__('Event Width', 'quick-event-manager').'</h2></td></tr>
    <tr>
    <td colspan="2"><input type="radio" name="widthtype" value="percent" ' . $percent . ' /> '.__('100% (fill the available space)', 'quick-event-manager').'<br />
    <input type="radio" name="widthtype" value="pixel" ' . $pixel . ' /> '.__('Pixel (fixed)', 'quick-event-manager').'<br />
    '.__('Enter the max-width ', 'quick-event-manager').': <input type="text" style="width:4em;" label="width" name="width" value="' . $style['width'] . '" />px '.__('(Just enter the value, no need to add \'px\')', 'quick-event-manager').'.</td></tr>
    <tr>
    <td colspan="2"><h2>'.__('Font Options', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td colspan="2"><input type="radio" name="font" value="theme" ' . $theme . ' /> '.__('Use your theme font styles', 'quick-event-manager').'<br />
	<input type="radio" name="font" value="plugin" ' . $plugin . ' /> '.__('Use Plugin font styles (enter font family and size below)', 'quick-event-manager').'</td></tr>
    <tr>
    <td>'.__('Font Family', 'quick-event-manager').':</td>
    <td><input type="text" style="" label="font-family" name="font-family" value="' . $style['font-family'] . '" /></td></tr>
    <tr>
    <td>'.__('Font Size', 'quick-event-manager').':</td>
    <td><input type="text" style="width:4em;" label="font-size" name="font-size" value="' . $style['font-size'] . '" /><br>
    <span class="description">This is the base font size, you can set the sizes of each part of the listing in the Event Settings.</span></td></tr>
    <tr>
    <td>'.__('Header Size', 'quick-event-manager').':</td>
    <td><input type="text" style="width:4em;" label="header-size" name="header-size" value="' . $style['header-size'] . '" /> '.__('This the size of the title in the event list', 'quick-event-manager').'.</td>
    </tr>
    <tr>
    <td>'.__('Header Colour', 'quick-event-manager').':</td>
    <td><input type="text" class="qem-color" label="header-colour" name="header-colour" value="' . $style['header-colour'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2"><h2>'.__('Calender Icon', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td>'.__('Remove styles', 'quick-event-manager').'</td>
    <td><input type="checkbox" style="margin:0; padding: 0; border: none" name="vanilla"' . $style['vanilla'] . ' value="checked" /> '.__('Do not style the calendar icon', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td style="vertical-align:top;">'.__('Size', 'quick-event-manager').'</td>
    <td>
	<input type="radio" name="calender_size" value="small" ' . $small . ' /> '.__('Small', 'quick-event-manager').' (40px)<br />
	<input type="radio" name="calender_size" value="medium" ' . $medium . ' /> '.__('Medium', 'quick-event-manager').' (60px)<br />
	<input type="radio" name="calender_size" value="large" ' . $large . ' /> '.__('Large', 'quick-event-manager').'(80px)</td>
    </tr>
    <tr>
    <td>'.__('Corners', 'quick-event-manager').'</td>
    <td>
    <input type="radio" name="icon_corners" value="square" ' . $square . ' /> '.__('Square', 'quick-event-manager').'&nbsp;
    <input type="radio" name="icon_corners" value="rounded" ' . $rounded . ' /> '.__('Rounded', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td>'.__('Border Thickness', 'quick-event-manager').'</td>
    <td><input type="text" style="width:2em;" label="calendar border" name="date_border_width" value="' . $style['date_border_width'] . '" /> px</td>
    </tr>
    <tr>
    <td>'.__('Border Colour', 'quick-event-manager').':</td>
    <td><input type="text" class="qem-color" label="calendar border" name="date_border_colour" value="' . $style['date_border_colour'] . '" /><br><span class="description">'.__('There is an option below to use category colours for the icon border', 'quick-event-manager').'.</span></td>
    </tr>
    <tr>
    <td>'.__('Calendar Icon Order', 'quick-event-manager').'</td>
    <td>
    <input type="radio" name="iconorder" value="default" ' . $default . ' /> '.__('DMY', 'quick-event-manager').'&nbsp;<input type="radio" name="iconorder" value="month" ' . $month . ' /> '.__('MDY', 'quick-event-manager').'&nbsp;
    <input type="radio" name="iconorder" value="year" ' . $year . ' /> '.__('YDM', 'quick-event-manager').'&nbsp;
    <input type="radio" name="iconorder" value="dm" ' . $dm . ' /> '.__('DM', 'quick-event-manager').'&nbsp;<input type="radio" name="iconorder" value="md" ' . $md . ' /> '.__('MD', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td>'.__('Start/Finish Labels', 'quick-event-manager').'</td>
    <td><input type="checkbox" style="margin:0; padding: 0; border: none" name="uselabels"' . $style['uselabels'] . ' value="checked" /> '.__('Show start/finish labels', 'quick-event-manager').'<br>
    '.__('Start', 'quick-event-manager').': <input type="text" style="width:7em;" name="startlabel" value="' . $style['startlabel'] . '" /> '.__('Finish', 'quick-event-manager').': <input type="text" style="width:7em;" name="finishlabel" value="' . $style['finishlabel'] . '" /></td>
    </tr>
    <tr>
    <td>'.__('Day Name', 'quick-event-manager').'</td>
    <td><input type="checkbox" style="margin:0; padding: 0; border: none" name="use_dayname"' . $style['use_dayname'] . ' value="checked" /> '.__('Show day name', 'quick-event-manager').'<br>
    <input type="checkbox" style="margin:0; padding: 0; border: none" name="use_dayname_inline"' . $style['use_dayname_inline'] . ' value="checked" /> '.__('Show day name inline with date', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td style="vertical-align:top;">'.__('Date Background colour', 'quick-event-manager').'</td>
    <td>
	<input type="radio" name="date_background" value="grey" ' . $grey . ' /> '.__('Grey', 'quick-event-manager').'<br />
	<input type="radio" name="date_background" value="red" ' . $red . ' /> '.__('Red', 'quick-event-manager').'<br />
	<input type="radio" name="date_background" value="color" ' . $color . ' /> '.__('Set your own', 'quick-event-manager').'<br />
    <input type="text" class="qem-color" label="background" name="date_backgroundhex" value="' . $style['date_backgroundhex'] . '" /></td>
    </tr>
    <tr>
    <td>'.__('Date Text Colour', 'quick-event-manager').'</td>
    <td><input type="text" class="qem-color" label="date colour" name="date_colour" value="' . $style['date_colour'] . '" /></td>
    </tr>
    <tr>
    <td style="vertical-align:top;">'.__('Month Background colour', 'quick-event-manager').'</td>
    <td>
	<input type="radio" name="month_background" value="mwhite" ' . $mwhite . ' /> '.__('White', 'quick-event-manager').'<br />
	<input type="radio" name="month_background" value="colour" ' . $colour . ' /> '.__('Set your own', 'quick-event-manager').'<br />
    <input type="text" class="qem-color" name="month_backgroundhex" value="' . $style['month_backgroundhex'] . '" /></td>
    </tr>
    <tr>
    <td>'.__('Month Text Colour', 'quick-event-manager').'</td>
    <td><input type="text" class="qem-color" label="month colour" name="month_colour" value="' . $style['month_colour'] . '" /></td>
    </tr>
    <tr>
    <td>'.__('Month Text Style', 'quick-event-manager').'</td>
    <td><input type="checkbox" name="date_bold" value="checked" ' . $style['date_bold'] . ' /> '.__('Bold', 'quick-event-manager').'&nbsp;
	<input type="checkbox" name="date_italic" value="checked" ' . $style['date_italic'] . ' /> '.__('Italic', 'quick-event-manager').'</td>
    </tr>
	<tr>
    <td colspan="2"><h2>'.__('Event Content', 'quick-event-manager').'</h2></td>
    </tr>
	<tr>
    <td style="vertical-align:top;">'.__('Event Border', 'quick-event-manager').'</td>
    <td><input type="checkbox" style="margin:0; padding: 0; border: none" name="event_border"' . $style['event_border'] . ' value="checked" /> '.__('Add a border to the event post', 'quick-event-manager').'<br /><span class="description">'.__('Thickness and colour will be the same as the calendar icon', 'quick-event-manager').'.</span></td>
    </tr>
    <tr>
    <td style="vertical-align:top;">'.__('Event Background Colour', 'quick-event-manager').'</td>
    <td><input type="radio" name="event_background" value="bgwhite" ' . $bgwhite . ' /> '.__('White', 'quick-event-manager').'<br />
	<input type="radio" name="event_background" value="bgtheme" ' . $bgtheme . ' /> '.__('Use theme colours', 'quick-event-manager').'<br />
	<input type="radio" name="event_background" value="bgcolor" ' . $bgcolor . ' /> '.__('Set your own', 'quick-event-manager').'<br />
	<input type="text" class="qem-color" label="background" name="event_backgroundhex" value="' . $style['event_backgroundhex'] . '" /></td>
    </tr>
    <tr>
    <td style="vertical-align:top;">'.__('Margins and Padding', 'quick-event-manager').'</td>
    <td><span class="description">'.__('Set the margins and padding of each bit using CSS shortcodes', 'quick-event-manager').':</span><br><input type="text" label="line margin" name="line_margin" value="' . $style['line_margin'] . '" /></td>
    </tr>
    <tr>
    <td style="vertical-align:top;">'.__('Event Margin', 'quick-event-manager').'</td>
    <td><span class="description">'.__('Set the margin or each event using CSS shortcodes', 'quick-event-manager').':</span><br>
    <input type="text" label="margin" name="event_margin" value="' . $style['event_margin'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2"><h2>'.__('Categories', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td width="30%">'.__('Display category key', 'quick-event-manager').'</td>
    <td><input type="checkbox" style="margin:0; padding: 0; border: none" name="showkeyabove" ' . $style['showkeyabove'] . ' value="checked" /> '.__('Show above event list', 'quick-event-manager').'<br>
    <input type="checkbox" style="margin:0; padding: 0; border: none" name="showkeybelow" ' . $style['showkeybelow'] . ' value="checked" /> '.__('Show below event list', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="30%">'.__('Caption', 'quick-event-manager').'</td>
    <td><input type="text" style="" label="text" name="keycaption" value="' . $style['keycaption'] . '" /></td>
    </tr>
    <tr>
    <td width="30%">'.__('Add link back to all events', 'quick-event-manager').'</td>
    <td><input type="checkbox" style="margin:0; padding: 0; border: none" name="catallevents" ' . $style['catallevents'] . ' value="checked" /><br><span class="description">'.__('This uses the URL set on the', 'quick-event-manager').' <a href="?page=quick-event-manager/settings.php&tab=display">'.__('Event Display', 'quick-event-manager').'</a> '.__('page', 'quick-event-manager').'.</span></td>
    </tr>
    <tr>
    <td width="30%">'.__('Caption', 'quick-event-manager').'</td>
    <td><input type="text" style="" label="text" name="catalleventscaption" value="' . $style['catalleventscaption'] . '" /></td>
    </tr>
    <tr>
    <td style="vertical-align:top;">'.__('Category Colours', 'quick-event-manager').'</td><td><input type="checkbox" style="margin:0; padding: 0; border: none" name="cat_border"' . $style['cat_border'] . ' value="checked" /> '.__('Use category colours for the event border', 'quick-event-manager').'<br />
    <span class="description">'.__('Options are set on the','quick-event-manager').' <a href="?page=quick-event-manager/settings.php&tab=calendar">'.__('Calendar Settings','quick-event-manager').'</a> '.__('page', 'quick-event-manager').'.</span></td>
    </tr>
    <tr>
    <td width="30%"></td><td><input type="checkbox" style="margin:0; padding: 0; border: none" name="showcategory" ' . $style['showcategory'] . ' value="checked" /> '.__('Show name of current category', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="30%"></td>
    <td>'.__('Current category label', 'quick-event-manager').':<br><input type="text" style="" label="text" name="showcategorycaption" value="' . $style['showcategorycaption'] . '" /></td>
    </tr>
    <tr>
    <td>'.__('Linking', 'quick-event-manager').'</td>
    <td><input type="checkbox" style="margin:0; padding: 0; border: none" name="linktocategories" ' . $style['linktocategories'] . ' value="checked" /> '.__('Link keys to categories', 'quick-event-manager').'<br>
    <input type="checkbox" style="margin:0; padding: 0; border: none" name="showuncategorised" ' . $style['showuncategorised'] . ' value="checked" /> '.__('Show uncategorised key', 'quick-event-manager').'</td>
    </tr>
    </table>
    <h2>'.__('Custom CSS', 'quick-event-manager').'</h2>
    <p><input type="checkbox" style="margin:0; padding: 0; border: none" name="use_head"' . $style['use_head'] . ' value="checked" /> '.__('Add styles to document head', 'quick-event-manager').'. '.__('Use this option if you are unable to create or save a stylesheet for the plugin', 'quick-event-manager').'</p>
    <p><input type="checkbox" style="margin:0; padding: 0; border: none" name="use_custom"' . $style['use_custom'] . ' value="checked" /> '.__('Use Custom CSS', 'quick-event-manager').'</p>
    <p><textarea style="width:100%;height:100px;" name="custom">' . $style['custom'] . '</textarea></p>
    <p>'.__('To see all the styling use the', 'quick-event-manager').' <a href="plugin-editor.php?file=quick-event-manager/quick-event-manager.css">'.__('CSS editor', 'quick-event-manager').'</a>.</p>
    <p>'.__('The main style wrapper is the <code>.qem</code> class.', 'quick-event-manager').'</p>
    <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Save Changes', 'quick-event-manager').'" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="'.__('Reset', 'quick-event-manager').'" onclick="return window.confirm( \''.__('Are you sure you want to reset the style settings?', 'quick-event-manager').'\' );"/></p>';
    $content .= wp_nonce_field("save_qem");
    $content .= '</form>
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
    if( isset( $_POST['Submit']) && check_admin_referer("save_qem")) {
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
            'showuncategorised',
            'cellspacing',
            'tdborder',
            'header',
            'headerstyle',
            'eventimage',
            'imagewidth',
            'usetooltip',
            'event_corner',
            'fixeventborder',
            'showmonthsabove',
            'showmonthsbelow',
            'monthscaption',
            'hidenavigation',
            'jumpto'
        );
        foreach ( $options as $item) {
            $cal[$item] = stripslashes($_POST[$item]);
            $cal[$item] = filter_var($cal[$item],FILTER_SANITIZE_STRING);
        }
        $arr = array('a','b','c','d','e','f','g','h','i','j');
        foreach ($arr as $i) {
            $cal['cat'.$i] = $_POST['cat'.$i];
            $cal['cat'.$i.'back'] = $_POST['cat'.$i.'back'];
            $cal['cat'.$i.'text'] = $_POST['cat'.$i.'text'];
        }
        update_option('qem_calendar', $cal);
        qem_create_css_file ('update');
        qem_admin_notice (__('The calendar settings have been updated', 'quick-event-manager'));
    }
    if( isset( $_POST['Reset']) && check_admin_referer("save_qem")) {
        delete_option('qem_calendar');
        qem_create_css_file ('update');
        qem_admin_notice (__('The calendar settings have been reset', 'quick-event-manager'));
    }
    $calendar = qem_get_stored_calendar();
    $$calendar['eventlink'] = 'checked';
    $$calendar['startday'] = 'checked';
    $$calendar['smallicon'] = 'checked';
    $$calendar['navicon'] = 'checked';
    $$calendar['header'] = 'checked';
    $$calendar['event_corner'] = 'checked';

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
    <h2>'.__('Using the Calendar', 'quick-event-manager').'</h2>
    <p>'.__('To add a calendar to your site use the shortcode: [qemcalendar]', 'quick-event-manager').'.</p>
    <form method="post" action="">
    <table width="100%">
    <tr>
    <td colspan="2"><h2>'.__('General Settings', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td style="vertical-align:top;">'.__('Linking to Events', 'quick-event-manager').'</td>
    <td><input type="radio" name="eventlink" value="linkpopup" ' . $linkpopup . ' /> '.__('Link opens event summary in a popup', 'quick-event-manager').'<br />
    <input type="radio" name="eventlink" value="linkpage" ' . $linkpage . ' /> '.__('Link opens event page' ,'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="30%">'.__('Old Events', 'quick-event-manager').'</td>
    <td><input type="checkbox" style="margin:0; padding: 0; border: none" name="archive" ' . $calendar['archive'] . ' value="checked" /> '.__('Show past events in the calendar', 'quick-event-manager').'.</td>
    </tr>
    <tr>
    <td width="30%">'.__('Linking Calendar to the Event List', 'quick-event-manager').'</td>
    <td><input type="checkbox" style="margin:0; padding: 0; border: none" name="connect"' . $calendar['connect'] . ' value="checked" /> '.__('Link Event List to Calendar Page', 'quick-event-manager').'.<br>
    <span class="description">'.__('You will need to create pages for the calendar and the event list', 'quick-event-manager').'.</span>
    </td>
    </tr>
    <tr>
    <td width="30%">'.__('Calendar link text', 'quick-event-manager').'</td><td><input type="text" style="" label="calendar_text" name="calendar_text" value="' . $calendar['calendar_text'] . '" /></td></tr>
    <tr><td width="30%">'.__('Calendar page URL', 'quick-event-manager').'</td><td><input type="text" style="" label="calendar_url" name="calendar_url" value="' . $calendar['calendar_url'] . '" /></td></tr>
    <tr><td width="30%">'.__('Event list link text', 'quick-event-manager').'</td><td><input type="text" style="" label="eventlist_text" name="eventlist_text" value="' . $calendar['eventlist_text'] . '" /></td></tr>
    <tr>
    <td width="30%">'.__('Event list page', 'quick-event-manager').' URL</td>
    <td><input type="text" style="" label="eventlist_url" name="eventlist_url" value="' . $calendar['eventlist_url'] . '" /></td></tr>
    <tr>
    <td width="30%">Navigation Labels</td>
    <td><input type="text" style="width:50%;" label="text" name="prevmonth" value="' . $calendar['prevmonth'] . '" /><input type="text" style="text-align:right;width:50%;" label="text" name="nextmonth" value="' . $calendar['nextmonth'] . '" /></td>
    </tr>
    <tr>
    <td width="30%">'.__('Navigation Icons', 'quick-event-manager').'</td>
    <td>
    <input type="radio" name="navicon" value="none" ' . $none . ' /> '.__('None', 'quick-event-manager').' 
    <input type="radio" name="navicon" value="arrows" ' . $arrows . ' /> &#9668; &#9658; 
    <input type="radio" name="navicon" value="unicodes" ' . $unicodes . ' />'.__('Other', 'quick-event-manager').' ('.__('enter', 'quick-event-manager').' <a href="http://character-code.com/arrows-html-codes.php" target="_blank">'.__('hex code', 'quick-event-manager').'</a> '.__('below', 'quick-event-manager').').<br />
    Left: <input type="text" style="width:6em;" label="text" name="leftunicode" value="' . $calendar['leftunicode'] . '" /> Right: <input type="text" style="width:6em;" label="text" name="rightunicode" value="' . $calendar['rightunicode'] . '" /></td>
    </tr>
    <tr>
    <td width="30%">'.__('Jump to links', 'quick-event-manager').'</td>
    <td><input type="checkbox" style="margin:0; padding: 0; border: none" name="jumpto"' . $calendar['jumpto'] . ' value="checked" /> '.__('Jump to the top of the calendar when linking to a new month.', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td colspan="2"><h2>'.__('Calendar Options', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td style="vertical-align:top;">'.__('Month and Date Header', 'quick-event-manager').'</td>
    <td><input type="radio" name="header" value="h2" ' . $h2 . ' /> H2 <input type="radio" name="header" value="h3" ' . $h3 . ' /> H3 <input type="radio" name="header" value="h4" ' . $h4 . ' /> H4<br>
Header CSS:<br>
    <input type="text" style="" name="headerstyle" value="' . $calendar['headerstyle'] . '" /></td>
    </tr>
    <tr>
    <td width="30%">'.__('Day Border', 'quick-event-manager').'</td>
    <td><input type="text" style="width:12em;" label="tdborder" name="tdborder" value="' . $calendar['tdborder'] . '" /> Example: 1px solid red</td>
    </tr>
    <tr>
    <td width="30%">'.__('Cellspacing', 'quick-event-manager').'</td>
    <td><input type="text" style="width:2em;" label="cellspacing" name="cellspacing" value="' . $calendar['cellspacing'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2"><h2>'.__('Months', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td width="30%">'.__('Display 12 Mavigation', 'quick-event-manager').'</td>
    <td><input type="checkbox" style="margin:0; padding: 0; border: none" name="showmonthsabove" ' . $calendar['showmonthsabove'] . ' value="checked" /> '.__('Show above calendar', 'quick-event-manager').'<br>
    <input type="checkbox" style="margin:0; padding: 0; border: none" name="showmonthsbelow" ' . $calendar['showmonthsbelow'] . ' value="checked" /> '.__('Show below calendar', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="30%">'.__('Caption', 'quick-event-manager').'</td>
    <td><input type="text" style="" label="text" name="monthscaption" value="' . $calendar['monthscaption'] . '" /></td>
    </tr>
    
    <tr>
    <td width="30%">'.__('Hide navigation', 'quick-event-manager').'</td>
    <td><input type="checkbox" style="margin:0; padding: 0; border: none" name="hidenavigation" ' . $calendar['hidenavigation'] . ' value="checked" /> '.__('Remove Prev and Next links', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td colspan="2"><h2>'.__('Event Options', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td width="30%">'.__('Multi-day Events', 'quick-event-manager').'</td>
    <td><input type="checkbox" style="margin:0; padding: 0; border: none" name="showmultiple" ' . $calendar['showmultiple'] . ' value="checked" /> '.__('Show event on all days', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="30%">'.__('Event Border', 'quick-event-manager').'</td>
    <td><input type="text" style="width:12em;" label="eventborder" name="eventborder" value="' . $calendar['eventborder'] . '" /> enter \'none\' to remove border</td>
    </tr>
    <tr>
    <td width="30%"></td>
    <td><input type="checkbox" style="margin:0; padding: 0; border: none" name="fixeventborder" ' . $calendar['fixeventborder'] . ' value="checked" /> '.__('Lock border colour (ignore category colours)', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td>'.__('Corners', 'quick-event-manager').'</td>
    <td>
    <input type="radio" name="event_corner" value="square" ' . $square . ' /> '.__('Square', 'quick-event-manager').'&nbsp;
    <input type="radio" name="event_corner" value="rounded" ' . $rounded . ' /> '.__('Rounded', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="30%">'.__('Character Number', 'quick-event-manager').'</td>
    <td><input type="text" style="width:4em;" label="text" name="eventlength" value="' . $calendar['eventlength'] . '" /><span class="description"> Number of characters to display in event box</span></td>
    </tr>
    <tr>
    <td style="vertical-align:top;">'.__('Small Screens', 'quick-event-manager').'</td>
    <td><span class="description">'.__('What to display on small screens', 'quick-event-manager').':</span><br>
    <input type="radio" name="smallicon" value="trim" ' . $trim . ' /> '.__('Full message', 'quick-event-manager').' <input type="radio" name="smallicon" value="arrow" ' . $arrow . ' /> '.__('&#9654;', 'quick-event-manager').' <input type="radio" name="smallicon" value="box" ' . $box . ' /> '.__('&#9633;', 'quick-event-manager').' <input type="radio" name="smallicon" value="square" ' . $square . ' /> '.__('&#9632;', 'quick-event-manager').' <input type="radio" name="smallicon" value="asterix" ' . $asterix . ' /> '.__('&#9733;', 'quick-event-manager').' 
    <input type="radio" name="smallicon" value="blank" ' . $blank . ' /> '.__('Blank', 'quick-event-manager').' 
    <input type="radio" name="smallicon" value="other" ' . $other . ' /> '.__('Other', 'quick-event-manager').' ('.__('enter escaped', 'quick-event-manager').' <a href="http://www.fileformat.info/info/unicode/char/search.htm" target="blank">unicode</a> '.__('or hex code below', 'quick-event-manager').').<br />
    <input type="text" style="width:6em;" label="text" name="unicode" value="' . $calendar['unicode'] . '" /></td>
    </tr>		
    <tr><td width="30%">'.__('Background', 'quick-event-manager').'</td>
    <td><input type="text" class="qem-color" label="background" name="eventbackground" value="' . $calendar['eventbackground'] . '" /><br><span class="description">Select clear to use day colour</span></td>
    </tr>
    <tr>
    <td width="30%">'.__('Text', 'quick-event-manager').'</td>
    <td><input type="text" class="qem-color" label="text" name="eventtext" value="' . $calendar['eventtext'] . '" /></td>
    </tr>
    <tr>
    <td width="30%">'.__('Text Styles', 'quick-event-manager').'</td>
    <td><input type="checkbox" style="margin:0; padding: 0; border: none" name="eventbold" ' . $calendar['eventbold'] . ' value="checked" /> '.__('Bold', 'quick-event-manager').'<input type="checkbox" style="margin:0; padding: 0; border: none" name="eventitalic" ' . $calendar['eventitalic'] . ' value="checked" /> '.__('Italic', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="30%">'.__('Event Hover', 'quick-event-manager').'</td>
    <td><input type="text" class="qem-color" label="background" name="eventhover" value="' . $calendar['eventhover'] . '" /></td>
    </tr>
    <tr>
    <td width="30%">'.__('Event Image', 'quick-event-manager').'</td>
    <td><input type="checkbox" style="margin:0; padding: 0; border: none" name="eventimage" ' . $calendar['eventimage'] . ' value="checked" /> '.__('Show event image on the calendar', 'quick-event-manager').'<br>'.__('Image Width', 'quick-event-manager').'<input type="text" style="width:3em;" label="text" name="imagewidth" value="' . $calendar['imagewidth'] . '" /> px</td>
    </tr>
    <tr>
    <td width="30%">'.__('Hover Message', 'quick-event-manager').'</td>
    <td><input type="checkbox" style="margin:0; padding: 0; border: none" name="usetooltip" ' . $calendar['usetooltip'] . ' value="checked" /> '.__('Show full event title on hover', 'quick-event-manager').'</td>
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
    $arr = array('a','b','c','d','e','f','g','h','i','j');
    foreach ($arr as $i) {
        $content .= '<p>'.qem_categories ('cat'.$i,$calendar['cat'.$i]).'&nbsp;
        <input type="text" class="qem-color" label="cat'.$i.'back" name="cat'.$i.'back" value="' . $calendar['cat'.$i.'back'] . '" />&nbsp;
        <input type="text" class="qem-color" label="cat'.$i.'text" name="cat'.$i.'text" value="' . $calendar['cat'.$i.'text'] . '" /></p>';
    }
    $content .= '</div>
    <table width="100%">
    <tr>
    <td width="30%">'.__('Display category key', 'quick-event-manager').'</td>
    <td><input type="checkbox" style="margin:0; padding: 0; border: none" name="showkeyabove" ' . $calendar['showkeyabove'] . ' value="checked" /> '.__('Show above calendar', 'quick-event-manager').'<br>
    <input type="checkbox" style="margin:0; padding: 0; border: none" name="showkeybelow" ' . $calendar['showkeybelow'] . ' value="checked" /> '.__('Show below calendar', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="30%">'.__('Caption:', 'quick-event-manager').'</td>
    <td><input type="text" style="" label="text" name="keycaption" value="' . $calendar['keycaption'] . '" /></td>
    </tr>
    <tr>
    <td width="30%"></td><td><input type="checkbox" style="margin:0; padding: 0; border: none" name="linktocategories" ' . $calendar['linktocategories'] . ' value="checked" /> '.__('Link keys to categories', 'quick-event-manager').'<br>
    <input type="checkbox" style="margin:0; padding: 0; border: none" name="showuncategorised" ' . $calendar['showuncategorised'] . ' value="checked" /> '.__('Show uncategorised key', 'quick-event-manager').'</td>
    </tr>
    </table>
    <h2>'.__('Start the Week', 'quick-event-manager').'</h2>
    <p><input type="radio" name="startday" value="sunday" ' . $sunday . ' /> '.__('On Sunday' ,'quick-event-manager').'<br />
    <input type="radio" name="startday" value="monday" ' . $monday . ' /> '.__('On Monday' ,'quick-event-manager').'</p>
    <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Save Changes', 'quick-event-manager').'" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="'.__('Reset', 'quick-event-manager').'" onclick="return window.confirm( \''.__('Are you sure you want to reset the calendar settings?', 'quick-event-manager').'\' );"/></p>';
    $content .= wp_nonce_field("save_qem");
    $content .= '</form>
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
    $processpercent=$processfixed=$qem_apikey='';
    if( isset( $_POST['Submit']) && check_admin_referer("save_qem")) {
        $options = array(
            'useform',
            'formwidth',
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
            'usedropdown','useselector',
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
            'yourselector',
            'yournumber1',
            'useaddinfo',
            'addinfo',
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
            'usecopy',
            'copyblurb',
            'alreadyregistered',
            'useread_more',
            'read_more',
            'sort',
            'registeredusers',
            'paypal',
            'qempaypalsubmit',
            'numberattending',
            'numberattendingbefore',
            'numberattendingafter',
            'allowmultiple',
            'nameremoved',
            'checkremoval',
            'allowtags',
            'useterms',
            'termslabel',
            'termsurl',
            'termstarget',
            'ontheright',
            'usemorenames',
            'morenames'
        );
        foreach ($options as $item) {
            $register[$item] = stripslashes( $_POST[$item]);
            if ($_POST['allowtags'])
                $register[$item] = strip_tags($register[$item],'<p><b><a><em><i><strong>');
            else 
                $register[$item] = filter_var($register[$item],FILTER_SANITIZE_STRING);
        }
        update_option('qem_register', $register);
        qem_create_css_file ('update');
        qem_admin_notice(__('The registration form settings have been updated', 'quick-event-manager'));
    }
    if( isset( $_POST['Reset']) && check_admin_referer("save_qem")) {
        delete_option('qem_register');
        qem_admin_notice(__('The registration form settings have been reset', 'quick-event-manager'));
    }
    if( isset( $_POST['Validate']) && check_admin_referer("save_qem")) {
        $apikey = $_POST['qem_apikey'];
        $blogurl = get_site_url();
        $akismet = new qem_akismet($blogurl, $apikey);
        if($akismet->isKeyValid()) {
            qem_admin_notice("Valid Akismet API Key. All messages will now be checked against the Akismet database.");update_option('qem-akismet', $apikey);
        } else qem_admin_notice("Your Akismet API Key is not Valid");
    }
    if( isset( $_POST['Delete']) && check_admin_referer("save_qem")) {
        delete_option('qem-akismet');
        qem_admin_notice("Akismet validation is no longer active on the Quick Event Manager");
    }
    
    $register = qem_get_stored_register();
    $content = '<div class="qem-settings"><div class="qem-options">
    <form id="" method="post" action="">
    <table width="100%">
    <tr>
    <td colspan="3"><h2>'.__('General Settings', 'quick-event-manager').'</h2></td></tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="useform"' . $register['useform'] . ' value="checked" /></td>
    <td colspan="2">'.__('Add a registration form to ALL your events', 'quick-event-manager').'<br>
    <span class="description">'.__('To add a registration form to individual events use the event editor', 'quick-event-manager').'.</span></td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="formborder"' . $register['formborder'] . ' value="checked" /></td>
    <td colspan="2">'.__('Add a border to the form', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td></td>
    <td colspan="2">'.__('Form Width', 'quick-event-manager').'<input type="text" style="width:4em" name="formwidth" value="' . $register['formwidth'] . '" /> use px, em or %. Default is px.</td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="ontheright"' . $register['ontheright'] . ' value="checked" /></td>
    <td colspan="2">'.__('Display the registration form on the right below the event image and map (if used)', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="notarchive" ' . $register['notarchive'] . ' value="checked" /></td>
    <td colspan="2">'.__('Do not display registration form on old events', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="showuser" ' . $register['showuser'] . ' value="checked" /></td>
    <td colspan="2">'.__('Pre-fill user name if logged in', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="registeredusers" ' . $register['registeredusers'] . ' value="checked" /></td>
    <td colspan="2">'.__('Only users who have logged in can register', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="allowmultiple" ' . $register['allowmultiple'] . ' value="checked" /></td>
    <td colspan="2">'.__('Allow multiple registrations', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="eventfull" ' . $register['eventfull'] . ' value="checked" /></td>
    <td colspan="2">'.__('Hide registration form when event is full', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td></td>
    <td>'.__('Message to display', 'quick-event-manager').':</td>
    <td><input type="text" style="" name="eventfullmessage" value="' . $register['eventfullmessage'] . '" /></td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="allowtags"' . $register['allowtags'] . ' value="cheFcked" /></td>
    <td colspan="2">'.__('Allow HTML tags', 'quick-event-manager').' '.__('Warning: this may leave your site open to CSRF and XSS attacks so be careful.', 'quick-event-manager').'</td>
    </tr>
    <td colspan="3"><h2>'.__('Notifications', 'quick-event-manager').'</h2></td>
    <tr>
    <td colspan="2">'.__('Your Email Address', 'quick-event-manager').'</td>
    <td><input type="text" style="" name="sendemail" value="' . $register['sendemail'] . '" /><br><span class="description">'.__('This is where registration notifications will be sent', 'quick-event-manager').'</span></td>
    </tr>
    <tr>
    <td colspan="3"><h2>'.__('Registration Form', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td colspan="2">'.__('Form title', 'quick-event-manager').'</td>
    <td><input type="text" style="" name="title" value="' . $register['title'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2">'.__('Form blurb', 'quick-event-manager').'</td>
    <td><input type="text" style="" name="blurb" value="' . $register['blurb'] . '" /></td>
    </tr>
    <td colspan="2">'.__('Submit Button', 'quick-event-manager').'</td>
    <td><input type="text" style="" name="qemsubmit" value="' . $register['qemsubmit'] . '" /></td>
    </tr>
    </table>
    <p>'.__('Check those fields you want to use. Drag and drop to change the order', 'quick-event-manager').'.</p>
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
            $req = '';
            $label = __('Dropdown', 'quick-event-manager');
            $input = 'yourdropdown';
            break;
            case 'field12':
            $use = 'usenumber1';
            $req = 'reqnumber1';
            $label = __('Number', 'quick-event-manager');
            $input = 'yournumber1';
            break;
            case 'field13':
            $use = 'useaddinfo';
            $req = '';
            $label = __('Additional Info (displays as plain text)', 'quick-event-manager');
            $input = 'addinfo';
            break;
            case 'field14':
            $use = 'useselector';
            $req = '';
            $label = __('Dropdown', 'quick-event-manager');
            $input = 'yourselector';
            break;
        }
        $content .= '<tr id="'.$name.'">
        <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="'.$use.'" ' . $register[$use] . ' value="checked" /></td>
        <td width="5%">';
        if ($req) $content .= '<input type="checkbox" style="margin:0; padding: 0; border: none" name="'.$req.'" ' . $register[$req] . ' value="checked" />';
        $content .= '</td><td width="20%">'.$label.'</td><td>';
        if ($name=='field7') $content .= $input;
        else $content .= '<input type="text" style="padding:1px;border: 1px solid #343838;" name="'.$input.'" value="' . $register[$input] . '" />';
        $content .= '</td></tr>';
    }
    $content .='</tbody>
    </table>
    <input type="hidden" id="qem_register_sort" name="sort" value="'.$register['sort'].'" />
    <table>
    <td colspan="3"><h2>'.__('Show box for more names', 'quick-event-manager').'</h2></td>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="usemorenames" ' . $register['usemorenames'] . ' value="checked" /></td>
    <td colspan="2">'.__('Show box to add more names if number attending is greater than 1').'</td>
    </tr>
    <tr>
    <td></td>
    <td>'.__('More names label', 'quick-event-manager').'</td>
    <td><input type="text" style="" name="morenames" value="' . $register['morenames'] . '" /></td>
    </tr>
    <tr>
    <td colspan="3"><h2>'.__('Terms and Conditions', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="useterms" ' . $register['useterms'] . ' value="checked" />
    </td>
    <td colspan="2">'.__('Include Terms and Conditions checkbox').'</td>
    </tr>
    <tr>
    <td></td>
    <td>'.__('T&C label', 'quick-event-manager').'</td>
    <td><input type="text" style="" name="termslabel" value="' . $register['termslabel'] . '" /></td>
    </tr>
    <tr>
    <td></td>
    <td>'.__('T&C URL', 'quick-event-manager').'</td>
    <td><input type="text" style="" name="termsurl" value="' . $register['termsurl'] . '" /></td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="termstarget" ' . $register['termstarget'] . ' value="checked" /></td>
    <td colspan="2">'.__('Open link in new Tab/Window').'</td>
    </tr>
    <tr>
    <td colspan="3"><h2>'.__('Error and Thank-you messages', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td colspan="2">'.__('Thank you message title', 'quick-event-manager').'</td>
    <td><input type="text" style="" name="replytitle" value="' . $register['replytitle'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2">'.__('Thank you message blurb', 'quick-event-manager').'</td>
    <td><textarea style="width:100%;height:100px;" name="replyblurb">' . $register['replyblurb'] . '</textarea></td>
    </tr>
    <tr>
    <td colspan="2">'.__('Error Message', 'quick-event-manager').'</td>
    <td><input type="text" style="" name="error" value="' . $register['error'] . '" /></td>
    </tr>
    <tr>
    <td colspan="2">'.__('Already Registered', 'quick-event-manager').'</td>
    <td><input type="text" style="" name="alreadyregistered" value="' . $register['alreadyregistered'] . '" /></td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="checkremoval" ' . $register['checkremoval'] . ' value="checked" /></td>
    <td colspan="2">'.__('Use \'Not Attending\' option to allow people to remove their names from the list', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td></td>
    <td>'.__('Name Removed Message', 'quick-event-manager').'</td>
    <td><input type="text" style="" name="nameremoved" value="' . $register['nameremoved'] . '" /></td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="useread_more"' . $register['useread_more'] . ' value="checked" /></td>
    <td colspan="2">'.__('Display a \'return to event\' message after registration', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td></td>
    <td>'.__('Return to event message:', 'quick-event-manager').'</td>
    <td><input type="text" style="width:100%;" label="read_more" name="read_more" value="' . $register['read_more'] . '" /></td>
    </tr>
    <tr>
    <td colspan="3"><h2>'.__('Confirmation Email', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td colspan="3">You can reply to the sender using the <a href="?page=quick-event-manager/settings.php&tab=auto">Auto Responder</a>.</td>
    </tr>
    <tr>
    <td colspan="3"><h2>'.__('Show Attendees', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="whoscoming" ' . $register['whoscoming'] . ' value="checked" /></td>
    <td colspan="2">'.__('List attendees', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="whosavatar" ' . $register['whosavatar'] . ' value="checked" /></td>
    <td colspan="2">'.__('Show avatars', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td colspan="2">'.__('Message', 'quick-event-manager').'</td>
    <td><input type="text" style="" name="whoscomingmessage" value="' . $register['whoscomingmessage'] . '" /></td>
    </tr>
    <tr>
    <td colspan="3"><h2>'.__('Places Available and Numbers Attending', 'quick-event-manager').'</h2></td>
    </tr>
    <tr>
    <td colspan="3">'.__('Show how many places are left for an event', 'quick-event-manager').'. '.__('Set the number of places in the event editor', 'quick-event-manager').'.</td>
    </tr>
    <tr>
    <td></td>
    <td>'.__('Message to display', 'quick-event-manager').':</td>
    <td><input type="text" style="width:40%;" name="placesbefore" value="' . $register['placesbefore'] . '" /> {number} <input type="text" style="width:40%;" name="placesafter" value="' . $register['placesafter'] . '" />
    </td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="eventlist" ' . $register['eventlist'] . ' value="checked" /></td>
    <td colspan="2">'.__('Show places available on event list - this only works if you have selected \'Add an attendee counter to this form\' on the event editor.', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td width="5%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="numberattending" ' . $register['numberattending'] . ' value="checked" /></td>
    <td colspan="2">'.__('Show number of people attending.', 'quick-event-manager').'</td>
    </tr>
    <tr>
    <td></td><td>'.__('Message to display', 'quick-event-manager').':</td>
    <td><input type="text" style="width:40%; " name="numberattendingbefore" value="' . $register['numberattendingbefore'] . '" /> {number} <input type="text" style="width:40%; " name="numberattendingafter" value="' . $register['numberattendingafter'] . '" /></td>
    </tr>
    </table>
    <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Save Changes', 'quick-event-manager').'" />
    <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="'.__('Reset', 'quick-event-manager').'" onclick="return window.confirm( \''.__('Are you sure you want to reset the registration form?', 'quick-event-manager').'\' );"/></p>
    <h2>'.__('Use Akismet Validation', 'quick-event-manager').'</h2>
    <p>'.__('Enter your API Key to check all messages against the Akismet database.', 'quick-event-manager').'</p> 
    <p><input type="text" label="akismet" name="qem_apikey" value="'.$qem_apikey.'" /></p>
    <p><input type="submit" name="Validate" class="button-primary" style="color: #FFF;" value="Activate Akismet Validation" /> <input type="submit" name="Delete" class="button-secondary" value="Deactivate Aksimet Validation" onclick="return window.confirm( \'This will delete the Akismet Key.\nAre you sure you want to do this?\' );"/></p>';
    $content .= wp_nonce_field("save_qem");
    $content .= '</form>
    </div>
    <div class="qem-options" style="float:right">
    <h2>'.__('Example form', 'quick-event-manager').'</h2>
    <p>'.__('This is an example of the form. When it appears on your site it will use your theme styles.', 'quick-event-manager').'</p>';
    $content .= qem_loop();
	$content .= '</div></div>';
	echo $content;		
}

function qem_autoresponse_page() {
    if( isset( $_POST['Submit']) && check_admin_referer("save_qem")) {
        $options = array(
            'enable',
            'subject',
            'subjecttitle',
            'subjectdate',
            'message',
            'useeventdetails',
            'eventdetailsblurb',
            'useregistrationdetails',
            'registrationdetailsblurb',
            'sendcopy',
            'fromname',
            'fromemail',
            'permalink'
        );
        foreach ( $options as $item) {
            $auto[$item] = stripslashes($_POST[$item]);
        }
        update_option( 'qem_autoresponder', $auto );
        qem_admin_notice("The autoresponder settings have been updated.");
    }
    if( isset( $_POST['Reset']) && check_admin_referer("save_qem")) {
        delete_option('qem_autoresponder');
        qem_admin_notice("The autoresponder settings have been reset.");
    }
	
	$auto = qem_get_stored_autoresponder();
    $message = $auto['message'];
	$content ='<div class="qem-settings"><div class="qem-options" style="width:90%;">
	<h2 style="color:#B52C00">'.__('Auto responder settings', 'quick-event-manager').'</h2>
    <p>'.__('The Auto Responder will send an email to the Registrant if enabled of if they chooses choose to recieve a copy of their details', 'quick-event-manager').'.</p>
    <form method="post" action="">
    <p><input type="checkbox" name="enable"' . $auto['enable'] . ' value="checked" /> '.__('Enable Auto Responder', 'quick-event-manager').'.</p>
    <p>'.__('From Name:', 'quick-event-manager').' (<span class="description">'.__('Defaults to your', 'quick-event-manager').' <a href="'. get_admin_url().'options-general.php">'.__('Site Title', 'quick-event-manager').'</a> '.__('if left blank', 'quick-event-manager').'.</span>):<br>
    <input type="text" style="width:50%" name="fromname" value="' . $auto['fromname'] . '" /></p>
    <p>'.__('From Email:', 'quick-event-manager').' (<span class="description">'.__('Defaults to the', 'quick-event-manager').' <a href="'. get_admin_url().'options-general.php">'.__('Admin Email', 'quick-event-manager').'</a> '.__('if left blank', 'quick-event-manager').'.</span>):<br>
    <input type="text" style="width:50%" name="fromemail" value="' . $auto['fromemail'] . '" /></p>    
    <p>'.__('Subject:', 'quick-event-manager').'<br>
    <input style="width:100%" type="text" name="subject" value="' . $auto['subject'] . '"/></p>
    <p><input type="checkbox" style="margin:0; padding: 0; border: none" name="subjecttitle"' . $auto['subjecttitle'] . ' value="checked" />&nbsp'.__('Show event title', 'quick-event-manager').'&nbsp;
    <input type="checkbox" style="margin:0; padding: 0; border: none" name="subjectdate"' . $auto['subjectdate'] . ' value="checked" />&nbsp;'.__('Show date', 'quick-event-manager').'</p>
    <h2>'.__('Message Content', 'quick-event-manager').'</h2>
    <p>To create individual event messages use the \'Registration Confirmation Message\' option at the bottom of the <a href="post-new.php?post_type=event">Event Editor</a>.</p>';
    echo $content;
    wp_editor($message, 'message', $settings = array('textarea_rows' => '20','wpautop'=>false));
    $content ='<p><input type="checkbox" style="margin:0; padding: 0; border: none" name="useregistrationdetails"' . $auto['useregistrationdetails'] . ' value="checked" />&nbsp;'.__('Add registration details to the email', 'quick-event-manager').'</p>
    <p>'.__('Registration details blurb', 'quick-event-manager').'<br>
    <input type="text" style="" name="registrationdetailsblurb" value="' . $auto['registrationdetailsblurb'] . '" /></p>
    <p><input type="checkbox" style="margin:0; padding: 0; border: none" name="useeventdetails"' . $auto['useeventdetails'] . ' value="checked" />&nbsp;'.__('Add event details to the email', 'quick-event-manager').'</p>
    <p>'.__('Event details blurb', 'quick-event-manager').'<br>
<input type="text" style="" name="eventdetailsblurb" value="' . $auto['eventdetailsblurb'] . '" /></p
    <p><input type="checkbox" style="margin:0; padding: 0; border: none" name="permalink"' . $auto['permalink'] . ' value="checked" />&nbsp;'.__('Include link to event page', 'quick-event-manager').'</td>
    <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the auto responder settings?\' );"/></p>';
    $content .= wp_nonce_field("save_qem");
    $content .= '</form>
    </div>
    </div>';
    echo $content;
}

function qem_payment (){
    if( isset( $_POST['Submit']) && check_admin_referer("save_qem")) {
        $options = array(
            'useqpp',
            'qppform',
            'paypal',
            'paypalemail',
            'currency',
            'useprocess',
            'processtype',
            'processpercent',
            'processfixed',
            'waiting',
            'qempaypalsubmit',
            'ipn',
            'title',
            'paid',
            'ipnblock',
            'sandbox',
            'usecoupon',
            'couponcode'
        );
        foreach ($options as $item) {
            $payment[$item] = stripslashes( $_POST[$item]);
            $payment[$item] = filter_var($payment[$item],FILTER_SANITIZE_STRING);
        }
        update_option('qem_payment', $payment);
        qem_admin_notice(__('The payment form settings have been updated', 'quick-event-manager'));
    }
    if( isset( $_POST['Reset']) && check_admin_referer("save_qem")) {
        delete_option('qem_payment');
        qem_admin_notice(__('The payment form settings have been reset', 'quick-event-manager'));
    }
    $payment = qem_get_stored_payment();
    $$payment['processtype'] = 'checked';
    $$payment['paymenttype'] = 'checked';
    $content = '<div class="qem-settings">
    <form id="" method="post" action="">
    <div class="qem-options">
    <h2>'.__('PayPal Payments', 'quick-event-manager').'</h2>
    <p>'.__('This setting only works if you have a simple cost on your event. This means <em>Entry $10</em> will be OK but <em>&pound;5 for adults and &pound;3 for children</em> may cause problems.', 'quick-event-manager').'</p>
    <table width="100%">
    <tr>
    <td width="30%"><input type="checkbox" style="margin:0; padding: 0; border: none" name="paypal"' . $payment['paypal'] . ' value="checked" />&nbsp;'.__('Transfer to PayPal after registration', 'quick-event-manager').'</td>
    <td>'.__('After registration the plugin will link to paypal using the event title, cost and number of places for the payment details.', 'quick-event-manager').'<br><span class="description"> '.__('You can also select payments on individual events using the', 'quick-event-manager').' <a href="edit.php?post_type=event">'.__('Event Editor', 'quick-event-manager').'</a></span>.</td>
    </tr>
    <tr>
    <td width="30%">'.__('Your PayPal Email Address', 'quick-event-manager').'</td>
    <td><input type="text" style="" name="paypalemail" value="' . $payment['paypalemail'] . '" /></td>
    </tr>
    <tr>
    <td>'.__('Currency code', 'quick-event-manager').':</td>
    <td><input type="text" style="width:3em" label="new_curr" name="currency" value="'.$payment['currency'].'" />&nbsp;(For example: GBP, USD, EUR)<br>
    <span class="description"><a href="https://developer.paypal.com/webapps/developer/docs/classic/api/currency_codes/" target="blank">'.__('Allowed Paypal Currencies', 'quick-event-manager').'</a>.</span></td>
    </tr>
    <tr>
    <td><input type="checkbox" style="margin:0; padding: 0; border: none" name="useprocess"' . $payment['useprocess'] . ' value="checked" /> '.__('Add processing fee', 'quick-event-manager').'</td>
    <td><input type="radio" name="processtype" value="processpercent" ' . $processpercent . ' /> '.__('Percentage of the total', 'quick-event-manager').': <input type="text" style="width:4em;padding:2px" label="processpercent" name="processpercent" value="' . $payment['processpercent'] . '" /> %<br>
    <input type="radio" name="processtype" value="processfixed" ' . $processfixed . ' /> '.__('Fixed amount', 'quick-event-manager').': <input type="text" style="width:4em;padding:2px" label="processfixed" name="processfixed" value="' . $payment['processfixed'] . '" /> '.$payment['currency'].'</td>
    </tr>
    <tr>
    <td>'.__('Submit Label', 'quick-event-manager').'</td>
    <td><input type="text" style="" name="qempaypalsubmit" value="' . $payment['qempaypalsubmit'] . '" /></td>
    </tr>
    <tr>
    <td>'.__('Waiting Message', 'quick-event-manager').'</td>
    <td><input type="text" style="" name="waiting" value="' . $payment['waiting'] . '" /></td>
    </tr>
    </table>
    <h2>'.__('Coupons', 'quick-event-manager').'</h2>
    <p class="description">'.__('Discounts are applied at checkout before any processing fees are caclulated. The coupon field will appear just before the submission button on the registration form', 'quick-event-manager').'.</p>
    <p><input type="checkbox" style="margin:0; padding: 0; border: none" name="usecoupon" ' . $payment['usecoupon'] . ' value="checked" /> '.__('Use Coupons', 'quick-event-manager').'. <a href="?page=quick-event-manager/settings.php&tab=coupon">'.__('Set coupon codes', 'quick-event-manager').'</a></p>
    <p>'.__('Coupon code label', 'quick-event-manager').':<br><input type="text"  style="width:100%" name="couponcode" value="' . $payment['couponcode'] . '" /></p>
    <h2>'.__('Instant Payment Notification', 'quick-event-manager').'</h2>
    <p>'.__('IPN only works if you have a PayPal Business or Premier account and IPN has been set up on that account', 'quick-event-manager').'.</p>
    <p>'.__('See the', 'quick-event-manager').' <a href="https://developer.paypal.com/webapps/developer/docs/classic/ipn/integration-guide/IPNSetup/">'.__('PayPal IPN Integration Guide', 'quick-event-manager').'</a> '.__('for more information on how to set up IPN', 'quick-event-manager').'.</p>

    <p>'.__('The IPN listener URL you will need is', 'quick-event-manager').':<pre>'.site_url('/?qem_ipn').'</pre></p>
    <p>'.__('To see the Payments Report click on the', 'quick-event-manager').' <b>'.__('Registration', 'quick-event-manager').'</b> '.__('link in your dashboard menu or', 'quick-event-manager').' <a href="?page=quick-event-manager/quick-event-messages.php">'.__('click here', 'quick-event-manager').'</a>.</p>
    <p><input type="checkbox" style="margin:0; padding: 0; border: none" name="ipn" ' . $payment['ipn'] . ' value="checked" />&nbsp;'.__('Enable IPN', 'quick-event-manager').'.</p>
    <p>'.__('Payment Report Column header', 'quick-event-manager').':<br>
    <input type="text"  style="width:100%" name="title" value="' . $payment['title'] . '" /></p>
    <p>'.__('Payment Complete Label', 'quick-event-manager').':<br>
    <input type="text"  style="width:100%" name="paid" value="' . $payment['paid'] . '" /></p>
    <p><input type="checkbox" style="margin:0; padding: 0; border: none" name="ipnblock"' . $payment['ipnblock'] . ' value="checked" />&nbsp;'.__('Hide registration details unless payment is complete', 'quick-event-manager').'.</p>
    <p><input type="hidden" name="qppform" value="' . $payment['qppform'] . '" />
    <input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Save Changes', 'quick-event-manager').'" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="'.__('Reset', 'quick-event-manager').'" onclick="return window.confirm( \''.__('Are you sure you want to reset the settings?', 'quick-event-manager').'\' );"/></p>
    <p><input type="checkbox" style="margin:0; padding: 0; border: none" name="sandbox" ' . $payment['sandbox'] . ' value="checked" />&nbsp;'.__('Use Paypal sandbox (developer use only)', 'quick-event-manager').'</p>
    </div>';
    if (function_exists('qpp_loop')) {
        $content .= '<div class="qem-options" style="float:right">
        <h2>'.__('Separate Payment Form', 'quick-event-manager').'</h2>
        <p><span style="color:red;font-weight:bold;">'.__('Warning!', 'quick-event-manager').'</span> '.__('This function has been depreciated.', 'quick-event-manager').'</p>
        <p>'.__('Payments are now integrated into the registation form.', 'quick-event-manager').'&nbsp;'.__('If you really, really need to use the Quick PayPal Payment plugin check this box:', 'quick-event-manager').'&nbsp;<input type="checkbox" style="margin:0; padding: 0; border: none" name="useqpp"' . $payment['useqpp'] . ' value="checked" /></p></div>';
    }
    $content .= wp_nonce_field("save_qem");
    $content .= '</form></div>';
    echo $content;
}

function qem_coupon_codes() {
    if( isset( $_POST['Submit']) && check_admin_referer("save_qem")) {
        $options = array('code','coupontype','couponpercent','couponfixed');
        for ($i=1; $i<=10; $i++) {
            foreach ( $options as $item) $coupon[$item.$i] = stripslashes($_POST[$item.$i]);
            if (!$coupon['coupontype'.$i]) $coupon['coupontype'.$i] = 'percent'.$i;
            if (!$coupon['couponpercent'.$i]) $coupon['couponpercent'.$i] = '10';
            if (!$coupon['couponfixed'.$i]) $coupon['couponfixed'.$i] = '5';
        }
        update_option('qem_coupon',$coupon);
        qem_admin_notice("The coupon settings have been updated");
    }
    if( isset( $_POST['Reset']) && check_admin_referer("save_qem")) {
        delete_option('qem_coupon');
        qem_admin_notice("The coupon settings have been reset");
    }
    $payment = qem_get_stored_payment();
    $before = array(
        'USD'=>'&#x24;',
        'CDN'=>'&#x24;',
        'EUR'=>'&euro;',
        'GBP'=>'&pound;',
        'JPY'=>'&yen;',
        'AUD'=>'&#x24;',
        'BRL'=>'R&#x24;',
        'HKD'=>'&#x24;',
        'ILS'=>'&#x20aa;',
        'MXN'=>'&#x24;',
        'NZD'=>'&#x24;',
        'PHP'=>'&#8369;',
        'SGD'=>'&#x24;',
        'TWD'=>'NT&#x24;',
        'TRY'=>'&pound;');
    $after = array(
        'CZK'=>'K&#269;',
        'DKK'=>'Kr',
        'HUF'=>'Ft',
        'MYR'=>'RM',
        'NOK'=>'kr',
        'PLN'=>'z&#322',
        'RUB'=>'&#1056;&#1091;&#1073;',
        'SEK'=>'kr',
        'CHF'=>'CHF',
        'THB'=>'&#3647;');
    foreach($before as $item=>$key) {if ($item == $payment['currency']) $b = $key;}
    foreach($after as $item=>$key) {if ($item == $payment['currency']) $a = $key;}
    $coupon = qem_get_stored_coupon();
    $content ='<div class="qem-settings"><div class="qem-options">';
    $content .='<h2>'.__('Coupons Codes', 'quick-event-manager').'</h2>';
    $content .= '<form method="post" action="">
    <p<span<b>Note:</b>&nbsp;'.__('Leave fields blank if you don\'t want to use them', 'quick-event-manager').'</span></p>
    <table width="100%">
    <tr>
    <td>'.__('Coupon Code', 'quick-event-manager').'</td>
    <td>'.__('Percentage', 'quick-event-manager').'</td>
    <td>'.__('Fixed Amount', 'quick-event-manager').'</td>
    </tr>';
    for ($i=1; $i<=$coupon['couponnumber']; $i++) {
        $percent = ($coupon['coupontype'.$i] == 'percent'.$i ? 'checked' : '');
        $fixed = ($coupon['coupontype'.$i] == 'fixed'.$i ? 'checked' : ''); 
        $content .= '<tr>
        <td><input type="text" name="code'.$i.'" value="' . $coupon['code'.$i] . '" /></td>
        <td><input type="radio" name="coupontype'.$i.'" value="percent'.$i.'" ' . $percent . ' /> <input type="text" style="width:4em;padding:2px" label="couponpercent'.$i.'" name="couponpercent'.$i.'" value="' . $coupon['couponpercent'.$i] . '" /> %</td>
        <td><input type="radio" name="coupontype'.$i.'" value="fixed'.$i.'" ' . $fixed.' />&nbsp;'.$b.'&nbsp;<input type="text" style="width:4em;padding:2px" label="couponfixed'.$i.'" name="couponfixed'.$i.'" value="' . $coupon['couponfixed'.$i] . '" /> '.$a.'</td>
        </tr>';
    }
    $content .= '</table>
    <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the coupon codes?\' );"/></p>';
    $content .= wp_nonce_field("save_qem");
    $content .= '</form>
    </div>
    </div>';
    echo $content;
}

function qem_template () {
    if( isset( $_POST['Submit']) && check_admin_referer("save_qem")) {
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
            qem_admin_notice('<strong>'.__('ERROR: Unable to create new theme file', 'quick-event-manager').'</strong>');
        }
    }
    $content = '<div class="qem-settings"><div class="qem-options">
    <h2>'.__('The Automated Option', 'quick-event-manager').'</h2>';
    $new = get_stylesheet_directory(). '/single.php';
    if (file_exists($new)) {
        $content .= '<p>'.__('If your theme adds posting dates and other unwanted features to your event page you can set up and edit a template for single events.', 'quick-event-manager').'</p>
        <p>'.__('This function clones the \'single.php\' theme file and saves it as \'single-event.php\'.', 'quick-event-manager').'</p>
        <p>'.__('Once created you can edit the file in your <a href="'.admin_url('theme-editor.php').'">appearance editor', 'quick-event-manager').'</a>.</p>
        <p>'.__('If you aren\'t confident editing theme files it may be prudent to read the', 'quick-event-manager').' <a href="http://codex.wordpress.org/Page_Templates">WordPress documentation</a> '.__('first.', 'quick-event-manager').'</p>';
        $new = get_stylesheet_directory(). '/single-event.php';
        if (file_exists($new)) $content .= '<p style="color:red">'.__('An Event Template already exists. Clicking the button below will overwrite the existing file.', 'quick-event-manager').' <a href="'.admin_url('theme-editor.php?file=single-event.php').'">'.__('View Template file', 'quick-event-manager').'</a>.</p>';
        $content .= '<form id="" method="post" action="">
        <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Create Event Template', 'quick-event-manager').'" /></p>';
        $content .= wp_nonce_field("save_qem");
        $content .= '</form>';
    } else {
        $content .= __('Your theme doesn\'t appear to have the \'single.php\' file needed to create an event template. To create an event template follow the instrctions on the right.', 'quick-event-manager').'</p>';
    }
    $content .= '</div>
    <div class="qem-options" style="float:right">
    <h2>'.__('The DIY Option', 'quick-event-manager').'</h2>
    <p>'.__('It\'s very easy to create your own template if you have FTP access to your theme', 'quick-event-manager').'.</p>
    <p>'.__('1. Connect to your domain using FTP', 'quick-event-manager').'.</p>
    <p>'.__('2. Navigate to the theme directory. Normally wp-content/themes/your theme', 'quick-event-manager').'.</p>
    <p>'.__('3. Download the file called single.php to your computer', 'quick-event-manager').'.</p>
    <p>'.__('4. Open the file using a text editor', 'quick-event-manager').'.</p>
    <p>'.__('5. Add the following to the very top of the file', 'quick-event-manager').':
    <code><&#063;php
    /*
    Template Name: Single Event
    */
    &#063;>
    </code>
    </p>
    <p>'.__('6. Save as: <code>single-event.php</code>', 'quick-event-manager').'.</p>
    <p>'.__('7. Upload the file to your theme directory', 'quick-event-manager').'.</p>
    <p>'.__('The event manager will detect the new template and use it for single events', 'quick-event-manager').'.</p>
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

function qem_donate_page() {
    $content = '<div class="qem-settings"><div class="qem-options">';
    $content .= qemdonate_loop();
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
    <div class='qem-style'>\r\t";
    if ($errors) $content .= "<h2 class='error'>Feed me...</h2>\r\t<p class='error'>...your donation details</p>\r\t";
    else $content .= "<h2 style=\"color:red\">Make a donation</h2>\r\t<p>Whilst I enjoy creating these plugins they don't pay the bills. So a paypal donation will always be gratefully received</p>\r\t";
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
    $formvalues = array();
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
    wp_enqueue_script('qem-media-script',plugins_url('quick-event-media.js', __FILE__ ), array( 'jquery' ), false, true );
    wp_enqueue_style('qem_settings',plugins_url('settings.css', __FILE__));
    wp_enqueue_style('event_style',plugins_url('quick-event-manager.css', __FILE__));
    wp_enqueue_style('event_custom',plugins_url('quick-event-manager-custom.css', __FILE__));
    wp_enqueue_script('event_script',plugins_url('quick-event-manager.js', __FILE__));
}

add_action('admin_enqueue_scripts', 'qem_settings_scripts');

function qem_admin_pages() {
    add_menu_page(__('Registration', 'quick-event-manager'), __('Registration', 'quick-event-manager'), 'manage_options','quick-event-manager/quick-event-messages.php','','dashicons-id');
    add_submenu_page('edit.php?post_type=event' , 'Registrations' , 'Registrations' , 'manage_options' , 'registration' , 'qem_messages');
}

function event_page_init() {
    add_options_page( __('Event Manager', 'quick-event-manager'), __('Event Manager', 'quick-event-manager'), 'manage_options', __FILE__, 'qem_tabbed_page');
}

function qem_admin_notice($message) {
    if (!empty( $message)) echo '<div class="updated"><p>'.$message.'</p></div>';
}

function qem_plugin_row_meta( $links, $file = '' ){
    if( false !== strpos($file , '/quick-event-manager.php') ){
        $new_links = array('<a href="http://quick-plugins.com/quick-event-manager/"><strong>Help and Support</strong></a>','<a href="'.get_admin_url().'options-general.php?page=quick-event-manager/settings.php&tab=donate"><strong>Donate</strong></a>');
        $links = array_merge( $links, $new_links );  
    }
    return $links;
}