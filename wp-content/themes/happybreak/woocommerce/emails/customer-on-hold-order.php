<?php
/**
 * Customer on-hold order email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-on-hold-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://docs.woocommerce.com/document/template-structure/
 * @author        WooThemes
 * @package    WooCommerce/Templates/Emails
 * @version     2.5.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action('woocommerce_email_header', $email_heading, $email); ?>

    <p><?php _e('Bonjour ', 'happybreak'); ?><?php echo $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ?></p>
<?php if (!(int)$order->get_meta($email->id . '_sent', true)) : ?>
    <p><?php _e("Merci pour votre commande Happybreak.", 'happybreak'); ?></p>
<?php else : ?>
    <p><?php _e("Sauf erreur de ma part je n’ai pas encore reçu votre paiement.", 'happybreak'); ?></p>
    <p><?php _e("Si vous avez des questions, vous pouvez me contacter au 09 80 01 01 01 (appel non surtaxé).", 'happybreak'); ?></p>
<?php endif; ?>

<?php if ($order->get_payment_method() == 'bacs'): ?>

    <p><?php _e("Vous avez choisi de payer votre commande happybreak par virement, veuillez trouver ci-dessous les informations pour effectuer le paiement :", 'happybreak'); ?></p>
    <ol>
        <li><?php _e("Notez le numéro de commande et votre nom dans les références de l’opération", 'happybreak'); ?></li>
        <li>
            <?php _e("Utilisez le RIB suivant", 'happybreak'); ?> :
            <br/><?php _e("Domiciliation : HSBC FR LYON OUEST CAE", 'happybreak'); ?>
            <br/>
            <br/><?php _e("Code banque : 30056", 'happybreak'); ?>
            <br/><?php _e("Code guichet : 00964", 'happybreak'); ?>
            <br/><?php _e("Numéro de compte : 09640024731", 'happybreak'); ?>
            <br/><?php _e("Clé RIB : 9", 'happybreak'); ?>
        </li>
        <li><?php _e("Expédiez-le à l'adresse suivante : Bloom, 213 rue de Gerland, bâtiment B4, 69007 Lyon", 'happybreak'); ?></li>
    </ol>
    <p><?php _e("Attention : votre carte ne sera expédiée que quand votre virement sera accepté. Cette démarche peut prendre 2 ou 3 jours.", 'happybreak'); ?></p>

<?php elseif ($order->get_payment_method() == 'cheque'): ?>

    <p><?php _e("Vous avez choisi de payer votre commande happybreak par chèque, veuillez trouver ci-dessous les informations pour effectuer le paiement :", 'happybreak'); ?></p>
    <ol>
        <li><?php _e("Libellez le chèque en euros à l'ordre de : BLOOM SAS.", 'happybreak'); ?></li>
        <li><?php _e("Notez au dos du chèque le numéro de commande (pas sur la partie détachable).", 'happybreak'); ?></li>
        <li><?php _e("Expédiez-le à l'adresse suivante : Bloom, 213 rue de Gerland, bâtiment B4, 69007 Lyon", 'happybreak'); ?></li>
    </ol>
    <p><?php _e("Attention : votre commande ne sera expédiée que quand votre chèque sera encaissé. Cette démarche peut prendre une quinzaine de jours.", 'happybreak'); ?></p>

<?php endif; ?>

<?php

/**
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 * @since 2.5.0
 */
do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);

/**
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email);

/**
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);

/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action('woocommerce_email_footer', $email);
