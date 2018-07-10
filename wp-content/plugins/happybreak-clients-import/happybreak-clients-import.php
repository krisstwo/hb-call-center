<?php
/*
Plugin Name: Import client
Plugin URI: http://coffeeandbrackets.com
Description: Un plugin import client csv to db
Version: 0.1
Author: Coffeeandbrackets.com
Author URI: http:/coffeeandbrackets.com
License: v1
*/

//TODO: plugin to be renamed
//TODO: code to be organised

define('CALL_CENTER_AGENT_ROLE', 'call_center_agent');
define('CALL_CENTER_SUPER_AGENT_ROLE', 'call_center_super_agent');
define('ORDER_CALL_CENTER_AGENT_USER_ID', 'call_center_agent_user_id');
define('PRODUCT_SHIPPING_TAG_SLUG', 'livraison');


/**
 * redirect user not login
 * admin can logged
 * show only for user connected
 */
function redirect_guest()
{
    global $wp;

    if (empty($_GET['pay_for_order']) && empty($wp->query_vars['order-received']) && !is_page('thank-you') && !is_user_logged_in() && !is_super_admin(get_current_user_id()) && !members_current_user_has_role(CALL_CENTER_AGENT_ROLE) && !members_current_user_has_role(CALL_CENTER_SUPER_AGENT_ROLE)) {
        wp_redirect(' https://www.happybreak.com');
        exit();
    }

}

add_action('template_redirect', 'redirect_guest');

/**
 * add id teleoperateur
 * @param $order_id
 * @return bool
 */
function happybreak_assign_teleopertor_to_order($order_id)
{

    $order = wc_get_order($order_id);

    //only stamp with current user if meta left empty, this will prevent admin from hijacking orders he edits
    if(!empty($order->get_meta(ORDER_CALL_CENTER_AGENT_USER_ID, true)))
        return;

    $order->update_meta_data(ORDER_CALL_CENTER_AGENT_USER_ID, get_current_user_id());
    $order->save();

    return true;
}

add_action('save_post_shop_order', 'happybreak_assign_teleopertor_to_order');

/**
 * custom folter teleoperateur
 * @param $post_type
 */
function happybreak_custom_orders_filters($post_type)
{
    if ($post_type == 'shop_order' && is_super_admin(get_current_user_id())) {

        $usersTeleOp = get_users('blog_id=1&orderby=nicename&role=' . CALL_CENTER_AGENT_ROLE);
        echo '<select name="agent_filter">';
        ?>
        <option value><?php _e('Tous les Téléopérateurs'); ?></option>
        <?php
        if (!empty($usersTeleOp)) {
            foreach ($usersTeleOp as $row) :
                echo '<option value="' . $row->ID . '">' . $row->user_login . '  -  ' . $row->user_email . ' </option>';
            endforeach;
        }

        echo '</select>';

    }

}

add_action('restrict_manage_posts', 'happybreak_custom_orders_filters');

/**
 * update query search
 * @param $query
 * @return mixed
 */
function filter_teleoperateur_admin($query)
{

    if ($_GET['agent_filter'] && is_super_admin(get_current_user_id())) {
        $query->set('meta_key', ORDER_CALL_CENTER_AGENT_USER_ID);
        $query->set('meta_value', $_GET['agent_filter']);
    }
    return $query;
}

add_filter('parse_query', 'filter_teleoperateur_admin');

/**
 *
 * @param $where
 * @param $query
 * @return mixed
 */
function happybreak_force_filter_orders_by_agent($where, $query)
{
    global $wpdb;
    $currentUserId = get_current_user_id();
    if (is_admin() && !is_super_admin($currentUserId) && !members_current_user_has_role(CALL_CENTER_SUPER_AGENT_ROLE) && strpos($query->query['post_type'], 'order') !== false) {
        $where .= " AND EXISTS (SELECT * FROM {$wpdb->postmeta} pm_agent WHERE pm_agent.post_id = {$wpdb->posts}.ID AND pm_agent.meta_key = '" . ORDER_CALL_CENTER_AGENT_USER_ID . "' AND pm_agent.meta_value = '$currentUserId')";
    }

    return $where;
}

