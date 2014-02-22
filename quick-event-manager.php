<?php
/*
Plugin Name: Quick Event Manager
Plugin URI: http://www.quick-plugins.com/quick-event-manager
Description: A simple event manager. There is nothing to configure, all you need is an event and the shortcode.
Version: 5.4
Author: aerin
Author URI: http://www.quick-plugins.com
Text Domain: qme
Domain Path: /languages
*/
if (is_admin()) {
	require_once( plugin_dir_path( __FILE__ ) . '/settings.php' );
	require_once( plugin_dir_path( __FILE__ ) . '/quick-event-editor.php' );
	}
add_shortcode("qem","event_shortcode");
add_shortcode('qem-calendar', 'qem_show_calendar');
add_shortcode('qemcalendar', 'qem_show_calendar');
add_action('wp_enqueue_scripts','qem_enqueue_scripts');
add_action('init', 'event_register');
add_action("widgets_init", create_function('', 'return register_widget("qem_widget");') );
add_action('plugins_loaded', 'qem_lang_init');
add_filter("plugin_action_links","event_plugin_action_links", 10, 2 );
add_filter( 'pre_get_posts', 'qem_add_custom_types' );
register_activation_hook(__FILE__, 'qem_flush_rules');

function qem_flush_rules() {
	event_register();
	flush_rewrite_rules();
	}
function qem_enqueue_scripts() {
	wp_enqueue_style('event_style',plugins_url('quick-event-manager.css', __FILE__));
	wp_enqueue_style('event_custom',plugins_url('quick-event-manager-custom.css', __FILE__));
	wp_enqueue_script('event_script',plugins_url('quick-event-manager.js', __FILE__));
	}
function qem_create_css_file ($update) {
	if (function_exists(file_put_contents)) {
		$css_dir = plugin_dir_path( __FILE__ ) . '/quick-event-manager-custom.css' ;
		$filename = plugin_dir_path( __FILE__ );
		if (is_writable($filename) && (!file_exists($css_dir) || !empty($update))) {
			$data = qem_generate_css();
			file_put_contents($css_dir, $data, LOCK_EX);
			}
		}
	else add_action('wp_head', 'qem_head_css');
	}
function qem_lang_init() {
	load_plugin_textdomain( 'quick-event-manager', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
function event_register() {
	qem_create_css_file ('');
	if (!post_type_exists( 'event' ) ) {
		$labels = array(
            'name'=> _x('Events', 'post type general name', 'quick-event-manager'),
            'singular_name' => _x('Event', 'post type singular name', 'quick-event-manager'),
            'add_new'=> _x('Add New', 'event', 'quick-event-manager'),
            'add_new_item'=> __('Add New Event', 'quick-event-manager'),
            'edit_item'=> __('Edit Event', 'quick-event-manager'),
            'new_item'=> __('New Event', 'quick-event-manager'),
            'view_item'=> __('View Event', 'quick-event-manager'),
            'search_items'=> __('Search event', 'quick-event-manager'),
            'not_found'=>  __('Nothing found', 'quick-event-manager'),
            'not_found_in_trash'=> __('Nothing found in Trash', 'quick-event-manager'),
            'parent_item_colon'=> ''
            );
		$args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable'=> true,
            'exclude_from_search'=> false,
            'show_ui' => true,
            'query_var' => true,
            'rewrite' => true,
            'show_in_menu' => true,
            'capability_type'=> 'post',
            'hierarchical' => false,
            'has_archive' => true,
            'menu_position'	=> null,
            'taxonomies' => array('category','post_tag'),
            'supports' => array('title','editor','thumbnail','comments')
	  	    );
		register_post_type('event',$args);
		}
	}
function qem_add_custom_types( $query ) {
	if( is_category() || is_tag() ) {
    		$query->set( 'post_type', array('post', 'event','nav_menu_item'));
		return $query;
		}
	}
function event_plugin_action_links($links, $file) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$event_links = '<a href="'.get_admin_url().'options-general.php?page=quick-event-manager/settings.php">'.__('Settings', 'quick-event-manager').'</a>';
		array_unshift( $links, $event_links );
		}
	return $links;
	}
