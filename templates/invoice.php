<?php 

use Svbk\WP\Plugins\PrivateArea;
use Svbk\WP\Helpers;

$profile = new PrivateArea\Profile( get_the_ID() );
?>
<html>
    <head></head>
    <body>
        <!--mpdf
        <htmlpageheader name="myheader">
            <table width="100%">
            <tr>
                <td width="70%">
                    <span style="font-weight: bold; font-size: 14pt;"><?php bloginfo('contact_company_name'); ?></span><br />
                    <?php _e('VAT ID / SSN', 'svbk-privatearea'); ?>: <?php bloginfo('contact_vat'); ?><br />
                    <?php bloginfo('contact_address'); ?><br />
                    <?php bloginfo('contact_cap'); ?> - <?php bloginfo('contact_city'); ?>
                </td>
                <td width="30%" style="text-align: right;">
                    <div id="company-logo"></div>
                </td>        
            </tr>
            </table>
        </htmlpageheader>
        <htmlpagefooter name="myfooter">
        <div class="page-number" ><?php _e('Page {PAGENO} of {nb}', 'svbk-privatearea'); ?></div>
        </htmlpagefooter>
        <sethtmlpageheader name="myheader" value="on" show-this-page="1" />
        <sethtmlpagefooter name="myfooter" value="on" />
        mpdf-->
        
        <h1 class="company-name"><?php _e('Invoice Number', 'svbk-privatearea'); ?>: <?php echo esc_html($invoice_id); ?></h1>
        <div class="issue-date"><?php printf( _x('Date: %s', 'invoice', 'svbk-privatearea') , $payment_date ); ?></div>
        <div class="customer-details">
            <span style="font-size: 7pt; color: #555555; font-family: sans;">Spett.le:</span><br />
            <span style="font-weight: bold; font-size: 14pt;"><?php the_title() ?></span><br />
            <?php _e('VAT ID / SSN', 'svbk-privatearea'); ?>: <?php the_field('billing_code'); ?><br />
            <?php the_field('billing_address_1'); ?><br />
            <?php the_field('billing_postcode'); ?> - <?php the_field('billing_city'); ?> (<?php the_field('billing_state'); ?>)<br />
        </div>
        
        <br />
        <table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse; " cellpadding="8">
            <thead>
                <tr>
                    <td width="70%"><?php _e('Description', 'svbk-privatearea'); ?></td>
                    <td width="30%"><?php _e('Amount', 'svbk-privatearea'); ?></td>
                </tr>
            </thead>
            <tbody>
            <tr class="item">
                <td class="description">Description</td>
                <td class="cost">&euro;<?php printf('%.02f', $payed_amount); ?></td>
            </tr>
            </tbody>
        </table>
    </body>
</html>