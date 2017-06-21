<?php

/**
 * @package SilverbackStudio Member Private Area
 * @version 1.1
 */
/*
Plugin Name: SilverbackStudio Member Private Area 
Description: Private Area Functions and Automations
Author: Silverback Studio
Version: 1.1
Author URI: http://www.silverbackstudio.it/
Text Domain: svbk-privatearea
*/

namespace Svbk\WP\Plugins\PrivateArea;

use Svbk\WP\Helpers;
use DateTime;
use DateInterval;
use WP_Query;
use Mandrill_Error;
use WP_REST_Request; 
use WP_Error; 
use \Mpdf\Mpdf as PDF;

define('PRIVATEAREA_NOTICE_TRANSIENT_TIMEOUT', 60);
define('PRIVATEAREA_MEMBER_ENDPOINT', 'member/v1' );

add_action('switch_theme', array( ACL::class, 'setup_user_roles') );
add_action('updated_post_meta', array( ACL::class, 'reflectOnUser'), 10, 4 );

function init() {
  load_plugin_textdomain( 'svbk-privatearea', false, dirname( plugin_basename( __FILE__ ) ). '/languages' ); 
}

add_action('plugins_loaded', __NAMESPACE__.'\\init'); 

function set_admin_notices_transient( $handle, $value ){
    
    $value = (array) $value;
    $prev = get_transient( $handle );
    
    if( $prev ){
        set_transient( $handle, array_merge( $prev, $value ), PRIVATEAREA_NOTICE_TRANSIENT_TIMEOUT );
    } else {
        set_transient( $handle,  $value, PRIVATEAREA_NOTICE_TRANSIENT_TIMEOUT );
    }
    
}

function mailchimp_request($email, $data, $update = true){
    
    $mailchimp = new Helpers\Mailing\MailChimp( Helpers\Theme\Theme::conf('mailing', 'mc_apikey') );
    $list_id = Helpers\Theme\Theme::conf('mailing', 'mc_list_id');
    
    if( $update ) {
        $subscriber_hash = $mailchimp->subscriberHash( $email );
        $mailchimp->patch( "lists/$list_id/members/$subscriber_hash", $data );          
    } else {
        $mailchimp->post("lists/$list_id/members", array_merge( [
				'email_address' => $email,
				'status'        => 'subscribed',
		], $data ) );        
    }
    
    return $mailchimp;
}  

add_action( 'svbk_member_type_updated', function( $type, $member ) {
    
    $mailchimp = new Helpers\Mailing\MailChimp( Helpers\Theme\Theme::conf('mailing', 'mc_apikey') );
    $list_id = Helpers\Theme\Theme::conf('mailing', 'mc_list_id');    
    
    $email = $member->meta('user_email');
    
    $subscriber_hash = $mailchimp->subscriberHash( $email );
    $mailchimp->patch( "lists/$list_id/members/$subscriber_hash", [ 'merge_fields' => [ 'MEMBERTYPE' => $type ] ] );    
    
    if( ! $mailchimp->success() ) {
        set_admin_notices_transient( "svbk_mc_user_update_error" , array( $email => $mailchimp->getLastError() ));
    } else {
        set_admin_notices_transient( "svbk_mc_user_update_success" , array( $email ));
    }
    
}, 10, 2 );


add_action( 'admin_notices', __NAMESPACE__.'\\mc_user_update_notices');

function mc_user_update_notices() {
    
    if ( $errors = get_transient( "svbk_mc_user_update_error" ) ) {  ?>
        <div class="notice notice-error "> 
            <?php foreach( $errors as $email => $error ) : ?>
            <p><?php printf( __( 'Mailchimp Update Error for user <b>%s</b>: %s.', 'svbk-privatearea'), $email, $error ); ?></p>   
            <?php endforeach; ?>
            <p><?php _e('Please update values manually or subscribe the user to mailchimp list', 'svbk-privatearea'); ?></p>
        </div><?php
    
        delete_transient("svbk_mc_user_update_error");
    }
    
    if ( $messages = get_transient( "svbk_mc_user_update_success" ) ) {  ?>
        <div class="notice notice-success is-dismissible"> 
            <?php foreach( $messages as $email ) : ?>
            <p><?php printf( __( 'Mailchimp update completed for user %s', 'svbk-privatearea'), $email); ?></p>   
            <?php endforeach; ?>
        </div><?php
    
        delete_transient("svbk_mc_user_update_success");
    } 
    
    if ( $errors = get_transient( "svbk_mc_user_create_error" ) ) {  ?>
        <div class="notice notice-error "> 
            <?php foreach( $errors as $email => $error ) : ?>
            <p><?php printf( __( 'Mailchimp Create Error for user <b>%s</b>: %s.', 'svbk-privatearea'), $email, $error ); ?></p>   
            <?php endforeach; ?>
            <p><?php _e('Please update values manually or subscribe the user to mailchimp list', 'svbk-privatearea'); ?></p>
        </div><?php
    
        delete_transient("svbk_mc_user_create_error");
    }
    
    if ( $emails = get_transient( "svbk_mc_user_create_success" ) ) {  ?>
        <div class="notice notice-success is-dismissible"> 
            <?php foreach( $emails as $email ) : ?>
            <p><?php printf( __( 'Mailchimp create completed for user %s', 'svbk-privatearea'), $email); ?></p>   
            <?php endforeach; ?>
        </div><?php
    
        delete_transient("svbk_mc_user_create_success");
    }     

}  

