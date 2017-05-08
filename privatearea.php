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

use Svbk\WP\Helpers;
use Svbk\WP\Plugins\PrivateArea;

define('PRIVATEAREA_NOTICE_TRANSIENT_TIMEOUT', 60);
define('PRIVATEAREA_MEMBER_ENDPOINT', 'member/v1' );

add_action('switch_theme', array( PrivateArea\ACL::class, 'setup_user_roles') );
add_action('updated_post_meta', array( PrivateArea\ACL::class, 'reflectOnUser'), 10, 4 );

function svbk_privatearea_init() {
  load_plugin_textdomain( 'svbk-privatearea', false, dirname( plugin_basename( __FILE__ ) ). '/languages' ); 
}

add_action('plugins_loaded', 'svbk_privatearea_init'); 

function set_admin_notices_transient( $handle, $value ){
    
    $value = (array) $value;
    $prev = get_transient( $handle );
    
    if( $prev ){
        set_transient( $handle, array_merge( $prev, $value ), PRIVATEAREA_NOTICE_TRANSIENT_TIMEOUT );
    } else {
        set_transient( $handle, $value, PRIVATEAREA_NOTICE_TRANSIENT_TIMEOUT );
    }
    
}

function svbk_mailchimp_request($email, $data, $update = true){
    
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


add_action( 'admin_notices', 'svbk_mc_user_update_notices');

function svbk_mc_user_update_notices() {
    
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
    
    if ( $messages = get_transient( "svbk_mc_user_create_success" ) ) {  ?>
        <div class="notice notice-success is-dismissible"> 
            <?php foreach( $messages as $email ) : ?>
            <p><?php printf( __( 'Mailchimp create completed for user %s', 'svbk-privatearea'), $email); ?></p>   
            <?php endforeach; ?>
        </div><?php
    
        delete_transient("svbk_mc_user_create_success");
    }     

}  

add_action( 'user_register', 'svbk_user_register_mc' );

function svbk_user_register_mc( $user_id ){
    
    $member = new PrivateArea\Member( $user_id );
    $email = $member->meta('user_email');
    
    $mailchimp = new Helpers\Mailing\MailChimp( Helpers\Theme\Theme::conf('mailing', 'mc_apikey') );
    $list_id = Helpers\Theme\Theme::conf('mailing', 'mc_list_id');    
    
    $mailchimp->post("lists/$list_id/members", [
			'email_address' => $email,
			'status'        => 'subscribed',
			'merge_fields' => [ 
			    'FNAME' => $member->meta('firstname'), 
			    'LNAME' => $member->meta('lastname'),
			    'MARKETING' => 'yes',
			    'MEMBERTYPE' => $member->get_type() ?: 'subscriber',
			],
            'ip_signup'     => $_SERVER['REMOTE_ADDR'],
            'ip_opt'        => $_SERVER['REMOTE_ADDR'],
            'language'      => substr(get_locale(), 0, 2),	
	]); 
	
    if( ! $mailchimp->success() ) {
        set_admin_notices_transient( "svbk_mc_user_create_error" , array( $email => $mailchimp->getLastError() ));
    } else {
        set_admin_notices_transient( "svbk_mc_user_create_success" , array( $email ));
    }	
    
}

//add_action( 'user_register', 'svbk_user_register_create_profile', 9 );

function svbk_user_register_create_profile( $user_id, $profile_meta = array() ){
    
    $user = get_userdata( $user_id );
    
    if( empty( array_intersect( $user->roles, array_keys( PrivateArea\ACL::available_roles() ) ) ) ){
        return;
    }
    
    $member = new PrivateArea\Member( $user );
    $profile = $member->profile();
    
    if( empty( $profile ) ){
        $profile = PrivateArea\Profile::create( 
            array_merge(
                array( 
                    'post_title' => sprintf ( __( 'Business of %s', 'svbk-privatearea'), $member->meta('user_email') ) 
                    ),
                $profile_meta
            )
        );
        $member->set_profile( $profile );
    }
    
    return $profile;
}

add_action( 'profile_update', 'svbk_ser_update_mc', 10, 2 );

function svbk_user_update_mc( $user_id, $old_user_data ){
    
    $mailchimp = new Helpers\Mailing\MailChimp( Helpers\Theme\Theme::conf('mailing', 'mc_apikey') );
    $list_id = Helpers\Theme\Theme::conf('mailing', 'mc_list_id');      
    
    $member = new PrivateArea\Member( $user_id );
    
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
    'callback' => 'svbk_register_new_payment_webhook',
  ) );
  
  register_rest_route( PRIVATEAREA_MEMBER_ENDPOINT, '/payment/ipn', array(
    'methods' => 'POST',
    'callback' => 'svbk_register_new_payment_ipn',
  ) );  
} );


