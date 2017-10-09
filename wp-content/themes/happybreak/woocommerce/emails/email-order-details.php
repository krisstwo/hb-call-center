<?php
/**
 * Order details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$text_align = is_rtl() ? 'right' : 'left';

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>

<h3 style="color: #000000; font-weight: 800"><?php _e( 'VOTRE COMMANDE', 'woocommerce' ); ?></h3>
<p>
    <?php _e( 'N° de commande', 'happybreak' ); ?> : <?php echo $order->get_order_number() ?>
<br/>
    <?php _e( 'Date de la commande', 'happybreak' ); ?> : <?php
    $cls_date = new DateTime($order->get_date_created());
    echo $cls_date->format('d/m/Y h:i');
     ?>
</p>

<p>
    <?php $totals = $order->get_order_item_totals(); ?>
    <?php _e( 'Produits dans la commande', 'happybreak' ); ?> :
</p>

<p>
    <?php echo wc_get_email_order_items( $order, array(
        'show_sku'      => $sent_to_admin,
        'show_image'    => false,
        'image_size'    => array( 32, 32 ),
        'plain_text'    => $plain_text,
        'sent_to_admin' => $sent_to_admin,
    ) ); ?>
</p>
<p>
    <?php _e( 'Total TTC de la commande', 'happybreak' ); ?> : <?= wc_price($order->get_total()); ?>
    <br/>
    <?= $totals['payment_method']['label']; ?> <?= $totals['payment_method']['value']; ?>
</p>


<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>