add_action( 'user_register', __NAMESPACE__.'\\user_register_mc' );

function user_register_mc( $user_id ){
    
    $member = new Member( $user_id );
    $email = $member->meta('user_email');
    
    $mailchimp = new Helpers\Mailing\MailChimp( Helpers\Theme\Theme::conf('mailing', 'mc_apikey') );
    $list_id = Helpers\Theme\Theme::conf('mailing', 'mc_list_id');    
    
    $errors = $mailchimp->subscribe($list_id, $email, [
			'merge_fields' => [ 
			    'FNAME' => $member->meta('firstname'), 
			    'LNAME' => $member->meta('lastname'),
			    'MARKETING' => 'yes',
			    'MEMBERTYPE' => $member->get_type() ?: 'subscriber',
			]
	]); 
	
    if( !empty($errors) ) {
        set_admin_notices_transient( "svbk_mc_user_create_error" , array( $email => $mailchimp->getLastError() ));
    } else {
        set_admin_notices_transient( "svbk_mc_user_create_success" , array( $email ));
    }	
    
}

add_action( 'user_register', __NAMESPACE__.'\\create_profile', 9 );

function create_profile( $user_id, $post_data = array() ){
    
    $user = get_userdata( $user_id );
    
    if( empty( array_intersect( $user->roles, array_keys( ACL::available_roles() ) ) ) ){
        return;
    }
    
    $member = new Member( $user );
    $profile = $member->profile();
    
    if( empty( $profile ) ){
        $profile = Profile::create( 
            array_merge(
                array( 
                    'post_title' => sprintf ( __( 'Business of %s', 'svbk-privatearea'), $member->meta('user_email') ),
                    'meta_input' => array(
                        'billing_first_name' => $member->meta( 'first_name' ),
                        'billing_last_name' => $member->meta( 'last_name' ),
                        ),
                    ),
                $post_data
            )
        );
        $member->set_profile( $profile );
        
        $paymentDate = new DateTime('NOW');
        $profile->set_subscribe_date( $paymentDate );  
        
        $paymentDate->add( new DateInterval( Helpers\Theme\Theme::conf('subscription', 'trial') ) );
        $profile->set_expire( $paymentDate );
    }
    
    //Dirty fix to prevent ACF to reset the field after loading
    add_filter('acf/update_value/key=field_5903573a0e98b', function($value, $post_id, $field) use ($profile) { 
        if( $value ) {
            //delete auto created post
            wp_delete_post( $profile->id() );
            unset( $profile );
        } else {
            $value = $profile->id(); 
        }
        
        return $value;
    } , 10, 3);
    
    return $profile;
}

add_action( 'profile_update', __NAMESPACE__.'\\user_update_mc', 10, 2 );

function user_update_mc( $user_id, $old_user_data ){
    
    $mailchimp = new Helpers\Mailing\MailChimp( Helpers\Theme\Theme::conf('mailing', 'mc_apikey') );
    $list_id = Helpers\Theme\Theme::conf('mailing', 'mc_list_id');      
    
    $member = new Member( $user_id );
    
    $old_email = $old_user_data->data->user_email;
    $email = $member->meta('user_email');
    
    if( strcasecmp($old_email, $email) !== 0 ){
        $subscriber_hash = $mailchimp->subscriberHash( $old_email );
    } else {
        $subscriber_hash = $mailchimp->subscriberHash( $email );
    }

    $resp = $mailchimp->put("lists/$list_id/members/$subscriber_hash", [
			'email_address' => $email,
			'status_if_new' => 'subscribed',
			'merge_fields' => [ 
			    'EMAIL' => $email,
			    'FNAME' => $member->meta('first_name'), 
			    'LNAME' => $member->meta('last_name'),
			    'MARKETING' => 'yes',
			    'MEMBERTYPE' => $member->get_type() ?: 'subscriber',
			],
            'language' => substr(get_locale(), 0, 2),	
	]); 
	
    if( ! $mailchimp->success() ) {
        set_admin_notices_transient( "svbk_mc_user_update_error" , array( $email => $mailchimp->getLastError() ));
    } else {
        set_admin_notices_transient( "svbk_mc_user_update_success" , array( $email ));
    }	
    
}

