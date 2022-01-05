
<?php
/*
 * Plugin Name: Delivery Time for WooCommerce
 * Description: Add Delivery Time setting to WooCommerce
 * Author: Dane T. Shingu
 * Author URI: <mailto:dane.shingu@gmail.com>
 * Text Doman: wc_delivery_time
 * Version: 1.0
 *
 */

$woocommerce_path = trailingslashit( WP_PLUGIN_DIR ) . 'woocommerce/woocommerce.php';

if ( ! function_exists( 'add_action' ) ) {
	exit;
}

if ( in_array( $woocommerce_path, wp_get_active_and_valid_plugins() ) ) {
   
    include( 'includes/WPF_WC_DeliveryTime.php' );

}

