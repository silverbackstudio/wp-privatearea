<?php

namespace Svbk\WP\Plugins\PrivateArea;

class ACL {

    const ROLE_SUPPORTER = 'supporter';
    const ROLE_MEMBER = 'member';
    const GET_ITEM_MEMBER_CAP_PREFIX = 'get_member_';

    public static function can_view( $post_type ){
    	return  ! get_field( 'restricted_content' ) || current_user_can( self::GET_ITEM_MEMBER_CAP_PREFIX . $post_type );
    }
    
    public static function available_roles(){
        return array(
            self::ROLE_SUPPORTER => __('Supporter', 'svbk-privatearea'),
            self::ROLE_MEMBER => __('Member', 'svbk-privatearea'),
        );
    }    

    public static function setup_user_roles() {
    
        if( ! get_role( self::ROLE_SUPPORTER ) ) {
             add_role( self::ROLE_SUPPORTER, __('Supporter', 'svbk-privatearea'), array( 
                 'read_private_pages' => true,
                 'read_private_trainings' => true,
                 'read_private_downloads' => true,
                 'read_private_agreements' => true,
                 'read_private_consultations' => true,
                 'list_agreements' => true, 
                 'list_downloads' => true, 
                 'list_trainings' => true, 
                 'list_consultations' => true, 
             ) );
        }
    
        if( ! get_role( self::ROLE_MEMBER ) ) {
             add_role( self::ROLE_MEMBER, __('Member', 'svbk-privatearea'), array( 
            'read_private_pages' => true,
             'read_private_trainings' => true,
             'read_private_downloads' => true,
             'read_private_agreements' => true,
             'read_private_consultations' => true,         
             
             'list_agreements' => true, 
             'view_agreement' => true, 
             self::GET_ITEM_MEMBER_CAP_PREFIX . 'agreement' => true,
             
             'list_downloads' => true, 
             'view_download' => true, 
             self::GET_ITEM_MEMBER_CAP_PREFIX . 'download' => true, 
             
             'list_trainings'=> true, 
             'view_training' => true, 
             self::GET_ITEM_MEMBER_CAP_PREFIX . 'training' => true,          
             
             'list_consultations' => true, 
             self::GET_ITEM_MEMBER_CAP_PREFIX . 'consultation' => true, 
             
             ) );
        }
        
    }

    public static function reflectOnUser($meta_id, $object_id, $meta_key, $_meta_value){
        
        $available_roles = array_keys ( ACL::available_roles() );
        
        if( ( Profile::MEMBER_TYPE_FIELD !== $meta_key ) || ! in_array( $_meta_value, $available_roles ) ) {
            return;   
        }
        
        $admin_users = get_users(
            array(
                'meta_key' => Member::PROFILE_FIELD,
                'meta_value' => $object_id
            )
        );
        
        foreach($admin_users as $admin_user){
            $member = new Member( $admin_user );
            $member->set_type( $_meta_value );
        }
        
    }
    
    public static function reflectOnProfile($user_id, $role, $old_roles){
        
        if( ! in_array( $role, array_keys ( ACL::available_roles() ) ) ) {
            return;   
        }
        
        $member = new Member( $user_id );
        $member->profile()->set_type( $role );
        
    }

}

