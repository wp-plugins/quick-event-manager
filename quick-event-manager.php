<?php
/*
Plugin Name: Quick Event Manager
Plugin URI: http://www.quick-plugins.com/quick-event-manager
Description: A simple event manager. There is nothing to configure, all you need is an event and the shortcode.
Version: 5.8
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
add_shortcode('qemnewevent', 'qem_user_event');

add_action('wp_enqueue_scripts','qem_enqueue_scripts');
add_action('init', 'event_register');
add_action("widgets_init", create_function('', 'return register_widget("qem_widget");') );
add_action("widgets_init", create_function('', 'return register_widget("qem_calendar_widget");') );
add_action('plugins_loaded', 'qem_lang_init');
add_filter("plugin_action_links","event_plugin_action_links", 10, 2 );
add_filter( 'pre_get_posts', 'qem_add_custom_types' );
add_theme_support('post-thumbnails', array('post', 'page', 'event'));
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
		if (is_writable($filename) && (!file_exists($css_dir)) || !empty($update)) {
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
		'supports' => array('title','editor','thumbnail','comments'),
		'show_ui' => true,
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
		'links'=>'checked',
		'daterange'=>'current',
		'size'=>'',
		'headersize'=>'headtwo',
		'settings'=>'checked',
        'images'=>'',
		'category'=>'',
		'order'=>'',
        'fields'=>''
    ),$atts));
	global $post;
    $display = event_get_stored_display();
	$cal = qem_get_stored_calendar();
	ob_start();
	if ($display['event_descending'] || $order == 'asc')
		$args = array('post_type'=> 'event','orderby' => 'meta_value_num','meta_key' => 'event_date','posts_per_page'=> -1);
	else
		$args = array('post_type'=>'event','orderby'=>'meta_value_num','meta_key'=>'event_date','order'=>'asc','posts_per_page'=>-1);
	query_posts( $args );
	$event_found = false;
	$today = strtotime(date('Y-m-d'));
    if ($id == 'all') $all = 'all';
    if ($id == 'current') $monthnumber = date('n');
    if ($id == 'remaining') $remaining = date('n');
    if ($id == 'archive') $archive = 'archive';
    if (is_numeric($id)) $monthnumber = $id;
    if (is_numeric($id) && strlen($id) == 4) $yearnumber = $id;
	if ( have_posts()){
		if ($cal['connect']) $content .='<p><a href="'.$cal['calendar_url'].'">'.$cal['calendar_text'].'</a></p>';
		while (have_posts()) {
			the_post();
			$unixtime = get_post_meta($post->ID, 'event_date', true);
			$enddate = get_post_meta($post->ID, 'event_end_date', true);
            $monthnow = date_i18n("n", $unixtime);
            $month = date_i18n("M", $unixtime);
            $year = date_i18n("Y", $unixtime);
            $thisyear = date('Y');
			if ($i < $posts) {
				if ($all || 
                    (($archive && $unixtime < $today && $enddate < $today) ||
                    ($id == '' && ($unixtime >= $today || $enddate >= $today || $display['event_archive'] == 'checked')) ||
                    ($monthnumber && $monthnow == $monthnumber && $thisyear == $year) ||
                     ($remaining && $monthnow == $remaining && $thisyear == $year && ($unixtime >= $today || $enddate >= $today )) ||
                    ($yearnumber && $yearnumber == $year)) &&
                    (in_category($category) || !$category)
                   ) {
                    if ($display['monthheading'] && ($month != $thismonth || $year != $thisyear)) $content .='<h2>'.$month.' '.$year.'</h2>';
					$content .= qem_event_construct ($links,$size,$headersize,$settings,$fullevent,$images,$fields);
					$event_found = true;
					$i++;$thismonth = $month;$thisyear= $year;
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

function qem_external_permalink( $link, $post ) {
    $meta = get_post_meta( $post->ID, 'event_link', TRUE );
    $url  = esc_url( filter_var( $meta, FILTER_VALIDATE_URL ) );
    return $url ? $url : $link;
    }

function qem_event_construct ($links,$size,$headersize,$settings,$fullevent,$images,$fields){
	global $post;
	$event = event_get_stored_options();
	$display = event_get_stored_display();
	$style = qem_get_stored_style();
	$cal = qem_get_stored_calendar();
	$custom = get_post_custom();
	$register = qem_get_stored_register();
	$payment = qem_get_stored_payment();
	$link = get_post_meta($post->ID, 'event_link', true);
	$endtime = get_post_meta($post->ID, 'event_end_time', true);
	$unixtime = get_post_meta($post->ID, 'event_date', true);
	$enddate = get_post_meta($post->ID, 'event_end_date', true);
	$image = get_post_meta($post->ID, 'event_image', true);
	$cost = get_post_meta($post->ID, 'event_cost', true);
	$usereg = get_post_meta($post->ID, 'event_register', true);
	$usepay = get_post_meta($post->ID, 'event_pay', true);
    $meta = get_post_meta( $post->ID, 'event_link', TRUE );
    $today = strtotime(date('Y-m-d'));
    if ($today > $unixtime && $register['notarchive']) {$register['useform']='';$usereg ='';}
    if ($images == 'off') $image='';
    if ($fields) {
        foreach (explode( ',',$event['sort']) as $name) $event['summary'][$name] = '';
        $derek = explode( ',',$fields);
        foreach ($derek as $item) $event['summary']['field'.$item] = 'checked';
            }
    if ($display['external_link'] && $meta) {
        add_filter( 'post_type_link', 'qem_external_permalink', 10, 2 );
    }
    if (($display['show_end_date'] && $display['sidebyside'] && $enddate) || ($display['sidebyside'] && $enddate && is_singular ('event'))) $join = 'checked';
	else $join='';	
	if($size) $width = '-'.$size;
	else {$size = $style['calender_size']; $width = '-'.$style['calender_size'];}
	$headersize = ($headersize == 'headthree' ? 'h3' : 'h2');
	$content .= '<div class="qem"><div style="float:left;">'.get_event_calendar_icon($size,'event_date',$join);
	if ($join) $content .= '</div><div style="float:left;">';
	if($display['show_end_date'] || is_singular ('event')) $content .= get_event_calendar_icon($size,'event_end_date','');
	$content .= '</div><div class="qem'.$width.'">';
	if (($image && $display['event_image'] && !is_singular ('event')) || ($image && $images)) $content .= '<div style="float:right" ><a href="'.get_permalink() . '"><img class="qem-list-image" src='.$image.'></a></div>';
	if ($image && is_singular ('event')) $content .= '<div style="float:right" ><a href="'.get_permalink() . '"><img class="qem-image" src='.$image.'></a></div>';
	if (!is_singular ('event'))	{
		$content .= '<'.$headersize.' style="display:inline;margin-top:0;padding-top:0;">';
		if ($links == 'checked') $content .=  '<a href="' . get_permalink() . '">' . $post->post_title . '</a>';
		else $content .=  $post->post_title;
		$content .= '</'.$headersize.'>';
		}
	if ($fullevent && !$image && function_exists(file_get_contents)) $content .= get_event_map();
	if ($fullevent) {
 		foreach (explode( ',',$event['sort']) as $name)
		if ($event['active_buttons'][$name]) $content .= build_event($name,$event,$custom,'checked');
		$content .= get_the_content();
		if ($register['useform'] || $usereg ) $content.= qem_loop();
        if (function_exists('qpp_start') && (($payment['useqpp'] && !$payment['qppcost']) || ($payment['qppcost'] && $cost) || $usepay))
            {$atts = array('form'=>$payment['qppform'],'id' => $post->post_title,'amount'=>$cost);$content.= qpp_loop($atts);}
    } else {
		foreach (explode( ',',$event['sort']) as $name)
		if ($event['summary'][$name] == 'checked') $content .= build_event($name,$event,$custom,$settings);
        if ($register['eventlist'] && ($register['useform'] || $usereg )) {
            $num = qem_numberscoming($register,null);
            if (!$num) $content .= '<p class="qem_full">' . $register['eventfullmessage'] . '</p>';
            else $content .= $num;
        }
		if ($links == 'checked') $content .= '<p><a href="' . get_permalink() . '">' . $display['read_more'] . '</a></p>';
    }
	if (is_singular ('event') && $display['back_to_list']) {
if ($display['back_to_url']) $content .= '<a href="'.$display['back_to_url'].'">'.$display['back_to_list_caption'].'</a>';
else  $content .= '<a href="javascript:history.go(-1)">'.$display['back_to_list_caption'].'</a>';
}

    $content .= "<div style='clear:both'></div></div></div>\r\n";
	return $content;
	}

function get_event_calendar_icon($width,$dateicon,$join) {
	global $post;
	$style = qem_get_stored_style();
	$display = event_get_stored_display();
	$mrcombi = '2' * $style['date_border_width'].'px';
	$mr = '5' + $style['date_border_width'].'px';
	$mb = ' 0';
	$tl = '-webkit-border-top-left-radius:0; -moz-border-top-left-radius:0; border-top-left-radius:0;';
	$tr = '-webkit-border-top-right-radius:0; -moz-border-top-right-radius:0; border-top-right-radius:0;';
	$bl = '-webkit-border-bottom-left-radius:0; -moz-border-bottom-left-radius:0; border-bottom-left-radius:0';
	$br = '-webkit-border-bottom-right-radius:0; -moz-border-bottom-right-radius:0; border-bottom-right-radius:0';
    if ($dateicon == 'event_date' && (!$display['sidebyside'] || !$display['combined'])) $mb = ' '.$mr;
    if ($dateicon == 'event_date' && $display['sidebyside'] && $display['combined'] && $join) {$bor = ' style="border-right:none;'.$tr.$br.'"';$mr=' 0';}
    if ($dateicon == 'event_end_date' && $display['sidebyside'] && $display['combined']) {$bor = ' style="border-left:1px solid '.$style['date_border_colour'].';'.$tl.$bl.'"';}
    if ($style['date_bold']) {$boldon = '<b>'; $boldoff = '</b>';}
	if ($style['date_italic']) {$italicon = '<em>'; $italicoff = '</em>';}
	$unixtime = get_post_meta($post->ID, $dateicon, true);
	if ($unixtime){
		$month = date_i18n("M", $unixtime);
		$dayname = date_i18n("D", $unixtime);
		$day = date_i18n("d", $unixtime);
		$year = date_i18n("Y", $unixtime);
		$content = '<div class="qem-calendar-' . $width . '" style="margin:0 '.$mr.$mb.' 0;"><span class="day" '.$bor.'>';
		if ($style['use_dayname']) $content .= '<span>'.$dayname.'</span>';
		$content .= $day.'</span><span class="nonday" '.$bor.'><span class="month">'.$boldon.$italicon.$month.$italicoff.$boldoff.'</span>'.$year.'</span></div>';
		return $content;
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
			if (!empty($event['description_label']))
				$caption = $event['description_label'].' ';
			if (!empty ( $custom['event_desc'][0] ))
				$output .= '<p ' . $style . '>' . $caption . $custom['event_desc'][0] . '</p>';
			break;
		case 'field2':
			if (!empty ( $custom['event_start'][0] )) {
				$output .= '<p ' . $style . '>' . $event['start_label'] . ' ' . $custom['event_start'][0];
				if ( !empty ( $custom['event_finish'][0] )) $output .= ' ' . $event['finish_label'] . ' ' . $custom['event_finish'][0];
			 	$output .= '</p>';}
			break;
		case 'field3':
			if (!empty($event['location_label']))
				$caption = $event['location_label'].' ';
			if (!empty ( $custom['event_location'][0] ))
				$output .= '<p ' . $style . '>' . $caption . $custom['event_location'][0]  . '</p>';
			break;
		case 'field4':
			if (!empty($event['address_label']))
				$caption = $event['address_label'].' ';
			if (!empty ($custom['event_address'][0] ))
				$output .= '<p ' . $style . '>' . $caption . $custom['event_address'][0]  . '</p>';
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
	if (is_singular ('event') ) $content= qem_event_construct ('off',$size,$headersize,'checked','fullevent','','');	
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
		<img src="http://maps.googleapis.com/maps/api/staticmap?center=' . $map . '&size=' . $display['map_width'] . 'x' . $display['map_height'] . '&markers=color:blue%7C'.$map.'&sensor=true" /></a></div>';
		}
	return $mapurl;
	}

class qem_widget extends WP_Widget {
	function qem_widget() {
		$widget_ops = array('classname' => 'qem_widget', 'description' => ''.__('Add and event list to your sidebar', 'quick-event-manager').'');
		$this->WP_Widget('qem_widget', 'Quick Event List', $widget_ops);
		}	
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'posts' => '3','size' =>'small','headersize' => 'headtwo','settings' => '','links' => 'checked') );
		$posts = $instance['posts'];
		$size = $instance['size'];
		$$size = 'checked';
		$headersize = $instance['headersize'];
		$$headersize = 'checked';
		$settings = $instance['settings'];
        $links = $instance['links'];
		if ( isset( $instance[ 'title' ] ) ) {	$title = $instance[ 'title' ];}
		else {$title = __( 'Event List', 'text_domain' );}
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
        <h3>Links</h3>
		<p><input type="checkbox" id="<?php echo $this->get_field_name('links'); ?>" name="<?php echo $this->get_field_name('links'); ?>" value="checked" <?php echo $links; ?>> Link to Event</p>
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
        $instance['links'] = $new_instance['links'];
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

class qem_calendar_widget extends WP_Widget {
	function qem_calendar_widget() {
		$widget_ops = array('classname' => 'qem_calendar_widget', 'description' => ''.__('Add an event calendar to your sidebar', 'quick-event-manager').'');
		$this->WP_Widget('qem_calendar_widget', 'Quick Event Calendar', $widget_ops);
		}	
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'eventlength' => '12','smallicon' => 'trim','unicode' =>'\263A') );
        $eventlength = $instance['eventlength'];		
        $smallicon = $instance['smallicon'];
        $$smallicon = 'checked';
		$unicode = $instance['unicode'];
		if ( isset( $instance[ 'title' ] ) ) {$title = $instance[ 'title' ];}
		else {$title = __( 'Event Calendar', 'text_domain' );}
		?>
		<h3>Event Symbol</h3><p>If there is no room on narrow sidebars for the full calendar details select an alternate symbol below:</p>
		<p>
            <input type="radio" id="<?php echo $this->get_field_name('smallicon'); ?>" name="<?php echo $this->get_field_name('smallicon'); ?>" value="trim" <?php echo $trim; ?>> Event name<br />
            <input type="radio" id="<?php echo $this->get_field_name('smallicon'); ?>" name="<?php echo $this->get_field_name('smallicon'); ?>" value="arrow" <?php echo $arrow; ?>> &#9654;<br />
            <input type="radio" id="<?php echo $this->get_field_name('smallicon'); ?>" name="<?php echo $this->get_field_name('smallicon'); ?>" value="box" <?php echo $box; ?>> &#9633;<br />
            <input type="radio" id="<?php echo $this->get_field_name('smallicon'); ?>" name="<?php echo $this->get_field_name('smallicon'); ?>" value="square " <?php echo $square; ?>> &#9632;<br />
            <input type="radio" id="<?php echo $this->get_field_name('smallicon'); ?>" name="<?php echo $this->get_field_name('smallicon'); ?>" value="asterix" <?php echo $asterix; ?>> &#9733;<br />
            <input type="radio" id="<?php echo $this->get_field_name('smallicon'); ?>" name="<?php echo $this->get_field_name('smallicon'); ?>" value="blank" <?php echo $blank; ?>> Blank<br />
            <input type="radio" id="<?php echo $this->get_field_name('smallicon'); ?>" name="<?php echo $this->get_field_name('smallicon'); ?>" value="other" <?php echo $other; ?>> Other (enter escaped <a href="http://www.fileformat.info/info/unicode/char/search.htm" target="blank">unicode</a> or hex code below)<br />
            <input type="text" id="<?php echo $this->get_field_name('unicode'); ?>" name="<?php echo $this->get_field_name('unicode'); ?>" value="<?php echo esc_attr( $unicode ); ?>" /></p>
<p><?php _e('All other options are changed on the ', 'quick-event-manager'); ?> <a href="options-general.php?page=quick-event-manager/settings.php"><?php _e('settings page', 'quick-event-manager'); ?></a>.</p>
		<?php
		}
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['smallicon'] = $new_instance['smallicon'];
		$instance['unicode'] = $new_instance['unicode'];
		return $instance;
		}
	function widget($args, $instance) {
 	   	extract($args, EXTR_SKIP);
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $args['before_widget'];
		if ( ! empty( $title ) ) echo $args['before_title'] . $title . $args['after_title'];
		echo qem_widget_calendar($instance,'widget');
		echo $args['after_widget'];
		}
	}

function qem_widget_calendar($atts) {
    $arr =array('arrow' => '\25B6','square' => '\25A0','box'=>'\20DE','asterix'=>'\2605','blank'=>' ');
	foreach ($arr as $item => $key)
	if($item == $atts['smallicon']) $smallicon = '#qem-calendar-widget .qemtrim span {display:none;}#qem-calendar-widget .qemtrim:after{content:"'.$key.'";font-size:150%;}';
        return '<div id="qem-calendar-widget"><style>'.$smallicon.'</style>'.qem_show_calendar($atts).'</div>';
    }

function qem_show_calendar($atts) {
	$cal = qem_get_stored_calendar();
	extract(shortcode_atts(array('category'=>''),$atts));
	global $post;
	$args = array('post_type' => 'event','orderby'=> 'meta_value_num','meta_key' => 'event_date','order' => 'asc','posts_per_page' => -1,'category' => '');
	$catarry = explode(",",$category);
    $test = 
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
	$eventdate = array();
    $eventenddate = array();
	$eventtitle = array();
	$eventsummary = array();
	$eventlinks = array();
	$eventslug = array();
	$query = new WP_Query( $args );
	if ( $query->have_posts()) {
		while ( $query->have_posts()) {
			$query->the_post();
			if (in_category($catarry) || !$category) {
				$startdate = get_post_meta($post->ID, 'event_date', true);
                $enddate = get_post_meta($post->ID, 'event_end_date', true);
				$link = get_permalink();
				$cat = get_the_category();
				$slug = $cat[0]->slug;
				$eventx = get_calendar_details();
                $title = get_the_title();
if ($cal['showmultiple']){
                do {
				array_push($eventdate, $startdate);
				array_push($eventtitle,$title);
				array_push($eventslug,$slug);
				array_push($eventsummary, $eventx);
				array_push($eventlinks,$link);
    $startdate = $startdate +(24*60*60);
} while ($startdate <= $enddate);}
                else {array_push($eventdate, $startdate);
				array_push($eventtitle,$title);
				array_push($eventslug,$slug);
				array_push($eventsummary, $eventx);
				array_push($eventlinks,$link);}
				}
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
	$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $parts = explode("&",$actual_link);
    $actual_link = $parts['0']; 
    $link = (strpos($actual_link,'?') ? '&' : '?');
    $catkey ='<p>'.$cal['keycaption'];
    $cat = array('a','b','c','d','e','f');
    $arr = get_categories();
    foreach ($cat as $i) {
        foreach($arr as $option){
		if ($cal['cat'.$i] == $option->slug) $thecat = $option->name;
        }
    if ($cal['cat'.$i]) $catkey .= '<div style="float:left; width: 1.5em; height: 1em; background:'.$cal['cat'.$i.'back'].';margin-right: 4px;"></div><div style="float:left;margin-right: 8px;">'.$thecat.'</div>';
        }
    $catkey .='<div style="clear:left;"></div></p>';
    if ($cal['showkeyabove']) $calendar .= $catkey;
    $calendar .='<div id="qem-calendar">
		<table style="width:100%" border="0" cellspacing="3" cellpadding="0">
		<tr class="top">
		<td colspan="1" ><a class="calnav" href="'.$actual_link.$link.'qemmonth='. $p_month . '&amp;qemyear=' . $p_year . '">&#9668;  '.$cal['prevmonth'].'</a></td>
		<td colspan="5" class="calmonth"><h2>'. $monthnames[$currentmonth-1].' '.$currentyear .'</h2></td>
		<td colspan="1"><a class="calnav" href="'.$actual_link.$link.'qemmonth='. $n_month . '&amp;qemyear=' . $n_year . '">'.$cal['nextmonth'].'  &#9658;</a></td>
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
		foreach ($eventdate as $key => $day) {
            $m=date('m', $day);$d=date('d', $day);$y=date('Y', $day);
			$zzz = mktime(0,0,0,$m,$d,$y);
			if($xxx==$zzz && $show) {	
				$tdstart = '<td class="eventday '.$oldday.'"><h2>'.($i - $startday+1).'</h2>';
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
    if ($cal['showkeybelow']) $calendar .= $catkey;
	$eventdate = remove_empty($eventdate);
	return $calendar;
	}

function remove_empty($array) {return array_filter($array, '_remove_empty_internal');}
function _remove_empty_internal($value) {return !empty($value) || $value === 0;}

function get_calendar_details() {
	global $post;
	$event = event_get_stored_options();
	$style = qem_get_stored_style();
	$width = $style['calender_size'];
	$display = event_get_stored_display();
	$custom = get_post_custom();
	$output = '<div style="float:left">' . get_event_calendar_icon($width,'event_date','').'</div><div class="qem-'.$width.'"><h2 style="display:inline;margin-top:0;padding-top:0;"><a href="' . get_permalink() . '">' . $post->post_title . '</a></h2>';
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
    $display = event_get_stored_display();
	$register = qem_get_stored_register();
	if ($style['calender_size'] == 'small') {$width = 'small';$radius = 7;$size='50px';}
	if ($style['calender_size'] == 'medium') {$width = 'medium';$radius = 10;$size='70px';}
	if ($style['calender_size'] == 'large') {$width = 'large';$radius = 15;$size='90px';}
    $rm = $size+5+($style['date_border_width']).'px';
	if ($style['date_background'] == 'color') $color = $style['date_backgroundhex'];
	if ($style['date_background'] == 'grey') $color = '#343838';
	if ($style['date_background'] == 'red') $color = 'red';
	if ($style['event_background'] == 'bgwhite') $eventbackground = 'background:white;';
	if ($style['event_background'] == 'bgcolor') $eventbackground = 'background:'.$style['event_backgroundhex'].';';
	$dayborder = 'color:' . $style['date_colour'].';background:'.$color.'; border: '. $style['date_border_width']. 'px solid ' .$style['date_border_colour'].';border-bottom:none;';
	$nondayborder = 'border: '. $style['date_border_width']. 'px solid ' .$style['date_border_colour'].';border-top:none;';
	$eventborder = 'border: '. $style['date_border_width']. 'px solid ' .$style['date_border_colour'].';';
	if ($style['icon_corners'] == 'rounded') {
		$dayborder = $dayborder.'-webkit-border-top-left-radius:'.$radius.'px; -moz-border-top-left-radius:'.$radius.'px; border-top-left-radius:'.$radius.'px; -webkit-border-top-right-radius:'.$radius.'px; -moz-border-top-right-radius:'.$radius.'px; border-top-right-radius:'.$radius.'px;';
		$nondayborder = $nondayborder.'-webkit-border-bottom-left-radius:'.$radius.'px; -moz-border-bottom-left-radius:'.$radius.'px; border-bottom-left-radius:'.$radius.'px; -webkit-border-bottom-right-radius:'.$radius.'px; -moz-border-bottom-right-radius:'.$radius.'px; border-bottom-right-radius:'.$radius.'px;';
		$eventborder = $eventborder.'-webkit-border-radius:'.$radius.'px; -moz-border-radius:'.$radius.'px; border-radius:'.$radius.'px;';
		}
	if ($style['event_border']) $showeventborder = 'border: '. $style['date_border_width']. 'px solid; padding:'.$radius.'px;'.$eventborder;
	if ($register['formborder']) $formborder = ".qem-register {".$calendarborder."padding:".$radius.";}\r\n";
	if ($style['widthtype'] == 'pixel') $eventwidth = preg_replace("/[^0-9]/", "", $style['width']) . 'px;';
	else $eventwidth = '100%';
	if ($display['event_image_width']) $i = preg_replace ( '/[^.,0-9]/', '', $display['event_image_width']);
    else $i = '300';
    if ($display['image_width']) $j = preg_replace ( '/[^.,0-9]/', '', $display['image_width']);
    else $j = '300';
    $arr =array('arrow' => '\25B6','square' => '\25A0','box'=>'\20DE','asterix'=>'\2605','blank'=>' ');
	foreach ($arr as $item => $key)
	if($item == $cal['smallicon']) $smallicon = '@media only screen and (max-width: 480px) {
	       .qemtrim span {display:none;}.qemtrim:after{content:"'.$key.'";font-size:150%;}}
           #qem-calendar-widget h2 {font-size: 1em;}
           #qem-calendar-widget .qemtrim span {display:none;}
           #qem-calendar-widget .qemtrim:after{content:"'.$key.'";font-size:150%;}';
	$script .= ".qem {width:".$eventwidth.";".$style['event_margin'].";}\r\n";
    $script .= ".qem p {".$style['line_margin'].";}\r\n";
	$script .= ".qem-small, .qem-medium, .qem-large {".$showeventborder.$eventbackground."}\r\n";
	$script .= $formborder;
    $script .= "img.qem-image {max-width:".$i."px;height:auto;overflow:hidden;}\r\n";
    $script .= "img.qem-list-image {width:100%;max-width:".$j."px;height:auto;overflow:hidden;}\r\n";
    $script .= ".qem-calendar-".$width."{width:".$size.";}\r\n";
	$script .= ".qem-".$width."{margin-left:".$rm.";}\r\n";
	$script .= ".qem-calendar-small .nonday, .qem-calendar-medium .nonday, .qem-calendar-large .nonday {display:block;".$nondayborder."}\r\n";
	$script .= ".qem-calendar-small .day, .qem-calendar-medium .day, .qem-calendar-large .day {".$daycolor.$dayborder."}\r\n";
	if ($style['font'] == 'plugin') $script .= ".qem p {font-family: ".$style['font-family']."; font-size: ".$style['font-size'].";}\r\n";
    $script .= ".qem h2, .qem h2 a{font-size: ".$style['header-size'].";color:".$style['header-colour']."}\r\n";
	if ($style['use_custom'] == 'checked') $script .= $style['custom'] . "\r\n";
	$script .="#qem-calendar .calday {background:".$cal['calday']."; color:".$cal['caldaytext']."}\r#qem-calendar .day {background:".$cal['day'].";}\r#qem-calendar .eventday {background:".$cal['eventday'].";}\r#qem-calendar .eventday a {color:".$cal['eventdaytext'].";border:1px solid ".$cal['eventdaytext'].";}\r#qem-calendar .oldday {background:".$cal['oldday'].";}\r#qem-calendar td a:hover {background:".$cal['eventhover']." !important;}";
    if ($cal['eventbold']) $eventbold = 'font-weight:bold;';
     if ($cal['eventitalic']) $eventitalic = 'font-style:italic;';
        $script .= ".qemtrim span {".$eventbold.$eventitalic."}";
   $script .="#qem-calendar .eventday a {color:".$cal['eventtext']." !important;background:".$cal['eventbackground']." !important;border:".$cal['eventborder']." !important;}\r";
	$cat = array('a','b','c','d','e','f');
	foreach ($cat as $i) {
		if ($cal['cat'.$i]) {
			$script .="#qem-calendar a.".$cal['cat'.$i]." {background:".$cal['cat'.$i.'back']." !important;color:".$cal['cat'.$i.'text']." !important;border:1px solid ".$cal['cat'.$i.'text']." !important;}\r";
			}
		}
	$script .= $smallicon;	
	return $script;
	}

function qem_head_css () {
	$data = '<style type="text/css" media="screen">'.qem_generate_css().'</style>';
	echo $data;
	}

function qem_loop() {
	ob_start();
	if (isset($_POST['qemregister'])) {
		$formvalues = $_POST;
		$formerrors = array();
		if (!qem_verify_form($formvalues, $formerrors)) qem_display_form($formvalues, $formerrors);
    	else qem_process_form($formvalues);
	} else {
        $values = qem_get_stored_register();
        if ( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            $values['yourname'] = $current_user->user_login;
            $values['youremail'] = $current_user->user_email;
            }
		$values['yourplaces'] = '1';
        $digit1 = mt_rand(1,10);
		$digit2 = mt_rand(1,10);
		if( $digit2 >= $digit1 ) {
		$values['thesum'] = "$digit1 + $digit2";
		$values['answer'] = $digit1 + $digit2;
		} else {
		$values['thesum'] = "$digit1 - $digit2";
		$values['answer'] = $digit1 - $digit2;}
		qem_display_form( $values ,null);
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
        if ($register['whosavatar']) {
            $content .= '<p>';
            foreach($whoscoming as $item => $value)
                $content .= '<img title="'.$item.'" src="http://www.gravatar.com/avatar/' . md5($value) . '?s=40&&d=identicon" />&nbsp;';
            $content .= '</p>';
            }
        return $content;
        }
    }

function qem_numberscoming($register,$values) {
	$event = get_the_ID();
    global $post;
    $number = get_post_meta($post->ID, 'event_number', true);
    $check = get_post_meta($post->ID, 'event_counter', true);
    $eventnumber = get_option($event.'places');
    if ($eventnumber == 'full') return '';
    if (!$eventnumber) $eventnumber = $number;
	if ($check) {
		$content = '<p id="whoscoming">'.$register['placesbefore'].' '.$eventnumber.' '.$register['placesafter'].'<p>';
        return $content;
        }
    }

function qem_display_form( $values, $errors ) {
    $register = qem_get_stored_register();
    global $post;
    $check = get_post_meta($post->ID, 'event_counter', true);
    $num = qem_numberscoming($register,$values);
    if (!$num && $check) $content = '<h2>' . $register['eventfullmessage'] . '</h2>';
    else {
        if (!empty($register['title'])) $register['title'] = '<h2>' . $register['title'] . '</h2>';
        if (!empty($register['blurb'])) $register['blurb'] = '<p>' . $register['blurb'] . '</p>';
        $content = qem_whoscoming($register);
        if (count($errors) > 0) {
        $content .= "<h2 style='color:red'>" . $register['error'] . "</h2>\r\t";
        $arr = array('yourname','youremail','yourtelephone','yourplaces','yourmessage','youranswer');
        foreach ($arr as $item) if ($errors[$item] == 'error') $errors[$item] = ' style="border:1px solid red;" ';
        if ($errors['yourplaces']) $errors['yourplaces'] = 'border:1px solid red;';
        if ($errors['youranswer']) $errors['youranswer'] = 'border:1px solid red;';
        }
        else $content .= $register['title'] . $register['blurb'];
        $content .= $num;
        $content .= '<div class="qem-register">';
        $content .= '<form action="" method="POST" enctype="multipart/form-data">';
        if ($register['usename'])
            $content .= '<input id="yourname" name="yourname"'.$errors['yourname'].' type="text" value="'.$values['yourname'].'" onblur="if (this.value == \'\') {this.value = \''.$values['yourname'].'\';}" onfocus="if (this.value == \''.$values['yourname'].'\') {this.value = \'\';}" />';
        if ($register['usemail']) 
            $content .= '<input id="email" name="youremail"'.$errors['youremail'].' type="text" value="'.$values['youremail'].'" onblur="if (this.value == \'\') {this.value = \''.$values['youremail'].'\';}" onfocus="if (this.value == \''.$values['youremail'].'\') {this.value = \'\';}" />';
        if ($register['usetelephone']) 
            $content .= '<input id="email" name="yourtelephone"'.$errors['yourtelephone'].' type="text" value="'.$values['yourtelephone'].'" onblur="if (this.value == \'\') {this.value = \''.$values['yourtelephone'].'\';}" onfocus="if (this.value == \''.$values['yourtelephone'].'\') {this.value = \'\';}" />';
        if ($register['useplaces']) 
            $content .= '<input id="yourplaces" name="yourplaces" type="text" style="'.$errors['yourplaces'].'width:3em;margin-right:5px" value="'.$values['yourplaces'].'" onblur="if (this.value == \'\') {this.value = \''.$values['yourplaces'].'\';}" onfocus="if (this.value == \''.$values['yourplaces'].'\') {this.value = \'\';}" />'.$register['yourplaces'].'<br>';
        if ($register['usemessage']) 
            $content .= '<textarea rows="4" label="message" name="yourmessage"'.$errors['yourmessage'].' onblur="if (this.value == \'\') {this.value = \''.$values['yourmessage'].'\';}" onfocus="if (this.value == \''.$values['yourmessage'].'\') {this.value = \'\';}" />' . stripslashes($values['yourmessage']) . '</textarea>';
        if ($register['usecaptcha']) 
            $content .= $values['thesum'].' = <input id="youranswer" name="youranswer" type="text" style="'.$errors['youranswer'].'width:3em;"  value="'.$values['youranswer'].'" onblur="if (this.value == \'\') {this.value = \''.$values['youranswer'].'\';}" onfocus="if (this.value == \''.$values['youranswer'].'\') {this.value = \'\';}" /><input type="hidden" name="answer" value="' . strip_tags($values['answer']) . '" />
    <input type="hidden" name="thesum" value="' . strip_tags($values['thesum']) . '" />';
	$content .= '<input type="submit" value="'.$register['qemsubmit'].'" id="submit" name="qemregister" />
    </form>
	<div style="clear:both;"></div></div>';
        }
	echo $content;
	}

function qem_verify_form(&$values, &$errors) {
	$register = qem_get_stored_register();
	if ($register['usemail'] && !filter_var($values['youremail'], FILTER_VALIDATE_EMAIL)) $errors['youremail'] = 'error';
	$values['yourname'] = filter_var($values['yourname'], FILTER_SANITIZE_STRING);
	if ($register['usename'] && (empty($values['yourname']) || $values['yourname'] == $register['yourname'])) $errors['yourname'] = 'error';
	$values['youremail'] = filter_var($values['youremail'], FILTER_SANITIZE_STRING);
	if ($register['usemail'] && (empty($values['youremail']) || $values['youremail'] == $register['youremail'])) $errors['youremail'] = 'error';
    $values['yourplaces'] = preg_replace ( '/[^0-9]/', '', $values['yourplaces']);
    if ($register['useplaces'] && empty($values['yourplaces'])) $values['yourplaces'] = '1';
	$values['yourmessage'] = filter_var($values['yourmessage'], FILTER_SANITIZE_STRING);
    if ($register['usecaptcha'] && (empty($values['youranswer']) || $values['youranswer'] <> $values['answer'])) $errors['youranswer'] = 'error';
    $values['youranswer'] = filter_var($values['youranswer'], FILTER_SANITIZE_STRING);
    $event = get_the_ID();
    $eventnumber = get_option($event.'places');
    if ($eventnumber && $values['yourplaces'] > $eventnumber) $errors['yourplaces'] = 'error';
	return (count($errors) == 0);	
	}

function qem_process_form($values) {
    global $post;
    $date = get_post_meta($post->ID, 'event_date', true);
    $places = get_post_meta($post->ID, 'event_number', true);
	$date = date_i18n("d M Y", $date);
	$register = qem_get_stored_register();
	if (empty($register['sendemail'])) {
        global $current_user;
        get_currentuserinfo();
        $qem_email = $current_user->user_email;
    }
	else $qem_email = $register['sendemail'];
	$subject = $register['subject'];
    if ($register['subjecttitle']) $subject = $subject.' '.get_the_title();
    if ($register['subjectdate']) $subject = $subject.' '.$date;
	if (empty($subject)) $subject = 'Event Register';
	$content .= '<html>';
	if ($register['usename']) $content .= '<p><b>' . $register['yourname'] . ': </b>' . strip_tags(stripslashes($values['yourname'])) . '</p>';
	if ($register['usemail']) $content .= '<p><b>' . $register['youremail'] . ': </b>' . strip_tags(stripslashes($values['youremail'])) . '</p>';
    if ($register['usetelephone']) $content .= '<p><b>' . $register['yourtelephone'] . ': </b>' . strip_tags(stripslashes($values['yourtelephone'])) . '</p>';
    if ($register['useplaces']) $content .= '<p><b>' . $register['yourplaces'] . ': </b>' . strip_tags(stripslashes($values['yourplaces'])) . '</p>';
    if ($register['usemessage']) $content .= '<p><b>' . $register['yourmessage'] . ': </b>' . strip_tags(stripslashes($values['yourmessage'])) . '</p>';
    $content .= '</html>';
	$event = get_the_ID();
	$whoscoming = get_option($event);
	if(!is_array($whoscoming)) $whoscoming = array();                           
	$whoscoming[$values['yourname']] = $values['youremail'];
	update_option( $event, $whoscoming );
    $eventnumber = get_option($event.'places');
    if (!$eventnumber) $eventnumber = $places;
    if (!is_numeric($values['yourplaces'])) $values['yourplaces'] = 1;
    $eventnumber = $eventnumber - $values['yourplaces'];
    if ($eventnumber < 1) $eventnumber = 'full';
    update_option( $event.'places', $eventnumber );
	$headers = "From: {$values['yourname']} <{$values['youremail']}>\r\n"
		. "MIME-Version: 1.0\r\n"
		. "Content-Type: text/html; charset=\"utf-8\"\r\n";	
	wp_mail($qem_email, $subject, $content, $headers);
	if (!empty($register['replytitle'])) $register['replytitle'] = '<h2>' . $register['replytitle'] . '</h2>';
	if (!empty($register['replyblurb'])) $register['replyblurb'] = '<p>' . $register['replyblurb'] . '</p>';
	$replycontent = qem_whoscoming($register);
	$replycontent .= $register['replytitle'].$register['replyblurb'];
    $replycontent .='<p><a href="' . get_permalink() . '">' . $register['read_more'] . '</a></p>';
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
	$event['label'] = array('field1'=> __('Short Description', 'quick-event-manager'),'field2' => __('Event Time', 'quick-event-manager'),'field3' => __('Location', 'quick-event-manager'), 'field4' => __('Address', 'quick-event-manager'), 'field5' => __('Event Website', 'quick-event-manager'), 'field6' => __('Cost', 'quick-event-manager'));
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
	$version = get_option('qem_version');
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
	$display['sidebyside'] = 'checked';
	$display['event_image'] = '';
    $display['monthheading'] = '';
    $display['back_to_list_caption'] = 'Return to Event list';
	$display['image_width'] = '300';
    $display['event_image_width'] = '300';
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
    $style['header-size'] = '100%';
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
    $style['line_margin'] = 'margin: 0 0 8px 0;padding: 0 0 0 0';
	$style['custom'] = ".qem {\r\n}\r\n.qem h2{\r\n}";
	$style['combined'] = 'checked';
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
    $calendar['eventbackground'] = '#FFF';
    $calendar['eventtext'] = '#343838';
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
    $calendar['prevmonth'] = 'Prev';
    $calendar['nextmonth'] = 'Next';
	$calendar['smallicon'] = 'arrow';
	$calendar['unicode'] = '\263A';
    $calendar['eventtext'] = '#343838';
    $calendar['eventbackground'] = '#FFF';
    $calendar['eventhover'] = '#EED1AC';
    $calendar['eventborder'] = '1px solid #343838';
    $calendar['keycaption'] = 'Event Key:';
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
    $register['yourtelephone'] = 'Telephone Number';
	$register['yourplaces'] = 'Number of places required';
	$register['yourmessage'] = 'Message';
	$register['error'] = 'Please complete the form';
	$register['subject'] = 'Registration for:';
    $register['subjecttitle'] = 'checked';
    $register['subjectdate'] = 'checked';
	$register['whoscoming'] = '';
	$register['whoscomingmessage'] = 'Look who\'s coming: ';
    $register['placesbefore'] = 'There are';
    $register['placesafter'] = 'places available.';
    $register['eventfull'] = '';
    $register['eventfullmessage'] = 'Registration is closed';
    $register['read_more'] = 'Return to the event page';
	return $register;
	}

function qem_get_stored_payment () {
	$payment = get_option('qem_payment');
	if(!is_array($payment)) $payment = array();
	$default = qem_get_default_payment();
	$payment = array_merge($default, $payment);
	return $payment;
	}

function qem_get_default_payment () {
	$payment = array();
	$payment['useqpp'] = 'useqppselect';
	$payment['qppform'] = 'default';
	return $payment;
	}

add_action('admin_menu', 'event_page_init');
add_filter('the_content', 'get_event_content');