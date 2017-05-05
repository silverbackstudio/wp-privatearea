<?php

namespace Svbk\WP\Plugins\PrivateArea\Shortcakes;

use WP_Query;
use Svbk\WP\Helpers;
use Svbk\WP\Plugins\PrivateArea;
use Svbk\WP\Shortcakes\Forms\Form as Base;

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
    
    public function fields(){
        return array(
        		array(
        			'label'  => esc_html__( 'Submit Button Label', 'svbk-privatearea' ),
        			'attr'   => 'submit_button_label',
        			'type'   => 'text',
        			'encode' => true,
        			'description' => esc_html__( 'Submit Button label text', 'svbk-privatearea' ),
        		),
        		array(
        			'label'  => esc_html__( 'Form Type', 'svbk-privatearea' ),
        			'attr'   => 'member_type',
        			'type'   => 'select',
		            'options' => array(
				        array( 'value' => PrivateArea\ACL::ROLE_MEMBER, 'label' => esc_html__( 'Member', 'svbk-privatearea' ) ),
				        array( 'value' => PrivateArea\ACL::ROLE_SUPPORTER, 'label' => esc_html__( 'Supporter', 'svbk-privatearea' ) ),
				    ),
        			'description' => esc_html__( 'Select the type of subscription', 'svbk-privatearea' ),
        		)        		
        );
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
    
    
}