function event_shortcode($atts,$widget) {
	extract(shortcode_atts(array(
		'fullevent'=>'',
		'id'=> '',
		'posts'=> '99',
		'links'=>'on',
		'daterange'=>'current',
		'size'=>'',
		'headersize'=>'headtwo',
		'settings'=>'checked',
		'category'=>''
		),$atts));
	global $post;
	$display = event_get_stored_display();
	$cal = qem_get_stored_calendar();
	ob_start();
	if ($display['event_descending'])
        $args = array('post_type'=> 'event','orderby'	=> 'meta_value_num','meta_key'	=> 'event_date','posts_per_page'=> -1);
	else
        $args = array('post_type'=>'event','orderby'=>'meta_value_num','meta_key'=>'event_date','order'=>'asc','posts_per_page'=>-1);
	query_posts( $args );
	$event_found = false;
	$today = strtotime(date('Y-m-d'));
	if ( have_posts()){
		if ($cal['connect']) $content .='<p><a href="'.$cal['calendar_url'].'">'.$cal['calendar_text'].'</a></p>';
		while ( have_posts() )	{
			the_post();
			$unixtime = get_post_meta($post->ID, 'event_date', true);
			$enddate = get_post_meta($post->ID, 'event_end_date', true);
			if ($i < $posts) {
				if (($id == 'archive' && $unixtime < $today && $enddate < $today) || ($id == '' && ($unixtime >= $today || $enddate >= $today || $display['event_archive'] == 'checked')) && (in_category($category) || !$category)) {
					$content .= qem_event_construct ($links,$size,$headersize,$settings,$fullevent);
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
function qem_event_construct ($links,$size,$headersize,$settings,$fullevent){
	global $post;
	$event = event_get_stored_options();
	$display = event_get_stored_display();
	$style = qem_get_stored_style();
	$cal = qem_get_stored_calendar();
	$custom = get_post_custom();
	$register = qem_get_stored_register();
	$link = get_post_meta($post->ID, 'event_link', true);
	$endtime = get_post_meta($post->ID, 'event_end_time', true);
	$unixtime = get_post_meta($post->ID, 'event_date', true);
	$enddate = get_post_meta($post->ID, 'event_end_date', true);
	$image = get_post_meta($post->ID, 'event_image', true);
	if ($display['sidebyside'] && $enddate) {
		if ($style['calender_size'] == 'small')  $m = 'style="margin-left:100px;"';
		if ($style['calender_size'] == 'medium')  $m = 'style="margin-left:150px;"';
		if ($style['calender_size'] == 'large')  $m = 'style="margin-left:200px;"';
		}
	if($size) $width = '-'.$size;
	else {$size = $style['calender_size']; $width = '-'.$style['calender_size'];}
	$headersize = ($headersize == 'headthree' ? 'h3' : 'h2');
    $content .= '<div class="qem"><div style="float:left">'.get_event_calendar_icon($size,'event_date');
    if ($m) $content .= '</div><div style="float:left">';
    if($display['show_end_date']) $content .= get_event_calendar_icon($size,'event_end_date');
    $content .= '</div>';
    if ($image && ($display['event_image'] || is_singular ('event'))) $content .= '<div style="float:right" ><a href="'.get_permalink() . '"><img src='.$image.'></a></div>';
	$content .= '<div class="qem'.$width.'">';
	if (!is_singular ('event'))	{
		$content .= '<'.$headersize.' style="display:inline;margin-top:0;padding-top:0;">';
		if ($links == 'on') $content .=  '<a href="' . get_permalink() . '">' . $post->post_title . '</a>';
		else $content .=  $post->post_title;
		$content .= '</'.$headersize.'>';
		}
	if ($fullevent && !$image) $content .= get_event_map();
	if ($fullevent) {
 		foreach (explode( ',',$event['sort']) as $name)
		if ($event['active_buttons'][$name]) $content .= build_event($name,$event,$custom,'checked');
		$content .= get_the_content();
		if ($register['useform']) $content.= qem_loop();
		}
	else {
		foreach (explode( ',',$event['sort']) as $name)
		if ($event['summary'][$name] == 'checked') $content .= build_event($name,$event,$custom,$settings);
        if ($links == 'on') $content .= '<p><a href="' . get_permalink() . '">' . $display['read_more'] . '</a></p>';
		}
	$content .= "</div><div style='clear:both'></div></div>\r\n";
	return $content;		
	}
function get_event_calendar_icon($width,$dateicon) {
	global $post;
	$style = qem_get_stored_style();
	setlocale(LC_TIME,get_locale().'.UTF8');
	$rm = '5' + $style['date_border_width'].'px';
	if ($style['date_bold']) {$boldon = '<b>'; $boldoff = '</b>';}
	if ($style['date_italic']) {$italicon = '<em>'; $italicoff = '</em>';}
	$unixtime = get_post_meta($post->ID, $dateicon, true);
	if ($unixtime){
        $month = date_i18n("M", $unixtime);
        $day = date_i18n("d", $unixtime);
        $year = date_i18n("Y", $unixtime);
		return '<div class="qem-calendar-' . $width . '" style="margin-right:'.$rm.'"><span class="day">'.$day.'</span><span class="month">'.$boldon.$italicon.$month.$italicoff.$boldoff.'</span>'.$year.'</div>';
		}
	}
function build_event ($name,$event,$custom,$settings) {
	$style = '';
	if ($settings){
		if ($event['bold'][$name] == 'checked') $style .= 'font-weight: bold; ';
		if ($event['italic'][$name] == 'checked') $style .= 'font-style: italic; ';
		if ($event['colour'][$name]) $style .= 'color: '. $event['colour'][$name] . '; ';
		if ($event['size'][$name]) $style .= 'font-size: ' . $event['size'][$name] . '%; ';
		if ($style) $style = 'style="' . $style . '" ';
		}
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
			if ($event['target_link']) $target = 'target="_blank"';
			if (!preg_match("~^(?:f|ht)tps?://~i", $custom['event_link'][0])) $url = 'http://' . $custom['event_link'][0]; else  $url = $custom['event_link'][0];
			if (empty($custom['event_anchor'][0])) $custom['event_anchor'][0] = $custom['event_link'][0];
			if (!empty ( $custom['event_link'][0] )) $output .= '<p ' . $style . '>' . $caption .  '<a ' . $style .' '.$target.' href="' . $url . '">' . $custom['event_anchor'][0]  . '</a></p>';
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
	if (is_singular ('event') ) $content= qem_event_construct ('off',$size,$headersize,'checked','fullevent');	
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
		$mapurl .= '<div class="qemmap">
		<a href="http://maps.google.fr/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q=' . $map . '&amp;t=m">
		<img src="http://maps.googleapis.com/maps/api/staticmap?center=' . $map . '&size=' . $display['map_width'] . 'x' . $display['map_height'] . '&markers=color:blue%7C'.$map.'&sensor=true" />
		</a></div>';
		}
	return $mapurl;
    }
class qem_widget extends WP_Widget {
	function qem_widget() {
		$widget_ops = array('classname' => 'qem_widget', 'description' => ''.__('Add events to your sidebar', 'quick-event-manager').'');
		$this->WP_Widget('qem_widget', 'Event Manager', $widget_ops);
		}	
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'posts' => '3','size' =>'small','headersize' => 'headtwo','settings' => '') );
		$posts = $instance['posts'];
		$size = $instance['size'];
		$$size = 'checked';
		$headersize = $instance['headersize'];
		$$headersize = 'checked';
		$settings = $instance['settings'];
		if ( isset( $instance[ 'title' ] ) ) {	$title = $instance[ 'title' ];}
		else {$title = __( 'New title', 'text_domain' );}
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('posts'); ?>"><?php _e('Number of posts to display: ', 'quick-event-manager'); ?><input style="width:3em" id="<?php echo $this->get_field_id('posts'); ?>" name="<?php echo $this->get_field_name('posts'); ?>" type="text" value="<?php echo attribute_escape($posts); ?>" /></label></p>
		<h3>Calender Icon Size</h3>
		<p><input type="radio" id="<?php echo $this->get_field_name('size'); ?>" name="<?php echo $this->get_field_name('size'); ?>" value="small" <?php echo $small; ?>> Small<br>
		<input type="radio" id="<?php echo $this->get_field_name('size'); ?>" name="<?php echo $this->get_field_name('size'); ?>" value="medium" <?php echo $medium; ?>> Medium<br>
		<input type="radio" id="<?php echo $this->get_field_name('size'); ?>" name="<?php echo $this->get_field_name('size'); ?>" value="large" <?php echo $large; ?>> Large</p>
		<h3>Event Title</h3>
		<p><input type="radio" id="<?php echo $this->get_field_name('headersize'); ?>" name="<?php echo $this->get_field_name('headersize'); ?>" value="headtwo" <?php echo $headtwo; ?>> H2 <input type="radio" id="<?php echo $this->get_field_name('headersize'); ?>" name="<?php echo $this->get_field_name('headersize'); ?>" value="headthree" <?php echo $headthree; ?>> H3</p>
		<h3>Styling</h3>
		<p><input type="checkbox" id="<?php echo $this->get_field_name('settings'); ?>" name="<?php echo $this->get_field_name('settings'); ?>" value="checked" <?php echo $settings; ?>> Use plugin styles (<a href="options-general.php?page=quick-event-manager/settings.php&tab=settings">View styles</a>)</p>
		<p><?php _e('All other options are changed on the ', 'quick-event-manager'); ?> <a href="options-general.php?page=quick-event-manager/settings.php"><?php _e('settings page', 'quick-event-manager'); ?></a>.</p>
		<?php
		}
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['posts'] = $new_instance['posts'];
		$instance['size'] = $new_instance['size'];
		$instance['headersize'] = $new_instance['headersize'];
		$instance['settings'] = $new_instance['settings'];
		return $instance;
		}
	function widget($args, $instance) {
 	   	extract($args, EXTR_SKIP);
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $args['before_widget'];
		if ( ! empty( $title ) ) echo $args['before_title'] . $title . $args['after_title'];
		echo event_shortcode($instance,'widget');
		echo $args['after_widget'];
		}
    }
