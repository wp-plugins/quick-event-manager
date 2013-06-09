<?php
/*
Plugin Name: Quick Event Manager
Plugin URI: http://www.quick-plugins.com/quick-event-manager
Description: A really, really simple event manager. There is nothing to configure, all you need is an event and the shortcode.
Version: 2.3
Author: fisicx
Author URI: http://www.quick-plugins.com
*/

if (is_admin()) require_once( plugin_dir_path( __FILE__ ) . '/settings.php' );

add_shortcode( 'qem', 'event_shortcode' );
add_action('wp_head', 'qem_use_custom_css');
add_action( 'init', 'event_register');
add_action( 'widgets_init', create_function('', 'return register_widget("qem_widget");') );
add_filter( 'plugin_action_links', 'event_plugin_action_links', 10, 2 );

$styleurl = plugins_url('quick-event-manager-style.css', __FILE__);
wp_register_style('event_style', $styleurl);
wp_enqueue_style( 'event_style');

function event_page_init() {
	add_options_page('Event Settings', 'Event Settings', 'manage_options', __FILE__, 'event_settings');
	}
function event_plugin_action_links($links, $file) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$event_links = '<a href="'.get_admin_url().'options-general.php?page=quick-event-manager/quick-event-manager.php">'.__('Settings').'</a>';
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
	extract(shortcode_atts(array( 'id' => '',posts => '' ), $atts));
	global $post;
	$event = event_get_stored_options();
	if ($posts == 0) $posts = 99;
	extract( shortcode_atts( array(	'daterange' => 'current', ), $atts ) );
	ob_start();
	if ($event['event_descending']) {
		$args = array('post_type'=> 'event', 'orderby'=> 'meta_value_num', 'meta_key'=> 'event_date','posts_per_page'=>-1);
		}
	else {
		$args = array('post_type'=> 'event','orderby'=> 'meta_value_num','meta_key'=> 'event_date','order'=> 'asc','posts_per_page'=>-1);
		}
	query_posts( $args );
	$event_found = false;
	$today = strtotime(date('Y-m-d'));
	$width = '-'.$event['calender_size'];
	if ( have_posts()){
		while ( have_posts() )	{
		the_post();
		$link = get_post_meta($post->ID, 'event_link', true);
		$endtime = get_post_meta($post->ID, 'event_end_time', true);
		$unixtime = get_post_meta($post->ID, 'event_date', true);
		if ($i < $posts) {
			if (($id == 'archive' && $unixtime < $today) || ($id == '' && ($unixtime >= $today || $event['event_archive'] == 'checked'))) {
				$content .= '<div id="qem">' . get_event_calendar_icon() . 
				get_event_summary() . '</div><div style="clear:left;"></div>';	
				$event_found = true;
				$i++;
				}
			}
		}
	echo $content;
	}
	wp_reset_query();
	if (!$event_found) echo "<p>".$event['noevent']."</p>";
	$output_string = ob_get_contents();
	ob_end_clean();
	return $output_string;
	}

function get_event_calendar_icon() {
	global $post;
	$event = event_get_stored_options();
	setlocale(LC_TIME,get_locale().'.UTF8');
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
	$width = '-'.$event['calender_size'];
	$output = '<div class="qem'.$width.'">
	<div style="float:left"><h2><a href="' . get_permalink() . '">' . $post->post_title . '</a></h2><div style="clear:left"></div>';
	foreach (explode( ',',$event['sort']) as $name)
	if ($event['summary'][$name] == 'checked') {
		$output .= build_event($name,$event,$custom);
		}
	$output .= '<p><a href="' . get_permalink() . '">' . $event['read_more'] . '</a></p></div></div>';
	return $output;
	}

function get_event_details() {
	global $post;
	$event = event_get_stored_options();
	$width = '-'.$event['calender_size'];
	$custom = get_post_custom();
	$output = '<div class="qem'.$width.'">' . get_event_map() .
	$output .= '';
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
		$mapurl .= '
		<div style="float:right; margin: 0 0 10px 10px;">
		<a href="http://maps.google.co.uk/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q=' . $map . '&amp;t=m">
		<img src="http://maps.googleapis.com/maps/api/staticmap?center=' . $map . '&size=' . $event['map_width'] . 'x' . $event['map_height'] . '&markers=color:blue%7C'.$map.'&sensor=false" />
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
		<p>All options for the quick events manager are changed on the plugin <a href="'.get_admin_url().'options-general.php?page=quick-event-manager/quick-event-manager.php">Settings</a> page.</p>
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
	$event = get_option('event_settings');
	if ($event['styles'] == 'checked') {
		$event = event_get_stored_options();
		echo "<style type=\"text/css\" media=\"screen\">\r\n" . $event['custom'] . "\r\n</style>\r\n";
		}
	}

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
	$event['noevent'] = 'No event found';
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
	$event['styles'] = '';
	$event['custom'] = "#qem h1 {\r\n}\r\n#qem h2 a{\r\n}";
	return $event;
	}

add_action('admin_menu', 'event_page_init');
add_filter('the_content', 'get_event_content');

