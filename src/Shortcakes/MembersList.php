<?php

namespace Svbk\WP\Plugins\PrivateArea\Shortcakes;

use WP_Query;
use Svbk\WP\Plugins\PrivateArea;
use Svbk\WP\Shortcakes\Shortcake as Base;

class MembersList extends Base {
    
    public $shortcode_id = 'members_list';
    public $post_type = 'member';  
    public $query_args = array();    
    
    public static $defaults = array(
		'count' => 5,
    );    
    
    public function title(){
        return __('Members List', 'svbk-privatearea');
    }
    
    function fields(){
        return array(        
        		array(
        			'label'  => esc_html__( 'Members Count', 'svbk-privatearea' ),
        			'attr'   => 'count',
        			'type'   => 'number',
        			'encode' => true,
        			'description' => esc_html__( 'How many members to show', 'svbk-privatearea' ),
        			'meta'   => array(
        				'placeholder' =>  self::$defaults['count'],
        			),
        		)
            );
    }

    protected function getQueryArgs($attr){

    	return array_merge(array(
    	    'post_type' => $this->post_type,
    	    'post_status' => 'publish',
            'meta_query' => array(
                'relation'=>'AND',
                array(
                   'key' => PrivateArea\Profile::MEMBER_TYPE_FIELD,
                   'value' => PrivateArea\ACL::ROLE_MEMBER,
                ), 
                array(
                   'key' => PrivateArea\Profile::EXPIRE_FIELD,
                   'value' => date( PrivateArea\Profile::DATE_FORMAT_SAVE ),
                   'compare' => '>=',
                   'type' => 'NUMERIC'
                )                
            ),    	    
    	    'orderby' => 'date',
    	    'order' => 'ASC',
    	    'posts_per_page' => $attr['count'],
    	), $this->query_args );
    	
    }
    
    public function output( $attr, $content, $shortcode_tag) {
        
        $output = '';

        $attr = $this->shortcode_atts( self::$defaults, $attr, $shortcode_tag );         

        $members = new WP_Query( $this->getQueryArgs($attr) );
        
        if($members->have_posts()){
            
                $output .= '<aside class="members-list" >';

            if ( locate_template('template-parts/thumb-' . $this->post_type . '.php') != '' ) { 
                
                ob_start();
                
                while( $members->have_posts() ): $members->the_post();
            	    get_template_part( 'template-parts/thumb', $this->post_type );
                endwhile;
                
                $output .= ob_get_contents();
                ob_end_clean();                
                
            } else {
                
                while( $members->have_posts() ): $members->next_post();
                
                    $output .= '<blockquote class="member">';
                    $output .= apply_filters( 'the_content', $members->post->post_content );
                    $output .= '<footer class="author">';
                    $output .=  '<cite class="name">'. get_the_title( $members->post ) .'</cite>';
                    $output .=  '<div class="picture">' . get_the_post_thumbnail( $members->post->ID, 'small' ) . '</div>';
                    $output .=  '<span class="role">' . get_field( 'author_role', $members->post->ID ) .'</span>';
                    $output .= '</footer>';
                    $output .= '</blockquote>';                
                
                endwhile;
            }
            
            $output .= '</aside>';
        }
    	
    	return $output;
    	
    }
    
}
