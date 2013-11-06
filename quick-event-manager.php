<?php
/*
Plugin Name: Quick Event Manager
Plugin URI: http://www.quick-plugins.com/quick-event-manager
Description: A simple event manager. There is nothing to configure, all you need is an event and the shortcode.
Version: 4.2
Author: aerin
Author URI: http://www.quick-plugins.com
Text Domain: qme
Domain Path: /languages
*/

if (is_admin()) require_once( plugin_dir_path( __FILE__ ) . '/settings.php' );

add_shortcode("qem","event_shortcode");
add_shortcode('qem-calendar', 'qem_show_calendar'); 
add_action("init","event_register");
add_action('wp_head', 'qem_head_script');
add_action("widgets_init", create_function('', 'return register_widget("qem_widget");') );
add_filter("plugin_action_links","event_plugin_action_links", 10, 2 );


/** Loads the translation */
function qem_lang_init() {
	load_plugin_textdomain( 'quick-event-manager', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action('plugins_loaded', 'qem_lang_init');
/** end translation **/

function qem_add_custom_types( $query ) {
  if( is_category() || is_tag() ) {
    $query->set( 'post_type', array(
     'post', 'event','nav_menu_item'
		));
	  return $query;
	}
}
add_filter( 'pre_get_posts', 'qem_add_custom_types' );

wp_enqueue_style('event_style',plugins_url('quick-event-manager-style.css', __FILE__));

function event_plugin_action_links($links, $file) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$event_links = '<a href="'.get_admin_url().'options-general.php?page=quick-event-manager/settings.php">'.__('Settings', 'quick-event-manager').'</a>';
		array_unshift( $links, $event_links );
		}
	return $links;
}

function event_register() {
	$labels = array(
		'name' 					=> _x('Events', 'post type general name', 'quick-event-manager'),
		'singular_name' 		=> _x('Event', 'post type singular name', 'quick-event-manager'),
		'add_new' 				=> _x('Add New', 'event', 'quick-event-manager'),
		'add_new_item' 			=> __('Add New Event', 'quick-event-manager'),
		'edit_item' 			=> __('Edit Event', 'quick-event-manager'),
		'new_item' 				=> __('New Event', 'quick-event-manager'),
		'view_item' 			=> __('View Event', 'quick-event-manager'),
		'search_items' 			=> __('Search event', 'quick-event-manager'),
		'not_found'				=>  __('Nothing found', 'quick-event-manager'),
		'not_found_in_trash' 	=> __('Nothing found in Trash', 'quick-event-manager'),
		'parent_item_colon' 	=> ''
		);
	$args = array(
		'labels'				=> $labels,
		'public' 				=> true,
		'publicly_queryable' 	=> true,
		'exclude_from_search' 	=> false,
		'show_ui' 				=> true,
		'query_var' 			=> true,
		'rewrite' 				=> true,
		'show_in_menu' 			=> true,
		'capability_type' 		=> 'post',
		'hierarchical' 			=> false,
		'has_archive' 			=> true,
		'menu_position'			=> null,
		'taxonomies' 			=> array('category','post_tag'),
		'supports'				=> array('title','editor','thumbnail','comments')
	  	);
	register_post_type('event',$args);
}

