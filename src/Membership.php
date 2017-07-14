<?php

namespace Svbk\WP\Plugins\PrivateArea;

class Membership {

    public static $levels = array();

    const LEVEL_CAPABILITY_PREFIX = 'has_membership_level_';
    const LEVELS_FIELDS = 'svbk_privatearea_levels';

    public static function add_level($level_name, $label, $capabilities = array(), $force_update = true ){
        
      $role = get_role( $level_name );
      $capabilities[] = self::LEVEL_CAPABILITY_PREFIX . $level_name;

      if( null === $role ) {
            $caps = array();
            foreach( $capabilities as $cap ) {
                $caps[$cap] = true;
            }
            add_role( $level_name, $label, $caps );
      } elseif( $force_update ) {
            $role->remove_cap( array_diff( $role->capabilities, $capabilities ) );
            $role->add_cap( array_diff( $capabilities, $role->capabilities ) );
      }
      
    }

    public static function levels( $refresh = false ){
        return wp_list_pluck( self::levelDetails(), 'name', 'role' );
    }
    
    public static function levelDetails( $level = null ){

        $found = null;
        $levels = wp_cache_get( 'membership_levels', 'svbk-privatearea', $refresh, $found );
        
        if ( $refresh || ! $found ) {

            $levels = get_field('svbk_privatearea_levels', 'options');
            $levels = array_combine( wp_list_pluck($levels, 'role'), $levels);

            wp_cache_set( 'membership_levels', $levels, 'svbk-privatearea', 30 );
            
        }        
        
        if( $level ){
            return !empty( $levels[$level] ) ? $levels[$level] : false;
        }
        
        return $levels;
    }

    public static function reflectOnUser($meta_id, $object_id, $meta_key, $_meta_value){
        
        $available_roles = array_keys ( self::levels() );
        
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
        
        if( ! in_array( $role, array_keys ( self::levels() ) ) ) {
            return;   
        }
        
        $member = new Member( $user_id );
        $member->profile()->set_type( $role );
        
    }

}