function qem_show_calendar() {
	$cal = qem_get_stored_calendar();
	global $post;
	$args = array('post_type' => 'event','orderby'=> 'meta_value_num','meta_key' => 'event_date','order' => 'asc','posts_per_page' => -1);
	$monthnames = array();		
	for ($i = 0; $i <= 12; $i++) {
		$monthnames[] = date_i18n('F', $monthstamp);	
		$monthstamp = strtotime('+1 month', $monthstamp);
		}	
	if ($cal['startday'] == 'monday') $timestamp = strtotime('next sunday'); 
	if ($cal['startday'] == 'sunday')  $timestamp = strtotime('next saturday'); 
	$days = array();
	for ($i = 0; $i <= 7; $i++) {
		$days[] = date_i18n('D', $timestamp);
		$timestamp = strtotime('+1 day', $timestamp);
		}	
	$qem_dates = array();
	$unixtime = array();
	$eventtitle = array();
	$eventsummary = array();
	$eventlinks = array();
	$eventslug = array();
	$query = new WP_Query( $args );
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$eventdate = get_post_meta($post->ID, 'event_date', true);
			$title = get_the_title();
			$link = get_permalink();
			$category = get_the_category();
			$slug = $category[0]->slug;
			$eventx = get_calendar_details();
			array_push($unixtime, $eventdate);
			array_push($eventtitle,$title);
			array_push($eventslug,$slug);
			array_push($eventsummary, $eventx);
			array_push($eventlinks,$link);
			}
		}
	wp_reset_postdata();
	global $_GET;
	if (!isset($_GET["qemmonth"])) {$_GET["qemmonth"] = date_i18n("n");}
	if (!isset($_GET["qemyear"])) {$_GET["qemyear"] = date_i18n("Y");}
	$currentmonth = $_GET["qemmonth"];
	$currentyear = $_GET["qemyear"];
	$p_year = $currentyear;
	$n_year = $currentyear;
	$p_month = $currentmonth-1;
	$n_month = $currentmonth+1;
	if ($p_month == 0 ) {$p_month = 12;$p_year = $currentyear - 1;}
	if ($n_month == 13 ) {$n_month = 1;$n_year = $currentyear + 1;};
	if ($cal['connect']) $calendar .='<p><a href="'.$cal['eventlist_url'].'">'.$cal['eventlist_text'].'</a></p>';
	$calendar .='<div id="qem-calendar">
		<table style="width:100%" border="0" cellspacing="3" cellpadding="0">
		<tr class="top">
		<td colspan="1" ><a class="calnav" href="?qemmonth='. $p_month . '&amp;qemyear=' . $p_year . '">'.__('&#9668; Prev', 'quick-event-manager').'</a></td>
		<td colspan="5" class="calmonth"><h2>'. $monthnames[$currentmonth-1].' '.$currentyear .'</h2></td>
		<td colspan="1"><a class="calnav" href="?qemmonth='. $n_month . '&amp;qemyear=' . $n_year . '">'.__('Next &#9658;', 'quick-event-manager').'</a></td>
		</tr>
		<tr>';
	for($i=1;$i<=7;$i++) $calendar .= '<td class="calday">' . $days[$i] . '</td>';
	$calendar .= '</tr>';
	$timestamp = mktime(0,0,0,$currentmonth,1,$currentyear);
	$maxday = date_i18n("t",$timestamp);
	$thismonth = getdate($timestamp);
	if ($cal['startday'] == 'monday') {
		$startday = $thismonth['wday']-1;
		if ($startday=='-1') $startday='6';
		}
	else $startday = $thismonth['wday'];
	for ($i=0; $i<($maxday+$startday); $i++) {
		$oldday ='';
		$xxx = mktime(0,0,0,$currentmonth,$i - $startday+1,$currentyear);
		if (date_i18n("d") > $i - $startday+1 && $currentmonth <= date_i18n("n") && $currentyear == date_i18n("Y")) $oldday = 'oldday';
		if ($currentmonth < date_i18n("n") && $currentyear == date_i18n("Y")) $oldday = 'oldday';
		if ($currentyear < date_i18n("Y")) $oldday = 'oldday';
        if (($cal['archive'] && $oldday) || !$oldday) $show = 'checked'; else $show ='';
		$tdstart = '<td class="day '.$oldday.'"><h2>'.($i - $startday+1).'</h2><br>';
		$tdcontent = '';
		foreach ($unixtime as $key => $day) {
            $m=date('m', $day);$d=date('d', $day);$y=date('Y', $day);
            $zzz = mktime(0,0,0,$m,$d,$y);
            if($xxx==$zzz && $show) {	
                $tdstart = '<td class="eventday '.$oldday.'"><h2>'.($i - $startday+1).'</h2><br>';
                $length = $cal['eventlength'];
                if(strlen($eventtitle[$key]) > $length) $trim = preg_replace("/^(.{1,$length})(\s.*|$)/s", '\\1...', $eventtitle[$key]);
                else $trim = $eventtitle[$key];
                if ($cal['eventlink'] == 'linkpopup' ) $tdcontent .= '<a class="event ' . $eventslug[$key] . '" onclick=\'pseudo_popup("<div class =\"qempop\">'.$eventsummary[$key].'</div>")\'><div class="qemtrim"><span>'.$trim.'</span></div></a>';
                else $tdcontent .= '<a class="' . $eventslug[$key] . '" href="' . $eventlinks[$key] . '"><div class="qemtrim"><span>' . $trim . '</span></div></a>';
                }
            }
        $tdbuilt = $tdstart.$tdcontent.'</td>';
        if(($i % 7) == 0 ) $calendar .= "<tr>\r\t";
        if($i < $startday) $calendar .= '<td ></td>';  
        else $calendar .= $tdbuilt;
        if(($i % 7) == 6 ) $calendar .= "</tr>";
        }
	$calendar .= "</table></div>";
	$unixtime = remove_empty($unixtime);
	return $calendar;
	}
