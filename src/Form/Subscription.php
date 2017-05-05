<?php
namespace Svbk\WP\Plugins\PrivateArea\Form;

use Svbk\WP\Helpers\Form\Submission;
use Svbk\WP\Plugins\PrivateArea;

class Subscription extends Submission {

    public $field_prefix = 'subms';
    public $action = 'svbk_subscription';
    
    public $price = 250;
    
    public $mc_apikey = '';
    public $braintreeConfig = array( 'accessToken' => '' );
    public $orderPrefix = 'SVBK-';
    public $orderDescriptor = 'SVBK*ORDER';
    public $orderDescription = '';
    public $braintreeGateway;
    
    public $createdUser;
    
    public function setInputFields( $fields=array(), $set_local = true){
        
        //parent::setInputFields();
        //$this->removeInputFields();
        
        $this->inputFields = 
            array(
                'last_name' => array( 
                    'required' => true,
                    'label' => __('First Name', 'svbk-helpers'), 
                    'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
                    'error' => __('Please enter first name', 'svbk-helpers')
                ),
                'first_name' => array( 
                    'required' => true,
                    'label' => __('Last Name', 'svbk-helpers'), 
                    'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
                    'error' => __('Please enter last name', 'svbk-helpers')
                ),                
                'user_email' => array( 
                    'required' => true,
                    'label' => __('Email Address', 'svbk-helpers'), 
                    'filter' => FILTER_VALIDATE_EMAIL,
                    'error' => __('Invalid email address', 'svbk-helpers')
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
                    'required' => true,
                    'label' => __('Website', 'svbk-privatearea'), 
                    'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
                    'error' => __('Invalid website', 'svbk-privatearea')
                ),  
        );
        
    }     
    
    // protected function paymentGateway(){
        
    //     if(!is_a('Braintree_Gateway', $this->braintreeGateway)){
    //         $this->braintreeGateway = new Braintree_Gateway($this->braintreeConfig);
    //     }
        
    //     return $this->braintreeGateway;        
    // }
    
    // public function getClientToken(){
    //     return $this->paymentGateway()->clientToken()->generate();
    // }
    
    protected function mainAction(){
    
        $user_id = register_new_user( sanitize_user( $this->getInput('user_email') ) , $this->getInput('user_email') );
    
        if( is_wp_error( $user_id ) ){
            $this->addError( $user_id->get_error_message(), 'user_email' );
            return;
        }
    
        $member = new PrivateArea\Member( $user_id );
        $member->set_type( PrivateArea\ACL::ROLE_SUPPORTER );
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
    
        svbk_user_register_create_profile($user_id, $profile_meta);
        
        $this->createdUser = $member->id();
        
        // $bt_result =  $this->paymentGateway()->transaction()->sale([
        //     "amount" => $this->price,
        //     'merchantAccountId' => 'EUR',
        //     "paymentMethodNonce" => $this->getInput('paypalToken'),
        //     "orderId" => $this->orderPrefix . date('YmdHis'),
        //     "descriptor" => [
        //       "name" => $this->orderDescriptor,
        //     ],
        //     "options" => [
        //       "paypal" => [
        //         "description" => $this->orderDescription,
        //       ],
        //     ]
        // ]);
        
        // if ($bt_result->success) {
        //     var_dump($bt_result);        
        // }

        
        //     if( !empty( $this->mc_apikey ) && !empty( $this->mc_list_id )){
        //         $mc = new MailChimp( $this->mc_apikey );
                
        //         $errors = $mc->subscribe( $this->mc_list_id, $this->getInput('email'), $this->subscribeAttributes() );
                
        //         array_walk($errors, array($this, 'addError'));
        //     }
        
    }    
    
    // public function formatResponse($errors, $form) {
        
    //     $response = json_decode( parent::formatResponse($errors, $form) );
        
    //     // $response['paypalConfig'] = array(
    //     //         'authorization' => $this->getClientToken()
    //     // );
        
    //     // $response['paypalConfig'] = array(
    //     //         'authorization' => $this->getClientToken()
    //     // );        
        
    //     $response['redirect'] = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=AH35PAGK6PMS6&custom=4';
        
    // }
        
    
    // protected function subscribeAttributes(){
    //     return array( 
    //         'merge_fields' => [ 
    //             'FNAME'=> $this->getInput('fname'), 
    //             'LNAME' => $this->getInput('lname'),
    //             'MARKETING' => $this->getInput('policy_directMarketing') ? 'yes' : 'no',
    //         ] 
    //     );
    // }

    
}