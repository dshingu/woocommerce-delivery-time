
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

if ( !function_exists( 'add_action' ) ) {
	exit;
}

include( 'includes/WPF_WC_DeliveryTime.php' );