function remove_empty($array) {return array_filter($array, '_remove_empty_internal');}
function _remove_empty_internal($value) {return !empty($value) || $value === 0;}
function get_calendar_details() {
	global $post;
	$event = event_get_stored_options();
	$style = qem_get_stored_style();
	$cal = qem_get_stored_calendar();
	$width = $style['calender_size'];
	$display = event_get_stored_display();
	$custom = get_post_custom();
	$output = '<div style="float:left">' . get_event_calendar_icon($width,'event_date').'</div><div class="qem-'.$width.'"><h2 style="display:inline;margin-top:0;padding-top:0;"><a href="' . get_permalink() . '">' . $post->post_title . '</a></h2>';
	foreach (explode( ',',$event['sort']) as $name)
		if ($event['summary'][$name] == 'checked') $output .= build_event($name,$event,$custom,'checked');
	$output .='<p><a href="' . get_permalink() . '">' . $display['read_more'] . '</a></p></div>';
	$output = str_replace('"','\"',$output);
	$output = str_replace("'","&#8217;",$output);
	return $output;
	}
function qem_generate_css() {
	$style = qem_get_stored_style();
	$cal = qem_get_stored_calendar();
	$register = qem_get_stored_register();
	if ($style['calender_size'] == 'small') {$width = 'small';$radius = 5;$rm = '45'+(3*$style['date_border_width']).'px';}
	if ($style['calender_size'] == 'medium') {$width = 'medium';$radius = 7;$rm = '65'+(3*$style['date_border_width']).'px';}
	if ($style['calender_size'] == 'large') {$width = 'large';$radius = 10;$rm = '85'+(3*$style['date_border_width']).'px';}
	if ($style['date_background'] == 'color') $color = $style['date_backgroundhex'];
	if ($style['date_background'] == 'grey') $color = '#343838';
	if ($style['date_background'] == 'red') $color = 'red';
	if ($style['event_background'] == 'bgwhite') $background = 'white';
	if ($style['event_background'] == 'bgcolor') $background = $style['event_backgroundhex'];
	if ($style['icon_corners'] == 'rounded') {
		$dayradius = $radius - $style['date_border_width'];
		$calradius = $radius + $style['date_border_width'];
		$daybackgroundcorner ='-webkit-border-top-left-radius:'.$radius.'px; -moz-border-top-left-radius:'.$radius.'px; border-top-left-radius:'.$radius.'px; -webkit-border-top-right-radius:'.$radius.'px; -moz-border-top-right-radius:'.$radius.'px; border-top-right-radius:'.$radius.'px;';
		$calendarbordercorner ='-webkit-border-radius:'.$calradius.'px; -moz-border-radius:'.$calradius.'px; border-radius:'.$calradius.'px;';
		$eventbackgroundcorner ='-webkit-border-radius:'.$radius.'px; -moz-border-radius:'.$radius.'px; border-radius:'.$radius.'px;';
		$radius = $radius.'px;';
		}
	$daycolour = 'color:' . $style['date_colour'].';';
	$daybackground = 'background:'.$color.';'.$daybackgroundcorner;
	$calendarborder = 'border: '. $style['date_border_width']. 'px solid ' .$style['date_border_colour'].';'.$calendarbordercorner;
	$eventbackground = 'background:'.$background.';'.$eventbackgroundcorner;
	if ($style['event_border']) $eventborder = $calendarborder.'padding:'.$radius;
	if ($register['formborder']) $formborder = $calendarborder.'padding:'.$radius;
	if ($style['widthtype'] == 'pixel') $eventwidth = preg_replace("/[^0-9]/", "", $style['width']) . 'px;';
	else $eventwidth = '100%';
	$script .= ".qem {width:".$eventwidth.";".$style['event_margin'].";}\r\n";
	$script .= ".qem-small, .qem-medium, .qem-large {".$eventborder.";".$eventbackground."}\r\n";
	$script .= ".qem-register {".$formborder.";}\r\n";
	$script .= ".qem-".$width."{margin-left:".$rm.";}\r\n";
	$script .= ".qem-calendar-small, .qem-calendar-medium, .qem-calendar-large {".$calendarborder."}\r\n";
	$script .= ".qem-calendar-small .day, .qem-calendar-medium .day, .qem-calendar-large .day {".$daycolour.$daybackground."}\r\n";
	if ($style['font'] == 'plugin') $script .= ".qem p {font-family: ".$style['font-family']."; font-size: ".$style['font-size'].";}\r\n";
	if ($style['use_custom'] == 'checked') $script .= $style['custom'] . "\r\n";
	$script .="#qem-calendar .calday {background:".$cal['calday']."; color:".$cal['caldaytext']."}\r#qem-calendar .day {background:".$cal['day'].";}\r#qem-calendar .eventday {background:".$cal['eventday'].";}\r#qem-calendar .eventday a {color:".$cal['eventdaytext'].";border:1px solid ".$cal['eventdaytext'].";}\r#qem-calendar .oldday {background:".$cal['oldday'].";}\r#qem-calendar td a:hover {background:".$cal['eventhover']." !important;}";
	$cat = array('a','b','c','d','e','f');
	foreach ($cat as $i) {
		if ($cal['cat'.$i]) {
			$script .="#qem-calendar .".$cal['cat'.$i]." {background:".$cal['cat'.$i.'back']." !important;}\r";
			$script .="#qem-calendar a.".$cal['cat'.$i]." {color:".$cal['cat'.$i.'text']." !important;border:1px solid ".$cal['cat'.$i.'text']." !important;}\r";
			}
		}
	return $script;
	}
