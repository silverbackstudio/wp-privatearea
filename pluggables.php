<?php

/**
 * @package SilverbackStudio Member Private Area
 * @version 1.1
 */

use Svbk\WP\Helpers;
use Svbk\WP\Plugins\PrivateArea;

if ( !function_exists('wp_new_user_notification') ) {
    
    function wp_new_user_notification( $user_id, $type = null, $notify = '' ) {

        $deprecated = null;
        
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
            require_once( ABSPATH . WPINC . '/class-phpass.php');
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
        
            if(! $type ) {
                $type = $member->get_type();
            }
            
            if( $type && Helpers\Theme\Theme::conf('mailing', 'template_new_user_' . $type ) ) {
                $template = Helpers\Theme\Theme::conf('mailing', 'template_new_user_' . $type );
            } else {
                $template = Helpers\Theme\Theme::conf('mailing', 'template_new_user_default' );
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
                    'from_email' => Helpers\Theme\Theme::conf('mailing', 'from_email'),
                    'from_name' => Helpers\Theme\Theme::conf('mailing', 'from_name'),                    
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

