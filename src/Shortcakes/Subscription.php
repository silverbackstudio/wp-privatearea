<?php

namespace Svbk\WP\Plugins\PrivateArea\Shortcakes;

use WP_Query;
use Svbk\WP\Helpers;
use Svbk\WP\Plugins\PrivateArea;
use Svbk\WP\Shortcakes\Shortcake as Base;

class Subscription extends Base {
    
    public $md_apikey = '';
    public $md_template = '';
    public $messageDefaults;

    public $shortcode_id = 'subscription';
    public $field_prefix = 'subs';    
    public $action = 'svbk_subscribe';
    
    public $classes = array( 'form-privatearea-subscribe' );
    
    public $braintreeConfig = array( 'accessToken' => '' );
    public $orderPrefix = 'SVBK-';
    public $orderDescriptor = 'SVBK*ORDER';
    public $orderDescription = '';
    
    public $formClass = '\Svbk\WP\Plugins\PrivateArea\Form\Subscription';

    public function title(){
        return __('Subscription Form', 'svbk-privatearea');
    }
    
    public function confirmMessage(){
        return $this->confirmMessage ?: __('Thanks for your request, we will reply as soon as possible.', 'svbk-shortcakes');
    }    
    
    protected function getForm($set_send_params=false){
        
        $form = parent::getForm($set_send_params);
        
        $form->braintreeConfig = $this->braintreeConfig;
        $form->orderPrefix = $this->orderPrefix;
        $form->orderDescriptor = $this->orderDescriptor;
        $form->orderDescription = $this->orderDescription;
        
        if($set_send_params) {
            
            $form->md_apikey = $this->md_apikey;
            $form->templateName = $this->md_template;
            
            if(!empty( $this->messageDefaults ) ){
                $form->messageDefaults = array_merge(
                    $form->messageDefaults,
                    $this->messageDefaults
                );
            }
            
        }
        
        return $form;
    }    
    
    public function formatResponse($errors, $form) {
        
        $response = json_decode( parent::formatResponse($errors, $form), true );
        
        if( empty( $errors ) && $form->createdUser){
            $response['redirect'] = Helpers\Payment\PayPal::buttonUrl( 
                Helpers\Theme\Theme::conf('paypal', 'button_id'), 
                array( 
                    'custom' => $form->createdUser,
                )
            );
        }
        
        return json_encode( $response );
    }    
    
    // function fields(){
        
    //     $fields = parent::fields();
        
    //     $fields[] = array(
    //     			'label'  => esc_html__( 'Members Count', 'svbk-privatearea' ),
    //     			'attr'   => 'subscribe',
    //     			'type'   => 'text',
    //     			'encode' => true,
    //     			'description' => esc_html__( 'How many members to show', 'svbk-privatearea' ),
    //     			'meta'   => array(
    //     				'placeholder' =>  self::$defaults['count'],
    //     			),
    //         );
            
    //     return $fields;
    // }
    
}