function qem_head_css () {
	$data = '<style type="text/css" media="screen">'.qem_generate_css().'</style>';
	echo $data;
	}
function qem_loop() {
	ob_start();
	if (isset($_POST['qemsubmit'])) {
		$formvalues = $_POST;
		$formerrors = array();
		if (!qem_verify_form($formvalues, $formerrors)) qem_display_form($formvalues, $formerrors);
    	else qem_process_form($formvalues);
	} else {
		$register = qem_get_stored_register();
		qem_display_form( $register ,null);
		}
	$output_string=ob_get_contents();
	ob_end_clean();
	return $output_string;
	}
function qem_whoscoming($register) {
	$event = get_the_ID();
	$whoscoming = get_option($event);
	if ($register['whoscoming'] && $whoscoming) {
		$content = '<p id="whoscoming"><b>'.$register['whoscomingmessage'].'</b>';
		foreach(array_keys($whoscoming) as $item) $str = $str.$item.', ';
		$content .= substr($str, 0, -2); 
		$content .= '</p>';
		$content .= '<p>';
		foreach($whoscoming as $item => $value) $content .= '<img title="'.$item.'" src="http://www.gravatar.com/avatar/' . md5($value) . '?s=40&&d=identicon" />&nbsp;';
		$content .= '</p>';}
		return $content;
	}
