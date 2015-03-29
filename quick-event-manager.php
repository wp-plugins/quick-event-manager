<?php
/*
Plugin Name: Quick Event Manager
Plugin URI: http://www.quick-plugins.com/quick-event-manager
Description: A simple event manager. There is nothing to configure, all you need is an event and the shortcode.
Version: 6.0
Author: aerin
Author URI: http://www.quick-plugins.com
Text Domain: qme
Domain Path: /languages
*/

require_once( plugin_dir_path( __FILE__ ) . '/quick-event-options.php' );
require_once( plugin_dir_path( __FILE__ ) . '/quick-event-akismet.php' );
require_once( plugin_dir_path( __FILE__ ) . '/quick-event-register.php' );
require_once( plugin_dir_path( __FILE__ ) . '/quick-event-payments.php' );

if (is_admin()) {
    require_once( plugin_dir_path( __FILE__ ) . '/settings.php' );
    require_once( plugin_dir_path( __FILE__ ) . '/quick-event-editor.php' );
}

add_shortcode("qem","qem_event_shortcode");
add_shortcode('qem-calendar', 'qem_show_calendar');
add_shortcode('qemcalendar', 'qem_show_calendar');
add_shortcode('qemnewevent', 'qem_user_event');
add_shortcode('qemreport', 'qem_registration_report');

add_action('wp_enqueue_scripts','qem_enqueue_scripts');
add_action('init', 'event_register');
add_action("widgets_init", create_function('', 'return register_widget("qem_widget");') );
add_action("widgets_init", create_function('', 'return register_widget("qem_calendar_widget");') );
add_action('plugins_loaded', 'qem_lang_init');
add_filter("plugin_action_links","event_plugin_action_links", 10, 2 );
add_filter( 'pre_get_posts', 'qem_add_custom_types' );

add_filter('wp_dropdown_users', 'qem_users');

add_theme_support('post-thumbnails', array('post', 'page', 'event'));

register_activation_hook(__FILE__, 'qem_flush_rules');

function qem_flush_rules() {
    event_register();
    flush_rewrite_rules();
}

$display = event_get_stored_display();
if ($display['recentposts']) add_filter( 'widget_posts_args', 'qem_recent_posts_args');
$style = qem_get_stored_style();
if ($style['use_head']) add_action('wp_head', 'qem_head_css');

function qem_recent_posts_args($args) {
    $args['post_type'] = array('post', 'event');
    return $args;
}

function qem_enqueue_scripts() {
    wp_enqueue_style('event_style',plugins_url('quick-event-manager.css', __FILE__));
    wp_enqueue_style('event_custom',plugins_url('quick-event-manager-custom.css', __FILE__));
    wp_enqueue_script('event_script',plugins_url('quick-event-manager.js', __FILE__));
	wp_enqueue_script('event_lightbox',plugins_url('quick-event-lightbox.js', __FILE__ ), array( 'jquery' ), false, true );
}

function qem_create_css_file ($update) {
    if (function_exists('file_put_contents')) {
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
    qem_generate_csv();
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
            'capability_type' => array('event','events'),
            'map_meta_cap' => true,
            'hierarchical' => false,
            'has_archive' => true,
            'menu_position'	=> null,
            'taxonomies' => array('category','post_tag'),
            'supports' => array('title','editor','thumbnail','comments','excerpt'),
            'show_ui' => true,
        );
        register_post_type('event',$args);
    }
}