function event_shortcode($atts) {
	extract(shortcode_atts(array(
	'fullevent' =>'',
	'id' 		=> '',
	posts       => '99',
	'links'     =>'on',
	'daterange' => 'current'), 
	$atts)
	);
	
	global $post;
	
		$event = event_get_stored_options();
		$display = event_get_stored_display();
		$style = qem_get_stored_style();
		$cal = qem_get_stored_calendar();
		ob_start();
		
	if ($display['event_descending']) {
		$args = array(
		'post_type'			=> 'event',
		'orderby'			=> 'meta_value_num',
		'meta_key'			=> 'event_date',
		'posts_per_page'	=> -1);
		}
		else {
			$args = array(
			'post_type'			=> 'event',
			'orderby'			=> 'meta_value_num',
			'meta_key'			=> 'event_date',
			'order'				=> 'asc',
			'posts_per_page'	=> -1);
			}
		query_posts( $args );
	
	$event_found = false;
	$today = strtotime(date('Y-m-d'));
	$width = '-'.$style['calender_size'];
	
	if ( have_posts()){
		if ($cal['connect']) $content .='<p><a href="'.$cal['calendar_url'].'">'.$cal['calendar_text'].'</a></p>';
		
			while ( have_posts() )	{
			the_post();
			
			$link = get_post_meta($post->ID, 'event_link', true);
			$endtime = get_post_meta($post->ID, 'event_end_time', true);
			$unixtime = get_post_meta($post->ID, 'event_date', true);
			
		if ($i < $posts) {
			if (($id == 'archive' && $unixtime < $today) || ($id == '' && ($unixtime >= $today || $display['event_archive'] == 'checked'))) {
				if ($fullevent) {
					$content .= '<div class="qem">'.
					get_event_calendar_icon() .'<div class="qem'.$width.'"><h2 style="display:inline;">'.$post->post_title.'</h2></div>'.
					get_event_details() . get_the_content() .
					'</div></div><div style="clear:both"></div>';
					}
					else {				
						$content .= '<div class="qem">' . get_event_calendar_icon() . 
						get_event_summary($links) . '</div><div style="clear:both"></div></div>';
						}
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
	if ($style['date_bold']) {$boldon = '<b>'; $boldoff = '</b>';
	} else {
		$boldon = ''; $boldoff = '';
		}
	if ($style['date_italic']) {$italicon = '<em>'; $italicoff = '</em>';
	} else {
		$italicon = ''; $italicoff = '';
	}
	
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
	$output = '<div class="qem'.$width.'">';
	$output .= '<h2 style="display:inline;margin-top:0;padding-top:0;">';
	
	if ($links == 'on') $output .=  '<a href="' . get_permalink() . '">' . $post->post_title . '</a>';
		else $output .=  $post->post_title;
		$output .= '</h2>';
	
			foreach (explode( ',',$event['sort']) as $name)
			if ($event['summary'][$name] == 'checked') {
				$output .= build_event($name,$event,$custom);
				}
	if ($links == 'on') $output .= '<p><a href="' . get_permalink() . '">' . $display['read_more'] . '</a></p>';
		$output .= '';
	
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
			if (empty($custom['event_anchor'][0])) $custom['event_anchor'][0] = $custom['event_link'][0];
			if (!empty ( $custom['event_link'][0] )) $output .= '<p ' . $style . '>' . $caption .  '<a href="' . $url . '">' . $custom['event_anchor'][0]  . '</a></p>';
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
		<a href="http://maps.google.fr/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q=' . $map . '&amp;t=m">
		<img src="http://maps.googleapis.com/maps/api/staticmap?center=' . $map . '&size=' . $display['map_width'] . 'x' . $display['map_height'] . '&markers=color:blue%7C'.$map.'&sensor=true" />
		</a>
		</div>';
		}
	return $mapurl;
}

class qem_widget extends WP_Widget {
	
	function qem_widget() {
		$widget_ops = array('classname' => 'qem_widget', 'description' => ''.__('Add events to your sidebar', 'quick-event-manager').'');
		$this->WP_Widget('qem_widget', 'Quick Events', $widget_ops);
		}
		
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'posts' => '') );
		$posts = $instance['posts'];
		?>
		<p><label for="<?php echo $this->get_field_id('posts'); ?>"><?php _e('Number of posts to display: ', 'quick-event-manager'); ?><input style="width:3em" id="<?php echo $this->get_field_id('posts'); ?>" name="<?php echo $this->get_field_name('posts'); ?>" type="text" value="<?php echo attribute_escape($posts); ?>" /></label></p>
		<p><?php _e('Leave blank to use the default settings.', 'quick-event-manager') ?></p>
		<p><?php _e('All options for the quick events manager are changed on the plugin', 'quick-event-manager'); ?> <a href="options-general.php?page=quick-event-manager/settings.php"><?php _e('setting page', 'quick-event-manager'); ?></a>.</p>
		
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
	
function qem_head_script () {
	
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
	
	$script = "<style type=\"text/css\" media=\"screen\">\r\n";
	$script .= ".qem {width:".$eventwidth.";}\r\n";
	$script .= ".qem-".$width." {".$eventborder.";".$eventbackground."}\r\n";
	
	if ($style['font'] == 'plugin') $script .= ".qem p {font-family: ".$style['font-family']."; font-size: ".$style['font-size'].";}\r\n";
	$script .= ".qem-calendar-".$width." {".$calendarborder."}\r\n";
	$script .= ".qem-calendar-".$width." .day {".$daycolour.$daybackground."}\r\n";
	
	if ($style['use_custom'] == 'checked') $script .= $style['custom'] . "\r\n";
	
	$script .= "</style>\r\n";
	$script .='<script type=\'text/javascript\'>
		function pseudo_popup(content) {
			var popup = document.createElement("div");
			popup.innerHTML = content;
			var viewport_width = window.innerWidth;
			var viewport_height = window.innerHeight;
		function add_underlay() {
			var underlay = document.createElement("div");
			underlay.style.position = "fixed";
			underlay.style.top = "0px";
			underlay.style.left = "0px";
			underlay.style.width = viewport_width + "px";
			underlay.style.height = viewport_height + "px";
			underlay.style.background = "#7f7f7f";
			if( navigator.userAgent.match(/msie/i) ) {
				underlay.style.background = "#7f7f7f";
				underlay.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=50)";
			} else {
				underlay.style.background = "rgba(127, 127, 127, 0.5)";
			}
			underlay.onclick = function() {
				underlay.parentNode.removeChild(underlay);
				popup.parentNode.removeChild(popup);
				};
			document.body.appendChild(underlay);
			}
		add_underlay();
		var x = viewport_width / 2;
		var y = viewport_height / 2;
		popup.style.position = "fixed";
		document.body.appendChild(popup);
		x -= popup.clientWidth / 2;
		y -= popup.clientHeight / 2;
		popup.style.top = y + "px";
		popup.style.left = x + "px";
		return false;
		}
	</script>';
	echo $script;
}
	
function qem_show_calendar() {
	$cal = qem_get_stored_calendar();
	
	global $post;
	
		$args = array(
		'post_type'			=> 'event',
		'orderby'			=> 'meta_value_num',
		'meta_key'			=> 'event_date',
		'order'				=> 'asc',
		'posts_per_page'	=> -1
		);
	
	// $monthstamp = strtotime('next January'); // bug when activated: when january 2014 it shows october 2014 !
	
	$monthnames = array();		
	for ($i = 0; $i <= 12; $i++) {	
	
		/* i10n */
	$monthnames[] = date_i18n('F', $monthstamp);	// month name calendar header	
		/* end */
		
	$monthstamp = strtotime('+1 month', $monthstamp);
		}
		
	if ($cal['startday'] == 'monday') $timestamp = strtotime('next sunday'); 
	if ($cal['startday'] == 'sunday')  $timestamp = strtotime('next saturday'); 
	$days = array();
	for ($i = 0; $i <= 7; $i++) {
		
		/* i10n */
	$days[] = date_i18n('D', $timestamp);    // day name calendar sub header
		/* end */
	
	$timestamp = strtotime('+1 day', $timestamp);
		}	
		
	$qem_dates = array();
	$unixtime = array();
	$eventtitle = array();
	$eventsummary = array();
	$eventlinks = array();
	$query = new WP_Query( $args );
	
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$eventdate = get_post_meta($post->ID, 'event_date', true);
			$title = get_the_title();
			$link = get_permalink();
			$eventx = get_calendar_details($eventdate);
			array_push($unixtime, $eventdate);
			array_push($eventtitle,$title);
			array_push($eventsummary, $eventx);
			array_push($eventlinks,$link);
			}
		}
		
	wp_reset_postdata();
	
	global $_GET;
	
	if (!isset($_GET["month"])) {
		$_GET["month"] = date_i18n("n");
		}
		
	if (!isset($_GET["qemyear"])) {
		$_GET["qemyear"] = date_i18n("Y");
	}
	
	$currentmonth = $_GET["month"];
	$currentyear = $_GET["qemyear"];
	$p_year = $currentyear;
	$n_year = $currentyear;
	$p_month = $currentmonth-1;
	$n_month = $currentmonth+1;
	
	if ($p_month == 0 ) {
    		$p_month = 12;
    		$p_year = $currentyear - 1;
		}
	if ($n_month == 13 ) {
    		$n_month = 1;
    		$n_year = $currentyear + 1;
		};
		
	$calendar ='
	<style type="text/css">
	#qem-calendar .calday {background:'.$cal['calday'].';}
	#qem-calendar .day {background:'.$cal['day'].';}
	#qem-calendar .eventday {background:'.$cal['eventday'].';}
	#qem-calendar .eventday a {color:'.$cal['eventdaytext'].';border:1px solid '.$cal['eventdaytext'].';}
	#qem-calendar .oldday {background:'.$cal['oldday'].';}
	#qem-calendar td a:hover {background:'.$cal['eventhover'].'; }
	</style>';
	
	if ($cal['connect']) $calendar .='<p><a href="'.$cal['eventlist_url'].'">'.$cal['eventlist_text'].'</a></p>';
	$calendar .='<div id="qem-calendar">
	<table style="width:100%" border="0" cellspacing="3" cellpadding="0">
	<tr class="top">
		<td colspan="1" ><a class="calnav" href="?month='. $p_month . '&amp;qemyear=' . $p_year . '">'.__('&#9668; Prev', 'quick-event-manager').'</a></td>
		<td colspan="5" class="calmonth"><h2>'. $monthnames[$currentmonth-1].' '.$currentyear .'</h2></td>
		<td colspan="1"><a class="calnav" href="?month='. $n_month . '&amp;qemyear=' . $n_year . '">'.__('Next &#9658;', 'quick-event-manager').'</a></td>
	</tr>
	<tr >';
	
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
		$today = strtotime(date_i18n("d M Y", time()));
	for ($i=0; $i<($maxday+$startday); $i++) {
		$oldday ='';
		$xxx = mktime(0,0,0,$currentmonth,$i - $startday+1,$currentyear);
		if (date_i18n("d") > $i - $startday+1 && $currentmonth <= date_i18n("n") && $currentyear == date_i18n("Y"))$oldday = 'oldday';
		if ($currentmonth < date_i18n("n") && $currentyear == date_i18n("Y"))$oldday = 'oldday';
		if ($currentyear < date_i18n("Y"))$oldday = 'oldday';
		$tdstart = '<td class="day '.$oldday.'"><h2>'.($i - $startday+1).'</h2><br>';
		$tdcontent = '';
		
		foreach ($unixtime as $key => $day) {
		$m=date('m', $day);$d=date('d', $day);$y=date('Y', $day);
		$zzz = mktime(0,0,0,$m,$d,$y);
						
			if($xxx==$zzz) {		
			$tdstart = '<td class="eventday '.$oldday.'"><h2>'.($i - $startday+1).'</h2><br>';
				if (strlen($eventtitle[$key]) > 10) $ellipses = ' ...';
					else $ellipses = '';
						$trim = substr($eventtitle[$key], 0 , 10).$ellipses;
				if ($cal['eventlink'] == 'linkpopup' ) $tdcontent .= '<a class="event" onclick=\'pseudo_popup("<div class =\"qempop\">'.$eventsummary[$key].'</div>")\'>'.$trim.'</a>';
					else $tdcontent .= '<a href="' . $eventlinks[$key] . '">' . $trim . '</a>';
			}
		}
			$tdbuilt = $tdstart.$tdcontent.'</td>';
			
			if(($i % 7) == 0 ) $calendar .= '<tr>';
			
			if($i < $startday) $calendar .= '<td ></td>';  
			
				else $calendar .= $tdbuilt;
				
			if(($i % 7) == 6 ) $calendar .= "</tr>";
	}
	
	$calendar .= "</table></div>";
	$unixtime = remove_empty($unixtime);
	
	return $calendar;
}

