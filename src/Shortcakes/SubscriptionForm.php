<?php

namespace Svbk\WP\Plugins\PrivateArea\Shortcakes;

use WP_Query;
use Svbk\WP\Helpers;
use Svbk\WP\Plugins\PrivateArea;
use Svbk\WP\Shortcakes\Forms\Form as Base;

class SubscriptionForm extends Base {
    
    public $md_apikey = '';
    public $md_template = '';
    public $messageDefaults;

    public $shortcode_id = 'subscription';
    public $field_prefix = 'subs';    
    public $action = 'svbk_subscribe';
    
    public $redirectToPayment = true;
    public $userRole;
    
    public $classes = array( 'form-privatearea-subscribe' );
    
    public $formClass = '\Svbk\WP\Plugins\PrivateArea\Form\Subscription';

    public function title(){
        return __('Subscription Form', 'svbk-privatearea');
    }
    
    public function confirmMessage(){
        return $this->confirmMessage ?: __('Thanks for your request, we will reply as soon as possible.', 'svbk-privatearea');
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
        );
    }
    
    protected function getForm($set_send_params=false){
        
        $form = parent::getForm($set_send_params);
    
        $form->userRole = $this->userRole;
        
        return $form;
    }    
    
    public function formatResponse($errors, $form) {
        
        $response = json_decode( parent::formatResponse($errors, $form), true );
        
        if( $this->redirectToPayment && empty( $errors ) && $form->createdUser){
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