function qem_add_custom_types( $query ) {
    if( !is_admin() && $query->is_category() || $query->is_tag() && $query->is_main_query() ) {
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

function qem_event_shortcode($atts,$widget) {
    extract(shortcode_atts(array(
        'fullevent'=>'',
        'id'=> '',
        'posts'=> '99',
        'links'=>'checked',
        'daterange'=>'current',
        'size'=>'',
        'headersize'=>'headtwo',
        'settings'=>'checked',
        'vanillawidget'=>'checked',
        'images'=>'',
        'category'=>'',
        'order'=>'',
        'fields'=>'',
        'listlink'=>'',
        'listlinkanchor'=>'',
        'listlinkurl'=>'',
        'cb'=>'',
        'y'=>'',
        'categorykeyabove'=>'',
        'categorykeybelow'=>'',
        'usecategory'=>''
    ),$atts));
    global $post;
    global $_GET;
    if (isset($_GET['category'])) $category = $_GET['category'];
    $display = event_get_stored_display();
    $cal = qem_get_stored_calendar();
    $style = qem_get_stored_style();
    if (!$listlinkurl) $listlinkurl = $display['back_to_url'];
    if (!$listlinkanchor) $listlinkanchor = $display['back_to_list_caption'];
    if ($listlink) $listlink='checked';
    if ($cb) $style['cat_border'] = 'checked';
    ob_start();
    if ($display['event_descending'] || $order == 'asc')
        $args = array(
        'post_type'=> 'event',
        'orderby' => 'meta_value_num',
        'meta_key' => 'event_date',
        'posts_per_page'=> -1
    );
    else
        $args = array(
        'post_type'=>'event',
        'orderby'=>'meta_value_num',
        'meta_key'=>'event_date',
        'order'=>'asc',
        'posts_per_page'=> -1
    );
    $the_query = new WP_Query( $args );
    $event_found = false;
    $today = strtotime(date('Y-m-d'));
    $remaining='';$all='';$i='';$monthnumber='';$archive='';$yearnumber='';$content='';
    if ($usecategory) $cb = 'checked';
    if (!$widget && $style['cat_border'] && $style['showkeyabove']) $content .= qem_category_key($cal,$style,'');
    if ($widget && $usecategory && $categorykeyabove) $content .= qem_category_key($cal,$style,'');
    if ($category && $style['showcategory']) $content .= '<h2>'.$style['showcategorycaption'].' '.$category.'</h2>';
    if ($id == 'all') $all = 'all';
    if ($id == 'current') $monthnumber = date('n');
    if ($id == 'remaining') $remaining = date('n');
    if ($id == 'archive') $archive = 'archive';
    if (is_numeric($id)) $monthnumber = $id;
    if (is_numeric($id) && strlen($id) == 4) $yearnumber = $id;
    if ($id == 'calendar') {
        if (isset($_GET['qemmonth'])) {$monthnumber = $_GET['qemmonth'];}
        else $monthnumber = date('n');
    }
    if (strpos($id,'D') !== false) {$daynumber = filter_var($id, FILTER_SANITIZE_NUMBER_INT);}
    $thisyear = date('Y');$thismonth = date("M");$currentmonth = date("M");
    if ( $the_query->have_posts()){
        if ($cal['connect']) $content .='<p><a href="'.$cal['calendar_url'].'">'.$cal['calendar_text'].'</a></p>';
        while ($the_query->have_posts()) {
            $the_query->the_post();
            $unixtime = get_post_meta($post->ID, 'event_date', true);
            if (!$unixtime) $unixtime = time();
            $enddate = get_post_meta($post->ID, 'event_end_date', true);
            $hide_event = get_post_meta($post->ID, 'hide_event', true);
            $day = date_i18n("d", $unixtime);
            $monthnow = date_i18n("n", $unixtime);
            $month = date_i18n("M", $unixtime);
            $year = date_i18n("Y", $unixtime);
            if ($y) {$thisyear = $y;$yearnumber = 0;}
            if ($i < $posts) {
                if (//All Events
                    $all ||                                                     
                    // Archive Events
                    (($archive && $unixtime < $today && $enddate < $today) ||
                    // All if no ID
                    ($id == '' && ($unixtime >= $today || $enddate >= $today || $display['event_archive'] == 'checked')) ||
                    // Today only
                    ($daynumber == $day && $thismonth == $month && $thisyear == $year) ||
                    // This month
                    ($monthnumber && $monthnow == $monthnumber && $thisyear == $year) ||
                    // Rest of the month
                    ($remaining && $monthnow == $remaining && $thisyear == $year && ($unixtime >= $today || $enddate >= $today )) ||
                    // This year
                    ($yearnumber && $yearnumber == $year)) &&
                    // This category
                    (in_category($category) || !$category) 
                ) {
                    if ($display['monthheading'] && ($currentmonth || $month != $thismonth || $year != $thisyear)) 
                        $content .='<h2>'.$month.' '.$year.'</h2>';
                    if (!$hide_event) 
                        $content .= qem_event_construct ($links,$size,$headersize,$settings,$fullevent,$images,$fields,$widget,$cb,$vanillawidget,$display['linkpopup']);
                    $event_found = true;
                    $i++;
                    $thismonth = $month;
                    $thisyear = $year;
                    $currentmonth = '';
                }
            }
        }
        if (!$widget && $style['cat_border'] && $style['showkeybelow']) $content .= qem_category_key($cal,$style,'');
        if ($widget && $usecategory && $categorykeyabove) $content .= qem_category_key($cal,$style,'');
        if ($listlink) $content .= '<p><a href="'.$listlinkurl.'">'.$listlinkanchor.'</a></p>';
        echo $content;
    }
    if (!$event_found) echo "<h2>".$display['noevent']."</h2>";
    wp_reset_postdata();
    $output_string = ob_get_contents();
    ob_end_clean();
    return $output_string;
}	

function qem_external_permalink( $link, $post ) {
    $meta = get_post_meta( $post->ID, 'event_link', TRUE );
    $url  = esc_url( filter_var( $meta, FILTER_VALIDATE_URL ) );
    return $url ? $url : $link;
}

function qem_category_key($cal,$style,$calendar) {
    $cat = array('a','b','c','d','e','f');
    $arr = get_categories();
    $pageurl = qem_current_page_url();
    $parts = explode("&",$pageurl);
    $pageurl = $parts['0'];
    $link = (strpos($pageurl,'?') ? '&' : '?');
    if ($style['linktocategories'])
        $catkey = '<style>.qem-category:hover {background: #CCC !important;border-color: #343848 !important;}.qem-category a:hover {color:#383848 !important;}</style>';
    $catkey .= ($calendar ? '<p>'.$cal['keycaption'] : '<p>'.$style['keycaption']);
    foreach ($cat as $i) {
        foreach($arr as $option) if ($cal['cat'.$i] == $option->slug) $thecat = $option->name;
	if ($cal['cat'.$i]) {
            if ($calendar) {
                if ($cal['linktocategories']) {
                    $catkey .= '<div class="qem-category" style="border:1px solid '.$cal['cat'.$i.'text'].';background:'.$cal['cat'.$i.'back'].'"><a style="color:'.$cal['cat'.$i.'text'].'" href="'.$pageurl.$link.'category='.$thecat.'">'.$thecat.'</a></div>';
                } else {
                    $catkey .= '<div class="qem-category" style="border:1px solid '.$cal['cat'.$i.'text'].';background:'.$cal['cat'.$i.'back'].';color:'.$cal['cat'.$i.'text'].';">'.$thecat.'</div>';
                }
                if ($cal['showuncategorised'])
                    $catkey .= '<div class="qem-category" style="border:'.$style['date_border_width'].'px solid '.$style['date_border_colour'].';">Uncategorised</div>';
            } else {
                if ($style['linktocategories']) {
                    $catkey .= '<div class="qem-category" style="border:'.$style['date_border_width'].'px solid '.$cal['cat'.$i.'back'].';"><a href="'.$pageurl.$link.'category='.$thecat.'">'.$thecat.'</a></div>';
                } else {
                    $catkey .= '<div class="qem-category" style="border:'.$style['date_border_width'].'px solid '.$cal['cat'.$i.'back'].';">'.$thecat.'</div>';
                }
                if ($style['showuncategorised'])
                    $catkey .= '<div class="qem-category" style="border:'.$style['date_border_width'].'px solid '.$style['date_border_colour'].';">Uncategorised</div>';
            }
        }
    }
    $catkey .='</p><div style="clear:left;"></div>';
    return $catkey;
}

function qem_event_construct ($links,$size,$headersize,$settings,$fullevent,$images,$fields,$widget,$cb,$vw,$popup) { 
    global $post;
    $event = event_get_stored_options();
    $display = event_get_stored_display();
    $vertical = $display['vertical'];
    $style = qem_get_stored_style();
    $cal = qem_get_stored_calendar();
    $custom = get_post_custom();
    $link = get_post_meta($post->ID, 'event_link', true);
    $endtime = get_post_meta($post->ID, 'event_end_time', true);
    $endmonth=$amalgamated='';
    $unixtime = get_post_meta($post->ID, 'event_date', true);
    $enddate = get_post_meta($post->ID, 'event_end_date', true);
    $image = get_post_meta($post->ID, 'event_image', true);
    if (!$popup && is_singular ('event')) {
        $register = qem_get_stored_register();
        $payment = qem_get_stored_payment();
        $cost = get_post_meta($post->ID, 'event_cost', true);
        $usereg = get_post_meta($post->ID, 'event_register', true);
        $usecounter = get_post_meta($post->ID, 'event_counter', true);
        $usepay = get_post_meta($post->ID, 'event_pay', true);
    }
    $today = strtotime(date('Y-m-d'));
    $category = get_the_category();
    $cat = ($category && ((!$widget && $style['cat_border']) || $cb)  ? ' '.$category[0]->slug : ' ');
    
    if ($today > $unixtime && $register['notarchive']) {$register['useform']='';$usereg ='';}
    
    if ($images == 'off') $image='';
    if ($vw) $style['vanillawidget'] = 'checked';
    
    if ($fields) {
        foreach (explode( ',',$event['sort']) as $name) $event['summary'][$name] = '';
        $derek = explode( ',',$fields);
        foreach ($derek as $item) $event['summary']['field'.$item] = 'checked';
    }
    
    if ($display['external_link'] && $link) {
        add_filter( 'post_type_link', 'qem_external_permalink', 10, 2 );
    }
    
    if ($display['external_link_target'] && $link) 
            $target = ' target="_blank" ';
    if ($popup) $popupcontent = get_event_popup($links,$size,$headersize,$settings,$fullevent,$images,$fields,$widget,$cb,$vw,$popup);

    if (($display['show_end_date'] && $enddate) || ($enddate && is_singular ('event')))
        $join = 'checked';
    else
        $join='';
    
    if($size) {
        $width = '-'.$size;
    } else {
        $size = $style['calender_size']; 
        $width = '-'.$style['calender_size'];
    }
	
    $headersize = ($headersize == 'headthree' ? 'h3' : 'h2');
	
    $content = '<div class="qem'.$cat.'">';
    
    if ($display['amalgamated']) {
        $month = date_i18n("M", $unixtime);
        $day = date_i18n("d", $unixtime);
        $year = date_i18n("Y", $unixtime);
        if ($enddate){
            $endmonth = date_i18n("M", $enddate);
            $endday = date_i18n("d", $enddate);
            $endyear = date_i18n("Y", $enddate);
        }
        if ($month == $endmonth && $year == $endyear && $endday) $amalgamated = 'checked';
    }

    if ((!$style['vanilla'] && !$style['vanillawidget']) || (!$style['vanilla'] && $style['vanillawidget'] && !$widget )) {
        $content .= '<div class="qem-icon">'.get_event_calendar_icon($size,'event_date',$join,$vw,$widget);
        if ($join && !$amalgamated && !$vertical) 
            $content .= '</div><div class="qem-icon">';
        if(($display['show_end_date'] || is_singular ('event')) && !$amalgamated) 
            $content .= get_event_calendar_icon($size,'event_end_date','',$vw,$widget);
        $content .= '</div><div class="qem'.$width.'">';
        $clear = '<div style="clear:both"></div></div>';
    }
    
    if ($popup ) $link = '<a onclick=\'pseudo_popup("<div class =\"qemeventpop\">'.$popupcontent.'</div>")\'>';
    else $link =  '<a href="'.get_permalink() . '">';
    
    $content .= '<div class="qemright">';
    if (($image && $display['event_image'] && !is_singular ('event') && !$widget) || ($image && $images)) 
        $content .= $link.'<img class="qem-list-image" src='.$image.'></a><br>';
    if ($image && is_singular ('event') && !$widget) 
        $content .= $link.'<img class="qem-image" src='.$image.'></a><br>';
    if ($image && $display['event_image'] && $widget) 
        $content .= $link.'<img class="qem-list-image" src='.$image.'></a><br>';
        
    if (function_exists('file_get_contents') && (($fullevent && !$image) || $display['map_in_list'] || ($display['map_and_image'] && ($display['map_in_list'] || $fullevent))))
        $content .= get_event_map();
    $content .= '</div>';
    
    if (!is_singular ('event') || $widget)	{
        $content .= '<'.$headersize.'>';
	if ($links == 'checked') {
        if ($popup ) 
            $content .= '<a onclick=\'pseudo_popup("<div class =\"qemeventpop\">'.$popupcontent.'</div>")\'>' . $post->post_title . '</a>';
        else 
            $content .=  '<a href="' . get_permalink() . '"'.$target.'>' . $post->post_title . '</a>';
    }
	else $content .=  $post->post_title;
	$content .= '</'.$headersize.'>';
    }

    if ($style['vanilla'] || ($style['vanillawidget'] && $widget)) {
        $content .= '<h3>'.get_event_calendar_icon($size,'event_date',$join,$vw,$widget);
        if(($display['show_end_date'] || is_singular ('event')) && !$amalgamated) 
            $content .= get_event_calendar_icon($size,'event_end_date','',$vw,$widget);
        $content .= '</h3>';
    }
    
    if ($fullevent) {
        foreach (explode( ',',$event['sort']) as $name)
            if ($event['active_buttons'][$name])
            $content .= qem_build_event($name,$event,$display,$custom,'checked');
        if (is_singular ('event')) $content .= get_the_content();
        if (($register['useform'] && $usereg) || $usereg )
            $content.= qem_loop();
        if (function_exists('qpp_loop') && !$register['paypal'] && (($payment['useqpp'] && !$payment['qppcost']) || ($payment['qppcost'] && $cost) || $usepay)) {
            $atts = array('form'=>$payment['qppform'],'id' => $post->post_title,'amount'=>$cost);
            $check = get_post_meta($post->ID, 'event_counter', true);
            $values = array();
            $num = qem_numberscoming($register);
            if (!$num && $check) {
                $content .= '<h2>' . $register['eventfullmessage'] . '</h2>';
            } else {
                $content .= $num;
                $content.= qpp_loop($atts);
            }
        }
    } else {
        foreach (explode( ',',$event['sort']) as $name)
            if ($event['summary'][$name] == 'checked') 
            $content .= qem_build_event($name,$event,$display,$custom,$settings);
        if ($register['eventlist'] && $usecounter ) {
            $num = qem_numberscoming($register);
            if (!$num) $content .= '<p class="qem_full">' . $register['eventfullmessage'] . '</p>';
            else $content .= $num;
        }
        
    }
    if ($links == 'checked'  && ($fullevent=='popup' || !$fullevent)) {
        if ($popup) 
            $content .= '<p style="cursor:pointer"><a onclick=\'pseudo_popup("<div class =\"qemeventpop\">'.$popupcontent.'</div>")\'>'.$display['read_more'].'</a></p>';
        else 
            $content .= '<p><a href="'.get_permalink().'"'. $target.'>' . $display['read_more'] . '</a></p>';
    }
    if (is_singular ('event') && $display['useics'] && !$widget) $content .= qem_ics();
    if ($display['back_to_list']  && (is_singular ('event') || $fullevent=='popup')) {
        if ($display['back_to_url']) $content .= '<p><a href="'.$display['back_to_url'].'">'.$display['back_to_list_caption'].'</a></p>';
        else  $content .= '<p><a href="javascript:history.go(-1)">'.$display['back_to_list_caption'].'</a></p>';
    }
    $content .= $clear."</div>";
    return $content;
}

function get_event_calendar_icon($width,$dateicon,$join,$vw,$widget) {
    global $post;
    $style = qem_get_stored_style();
    $display = event_get_stored_display();
    $vertical = $display['vertical'];
    $mrcombi = '2' * $style['date_border_width'].'px';
    $mr = '5' + $style['date_border_width'].'px';
    $mb = ($vertical ? ' 8px' : ' 0');
    $bor=$boldon=$italicon=$month=$italicoff=$boldoff=$endname=$bar = $bor = '';
    $tl = '-webkit-border-top-left-radius:0; -moz-border-top-left-radius:0; border-top-left-radius:0;';
    $tr = '-webkit-border-top-right-radius:0; -moz-border-top-right-radius:0; border-top-right-radius:0;';
    $bl = '-webkit-border-bottom-left-radius:0; -moz-border-bottom-left-radius:0; border-bottom-left-radius:0';
    $br = '-webkit-border-bottom-right-radius:0; -moz-border-bottom-right-radius:0; border-bottom-right-radius:0';
    if ($dateicon == 'event_date' && !$display['combined'] && !$vertical) $mb = ' '.$mr;
    if ($dateicon == 'event_end_date' && $display['combined'] && !$vertical) {
        $bar = $bor = '';
        $bar='style="border-left-width:1px;'.$tl.$bl.'"';
    }
    if ($style['date_bold']) {$boldon = '<b>'; $boldoff = '</b>';}
    if ($style['date_italic']) {$italicon = '<em>'; $italicoff = '</em>';}
    if ($vw) $style['vanillawidget'] = 'checked';
    $unixtime = get_post_meta($post->ID, $dateicon, true);
    $endtime = get_post_meta($post->ID, 'event_end_date', true);

    if ($unixtime){
        $month = date_i18n("M", $unixtime);
        $dayname = date_i18n("D", $unixtime);
        $day = date_i18n("d", $unixtime);
        $year = date_i18n("Y", $unixtime);
        if ($endtime && $display['amalgamated']){
            $endmonth = date_i18n("M", $endtime);
            $endday = date_i18n("d", $endtime);
            $endyear = date_i18n("Y", $endtime);
            if ($month == $endmonth && $year == $endyear && $endday && $dateicon != 'event_end_date') {
                if ($style['use_dayname']) $endname = date_i18n("D", $endtime).' ';
                $day = $day.' - '.$endname.$endday;
                $amalgum = 'on';
            }
        }
        if ($dateicon == 'event_date' && $display['combined'] && $join && !$amalgum) {
            $bar = $bor = '';
            $bar = 'style="border-right:none;'.$tr.$br.'"';
            $mr=' 0';
        }
        if ($style['iconorder'] == 'month') {
            $top = $month;
            $middle = $day;
            $bottom = $year;
        } elseif ($style['iconorder'] == 'year') {
            $top = $year;
            $middle = $day;
            $bottom = $month;
        } elseif ($style['iconorder'] == 'dm') {
            $top = $day;
            $middle = $month;
        } elseif ($style['iconorder'] == 'md') {
            $top = $month;
            $middle = $day;
        } else {
            $top = $day;
            $middle = $month;
            $bottom = $year;
        }

        if ($style['vanilla'] || ($style['vanillawidget'] && $widget)) {
            if ($dateicon == 'event_end_date') $sep = '&nbsp; - &nbsp;';
            $content = $sep;
            if ($style['use_dayname']) $content .= $dayname.'&nbsp;';
            $content .= $top.'&nbsp;'.$middle.'&nbsp;'.$bottom;
        } else {
            $content = '<div class="qem-calendar-' . $width . '" style="margin:0 '.$mr.$mb.' 0;"><span class="day" '.$bar.'>';
            if ($style['use_dayname']) {
                $content .= $dayname;
                $content .= ($style['use_dayname_inline'] ? ' ' : '<br>');
            }
            $content .= $top.'</span><span class="nonday" '.$bar.'><span class="month">'.$boldon.$italicon.$middle.$italicoff.$boldoff.'</span>'.$bottom.'</span></div>';
        }
        return $content;
    }
}

function qem_build_event ($name,$event,$display,$custom,$settings) {
    $style = $output = $caption = $target = '';
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
            $output .= '<p itemprop="description" ' . $style . '>' . $caption . $custom['event_desc'][0] . '</p>';
        break;
        case 'field2':
        if (!empty($custom['event_start'][0] )) {
            $output .= '<p ' . $style . '>' . $event['start_label'] . ' ' . $custom['event_start'][0];
            if (!empty($custom['event_finish'][0])) 
                $output .= ' '.$event['finish_label'].' '. $custom['event_finish'][0];
            if ($display['usetimezone'] && $custom['event_timezone'][0]) 
                $output .= ' '.$display['timezonebefore'].' '.$custom['event_timezone'][0].' '.$display['timezoneafter'];
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
        if (!empty($event['url_label'])) 
            $caption = $event['url_label'].' ';
        if ($event['target_link']) 
            $target = 'target="_blank"';
        if (!preg_match("~^(?:f|ht)tps?://~i", $custom['event_link'][0])) 
            $url = 'http://' . $custom['event_link'][0]; 
        else  
            $url = $custom['event_link'][0];
        if (empty($custom['event_anchor'][0])) 
            $custom['event_anchor'][0] = $custom['event_link'][0];
        if (!empty ( $custom['event_link'][0] )) 
            $output .= '<p ' . $style . '>' . $caption .  '<a itemprop="url" ' . $style .' '.$target.' href="' . $url . '">' . $custom['event_anchor'][0]  . '</a></p>';
        break;
        case 'field6':
        if (!empty($event['cost_label'])) 
            $caption = $event['cost_label'].' ';
        if (!empty ( $custom['event_cost'][0] )) 
            $output .= '<p itemprop="price" ' . $style . '>' . $caption . $custom['event_cost'][0]  . '</p>';
        break;
        case 'field7':
        if (!empty($event['organiser_label'])) 
            $caption = $event['organiser_label'].' ';
        if (!empty ( $custom['event_organiser'][0] )) {
            $output .= '<p' . $style . '>' . $caption . $custom['event_organiser'][0];
if (!empty($custom['event_telephone'][0]) && $settings['show_telephone']) 
            $output .= ' / ' . $custom['event_telephone'][0];
            $output .= '</p>';
        }
        break;
    }
    return $output;
}	

function get_event_content($content) {
    global $post;
    if (is_singular ('event') ) 
        $content = qem_event_construct ('off','','','checked','fullevent','','','','','','');	
    return $content;
}

function get_event_map() {
    global $post;
    $event = event_get_stored_options();
    $display = event_get_stored_display();
    $mapurl = '';
    if ($display['map_target']) $target = ' target="_blank" ';
    $custom = get_post_custom();
    if (($event['show_map'] == 'checked') && (!empty($custom['event_address'][0]))) {
        $map = str_replace(' ' ,'+',$custom['event_address'][0]);
        $mapurl .= '<div class="qemmap"><a href="http://maps.google.com/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q=' . $map . '&amp;t=m" '.$target.'><img src="http://maps.googleapis.com/maps/api/staticmap?center=' . $map . '&size=' . $display['map_width'] . 'x' . $display['map_height'] . '&markers=color:blue%7C'.$map.'&sensor=true" /></a></div>';
    }
    return $mapurl;
}

class qem_widget extends WP_Widget {
    function qem_widget() {
        $widget_ops = array(
            'classname' => 'qem_widget',
            'description' => ''.__('Add an event list to your sidebar', 'quick-event-manager').'');
        $this->WP_Widget('qem_widget', 'Quick Event List', $widget_ops);
    }	
    function form($instance) {
        $instance = wp_parse_args( (array) $instance, array(
            'posts' => '3',
            'size' =>'small',
            'headersize' => 'headtwo',
            'settings' => '',
            'links' => 'checked',
            'listlink'=>'',
            'listlinkanchor'=>'See full event list',
            'listlinkurl'=>'',
            'vanillawidget'=>'',
            'usecategory' =>'checked',
            'categorykeyabove' =>'checked',
            'categorykeybelow' =>'checked'
        ));
        $posts = $instance['posts'];
        $size = $instance['size'];
        $$size = 'checked';
        $headersize = $instance['headersize'];
        $$headersize = 'checked';
        $settings = $instance['settings'];
        $vanillawidget = $instance['vanillawidget'];
        $links = $instance['links'];
        $listlink = $instance['listlink'];
        $listlinkanchor = $instance['listlinkanchor'];
        $listlinkurl = $instance['listlinkurl'];
        $usecategory = $instance['usecategory'];
        $categorykeyabove = $instance['categorykeyabove'];
        $categorykeybelow = $instance['categorykeybelow'];
        if ( isset( $instance[ 'title' ] ) ) {$title = $instance[ 'title' ];}
        else {$title = __( 'Event List', 'text_domain' );}
        ?>
        <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
        <p><label for="<?php echo $this->get_field_id('posts'); ?>"><?php _e('Number of posts to display: ', 'quick-event-manager'); ?><input style="width:3em" id="<?php echo $this->get_field_id('posts'); ?>" name="<?php echo $this->get_field_name('posts'); ?>" type="text" value="<?php echo attribute_escape($posts); ?>" /></label></p>
        <h3>Calender Icon</h3>
        <p><input type="checkbox" id="<?php echo $this->get_field_name('vanillawidget'); ?>" name="<?php echo $this->get_field_name('vanillawidget'); ?>" value="checked" <?php echo $vanillawidget; ?>> Strip styling from date icon.</p>
        <p><input type="radio" id="<?php echo $this->get_field_name('size'); ?>" name="<?php echo $this->get_field_name('size'); ?>" value="small" <?php echo $small; ?>> Small<br>
        <input type="radio" id="<?php echo $this->get_field_name('size'); ?>" name="<?php echo $this->get_field_name('size'); ?>" value="medium" <?php echo $medium; ?>> Medium<br>
        <input type="radio" id="<?php echo $this->get_field_name('size'); ?>" name="<?php echo $this->get_field_name('size'); ?>" value="large" <?php echo $large; ?>> Large</p>
        <h3>Event Title</h3>
        <p><input type="radio" id="<?php echo $this->get_field_name('headersize'); ?>" name="<?php echo $this->get_field_name('headersize'); ?>" value="headtwo" <?php echo $headtwo; ?>> H2 <input type="radio" id="<?php echo $this->get_field_name('headersize'); ?>" name="<?php echo $this->get_field_name('headersize'); ?>" value="headthree" <?php echo $headthree; ?>> H3</p>
        <h3>Styling</h3>
        <p><input type="checkbox" id="<?php echo $this->get_field_name('settings'); ?>" name="<?php echo $this->get_field_name('settings'); ?>" value="checked" <?php echo $settings; ?>> Use plugin styles (<a href="options-general.php?page=quick-event-manager/settings.php&tab=settings">View styles</a>)</p>
        <h3>Categories</h3>
        <p><select id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>" class="widefat" style="width:100%;">
        <option value="0">All Categories</option>
        <?php foreach(get_terms('category','parent=0&hide_empty=0') as $term) { ?>
        <option <?php selected( $instance['category'], $term->term_id ); ?> value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
        <?php } ?>      
        </select></p>
        <p><input type="checkbox" id="<?php echo $this->get_field_name('usecategory'); ?>" name="<?php echo $this->get_field_name('usecategory'); ?>" value="checked" <?php echo $usecategory; ?>> Show category colours</p>
        <p><input type="checkbox" id="<?php echo $this->get_field_name('categorykeyabove'); ?>" name="<?php echo $this->get_field_name('categorykeyabove'); ?>" value="checked" <?php echo $categorykeyabove; ?>> Show category key above list</p>
        <p><input type="checkbox" id="<?php echo $this->get_field_name('categorykeybelow'); ?>" name="<?php echo $this->get_field_name('categorykeybelow'); ?>" value="checked" <?php echo $categorykeybelow; ?>> Show category key below list</p>
        <h3>Links</h3>
        <p><input type="checkbox" id="<?php echo $this->get_field_name('links'); ?>" name="<?php echo $this->get_field_name('links'); ?>" value="checked" <?php echo $links; ?>> Show links to events</p>
        <p><input type="checkbox" id="<?php echo $this->get_field_name('listlink'); ?>" name="<?php echo $this->get_field_name('listlink'); ?>" value="checked" <?php echo $listlink; ?>> Link to Event List</p>
        <p><label for="<?php echo $this->get_field_id( 'listlinkanchor' ); ?>"><?php _e( 'Anchor text:' ); ?></label>
        <input class="widefat" type="text" id="<?php echo $this->get_field_name('listlinkanchor'); ?>" name="<?php echo $this->get_field_name('listlinkanchor'); ?>" value="<?php echo attribute_escape($listlinkanchor); ?>" ></p>
        <p><label for="<?php echo $this->get_field_id( 'listlinkurl' ); ?>"><?php _e( 'URL of list page:' ); ?></label>
        <input class="widefat" type="text" id="<?php echo $this->get_field_name('listlinkurl'); ?>" name="<?php echo $this->get_field_name('listlinkurl'); ?>" value="<?php echo attribute_escape($listlinkurl); ?>" ></p>
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
        $instance['listlink'] = $new_instance['listlink'];
        $instance['listlinkanchor'] = $new_instance['listlinkanchor'];
        $instance['listlinkurl'] = $new_instance['listlinkurl'];
        $instance['vanillawidget'] = $new_instance['vanillawidget'];
        $instance['category'] = $new_instance['category'];
        $instance['usecategory'] = $new_instance['usecategory'];
        $instance['categorykeyabove'] = $new_instance['categorykeyabove'];
        $instance['categorykeybelow'] = $new_instance['categorykeybelow'];
        return $instance;
    }
    function widget($args, $instance) {
        extract($args, EXTR_SKIP);
        $title = apply_filters( 'widget_title', $instance['title'] );
        echo $args['before_widget'];
        if ( ! empty( $title ) ) echo $args['before_title'] . $title . $args['after_title'];
        echo qem_event_shortcode($instance,'widget');
        echo $args['after_widget'];
    }
}

class qem_calendar_widget extends WP_Widget {
    function qem_calendar_widget() {
        $widget_ops = array(
            'classname' => 'qem_calendar_widget',
            'description' => ''.__('Add an event calendar to your sidebar', 'quick-event-manager').'');
        $this->WP_Widget('qem_calendar_widget', 'Quick Event Calendar', $widget_ops);
    }	
	
    function form($instance) {
        $instance = wp_parse_args( (array) $instance, array(
            'eventlength' => '12',
            'smallicon' => 'trim','unicode' =>'\263A') );
        $eventlength = $instance['eventlength'];
        $smallicon = $instance['smallicon'];
        $$smallicon = 'checked';
        $unicode = $instance['unicode'];
        if ( isset( $instance[ 'title' ] ) ) {$title = $instance[ 'title' ];}
        else {$title = __( 'Event Calendar', 'text_domain' );}
        ?>
        <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
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
    $arr =array(
        'arrow' => '\25B6',
        'square' => '\25A0',
        'box'=>'\20DE',
        'asterix'=>'\2605',
        'blank'=>' '
    );
    foreach ($arr as $item => $key)
$smallicon = '';
        if($item == $atts['smallicon'])
            $smallicon = '#qem-calendar-widget .qemtrim span {display:none;}#qem-calendar-widget .qemtrim:after{content:"'.$key.'";font-size:150%;text-align:center}';
    return '<div id="qem-calendar-widget"><style>'.$smallicon.'</style>'.qem_show_calendar($atts).'</div>';
}

function qem_show_calendar($atts) {
    $cal = qem_get_stored_calendar();
    $style = qem_get_stored_style();
    extract(shortcode_atts(array('category'=>'','month'=>'','year'=>''),$atts));
    global $post;
    global $_GET;
    if (isset($_GET['category'])) $category = $_GET['category']; 

    $args = array(
        'post_type' => 'event',
        'orderby'=> 'meta_value_num',
        'meta_key' => 'event_date',
        'order' => 'asc',
        'posts_per_page' => -1,
        'category' => ''
    );
    $catarry = explode(",",$category);
    if ($cal['navicon'] == 'arrows') {
        $leftnavicon = '&#9668; ';
        $rightnavicon = ' &#9658;';
    }
    if ($cal['navicon'] == 'unicodes') {
        $leftnavicon = $cal['leftunicode'].' ';
        $rightnavicon = ' '.$cal['rightunicode'];
    }
    $monthnames = array();
    $monthstamp = 0;
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
                $startdate = strtotime(date("d M Y", $startdate));
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
                    } while ($startdate <= $enddate);
                } else {
                    array_push($eventdate, $startdate);
                    array_push($eventtitle,$title);
                    array_push($eventslug,$slug);
                    array_push($eventsummary, $eventx);
                    array_push($eventlinks,$link);
                }
            }
        }
    }
    wp_reset_postdata();
    if (!isset($_GET["qemmonth"])) {
        if ($month) $_GET["qemmonth"] = $month;
        else $_GET["qemmonth"] = date_i18n("n");}
    if (!isset($_GET["qemyear"])) {
        if ($year) $_GET["qemyear"] = $year;
        else $_GET["qemyear"] = date_i18n("Y");}
    $currentmonth = $_GET["qemmonth"];
    $currentyear = $_GET["qemyear"];
    $calendar = '';
    $p_year = $currentyear;
    $n_year = $currentyear;
    $p_month = $currentmonth-1;
    $n_month = $currentmonth+1;
    if ($p_month == 0 ) {$p_month = 12;$p_year = $currentyear - 1;}
    if ($n_month == 13 ) {$n_month = 1;$n_year = $currentyear + 1;};
    if ($cal['connect']) 
        $calendar .='<p><a href="'.$cal['eventlist_url'].'">'.$cal['eventlist_text'].'</a></p>';
    $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $parts = explode("&",$actual_link);
    $actual_link = $parts['0']; 
    $link = (strpos($actual_link,'?') ? '&' : '?');
    $catkey = qem_category_key($cal,$style,'calendar');
    if ($cal['showkeyabove']) $calendar .= $catkey;
    $calendar .='<div id="qem-calendar">
    <table cellspacing="3" cellpadding="0">
    <tr class="top">
    <td colspan="1" ><a class="calnav" href="'.$actual_link.$link.'qemmonth='. $p_month . '&amp;qemyear=' . $p_year . '">' .$leftnavicon.$cal['prevmonth'].'</a></td>
    <td colspan="5" class="calmonth"><h2>'. $monthnames[$currentmonth-1].' '.$currentyear .'</h2></td>
    <td colspan="1"><a class="calnav" href="'.$actual_link.$link.'qemmonth='. $n_month . '&amp;qemyear=' . $n_year . '">'.$cal['nextmonth'].
        $rightnavicon.'</a></td>
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
        if ($currentmonth < date_i18n("n") && $currentyear == date_i18n("Y"))
            $oldday = 'oldday';
        if ($currentyear < date_i18n("Y")) 
            $oldday = 'oldday';
        if (($cal['archive'] && $oldday) || !$oldday) 
            $show = 'checked';
        else $show ='';
        $tdstart = '<td class="day '.$oldday.'"><h2>'.($i - $startday+1).'</h2><br>';
        $tdcontent = '';
        foreach ($eventdate as $key => $day) {
            $m=date('m', $day);$d=date('d', $day);$y=date('Y', $day);
            $zzz = mktime(0,0,0,$m,$d,$y);
            if($xxx==$zzz && $show) {
                $tdstart = '<td class="eventday '.$oldday.'"><h2>'.($i - $startday+1).'</h2>';
                $length = $cal['eventlength'];
                if(strlen($eventtitle[$key]) > $length) 
                    $trim = preg_replace("/^(.{1,$length})(\s.*|$)/s", '\\1...', $eventtitle[$key]);
                else 
                    $trim = $eventtitle[$key];
                if ($cal['eventlink'] == 'linkpopup' ) 
                    $tdcontent .= '<a class="event ' . $eventslug[$key] . '" onclick=\'pseudo_popup("<div class =\"qempop\">'.$eventsummary[$key].'</div>")\'><div class="qemtrim"><span>'.$trim.'</span></div></a>';
                else 
                    $tdcontent .= '<a class="' . $eventslug[$key] . '" href="' . $eventlinks[$key] . '"><div class="qemtrim"><span>' . $trim . '</span></div></a>';
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
    $category = get_the_category();
    $cat = ($style['cat_border'] && $category ? $category[0]->slug : ' ');
    $output = '<div style="float:left" class="'.$cat.'">' . get_event_calendar_icon($width,'event_date','','','').'</div><div class="'.$cat.'"><div class="qem-'.$width.'"><h2 style="display:inline"><a href="' . get_permalink() . '">' . $post->post_title . '</a></h2>';
    foreach (explode( ',',$event['sort']) as $name)
        if ($event['summary'][$name] == 'checked') $output .= qem_build_event($name,$event,$display,$custom,'checked');
    $output .='<p><a href="' . get_permalink() . '">' . $display['read_more'] . '</a></p></div></div>';
    $output = str_replace('"','\"',$output);
    $output = str_replace("'","&#8217;",$output);
    return $output;
}

function get_event_popup($links,$size,$headersize,$settings,$fullevent,$images,$fields,$widget,$cb,$vanillawidget,$popup) {
    $output .= qem_event_construct ('checked',$size,$headersize,$settings,'popup',$images,$fields,$widget,$cb,$vanillawidget,'');
    $output = str_replace('"','\"',$output);
    $output = str_replace("'","&#8217;",$output);
    return $output;
}

function qem_generate_css() {
    $style = qem_get_stored_style();
    $cal = qem_get_stored_calendar();
    $display = event_get_stored_display();
    $register = qem_get_stored_register();
    $showeventborder=$formborder=$daycolor=$eventbold=$colour=$eventitalic='';
    if ($style['calender_size'] == 'small') $radius = 7;
    if ($style['calender_size'] == 'medium') $radius = 10;
    if ($style['calender_size'] == 'large') $radius = 15;
    $ssize=50 + (2*$style['date_border_width']).'px';
    $srm = $ssize+5+($style['date_border_width']).'px';
    $msize=70 + (2*$style['date_border_width']).'px';
    $mrm = $msize+5+($style['date_border_width']).'px';
    $lsize=90 + (2*$style['date_border_width']).'px';
    $lrm = $lsize+5+($style['date_border_width']).'px';
    if ($style['date_background'] == 'color') $color = $style['date_backgroundhex'];
	if ($style['date_background'] == 'grey') $color = '#343838';
	if ($style['date_background'] == 'red') $color = 'red';
    if ($style['month_background'] == 'colour') $colour = $style['month_backgroundhex'];
	else $colour = '#FFF';
	if ($style['event_background'] == 'bgwhite') $eventbackground = 'background:white;';
	if ($style['event_background'] == 'bgcolor') $eventbackground = 'background:'.$style['event_backgroundhex'].';';
	
    $dayborder = 'color:' . $style['date_colour'].';background:'.$color.'; border: '. $style['date_border_width']. 'px solid ' .$style['date_border_colour'].';border-bottom:none;';
	$nondayborder = 'border: '. $style['date_border_width']. 'px solid ' .$style['date_border_colour'].';border-top:none;background:'.$colour.';';
    
    $monthcolor = 'span.month {color:'.$style['month_color'].';}';
	$eventborder = 'border: '. $style['date_border_width']. 'px solid ' .$style['date_border_colour'].';';
	if ($style['icon_corners'] == 'rounded') {
        $dayborder = $dayborder.'-webkit-border-top-left-radius:'.$radius.'px; -moz-border-top-left-radius:'.$radius.'px; border-top-left-radius:'.$radius.'px; -webkit-border-top-right-radius:'.$radius.'px; -moz-border-top-right-radius:'.$radius.'px; border-top-right-radius:'.$radius.'px;';
        $nondayborder = $nondayborder.'-webkit-border-bottom-left-radius:'.$radius.'px; -moz-border-bottom-left-radius:'.$radius.'px; border-bottom-left-radius:'.$radius.'px; -webkit-border-bottom-right-radius:'.$radius.'px; -moz-border-bottom-right-radius:'.$radius.'px; border-bottom-right-radius:'.$radius.'px;';
        $eventborder = $eventborder.'-webkit-border-radius:'.$radius.'px; -moz-border-radius:'.$radius.'px; border-radius:'.$radius.'px;';
    }
    if ($style['event_border']) $showeventborder = 'padding:'.$radius.'px;'.$eventborder;
    if ($register['formborder']) $formborder = ".qem-register {".$eventborder."padding:".$radius."px;}";
    if ($style['widthtype'] == 'pixel') $eventwidth = preg_replace("/[^0-9]/", "", $style['width']) . 'px;';
    else $eventwidth = '100%';
    $i = '300';
    if ($display['event_image_width']) $i = preg_replace ( '/[^.,0-9]/', '', $display['event_image_width']);
    if ($display['map_and_image_size']) $i = preg_replace ( '/[^.,0-9]/', '', $display['map_width']);
    if ($display['image_width']) $j = preg_replace ( '/[^.,0-9]/', '', $display['image_width']);
    elseif ($display['map_and_image_size']) $j = preg_replace ( '/[^.,0-9]/', '', $display['map_width']);
    else $j = '300';
    $arr =array('arrow' => '\25B6','square' => '\25A0','box'=>'\20DE','asterix'=>'\2605','blank'=>' ');    
    foreach ($arr as $item => $key)
        if($item == $cal['smallicon'])
            $script .= '@media only screen and (max-width: 480px) {
            .qemtrim span {display:none;}.qemtrim:after{content:"'.$key.'";font-size:150%;}}
            #qem-calendar-widget h2 {font-size: 1em;}
            #qem-calendar-widget .qemtrim span {display:none;}
            #qem-calendar-widget .qemtrim:after{content:"'.$key.'";font-size:150%;}';
    $script .= ".qem {width:".$eventwidth.";".$style['event_margin'].";}
    .qem p {".$style['line_margin'].";}
    .qem p, .qem h2 {margin: 0 0 8px 0;padding:0;}
    .qem-small, .qem-medium, .qem-large {".$showeventborder.$eventbackground."}".
	$formborder."img.qem-image {max-width:".$i."px;height:auto;overflow:hidden;}
    img.qem-list-image {width:100%;max-width:".$j."px;height:auto;overflow:hidden;}
    .qem-category {".$eventborder."}
    .qem-icon .qem-calendar-small {width:".$ssize.";}
    .qem-small {margin-left:".$srm.";}
    .qem-icon .qem-calendar-medium {width:".$msize.";}
    .qem-medium {margin-left:".$mrm.";}
    .qem-icon .qem-calendar-large {width:".$lsize.";}
    .qem-large {margin-left:".$lrm.";}
    .qem-calendar-small .nonday, .qem-calendar-medium .nonday, .qem-calendar-large .nonday {display:block;".$nondayborder."}
    .qem-calendar-small .day, .qem-calendar-medium .day, .qem-calendar-large .day {display:block;".$daycolor.$dayborder."}
    .qem-calendar-small .month, .qem-calendar-medium .month, .qem-calendar-large .month {color:".$style['month_colour']."}
    #qem-calendar .calday {background:".$cal['calday']."; color:".$cal['caldaytext']."}
    #qem-calendar .day {background:".$cal['day'].";}
    #qem-calendar .eventday {background:".$cal['eventday'].";}
    #qem-calendar .eventday a {color:".$cal['eventdaytext'].";border:1px solid ".$cal['eventdaytext'].";}
    #qem-calendar .oldday {background:".$cal['oldday'].";}
    #qem-calendar td a:hover {background:".$cal['eventhover']." !important;}
    .qemtrim span {".$eventbold.$eventitalic."}
    #qem-calendar .eventday a {color:".$cal['eventtext']." !important;background:".$cal['eventbackground']." !important;border:".$cal['eventborder']." !important;}
    @media only screen and (max-width: 480px) {img.qem-image, img.qem-list-image, .qemmap {max-width:100px;}}";
    if ($style['font'] == 'plugin') 
        $script .= ".qem p {font-family: ".$style['font-family']."; font-size: ".$style['font-size'].";}
        .qem h2, .qem h2 a {font-size: ".$style['header-size']." !important;color:".$style['header-colour']." !important}";
    if ($style['use_custom'] == 'checked') $script .= $style['custom'];
    if ($cal['eventbold']) $eventbold = 'font-weight:bold;';
    if ($cal['eventitalic']) $eventitalic = 'font-style:italic;';
    $cat = array('a','b','c','d','e','f');
    foreach ($cat as $i) {
        if ($cal['cat'.$i]) {
            $script .="#qem-calendar a.".$cal['cat'.$i]." {background:".$cal['cat'.$i.'back']." !important;color:".$cal['cat'.$i.'text']." !important;border:1px solid ".$cal['cat'.$i.'text']." !important;}";
            $script .= '.'.$cal['cat'.$i].' .qem-small, .'.$cal['cat'.$i].' .qem-medium, .'.$cal['cat'.$i].' .qem-large {border-color:'.$cal['cat'.$i.'back'].';}';
            $script .= '.'.$cal['cat'.$i].' .qem-calendar-small .day, .'.$cal['cat'.$i].' .qem-calendar-medium .day, .'.$cal['cat'.$i].' .qem-calendar-large .day, .'.$cal['cat'.$i].' .qem-calendar-small .nonday, .'.$cal['cat'.$i].' .qem-calendar-medium .nonday, .'.$cal['cat'.$i].' .qem-calendar-large .nonday {border-color:'.$cal['cat'.$i.'back'].';}';
        }
    }
    return $script;
}

