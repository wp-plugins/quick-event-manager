<?php

class qem_widget extends WP_Widget {
    
    function __construct() {
		parent::__construct(
			'qem_widget', // Base ID
			__( 'Quick Event List', 'quick-event-manager' ), // Name
			array( 'description' => __( 'Add an event list to your sidebar', 'quick-event-manager' ), ) // Args
		);
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
            'categorykeybelow' =>'checked',
            'fields' => ''
        ));
        $posts = $instance['posts'];
        $size = $instance['size'];
        $$size = 'checked';
        $fields = $instance['fields'];
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
        <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title' ); ?>:</label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
        <p><label for="<?php echo $this->get_field_id('posts'); ?>"><?php _e('Number of posts to display ', 'quick-event-manager'); ?>:<input style="width:3em" id="<?php echo $this->get_field_id('posts'); ?>" name="<?php echo $this->get_field_name('posts'); ?>" type="text" value="<?php echo attribute_escape($posts); ?>" /></label></p>
        <h3>Calender Icon</h3>
        <p><input type="checkbox" id="<?php echo $this->get_field_name('vanillawidget'); ?>" name="<?php echo $this->get_field_name('vanillawidget'); ?>" value="checked" <?php echo $vanillawidget; ?>> Strip styling from date icon.</p>
        <p><input type="radio" id="<?php echo $this->get_field_name('size'); ?>" name="<?php echo $this->get_field_name('size'); ?>" value="small" <?php echo $small; ?>> Small<br>
        <input type="radio" id="<?php echo $this->get_field_name('size'); ?>" name="<?php echo $this->get_field_name('size'); ?>" value="medium" <?php echo $medium; ?>> Medium<br>
        <input type="radio" id="<?php echo $this->get_field_name('size'); ?>" name="<?php echo $this->get_field_name('size'); ?>" value="large" <?php echo $large; ?>> Large</p>
        <h3>Event Title</h3>
        <p><input type="radio" id="<?php echo $this->get_field_name('headersize'); ?>" name="<?php echo $this->get_field_name('headersize'); ?>" value="headtwo" <?php echo $headtwo; ?>> H2 <input type="radio" id="<?php echo $this->get_field_name('headersize'); ?>" name="<?php echo $this->get_field_name('headersize'); ?>" value="headthree" <?php echo $headthree; ?>> H3</p>
        <h3>Fields</h3>
        <p>Enter the <a href="options-general.php?page=quick-event-manager/settings.php&tab=settings">field numbers</a> you want to display. Enter <em>none</em> to hide all fields.
        <input class="widefat" type="text" id="<?php echo $this->get_field_name('fields'); ?>" name="<?php echo $this->get_field_name('fields'); ?>" value="<?php echo attribute_escape($fields); ?>" ></p>        <h3>Styling</h3>
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
        <p><label for="<?php echo $this->get_field_id( 'listlinkurl' ); ?>"><?php _e( 'URL of list page' ); ?>:</label>
        <input class="widefat" type="text" id="<?php echo $this->get_field_name('listlinkurl'); ?>" name="<?php echo $this->get_field_name('listlinkurl'); ?>" value="<?php echo attribute_escape($listlinkurl); ?>" ></p>
        <p><?php _e('All other options are changed on the ', 'quick-event-manager'); ?> <a href="options-general.php?page=quick-event-manager/settings.php"><?php _e('settings page', 'quick-event-manager'); ?></a>.</p>
        <?php
    }
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['posts'] = $new_instance['posts'];
        $instance['fields'] = $new_instance['fields'];
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
    
    function __construct() {
		parent::__construct(
			'qem_calendar_widget', // Base ID
			__( 'Quick Event Calendar', 'quick-event-manager' ), // Name
			array( 'description' => __( 'Add an event calendar to your sidebar', 'quick-event-manager' ), ) // Args
		);
	}
	
    function form($instance) {
        $instance = wp_parse_args( (array) $instance, array(
            'eventlength' => '12',
            'smallicon' => 'trim',
            'unicode' =>'\263A',
            'categorykeybelow' => '',
            'categorykeyabove' => '',
            'header' => 'h2',
            'headerstyle' => ''
        ) );
        $eventlength = $instance['eventlength'];
        $smallicon = $instance['smallicon'];
        $header= $instance['header'];
        $$smallicon = 'checked';
        $$header = 'checked';
        $categorykeybelow = $instance['categorykeybelow'];
        $categorykeyabove = $instance['categorykeyabove'];
        $unicode = $instance['unicode'];
        $headerstyle = $instance['headerstyle'];
        if ( isset( $instance[ 'title' ] ) ) {$title = $instance[ 'title' ];}
        else {$title = __( 'Event Calendar', 'text_domain' );}
        ?>
        <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
<p><input type="checkbox" id="<?php echo $this->get_field_name('categorykeyabove'); ?>" name="<?php echo $this->get_field_name('categorykeyabove'); ?>" value="checked" <?php echo $categorykeyabove; ?>> Show category key above list</p>
        <p><input type="checkbox" id="<?php echo $this->get_field_name('categorykeybelow'); ?>" name="<?php echo $this->get_field_name('categorykeybelow'); ?>" value="checked" <?php echo $categorykeybelow; ?>> Show category key below list</p>
        <h3>Month and Date Style</h3>
        <p>
        <input type="radio" id="<?php echo $this->get_field_name('header'); ?>" name="<?php echo $this->get_field_name('header'); ?>" value="h2" <?php echo $h2; ?>> H2&nbsp;
        <input type="radio" id="<?php echo $this->get_field_name('header'); ?>" name="<?php echo $this->get_field_name('header'); ?>" value="h3" <?php echo $h3; ?>> H3&nbsp;
        <input type="radio" id="<?php echo $this->get_field_name('header'); ?>" name="<?php echo $this->get_field_name('header'); ?>" value="h4" <?php echo $h4; ?>> H4
        </p>
        <p>Header CSS:</p>
        <p><input class="widefat" type="text" id="<?php echo $this->get_field_name('headerstyle'); ?>" name="<?php echo $this->get_field_name('headerstyle'); ?>" value="<?php echo esc_attr( $headerstyle ); ?>" />
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
        $instance['header'] = $new_instance['header'];
        $instance['unicode'] = $new_instance['unicode'];
        $instance['categorykeyabove'] = $new_instance['categorykeyabove'];
        $instance['categorykeybelow'] = $new_instance['categorykeybelow'];
        $instance['headerstyle'] = $new_instance['headerstyle'];
        $instance['widget'] = 'widget';
        return $instance;
    }
	
    function widget($args, $instance) {
        extract($args, EXTR_SKIP);
        $title = apply_filters( 'widget_title', $instance['title'] );
        echo $args['before_widget'];
        if ( ! empty( $title ) ) echo $args['before_title'] . $title . $args['after_title'];
        echo qem_widget_calendar($instance);
        echo $args['after_widget'];
    }
}