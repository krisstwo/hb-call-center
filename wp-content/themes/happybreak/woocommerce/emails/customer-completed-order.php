<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>


<p>
<p><?php _e(' Bonjour ', 'happybreak'); ?><?php echo $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ?></p>


</p>
<p>
    <?php _e("Nous avons bien pris en compte votre commande passée le", 'happybreak'); ?>
    <?php
    $cls_date = new DateTime($order->get_date_created());
    echo $cls_date->format('d/m/Y');
    ?>
     <?php _e("et nous vous en remercions.", 'happybreak'); ?>
</p>
<p><?php _e("Vous trouverez votre facture en pièce jointe de cet email et recevrez toutes les informations nécessaires dans un prochain email.", 'happybreak'); ?></p>
<?php

/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
 ?>
