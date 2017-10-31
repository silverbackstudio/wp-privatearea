<?php

namespace Svbk\WP\Plugins\PrivateArea\Shortcakes;

use WP_Query;
use Svbk\WP\Helpers;
use Svbk\WP\Plugins\PrivateArea;
use Svbk\WP\Shortcakes\Shortcake;

class Payments extends Shortcake {

    public $shortcode_id = 'privatearea_payments';

    public function title(){
        return __('Payments Table', 'svbk-privatearea');
    }
    
    public static $defaults = array(
		'invoices' => 1,
    );        

    function fields(){
        return array(        
        		'invoices' => array(
        			'label'  => esc_html__( 'Show Invoices', 'svbk-privatearea' ),
        			'attr'   => 'invoices',
        			'type'   => 'checkbox',
        			'description' => esc_html__( 'Show invoices column', 'svbk-privatearea' ),
        		)
            );
    }

    public function output( $attr, $content, $shortcode_tag) {
        $attr = $this->shortcode_atts( self::$defaults, $attr, $shortcode_tag );         
        
        $profile = PrivateArea\Member::current()->profile();

        $invoices = filter_var($attr['invoices'], FILTER_VALIDATE_BOOLEAN);

        $content = '<table>';
        $content .= '   <tr>';
        $content .= '       <th>' . __( 'Date', 'svbk-privatearea' ) . '</th>';
        $content .= '       <th>' . __( 'Amount', 'svbk-privatearea' ) . '</th>';
                
        if( $invoices ) {
            $content .= '   <th>' . __( 'Invoice', 'svbk-privatearea' ) . '</th>';
        }
        
        $content .= '   </tr>';    
            
        while ( have_rows('payments', $profile->id()) ) : the_row();
            $content .= '<tr>';
            $content .= '   <td>' . date_i18n( get_option( 'date_format' ), intval( get_sub_field('date')) )  . '</td>';
            $content .= '   <td>' . number_format_i18n( get_sub_field('payed_amount'), 2 ) . ' &euro;</td>';
             
            if( $invoices && get_row_index() ) {
                $content .= '<td><a href="' . esc_url( 
                        add_query_arg( 
                            array( 
                                'pdf_download' => 'invoice', 
                                'invoice_seq' => get_row_index() 
                            )  
                        ) 
                    ) . ' " target="_blank">' . 
                    sprintf( __( 'Download Invoice (#%d)', 'svbk-privatearea' ), get_sub_field('invoice_id') ) . 
                    '</a></td>';
            }
            
            $content .= '</tr>';
            
        endwhile;        
        
        $content .= '</table>';
        
        return $content;

    }
    
}
