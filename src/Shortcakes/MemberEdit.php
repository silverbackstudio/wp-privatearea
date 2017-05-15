<?php

namespace Svbk\WP\Plugins\PrivateArea\Shortcakes;

use Svbk\WP\Plugins\PrivateArea;
use Svbk\WP\Shortcakes;

class MemberEdit extends Shortcakes\Forms\ACF {
    
    public $shortcode_id = 'user_edit';

    public function title(){
        return __('User Edit Form', 'svbk-privatearea');
    }
    
    public function shortcode_atts( $defaults, $attr=array(), $shortcode_tag='' ){
        
        $attr = parent::shortcode_atts($defaults, $attr, $shortcode_tag);
        
        $member = PrivateArea\Member::current();

        if( $member ) {
            $attr['post_id'] = 'user_' . $member->id();
        } 
        
        $attr['fields'] = wp_filter_object_list( 
            acf_get_fields( $attr['field_groups'] ), 
            array( 
                'name' => PrivateArea\Member::PROFILE_FIELD 
            ), 
            'NOT',
            'key'
        );
        
        return $attr;
        
    }

    public function output( $attr, $content, $shortcode_tag ) {
        
        $content = parent::output($attr, $content, $shortcode_tag);
        
        $content .= '<a class="lost-password" href="'. wp_lostpassword_url( get_permalink() ) .'" >'. __('Lost Password? Click here to reset it', 'propertymanagers') .'</a>';

        return $content;
        
    }

}