function svbk_register_new_payment_webhook( WP_REST_Request $request ) {
 
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

function svbk_register_new_payment_ipn( WP_REST_Request $request ){

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
    
    if ( floatval( $request->get_param('mc_gross') ) !== floatval( Helpers\Theme\Theme::conf('subscription_price') ) ){
        return new WP_Error( 'ipn_price_mismatch', 'Price mismatch: ' . $request->get_param('mc_gross') , array( 'status' => 200 ) );
    }     
    
    if ( strcmp( $request->get_param('mc_currency'), Helpers\Theme\Theme::conf('paypal', 'currency') )  !== 0 ){
        return new WP_Error( 'ipn_currency_mismatch', 'Currency mismatch: ' . $request->get_param('mc_currency') , array( 'status' => 200 ) );
    }     
    
    if ( strcmp( $request->get_param('item_number'), Helpers\Theme\Theme::conf( 'subscription_item' ) )  !== 0 ){
        return new WP_Error( 'ipn_wrong_item_number', 'Wrong item number: ' .  $request->get_param('item_number') , array( 'status' => 200 ) );
    }           
    
    $user = get_user_by('ID', (int) $request->get_param('custom') );

    if( !$user ){
      $user = get_user_by('email', $request->get_param('payer_email') );
    }
    
    if( !$user ){
      return new WP_Error( 'invalid_member_reference', 'Invalid Member Reference'  , array( 'status' => 200 ) );
    }
    
    $member = new PrivateArea\Member( $user );
    $profile = $member->profile();
  
    if( empty( $profile ) ){
        $profile = PrivateArea\Profile::create( array( 'post_title' => sprintf ( __( 'Business of %s', 'svbk-privatearea'), $member->meta('user_email') ) )  );
        $member->set_profile( $profile );
    }
  
    $profile->set_type( PrivateArea\ACL::ROLE_MEMBER );
    $member->set_type( PrivateArea\ACL::ROLE_MEMBER );

    add_post_meta( $profile->id(), 'svbk_last_transaction_id', $request->get_param('txn_id'), true );
    add_post_meta( $profile->id(), 'svbk_last_payer_id', $request->get_param('payer_id'), true );
    add_post_meta( $profile->id(), 'svbk_last_payer_email', $request->get_param('payer_email'), true );

    return;

}

if ( !function_exists('wp_new_user_notification') ) {
    
    function wp_new_user_notification( $user_id, $deprecated = null, $notify = '' ) {
        
        if ( $deprecated !== null ) {
            _deprecated_argument( __FUNCTION__, '4.3.1' );
        }
     
        global $wpdb, $wp_hasher;
        $user = get_userdata( $user_id );
     
        // The blogname option is escaped with esc_html on the way into the database in sanitize_option
        // we want to reverse this for the plain text arena of emails.
        $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
     
        if ( 'user' !== $notify ) {
            $switched_locale = switch_to_locale( get_locale() );
            $message  = sprintf( __( 'New user registration on your site %s:' ), $blogname ) . "\r\n\r\n";
            $message .= sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
            $message .= sprintf( __( 'Email: %s' ), $user->user_email ) . "\r\n";
     
            @wp_mail( get_option( 'admin_email' ), sprintf( __( '[%s] New User Registration' ), $blogname ), $message );
     
            if ( $switched_locale ) {
                restore_previous_locale();
            }
        }
     
        // `$deprecated was pre-4.3 `$plaintext_pass`. An empty `$plaintext_pass` didn't sent a user notification.
        if ( 'admin' === $notify || ( empty( $deprecated ) && empty( $notify ) ) ) {
            return;
        }
     
        // Generate something random for a password reset key.
        $key = wp_generate_password( 20, false );
     
        /** This action is documented in wp-login.php */
        do_action( 'retrieve_password_key', $user->user_login, $key );
     
        // Now insert the key, hashed, into the DB.
        if ( empty( $wp_hasher ) ) {
            $wp_hasher = new PasswordHash( 8, true );
        }
        $hashed = time() . ':' . $wp_hasher->HashPassword( $key );
        $wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );
     
        $switched_locale = switch_to_locale( get_user_locale( $user ) );
     
        $logger = new Helpers\Log\Email; 
        $logger->defaultSubject = 'Mandrill Send Log';
     
        try {

            $mandrill = new Helpers\Mailing\Mandrill( Helpers\Theme\Theme::conf('mailing', 'md_apikey') );
            $member = new PrivateArea\Member( $user );
            
            if( in_array( PrivateArea\ACL::ROLE_MEMBER, $user->roles ) ){
                $template = Helpers\Theme\Theme::conf('mailing', 'template_new_' . ACL::ROLE_MEMBER );
            } elseif( in_array( PrivateArea\ACL::ROLE_SUPPORTER, $user->roles ) ){
                $template = Helpers\Theme\Theme::conf('mailing', 'template_new_' . ACL::ROLE_SUPPORTER );
            } else {
                $template = 'wp-new-user';
            }
            
            $results = $mandrill->messages->sendTemplate($template, array(), array_merge_recursive(
                Helpers\Mailing\Mandrill::$messageDefaults,
                array(
                    'to' => array(
                        array(
                        'email' => $user->user_email,
                        'name' => $user->display_name,
                        'type' => 'to'
                        )
                    ),
                    'subject' => sprintf ( __('Your account details at %s', 'svbk-privatearea'), get_bloginfo( 'name' ) ),
                    'global_merge_vars' => Helpers\Mailing\Mandrill::castMergeTags(
                        array(
                            'USERNAME' => $user->user_login,
                            'USER_EMAIL' => $user->user_email,
                            'FNAME' => $user->first_name,
                            'LNAME' => $user->last_name,
                            'HOME_URL' => home_url( '/' ),
                            'PRIVATEAREA_URL' => get_permalink( get_theme_mod('private_area_home') ),
                            'SET_PASSWORD_URL' => network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login'),
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
     
        if ( $switched_locale ) {
            restore_previous_locale();
        }        

    }
}

add_filter( 'login_url', 'svbk_privatearea_login_page', 10, 3 );

function svbk_privatearea_login_page( $login_url, $redirect, $force_reauth ) {
    $login_page = get_permalink( get_theme_mod('private_area_home') );
    
    if( $login_page ) {
        $login_url = add_query_arg( 'redirect_to', $redirect, $login_page );
    }
    return $login_url;
}


function svbk_privatearea_customizer( $wp_customize ){
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
add_action( 'customize_register', 'svbk_privatearea_customizer' );