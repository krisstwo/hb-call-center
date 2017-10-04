<?php


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>


    <p><?php _e( ' Bonjour ', 'woocommerce' ); ?> <?php echo $order->get_billing_first_name() . ' ' .$order->get_billing_last_name()  ?></p>

    <p> <?php _e( 'Nous avons bien pris en compte votre pré-commande passée le ', 'woocommerce' ); ?>
        <?php
        $cls_date = new DateTime($order->get_date_created());
        echo $cls_date->format('d/m/Y -  h:i'); ?>
        <?php _e( 'et nous vous en remercions.', 'woocommerce' ); ?>


    </p>
  <?php if ( $order->has_status( 'pending' ) ) : ?>
    <p><?php printf( __( 'Cliquez sur ce lien, découvrez la carte Happybreak et commandez en ligne: %2$s', 'woocommerce' ), get_bloginfo( 'name', 'display' ), '<a href="' . esc_url( $order->get_checkout_payment_url() ) . '">' . __( 'pay', 'woocommerce' ) . '</a>' ); ?></p>
<?php endif; ?>


    <p><?php _e( 'Nous vous remercions et vous souhaitons de très beaux séjours', 'woocommerce' ); ?>
        </p>

    <p><?php _e( ' L’équipe Happybreak
        Merci de ne pas répondre à cet email. Pour nous contacter, rendez-vous sur le site <a href="www.happybreak.com">happybreak.com</a> et cliquez sur « contactez-nous » ou, appelez-nous au 09 80 01 01 01 (de 8h à 22h, 7j/7, appel non surtaxé).', 'woocommerce' ); ?>
    </p>
    <p><?php _e( ' A bientôt sur <a href="www.happybreak.com"> happybreak.com</a>', 'woocommerce' ); ?>
    </p>

<?php

