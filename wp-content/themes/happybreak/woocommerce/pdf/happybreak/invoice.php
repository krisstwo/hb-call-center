<?php do_action( 'wpo_wcpdf_before_document', $this->type, $this->order ); ?>

<table class="head container">
	<tr>
		<td class="header">
		<?php
		if( $this->has_header_logo() ) {
			$this->header_logo();
		} else {
			echo apply_filters( 'wpo_wcpdf_invoice_title', __( 'Invoice', 'woocommerce-pdf-invoices-packing-slips' ) );
		}
		?>
		</td>
	</tr>
    <tr>
        <td>
            <div class="shop-address"><?php $this->shop_address(); ?></div>
        </td>
    </tr>
</table>

<?php do_action( 'wpo_wcpdf_after_document_label', $this->type, $this->order ); ?>

<table class="order-data-addresses">
	<tr>
        <?php do_action( 'wpo_wcpdf_before_order_data', $this->type, $this->order ); ?>
		<td class="address billing-address">

            <?php if ( isset($this->settings['display_number']) ) { ?>
            <tr>
            <td>
                <?php _e( 'N° de facture', 'woocommerce-pdf-invoices-packing-slips' ); ?> :
              <span class="color_custom_info">
                  THB<?= str_pad($this->get_invoice_number(), 4, '0', STR_PAD_LEFT); ?>
              </span>
            </td>
            </tr>
            <?php } ?>
            <?php if ( isset($this->settings['display_date']) ) { ?>
                <td height="30">
                    <?php _e( 'Facture émise le', 'woocommerce-pdf-invoices-packing-slips' ); ?> :
                    <span class="color_custom_info">
                        <?php $this->invoice_date(); ?>
                   <span>
                </td>
            <?php } ?>

				<tr class="order-number">
					<td>
                        <?php _e( 'N° de commande', 'woocommerce-pdf-invoices-packing-slips' ); ?> :
                        <span class="color_custom_info">
                        T<?= str_pad($this->get_order_number(), 4, '0', STR_PAD_LEFT); ?>
                        <span>
                    </td>
				</tr>
				<tr class="order-date">
					<td>
                        <?php _e( 'Date de la commande', 'woocommerce-pdf-invoices-packing-slips' ); ?> :
                        <span class="color_custom_info">
                            <?php $this->order_date(); ?>
                         </span>
                    </td>
				</tr>
				<tr class="payment-method">
					<td>
                        <?php _e( 'Moyen de paiement', 'woocommerce-pdf-invoices-packing-slips' ); ?>
                        <span class="color_custom_info">
                            <?php $this->payment_method(); ?>
                         </span>
                    </td>
				</tr>
				<?php do_action( 'wpo_wcpdf_after_order_data', $this->type, $this->order ); ?>
		</td>
	</tr>
</table>

