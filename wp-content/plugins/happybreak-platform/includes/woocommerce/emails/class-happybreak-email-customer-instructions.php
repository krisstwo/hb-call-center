<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

define('PRODUCT_PRIVILEGE_INSTRUCTION_TAG_SLUG', 'instructions-privilege');
define('PRODUCT_3MOIS_INSTRUCTION_TAG_SLUG', 'instructions-3-mois');
define('PRODUCT_QTE_1_TAG_SLUG', 'qte-1');

class Happybreak_Email_Customer_Instructions extends WC_Email
{

    /**
     * @var string
     */
    private $activationCode;

    /**
     * Constructor.
     */
    public function __construct()
    {

        $this->id = 'customer_instructions';
        $this->customer_email = true;

        $this->title = __('Email d\'instructions', 'happybreak');
        $this->description = __('Email d\'instructions suite à la confirmation de commande', 'happybreak');

        $this->template_plain = null;

        // Triggers for this email
        add_action('woocommerce_order_status_completed_notification', array($this, 'trigger'), 10, 2);

        // Call parent constuctor
        parent::__construct();
    }

    /**
     * Trigger the sending of this email.
     *
     * @param int $order_id The order ID.
     * @param WC_Order $order Order object.
     */
    public function trigger($order_id, $order = false)
    {
        global  $wpdb;

        if ($order_id && !is_a($order, 'WC_Order')) {
            $order = wc_get_order($order_id);
        }

        if (!$this->is_enabled()) {
            return;
        }

        $productItems = $order->get_items();
        if (!count($productItems))
            return;

        //load product instruction tags
        $privilegeTerm = get_term_by('slug', PRODUCT_PRIVILEGE_INSTRUCTION_TAG_SLUG, 'product_tag');
        $mois3Term     = get_term_by('slug', PRODUCT_3MOIS_INSTRUCTION_TAG_SLUG, 'product_tag');
        $qte1Term      = get_term_by('slug', PRODUCT_QTE_1_TAG_SLUG, 'product_tag');

        foreach ($productItems as $productItem) {
            $product = $productItem->get_product();

            //setup data for this round
            if ($privilegeTerm && in_array($privilegeTerm->term_id, $product->get_tag_ids())) {

                $this->template_html = 'emails/customer-instructions-privilege.php';
                $this->subject = __('Utilisez votre carte privilège Happybreak', 'happybreak');

            } elseif ($mois3Term && in_array($mois3Term->term_id, $product->get_tag_ids())) {

                //TODO: go back to normal qty ? getting too wiered (client asked for 2 pieces as 1)
                $implicitQty = 2;
                if ($qte1Term && in_array($qte1Term->term_id, $product->get_tag_ids())) {
                    $implicitQty = 1;
                }

                $this->template_html = 'emails/customer-instructions-3mois.php';
                $this->subject = __('Utilisez votre e-carte 3 mois Happybreak', 'happybreak');

                //activation code logic
                $activationCodeMeta = $order->get_meta('activation_code_3mois', false);
                //fetch a new activation code only first time
                if (empty($activationCodeMeta)) {

                    $wpdb->query('LOCK TABLE activation_codes WRITE');
                    $rows = $wpdb->get_results('SELECT * FROM activation_codes WHERE is_used != 1 LIMIT ' . $implicitQty);

                    if (count($rows) < $implicitQty) {
                        wp_mail($this->get_option('admin_email'), '[ACTION REQUIRED] Codes 3 mois',
                            'Nombre restant insuffisant. Commande concernée : ' . $order->get_id());

                        $wpdb->query('UNLOCK TABLES');

                        return;
                    }

                    $this->activationCode = array();
                    foreach ($rows as $row) {
                        $this->activationCode[] = $row->Numerocarte;

                        //mark codes as used
                        $wpdb->update('activation_codes', array('is_used' => 1), array('carteund' => $row->carteund));
                    }

                    $wpdb->query('UNLOCK TABLES');

                    //save codes to order
                    foreach ($rows as $row) {
                        $order->add_meta_data('activation_code_3mois', $row->Numerocarte);
                    }

                    $order->save_meta_data();

                }else {
                    $this->activationCode = array();
                    foreach ($activationCodeMeta as $code) {
                        $this->activationCode[] = $code->value;
                    }
                }
            } else {
                continue;
            }

            //send this round
            $this->sendForProduct($product, $order);
        }

    }

    /**
     * Get content html.
     *
     * @access public
     * @return string
     */
    public function get_content_html()
    {
        return wc_get_template_html($this->template_html, array(
            'order' => $this->object,
            'activation_code' => $this->activationCode,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => false,
            'plain_text' => false,
            'email' => $this,
        ));
    }

    private function sendForProduct($product, $order)
    {
        if (is_a($order, 'WC_Order')) {
            $this->object = $order;
            $this->recipient = $this->object->get_billing_email();

            $this->find['order-date'] = '{order_date}';
            $this->find['order-number'] = '{order_number}';

            $this->replace['order-date'] = wc_format_datetime($this->object->get_date_created());
            $this->replace['order-number'] = $this->object->get_order_number();
        }

        $this->setup_locale();
        $this->send($this->get_recipient(), $this->subject, $this->get_content(), $this->get_headers(), $this->get_attachments());
        $this->restore_locale();
    }
}