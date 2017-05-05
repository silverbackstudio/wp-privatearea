<?php

namespace Svbk\WP\Plugins\PrivateArea\Shortcakes;

use WP_Query;
use Svbk\WP\Plugins\PrivateArea;
use Svbk\WP\Shortcakes\Shortcake as Base;

class MemberInfo extends Base {
    
    public $shortcode_id = 'member_info';

    public static $defaults = array(
		'field' => '',
    );    
    
    public function title(){
        return __('Member Info', 'svbk-privatearea');
    }
    
    public function register_ui(){
    	
    }    
    
    function fields(){
        return array(        
        		array(
        			'label'  => esc_html__( 'Member Field', 'svbk-privatearea' ),
        			'attr'   => 'field',
                    'type'   => 'select',
                    'options' => array(
                        'first_name' => __('First Name', 'svbk-privatearea'),
                        'last_name' => __('Last Name', 'svbk-privatearea'),
                        'expire_eta' => __('Expire ETA', 'svbk-privatearea'),
                        'member_type' => __('Member Type', 'svbk-privatearea'),
                    ),
        		)
            );
    }

    public function output( $attr, $content, $shortcode_tag) {
        
        $member = PrivateArea\Member::current();
        $profile = $member->profile();
        
        $attr = $this->shortcode_atts( self::$defaults, $attr, $shortcode_tag );
        
        if( defined('SHORTCODE_UI_DOING_PREVIEW') && SHORTCODE_UI_DOING_PREVIEW ) {
            return null;
        }          
        
        switch($attr['field']){
            case 'expire_eta':
                
                if( ! $profile ){
                    return 'N/A';
                }
                
                $eta = $profile->subscription_expire_eta();
                
                if ( null !== $eta ) {
                    return $eta->format('%a');
                } else {
                    return 'N/A';
                }
                break;
            case 'member_type':
                if( ! $profile ){
                    return 'N/A';
                }                
                
                return $profile->subscription_name();
                break;
            default:
                return $member->meta( $attr['field'] );
                break;
            
        }
            
    	return '[N/A]';
    	
    }
    
}
