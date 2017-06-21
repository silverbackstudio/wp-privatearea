<?php

namespace Svbk\WP\Plugins\PrivateArea\Shortcakes;

use Svbk\WP\Plugins\PrivateArea;
use Svbk\WP\Helpers;
use Svbk\WP\Shortcakes\Shortcake as Base;

class PayMembership extends Base {
    
    public $shortcode_id = 'pay_subscription';

    public static $defaults = array(
		'renew_button_label' => 'Renew Subscription',
		'upgrade_button_label' => 'Upgrade Subscription',
    );    
    
    public function title(){
        return __('Pay Subscription', 'svbk-privatearea');
    }
    
    function fields(){
        return array(        
        		array(
        			'label'  => esc_html__( 'Renew Button Label', 'svbk-privatearea' ),
        			'attr'   => 'renew_button_label',
        			'type'   => 'text',
        		),
        		array(
        			'label'  => esc_html__( 'Upgrade Button Label', 'svbk-privatearea' ),
        			'attr'   => 'upgrade_button_label',
        			'type'   => 'text',
        		)
            );
    }

    
    public function output( $attr, $content, $shortcode_tag) {
        
        $output = '';

        $attr = $this->shortcode_atts( self::$defaults, $attr, $shortcode_tag );         

        $member = PrivateArea\Member::current();
        $profile = $member->profile();    
        
        if( $profile ) {
        	$payment_button =  Helpers\Payment\PayPal::buttonUrl( 
        	    Helpers\Theme\Theme::conf('paypal', 'button_id'), 
        	    array( 
        	        'custom' => $member->id(),
        	    )
        	);
        }    
        
        $buttonLabel = $profile->is_type( PrivateArea\ACL::ROLE_MEMBER ) ? $attr['renew_button_label'] : $attr['upgrade_button_label'];
        
        return sprintf('<a class="button" href="%s" target="_blank" >%s</a>', esc_url($payment_button), $buttonLabel );
    }
    
}
