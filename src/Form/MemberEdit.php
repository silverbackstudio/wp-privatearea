<?php
namespace Svbk\WP\Plugins\PrivateArea\Form;

use Svbk\WP\Helpers;
use Svbk\WP\Plugins\PrivateArea;
use DateTime;
use DateInterval;
use WP_User;

class MemberEdit extends Subscription {

    public $field_prefix = 'medit';
    public $action = 'svbk_member_edit';
    
    public function setInputFields( $fields = array(), $set_local = true ){    
        
        parent::setInputFields( $fields, $set_local );
        
        $this->addInputFields( array(
                'billing_last_name' => array( 
                    'required' => true,
                    'label' => __('Company First Name', 'svbk-privatearea'), 
                    'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
                    'error' => __('Please enter first name', 'svbk-privatearea')
                ),
                'billing_first_name' => array( 
                    'required' => true,
                    'label' => __('Company Last Name', 'svbk-privatearea'), 
                    'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
                    'error' => __('Please enter last name', 'svbk-privatearea')
                ),           
        ), 'last_name' );
        
        $current_user = wp_get_current_user();
        
        if ( !($current_user instanceof WP_User) ) {
        return;
        }
    
        $member = new PrivateArea\Member( $current_user );
        
        $profile = $member->profile();
        
        if ( ! $profile ) {
            return;
        }
        
        $meta = get_post_meta( $profile->id() );

        foreach( $this->inputFields as $name => $value ){
    
            if( isset( $meta[ $name ] ) && isset( $meta[ $name ][ 0 ] )  ) {
                $this->inputFields[ $name ]['default'] = $meta[ $name ][ 0 ];
            }
        }
        
        $this->inputFields[ 'first_name' ]['default'] = $member->meta( 'first_name' );
        $this->inputFields[ 'last_name' ]['default'] = $member->meta( 'last_name' );
        $this->inputFields[ 'user_email' ]['default'] = $member->meta( 'user_email' );
        
    }
    
    protected function mainAction(){
    
        $current_user = wp_get_current_user();
        
        if ( !($current_user instanceof WP_User) ) {
        return;
        }
    
        if( is_wp_error( $current_user ) ){
            $this->addError( $current_user->get_error_message() );
            return;
        }
    
        $member = new PrivateArea\Member( $current_user );
        
        $member->set_meta( 'first_name', $this->getInput('first_name' ) );
        $member->set_meta( 'last_name', $this->getInput('last_name' ) );
    
        $profile_meta = array(
            'post_title' => $this->getInput( 'billing_company' ),
            'meta_input' => array(
                'billing_company' => $this->getInput( 'billing_company' ), 
                'billing_first_name' => $this->getInput( 'billing_first_name' ),
                'billing_last_name' => $this->getInput( 'billing_last_name' ),
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

        $user_save = wp_update_user( array( 
            'ID' => $member->id(),
            'first_name' => $this->getInput( 'first_name' ),
            'last_name' => $this->getInput( 'last_name' ),
            )
        );

        if ( is_wp_error( $user_save ) ) {
        	$errors = $user_save->get_error_messages();
        	foreach ($errors as $error) {
        		$this->addError( $error );
        	}
        } 

        if( $profile ) {
            $profile_meta['ID']  = $profile->id();
        }
        
        $post_id = wp_update_post( $profile_meta, true );	
        
        if ( is_wp_error($post_id) ) {
        	$errors = $post_id->get_error_messages();
        	foreach ($errors as $error) {
        		$this->addError( $error );
        	}
        }         
        
        if( ! $profile ){
            $member->set_profile( $post_id );
        }
        
    }    

    public function checkPolicy($policyPart='policy_service'){
        return true;
    }
    
    public function setPolicyParts( $policyParts = array() ){
        
    }       
    
}