add_filter('posts_where', 'happybreak_force_filter_orders_by_agent', 10, 2);
add_filter('posts_where_request', 'happybreak_force_filter_orders_by_agent', 10, 2);
add_filter('posts_where_paged', 'happybreak_force_filter_orders_by_agent', 10, 2);

function happybreak_allow_own_order_edit_only($allcaps, $caps, $args, $user)
{
    //admin or super agent pass
    if (!empty($allcaps['delete_users']) || members_current_user_has_role(CALL_CENTER_SUPER_AGENT_ROLE))
        return $allcaps;

    //Deny, allow
    unset($allcaps['edit_others_shop_orders']);

    /**
     * @var $user WP_User
     */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['post_ID'];
        $action = $_POST['action'];
    } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $id = $_GET['post'];
        $action = $_GET['action'];
    }

    if (in_array($action, array('edit', 'editpost')) && (int)get_post_meta($id, ORDER_CALL_CENTER_AGENT_USER_ID, true) === get_current_user_id()) {
        $allcaps = array_merge($allcaps, array('edit_others_shop_orders' => true));
    }

    return $allcaps;
}

add_filter('user_has_cap', 'happybreak_allow_own_order_edit_only', 10, 4);

function happybreak_disallow_product_edit_for_nonadmins($allcaps, $caps, $args, $user)
{
    //admin or super agent pass
    if (!empty($allcaps['delete_users']))
        return $allcaps;

    /**
     * @var $user WP_User
     */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['post_ID'];
        $action = $_POST['action'];
    } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $id = $_GET['post'];
        $action = $_GET['action'];
    }

    if (in_array($action, array('edit', 'editpost')) && get_post_type($id) === 'product') {
        unset($allcaps['edit_product']);
        unset($allcaps['edit_others_products']);
        unset($allcaps['edit_published_products']);
    }

    return $allcaps;
}

add_filter('user_has_cap', 'happybreak_disallow_product_edit_for_nonadmins', 10, 4);

/**
 * redirect user to custom page afetr payement
 */
add_action('woocommerce_thankyou', function ($order_id) {

    wp_safe_redirect(get_permalink(get_page_by_path('thank-you')));


});

function happybreak_add_guest_caps($allcaps, $caps, $args, $user)
{
    /**
     * @var $user WP_User
     */
    if ((int)$user->ID != 0 && !members_current_user_has_role(CALL_CENTER_AGENT_ROLE) && !members_current_user_has_role(CALL_CENTER_SUPER_AGENT_ROLE) && empty($allcaps['delete_users']))
        return $allcaps;

    return array_merge($allcaps, array('pay_for_order' => true));
}

add_filter('user_has_cap', 'happybreak_add_guest_caps', 10, 4);