function qem_display_form( $values, $errors ) {
	$register = qem_get_stored_register();
	if (!empty($qem['title'])) $qem['title'] = '<h2>' . $qem['title'] . '</h2>';
	if (!empty($qem['blurb'])) $qem['blurb'] = '<p>' . $qem['blurb'] . '</p>';
	$content = qem_whoscoming($register);
	$content .= '<div class="qem-register">';
	if (count($errors) > 0) $content .= "<h2>" . $register['error'] . "</h2>\r\t";
	else $content .= "<h2>".$register['title'] . "</h2><p>" . $register['blurb'] . "</p>";	
	$content .= '<form action="" method="POST" enctype="multipart/form-data">';
	if ($register['usename']) $content .= '<p><input id="yourname" name="yourname" type="text" value="'.$register['yourname'].'" onblur="if (this.value == \'\') {this.value = \''.$register['yourname'].'\';}" onfocus="if (this.value == \''.$register['yourname'].'\') {this.value = \'\';}" /><br>';
	if ($register['usemail']) $content .= '<input id="email" name="youremail" type="text" value="'.$register['youremail'].'" onblur="if (this.value == \'\') {this.value = \''.$register['youremail'].'\';}" onfocus="if (this.value == \''.$register['youremail'].'\') {this.value = \'\';}" /><br>';
	$content .= '<input type="submit" value="'.$register['qemsubmit'].'" id="submit" name="qemsubmit" /></p>
	</form>
	<div style="clear:both;"></div></div>';
	echo $content;
	}
