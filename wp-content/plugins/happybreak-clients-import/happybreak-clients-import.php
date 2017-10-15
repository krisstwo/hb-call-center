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
define('CLIENT_IMPORT', plugin_dir_path(__FILE__));
define('CALL_CENTER_AGENT_ROLE', 'call_center_agent');
define('ORDER_CALL_CENTER_AGENT_USER_ID', 'call_center_agent_user_id');


/**
 * run script import client
 */
function my_custom_url_handler()
{
    if ($_SERVER["REQUEST_URI"] == '/happyBreak/import_client') {
        require_once(CLIENT_IMPORT . 'ImportCsv.php');
        new ImportCsv();
    }
}

add_action('parse_request', 'my_custom_url_handler');


/**
 * redirect user not login
 * admin can logged
 * show only for user connected
 */
function redirect_guest()
{

    if (empty($_GET['pay_for_order']) && !is_page('thank-you') && !is_user_logged_in() && !is_super_admin(get_current_user_id()) && !members_current_user_has_role(CALL_CENTER_AGENT_ROLE)) {
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
    if (!is_super_admin($currentUserId)) {
        $where .= " AND EXISTS (SELECT * FROM {$wpdb->postmeta} pm_agent WHERE pm_agent.post_id = {$wpdb->posts}.ID AND pm_agent.meta_key = '" . ORDER_CALL_CENTER_AGENT_USER_ID . "' AND pm_agent.meta_value = '$currentUserId')";
    }

    return $where;
}

add_filter('posts_where', 'happybreak_force_filter_orders_by_agent', 10, 2);
add_filter('posts_where_request', 'happybreak_force_filter_orders_by_agent', 10, 2);
add_filter('posts_where_paged', 'happybreak_force_filter_orders_by_agent', 10, 2);

function happybreak_allow_own_order_edit_only($allcaps, $caps, $args, $user)
{
    if (!empty($allcaps['delete_users']))
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

    $order = wc_get_order($id);

    if (in_array($action, array('edit', 'editpost')) && $order && (int)$order->get_meta(ORDER_CALL_CENTER_AGENT_USER_ID, true) === get_current_user_id()) {
        $allcaps = array_merge($allcaps, array('edit_others_shop_orders' => true));
    }

    return $allcaps;
}

add_filter('user_has_cap', 'happybreak_allow_own_order_edit_only', 10, 4);

function Is_Backend_LOGIN()
{
    $ABSPATH_MY = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, ABSPATH);
    return ((in_array($ABSPATH_MY . 'wp-login.php', get_included_files()) || in_array($ABSPATH_MY . 'wp-register.php', get_included_files())) || $GLOBALS['pagenow'] === 'wp-login.php' || $_SERVER['PHP_SELF'] == '/wp-login.php');
}

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
    if ((int)$user->ID != 0 && !members_current_user_has_role(CALL_CENTER_AGENT_ROLE) && empty($allcaps['delete_users']))
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

    remove_meta_box('woocommerce-order-downloads', 'shop_order', 'normal');
    remove_meta_box('wpo_wcpdf-data-input-box', 'shop_order', 'normal');
    remove_meta_box('wpo_wcpdf-box', 'shop_order', 'side');

    //relocate order metaboxes
    require_once 'includes/woocommerce/admin/meta-boxes/class-happybreak-meta-box-order-actions.php';
    require_once 'includes/woocommerce/admin/meta-boxes/class-happybreak-meta-box-order-payment.php';
    $wp_meta_boxes['shop_order']['normal']['low']['woocommerce-order-actions'] = $wp_meta_boxes['shop_order']['side']['high']['woocommerce-order-actions'];
    unset($wp_meta_boxes['shop_order']['side']['high']['woocommerce-order-actions']);
    $wp_meta_boxes['shop_order']['normal']['low']['woocommerce-order-actions']['callback'] = 'Happybreak_Meta_Box_Order_Actions::output';
    if($order->needs_payment())
        add_meta_box('woocommerce-order-payment', '4. ' . __('Mode paiement', 'happybreak'), 'Happybreak_Meta_Box_Order_Payment::output', 'shop_order', 'normal', 'low');


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
        'label' => __("Complément d'adresse", 'woocommerce'),
        'required' => false
    );
    // set information after adress 2
    $fields['billing']['fields'] = array_slice($fields['billing']['fields'], 0, 5, true) +
        array("additional_address" => $additional) +
        array_slice($fields['billing']['fields'], 1, count($fields['billing']['fields']) - 1, true) ;



    $additional = array(
        'label' => __("Complément d'adresse", 'woocommerce'),
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
    $orderTable = array_slice($fields, 0, 5, true) +
        array("additional_address" => $aditionel) +
        array_slice($fields, 1, count($fields) - 1, true) ;


    return $orderTable;
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
        'required' => false
    );

    return $fields;
}
add_filter('woocommerce_admin_shipping_fields', 'happybreak_add_phone_to_field_order_admin');

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

    $query_args['meta_query'][] = array(
        'key' => 'billing_phone',
        'value' => substr($term, 2),
        'compare' => 'LIKE',
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

    return $columns;
}

add_filter('manage_edit-shop_order_columns', 'happybreak_hide_order_columns_for_nonadmins', 1000);

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
    if (members_current_user_has_role(CALL_CENTER_AGENT_ROLE))
        $classes .= ' role-agent';

    return $classes;
}

add_filter('admin_body_class', 'happybreak_admin_role_body_class');