<?php do_action( 'wpo_wcpdf_before_order_details', $this->type, $this->order ); ?>
<table class="recap_order">
    <tbody>
    <tr>
        <td>
            <h1 class="no-margin"> <?php _e( 'Votre commande :', 'woocommerce-pdf-invoices-packing-slips' ); ?></h1>
            <br/>
            <div class="produc_form">
                <?php $items = $this->get_order_items();
                if (sizeof($items) > 0) : foreach ($items as $item_id => $item) : ?>
                  <p>
                      <?php echo $item['quantity']; ?> * <?php echo $item['name']; ?>  <?php $description_label = __( 'Description', 'woocommerce-pdf-invoices-packing-slips' ); // registering alternate label translation ?>
                      = <?php echo $item['order_price']; ?> <?= __('TTC', 'happybreak'); ?>
                      (<?php echo $item['line_total']; ?> <?= __('HT', 'happybreak'); ?>)
                      <br/><?= __('Dont TVA', 'happybreak'); ?> <?php echo $item['tax_rates']; ?>
                      = <?php echo $item['line_tax']; ?>
                      	<?php do_action( 'wpo_wcpdf_before_item_meta', $this->type, $item, $this->order  ); ?>
				<span class="item-meta"><?php echo $item['meta']; ?></span>
				<dl class="meta">
					<?php $description_label = __( 'SKU', 'woocommerce-pdf-invoices-packing-slips' ); // registering alternate label translation ?>
                    <?php if( !empty( $item['sku'] ) ) : ?><dt class="sku"><?php _e( 'SKU:', 'woocommerce-pdf-invoices-packing-slips' ); ?></dt><dd class="sku"><?php echo $item['sku']; ?></dd><?php endif; ?>
                    <?php if( !empty( $item['weight'] ) ) : ?><dt class="weight"><?php _e( 'Weight:', 'woocommerce-pdf-invoices-packing-slips' ); ?></dt><dd class="weight"><?php echo $item['weight']; ?><?php echo get_option('woocommerce_weight_unit'); ?></dd><?php endif; ?>
				</dl>
				<?php do_action( 'wpo_wcpdf_after_item_meta', $this->type, $item, $this->order  ); ?>
                  </p>
                <?php endforeach; endif; ?>
            </div>

            <?php if (strpos($this->get_order_shipping()['value'], '0,00') === false): ?>
                <div class="shipping-items">

                    <?= __('Livraison', 'happybreak'); ?> <?= $this->get_order_shipping()['value']; ?> <?= __('TTC', 'happybreak'); ?>
                    <br/><?= __('Dont TVA', 'happybreak'); ?> <?= $this->get_order_shipping()['tax']; ?>
                    <br/>&nbsp;
                </div>
            <?php else : ?>
                <div class="shipping-items">

                    <?= __('Livraison', 'happybreak'); ?> <?= __('gratuite', 'happybreak'); ?>
                    <br/>&nbsp;
                </div>
            <?php endif; ?>

            <div class="note_customer">
                <div class="customer-notes">
                    <?php do_action( 'wpo_wcpdf_before_customer_notes', $this->type, $this->order ); ?>
                    <?php if ( $this->get_shipping_notes() ) : ?>
                        <h3><?php _e( 'Customer Notes', 'woocommerce-pdf-invoices-packing-slips' ); ?></h3>
                        <?php $this->shipping_notes(); ?>
                    <?php endif; ?>
                    <?php do_action( 'wpo_wcpdf_after_customer_notes', $this->type, $this->order ); ?>
                </div>
            </div>

            <div class="total_order">

                   <p>
                       <?= __('Total de la commande', 'happybreak'); ?>
                       : <?= $this->format_price($this->order->get_total()); ?>
                       <?php
                       foreach ($this->order->get_tax_totals() as $code => $tax) : ?>
                           <br/><?= __('Dont TVA', 'happybreak'); ?> <?= $this->get_tax_rate_by_id($tax->rate_id); ?> % = <?= $tax->formatted_amount; ?>
                       <?php endforeach; ?>
                   </p>
            </div>

        </td>
    </tr>


    <tr class="billing-address">
        <td>
             <h1 class="no-margin"><?php _e( 'VOTRE ADRESSE DE LIVRAISON', 'woocommerce-pdf-invoices-packing-slips' ); ?></h1>

                <?php $this->shipping_address(); ?>
        </td>
    </tr>

    <tr class="last_order_detail shipping-address">
        <td>
            <h1 class="no-margin"><?php _e( 'VOTRE ADRESSE DE FACTURATION', 'woocommerce-pdf-invoices-packing-slips' ); ?></h1>
            <?php $this->billing_address(); ?>
            <?php if ( isset($this->settings['display_email']) ) { ?>
                <div class="billing-email"><?php $this->billing_email(); ?></div>
            <?php } ?>
            <?php if ( isset($this->settings['display_phone']) ) { ?>
                <div class="billing-phone"><?php $this->billing_phone(); ?></div>
            <?php } ?>
        </td>
    </tr>




    </tbody>

</table>



<?php do_action( 'wpo_wcpdf_after_order_details', $this->type, $this->order ); ?>

<?php if ( $this->get_footer() ): ?>
<div id="footer">
	<?php $this->footer(); ?>
</div><!-- #letter-footer -->
<?php endif; ?>
<?php do_action( 'wpo_wcpdf_after_document', $this->type, $this->order ); ?>