add_action( 'rest_api_init', function () {
  register_rest_route( PRIVATEAREA_MEMBER_ENDPOINT, '/payment/webhook', array(
    'methods' => 'POST',
    'callback' => __NAMESPACE__.'\\payment_webhook',
  ) );
  
  register_rest_route( PRIVATEAREA_MEMBER_ENDPOINT, '/payment/ipn', array(
    'methods' => 'POST',
    'callback' => __NAMESPACE__.'\\payment_ipn',
  ) );  
} );


function payment_webhook( WP_REST_Request $request ) {
 
        $paypal = new Helpers\Payment\PayPal( Helpers\Theme\Theme::conf('paypal') );
        
        $logger = new Helpers\Log\Email;
        $logger->defaultSubject = 'PayPal Webhook'; 
        $paypal->setLogger( $logger );
        
        $result = $paypal->verifyWebhook( $request, Helpers\Theme\Theme::conf('paypal', 'webhook_id') );
        
        if( is_wp_error($result) ){
            return $result;
        }
 
        if( true === $result ){
            //@TODO: register payment
        }
        
}

function payment_ipn( WP_REST_Request $request ){

    $paypal = new Helpers\Payment\PayPal( Helpers\Theme\Theme::conf('paypal') );

    $logger = new Helpers\Log\Email;
    $logger->defaultSubject = 'PayPal IPN'; 
    $paypal->setLogger( $logger );

    $result = $paypal->verifyIPN( $request );

    if ( is_wp_error ( $result ) ) {
        return $result;
    }
    
    // The IPN is verified, process it:
    // check whether the payment_status is Completed
    // check that txn_id has not been previously processed
    // check that receiver_email is your Primary PayPal email
    // check that payment_amount/payment_currency are correct
    // process the notification
    // assign posted variables to local variables
    
    if( strcmp( $request->get_param('payment_status'), 'Completed') !== 0 ) {
        return new WP_Error( 'ipn_not_complete_payment', 'Payment not completed! -> ' . $request->get_param('payment_status' ) , array( 'status' => 200 ) );
    }
    
    if ( strcmp( $request->get_param('receiver_email'), Helpers\Theme\Theme::conf('paypal', 'receiver_email') )  !== 0 ){
        return new WP_Error( 'ipn_wrong_receiver_email', 'Wrong receiver email: ' . $request->get_param('receiver_email') , array( 'status' => 200 ) );        
    }
    
    if ( floatval( $request->get_param('mc_gross') ) !== floatval( Helpers\Theme\Theme::conf('subscription', 'price') ) ){
        return new WP_Error( 'ipn_price_mismatch', 'Price mismatch: ' . $request->get_param('mc_gross') , array( 'status' => 200 ) );
    }     
    
    if ( strcmp( $request->get_param('mc_currency'), Helpers\Theme\Theme::conf('paypal', 'currency') )  !== 0 ){
        return new WP_Error( 'ipn_currency_mismatch', 'Currency mismatch: ' . $request->get_param('mc_currency') , array( 'status' => 200 ) );
    }     
    
    if ( strcmp( $request->get_param('item_number'), Helpers\Theme\Theme::conf( 'subscription', 'paypal_item' ) )  !== 0 ){
        return new WP_Error( 'ipn_wrong_item_number', 'Wrong item number: ' .  $request->get_param('item_number') , array( 'status' => 200 ) );
    }           
    
    $user = get_user_by('ID', (int) $request->get_param('custom') );

    if( !$user ){
      $user = get_user_by('email', $request->get_param('payer_email') );
    }
    
    if( !$user ){
      $logger->critical( 'PAYPAL IPN: Invalid Member Reference: [' . $request->get_param('custom') . '] [' . $request->get_param('payer_email') . ']'  );
      return new WP_Error( 'invalid_member_reference', 'Invalid Member Reference'  , array( 'status' => 200 ) );
    }
    
    $member = new Member( $user );
    $profile = $member->profile();
  
    if( empty( $profile ) ){
        $profile = Profile::create( array( 'post_title' => sprintf ( __( 'Business of %s', 'svbk-privatearea'), $member->meta('user_email') ) )  );
        $member->set_profile( $profile );
    }
  
    $profile->set_type( ACL::ROLE_MEMBER );
    $member->set_type( ACL::ROLE_MEMBER );
    
    $paymentDate = $paypal->parseDate( $request->get_param('payment_date') );

    if( !$paymentDate ) {
        $paymentDate = new DateTime('NOW');
    }
    
    if( $profile->subscription_date() === null ){
        $profile->set_subscribe_date( $paymentDate );
    }

    $profile->subscription_extend( $paymentDate, new DateInterval( Helpers\Theme\Theme::conf('subscription', 'duration') ) );

    $invoiceId = intval( get_option('svbk_privatearea_last_invoice_id') ) + 1;
    update_option('svbk_privatearea_last_invoice_id', $invoiceId);

    if( function_exists('add_row') ) {
        
        add_row( 'payments', array(
            'transaction' => $request->get_param('txn_id'),
            'date' => $paymentDate->format(Profile::DATE_FORMAT_SAVE),
            'payed_amount' => $request->get_param('mc_gross'),
            'invoice_id' => $invoiceId,
        ), $profile->id() );
        
    } else {
        add_post_meta( $profile->id(), 'svbk_last_transaction_id', $request->get_param('txn_id'), true );
        add_post_meta( $profile->id(), 'svbk_last_payer_id', $request->get_param('payer_id'), true );
        add_post_meta( $profile->id(), 'svbk_last_payer_email', $request->get_param('payer_email'), true );
    }

    try {
    
        $mandrill = new Helpers\Mailing\Mandrill( Helpers\Theme\Theme::conf('mailing', 'md_apikey') );
        $member = new Member( $user );
    
        $type = $member->get_type();
    
        $results = $mandrill->messages->sendTemplate(Helpers\Theme\Theme::conf('mailing', 'template_new_' . ACL::ROLE_MEMBER ), array(), array_merge_recursive(
            Helpers\Mailing\Mandrill::$messageDefaults,
            array(
                'to' => array(
                    array(
                    'email' => $member->meta('user_email'),
                    'name' => $member->meta('display_name'),
                    'type' => 'to'
                    )
                ),
                'from_email' => Helpers\Theme\Theme::conf('mailing', 'from_email'),
                'from_name' => Helpers\Theme\Theme::conf('mailing', 'from_name'),                    
                //'subject' => sprintf ( __('Your account details at %s', 'svbk-privatearea'), get_bloginfo( 'name' ) ),
                'global_merge_vars' => Helpers\Mailing\Mandrill::castMergeTags(
                    array(
                        'FNAME' => $member->meta('first_name'),
                        'LNAME' => $member->meta('last_name'),
                        'HOME_URL' => home_url( '/' ),
                        'PRIVATEAREA_URL' => get_permalink( get_theme_mod('private_area_home') ),
                        'SET_PASSWORD_URL' => wp_lostpassword_url( get_permalink( get_theme_mod('private_area_home') ) ),
                    )
                ),
                'metadata' => array(
                    'website' => home_url( '/' )
                ),
                'merge' =>true,
                'tags' => array('wp-new-user'),
            )
        ) );
        
        if( !is_array($results) || !isset($results[0]['status']) ){
            throw new Mandrill_Error( __('The requesto to our mail server failed, please try again later or contact the site owner.', 'svbk-helpers') );
        } 
        
        $errors = $mandrill->getResponseErrors($results);    
        
        foreach($errors as $error){
            $logger->error($error);
        }            
    
    } catch(Mandrill_Error $e) {
        $logger->critical( $e->getMessage() );
    }       
         

    return;

}

