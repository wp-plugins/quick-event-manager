<?php
/*
Plugin Name: Quick Event Manager
Plugin URI: http://www.quick-plugins.com/quick-event-manager
Description: A really, really simple event manager. There is nothing to configure, all you need is an event and the shortcode.
Version: 3.0
Author: fisicx
Author URI: http://www.quick-plugins.com
*/

if (is_admin()) require_once( plugin_dir_path( __FILE__ ) . '/settings.php' );

add_shortcode("qem","event_shortcode");
add_action("wp_head","qem_use_custom_css");
add_action("init","event_register");
add_action("widgets_init", create_function('', 'return register_widget("qem_widget");') );
add_filter("plugin_action_links","event_plugin_action_links", 10, 2 );

$styleurl = plugins_url('quick-event-manager-style.css', __FILE__);
wp_register_style('event_style', $styleurl);
wp_enqueue_style( 'event_style');
wp_enqueue_script('jquery-ui-datepicker');
wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');

function event_plugin_action_links($links, $file) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$event_links = '<a href="'.get_admin_url().'options-general.php?page=quick-event-manager/settings.php">'.__('Settings').'</a>';
		array_unshift( $links, $event_links );
		}
	return $links;
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
		'taxonomies' => array('category','post_tag'),
		'supports' => array('title','editor','thumbnail','comments')
	  	);
	register_post_type( 'event' , $args );
	}

function event_shortcode($atts) {
	extract(shortcode_atts(array( 'id' => '',posts => '','links'=>'on',), $atts));
	global $post;
	$event = event_get_stored_options();
	$display = event_get_stored_display();
	if ($posts == 0) $posts = 99;
	extract( shortcode_atts( array('daterange' => 'current', ), $atts ) );
	ob_start();
	if ($display['event_descending']) {
		$args = array('post_type'=> 'event', 'orderby'=> 'meta_value_num', 'meta_key'=> 'event_date','posts_per_page'=>-1);
		}
	else {
		$args = array('post_type'=> 'event','orderby'=> 'meta_value_num','meta_key'=> 'event_date','order'=> 'asc','posts_per_page'=>-1);
		}
	query_posts( $args );
	$event_found = false;
	$today = strtotime(date('Y-m-d'));
	$width = '-'.$display['calender_size'];
	if ( have_posts()){
		while ( have_posts() )	{
		the_post();
		$link = get_post_meta($post->ID, 'event_link', true);
		$endtime = get_post_meta($post->ID, 'event_end_time', true);
		$unixtime = get_post_meta($post->ID, 'event_date', true);
		if ($i < $posts) {
			if (($id == 'archive' && $unixtime < $today) || ($id == '' && ($unixtime >= $today || $display['event_archive'] == 'checked'))) {
				$content .= '<div class="qem">' . get_event_calendar_icon() . 
				get_event_summary($links) . '</div><div style="clear:left;"></div>';	
				$event_found = true;
				$i++;
				}
			}
		}
	echo $content;
	}
	wp_reset_query();
	if (!$event_found) echo "<h2>".$display['noevent']."</h2>";
	$output_string = ob_get_contents();
	ob_end_clean();
	return $output_string;
	}

function get_event_calendar_icon() {
	global $post;
	$style = qem_get_stored_style();
	setlocale(LC_TIME,get_locale().'.UTF8');
	if ($style['calender_size'] == 'small') $width = 'small';
	if ($style['calender_size'] == 'medium') $width = 'medium';
	if ($style['calender_size'] == 'large') $width = 'large';
	if ($style['date_bold']) {$boldon = '<b>'; $boldoff = '</b>';} else {$boldon = ''; $boldoff = '';}
	if ($style['date_italic']) {$italicon = '<em>'; $italicoff = '</em>';} else {$italicon = ''; $italicoff = '';}
	$unixtime = get_post_meta($post->ID, 'event_date', true);
    	$month = date_i18n("M", $unixtime);
    	$day = date_i18n("d", $unixtime);
    	$year = date_i18n("Y", $unixtime);
	return '<div class="qem-calendar-' . $width . '"><span class="day">'.$day.'</span><span class="month">'.$boldon.$italicon.$month.$italicoff.$boldoff.'</span>'.$year.'</div>';
	}

