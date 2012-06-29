<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/* 
* +--------------------------------------------------------------------------+
* | Copyright (c) 2008-2012 Add This, LLC                                    |
* +--------------------------------------------------------------------------+
* | This program is free software; you can redistribute it and/or modify     |
* | it under the terms of the GNU General Public License as published by     |
* | the Free Software Foundation; either version 2 of the License, or        |
* | (at your option) any later version.                                      |
* |                                                                          |
* | This program is distributed in the hope that it will be useful,          |
* | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
* | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
* | GNU General Public License for more details.                             |
* |                                                                          |
* | You should have received a copy of the GNU General Public License        |
* | along with this program; if not, write to the Free Software              |
* | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA |
* +--------------------------------------------------------------------------+
*/
/**
* Plugin Name: AddThis Follow Widget
* Plugin URI: http://www.addthis.com
* Description: Generate followers for your social network and track what pages are generating the most followers 
* Version: 1.0.0
*
* Author: The AddThis Team
* Author URI: http://www.addthis.com/blog
*/


define('default_addthis_follow_style', 'horizontal_large');

class addthis_follow{

    function __construct(){
        add_action('widgets_init' , array($this , 'widgets_init'));
        add_action('admin_print_styles-widgets.php', array($this, 'admin_print_styles'));
    }
    
    function widgets_init(){
        register_widget('AddThisFollowSidebarWidget');
    }

    function admin_print_styles(){
        $style_location = apply_filters( 'addthis_follow_files_uri',  plugins_url( '', basename(dirname(__FILE__)) ) ) .'/addthis-follow/css/widgets-php.css'   ;
        $js_location = apply_filters( 'addthis_follow_files_uri',  plugins_url( '', basename(dirname(__FILE__)) ) ) .'/addthis-follow/js/widgets-php.js'   ;
        wp_enqueue_style('addthis_follow',  $style_location, array(), 0); 
        wp_enqueue_script('addthis_follow',  $js_location, array('jquery'), 0); 
    }




}
$addthis_follow = new addthis_follow();

class AddThisFollowSidebarWidget extends WP_Widget {

    var $styles = array(
        'hl' => array('Horizontal Large Menu', 'addthis_default_style addthis_32x32_style' ),
        'hs' => array('Horizontal Small Menu', 'addthis_default_style'),
        'vl' => array('Vertical Large Menu', 'addthis_vertical_style addthis_32x32_style'),
        'vs' => array('Vertical Small Menu', 'addthis_vertical_style')
    );

    // we can't set it now since we want to use bloginfo to autoset the RSS Feed
    var $buttons = array();


    /**
     *  Constructor
     */
    function AddThisFollowSidebarWidget()
    {

        $widget_ops = array( 'classname' => 'atfollowwidget', 'description' => 'Connect fans and followers with your profiles on top social services' );

        /* Widget control settings. */
        $control_ops = array( 'width' => 490);

        /* Create the widget. */
        $this->WP_Widget( 'addthis-follow-widget', 'AddThis Follow Widget', $widget_ops, $control_ops );
    
        $this->buttons = array(
        'facebook' => array(
            'name' => 'Facebook',
            'input' => 'http://www.facebook.com/ %s',
            'placeholder' => 'YOUR-PROFILE'
        ),
        'twitter' => array(
            'name' => 'Twitter',
            'input' => 'http://twitter.com/ %s',
            'placeholder' => 'YOUR-USERNAME'
        ),
        'youtube' => array(
            'name' => 'YouTube',
            'input' => 'http://www.youtube.com/user/ %s ',
            'placeholder' => ''
        ),
        'rss' => array(
            'name' => 'RSS',
            'input' => '%s',
            'placeholder' => get_bloginfo('rss2_url')
        ),
        'linkedin' => array(
            'name' => 'LinkedIn',
            'input' => 'http://www.linkedin.com/in/ %s',
            'placeholder' => ''
        ),
        'google' => array(
            'name' => 'Google+',
            'input' => 'https://plus.google.com/ %s',
            'placeholder' => ''
        ),
        'flickr' => array(
            'name' => 'Flickr',
            'input' => 'http://www.flickr.com/photos/ %s',
            'placeholder' => ''
        ),
        'vimeo' => array(
            'name' => 'Vimoe',
            'input' => 'http://www.vimeo.com/ %s ',
            'placeholder' => ''
        ),
        'pinterest' => array(
            'name' => 'Pintrest',
            'input' => 'http://www.pinterest.com/ %s',
            'placeholder' => ''
        ),
        'instagram' => array(
            'name' => 'Instagram',
            'input' => 'http://followgram.me/ %s',
            'placeholder' => ''
        ),
        'foursquare' => array(
            'name' => 'Foursquare',
            'input' => 'http://foursquare.com/ %s',
            'placeholder' => ''
        ),
        'tumblr' => array(
            'name' => 'Tumblr',
            'input' => 'http:// %s  &nbsp;.tumblr.com',
            'placeholder' => ''
        ),
    );

    }