add_filter( 'login_url', __NAMESPACE__.'\\login_page', 10, 3 );

function login_page( $login_url, $redirect, $force_reauth ) {
    $login_page = get_permalink( get_theme_mod('private_area_home') );
    
    if( $login_page ) {
        $login_url = add_query_arg( 'redirect_to', $redirect, $login_page );
    }
    return $login_url;
}


function customizer( $wp_customize ){
	//Private Area
	$wp_customize->add_section( 'private-area', array(
	  'title' => __( 'Private Area', 'propertymanagers' ),
	  'description' => __( 'Private Area', 'propertymanagers' ),
	  'priority' => 180,
	) );
	
	$wp_customize->add_setting( 'private_area_home', array(
	  'default' => false,
	));

	$wp_customize->add_control( 'private_area_home', array(
	  'label' => __( 'Home Page', 'propertymanagers' ),
	  'description' => __( 'Select the main Private Area page', 'propertymanagers' ),
	  'section' => 'private-area',
	  'type' => 'number',
	));	
	
	$wp_customize->add_setting( 'private_area_profile', array(
	  'default' => false,
	));

	$wp_customize->add_control( 'private_area_profile', array(
	  'label' => __( 'Profile Page', 'propertymanagers' ),
	  'description' => __( 'Select the Private Area profile page', 'propertymanagers' ),
	  'section' => 'private-area',
	  'type' => 'number',
	));		

}
add_action( 'customize_register', __NAMESPACE__.'\\customizer' );

