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
 * Description: Generate followers for your social networks and track what pages are generating the most followers 
 * Version: 1.2.6
 *
 * Author: The AddThis Team
 * Author URI: http://www.addthis.com/blog
 */
define( 'ADDTHIS_FOLLOW_PLUGIN_VERSION' , '1.2.6');
define( 'ADDTHIS_FOLLOW_PRODUCT_VERSION' , 'wpp');
define( 'ADDTHIS_FOLLOW_ATVERSION', '300');

class AddThisFollowWidget {

    function __construct() {
        add_action('widgets_init', array($this, 'widgets_init'));
        add_action('admin_print_styles-widgets.php', array($this, 'admin_print_styles'));
    }

    function widgets_init() {
        register_widget('AddThisFollowSidebarWidget');
    }

    function admin_print_styles() {
        $style_location = apply_filters('addthis_follow_files_uri', plugins_url('', basename(dirname(__FILE__)))) . '/addthis-follow/css/widgets-php.css';
        $js_location = apply_filters('addthis_follow_files_uri', plugins_url('', basename(dirname(__FILE__)))) . '/addthis-follow/js/widgets-php.js';
        wp_enqueue_style('addthis_follow', $style_location, array(), 0);
        wp_enqueue_script('addthis_follow', $js_location, array('jquery'), 0);
    }
    
}

new AddThisFollowWidget();

/**
 * Basic Follow Options class shared by the Follow Plugin and Widget
 */
class FollowOptions {

    private static $_instance = null;
    private $_styles = null;
    private $_defaultStyle = 'hl';
    private $_defaultTitle = 'Follow Me';
    private $_buttonOptions = null;

    private function __construct() {
        $this->_styles = array(
            'hl' => array('Horizontal Large Menu', 'addthis_default_style addthis_32x32_style'),
            'hs' => array('Horizontal Small Menu', 'addthis_default_style'),
            'vl' => array('Vertical Large Menu', 'addthis_vertical_style addthis_32x32_style'),
            'vs' => array('Vertical Small Menu', 'addthis_vertical_style'));
        $this->_buttonOptions = array(
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
            'linkedin' => array(
                'name' => 'LinkedIn',
                'input' => 'http://www.linkedin.com/in/ %s',
                'placeholder' => ''
            ),
            'linkedin-company' => array(
                'name' => 'LinkedIn',
                'input' => 'http://www.linkedin.com/company/ %s',
                'placeholder' => ''
            ),
            'google' => array(
                'name' => 'Google+',
                'input' => 'https://plus.google.com/ %s',
                'placeholder' => ''
            ),
            'youtube' => array(
                'name' => 'YouTube',
                'input' => 'http://www.youtube.com/user/ %s ',
                'placeholder' => ''
            ),
            'flickr' => array(
                'name' => 'Flickr',
                'input' => 'http://www.flickr.com/photos/ %s',
                'placeholder' => ''
            ),
            'vimeo' => array(
                'name' => 'Vimeo',
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
            'rss' => array(
                'name' => 'RSS',
                'input' => '%s',
                'placeholder' => get_bloginfo('rss2_url')
            )
        );
    }

    public static function getInstance() {
        if (self::$_instance == null) {
            self::$_instance = new FollowOptions();
        }
        return self::$_instance;
    }

    public function getStyles() {
        return $this->_styles;
    }

    public function getDefaultStyle() {
        return $this->_defaultStyle;
    }

    public function getDefaultTitle() {
        return $this->_defaultTitle;
    }

    public function getButtonOptions() {
        return $this->_buttonOptions;
    }

}

/**
 * Merge the source options with the default button options
 * such that the customizations done on the default options overrides the default options
 * 
 * @param array $sourceList - List of source options
 * @param array $buttonOptions  - List of attribute of ButtonOptions
 * @param string $title - string
 * @param string $style - string
 */
function merge_options($sourceList, &$buttonOptions, &$title, &$style) {
    foreach ($sourceList as $buttonKey => $buttonValue) {
        if (array_key_exists($buttonKey, $buttonOptions) &&
                !in_array($buttonValue, array('', 'YOUR-PROFILE', 'YOUR-USERNAME'))) {
            $buttonOptions[$buttonKey]['placeholder'] = $buttonValue;
        } else {
            if ($buttonKey == 'style' && !in_array($buttonValue, array('', 'hl'))) {
                $style = $buttonValue;
            } else if ($buttonKey == 'title' && !in_array($buttonValue, array(''))) {
                $title = $buttonValue;
            }
        }
    }
}

/**
 * AddThis Follow Plugin and its settings
 */
class AddThisFollowPlugin {

    private $_followOptions = null;

