<?php 

use Svbk\WP\Plugins\PrivateArea;
use Svbk\WP\Helpers;

$profile = new PrivateArea\Profile( get_the_ID() );
?>
<html>
    <head></head>
    <body>
        <div class="company-logo">LOGO</div>
        <p class="intro">Si certifica che</p>
        <h1 class="company-name" ><?php echo strtoupper( get_the_title() ) ?></h1>
        <p class="outro">&egrave; membro dell&apos;associazione per l&apos;anno <?php echo date('Y') ?>.</p>
        <div class="signature">Signature</div>
    </body>
</html>