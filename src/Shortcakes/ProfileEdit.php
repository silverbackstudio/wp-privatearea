<?php

namespace Svbk\WP\Plugins\PrivateArea\Shortcakes;

use Svbk\WP\Plugins\PrivateArea;
use Svbk\WP\Shortcakes;

class ProfileEdit extends Shortcakes\Forms\ACF {
    
    public $shortcode_id = 'member_edit';

    public function title(){
        return __('Member Edit Form', 'svbk-privatearea');
    }
    
    public function shortcode_atts( $defaults, $attr=array(), $shortcode_tag='' ){
        
        $attr = parent::shortcode_atts($defaults, $attr, $shortcode_tag);
        
        $member = PrivateArea\Member::current();
        $profile = $member->profile();
        
        if( $profile) {
            $attr['post_id'] = $profile->id();
        } else {
            $attr['post_id'] = 'new_post';
        }
        
        return $attr;
        
    }

}