    public function __construct() {
        $this->_followOptions = FollowOptions::getInstance();
        add_filter('admin_menu', array($this, 'admin_menu'));
        add_action('admin_init', array($this, 'register_follow_settings'));
    }

    function register_follow_settings() {
        register_setting('addthis_follow_settings', 'addthis_follow_settings', array($this, 'save_settings'));
    }

    /**
     * Callback for saving the Follow plugin settings
     * Sanitize the options and save it
     * 
     * @param array $input
     * @return array $options
     */
    function save_settings($input) {
        $options = array();
        $buttonOptions = $this->_followOptions->getButtonOptions();
        $allowedKeys = array_merge(array_keys($buttonOptions), array('style', 'title'));
        foreach ($input as $key => $value) {
            if (in_array($key, $allowedKeys)) {
                $value = sanitize_text_field($value);
                if ($value == '' && $buttonOptions[$key]['placeholder'] != '') {
                    $value = $buttonOptions[$key]['placeholder'];
                }
                $options[$key] = $value;
            }
        }
        return $options;
    }

    /**
     * AddThis Follow Admin menu
     */
    function admin_menu() {
        if (is_admin()) {
            $follow = add_options_page('AddThis Plugin Options', 'AddThis Follow', 'manage_options', basename(__FILE__), array($this, 'options'));
        }
    }

    /**
     * AddThis Follow settings page
     */
    function options() {
        wp_enqueue_style('adminstyles', plugins_url('css/admin-options.css', __FILE__));
        if (version_compare(get_bloginfo('version'), '3.3', '<')) {
            wp_head();
        }
        $buttonOptions = $this->_followOptions->getButtonOptions();
        $style = $this->_followOptions->getDefaultStyle();
        $title = $this->_followOptions->getDefaultTitle();

        $commonFollowOptions = get_option('addthis_follow_settings');
        $followWidgetOptions = get_option('widget_addthis-follow-widget');
        /**
         * Restore from widget settings if possible
         */
        if (($commonFollowOptions == false || empty($commonFollowOptions)) && $followWidgetOptions != false) {
            foreach ($followWidgetOptions as $key => $list) {
                if (is_int($key) && is_array($list)) {
                    merge_options($list, $buttonOptions, $title, $style);
                }
            }
            $restoreOptions = array('title' => $title, 'style' => $style);
            foreach($buttonOptions as $key => $value) {
                $restoreOptions[$key] = $value['placeholder'];
            }
            add_option('addthis_follow_settings', $restoreOptions);
        } elseif ($commonFollowOptions != false && !empty($commonFollowOptions)) {
            /**
             * Follow options already exists, just update it
             */
            merge_options($commonFollowOptions, $buttonOptions, $title, $style);
        }

        $this->displayOptionsForm($buttonOptions, $style, $title);
    }