function show_admin_bar(){
	return current_user_can( 'edit_posts' );
}

add_filter( 'show_admin_bar' , __NAMESPACE__.'\\show_admin_bar');

function enable_acf_forms(){
    
    if( is_page( get_theme_mod( 'private_area_profile' ) ) ) {
       acf_form_head(); 
       add_action('wp_enqueue_scripts', __NAMESPACE__.'\\dequeue_google_maps');
    }
    
}

add_action( 'wp', __NAMESPACE__.'\\enable_acf_forms' );

function dequeue_google_maps(){
    wp_dequeue_script('googlemaps');
}

if ( ! wp_next_scheduled( 'svbk_privatearea_expiration_check' ) ) {
  wp_schedule_event( time(), 'hourly', 'svbk_privatearea_expiration_check' );
}

add_action( 'svbk_privatearea_expiration_check', __NAMESPACE__.'\\check_expiration' );

function check_expiration() {
        
        $reference = new DateTime('NOW');

        send_expiration_notifications(
            $reference, 
            Helpers\Theme\Theme::conf('mailing', 'template_member_expired'), 
            'subscription_expired_notification_sent',
            array('tags' => array( 'wp-member-expire' ) ) 
        );
        
        $reference->add( new DateInterval( 'P15D' ) );
    
        send_expiration_notifications(
            $reference, 
            Helpers\Theme\Theme::conf('mailing', 'template_member_expiring'), 
            'subscription_expiring_notification_sent',
            array('tags' => array( 'wp-member-expiring' ) ) 
        ); 
        

}

function send_expiration_notifications( DateTime $reference, $mcTemplate, $notField, $mcArgs = array() ) {

    $args = array(
    	'post_type'  => Profile::POST_TYPE,
    	'posts_per_page' => -1,
    	'meta_query' => array(
    	    'operator' => 'AND',
    	    array(
    			'key'     => $notField,
    			'value'   => '1',
    			'compare' => '!=',
    		),
    		array(
    			'key'     => Profile::EXPIRE_FIELD,
    			'value'   => '',
    			'compare' => '!=',
    		), 
    		array(
    			'key'     => Profile::EXPIRE_FIELD,
    			'value'   => $reference->format( Profile::DATE_FORMAT_SAVE ),
    			'compare' => '<',
    			'type' => 'NUMERIC'
    		),
    	),
    );
    
    $profile_query = new WP_Query( $args );    
    
    if ( $profile_query->have_posts() ) {
    	// The 2nd Loop
    	while ( $profile_query->have_posts() ) {
    		$profile_query->next_post();

            $profile = new Profile( $profile_query->post->ID );

    	    $users = get_users(
                array(
                    'meta_key' => Member::PROFILE_FIELD,
                    'meta_value' => $profile->id(),
                )
            );
            
            $recipients = array();
            $merge_tags = array();
            
            foreach( $users as $user ) {
                
                $recipients[] = array(
                    'email' => $user->user_email,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'type' => 'to'
                );
                
                $merge_tags[] = array(
                    'rcpt' => $user->user_email,
                    'vars' => Helpers\Mailing\Mandrill::castMergeTags(
                        array(
                            'FNAME' => $user->first_name,
                            'LNAME' => $user->last_name,
                        )
                    )
                );
            }
    	
    	    $emailArgs =  array_merge_recursive(
    	        array(
                    'to' => $recipients,
                    'merge_vars' => $merge_tags,
                    'global_merge_vars' => array(
                        'COMPANY_NAME' => $profile->meta('company_name'),
                        'EXPIRE_DATE' => $profile->subscription_expires()->format( 'd/m/Y' ),
                        'RENEW_URL' => Helpers\Payment\PayPal::buttonUrl( Helpers\Theme\Theme::conf('paypal', 'button_id'), array( 'custom' => $user->ID ) )
                    ),
                ),
                $mcArgs
            );

            $errors = send_email( $mcTemplate, $emailArgs );
            
            if( empty($errors) ) {
                update_post_meta( $profile->id(), $notField, '1' );
            }            
            
    	}
    }    

}

