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

    if (empty($_GET['pay_for_order']) && !is_user_logged_in() && !is_super_admin(get_current_user_id()) && !members_current_user_has_role(CALL_CENTER_AGENT_ROLE)) {
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
    if ($post_type == 'shop_order') {

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

    if ($_GET['agent_filter']) {
        $query->set('meta_key', ORDER_CALL_CENTER_AGENT_USER_ID);
        $query->set('meta_value', $_GET['agent_filter']);
    }
    return $query;
}

add_filter('parse_query', 'filter_teleoperateur_admin');

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


/**
 * pay as customer
 * @param $order_id
 * @param $items
 */
function happybreak_redirect_to_checkout($order_id, $items)
{
    if (strpos($items['wc_order_action'], 'payAscustomer') !== false) {
        $getArgement = explode('__', $items['wc_order_action']);
        $urlRedirect = $getArgement[1];
        ob_start();
        header('Location: ' . $urlRedirect);
        ob_end_flush();
        exit();
    }
}

add_action('woocommerce_before_save_order_items', 'happybreak_redirect_to_checkout', 10, 2);

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
    if(!is_super_admin(get_current_user_id())){
        remove_meta_box( 'postcustom' , 'shop_order' , 'normal' );
        remove_meta_box( 'woocommerce-order-downloads' , 'shop_order' , 'normal' );
    }
}

add_action('add_meta_boxes', 'happybreak_remove_metabox_for_nonadmins', 999);

/**
 * add aditionel adress to billing/shipping form
 * @param $fields
 * @return mixed
 */
function happybreak_add_additional_address_to_billing_form($fields)
{
    $fields['billing']['fields']['billing_additional_address'] = array(
        'label' => __("Complément d'adresse", 'woocommerce'),
        'required' => false,
        'clear' => true,
        'type' => 'text'
    );
    $fields['shipping']['fields']['shipping_additional_address'] = array(
        'label' => __("Complément d'adresse", 'woocommerce'),
        'required' => false,
        'clear' => true,
        'type' => 'text'
    );

    return $fields;
}

add_filter('woocommerce_checkout_fields', 'happybreak_add_additional_address_to_billing_form');

/**
 * save aditionel adress
 * @param $order_id
 */
function happybreak_save_additional_address_to_billing_meta($order_id)
{
    if (!empty($_POST['billing_additional_address']) || !empty($_POST['shipping_additional_address'])) {
        update_post_meta($order_id, 'billing_additional_address', sanitize_text_field($_POST['billing_additional_address']));
        update_post_meta($order_id, 'shipping_additional_address', sanitize_text_field($_POST['shipping_additional_address']));
    }
}

add_action('woocommerce_checkout_update_order_meta', 'happybreak_save_additional_address_to_billing_meta');

/**
 * add aditionel_adress to edit form
 * @param $fields
 * @return mixed
 */
function happybreak_add_additional_address_to_user_edit_form($fields)
{
    $fields['billing']['fields']['billing_additional_address'] = array(
        'label' => __("Complément d'adresse", 'woocommerce'),
        'required' => false,
        'clear' => true,
        'type' => 'text'
    );
    $fields['shipping']['fields']['shipping_additional_address'] = array(
        'label' => __("Complément d'adresse", 'woocommerce'),
        'required' => false,
        'clear' => true,
        'type' => 'text'
    );

    return $fields;
}

add_filter('woocommerce_customer_meta_fields', 'happybreak_add_additional_address_to_user_edit_form');

/**
 * add phone to shipping form
 * @param $fields
 * @return mixed
 */
function happybreak_add_phone_to_shipping_user_edit_form($fields)
{
    $fields['shipping']['fields']['shipping_phone'] = array(
        'label' => __("Phone", 'woocommerce'),
        'required' => false,
        'clear' => true,
        'type' => 'text'
    );

    return $fields;
}

add_filter('woocommerce_customer_meta_fields', 'happybreak_add_phone_to_shipping_user_edit_form');

/**
 * save phone shipping
 * @param $order_id
 */
function happybreak_save_phone_to_shipping_meta($order_id)
{
    if (!empty($_POST['shipping_phone']) ) {
        update_post_meta($order_id, 'shipping_phone', sanitize_text_field($_POST['shipping_phone']));
       }
}

add_action('woocommerce_checkout_update_order_meta', 'happybreak_save_phone_to_shipping_meta');

/**
 * add phone to form shipping
 * @param $fields
 * @return mixed
 */
function happybreak_add_phone_to_shipping_form($fields)
{
    $fields['shipping']['fields']['shipping_phone'] = array(
        'label' => __("Phone", 'woocommerce'),
        'required' => false,
        'clear' => true,
        'type' => 'text'
    );

    return $fields;
}

add_filter('woocommerce_checkout_fields', 'happybreak_add_phone_to_shipping_form');

/**
 * add fiel to admin order form biiling #billing_additional_address
 * @param array $fields
 * @return array
 */
function happybreak_add_adress_to_field_order_admin(array $fields){
    $aditionel= array(
        'label' => __("Complément d'adresse", 'woocommerce'),
        'required' => false,
        'clear' => true,
        'type' => 'text'
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
    $fields['phone'] = array(
        'label' => __("Phone", 'woocommerce'),
        'required' => false,
        'clear' => true,
        'type' => 'text'
    );

    return $fields;
}
add_filter('woocommerce_admin_shipping_fields', 'happybreak_add_phone_to_field_order_admin');

/**
 * add fiel to admin order form shipping #shipping_additional_address
 * @param array $fields
 * @return array
 */
function happybreak_add_aditional_adress_to_shipping_field_order_admin(array $fields){
    $aditionel = array(
        'label' => __("Complément d'adresse", 'woocommerce'),
        'required' => false,
        'clear' => true,
        'type' => 'text'
    );
    // set information after adress 2
    $orderTable = array_slice($fields, 0, 5, true) +
        array("additional_address" => $aditionel) +
        array_slice($fields, 1, count($fields) - 1, true) ;

    return $orderTable;
}
add_filter('woocommerce_admin_shipping_fields', 'happybreak_add_aditional_adress_to_shipping_field_order_admin');

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
        return;

    $query_args['meta_query'][] = array(
        'key' => 'billing_phone',
        'value' => substr($term, 2),
        'compare' => 'LIKE',
    );

    return $query_args;
}

add_filter('woocommerce_customer_search_customers', 'happybreak_search_customer_by_phone', 10, 2);