function happybreak_remove_metabox_for_nonadmins()
{
    global $wp_meta_boxes, $post;

    $order = wc_get_order($post->ID);

    if(!is_super_admin(get_current_user_id())){
        remove_meta_box( 'postcustom' , 'shop_order' , 'normal' );
    }

    //remove unused metaboxes
    remove_meta_box('woocommerce-order-downloads', 'shop_order', 'normal');
    remove_meta_box('wpo_wcpdf-data-input-box', 'shop_order', 'normal');
    remove_meta_box('wpo_wcpdf-box', 'shop_order', 'side');

    require_once 'includes/woocommerce/admin/meta-boxes/class-happybreak-meta-box-order-actions.php';
    require_once 'includes/woocommerce/admin/meta-boxes/class-happybreak-meta-box-order-payment.php';
    require_once 'includes/woocommerce/admin/meta-boxes/class-happybreak-meta-box-order-items-modal.php';

    //relocate order metaboxes
    $wp_meta_boxes['shop_order']['normal']['low']['woocommerce-order-actions'] = $wp_meta_boxes['shop_order']['side']['high']['woocommerce-order-actions'];
    unset($wp_meta_boxes['shop_order']['side']['high']['woocommerce-order-actions']);
    $wp_meta_boxes['shop_order']['normal']['low']['woocommerce-order-actions']['callback'] = 'Happybreak_Meta_Box_Order_Actions::output';
    if($order->needs_payment())
        add_meta_box('woocommerce-order-payment', '4. ' . __('Mode paiement', 'happybreak'), 'Happybreak_Meta_Box_Order_Payment::output', 'shop_order', 'normal', 'low');

    add_meta_box('woocommerce-items-modal', 'woocommerce-items-modal', 'Happybreak_Meta_Box_Order_Items_Modal::output', 'shop_order', 'normal', 'high');


    //change order metaboxes titles
    $wp_meta_boxes['shop_order']['normal']['high']['woocommerce-order-data']['title'] = '1. ' . __('Je recherche mon client', 'happybreak');
    $wp_meta_boxes['shop_order']['normal']['high']['woocommerce-order-items']['title'] = '2. ' . __('Je sélectionne mon panier', 'happybreak');
    $wp_meta_boxes['shop_order']['normal']['low']['woocommerce-order-actions']['title'] = '3. ' . __('Je crée la commande', 'happybreak');
}

add_action('add_meta_boxes_shop_order', 'happybreak_remove_metabox_for_nonadmins', 999);

/**
 * add aditionel_adress to edit form
 * @param $fields
 * @return mixed
 */
function happybreak_add_additional_address_to_user_edit_form($fields)
{
    $additional = array(
        'label' => __("Complément d'adresse", 'happybreak'),
        'required' => false
    );
    // set information after adress 2
    $fields['billing']['fields'] = array_slice($fields['billing']['fields'], 0, 5, true) +
        array("additional_address" => $additional) +
        array_slice($fields['billing']['fields'], 1, count($fields['billing']['fields']) - 1, true) ;

    $fields['billing']['fields']['billing_additional_phone'] = array(
        'label' => __("Phone", 'woocommerce') . ' 2',
        'required' => false
    );



    $additional = array(
        'label' => __("Complément d'adresse", 'happybreak'),
        'required' => false
    );
    // set information after adress 2
    $fields['shipping']['fields'] = array_slice($fields['shipping']['fields'], 0, 6, true) +
        array("shipping_additional_address" => $additional) +
        array_slice($fields['shipping']['fields'], 1, count($fields['shipping']['fields']) - 1, true) ;


    $fields['shipping']['fields']['shipping_phone'] = array(
        'label' => __("Phone", 'woocommerce'),
        'required' => false
    );

    $fields['shipping']['fields']['shipping_additional_phone'] = array(
        'label' => __("Phone", 'woocommerce') . ' 2',
        'required' => false
    );

    return $fields;
}

add_filter('woocommerce_customer_meta_fields', 'happybreak_add_additional_address_to_user_edit_form');

/**
 * add fiel to admin order form biiling #billing_additional_address
 * @param array $fields
 * @return array
 */
function happybreak_add_adress_to_field_order_admin(array $fields){
    $aditionel= array(
        'label' => __("Complément d'adresse", 'woocommerce'),
        'required' => false
    );
    // set information after adress 2
    $fields = array_slice($fields, 0, 5, true) +
        array("additional_address" => $aditionel) +
        array_slice($fields, 1, count($fields) - 1, true);

    $fields['additional_phone'] = array(
        'label' => __("Phone", 'woocommerce') . ' 2',
        'required' => false
    );

    $fields['first_name']['custom_attributes'] = array('required' => true);
    $fields['last_name']['custom_attributes'] = array('required' => true);
    $fields['address_1']['custom_attributes'] = array('required' => true);
    $fields['city']['custom_attributes'] = array('required' => true);
    $fields['postcode']['custom_attributes'] = array('required' => true);
    $fields['email']['custom_attributes'] = array('required' => true);
    $fields['phone']['custom_attributes'] = array('required' => true);


    return $fields;
}
add_filter('woocommerce_admin_billing_fields', 'happybreak_add_adress_to_field_order_admin');