function get_event_summary($links) {
	global $post;
	$event = event_get_stored_options();
	$display = event_get_stored_display();
	$style = qem_get_stored_style();
	$custom = get_post_custom();
	$width = '-'.$style['calender_size'];
	$output = '<div class="qem'.$width.'">
	<div style="float:left"><h2 style="margin-top:0;padding-top:0;">';
	if ($links == 'on') $output .=  '<a href="' . get_permalink() . '">' . $post->post_title . '</a>';
	else $output .=  $post->post_title;
	$output .= '</h2><div style="clear:left"></div>';
	foreach (explode( ',',$event['sort']) as $name)
	if ($event['summary'][$name] == 'checked') {
		$output .= build_event($name,$event,$custom);
		}
	if ($links == 'on') $output .= '<p><a href="' . get_permalink() . '">' . $display['read_more'] . '</a></p>';
	$output .= '</div><div style="clear:left"></div></div>';
	return $output;
	}

function get_event_details() {
	global $post;
	$event = event_get_stored_options();
	$style = qem_get_stored_style();
	$width = '-'.$style['calender_size'];
	$custom = get_post_custom();
	$output = '<div class="qem'.$width.'">' . get_event_map();
	foreach (explode( ',',$event['sort']) as $name)
		if ($event['active_buttons'][$name]) {
			$output .= build_event($name,$event,$custom);
			}
	$output .= '';
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
		$content = '<div class="qem">' . 
		get_event_calendar_icon() . 
		get_event_details() . $content .
		'</div></div><div style="clear:both"></div>';
    		}
	return $content;
	}

function get_event_map() {
	global $post;
	$event = event_get_stored_options();
	$display = event_get_stored_display();
	$custom = get_post_custom();
	if (($event['show_map'] == 'checked') && (!empty($custom['event_address'][0]))) {
		$map = str_replace(' ' ,'+',$custom['event_address'][0]);
		$geocode=file_get_contents('http://maps.google.com/maps/geo?output=json&q=' . $map);
		$output= json_decode($geocode);
		$mapurl .= '
		<div style="float:right; margin: 0 0 10px 10px;">
		<a href="http://maps.google.co.uk/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q=' . $map . '&amp;t=m">
		<img src="http://maps.googleapis.com/maps/api/staticmap?center=' . $map . '&size=' . $display['map_width'] . 'x' . $display['map_height'] . '&markers=color:blue%7C'.$map.'&sensor=false" />
		</a>
		</div>';
		}
	return $mapurl;
	}

class qem_widget extends WP_Widget {
	function qem_widget() {
		$widget_ops = array('classname' => 'qem_widget', 'description' => 'Add events to your sidebar');
		$this->WP_Widget('qem_widget', 'Quick Events', $widget_ops);
		}
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'posts' => '') );
		$posts = $instance['posts'];
		?>
		<p><label for="<?php echo $this->get_field_id('posts'); ?>">Number of posts to display: <input style="width:3em" id="<?php echo $this->get_field_id('posts'); ?>" name="<?php echo $this->get_field_name('posts'); ?>" type="text" value="<?php echo attribute_escape($posts); ?>" /></label></p>
		<p>Leave blank to use the default settings.</p>
		<p>All options for the quick events manager are changed on the plugin <a href="options-general.php?page=quick-event-manager/settings.php">Settings</a> page.</p>
		<?php
		}
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['posts'] = $new_instance['posts'];
		return $instance;
		}
	function widget($args, $instance) {
 	   	extract($args, EXTR_SKIP);
		$posts=$instance['posts'];
		echo event_shortcode($instance);
		}
	}

function qem_use_custom_css () {
	$style = qem_get_stored_style();
	if ($style['calender_size'] == 'small') {$width = 'small';$radius = 5;}
	if ($style['calender_size'] == 'medium') {$width = 'medium';$radius = 7;}
	if ($style['calender_size'] == 'large') {$width = 'large';$radius = 10;}
	if ($style['date_background'] == 'color') $color = $style['date_backgroundhex'];
	if ($style['date_background'] == 'grey') $color = '#343838';
	if ($style['date_background'] == 'red') $color = 'red';
	if ($style['event_background'] == 'bgwhite') $background = 'white';
	if ($style['event_background'] == 'bgcolor') $background = $style['event_backgroundhex'];
	$dayradius = $radius - $style['date_border_width'];
	$daycolour = 'color:' . $style['date_colour'].';';
	$daybackground = 'background:' . $color . '; -webkit-border-top-left-radius:'.$dayradius.'px; -moz-border-top-left-radius:'.$dayradius.'px; border-top-left-radius:'.$dayradius.'px; -webkit-border-top-right-radius:'.$dayradius.'px; -moz-border-top-right-radius:'.$dayradius.'px; border-top-right-radius:'.$dayradius.'px;';
	$calendarborder = 'border: '. $style['date_border_width']. 'px solid ' .$style['date_border_colour'].'; -webkit-border-radius:'.$radius.'px; -moz-border-radius:'.$radius.'px; border-radius:'.$radius.'px; ';
	$eventbackground = 'background:'.$background.';-webkit-border-radius:'.$radius.'px; -moz-border-radius:'.$radius.'px; border-radius:'.$radius.'px; ';
	if ($style['event_border']) $eventborder = $calendarborder.'padding:'.$radius.'px';
	if ($style['widthtype'] == 'pixel') $eventwidth = preg_replace("/[^0-9]/", "", $style['width']) . 'px;';
	else $eventwidth = '100%';
	$code = "<style type=\"text/css\" media=\"screen\">\r\n";
	$code .= ".qem {width:".$eventwidth.";}\r\n";
	$code .= ".qem-".$width." {".$eventborder.";".$eventbackground."}\r\n";
	if ($style['font'] == 'plugin') $code .= ".qem p {font-family: ".$style['font-family']."; font-size: ".$style['font-size'].";}\r\n";
	$code .= ".qem-calendar-".$width." {".$calendarborder."}\r\n";
	$code .= ".qem-calendar-".$width." .day {".$daycolour.$daybackground."}\r\n";
	if ($style['use_custom'] == 'checked') $code .= $style['custom'] . "\r\n";
	$code .= "</style>\r\n";
	echo $code;
	}

