<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class Happybreak_Meta_Box_Order_Actions extends WC_Meta_Box_Order_Actions {

	/**
	 * Output the metabox.
	 *
	 * @param WP_Post $post
	 */
	public static function output( $post ) {
		global $theorder;

		// This is used by some callbacks attached to hooks such as woocommerce_order_actions which rely on the global to determine if actions should be displayed for certain orders.
		if ( ! is_object( $theorder ) ) {
			$theorder = wc_get_order( $post->ID );
		}
		?>
		<ul class="order_actions submitbox">

			<?php do_action( 'woocommerce_order_actions_start', $post->ID ); ?>

			<li class="wide">
				<input type="submit" class="button save_order button-primary" name="save" value="<?php echo 'auto-draft' === $post->post_status ? esc_attr__( 'Create', 'woocommerce' ) : esc_attr__( 'Update', 'woocommerce' ); ?>" />
			</li>

			<?php do_action( 'woocommerce_order_actions_end', $post->ID ); ?>

		</ul>
		<?php
	}
}