function send_email( $template, $args ){
    
        $logger = new Helpers\Log\Email; 
        $logger->defaultSubject = 'Mandrill Send Log';
     
        $defaultArgs = array(
            'global_merge_vars' => array(
                    'HOME_URL' => home_url( '/' ),
                    'PRIVATEAREA_URL' => get_permalink( get_theme_mod('private_area_home') ),
            ),
            'metadata' => array(
                'website' => home_url( '/' )
            ),
            'merge' => true,        
        );
        
        if( Helpers\Theme\Theme::conf('debug_email') ) {
            $defaultArgs['bcc_address'] = Helpers\Theme\Theme::conf('debug_email');
        }
        
        if( Helpers\Theme\Theme::conf('mailing', 'from_email') ) {
            $defaultArgs['from_email'] = Helpers\Theme\Theme::conf('mailing', 'from_email');
        }    
        
        if( Helpers\Theme\Theme::conf('mailing', 'from_name') ) {
            $defaultArgs['from_name'] = Helpers\Theme\Theme::conf('mailing', 'from_name');
        }            
     
        $emailArgs = array_replace_recursive(
            Helpers\Mailing\Mandrill::$messageDefaults,
            $defaultArgs,
            $args
        );
        
        if( isset( $emailArgs['global_merge_vars'] ) && is_array( $emailArgs['global_merge_vars'] ) ){
            $emailArgs['global_merge_vars'] = Helpers\Mailing\Mandrill::castMergeTags( $emailArgs['global_merge_vars'] );
        }
     
        try {

            $mandrill = new Helpers\Mailing\Mandrill( Helpers\Theme\Theme::conf('mailing', 'md_apikey') );

            $results = $mandrill->messages->sendTemplate($template, array(), $emailArgs);

            if( !is_array($results) || !isset($results[0]['status']) ){
                throw new Mandrill_Error( __('The requesto to our mail server failed, please try again later or contact the site owner.', 'svbk-helpers') );
            } 
            
            $errors = $mandrill->getResponseErrors($results);    
        
            foreach($errors as $error){
                $logger->error($error);
            }      
        
        } catch(Mandrill_Error $e) {
            $logger->critical( $e->getMessage() );
            return array( $e->getMessage() ); 
        }           
    
        return $errors;
    
}

function acf_profile_form_labels( $field ) {
    
    switch( $field['key'] ){
        case '_post_title':
            $field['label'] = __( 'Business Name', 'propertymanagers' );
            break;
        case '_post_content':
            $field['label'] = __( 'Description', 'propertymanagers' );
            break;
    }

    return $field;
}

add_filter('acf/prepare_field/key=_post_title', __NAMESPACE__.'\\acf_profile_form_labels');
add_filter('acf/prepare_field/key=_post_content', __NAMESPACE__.'\\acf_profile_form_labels');

if( ! is_admin() ){
    add_filter('acf/load_value/key=field_59035753xt98d', __NAMESPACE__.'\\acf_member_email_load', 10, 3);
    add_filter('acf/update_value/key=field_59035753xt98d', __NAMESPACE__.'\\acf_member_email_update', 10, 3);
    add_filter('acf/validate_value/key=field_59035753xt98d', __NAMESPACE__.'\\acf_member_email_validate', 10, 4);
    add_filter('acf/prepare_field/key=field_59035753xt98d', __NAMESPACE__.'\\acf_member_email_field');
}

function acf_member_email_load( $value, $post_id, $field ) {

    $user_id = (int)str_replace('user_', '', $post_id);
    $current_user = wp_get_current_user();
    
    if ( isset( $_GET[ 'newuseremail' ] ) && $current_user->ID && ( $user_id === $current_user->ID ) ) {
	    $new_email = get_user_meta( $current_user->ID, '_new_email', true );
	    if ( $new_email && hash_equals( $new_email[ 'hash' ], $_GET[ 'newuseremail' ] ) ) {
    		$user = new \stdClass;
    		$user->ID = $current_user->ID;
    		$user->user_email = esc_html( trim( $new_email[ 'newemail' ] ) );
    		wp_update_user( $user );
    		delete_user_meta( $current_user->ID, '_new_email' );
	    }  
    } 
    
    $userdata = get_user_by('ID', $user_id);
    
    $value = $userdata->user_email;
    
    return $value;
}

