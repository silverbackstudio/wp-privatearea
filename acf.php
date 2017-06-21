<?php

namespace Svbk\WP\Plugins\PrivateArea;

use Svbk\WP\Helpers;

add_action( 'after_setup_theme', __NAMESPACE__.'\\svbk_privatearea_acf_register_fields' );

function svbk_privatearea_acf_register_fields(){

	if( ! function_exists('acf_add_local_field_group') ){
		return;
	}

    acf_add_local_field_group(array (
    	'key' => 'group_58fdf842e0b8c',
    	'title' => _x('Member Subscription Details', 'field group', 'svbk-privatearea'),
    	'fields' => array (
    		array (
    			'key' => 'field_58fdf84e5eb4a',
    			'label' => __('Member Type', 'svbk-privatearea'),
    			'name' => Profile::MEMBER_TYPE_FIELD,
    			'type' => 'radio',
    			'instructions' => '',
    			'required' => 1,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'choices' => ACL::available_roles(),
    			'allow_null' => 0,
    			'other_choice' => 0,
    			'save_other_choice' => 0,
    			'default_value' => '',
    			'layout' => 'vertical',
    			'return_format' => 'value',
    		),
    		array (
    			'key' => 'field_58fdf940f473c',
    			'label' => __('Current Subscription Date', 'svbk-privatearea'),
    			'name' => Profile::DATE_FIELD,
    			'type' => 'date_picker',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'display_format' => 'd/m/Y',
    			'return_format' => 'U',
    			'first_day' => 1,
    		),
    		array (
    			'key' => 'field_58fdf8f43b57e',
    			'label' => __('Subscription Expire Date', 'svbk-privatearea'),
    			'name' => Profile::EXPIRE_FIELD,
    			'type' => 'date_picker',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'display_format' => 'd/m/Y',
    			'return_format' => 'U',
    			'first_day' => 1,
    		),
    		array (
    			'key' => 'field_591490068a6b1',
    			'label' => __('Invoice Number', 'svbk-privatearea'),
    			'name' => 'invoice_number',
    			'type' => 'text',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'default_value' => '',
    			'placeholder' => '',
    			'prepend' => '',
    			'append' => '',
    			'maxlength' => '',
    		),			
    		array (
    			'key' => 'field_591432d08bdde',
    			'label' => __('Expired Notification Sent', 'svbk-privatearea'),
    			'name' => 'subscription_expired_notification_sent',
    			'type' => 'true_false',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'message' => __('Uncheck this flag to resend the notification', 'svbk-privatearea'),
    			'default_value' => 0,
    			'ui' => 0,
    			'ui_on_text' => '',
    			'ui_off_text' => '',
    		),
    		array (
    			'key' => 'field_5914331a8bddf',
    			'label' => __('Expiring Notification Sent', 'svbk-privatearea'),
    			'name' => 'subscription_expiring_notification_sent',
    			'type' => 'true_false',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'message' => __('Uncheck this flag to resend the notification', 'svbk-privatearea'),
    			'default_value' => 0,
    			'ui' => 0,
    			'ui_on_text' => '',
    			'ui_off_text' => '',
    		),
    	),
    	'location' => array (
    		array (
    			array (
    				'param' => 'post_type',
    				'operator' => '==',
    				'value' => 'member',
    			),
    		),
    	),
    	'menu_order' => 0,
    	'position' => 'side',
    	'style' => 'default',
    	'label_placement' => 'top',
    	'instruction_placement' => 'label',
    	'hide_on_screen' => '',
    	'active' => 1,
    	'description' => '',
    ));


    acf_add_local_field_group(array (
    	'key' => 'group_5902fef56f38f',
    	'title' => _x('Member Details', 'field group', 'svbk-privatearea'),
    	'fields' => array (
    		array (
    			'key' => 'field_5902ff0730a9e',
    			'label' => __('CEO First Name', 'svbk-privatearea'),
    			'name' => 'billing_first_name',
    			'type' => 'text',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'default_value' => '',
    			'placeholder' => '',
    			'prepend' => '',
    			'append' => '',
    			'maxlength' => '',
    		),
    		array (
    			'key' => 'field_59030a2530a9f',
    			'label' => __('CEO Last Name', 'svbk-privatearea'),
    			'name' => 'billing_last_name',
    			'type' => 'text',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'default_value' => '',
    			'placeholder' => '',
    			'prepend' => '',
    			'append' => '',
    			'maxlength' => '',
    		),
    		array (
    			'key' => 'field_59030a4330aa0',
    			'label' => __('Company Name',  'svbk-privatearea'),
    			'name' => 'billing_company',
    			'type' => 'text',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'default_value' => '',
    			'placeholder' => '',
    			'prepend' => '',
    			'append' => '',
    			'maxlength' => '',
    		),
    		array (
    			'key' => 'field_59030a5130aa1',
    			'label' => __('VAT ID / SSN', 'svbk-privatearea'),
    			'name' => 'billing_code',
    			'type' => 'text',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'default_value' => '',
    			'placeholder' => '',
    			'prepend' => '',
    			'append' => '',
    			'maxlength' => '',
    		),
    		array (
    			'key' => 'field_59030a9c30aa2',
    			'label' => __('Billing Address', 'svbk-privatearea'),
    			'name' => 'billing_address_1',
    			'type' => 'text',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'default_value' => '',
    			'placeholder' => '',
    			'prepend' => '',
    			'append' => '',
    			'maxlength' => '',
    		),
    		array (
    			'key' => 'field_59030ab730aa3',
    			'label' => __('Billing City', 'svbk-privatearea'),
    			'name' => 'billing_city',
    			'type' => 'text',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'default_value' => '',
    			'placeholder' => '',
    			'prepend' => '',
    			'append' => '',
    			'maxlength' => '',
    		),
    		array (
    			'key' => 'field_59030ad630aa4',
    			'label' => __('Billing Post Code', 'svbk-privatearea'),
    			'name' => 'billing_postcode',
    			'type' => 'text',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'default_value' => '',
    			'placeholder' => '',
    			'prepend' => '',
    			'append' => '',
    			'maxlength' => '',
    		),
    		array (
    			'key' => 'field_59030af030aa5',
    			'label' => __('Billing State/Province', 'svbk-privatearea'),
    			'name' => 'billing_state',
    			'type' => 'text',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'default_value' => '',
    			'placeholder' => '',
    			'prepend' => '',
    			'append' => '',
    			'maxlength' => '',
    		),
    		array (
    			'key' => 'field_59030b0d30aa6',
    			'label' => __('Billing Country', 'svbk-privatearea'),
    			'name' => 'billing_country',
    			'type' => 'select',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'choices' => Helpers\Lists\Places::countries(),
    			'default_value' => array ( 'IT'	),
    			'allow_null' => 0,
    			'multiple' => 0,
    			'ui' => 1,
    			'ajax' => 1,
    			'return_format' => 'value',
    			'placeholder' => '',
    		),
    		array (
    			'key' => 'field_59030b3230aa7',
    			'label' => __('E-mail', 'svbk-privatearea'),
    			'name' => 'billing_email',
    			'type' => 'email',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'default_value' => '',
    			'placeholder' => '',
    			'prepend' => '',
    			'append' => '',
    		),
    		array (
    			'key' => 'field_59030b4230aa8',
    			'label' => __('Phone', 'svbk-privatearea'),
    			'name' => 'phone',
    			'type' => 'text',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'default_value' => '',
    			'placeholder' => '',
    			'prepend' => '',
    			'append' => '',
    			'maxlength' => '',
    		),
    		array (
    			'key' => 'field_59030b4109aa8',
    			'label' => __('Mobile', 'svbk-privatearea'),
    			'name' => 'mobile',
    			'type' => 'text',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'default_value' => '',
    			'placeholder' => '',
    			'prepend' => '',
    			'append' => '',
    			'maxlength' => '',
    		),		
    		array (
    			'key' => 'field_58e4998873b30',
    			'label' => __('Website', 'svbk-privatearea'),
    			'name' => 'website',
    			'type' => 'text',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'default_value' => '',
    			'maxlength' => '',
    			'placeholder' => '',
    			'prepend' => '',
    			'append' => '',
    		),
    		array (
    			'key' => 'field_59030be8hfd1a',
    			'label' => __('Map Location', 'svbk-privatearea'),
    			'name' => 'business_map_location',
    			'type' => 'google_map',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'center_lat' => '',
    			'center_lng' => '',
    			'zoom' => '',
    			'height' => 200,
    		),
    		array (
    			'key' => 'field_5913210782787',
    			'label' => __('Company Logo', 'svbk-privatearea'),
    			'name' => 'company_logo',
    			'type' => 'image',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'return_format' => 'id',
    			'preview_size' => 'thumbnail',
    			'library' => 'uploadedTo',
    			'min_width' => '',
    			'min_height' => '',
    			'min_size' => '',
    			'max_width' => '',
    			'max_height' => '',
    			'max_size' => '',
    			'mime_types' => '',
    		),		
    		array (
    			'key' => 'field_59030dc8c39eb',
    			'label' => __('Apartment Types', 'svbk-privatearea'),
    			'name' => 'apartment_types',
    			'type' => 'checkbox',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'choices' => Profile::rent_types(),
    			'allow_custom' => 0,
    			'save_custom' => 0,
    			'default_value' => array (
    			),
    			'layout' => 'vertical',
    			'toggle' => 0,
    			'return_format' => 'value',
    		),		
    		array (
    			'key' => 'field_59030bc7afd19',
    			'label' => __('Apartments', 'field label', 'svbk-privatearea'),
    			'name' => 'apartments',
    			'type' => 'repeater',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'collapsed' => '',
    			'min' => 0,
    			'max' => 0,
    			'layout' => 'table',
    			'button_label' => __('Add Apartment Group', 'svbk-privatearea'),
    			'sub_fields' => array (
    				array (
    					'key' => 'field_59030bdcafd1a',
    					'label' => __('City', 'svbk-privatearea'),
    					'name' => 'city',
    					'type' => 'text',
    					'instructions' => '',
    					'required' => 0,
    					'conditional_logic' => 0,
    					'wrapper' => array (
    						'width' => '',
    						'class' => '',
    						'id' => '',
    					),
    					'default_value' => '',
    					'maxlength' => '',
    					'placeholder' => '',
    					'prepend' => '',
    					'append' => '',
    				),
    				array (
    					'key' => 'field_59030c05afd1b',
    					'label' => __('Apartments Count', 'svbk-privatearea'),
    					'name' => 'apartments_count',
    					'type' => 'number',
    					'instructions' => '',
    					'required' => 0,
    					'conditional_logic' => 0,
    					'wrapper' => array (
    						'width' => '',
    						'class' => '',
    						'id' => '',
    					),
    					'default_value' => '',
    					'placeholder' => '',
    					'prepend' => '',
    					'append' => '',
    					'min' => 0,
    					'max' => '',
    					'step' => 1,
    				),
    				array (
    					'key' => 'field_59030c33afd1c',
    					'label' => __('Beds Count', 'svbk-privatearea'),
    					'name' => 'beds_count',
    					'type' => 'number',
    					'instructions' => '',
    					'required' => 0,
    					'conditional_logic' => 0,
    					'wrapper' => array (
    						'width' => '',
    						'class' => '',
    						'id' => '',
    					),
    					'default_value' => '',
    					'placeholder' => '',
    					'prepend' => '',
    					'append' => '',
    					'min' => 0,
    					'max' => '',
    					'step' => '',
    				),
    			),
    		),
    
    	),
    	'location' => array (
    		array (
    			array (
    				'param' => 'post_type',
    				'operator' => '==',
    				'value' => 'member',
    			),
    		),
    	),
    	'menu_order' => 0,
    	'position' => 'normal',
    	'style' => 'default',
    	'label_placement' => 'top',
    	'instruction_placement' => 'label',
    	'hide_on_screen' => '',
    	'active' => 1,
    	'description' => '',
    ));
    
    acf_add_local_field_group(array (
    	'key' => 'group_59035733550b1',
    	'title' => __('User Fields', 'svbk-privatearea'),
    	'fields' => array (
    		array (
    			'key' => 'field_59035720398d',
    			'label' => __('First Name', 'svbk-privatearea'),
    			'name' => 'first_name',
    			'type' => 'text',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'default_value' => '',
    			'placeholder' => '',
    			'prepend' => '',
    			'append' => '',
    			'maxlength' => '',
    		),
    		array (
    			'key' => 'field_5903575ksa98d',
    			'label' => __('Last Name', 'svbk-privatearea'),
    			'name' => 'last_name',
    			'type' => 'text',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'default_value' => '',
    			'placeholder' => '',
    			'prepend' => '',
    			'append' => '',
    			'maxlength' => '',
    		),	
    		array (
    			'key' => 'field_59035753xt98d',
    			'label' => __('E-mail', 'svbk-privatearea'),
    			'name' => 'user_email',
    			'type' => 'email',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'default_value' => '',
    			'placeholder' => '',
    			'prepend' => '',
    			'append' => '',
    			'maxlength' => '',
    		),			
    		array (
    			'key' => 'field_5903573a0e98b',
    			'label' => __('Member Profile', 'svbk-privatearea'),
    			'name' => Member::PROFILE_FIELD,
    			'type' => 'post_object',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'post_type' => array (
    				Profile::POST_TYPE
    			),
    			'taxonomy' => array (
    			),
    			'allow_null' => 0,
    			'multiple' => 0,
    			'return_format' => 'id',
    			'ui' => 1,
    		),
    		array (
    			'key' => 'field_590357d70e98d',
    			'label' => __('Business Role', 'svbk-privatearea'),
    			'name' => Member::BUSINESS_ROLE_FIELD,
    			'type' => 'text',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'default_value' => '',
    			'placeholder' => '',
    			'prepend' => '',
    			'append' => '',
    			'maxlength' => '',
    		),
    		array (
    			'key' => 'field_590357ea0e98e',
    			'label' => __('Profile Picture', 'svbk-privatearea'),
    			'name' => Member::PROFILE_PICTURE_FIELD,
    			'type' => 'image',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'return_format' => 'id',
    			'preview_size' => 'thumbnail',
    			'library' => 'uploadedTo',
    			'min_width' => '',
    			'min_height' => '',
    			'min_size' => '',
    			'max_width' => '',
    			'max_height' => '',
    			'max_size' => '',
    			'mime_types' => '',
    		),
    	),
    	'location' => array (
    		array (
    			array (
    				'param' => 'user_role',
    				'operator' => '==',
    				'value' => ACL::ROLE_SUPPORTER,
    			),
    		),
    		array (
    			array (
    				'param' => 'user_role',
    				'operator' => '==',
    				'value' => ACL::ROLE_MEMBER,
    			),
    		),
    		array (
    			array (
    				'param' => 'user_form',
    				'operator' => '==',
    				'value' => 'edit',
    			),
    		),		
    	),
    	'menu_order' => 0,
    	'position' => 'normal',
    	'style' => 'default',
    	'label_placement' => 'top',
    	'instruction_placement' => 'label',
    	'hide_on_screen' => '',
    	'active' => 1,
    	'description' => '',
    ));
    
    acf_add_local_field_group(array (
    	'key' => 'group_594a2a7f5e422',
    	'title' => __('Membership Payments', 'svbk-privatearea'),
    	'fields' => array (
    		array (
    			'key' => 'field_594a2a84029be',
    			'label' => __('Payments', 'svbk-privatearea'),
    			'name' => 'payments',
    			'type' => 'repeater',
    			'instructions' => '',
    			'required' => 0,
    			'conditional_logic' => 0,
    			'wrapper' => array (
    				'width' => '',
    				'class' => '',
    				'id' => '',
    			),
    			'collapsed' => 'field_594a2ac4029c0',
    			'min' => 0,
    			'max' => 0,
    			'layout' => 'table',
    			'button_label' => '',
    			'sub_fields' => array (
    				array (
    					'key' => 'field_594a2a98029bf',
    					'label' => __('Transaction', 'svbk-privatearea'),
    					'name' => 'transaction',
    					'type' => 'text',
    					'instructions' => '',
    					'required' => 0,
    					'conditional_logic' => 0,
    					'wrapper' => array (
    						'width' => '',
    						'class' => '',
    						'id' => '',
    					),
    					'default_value' => '',
    					'placeholder' => '',
    					'prepend' => '',
    					'append' => '',
    					'maxlength' => '',
    				),
    				array (
    					'key' => 'field_594a2ac4029c0',
    					'label' => __('Date', 'svbk-privatearea'),
    					'name' => 'date',
    					'type' => 'date_picker',
    					'instructions' => '',
    					'required' => 0,
    					'conditional_logic' => 0,
    					'wrapper' => array (
    						'width' => '',
    						'class' => '',
    						'id' => '',
    					),
    					'display_format' => 'd/m/Y',
    					'return_format' => 'U',
    					'first_day' => 1,
    				),
    				array (
    					'key' => 'field_594a2af2029c1',
    					'label' => __('Payed Amount', 'svbk-privatearea'),
    					'name' => 'payed_amount',
    					'type' => 'number',
    					'instructions' => '',
    					'required' => 0,
    					'conditional_logic' => 0,
    					'wrapper' => array (
    						'width' => '',
    						'class' => '',
    						'id' => '',
    					),
    					'default_value' => '',
    					'placeholder' => '',
    					'prepend' => '',
    					'append' => '',
    					'min' => '',
    					'max' => '',
    					'step' => '',
    				),
    				array (
    					'key' => 'field_594a2b0b029c2',
    					'label' => __('Invoice ID', 'svbk-privatearea'),
    					'name' => 'invoice_id',
    					'type' => 'text',
    					'instructions' => '',
    					'required' => 0,
    					'conditional_logic' => 0,
    					'wrapper' => array (
    						'width' => '',
    						'class' => '',
    						'id' => '',
    					),
    					'default_value' => '',
    					'placeholder' => '',
    					'prepend' => '',
    					'append' => '',
    					'maxlength' => '',
    				),
    			),
    		),
    	),
    	'location' => array (
    		array (
    			array (
    				'param' => 'post_type',
    				'operator' => '==',
    				'value' => 'member',
    			),
    		),
    	),
    	'menu_order' => 0,
    	'position' => 'normal',
    	'style' => 'default',
    	'label_placement' => 'top',
    	'instruction_placement' => 'label',
    	'hide_on_screen' => '',
    	'active' => 1,
    	'description' => '',
    ));
    

}