function qem_verify_form(&$values, &$errors) {
	$register = qem_get_stored_register();
	if ($register['usemail'] && !filter_var($values['youremail'], FILTER_VALIDATE_EMAIL)) $errors = 'error';
	$values['yourname'] = filter_var($values['yourname'], FILTER_SANITIZE_STRING);
	if ($register['usename'] && (empty($values['yourname']) || $values['yourname'] == $register['yourname'])) $errors = 'error';
	$values['youremail'] = filter_var($values['youremail'], FILTER_SANITIZE_STRING);
	if ($register['usemail'] && (empty($values['youremail']) || $values['youremail'] == $register['youremail'])) $errors = 'error';
	return (count($errors) == 0);	
	}
function qem_process_form($values) {
	$register = qem_get_stored_register();
	if (empty($register['sendemail'])) {global $current_user;get_currentuserinfo();$qem_email = $current_user->user_email;}
	else $qem_email = $register['sendemail'];
	$subject = get_the_title().' '.$register['title'];
	if (empty($subject)) $subject = 'Event Register';
	$content .= '<html>';
	if ($register['usename']) $content .= '<p><b>' . $register['yourname'] . ': </b>' . strip_tags(stripslashes($values['yourname'])) . '</p>';
	if ($register['usemail']) $content .= '<p><b>' . $register['youremail'] . ': </b>' . strip_tags(stripslashes($values['youremail'])) . '</p>';
    	$content .= '</html>';
	$event = get_the_ID();
	$whoscoming = get_option($event);
	if(!is_array($whoscoming)) $whoscoming = array();                           
	$whoscoming[$values['yourname']] = $values['youremail'];
	update_option( $event, $whoscoming );
	$headers = "From: {$values['yourname']} <{$values['youremail']}>\r\n"
		. "MIME-Version: 1.0\r\n"
		. "Content-Type: text/html; charset=\"utf-8\"\r\n";	
	wp_mail($qem_email, $subject, $content, $headers);
	if (!empty($register['replytitle'])) $register['replytitle'] = '<h2>' . $register['replytitle'] . '</h2>';
	if (!empty($register['replyblurb'])) $register['replyblurb'] = '<p>' . $register['replyblurb'] . '</p>';
	$replycontent = qem_whoscoming($register);
	$replycontent .= "<div class='qem-register'>".$register['replytitle'].$register['replyblurb']."</div>";
	echo $replycontent;
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
	$event['active_buttons'] = array('field1'=>'on','field2'=>'on','field3'=>'on','field4'=>'on','field5'=>'on','field6'=>'on');	
	$event['summary'] = array('field1'=>'checked','field2'=>'checked','field3'=>'checked','field4'=>'','field5'=>'','field6'=> '');
	$event['label'] = array('field1'=> __('Short Description', 'quick-event-manager'),'field2' => __('Event Time', 'quick-event-manager'),'field3' => __('Location', 'quick-event-manager'), 'field4' => __('Address', 'quick-event-manager'), 'field5' => __('Event Website', 'quick-event-manager'), 'field6'	=> __('Cost', 'quick-event-manager'));
	$event['sort'] = implode(',',array('field1','field2','field3','field4','field5','field6'));
	$event['bold'] = array('field1'	=>'','field2'=>'checked','field3'=>'','field4'	=>'','field5'	=>'','field6'	=>'');
	$event['italic'] = array('field1'=>'','field2'=>'','field3'	=>'','field4'=>'checked','field5'=>'','field6'=>'');
	$event['colour'] = array('field1'=>'','field2'=>'#343838','field3'=>'','field4'	=>'','field5'=>'','field6'=>'#008C9E');
	$event['size'] = array('field1'=>'110','field2'=>'120','field3'=>'','field4'=>'','field5'=>'','field6'=>'120');
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
	$display['read_more'] = 'Find out more...'; // no need to translate this
	$display['noevent'] = 'No event found'; // no need to translate this
    $display['sidebyside'] = 'checked';
    $display['event_image'] = 'checked';
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
	$style['icon_corners'] = 'rounded';
	$style['styles'] = '';
	$style['event_margin'] = 'margin: 0 0 20px 0;';
	$style['custom'] = ".qem {\r\n}\r\n.qem h2{\r\n}";
	return $style;
	}
