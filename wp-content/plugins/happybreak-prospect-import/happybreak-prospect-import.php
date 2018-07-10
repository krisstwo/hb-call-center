<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

/*
Plugin Name: Happybreak prospects import
Description: Import prospects from csv
Version: 0.1
Author: Coffeeandbrackets.com
Author URI: http:/coffeeandbrackets.com
*/

define('PLUGIN_PATH', plugin_dir_path(__FILE__));

function hpi_redirect_to_referrer()
{
    // redirect to form page
    // To make the Coding Standards happy, we have to initialize this.
    if ( ! isset($_POST['_wp_http_referer'])) { // Input var okay.
        $_POST['_wp_http_referer'] = wp_get_referer();

    }

    // Sanitize the value of the $_POST collection for the Coding Standards.
    $redirectUrl = sanitize_text_field(
        wp_unslash($_POST['_wp_http_referer']) // Input var okay.
    );

    wp_safe_redirect(urldecode($redirectUrl));
}

function hpi_prospects_import()
{
    require_once PLUGIN_PATH . 'admin/import.php';
}

function happbreak_setup_admin_menu()
{
    add_management_page(__('Import de prospects', 'happybreak'),
        __('Import de prospects', 'happybreak-prospect-import'),
        'manage_options', dirname(__FILE__), 'hpi_prospects_import');
}

add_action('admin_menu', 'happbreak_setup_admin_menu');

// Define global notices storage for the plugin
! session_id() && session_start();
if ( ! isset($_SESSION['happybreak-prospect-import-notices'])) {
    $_SESSION['happybreak-prospect-import-notices'] = array(
        'info' => array(),
        'success' => array(),
        'warning' => array(),
        'error' => array()
    );
}

function hpi_prospects_import_process()
{
    // Security checks
    if (wp_verify_nonce($_POST['_wpnonce'],
            'happybreak-prospect-import-process') && current_user_can('manage_options')) {

        // Upload presence check
        if (empty($_FILES) || empty($_FILES['happybreak-prospect-import-file'])) {
            $_SESSION['happybreak-prospect-import-notices']['error'][] = __('Pas de fihier uploadé',
                'happybreak-prospect-import');
            hpi_redirect_to_referrer();

            return;
        }

        // Upload access permission check
        if ( ! is_readable($_FILES['happybreak-prospect-import-file']['tmp_name'])) {
            $_SESSION['happybreak-prospect-import-notices']['error'][] = __('Impossible de lire le fichier uploadé (permissions)',
                'happybreak-prospect-import');
            hpi_redirect_to_referrer();

            return;
        }

        // Upload file type check
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if (false === array_search($finfo->file($_FILES['happybreak-prospect-import-file']['tmp_name']), array(
                'text/plain',
                'application/vnd.ms-excel',
                'application/csv',
                'application/x-csv',
                'text/csv',
                'text/comma-separated-values',
                'text/x-comma-separated-values',
                'text/tab-separated-values'
            ))) {
            $_SESSION['happybreak-prospect-import-notices']['error'][] = __('Type de fichier invalide',
                'happybreak-prospect-import');
            hpi_redirect_to_referrer();

            return;
        }

        $skipLines = sanitize_text_field($_POST['happybreak-prospect-import-skip-lines']);

        // Run import
        try {
            require_once 'hpi_Import.php';
            new Hpi_Import($_FILES['happybreak-prospect-import-file']['tmp_name'], $skipLines);
            $_SESSION['happybreak-prospect-import-notices']['success'][] = __('Fichier importé avec succés ! (Voir plus bas s\'il y a eu des lignes rejetées)',
                'happybreak-prospect-import');
        } catch (Exception $e) {
            $_SESSION['happybreak-prospect-import-notices']['error'][] = $e->getMessage();
            hpi_redirect_to_referrer();

            return;
        }

        hpi_redirect_to_referrer();
    } else {
        wp_die('Could not verify nonce or privileges');
    }
}

add_action('admin_post_happybreak-prospect-import-process', 'hpi_prospects_import_process');

function hpi_prospects_import_delete_csv()
{
    if (wp_verify_nonce($_GET['_wpnonce'],
            'happybreak-prospect-import-delete-csv') && current_user_can('manage_options')) {
        if ( ! empty($_GET['name'])) {
            require_once 'hpi_Import.php';

            // Abort if trying to traverse
            $name = sanitize_text_field($_GET['name']);
            if (strpos($name, '..') !== false) {
                wp_die('Path not allowed');
            }

            wp_delete_file(trailingslashit(Hpi_Import::getUploadDir()) . $name);
            $_SESSION['happybreak-prospect-import-notices']['success'][] = __('Fichier suppirmé',
                'happybreak-prospect-import');

            hpi_redirect_to_referrer();
        }
    } else {
        wp_die('Could not verify nonce or privileges');
    }
}

add_action('admin_post_happybreak-prospect-import-delete-csv', 'hpi_prospects_import_delete_csv');

function hpi_prospects_import_download_template()
{
    require_once 'hpi_Import.php';
    Hpi_Import::generateTemplateFile();
}

add_action('admin_post_happybreak-prospect-import-download-template', 'hpi_prospects_import_download_template');

function hpi_prospects_import_notices()
{
    // TODO: handle other notices
    foreach ($_SESSION['happybreak-prospect-import-notices']['error'] as $notice) {
        echo '<div class="notice notice-error"><p>';
        echo $notice;
        echo '</p></div>';
    }

    foreach ($_SESSION['happybreak-prospect-import-notices']['success'] as $notice) {
        echo '<div class="notice notice-success"><p>';
        echo $notice;
        echo '</p></div>';
    }

    // Truncate notices
    $_SESSION['happybreak-prospect-import-notices']['error']   = array();
    $_SESSION['happybreak-prospect-import-notices']['success'] = array();
}

add_action('admin_notices', 'hpi_prospects_import_notices');