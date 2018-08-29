<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


class Happybreak_Meta_Box_Order_Items_Modal
{

    /**
     * Output the metabox.
     *
     * @param WP_Post $post
     */
    public static function output($post)
    {
        global $theorder;

        // This is used by some callbacks attached to hooks such as woocommerce_order_actions which rely on the global to determine if actions should be displayed for certain orders.
        if (!is_object($theorder)) {
            $theorder = wc_get_order($post->ID);
        }
        ?>
        <script type="text/template" id="tmpl-wc-modal-hb-add-products">
            <div class="wc-backbone-modal">
                <div class="wc-backbone-modal-content">
                    <section class="wc-backbone-modal-main" role="main">
                        <header class="wc-backbone-modal-header">
                            <h1><?php _e( 'Add products', 'woocommerce' ); ?></h1>
                            <button class="modal-close modal-close-link dashicons dashicons-no-alt">
                                <span class="screen-reader-text">Close modal panel</span>
                            </button>
                        </header>
                        <article>
                            <?php
                            $products = wc_get_products(array(
                                'status' => array('publish'),
                                'orderby' => 'ID',
                                'order' => 'ASC'
                            ));
                            ?>
                            <form>
                            <?php foreach ($products as $i => $product): ?>
                                <p>
                                    <input type="radio" id="add_order_items_<?php echo $i; ?>" name="add_order_items" value="<?php echo $product->get_ID(); ?>" /> <label for="add_order_items_<?php echo $i; ?>"><?php echo $product->get_title(); ?></label>
                                </p>
                            <?php endforeach; ?>
                            </form>
                        </article>
                        <footer>
                            <div class="inner">
                                <button id="btn-ok" class="button button-primary button-large"><?php _e( 'Add', 'woocommerce' ); ?></button>
                            </div>
                        </footer>
                    </section>
                </div>
            </div>
            <div class="wc-backbone-modal-backdrop modal-close"></div>
        </script>
        <?php
    }
}
