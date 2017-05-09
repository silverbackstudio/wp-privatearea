<?php
namespace Svbk\WP\Plugins\PrivateArea;

use DateTime;
use DateInterval;
use Svbk\WP\Helpers;

class Member {

    protected $user;
    protected $profile;

    public static $default_role;

    const PROFILE_FIELD = 'member_profile';
    const BUSINESS_ROLE_FIELD = 'business_role';
    const PROFILE_PICTURE_FIELD = 'custom_avatar';

    public static function current(){
        return new self( wp_get_current_user() );
    }
    
    public static function register_new( $username, $email, $type = ACL::ROLE_SUPPORTER ){
        
        $user_id = register_new_user( $username , $email );
    
        if( is_wp_error( $user_id ) ){
            return $user_id;
        }        
        
        $member = new self( $user_id );
        $member->set_type( $type );
        
        return $member;
    }
    
    public function user_default_role( $role ){
        
        if( $this->userRole ) {
            return $this->userRole;
        }
        
        return $role;
    }    
    
    public function __construct( $user ){
        
    	if ( is_numeric( $user ) ) {
            $this->user = get_user_by( 'ID', $user );
        } else {
            $this->user = $user;
        }
        
    	if( !is_a( $this->user, 'WP_User' ) || ! $this->id() ){
    		wp_die( 'Error. Unable to load the specified user' );
    	}          
    	
    }
    
    public function id() {
        return $this->user->ID;
    }
    
    public function avatar( $size ){
        return get_avatar( $this->id(), $size );
    }

    public function profile(){
        
        if( empty ( $this->profile ) && $this->meta( self::PROFILE_FIELD ) ) {
            $this->profile = new Profile( $this->meta( self::PROFILE_FIELD ) );        
        }
        
        return $this->profile;
    }
    
    public function set_profile( Profile $profile ){
        $this->profile = $profile;
        
        return update_user_meta( $this->id(), self::PROFILE_FIELD, $profile->id() );
    }

    public function meta($meta_key, $single = true){
        
        if( property_exists( $this->user, $meta_key ) ) {
            return $single ? $this->user->$meta_key : array( $this->user->$meta_key ) ;
        }          
        
        if( property_exists( $this->user->data, $meta_key ) ) {
            return $single ? $this->user->data->$meta_key : array( $this->user->data->$meta_key ) ;
        }
        
        return get_user_meta( $this->id(), $meta_key, $single );
    }

    public function set_meta( $meta_key, $meta_value ){
        return update_user_meta( $this->id(), $meta_key, $meta_value );
    }

    public function is_full_member(){
        return true;
    }
    
    public function set_type( $type ){
        
        $available_roles = array_keys ( ACL::available_roles() );        
        
        foreach ( $available_roles as $available_role ) {
            $this->user->remove_role( $available_role );
        }
        
        $this->user->add_role( $type );     
        
        do_action( 'svbk_member_type_updated', $type, $this);
    
    }
    
    public function get_type(){
        
        $profile = $this->profile();
        
        if( $profile ){
            return $profile->type();
        }
        
        return '';
        
    }

}