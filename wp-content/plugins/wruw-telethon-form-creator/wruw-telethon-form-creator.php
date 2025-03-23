<?php
/*
Plugin Name: WRUW Telethon Form Creator
Description: Generates HTML forms, postcards, and labels for WRUW Telethon
Version: 1.0.0
Author: Isaac Nicholas
*/

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

define('wruw_PLUGIN_URL', plugin_dir_url(__FILE__));
define('wruw_PLUGIN_PATH', plugin_dir_path(__FILE__));

require wruw_PLUGIN_PATH . 'assets/qr-code-lib.php'; // Include QR code library

add_action('admin_head-edit.php', 'wruw_add_bulk_actions');
add_filter('handle_bulk_actions-edit-shop_order', 'wruw_handle_bulk_actions', 10, 3);
add_action('woocommerce_admin_order_data_after_billing_address', 'wruw_add_template_button');

/**
 * Add custom bulk actions to WooCommerce order list
 */
function wruw_add_bulk_actions() {
    global $post_type;
    
    if ($post_type == 'shop_order') {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('select[name="action"], select[name="action2"]').append('<option value="generate_template1">Generate Template 1</option>');
                $('select[name="action"], select[name="action2"]').append('<option value="generate_template2">Generate Template 2</option>');
                $('select[name="action"], select[name="action2"]').append('<option value="generate_template3">Generate Template 3</option>');
            });
        </script>
        <?php
    }
}

/**
 * Handle custom bulk actions for WooCommerce orders
 */
function wruw_handle_bulk_actions($redirect_url, $doaction, $post_ids) {
    if ('generate_template1' === $doaction || 'generate_template2' === $doaction || 'generate_template3' === $doaction) {
        $template_name = str_replace('generate_', '', $doaction);
        $grouped_orders = wruw_group_orders_by_address_and_name($post_ids);

        foreach ($grouped_orders as $key => $orders) {
            $all_html_content = '';

            foreach ($orders as $order_id) {
                $html_content = wruw_generate_html_file($order_id, $template_name);
                if ($html_content) {
                    $all_html_content .= $html_content;
                }
            }

            // Save combined HTML to a file
            $file_path = wruw_PLUGIN_PATH . 'generated/' . sanitize_file_name("$key-$template_name.html");
            file_put_contents($file_path, $all_html_content);
        }

        return add_query_arg(array('bulk_orders_generated' => count($post_ids), 'template_type' => $template_name, 'group_count' => count($grouped_orders)), $redirect_url);
    }

    return $redirect_url;
}

/**
 * Group orders by address and name
 */
function wruw_group_orders_by_address_and_name($order_ids) {
    $grouped_orders = array();

    foreach ($order_ids as $order_id) {
        $order = wc_get_order($order_id);

        if (!$order) continue;

        $address = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . ',' . 
                   $order->get_billing_address_1() . '<br>' . 
                   $order->get_billing_city() . ', ' . 
                   $order->get_billing_state() . ' ' . 
                   $order->get_billing_postcode();

        if (!isset($grouped_orders[$address])) {
            $grouped_orders[$address] = array();
        }

        $grouped_orders[$address][] = $order_id;
    }

    return $grouped_orders;
}

/**
 * Add template generation button to individual order page
 */
function wruw_add_template_button($order) {
    echo '<br><a href="#" class="button generate-template-button" data-order-id="' . esc_attr($order->get_id()) . '" data-template="template1">Generate Template 1</a>';
    echo ' <a href="#" class="button generate-template-button" data-order-id="' . esc_attr($order->get_id()) . '" data-template="template2">Generate Template 2</a>';
    echo ' <a href="#" class="button generate-template-button" data-order-id="' . esc_attr($order->get_id()) . '" data-template="template3">Generate Template 3</a>';
}

/**
 * Generate HTML content for a given order and template
 */