    /**
     * Display the Follow Options Form
     * @global AddThis_addjs $addthis_addjs
     * @param FollowOptions $buttonOptions
     * @param string $style
     * @param string $title
     */
    function displayOptionsForm($buttonOptions, $style, $title) {
        global $addthis_addjs;
        ?>
        <?php if(!at_follow_is_pro_user()) { ?>
        <div class="updated addthis_setup_nag">
            <p>AddThis Pro now available - start your trial at 
                <a href="http://www.addthis.com" target="_blank">www.addthis.com</a> 
                and get premium widgets, personalized content recommendations, 
                advanced customization options and priority support.
            </p>
        </div><br/>
        <?php } ?>
        <?php echo $addthis_addjs->getAtPluginPromoText(); ?>
        <img alt='addthis' src="//cache.addthis.com/icons/v1/thumbs/32x32/more.png" class="header-img"/>
        <span class="addthis-title">AddThis</span> <span class="addthis-plugin-name">Follow</span>
        <form method="post" action="options.php">
            <?php
            settings_fields('addthis_follow_settings');

            echo '<table class="follow-container">
                    <tr>
                        <td>
                            <p><strong><label for="style">' . __('Style:', 'addthis') . '</label></strong></p>
                            <select id="style" name="addthis_follow_settings[style]">';
            foreach ($this->_followOptions->getStyles() as $c => $n) {
                $selected = ($style == $c) ? ' selected="selected" ' : '';
                echo '<option ' . $selected . 'value="' . $c . '">' . $n[0] . '</option>';
            }
            echo '</select>
                        </td>
                        <td style="text-align:right;padding-left:40px;">
                            <p>&nbsp;</p>
                            <p><strong>' . __('AddThis Profile ID:', 'addthis') . '</strong>
                                <input style="width:160px" disabled="disabled" class="widefat" type="text" value="' . $addthis_addjs->pubid . '" />
                                    </p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                        <p><strong><label for="title">' . __('Header:') . '</label></strong></p>
                        <input style="width:160px" class="widefat" id="title" name="addthis_follow_settings[title]" type="text" value="' . $title . '" />
                            </td><td>&nbsp;</td>
                            </tr>
                    <tr>
<td>
   <p><strong>' . __('Buttons:') . '</strong></p>';
       ?>

       <table id="follow-table" cellspacing="0" cellpadding="0">
           <?php
           $buttonCount = count($buttonOptions);
           $count = 0;

           foreach ($buttonOptions as $id => $button) {
           	   if ($id == "linkedin-company") {
	           			$source = "http://cache.addthiscdn.com/icons/v1/thumbs/linkedin.gif";
	           	}
	           	else {
	           		$source = "http://cache.addthiscdn.com/icons/v1/thumbs/$id.gif";
	           	}
           	   $rowClass = '';
               if (++$count < $buttonCount) {
                   $rowClass = ' class="follow-table-row"';
               }
               echo '<tr' . $rowClass . '>
           <td>
               <img src="'.$source.'" />
               <strong><label for="' . $id . '">' . __($button['name'], 'addthis') . '</label></strong>
           </td>
           <td style="text-align:right">
           ' . sprintf($button['input'], '<input class="follow-input" id="' . $id . '" name="addthis_follow_settings[' . $id . ']" type="text" value="' . $button['placeholder'] . '">') . '
           </td>
           </tr>';
           }

           echo '</table> 
       </td><td>&nbsp;</td>
       </tr>
       <tr>
       <td colspan="2">
       <p><input class="button-secondary" type="submit" name="Submit" value="';
           echo _e('Save');
           echo '" /> </p></td> </tr> </table></form>';
    }

}

add_action('init', 'initialize_addthis_follow_plugin');
register_deactivation_hook(__FILE__, 'addthis_follow_remove');

/**
 * Deactivation callback
 */
function addthis_follow_remove() {
    delete_option('addthis_follow_settings');
}

/**
 * Plugin initialization callback
 */
function initialize_addthis_follow_plugin() {
    new AddThisFollowPlugin();
}

class AddThisFollowSidebarWidget extends WP_Widget {

    function AddThisFollowSidebarWidget() {
        $widget_ops = array('classname' => 'atfollowwidget', 'description' => 'Connect fans and followers with your profiles on top social services');

        /* Widget control settings. */
        $control_ops = array('width' => 490);

        /* Create the widget. */
        $this->WP_Widget('addthis-follow-widget', 'AddThis Follow', $widget_ops, $control_ops);
        
    }

    /**
     * Echo's out the content of our widget
     */
    function widget($args, $instance) {
    	if (!empty($args)) {
	        extract($args);
	   	}

        $title = apply_filters('widget_title', $instance['title']);

        echo $before_widget;
        if ($title)
            echo $before_title . $title . $after_title;

        unset($instance['profile']);
        unset($instance['title']);
        $styles = FollowOptions::getInstance()->getStyles();
        $class = $styles[$instance['style']][1];

        echo '<div class="' . $class . ' addthis_toolbox">';

        foreach (FollowOptions::getInstance()->getButtonOptions() as $id => $button) {
            if (isset($instance[$id]) && !(empty($instance[$id])) && ( $id == 'rss' || $instance[$id] != $button['placeholder'] )) {
            	if ($id == "linkedin-company") {
        			echo '<a addthis:userid="' . esc_attr($instance[$id]) . '" class="addthis_button_linkedin_follow" addthis:usertype="company"></a>';
        		}
        		else {
                	echo '<a addthis:userid="' . esc_attr($instance[$id]) . '" class="addthis_button_' . $id . '_follow"></a>';
        		}
            }
        }

        // end the div
        echo '</div>';

        echo $after_widget;
    }

    /**
     * Update this instance
     */
    function update($new_instance, $old_instance) {
        $instance = array();
        $styles = FollowOptions::getInstance()->getStyles();
        $options = get_option('addthis_settings');
        global $addthis_addjs;
        if (isset($new_instance['profile']) && substr($new_instance['profile'], 0, 2) != 'wp-') {
        	if (strcmp($new_instance['profile'], $options['profile']) != 0) {
        		$addthis_addjs->setProfileId($new_instance['profile']);
        	}
        }
		
        foreach (FollowOptions::getInstance()->getButtonOptions() as $id => $button) {
            if (isset($new_instance[$id]))
                $instance[$id] = sanitize_text_field($new_instance[$id]);
        }
        
        $style = $new_instance['style'];
        if (isset($styles[$style])) {
            $instance['style'] = $style;
        } else {
            $instance['style'] = isset($styles[$style]);
        }
        
        $instance['title'] = sanitize_text_field($new_instance['title']);

        return $instance;
    }