/**
 * add fiel to admin order form shipping #shipping_phone
 * @param array $fields
 * @return array
 */
function happybreak_add_phone_to_field_order_admin(array $fields){
    $additional = array(
        'label' => __("Complément d'adresse", 'woocommerce'),
        'required' => false
    );
    // set information after adress 2
    $fields = array_slice($fields, 0, 5, true) +
        array("additional_address" => $additional) +
        array_slice($fields, 1, count($fields) - 1, true) ;

    $fields['phone'] = array(
        'label' => __("Phone", 'woocommerce'),
        'required' => true
    );

    $fields['additional_phone'] = array(
        'label' => __("Phone", 'woocommerce') . ' 2',
        'required' => false
    );

    $fields['first_name']['custom_attributes'] = array('required' => true);
    $fields['last_name']['custom_attributes'] = array('required' => true);
    $fields['address_1']['custom_attributes'] = array('required' => true);
    $fields['city']['custom_attributes'] = array('required' => true);
    $fields['postcode']['custom_attributes'] = array('required' => true);

    return $fields;
}
add_filter('woocommerce_admin_shipping_fields', 'happybreak_add_phone_to_field_order_admin');

function happybreak_add_extrafields_to_customer_details($data)
{
    $extraFields = array_filter($data['meta_data'], function ($item) {
        return in_array($item->key, array('billing_additional_phone', 'shipping_additional_phone'));
    });
    if (!empty($extraFields)) {
        foreach ($extraFields as $field) {
            if (strpos($field->key, 'billing_') === 0) {
                $data['billing'][str_replace('billing_', '', $field->key)] = $field->value;
            } elseif (strpos($field->key, 'shipping_') === 0) {
                $data['shipping'][str_replace('shipping_', '', $field->key)] = $field->value;
            }
        }
    }

    return $data;
}

add_filter('woocommerce_ajax_get_customer_details', 'happybreak_add_extrafields_to_customer_details');

/**
 * add custom field to shipping adress
 * invoice pdf
 * @param $shipping_address
 * @return string
 */
function happybreak_add_custom_field_shipping_to_template_mail_invocie($shipping_address) {
    $order_id=$_GET['order_ids'];
    if(empty($order_id))
        return $shipping_address;

    $order = new WC_Order($order_id);
    if(!$order)
        return $shipping_address;

    $adress='<p>'. $order->shipping_additional_address .'<br> '.$order->shipping_phone.'</br></p>';

    return $shipping_address.$adress;
}
add_filter( 'wpo_wcpdf_shipping_address', 'happybreak_add_custom_field_shipping_to_template_mail_invocie' );

/**
* add custom field to biiling adress
* invoice pdf
* @param $shipping_address
* @return string
*/
function happybreak_add_custom_field_billing_to_template_mail_invocie($shipping_address) {
    $order_id=$_GET['order_ids'];
    if(empty($order_id))
        return $shipping_address;

    $order = new WC_Order($order_id);
    if(!$order)
        return $shipping_address;

    $adress='<p>'. $order->billing_additional_address .'<br> '.$order->billing_phone.'</br></p>';

    return $shipping_address.$adress;
}
add_filter( 'wpo_wcpdf_billing_address', 'happybreak_add_custom_field_billing_to_template_mail_invocie' );

function happybreak_search_customer_by_phone($query_args, $term)
{
    if (strpos($term, 't ') !== 0)
        return $query_args;

    $query_args['search_columns'] = array();

    $query_args['meta_query'] = array(
            'relation' => 'OR'
    );

    $query_args['orderby'] = 'NULL';

    $query_args['meta_query'][] = array(
        'key' => 'billing_phone',
        'value' => substr($term, 2),
        'compare' => '=',
    );

    $query_args['meta_query'][] = array(
        'key' => 'shipping_phone',
        'value' => substr($term, 2),
        'compare' => '=',
    );

    $query_args['meta_query'][] = array(
        'key' => 'billing_additional_phone',
        'value' => substr($term, 2),
        'compare' => '=',
    );

    $query_args['meta_query'][] = array(
        'key' => 'shipping_additional_phone',
        'value' => substr($term, 2),
        'compare' => '=',
    );

    return $query_args;
}

