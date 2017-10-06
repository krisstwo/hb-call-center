<?php
/**
 * Email Addresses
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-addresses.php.
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

?>			<h3 style="color: #000000; font-weight: 800"><?php _e( 'Billing address', 'woocommerce' ); ?></h3>

			<p class="text"><?php echo $order->get_formatted_billing_address(); ?>
            <?php echo '<br>'. $order->billing_additional_address .'</br><br> '.$order->billing_phone.'</br>'; ?>
            </p>
		<?php if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() && ( $shipping = $order->get_formatted_shipping_address() ) ) : ?>
				<h3 style="color: #000000; font-weight: 800"><?php _e( 'Shipping address', 'woocommerce' ); ?></h3>

				<p class="text"><?php echo $shipping; ?></p>
		<?php endif; ?>