function acf_member_email_update( $value, $post_id, $field ) {

    $user_id = (int)str_replace('user_', '', $post_id);
    $userdata = get_user_by('ID', $user_id);

    if( $userdata->user_email === $value ){
        return '';
    }

    $new_user_email = get_user_meta($user_id, '_new_email', true );

    if( $new_user_email && ($new_user_email['newemail'] === $value) ) {
        return '';
    }

	$hash = md5( $value . time() . mt_rand() );
	$new_user_email = array(
		'hash' => $hash,
		'newemail' => $value
	);
	update_user_meta($user_id, '_new_email', $new_user_email );
	
	$changeUrl = get_permalink( get_theme_mod('private_area_profile') );
	$changeUrl = add_query_arg( 'newuseremail', $hash, $changeUrl );
	
	send_email( Helpers\Theme\Theme::conf('mailing', 'template_member_emailreset') , array(
            'to' => array( 
                array(
                    'email' => $value,
                    'name' => $userdata->first_name . ' ' . $userdata->last_name,
                    'type' => 'to'
                )
            ),
            'subject' => sprintf ( __('%s - E-mail change request', 'svbk-privatearea'), get_bloginfo( 'name' ) ),
            'global_merge_vars' => array(
                'FNAME' => $userdata->first_name,
                'LNAME' => $userdata->last_name,
                'NEW_EMAIL' => $value,
                'EMAIL_CONFIRM_URL' => esc_url( $changeUrl ),
            ),	
            'tags' => array( 'wp-member-newemail' )
	    )
	);
    
    return '';
}

function acf_member_email_validate( $valid, $value, $field, $input ) {

    $userdata = wp_get_current_user();

    if( ($valid !== true) || ( $userdata->user_email === $value ) ){
        return $valid;
    }

    if( ! is_email( $value ) ){
        return __('This email address is invalid', 'svbk-privatearea');
    }
    
    if( email_exists( $value ) ){
        return __('This email address already exists', 'svbk-privatearea');
    }

    return $valid;
}

function acf_member_email_field( $field ) {

    $userdata = wp_get_current_user();
    $new_email = get_user_meta( $userdata->ID, '_new_email', true);
    
    if( $new_email ){
        $field['instructions'] = sprintf( __('Email change request pending. We sent an email to %s, please click on the link inside to confirm.', 'svbk-privatearea'), $new_email[ 'newemail' ] );
    }    

    return $field;
}

function pdf_apply_template(PDF $mpdf, $template){

	$css_path = file_exists( get_theme_file_path( $template . '.css' ) ) ? get_theme_file_path($template . '.css') : ( plugin_dir_path( __FILE__ ) . "templates/{$template}.css" );
	
    if( $css_path ) {
        $stylesheet = file_get_contents( $css_path );
        $mpdf->WriteHTML( $stylesheet, 1 ); 
    }

    ob_start();

    if( ! locate_template("pdf-templates/{$template}.php", true, false) ) {
        load_template( plugin_dir_path( __FILE__ ) . "templates/{$template}.php", false);
    } 
 
    $html = ob_get_contents();
    ob_end_clean();
    
    $mpdf->WriteHTML($html, 0);
    return $mpdf;
}


function download_page_trigger()
{

	global $post;

    if( ! is_user_logged_in() || empty($_GET['pdf_download']) || ! is_page( get_theme_mod( 'private_area_profile' ) ) ) {
        return;
    }

    $member = Member::current();
	$profile = $member->profile();
	
	if( ! $profile ){
	    wp_die( __('Your account profile not exists or is not been approved yet.', 'svbk-privatearea') );
	}

	$post = get_post( $profile->id(), 'OBJECT', 'display' );
	setup_postdata( $post );
	
    switch( $_GET['pdf_download'] ){
        case 'invoice':
        	$pdf = new PDF();
    	    $pdf->SetDisplayMode('fullpage');
        	$pdf->setAutoTopMargin = 'pad';
            $pdf->orig_tMargin = 30;
        	
        	if( ! $profile->meta('invoice_number') ) {
        	    wp_die( __('Your invoice is\'t ready yet. Please try again later', 'svbk-privatearea') );
        	}   
        	
            $pdf = pdf_apply_template($pdf, 'invoice' );
            $pdf->SetTitle( sprintf( __('%s - Invoice N. %s - Date %s ', 'svbk-privatearea'), get_bloginfo('name'), $profile->meta('invoice_number'), $profile->subscription_date()->format('d/m/Y')) );
            
            break;
        case 'certificate':
        	$pdf = new PDF( array('orientation' => 'L', 'margin_top'=>30) );
        	$pdf->SetDisplayMode('fullpage');
        	$pdf->AddPage('L');
    
        	if( ! $profile->type() === ACL::ROLE_MEMBER ) {
        	    wp_die( __('TThe certificate is available only to full members.', 'svbk-privatearea') );
        	}
        	
            $pdf = pdf_apply_template($pdf, 'certificate' );
        
            $pdf->SetTitle( sprintf( __('%s - Certificate %s ', 'svbk-privatearea'), get_bloginfo('name'), date('Y'), $profile->subscription_date()->format('d/m/Y')) );

            break;         
        default:
            return;
    }
    
    $pdf->SetAuthor( get_bloginfo('contact_company_name') );
    $pdf->Output();
    
    exit;
}
add_action( 'template_redirect', __NAMESPACE__.'\\download_page_trigger' );