function qem_upgrade ($event){
	$upgrade = get_option('qem_upgrade');
	if (empty($upgrade)) {
		$display = array();
		$display['read_more'] = $event['read_more'];
		$display['noevent'] = $event['noevent'];
		$display['address_style'] = $event['address_style'];
		$display['event_order'] = $event['event_order'];
		$display['event_archive'] = $event['event_archive'];
		$display['map_width'] = $event['map_width'];
		$display['map_height'] = $event['map_height'];
		update_option('qem_display', $display);
		$style = array();
		$style['date_background'] = $event['date_background'];
		$style['date_backgroundhex'] = $event['background_hex'];
		$style['calender_size'] = $event['calender_size'];
		$style['date_bold'] = $event['date_bold'];
		$style['date_italic'] = $event['date_italic'];
		$style['use_custom'] = $event['styles'];
		$style['custom'] = $event['custom'];
		update_option('qem_style', $style);
		$upgrade = 'complete';
		update_option('qem_upgrade', $upgrade);
		}
	}

function event_get_stored_options () {
	$event = get_option('event_settings');
	if(!is_array($event)) $event = array();
	else qem_upgrade($event);
	$option_default = event_get_default_options();
	$event = array_merge($option_default, $event);
	return $event;
	}

function event_get_default_options () {
	$event = array();
	$event['active_buttons'] = array( 'field1'=>'on' , 'field2'=>'on' , 'field3'=>'on' , 'field4'=>'on' , 'field5'=>'on' , 'field6'=>'on');	
	$event['summary'] = array('field1'=>'checked' , 'field2'=>'checked' , 'field3'=>'checked' , 'field4'=>'' , 'field5'=>'' , 'field6'=>'');
	$event['label'] = array( 'field1'=>'Short Description' , 'field2'=>'Event Time' , 'field3'=>'Location' , 'field4'=>'Address' ,  'field5'=>'Event Website' , 'field6'=>'Cost' );
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
	$event['show_map'] = 'checked';
	$event['address_style'] = 'italic';
	$event['website_link'] = 'checked';
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
	$display['event_order'] = 'newest';
	$display['event_archive'] = '';
	$display['map_width'] = '200';
	$display['map_height'] = '200';
	return $display;
	}
function qem_get_stored_style() {
	$style = get_option('qem_style');
	if(!is_array($style)) $style = array();
	$default = qem_get_default_style();
	$style = array_merge($default, $style);
	return $style;
	}
function qem_get_default_style() {
	$style['font'] = 'theme';
	$style['font-family'] = 'arial, sans-serif';
	$style['font-size'] = '1em';
	$style['width'] = 600;
	$style['widthtype'] = 'percent';
	$style['event_border'] = '';
	$style['event_background'] = 'bgtheme';
	$style['event_backgroundhex'] = '#FFF';
	$style['date_colour'] = '#FFF';
	$style['date_background'] = 'grey';
	$style['date_backgroundhex'] = '#FFF';
	$style['date_border_width'] = '2';
	$style['date_border_colour'] = '#343838';
	$style['date_bold'] = '';
	$style['date_italic'] = 'checked';
	$style['calender_size'] = 'medium';
	$style['styles'] = '';
	$style['custom'] = ".qem {\r\n}\r\n.qem h2{\r\n}";
	return $style;
	}

add_action('admin_menu', 'event_page_init');
add_filter('the_content', 'get_event_content');

