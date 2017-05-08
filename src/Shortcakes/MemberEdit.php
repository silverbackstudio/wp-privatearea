<?php

namespace Svbk\WP\Plugins\PrivateArea\Shortcakes;

use WP_Query;
use Svbk\WP\Helpers;
use Svbk\WP\Plugins\PrivateArea;
use Svbk\WP\Shortcakes\Forms\Form as Base;

class MemberEdit extends Base {
    
    public $renderOrder = array(
        'wrapperBegin',
        'openButton',  
        'hiddenBegin',
        'closeButton',
        'hiddenContentBegin',
    	'formBegin',
    	'title',
    	'input',
        'requiredNotice',
        'submitButton',
        'messages',
        'formEnd',
        'hiddenContentEnd',
        'hiddenEnd',
        'wrapperEnd',
    );    
    
    public $shortcode_id = 'member_edit';
    public $field_prefix = 'medit';    
    public $action = 'svbk_member_edit';
    
    public $classes = array( 'form-privatearea-subscribe', 'form-privatearea-member-edit' );
    
    public $formClass = '\Svbk\WP\Plugins\PrivateArea\Form\MemberEdit';

    public function title(){
        return __('Member Edit Form', 'svbk-privatearea');
    }
    
    public function confirmMessage(){
        return $this->confirmMessage ?: __('Your informations have been updated', 'svbk-privatearea');
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
    
    
}