function qem_head_css () {
    $data = '<style type="text/css" media="screen">'.qem_generate_css().'</style>';
    echo $data;
}


function dateToCal($timestamp) {
    if ($timestamp) return date('Ymd\THis', $timestamp);
}

function escapeString($string) {
    return preg_replace('/([\,;])/','\\\$1', $string);
}

function qem_time ($starttime) {
    $starttime = str_replace('AM','',strtoupper($starttime));
	if (strpos($starttime,':')) $needle = ':';
	if (strpos($starttime,'.')) $needle = '.';
	if (strpos($starttime,' ')) $needle = ' ';
    if (strpos(strtoupper($starttime),'PM') && substr($starttime, 0, 2) != '12') $afternoon = 43200;
	if ($needle) list($hours, $minutes) = explode($needle, $starttime);
    else $hours = $starttime;
    if (strlen($starttime) == 4 && is_numeric($starttime)) {$hours = substr($starttime, 0, 2);$minutes = substr($starttime, 3);}
    $seconds=$hours*3600+$minutes*60+$afternoon;
	return $seconds;
	}

function qem_ics() {
    global $post;
    $display = event_get_stored_display();
    $summary = $post->post_title;
    $datestart = get_post_meta($post->ID, 'event_date', true);
    $dateend = get_post_meta($post->ID, 'event_end_date', true);
    $address = get_post_meta($post->ID, 'event_address', true);
    $url = get_permalink();
    $description = get_post_meta($post->ID, 'event_desc', true);
    $filename = $post->post_title.'.ics';
    if (!$dateend) {
        $finish = get_post_meta($post->ID, 'event_finish', true);
        $date = date('Ymd\T', $datestart);
        $time = qem_time($finish);
        $time = date('His',$time);
        $dateend = $date.$time;
    }
    else $datend = dateToCal($dateend);
$ics = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN
CALSCALE:GREGORIAN
BEGIN:VEVENT
DTEND:'.$dateend.'
UID:'.uniqid().'
DTSTAMP:'.dateToCal(time()).'
LOCATION:'.$address.'
DESCRIPTION:'.$description.'
URL;VALUE=URI:'.$url.'
SUMMARY:'.$summary.'
DTSTART:'.dateToCal($datestart).'
END:VEVENT
END:VCALENDAR';
    qem_generate_csv();
    $content = '<form method="post" action="">
    <input type="hidden" name="qem_ics" value="'.$ics.'">
    <input type="hidden" name="qem_filename" value="'.$filename.'">
    <input type="submit" name="qem_create_ics" class="qem-register" id="submit" value="'.$display['useicsbutton'].'" /></form>';
    return $content;
}

