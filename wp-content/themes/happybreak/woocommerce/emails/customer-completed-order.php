<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>


<p>
    <p><?php _e( ' Bonjour ', 'woocommerce' ); ?> <?php echo $order->get_billing_first_name() . ' ' .$order->get_billing_last_name()  ?></p>


</p>
<p>
<?php _e( "Nous avons bien pris en compte votre commande passée", 'woocommerce' ); ?>
    <?php
    $cls_date = new DateTime($order->get_date_created());
    echo $cls_date->format('d/m/Y -  h:i');
    ?>
    <?php _e( "et nous vous en remercions.
    Vous trouverez ci-dessous un récapitulatif des informations que vous nous avez communiquées.
    Vous recevrez votre commande à l'adresse de livraison indiquée ci-dessous.", 'woocommerce' ); ?>

</p>
<?php

/**
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 * @since 2.5.0
 */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/**
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
 ?>
