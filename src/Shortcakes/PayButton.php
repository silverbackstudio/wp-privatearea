<?php

namespace Svbk\WP\Plugins\PrivateArea\Shortcakes;

use Svbk\WP\Plugins\PrivateArea;
use Svbk\WP\Helpers;
use Svbk\WP\Shortcakes\Shortcake as Base;

class PayButton extends Base {
    
    public $shortcode_id = 'pay_button';
    public $icon = 'dashicons-cart';

    public $classes = 'pay-button';

    public $defaults = array(
		'membership_level' => '',
		'payment_page' => '',
		'show_countdown' => true,
		'show_discount' => true,
		'button_label' => '',
    );    
    
    public $renderOrder = array(
        'wrapperStart',
        'listPrice',
        'countdown',        
        'discountedPrice',
        'button',
        'wrapperEnd'
    );
    
    public function title(){
        return __('Pay Button', 'svbk-privatearea');
    }
    
    function fields(){
        return array(        
        		array(
        			'label'  => esc_html__( 'Membership Level', 'svbk-privatearea' ),
        			'attr'   => 'membership_level',
        			'type'   => 'select',
        			'options' => PrivateArea\Membership::levels(),
        		),
        		array(
        			'label'    => esc_html__( 'Show Countdown', 'svbk-privatearea' ),
        			'attr'     => 'show_countdown',
        			'type'     => 'checkbox'
        		), 
        		array(
        			'label'    => esc_html__( 'Show Countdown', 'svbk-privatearea' ),
        			'attr'     => 'show_discount',
        			'type'     => 'checkbox'
        		),         		
        		array(
        			'label'    => esc_html__( 'Payment Page', 'svbk-privatearea' ),
        			'attr'     => 'payment_page',
        			'type'     => 'post_select',
        			'query'    => array( 'post_type' => 'page' ),
        			'multiple' => true,
        		),        		
        		array(
        			'label'  => esc_html__( 'Button Label', 'svbk-privatearea' ),
        			'attr'   => 'button_label',
        			'type'   => 'text',
        		)
            );
    }

    protected function getClasses( $attr, $levelDetails = null ){
        
        $classes = parent::getClasses( $attr );
        
        if( $levelDetails ) {
            $classes[] = 'prices';
            $classes[] = 'level-' . $levelDetails['role'];
        }
        
        if ( $attr['show_discount'] ) {
            $classes[] = 'has-discount';
        }
        
        
        return $classes;
    }

    
    public function renderOutput( $attr, $content, $shortcode_tag) {
        
        $output = '';

        $attr = $this->shortcode_atts( $this->defaults, $attr, $shortcode_tag );         
        $levelDetails = PrivateArea\Membership::levelDetails( $attr['membership_level'] );
        
        $output['wrapperStart'] = '<aside ' . self::renderClasses( $this->getClasses($attr, $levelDetails) ) . '>';
        
        if ( $attr['show_countdown'] ) : 
            $output['countdown'] = '<div class="countdown level-' . esc_attr( $attr['membership_level'] ) . '" data-level="' . esc_attr( $attr['membership_level'] ) . '"></div>';
        endif;         
        
        $output['listPrice'] = '<div class="price regular">' . PrivateArea\format_price( $levelDetails['price'] ) . '</div>';
        
        if ( $attr['show_discount'] ) : 
        $output['discountedPrice'] .= '<div class="price discounted">' . PrivateArea\format_price( $levelDetails['discounted_price'] ) . '</div>';
        endif;
        
        $output['button'] = '<a href="' . get_permalink( $attr['payment_page'] ) . '" class="button">' . esc_html( $attr['button_label'] ) . '</a>';
        $output['wrapperEnd'] .= '</aside>';
        
        return $output;
        
    }
    
}