    /**
     * Echo's out the content of our widget
     */
    function widget($args, $instance)
    {
        extract ( $args );

        $title = apply_filters('widget_title', $instance['title']);
        
        echo $before_widget;
        if ($title)
                echo $before_title . $title . $after_title;

        unset($instance['profile']);
        unset($instance['title']);

        $class = $this->styles[$instance['style']][1];

        echo '<div class="' . $class  . ' addthis_toolbox">';
        
        foreach($this->buttons as $id => $button){
            if (isset($instance[$id]) && ! (empty($instance[$id])) && ( $id == 'rss' ||  $instance[$id] != $button['placeholder']  )){
                echo '<a addthis:userid="' . esc_attr($instance[$id]) . '" class="addthis_button_'.$id.'_follow"></a>'; 
            }
        }
        
        // end the div
        echo '</div>';

        
        echo $after_widget;
    
    }

    /**
     * Update this instance
     */
    function update($new_instance, $old_instance)
    {
        $instance = array();
        global $addthis_addjs;
        if (isset( $new_instance['profile']) && substr($new_instance['profile'],0,2) != 'wp-'  )
        {
            $addthis_addjs->setProfileId( $new_instance['profile'] );
        }

        foreach($this->buttons as $id => $button){
            if (isset($new_instance[$id]))
                $instance[$id] = sanitize_text_field($new_instance[$id]);
        }
        $style =  $new_instance['style'];
        if (isset($this->styles[$style]) ) 
        {
             $instance['style'] = $style;
        }
        else
        {
             $instance['style'] = isset($this->styles[$style]);
        }
        $instance['title'] = sanitize_text_field($new_instance['title']);

        return $instance;
    }

    /**
     *  The form with the widget options
     */
    function form($instance)
    {
        global $addthis_addjs;
        if (empty($instance))
        {
            $instance = array('style'=> 'hl' , 'title' => 'Follow Me');
        }
        $addthis_options = get_option('addthis_settings');

        $style = (empty( $instance['style'] ) ) ? 'hl'  :  esc_attr($instance['style']);
        $title = (empty( $instance['title'] ) ) ? '' :   esc_attr($instance['title']);
        $profile = $addthis_addjs->getProfileID();
        
        echo $addthis_addjs->getAtPluginPromoText();

        echo '<p><label for="'.$this->get_field_id('title') .'">' . __('Title:') . '<input class="widefat" id="'.$this->get_field_id('title').'" name="'. $this->get_field_name('title') .'" type="text" value="'.$title .'" /></label></p>';

        echo '<p><label for="'. $this->get_field_id('profile') .'">'.__('Profile ID (shared accross all AddThis plugins):', 'addthis') .'</label><input class="widefat" id="'. $this->get_field_id('profile') .'" name="'.$this->get_field_name('profile') .'" type="text" value="'. $profile .'"</p>';
    
        // Style box
        echo '<p><label for="'. $this->get_field_id('style') . '">' . __('Style:', 'addthis') . '<br /><select id="toolbox-style" name="'.  $this->get_field_name('style') .'">';
        foreach($this->styles as $c => $n) {
            $selected = ($instance['style'] == $c) ? ' selected="selected" ' : '';
            echo '<option '.$selected.'value="'. $c . '">'.$n[0].'</option>';
        }
        echo '</select>';

        echo "<h4>Buttons</h4>";
        $count = 0;
        // Buttons
        foreach($this->buttons as $id => $button) {
            $class = ($count >= 4) ? 'atmore hidden' : '';
            $value = (empty($instance[$id])) ?   $button['placeholder'] : esc_attr($instance[$id]);
            echo '<p class="atfollowservice '.$class.'" ><img src="http://cache.addthiscdn.com/icons/v1/thumbs/'.$id.'.gif" /><label for="'. $this->get_field_id($id) .'">' . __( $button['name'], 'addthis') .'<span class="atinput">'. sprintf($button['input'] , '<input class="" id="'. $this->get_field_id($id) .'" name="'.$this->get_field_name($id) .'" type="text" value="'. $value .'">' ) .'</span></label></p>';
            $count++;
        }
        echo "<a href='#' class='atmorelink button-secondary'><span class='atmore'>" . __('More Options', 'addthis') . '</span><span class="atless hidden">'. __('Less Options', 'addthis'). "<span></a>";


    }

}

// Setup our shared resources early 
add_action('init', 'addthis_follow_early', 1);
function addthis_follow_early(){
    global $addthis_addjs;
    if (! isset($addthis_addjs)){
        require('includes/addthis_addjs.php');
        $addthis_options = get_option('addthis_settings');
        $addthis_addjs = new AddThis_addjs($addthis_options);
    }
}