function wruw_generate_html_file($order_id, $template_name) {
    $order = wc_get_order($order_id);
    
    if (!$order) return '';

    $address = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . ',' . 
               $order->get_billing_address_1() . '<br>' . 
               $order->get_billing_city() . ', ' . 
               $order->get_billing_state() . ' ' . 
               $order->get_billing_postcode();
    $items = '';
    foreach ($order->get_items() as $item_id => $item) {
        $product_name = $item->get_product()->get_name();
        $quantity = $item->get_quantity();
        $items .= "<li>$product_name x $quantity</li>";
    }

    $qr_code_url = wruw_generate_qr_code($order_id);

    ob_start();
    
    include wruw_PLUGIN_PATH . "templates/$template_name.html";
    
    $html_content = ob_get_clean();

    // Save HTML to a file
    $file_path = wruw_PLUGIN_PATH . 'generated/' . sanitize_file_name("order-$order_id-$template_name.html");
    file_put_contents($file_path, $html_content);

    // Generate CSV and save it
    wruw_generate_csv($order_id, $address, $items);
    
    return $html_content;
}

/**
 * Generate QR code for order number
 */
function wruw_generate_qr_code($order_id) {
    $qr_code_data = 'Order Number: ' . $order_id;
    $file_path = wruw_PLUGIN_PATH . 'generated/qr-' . sanitize_file_name("$order_id.png");

    QRcode::png($qr_code_data, $file_path);
    return wruw_PLUGIN_URL . 'generated/qr-' . sanitize_file_name("$order_id.png");
}

/**
 * Generate CSV for order data
 */
function wruw_generate_csv($order_id, $address, $items) {
    $csv_content = "Order ID,Address,Items\n";
    $csv_content .= "$order_id,\"$address\",\"$items\"\n";

    // Save CSV to a file
    $file_path = wruw_PLUGIN_PATH . 'generated/' . sanitize_file_name("order-$order_id.csv");
    file_put_contents($file_path, $csv_content);

    // Optionally, force download of the CSV file
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="order-' . sanitize_file_name("$order_id.csv") . '"');

    echo $csv_content;
    exit();
}

/**
 * Handle AJAX request for individual order template generation
 */
add_action('wp_ajax_wruw_generate_template', 'wruw_handle_ajax_request');
function wruw_handle_ajax_request() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wruw_nonce')) wp_die();

    $order_id = intval($_POST['order_id']);
    $template_name = sanitize_text_field($_POST['template']);

    $html_content = wruw_generate_html_file($order_id, $template_name);
    
    if ($html_content) {
        // Navigate to the generated HTML file
        wp_redirect(wruw_PLUGIN_URL . 'generated/' . sanitize_file_name("order-$order_id-$template_name.html"));
        exit;
    }

    wp_send_json_error('Failed to generate template.');
}

/**
 * Display a link to the generated bulk HTML files
 */
add_action('admin_notices', 'wruw_bulk_html_notice');
function wruw_bulk_html_notice() {
    if (isset($_GET['bulk_orders_generated']) && isset($_GET['template_type']) && isset($_GET['group_count'])) {
        $count = intval($_GET['bulk_orders_generated']);
        $template_type = sanitize_text_field($_GET['template_type']);
        $group_count = intval($_GET['group_count']);

        echo '<div class="notice notice-success is-dismissible">';
        echo '<p>' . esc_html(sprintf('Successfully generated templates for %d orders in %d groups.', $count, $group_count)) . '</p>';
        echo '<ul>';

        $generated_files = glob(wruw_PLUGIN_PATH . 'generated/*-' . sanitize_file_name("$template_type.html"));
        foreach ($generated_files as $file_path) {
            $file_name = basename($file_path);
            echo '<li><a href="' . wruw_PLUGIN_URL . 'generated/' . esc_url($file_name) . '" target="_blank">' . esc_html($file_name) . '</a></li>';
        }

        echo '</ul>';
        echo '</div>';
    }
}