    /**
     *  The form with the widget options
     */
    function form($instance) {
        global $addthis_addjs;
        $addthis_follow_options = get_option('addthis_follow_settings');
        $followOptions = FollowOptions::getInstance();
        $buttonOptions = $followOptions->getButtonOptions();

        $style = $followOptions->getDefaultStyle();
        $title = $followOptions->getDefaultTitle();
        if ($addthis_follow_options != false && !empty($addthis_follow_options)) {
            merge_options($addthis_follow_options, $buttonOptions, $title, $style);
        }

        $style = (empty($instance) ) ? $style : esc_attr($instance['style']);
        $title = (empty($instance) ) ? $title : esc_attr($instance['title']);
        $profile = $addthis_addjs->getProfileID();

        echo $addthis_addjs->getAtPluginPromoText();
        echo '
        <table width="100%" class="follow-table">
            <tr>
                <td style="width:290px">
                    <p><strong><label for="' . $this->get_field_id('style') . '">' . __('Styles:', 'addthis') . '</label></strong></p>
                    <select id="toolbox-style" name="' . $this->get_field_name('style') . '">';
        foreach ($followOptions->getStyles() as $c => $n) {
            $selected = ($style == $c) ? ' selected="selected" ' : '';
            echo '<option ' . $selected . 'value="' . $c . '">' . $n[0] . '</option>';
        }
        echo '</select>
                </td>
                <td style="float:right">
                    <p><strong><label for="' . $this->get_field_id('profile') . '">' . __('AddThis Profile ID:', 'addthis') . '</label></strong></p>
                        <input style="width:160px" class="widefat" id="' . $this->get_field_id('profile') . '" name="' . $this->get_field_name('profile') . '" type="text" value="' . $profile . '" />
                </td>
            </tr>
            <tr>
                <td colspan="2">
                <p><strong><label for="' . $this->get_field_id('title') . '">' . __('Header:') . '</label></strong></p>
                <input style="width:160px" class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" />
                    </td>
                    </tr>
            <tr>
<td colspan="2">
<p><strong>' . __('Buttons:') . '</strong></p>';
        $count = 0;
        // Buttons
        foreach ($buttonOptions as $id => $button) {
            $class = ($count >= 4) ? 'atmore hidden' : '';
            $value = empty($instance) ? $button['placeholder'] : esc_attr($instance[$id]);
        	if ($id == "linkedin-company") {
	        	$source = "http://cache.addthiscdn.com/icons/v1/thumbs/linkedin.gif";
	        }
	        else {
	        	$source = "http://cache.addthiscdn.com/icons/v1/thumbs/$id.gif";
	        }
            echo '<p class="atfollowservice ' . $class . '" ><img src="'.$source.'" /><label for="' . $this->get_field_id($id) . '">' . __($button['name'], 'addthis') . '<span class="atinput">' . sprintf($button['input'], '<input class="" id="' . $this->get_field_id($id) . '" name="' . $this->get_field_name($id) . '" type="text" value="' . $value . '">') . '</span></label></p>';
            $count++;
        }
        echo '</td></tr>
    <tr><td colspan="2">';
        echo "<a href='#' class='atmorelink button-secondary'><span class='atmore'>" . __('More Options', 'addthis') . '</span><span class="atless hidden">' . __('Less Options', 'addthis') . "<span></a>
        </td></tr></table>";
    }

}

// Setup our shared resources early 
add_action('init', 'addthis_follow_early', 1);

function addthis_follow_early() {
    global $addthis_addjs;
    if (!isset($addthis_addjs)) {
        require('includes/addthis_addjs.php');
        $addthis_options = get_option('addthis_settings');
        $addthis_addjs = new AddThis_addjs($addthis_options);
    } elseif (!method_exists($addthis_addjs, 'getAtPluginPromoText')) {
        require('includes/addthis_addjs_extender.php');
        $addthis_addjs = new AddThis_addjs_extender($addthis_options);
    }
}

//Short code
function addthis_follow() {
	$options = get_option('addthis_follow_settings');
	$addthis_follow = new AddThisFollowSidebarWidget();
	echo $addthis_follow->widget('', $options);
	
}

// check for pro user
function at_follow_is_pro_user() {
    $isPro = false;
    $options = get_option('addthis_settings');
    $profile = $options['profile'];
    if ($profile) {
        $request = wp_remote_get( "http://q.addthis.com/feeds/1.0/config.json?pubid=" . $profile );
        $server_output = wp_remote_retrieve_body( $request );
        $array = json_decode($server_output);
        // check for pro user
        if (array_key_exists('_default',$array)) {
            $isPro = true;
        } else {
            $isPro = false;
        }
    }
    return $isPro;
}