function qem_get_stored_calendar() {
	$calendar = get_option('qem_calendar');
	if(!is_array($calendar)) $calendar = array();
	$default = qem_get_default_calendar();
	$calendar = array_merge($default, $calendar);
	return $calendar;
	}
function qem_get_default_calendar() {
	$calendar['day'] = '#EBEFC9';
	$calendar['calday'] = '#EBEFC9';
	$calendar['eventday'] = '#EED1AC';
	$calendar['oldday'] = '#CCC';
	$calendar['eventhover'] = '#F2F2E6';
	$calendar['eventdaytext'] = '#343838';
	$calendar['eventlink'] = 'linkpopup';
	$calendar['calendar_text'] = 'View as calendar';
	$calendar['calendar_url'] = '';
	$calendar['eventlist_text'] = 'View as a list of events';
	$calendar['eventlist_url'] = '';
	$calendar['eventlength'] = '20';
	$calendar['connect'] = '';
	$calendar['startday'] = 'sunday';
	$calendar['archive'] = 'checked';
	$calendar['archivelinks'] = 'checked';
	return $calendar;
	}
function qem_get_stored_register () {
	$register = get_option('qem_register');
	if(!is_array($register)) $register = array();
	$default = qem_get_default_register();
	$register = array_merge($default, $register);
	return $register;
	}
function qem_get_default_register () {
	$register = array();
	$register['usename'] = 'checked';
	$register['usemail'] = 'checked';
	$register['formborder'] = '';
	$register['title'] = 'Register for this event';
	$register['blurb'] = 'Enter your details below';
	$register['replytitle'] = 'Thank you for registering';
	$register['replyblurb'] = 'We will be in contact soon';
	$register['yourname'] = 'Your Name';
	$register['youremail'] = 'Email Address';
	$register['error'] = 'Please complete the form';
	$register['qemsubmit'] = 'Register';
	$register['whoscoming'] = '';
	$register['whoscomingmessage'] = 'Look who\'s coming: ';
	return $register;
	}
add_action('admin_menu', 'event_page_init');
add_filter('the_content', 'get_event_content');
