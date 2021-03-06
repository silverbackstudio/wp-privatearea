<?php
namespace Svbk\WP\Plugins\PrivateArea;

use DateTime;
use DateInterval;
use Svbk\WP\Helpers;

class Profile {

    protected $id = '';
    protected $data = '';

    const POST_TYPE = 'member';
    const MEMBER_TYPE_FIELD = 'member_type';
    const DATE_FIELD = 'subscription_date';
    const EXPIRE_FIELD = 'subscription_expire_date';
    const DATE_FORMAT_SAVE = 'Ymd';

    public function __construct( $profile_id ){
        $this->set_id( $profile_id );
    }

    public static function create( $post_args = array() ) {
        
        $post_args['post_type'] = self::POST_TYPE;
        
        $id = wp_insert_post( $post_args );
        
        if( $id ){
            return new self( $id );
        }
        
        return $id;
    }

    public function set_id( $id ){
        $this->id = $id;
        
        if( function_exists('get_fields') ){
            $this->data = get_fields( $this->id );
        } else {
            return null;
        }        
        
    }

    public function id(){
        return $this->id;
    }

    public function type(){
        return $this->meta( self::MEMBER_TYPE_FIELD );
    }
    
    public function data(){
        return $this->data;
    }    
    
    public function completed(){
        
        $all_meta = get_fields( $this->id() );
        $fields = acf_get_fields( 'group_5902fef56f38f' ) ;
        
        $complete = count( $fields );
        
        $filled = array_intersect_key($all_meta, wp_list_pluck($fields, 'key', 'name'));
        
        $current = count ( array_filter( $filled ) );
        return $current / $complete;
    }
    
    public function set_type( $type ) {
        update_post_meta( $this->id, self::MEMBER_TYPE_FIELD, $type );
        
        do_action( 'svbk_profile_type_updated', $type, $this);
    }

    public function meta( $meta_key, $single = true, $raw = true){
            
    	if ( ! $this->id ) {
    	    return '';
    	} 	        
        
    	if( $meta_key === 'company_name' ){
    		return get_the_title( $this->id );
    	}
    	
    	if( !$raw && isset( $this->data[$meta_key] ) ){
    	    return $this->data[$meta_key];
    	} 
    	
    	return get_post_meta($this->id, $meta_key, $single);        
    }
    
    public function subscription_name(){
        $role = $this->meta('member_type');
        $names = wp_roles()->get_names();
        
        if( isset( $names[ $role ] ) ) {
            return $names[ $role ];
        }
        
        return $role;
    }

    public function apartments(){
        $apartments = array();

        while ( have_rows('apartments', $this->id ) ) : the_row();
                $apartments[] = array(
                    'city' => get_sub_field( 'city' ),
                    'count' => get_sub_field( 'apartments_count' ),
                    'beds' => get_sub_field( 'beds_count' ),
                );
        endwhile;        
        
        return $apartments;
    }
    
    public function set_expire( DateTime $date ){
        return $this->set_expire_date( $date ); 
    }   
    
    public function set_expire_date( DateTime $date ){
        return update_field(self::EXPIRE_FIELD, $date->format(self::DATE_FORMAT_SAVE), $this->id());
    }       
    
    public function set_subscribe_date( DateTime $date ){
        return update_field(self::DATE_FIELD, $date->format(self::DATE_FORMAT_SAVE), $this->id());
    }       
    
    public function subscription_date(){
        $raw = $this->meta(self::DATE_FIELD, true, false); 
        
        if( $raw ) {
            return DateTime::createFromFormat( 'U', $raw );
        } else {
            return null;
        }
    }       
    
    public function subscription_expires(){
        $raw = $this->meta(self::EXPIRE_FIELD, true, false);

        if( $raw ) {
            return DateTime::createFromFormat( 'U', $raw );
        } else {
            return null;
        }
    }
    
    public function subscription_extend( DateTime $paymentDate, DateInterval $period ){
        
        if( $this->is_subscription_expired() ){
            $expireDate = clone $paymentDate;
        } else {
            $expireDate = $this->subscription_expires();
        }
        
        $expireDate->add( $period );
        $this->set_expire( $expireDate );   
        
        return $expireDate;
    }    

    public function is_type( $type ){
        return $this->meta( self::MEMBER_TYPE_FIELD ) === $type;
    }

    public function is_subscription_expired( $interval = null ){
        $expiration = $this->subscription_expires();
        
        if(null === $expiration){
            return true;
        }

        $limit = new DateTime('NOW'); 

        if( $interval ) {
          $limit->add( new DateInterval($interval) );
        } 
        
        return $limit > $expiration;
    }
    
    public function subscription_expire_eta( $format = null ){
        
        $expires = $this->subscription_expires();
        
        if(null === $expires){
            $eta = null;
        } else {
            $now = new DateTime();
            $eta = $now->diff( $expires );
        }
        
        if( $eta && $format ){
            return $eta->format( $format );
        } else {
            return $eta;
        }          
    }
    
    public static function rent_types(){
        return array (
				'1' => 'Monolocale',
				'2' => 'Bilocale',
				'3' => 'Trilocale',
				'4' => 'Quadrilocale',
				'5' => 'Più di 4 locali',
				'6' => 'Agriturismo',
				'7' => 'Appartamento',
				'8' => 'Attico',
				'9' => 'Barca a vela',
				'10' => 'Casa a schiera',
				'11' => 'Castello',
				'12' => 'Condominio',
				'13' => 'Loft',
				'14' => 'Mansarda',
				'15' => 'Palafitta',
				'16' => 'Stanza condivisa',
				'17' => 'Stanza singola',
				'18' => 'Studio',
				'19' => 'Villa',
				'20' => 'Chalet',
				'21' => 'Bungalow',
			);
    }
    
}