add_filter('woocommerce_customer_search_customers', 'happybreak_search_customer_by_phone', 10, 2);

function remove_dashboard_widgets_for_nonadmins() {
    global $wp_meta_boxes;

    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_drafts']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);

    if (current_user_can('manage_options'))
        return;

    remove_meta_box( 'woocommerce_dashboard_status', 'dashboard', 'normal');
    remove_meta_box( 'woocommerce_dashboard_recent_reviews', 'dashboard', 'normal');
}

add_action('wp_dashboard_setup', 'remove_dashboard_widgets_for_nonadmins' );

function happybreak_hide_order_columns_for_nonadmins($columns)
{
    if (!is_super_admin(get_current_user_id())) {
        unset($columns['pdf_invoice_number']);
        unset($columns['billing_address']);
        unset($columns['shipping_address']);
        unset($columns['customer_message']);
        unset($columns['order_notes']);
    }

    //Add completed date column
    $columns['date_completed'] = 'Date "Terminée"';
    //Add agent column
    $columns['agent'] = 'Agent';

    return $columns;
}

add_filter('manage_edit-shop_order_columns', 'happybreak_hide_order_columns_for_nonadmins', 1000);

function happybreak_order_list_sortable_columns($columns)
{
    $columns['date_completed'] = 'date_completed';

    return $columns;
}

add_filter('manage_edit-shop_order_sortable_columns', 'happybreak_order_list_sortable_columns');

function happybreak_order_list_order_by($query)
{
    if ( ! is_admin()) {
        return;
    }

    $orderby = $query->get('orderby');

    if ('date_completed' == $orderby) {
        $query->set('meta_key', '_date_completed');
        $query->set('orderby', 'meta_value');
    }
}

add_action('pre_get_posts', 'happybreak_order_list_order_by');

function happybreak_order_list_date_completed_column($column)
{
    global $post;

    $order = wc_get_order($post->ID);

    if ($column == 'date_completed') {
        echo $order->get_date_completed() ? $order->get_date_completed()->format('d/m/Y H:i:s') : 'pas encore terminée';
    }


    if ($column == 'agent') {
        $agentId = $order->get_meta(ORDER_CALL_CENTER_AGENT_USER_ID, true);
        $user    = get_userdata($agentId);

        printf('%s: %s (login: %s)', $agentId, $user->display_name, $user->user_login);
    }
}

add_action('manage_shop_order_posts_custom_column', 'happybreak_order_list_date_completed_column');

function happybreak_register_expedited_order_status()
{
    register_post_status('wc-expedited', array(
        'label' => 'Expédiée',
        'public' => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list' => true,
        'exclude_from_search' => false,
        'label_count' => _n_noop('Expédiée <span class="count">(%s)</span>', 'Expédiées <span class="count">(%s)</span>')
    ));
}

add_action('init', 'happybreak_register_expedited_order_status');

function happybreak_add_expedited_to_order_statuses($orderStatuses)
{

    $newOrderStatuses = array();

    foreach ($orderStatuses as $key => $status) {

        $newOrderStatuses[$key] = $status;

        if ('wc-completed' === $key) {
            $newOrderStatuses['wc-expedited'] = 'Expediée';
        }
    }

    return $newOrderStatuses;
}

add_filter('wc_order_statuses', 'happybreak_add_expedited_to_order_statuses');

function happybreak_hide_pdf_actions_for_nonadmins($actions)
{
    if (!is_super_admin(get_current_user_id())) {
        return array();
    }

    return $actions;
}

add_filter('wpo_wcpdf_listing_actions', 'happybreak_hide_pdf_actions_for_nonadmins');