function qem_generate_csv() {
    if (isset($_POST['qem_create_ics'])) {
        $ics = $_POST['qem_ics'];
        $filename = $_POST['qem_filename'];
        header( 'Content-Description: File Transfer' );
        header( 'Content-Disposition: attachment; filename="'.$filename.'"');
        header( 'Content-Type: text/csv');$fh = fopen("php://output",'w');
        fwrite($fh, $ics);
        fclose($fh);
        exit;
    }
    if(isset($_POST['qem_download_csv'])) {
        $event = $_POST['qem_download_form'];
        $title = $_POST['qem_download_title'];
        $register = qem_get_stored_register();
        $filename = urlencode($title.'.csv');
        if (!$title) $filename = urlencode('default.csv');
        header( 'Content-Description: File Transfer' );
        header( 'Content-Disposition: attachment; filename="'.$filename.'"');
        header( 'Content-Type: text/csv');$outstream = fopen("php://output",'w');
        $message = get_option( 'qem_messages_'.$event );
        if(!is_array($message))$message = array();
        $headerrow = array();
        if ($register['usename']) array_push($headerrow, $register['yourname']);
        if ($register['usemail']) array_push($headerrow, $register['youremail']);
        if ($register['usetelephone']) array_push($headerrow, $register['yourtelephone']);
        if ($register['useplaces']) array_push($headerrow, $register['yourplaces']);
        if ($register['usemessage']) array_push($headerrow, $register['yourmessage']);
        if ($register['useblank1']) array_push($headerrow, $register['yourblank1']);
        if ($register['useblank2']) array_push($headerrow, $register['yourblank2']);
        if ($register['usedropdown']) array_push($headerrow, $register['yourdropdown']);
        array_push($headerrow,'Date Sent');
        fputcsv($outstream,$headerrow, ',', '"');
        foreach($message as $value) {
            $cells = array();
            if ($register['usename']) array_push($cells,$value['yourname']);
            if ($register['usemail']) array_push($cells, $value['youremail']);
            if ($register['usetelephone']) array_push($cells, $value['yourtelephone']);
            if ($register['useplaces']) array_push($cells, $value['yourplaces']);
            if ($register['usemessage']) array_push($cells, $value['yourmessage']);
            if ($register['useblank1']) array_push($cells, $value['yourblank1']);
        if ($register['useblank2']) array_push($cells, $value['yourblank2']);
        if ($register['usedropdown']) array_push($cells, $value['yourdropdown']);
            array_push($cells,$value['sentdate']);
            fputcsv($outstream,$cells, ',', '"');
        }
        fclose($outstream); 
        exit;
    }
}