function remove_empty($array) {
	return array_filter($array, '_remove_empty_internal');
}
	
function _remove_empty_internal($value) {
	return !empty($value) || $value === 0;
}	
	
function get_calendar_details($thedate) {
	global $post;
	
	$thedate = date_i18n('d M Y',$thedate);
	$event = event_get_stored_options();
	$style = qem_get_stored_style();
	$display = event_get_stored_display();
	$custom = get_post_custom();
	$output = '<div class="details"><div class="qem">' . get_event_calendar_icon() . 
					get_event_summary('on') . '</div><div style="clear:both"></div></div>';
	$output = str_replace('"','\"',$output);
	$output = str_replace("'","&#8217;",$output);
	return $output;
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
	
		$event['active_buttons'] = array(
		'field1'	=> 'on',
		'field2'	=> 'on',
		'field3'	=> 'on',
		'field4'	=> 'on',
		'field5'	=> 'on',
		'field6'	=> 'on');	
	
		$event['summary'] = array(
		'field1'	=> 'checked',
		'field2'	=> 'checked',
		'field3'	=> 'checked',
		'field4'	=> '',
		'field5'	=> '',
		'field6'	=> '');
	
		$event['label'] = array(
		'field1'	=> __('Short Description', 'quick-event-manager'), 
		'field2'	=> __('Event Time', 'quick-event-manager'),
		'field3'	=> __('Location', 'quick-event-manager'), 
		'field4'	=> __('Address', 'quick-event-manager'), 
		'field5'	=> __('Event Website', 'quick-event-manager'), 
		'field6'	=> __('Cost', 'quick-event-manager')
		);
	
		$event['sort'] = implode(',',array(
		'field1',
		'field2',
		'field3',
		'field4',
		'field5',
		'field6'));
		
		$event['bold'] = array(
		'field1'	=>'',
		'field2'	=>'checked',
		'field3'	=>'',
		'field4'	=>'',
		'field5'	=>'',
		'field6'	=>'');
		
		$event['italic'] = array(
		'field1'	=> '',
		'field2'	=> '',
		'field3'	=> '',
		'field4'	=> 'checked',
		'field5'	=> '',
		'field6'	=> '');
		
		$event['colour'] = array(
		'field1'	=> '',
		'field2'	=> '#343838',
		'field3'	=> '',
		'field4'	=> '',
		'field5'	=> '',
		'field6'	=> '#008C9E');
		
		$event['size'] = array(
		'field1'	=> '110',
		'field2'	=> '120',
		'field3'	=> '',
		'field4'	=> '',
		'field5'	=> '',
		'field6'	=> '120');
		
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
	$calendar['connect'] = '';
	$calendar['startday'] = 'sunday';
	return $calendar;
}
add_action('admin_menu', 'event_page_init');
add_filter('the_content', 'get_event_content');