function happybreak_remove_finish_action_for_nonadmins($actions, $order)
{
    if (!is_super_admin(get_current_user_id())) {
        unset($actions['processing']);
        unset($actions['complete']);
    }

    return $actions;
}

add_filter('woocommerce_admin_order_actions', 'happybreak_remove_finish_action_for_nonadmins', 10, 2);

function happybreak_remove_orders_bulk_actions_for_nonadmins()
{
    if (!is_super_admin(get_current_user_id())) {
        add_filter('bulk_actions-edit-shop_order', '__return_empty_array');
    }
}

add_action('wp_loaded', 'happybreak_remove_orders_bulk_actions_for_nonadmins');

function happybreak_admin_styles()
{
    wp_enqueue_style('happybreak-admin-css', plugin_dir_url(__FILE__) . '/css/admin.css');
}

add_action('admin_print_styles', 'happybreak_admin_styles');

function happybreak_admin_scripts()
{
    wp_enqueue_script('happybreak-admin-js', plugin_dir_url(__FILE__) . '/js/admin.js');
}

add_action('admin_print_scripts', 'happybreak_admin_scripts');

/**
 * We already mark emails sent in happybreak_send_order_email but still need to do it for auto sent emails
 */
function happybreak_mark_on_hold_email_sent($order_id)
{
    $order = wc_get_order($order_id);

    if(!$order)
        return;

    if(!(int)$order->get_meta('customer_on_hold_order_sent', true)){
        $order->add_meta_data('customer_on_hold_order_sent', 1);
        $order->save_meta_data();
    }
}

add_action('woocommerce_order_status_pending_to_on-hold_notification', 'happybreak_mark_on_hold_email_sent');
add_action('woocommerce_order_status_failed_to_on-hold_notification', 'happybreak_mark_on_hold_email_sent');

function happybreak_send_order_email()
{
    $order = wc_get_order(absint($_GET['order_id']));
    $email = $_GET['email'];
    // Switch back to the site locale.
    wc_switch_to_site_locale();

    do_action('woocommerce_before_resend_order_emails', $order);

    // Ensure gateways are loaded in case they need to insert data into the emails.
    WC()->payment_gateways();
    WC()->shipping();

    // Load mailer.
    $mailer = WC()->mailer();
    $mails = $mailer->get_emails();

    if (!empty($mails)) {
        foreach ($mails as $mail) {
            if ($mail->id == $email) {
                $mail->trigger($order->get_id(), $order);
                if(!(int)$order->get_meta($mail->id . '_sent', true)){
                    $order->add_meta_data($mail->id . '_sent', 1);
                    $order->save_meta_data();
                }
                /* translators: %s: email title */
                $order->add_order_note(sprintf(__('%s email notification manually sent.', 'woocommerce'), $mail->title), false, true);
            }
        }
    }

    do_action('woocommerce_after_resend_order_email', $order, $email);

    // Restore user locale.
    wc_restore_locale();

    wp_safe_redirect(admin_url('edit.php?post_type=shop_order'));
}

add_action('wp_ajax_happybreak_send_order_email', 'happybreak_send_order_email');

function happybreak_add_order_email_actions($actions, WC_Order $order)
{
    if ($order->get_status() === 'on-hold') {
        $actions['on-hold-email'] = array(
            'url'       => admin_url( 'admin-ajax.php?order_id=' . $order->ID . '&action=happybreak_send_order_email&email=customer_on_hold_order' ),
            'name'      => __( 'Renvoyer la commande', 'happybreak' ),
            'action'    => 'on-hold-email',
        );
    } elseif ($order->needs_payment()) {
        $actions['pending-email'] = array(
            'url'       => admin_url( 'admin-ajax.php?order_id=' . $order->ID . '&action=happybreak_send_order_email&email=customer_invoice' ),
            'name'      => __( 'Renvoyer la commande', 'happybreak' ),
            'action'    => 'pending-email',
        );
    } else if ($order->get_status() === 'completed') {
        $actions['mark-expedited'] = array(
            'url' => wp_nonce_url(admin_url('admin-ajax.php?order_id=' . $order->ID . '&action=woocommerce_mark_order_status&status=expedited'), 'woocommerce-mark-order-status'),
            'name' => __('Marquer expédiée', 'happybreak'),
            'action' => 'mark-expedited',
        );
    }

    return $actions;
}