add_action('admin_menu', 'event_page_init');
add_filter('the_content', 'get_event_content');

function qem_add_role_caps() {
    qem_add_role();
    $roles = array('administrator','editor','event-manager');
    foreach ($roles as $item) {
    $role = get_role($item);
    $role->add_cap( 'read' );
    $role->add_cap( 'read_event');
    $role->add_cap( 'read_private_event' );
    $role->add_cap( 'edit_event' );
    $role->add_cap( 'edit_events' );
    $role->add_cap( 'edit_others_events' );
    $role->add_cap( 'edit_published_events' );
    $role->add_cap( 'publish_events' );
    $role->add_cap( 'delete_events' );
    $role->add_cap( 'delete_others_events');
    $role->add_cap( 'delete_private_events');
    $role->add_cap( 'delete_published_events');
    $role->add_cap( 'manage_categories');
    $role->add_cap( 'upload_files');
    }
}

function qem_users($output) {
    $users = get_users('role=event-manager');
    $output = "<select id=\"post_author_override\" name=\"post_author_override\" class=\"\">";
    $output .= "<option value=\"1\">Admin</option>";
    foreach($users as $user) {
        $sel = ($post->post_author == $user->ID)?"selected='selected'":'';
        $output .= '<option value="'.$user->ID.'"'.$sel.'>'.$user->user_login.'</option>';
    }
    $output .= "</select>";
    return $output;
}

function qem_add_role() {
    remove_role( 'event-manager' );
       add_role( 'event-manager', 'Event Manager', array( 'read' => true,'edit_posts' => false,'edit_event' => true, 'edit_events' => true,'publish_events' => true,'delete_events' => true ) );}

register_activation_hook( __FILE__, 'qem_add_role' );