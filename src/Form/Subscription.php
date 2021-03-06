<?php
namespace Svbk\WP\Plugins\PrivateArea\Form;

use Svbk\WP\Helpers;
use Svbk\WP\Plugins\PrivateArea;
use DateTime;
use DateInterval;

class Subscription extends Helpers\Form\Submission {

    public $field_prefix = 'subms';
    public $action = 'svbk_subscription';
    
    public $createdUser;
    public $userRole;
    
    public function setInputFields( $fields=array(), $set_local = true){
        
        //parent::setInputFields();
        //$this->removeInputFields();
        
        $this->inputFields = 
            array(
                'first_name' => array( 
                    'required' => true,
                    'label' => __('First Name', 'svbk-privatearea'), 
                    'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
                    'error' => __('Please enter first name', 'svbk-privatearea')
                ),
                'last_name' => array( 
                    'required' => true,
                    'label' => __('Last Name', 'svbk-privatearea'), 
                    'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
                    'error' => __('Please enter last name', 'svbk-privatearea')
                ),                
                'user_email' => array( 
                    'required' => true,
                    'label' => __('Email Address', 'svbk-privatearea'), 
                    'filter' => FILTER_VALIDATE_EMAIL,
                    'error' => __('Invalid email address', 'svbk-privatearea')
                ),                
                'billing_company' => array( 
                    'required' => false,
                    'label' => __('Company Name', 'svbk-privatearea'), 
                    'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
                    'error' => __('Please enter your company name', 'svbk-privatearea')
                ),
                'billing_code' => array( 
                    'required' => true,
                    'label' => __('VAT ID / SSN', 'svbk-privatearea'), 
                    'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
                    'error' => __('Please enter your fiscal code or VAT ID', 'svbk-privatearea')
                ),                
                'billing_address_1' => array( 
                    'required' => true,
                    'label' => __('Address', 'svbk-privatearea'), 
                    'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
                    'error' => __('Please enter valid address', 'svbk-privatearea')
                ),                
                'billing_postcode' => array( 
                    'required' => true,
                    'label' => __('ZipCode', 'svbk-privatearea'), 
                    'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
                    'error' => __('Invalid zipcode', 'svbk-privatearea')
                ),
                'billing_city' => array( 
                    'required' => true,
                    'label' => __('City', 'svbk-privatearea'), 
                    'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
                    'error' => __('Invalid city', 'svbk-privatearea')
                ),  
                'billing_state' => array( 
                    'required' => true,
                    'label' => __('State/Province', 'svbk-privatearea'), 
                    'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
                    'error' => __('Invalid state/province', 'svbk-privatearea')
                ),  
                'billing_country' => array( 
                    'required' => true,
                    'label' => __('Country', 'svbk-privatearea'), 
                    'choices' => Helpers\Lists\Places::countries(),
                    'default' => 'IT',
                    'type' => 'select',  
                    'class' => array('select2'),
                    'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
                    'error' => __('Invalid country', 'svbk-privatearea')
                ),                  
                'phone' => array( 
                    'required' => true,
                    'label' => __('Phone', 'svbk-privatearea'), 
                    'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
                    'error' => __('Invalid phone number', 'svbk-privatearea')
                ), 
                'mobile' => array( 
                    'required' => true,
                    'label' => __('Mobile', 'svbk-privatearea'), 
                    'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
                    'error' => __('Invalid mobile number', 'svbk-privatearea')
                ),   
                'website' => array( 
                    'required' => false,
                    'label' => __('Website', 'svbk-privatearea'), 
                    'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
                    'error' => __('Invalid website', 'svbk-privatearea')
                ),  
        );
        
    }     
    
    protected function mainAction(){

        remove_action( 'register_new_user', 'wp_send_new_user_notifications' );

        $member = PrivateArea\Member::register_new( sanitize_user( $this->getInput('user_email') ) , $this->getInput('user_email') );
        
        if( is_wp_error( $member ) ){
            $this->addError( $member->get_error_message(), 'user_email' );
            return;
        }        
        
        $member->set_meta( 'first_name', $this->getInput('first_name' ) );
        $member->set_meta( 'last_name', $this->getInput('last_name' ) );
    
        $profile_meta = array(
            'post_title' => $this->getInput( 'billing_company' ),
            'meta_input' => array(
                'billing_company' => $this->getInput( 'billing_company' ), 
                'billing_first_name' => $this->getInput( 'first_name' ),
                'billing_last_name' => $this->getInput( 'last_name' ),
                'billing_code' => $this->getInput( 'billing_code' ),
                'billing_address_1' => $this->getInput( 'billing_address_1' ),
                'billing_postcode' => $this->getInput( 'billing_postcode' ),
                'billing_city' => $this->getInput( 'billing_city' ),
                'billing_state' => $this->getInput( 'billing_state' ),
                'billing_country' => $this->getInput( 'billing_country' ),
                'billing_email' => $this->getInput( 'user_email' ),
                'phone' => $this->getInput( 'phone' ),
                'mobile' => $this->getInput( 'mobile' ),
                'website' => $this->getInput( 'website' ),
            )
        );
    
        $profile = $member->profile();
        if( ! $profile ) {
            $profile = PrivateArea\create_profile($member->id(), $profile_meta);
        } else {
            $profile_meta['ID'] = $profile->id();
            wp_update_post($profile_meta);
        }
        
        $profile->set_type( PrivateArea\ACL::ROLE_SUPPORTER );

        $paymentDate = new DateTime('NOW');
        $profile->set_subscribe_date( $paymentDate );  
        
        $paymentDate->add( new DateInterval( Helpers\Theme\Theme::conf('subscription', 'trial') ) );
        $profile->set_expire( $paymentDate );        

        wp_new_user_notification( $member->id(), $this->userRole, 'both' );

        do_action( 'profile_update', $member->id(), get_userdata( $member->id() ) );

        $this->createdUser = $member->id();
        
    }    


    
}