<?php

namespace Svbk\WP\Plugins\PrivateArea\Shortcakes;

use WP_Query;
use Svbk\WP\Helpers;
use Svbk\WP\Plugins\PrivateArea;
use Svbk\WP\Shortcakes\Shortcake;

class Login extends Shortcake {

    public $shortcode_id = 'privatearea_login';
    public $icon = 'dashicons-admin-network';

    public function title(){
        return __('Login form', 'svbk-privatearea');
    }
    
    public static $defaults = array(
        'redirect' => null, 
        'label_username' => null,
        'label_password' => null,
        'label_remember' => null,
        'label_log_in' => null,
        'remember' => true,
        'show_lostpassword' => false,
    );        

    function fields(){
        return array(        
        		array(
        			'label'  => esc_html__( 'Default redirect to', 'svbk-privatearea' ),
        			'attr'   => 'redirect',
                    'type'     => 'post_select',
                    'query'    => array( 'post_type' => 'page' ),
			        'multiple' => false,
        		),
        		array(
        			'label'  => esc_html__( 'Username Label', 'svbk-privatearea' ),
        			'attr'   => 'label_username',
        			'type'   => 'text',
        		),
        		array(
        			'label'  => esc_html__( 'Password Label', 'svbk-privatearea' ),
        			'attr'   => 'label_password',
        			'type'   => 'text',
        		),
        		array(
        			'label'  => esc_html__( 'Remember Label', 'svbk-privatearea' ),
        			'attr'   => 'label_remember',
        			'type'   => 'text',
        		),  
        		array(
        			'label'  => esc_html__( 'Login Button Label', 'svbk-privatearea' ),
        			'attr'   => 'label_log_in',
        			'type'   => 'text',
        		),          		
        		array(
        			'label'  => esc_html__( 'Show remember flag', 'svbk-privatearea' ),
        			'attr'   => 'remember',
        			'type'   => 'checkbox',
        		),  
        		array(
        			'label'  => esc_html__( 'Show lost password button', 'svbk-privatearea' ),
        			'attr'   => 'show_lostpassword',
        			'type'   => 'checkbox',
        		),          		
            );
    }

    public function output( $attr, $content, $shortcode_tag) {
        
        if( defined('SHORTCODE_UI_DOING_PREVIEW') && SHORTCODE_UI_DOING_PREVIEW ) {
                return '<div class="login-form-preview">[' . __('Login form', 'svbk-privatearea') . ']</div>';
        }   
        
        $attr = $this->shortcode_atts( self::$defaults, $attr, $shortcode_tag );         
        
        $attr['form_id'] = 'loginform-privatearea';
        $attr['redirect'] = filter_input(INPUT_GET, 'redirect_to', FILTER_SANITIZE_URL) ?: get_permalink($attr['redirect']);
        
        wp_login_form( array_filter($attr) ); 
        
        if ( boolval( $attr['show_lostpassword'] ) ) {
            echo '<a class="lost-password" href="' . wp_lostpassword_url( $attr['redirect'] ) . '" >' . _e('Lost Password?', 'svbk-privatearea') .'</a>';
        }
	

    }
    
}