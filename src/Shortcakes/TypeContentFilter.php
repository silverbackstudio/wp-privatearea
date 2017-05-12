<?php

namespace Svbk\WP\Plugins\PrivateArea\Shortcakes;

use WP_Query;
use Svbk\WP\Helpers;
use Svbk\WP\Plugins\PrivateArea;
use Svbk\WP\Shortcakes\Shortcake;

class TypeContentFilter extends Shortcake {

    public $shortcode_id = 'privatearea_content_filter';

    public function title(){
        return __('Member Type Content Filter', 'svbk-privatearea');
    }
    
    public static $defaults = array(
		'member_type' => '',
    );        

    function fields(){
        return array(        
        		array(
        			'label'  => esc_html__( 'Members Type', 'svbk-privatearea' ),
        			'attr'   => 'member_type',
        			'type'   => 'select',
        			'description' => esc_html__( 'Show only to this member type', 'svbk-privatearea' ),
        			'options' => PrivateArea\ACL::available_roles()
        		)
            );
    }

    public function output( $attr, $content, $shortcode_tag) {
        $attr = $this->shortcode_atts( self::$defaults, $attr, $shortcode_tag );         
    
        if( PrivateArea\Member::current()->get_type() === $attr['member_type'] ) {
            return $content;
        } 
    }
    
}
