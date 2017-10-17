<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

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

        $productItems = $order->get_items();
        if (!count($productItems))
            return;

        foreach ($productItems as $productItem) {
            $product = $productItem->get_product();

            if ($product->get_slug() === 'carte-privilege-1-an-2-pour-le-prix-dune') {
                $this->template_html = 'emails/customer-instructions-privilege.php';
                $this->subject = __('Utiliser votre carte privilège Happybreak', 'happybreak');
            } elseif ($product->get_slug() === 'carte-3-mois-2-pour-le-prix-dune') {
                $this->template_html = 'emails/customer-instructions-3mois.php';
                $this->subject = __('Utiliser votre e-carte 3 mois Happybreak', 'happybreak');

                //activation code logic
                $activationCode = $order->get_meta('activation_code_3mois', true);
                //fetch a new activation code only first time
                if (empty($activationCode)) {
                    $row = $wpdb->get_row('SELECT * FROM activation_codes WHERE is_used != 1');
                    $this->activationCode = $activationCode = $row->Numerocarte;

                    $order->add_meta_data('activation_code_3mois', $activationCode);
                    $order->save_meta_data();

                    $wpdb->update('activation_codes', array('is_used' => 1), array('carteund' => $row->carteund));
                }else {
                    $this->activationCode = $activationCode;
                }
            } else {
                return;
            }
            //no logic on multiple products
            break;
        }

        if (is_a($order, 'WC_Order')) {
            $this->object = $order;
            $this->recipient = $this->object->get_billing_email();

            $this->find['order-date'] = '{order_date}';
            $this->find['order-number'] = '{order_number}';

            $this->replace['order-date'] = wc_format_datetime($this->object->get_date_created());
            $this->replace['order-number'] = $this->object->get_order_number();
        }

        if (!$this->is_enabled() || !$this->get_recipient()) {
            return;
        }

        $this->setup_locale();
        $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
        $this->restore_locale();
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
}