function replace_profile_thumbnail($meta_value, $object_id, $meta_key, $single){
    
    if( ( $meta_key === '_thumbnail_id' ) && ( Profile::POST_TYPE === get_post_type($object_id) ) ) {
        
        $profile = new Profile( $object_id );
        $attachment_id = $profile->meta('company_logo');
        
        if( $attachment_id ){
            $meta_value = $attachment_id;
        }
    }

    return $meta_value;
}

add_filter( 'get_post_metadata', __NAMESPACE__.'\\replace_profile_thumbnail', 10, 4 );

function notices(){
    
    $member = Member::current();
    $profile = $member->profile();    
    
    if( $profile ) {
    	$payment_button =  Helpers\Payment\PayPal::buttonUrl( 
    	    Helpers\Theme\Theme::conf('paypal', 'button_id'), 
    	    array( 
    	        'custom' => $member->id(),
    	    )
    	);
    }    
    
	if ( $profile && $profile->is_subscription_expired( 'P15D' ) ): ?>
	<div class="warning notification">
		<div class="heading"><?php printf( __('Warning %s', 'svbk-privatearea'), $member->meta( 'first_name' )) ?></div>
		<p class="intro" >Mancano solo <?php echo $profile->subscription_expire_eta( '%a' ); ?> giorni alla scadenza della tua iscrizione.</p>
		<?php if( !empty($payment_button) && ACL::ROLE_MEMBER === $profile->type() ): ?>
		<p class="message" >Rinnova adesso e assicurati una altro anno da Property Manager!</p>
		<a class="button" href="<?php echo esc_url($payment_button); ?>" target="_blank" >Rinnova Ora</a>
		<?php elseif ( !empty($payment_button) ) : ?>
		<p class="message" >Associati adesso e assicurati un anno da Property Manager!</p>
		<a class="button" href="<?php echo esc_url($payment_button); ?>" target="_blank" >Associati Ora</a>
		<?php endif; ?>
	</div>
	<?php endif; ?>
	<?php
	if ( ( ! $profile || ( $profile->completed() < 1 ) ) && ! is_page( get_theme_mod( 'private_area_profile' ) ) ): ?>
	<div class="notice notification">
		<div class="heading"><?php printf( __('Warning %s', 'svbk-privatearea'), $member->meta( 'first_name' )) ?></div>
		<?php if( ! $profile ) : ?>
		<p class="intro" >Non hai ancora creato il tuo profilo</p>
		<p class="message" >Per poter usufruire di tutte le funzionalità completa i dati del tuo profilo</p>
		<a class="button" href="<?php echo get_permalink( get_theme_mod( 'private_area_profile' ) ); ?>" >Crea il tuo profilo</a>		
		<?php else: ?>
		<p class="intro" >Il tuo account è completo al <?php echo ceil( $profile->completed() * 100) ; ?>%</p>
		<p class="message" >Per poter usufruire di tutte le funzionalità completa i dati del tuo profilo</p>
		<a class="button" href="<?php echo get_permalink( get_theme_mod( 'private_area_profile' ) ); ?>" >Completa il tuo profilo</a>		
		<?php endif; ?>
	</div>
	<?php endif; ?>
<?php
}

add_action( 'privatearea_notices', __NAMESPACE__.'\\notices' );

function manage_user_columns( $column ) {
    $column['profile'] = __('Member Profile', 'svbk-privatearea');
    return $column;
}
add_filter( 'manage_users_columns', __NAMESPACE__.'\\manage_user_columns' );

function user_columns_row( $val, $column_name, $user_id ) {
    switch ($column_name) {
        case 'profile' :
            $profile_id = get_the_author_meta( Member::PROFILE_FIELD, $user_id );
            if( $profile_id && ( $edit_link = get_edit_post_link($profile_id) ) ) {
                return '<a href="' . $edit_link . '">' . ( get_the_title($profile_id) ?: __('No title', 'svbk-privatearea') ). '</a>';
            }
            break;
        default:
    }
    return $val;
}
add_filter( 'manage_users_custom_column', __NAMESPACE__.'\\user_columns_row', 10, 3 );

/**
 * Load Global Pluggables
 */
require 'pluggables.php';
require 'acf.php';