add_filter('woocommerce_admin_order_actions', 'happybreak_add_order_email_actions', 10, 2);

function happybreak_remove_order_status_for_nonadmins($statuses)
{
    global $post;

    //filter only on edit page, otherwise list won show complete
    if (is_admin() && $post && !is_super_admin(get_current_user_id())) {

        foreach ($statuses as $key => $status) {
            if($key != $post->post_status)
                unset($statuses[$key]);
        }
    }

    return $statuses;
}

add_filter('wc_order_statuses', 'happybreak_remove_order_status_for_nonadmins');

function happybreak_admin_role_body_class($classes)
{
    if (members_current_user_has_role(CALL_CENTER_AGENT_ROLE) || members_current_user_has_role(CALL_CENTER_SUPER_AGENT_ROLE))
        $classes .= ' role-agent';

    return $classes;
}

add_filter('admin_body_class', 'happybreak_admin_role_body_class');

function happybreak_admin_order_status_body_class($classes)
{
    $screen = get_current_screen();
    if ($screen->id === 'shop_order' && ! empty($_GET['post'])) {
        $order = wc_get_order($_GET['post']);

        if(!$order)
            return $classes;

        $classes .= ' order-status-' . $order->get_status();
    }

    return $classes;
}

add_filter('admin_body_class', 'happybreak_admin_order_status_body_class');

function happybreak_filter_available_payment_methods($availableMethods)
{
    if (!is_add_payment_method_page() && !is_super_admin(get_current_user_id()) && !members_current_user_has_role(CALL_CENTER_AGENT_ROLE) && !members_current_user_has_role(CALL_CENTER_SUPER_AGENT_ROLE)) {
        unset($availableMethods['bacs']);
        unset($availableMethods['cheque']);
    }

    return $availableMethods;
}

add_filter('woocommerce_available_payment_gateways', 'happybreak_filter_available_payment_methods');

function happybreak_force_add_shipping_after_add_item($item_id, $item)
{
    $order_id = absint($_POST['order_id']);
    $order = wc_get_order($order_id);

    if (!$order)
        return;

    //if already have shipping nothing to do
    if (count($order->get_shipping_methods()))
        return;

    $productItems = $order->get_items();
    foreach ($productItems as $item) {
        /**
         * @var $item WC_Order_Item_Product
         */
        /**
         * @var $product WC_Product
         */
        $product = $item->get_product();
        //product must has shipping tag
        $tagIds = $product->get_tag_ids();
        $shippingTerm = get_term_by('slug', PRODUCT_SHIPPING_TAG_SLUG, 'product_tag');

        if ($shippingTerm && in_array($shippingTerm->term_id, $tagIds)) {
            $shippingRate = new WC_Shipping_Rate();
            $shippingRate->cost = 2.9 * $item->get_quantity();
            $shippingItem = new WC_Order_Item_Shipping();
            $shippingItem->set_shipping_rate($shippingRate);
            $shippingItem->set_order_id($order_id);
            $shippingItem->save();
            $order = wc_get_order($order_id);
        }
    }
    
    $order->calculate_taxes();
    $order->calculate_totals(false);
}

add_action('woocommerce_saved_order_items', 'happybreak_force_add_shipping_after_add_item', 10, 2);

function happybreak_item_add_force_calcualte_texes($item, $itemId)
{
    $order_id = absint($_POST['order_id']);
    $order = wc_get_order($order_id);

    if(!$order)
        return $item;

    $order->calculate_taxes();
    $order->calculate_totals(false);

    return $order->get_item($itemId);
}

add_filter('woocommerce_ajax_order_item', 'happybreak_item_add_force_calcualte_texes', 10, 2);

add_filter('woocommerce_order_item_needs_processing', function () {
    return false;
});

function happybreak_on_hold_email_subject($subject, WC_Order $order)
{
    if ((int)$order->get_meta('customer_on_hold_order_sent', true)) {
        $subject = __("Information sur votre commande Happybreak", 'happybreak');
    } else {
        $subject = __("Votre commande Happybreak", 'happybreak');

        if ($order->get_payment_method() == 'bacs')
            $subject .= ' ' . __("par virement", 'happybreak');
        else if ($order->get_payment_method() == 'cheque')
            $subject .= ' ' . __("par chèque", 'happybreak');
    }

    return $subject;
}

add_filter('woocommerce_email_subject_customer_on_hold_order', 'happybreak_on_hold_email_subject', 10, 2);

function happybreak_add_instrunctions_email_class($emailClasses)
{
    require_once 'includes/woocommerce/emails/class-happybreak-email-customer-instructions.php';

    $emailClasses['Happybreak_Email_Customer_Instructions'] = new Happybreak_Email_Customer_Instructions();

    return $emailClasses;
}

add_filter('woocommerce_email_classes', 'happybreak_add_instrunctions_email_class');

/**
 * Remove WP user registration related user emails
 */
add_filter('send_password_change_email', '__return_false');
add_filter('send_email_change_email', '__return_false');
remove_action('after_password_reset', 'wp_password_change_notification');
remove_action('register_new_user', 'wp_send_new_user_notifications');
remove_action('edit_user_created_user', 'wp_send_new_user_notifications', 10, 2);

function happybreak_display_secured_payment_bloc()
{
    $pluginBaseUrl = plugin_dir_url(__FILE__);
    include_once 'templates/secure-payment.php';
    include_once 'templates/cc-type.php';
}

add_action('woocommerce_credit_card_form_start', 'happybreak_display_secured_payment_bloc');

function happybreak_display_braintree_secured_payment_bloc()
{
    $pluginBaseUrl = plugin_dir_url(__FILE__);
    include_once 'templates/braintree-secure-payment.php';
    include_once 'templates/braintree-cc-type.php';
}

add_filter( 'wc_braintree_credit_card_payment_form_description', 'happybreak_display_braintree_secured_payment_bloc');

/**
 * Notify admin of completed orders
 *
 * @param $orderId int
 * @param $order WC_Order
 */
function happybreak_notify_admin_of_completed_order($orderId, $order)
{
    $to   = get_bloginfo('admin_email');
    $body = "Bonjour,";
    $body .= "\n\nLa commande #$orderId du {$order->get_date_created()->format('Y/m/d H:i:s')} a été terminée.";
    $body .= "\n\nLien pour éditer la commnde : " . get_edit_post_link($orderId, null);
    wp_mail($to, "Commande #$orderId terminée", $body);
}

add_action('woocommerce_order_status_completed', 'happybreak_notify_admin_of_completed_order', 10, 2);

function happybreak_add_custom_fields_to_export($map)
{
    $map['call_center_agent_user_id']            = array('label' => 'ID Agent', 'checked' => 1);
    $map['call_center_agent_user_id']['segment'] = array('label' => 'ID Agent', 'checked' => 1);
    $map['call_center_agent_user_id']['colname'] = 'ID Agent';
    $map['call_center_agent_user_id']['default'] = 1;

    return $map;
}

add_filter('woe_get_order_fields_misc', 'happybreak_add_custom_fields_to_export');

function happybreak_order_export_field_agent($fieldValue, $order, $fieldName)
{
    if ($fieldName == 'call_center_agent_user_id') {
        $userData   = get_userdata($fieldValue);
        $fieldValue = sprintf('ID : %s %s', $fieldValue, $userData->display_name);
    }

    return $fieldValue;
}

add_action('woe_get_order_value_call_center_agent_user_id', 'happybreak_order_export_field_agent', 10, 3);