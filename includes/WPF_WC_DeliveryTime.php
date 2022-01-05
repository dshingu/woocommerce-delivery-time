<?php 

/**
 * 
 * @author: Dane T. Shingu
 * @description: Class with WordPress custom hooks and callbacks to add our logic to WordPress
 * 
 */

class WPF_WC_DeliveryTime
{

    private static $defaults = [
        'display_on' => ['single product page']
    ];

    public static function init () {

        // add filter to adding delivery settings tab to the WooCommerce admin page
        add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_delivery_settings_tab', 50 );

        // add action hook to adding delivery setting content to WooCommerce admin page
        add_action ( 'woocommerce_settings_tabs_delivery_time', [ new self, 'delivery_settings_tab_content' ] );

        // tap into woocommerce_update_options_ hook to update our setting
        add_action( 'woocommerce_update_options_delivery_time', [ new self, 'update_settings' ] );

        // add filter to add our custom fields to the product data metabox
        add_filter( 'woocommerce_product_data_tabs', [ new self, 'delivery_setting_mb_tab' ] );

        // add action to diplay our custom panel to the product data metabox
        add_action( 'woocommerce_product_data_panels',  [ new self, 'delivery_setting_product_data' ] );

        // add action to process our custom fields shown in product meta box
        add_action( 'woocommerce_process_product_meta', [ new self, 'save_delivery_setting_product_data' ] );

        // hook into woocommerce_after_shop_loop_item action to display info on product archive page
        add_action( 'woocommerce_after_shop_loop_item', [ new self, 'archive_page_show_info' ] );

        // hook into woocommerce_before_add_to_cart_form to display info on single product page
        add_action( 'woocommerce_before_add_to_cart_form', [ new self, 'single_page_show_info' ] );

        // enqueue our script(s) in WordPress
        add_action( 'wp_enqueue_scripts', [ new self, 'enqueue_script' ] );

        // add our custom action to WordPress 
        add_action( 'wp_ajax_wc_delivery_ajax_get_desc', [ new self, 'ajax_get_product_delivery_time_desc' ] );

        // addo our custom action to WordPress non-privilege
        add_action( 'wp_ajax_nopriv_wc_delivery_ajax_get_desc', [ new self, 'ajax_get_product_delivery_time_desc' ] );

        // add our custom css to WordPress
        add_action( 'wp_head', [ new self, 'custom_css' ] );
    }

    public static function activate () {

        $display_on = get_option( 'display_on', true );
        if ( ! $display_on ) { update_option( 'display_on', self::$defaults[ 'display_on' ] ); }

    }

    // our callback method to add our custom tab to woocommerce setting page
    public static function add_delivery_settings_tab ( $settings_tabs ) {
        $settings_tabs['delivery_time'] = __( 'Delivery Time', 'wc_delivery_time' );
        return $settings_tabs;
    }

    // our callback method to add content to our tab
    public function delivery_settings_tab_content () {
        woocommerce_admin_fields( self::delivery_settings_data() );
    }


    // our callback method to add content to our custom fields to our tab
    public function delivery_settings_data () {

        $settings = [
            'title' => [
                'name'     => __( 'WooCommerce Delivery Setting', 'wc_delivery_time' ),
                'type'     => 'title',
                'desc'     => '',
                'id'       => 'wc_settings_tab_demo_section_title'
            ],
            'delivery_time' => [
                'name' => __( 'Delivery Time', 'wc_delivery_time' ),
                'type' => 'number',
                'id' => 'delivery_time'
            ],
            'display_on' => [
                'name' => __( 'Display on', 'wc_delivery_time' ),
                'type' =>  'multiselect',
                'id' => 'display_on',
                'options' => [
                    'single product page' => __( 'Single product page', 'wc_delivery_time' ),
                    'archive product page' => __( 'Product archive page', 'wc_delivery_time' ),
                ]
            ],
            'color' => [
                'name' => __( 'Color', 'wc_delivery_time' ),
                'type' =>  'color',
                'id' => 'color'
            ],
            'section_end' => [
                'type' => 'sectionend',
                'id' => 'wc_settings_tab_demo_section_end'
            ]
        ];

        return apply_filters( 'wc_settings_tab_delivery_time', $settings );
    }


    // our callback method to update our settings
    public function update_settings () {
        woocommerce_update_options( self::delivery_settings_data() );
    }


    // our callback method to add our custom tab to the product metabox
    public function delivery_setting_mb_tab ( $tabs ) {

        $tabs[ 'delivery_setting' ] = [
            'label' => __( 'Delivery Setting', 'wc_delivery_time '),
            'target' => 'delivery_setting_product_data',
            'priority' => 110
        ];

        return $tabs;

    }


    // our callback method to add our panel to the product metabox
    public function delivery_setting_product_data () {
        
        echo '<div id="delivery_setting_product_data" class="panel woocommerce_options_panel hidden">';

        woocommerce_wp_text_input( [
            'id' => 'delivery_time',
            'type' => 'number',
            'value' => get_post_meta( get_the_ID(), 'delivery_time', true ),
            'label' => __( 'Delivery Time', 'wc_delivery_time' ),
        ] );

        woocommerce_wp_textarea_input( [
            'id' => 'delivery_time_description',
            'value' => get_post_meta( get_the_ID(), 'delivery_time_description', true ),
            'label' => __( 'Delivery Time Description', 'wc_delivery_time' ),
        ] );

        echo '</div>';

    }

    // our callback method to save our product custom fields
    public function save_delivery_setting_product_data () {

        $delivery_time              = isset( $_POST[ 'delivery_time' ] ) ? $_POST[ 'delivery_time' ] : null;
        $delivery_time_description  = isset( $_POST[ 'delivery_time_description' ] ) ? $_POST[ 'delivery_time_description' ] : null;
       
        update_post_meta( get_the_ID(), 'delivery_time', $delivery_time );
        update_post_meta( get_the_ID(), 'delivery_time_description', $delivery_time_description );

    }

    // our callback method to display info on our archive page
    public function archive_page_show_info () {
        
        $id = get_the_ID();
        $delivery_time = null;
        $product_delivery_time      = get_post_meta( get_the_ID(), 'delivery_time', true );
        $product_delivery_time_desc = get_post_meta( get_the_ID(), 'delivery_time_description', true );
        $wc_delivery_time           = get_option( 'delivery_time', true );
        $wc_delivery_display_on     = get_option( 'display_on', true );
        $wc_delivery_color          = get_option( 'color', true );

        if ( !in_array( 'archive product page', $wc_delivery_display_on ) ) return;
        if ( $product_delivery_time < 0 ) return;
        
        $delivery_time = empty( $product_delivery_time) || $product_delivery_time == 0 ? $wc_delivery_time : $product_delivery_time;
        $attr = $product_delivery_time_desc ? ' class="has-description wc_dt"' : ' class="wc_dt"';

        if ( $delivery_time == 1) {
            _e( "<a $attr data-id='{$id}'>Delivery Time: {$delivery_time} day</a>", 'wc_delivery_time' );
        } else {
            _e( "<a $attr data-id='{$id}'>Delivery Time: {$delivery_time} days</a>", 'wc_delivery_time' );
        }

    }

    // our callback method to add our panel to the product metabox
    public function single_page_show_info () {
        
        $id = get_the_ID();
        $delivery_time = null;
        $product_delivery_time      = get_post_meta( get_the_ID(), 'delivery_time', true );
        $product_delivery_time_desc = get_post_meta( get_the_ID(), 'delivery_time_description', true );
        $wc_delivery_time           = get_option( 'delivery_time', true );
        $wc_delivery_display_on     = get_option( 'display_on', true );
        $wc_delivery_color          = get_option( 'color', true );

        if ( !in_array( 'single product page', $wc_delivery_display_on ) ) return;
        if ( $product_delivery_time < 0 ) return;
        
        $delivery_time = empty( $product_delivery_time) || $product_delivery_time == 0 ? $wc_delivery_time : $product_delivery_time;
        $attr = $product_delivery_time_desc ? ' class="has-description wc_dt"' : ' class="wc_dt"';

        if ( $delivery_time == 1) {
            _e( "<a $attr data-id='{$id}'>Delivery Time: {$delivery_time} day</a>", 'wc_delivery_time' );
        } else {
            _e( "<a $attr data-id='{$id}'>Delivery Time: {$delivery_time} days</a>", 'wc_delivery_time' );
        }

    }

    // our callback method to add our custom css 
    public function custom_css () {

        // get the color from WordPress
        $color = get_option( 'color', true);
    
        // if color is set then add css with color
        if ( $color ) {

            echo "<style type='text/css'>
                a.wc_dt {
                    display: block;
                    text-align: center;
                    color: {$color} !important;
                }
                a.wc_dt.has-description {
                    cursor: pointer;
                }
                p.wc_deliv_desc {
                    padding: 10px;
                }
            </style>";

        } else {

            echo "<style type='text/css'>
            p.wc_deliv_desc {
                padding: 10px;
            }
            a.wc_dt.has-description {
                cursor: pointer;
            }
            p.wc_deliv_desc {
                padding: 10px;
            }
        </style>";

        }

    }

    // our custom callback to add script to WordPress
    public function enqueue_script () {


        $attr = 'class="wc_deliv_desc"';

        // if jquery is not loaded, then load it
        if ( ! wp_script_is( 'jquery', 'done' ) ) {
            wp_enqueue_script( 'jquery ' );
        }

        // add inline script after jquery-migrate
        wp_add_inline_script( 'jquery-migrate', "
            jQuery( document ).ready( function ( $ ) {
                $( 'a.wc_dt.has-description' ).click( function () {


                    var ajax_url = '". admin_url( 'admin-ajax.php' ) ."';
                    var id       = $( this ).data( 'id' );
                    var object = $( this );
                    var desc_container   = object.parent().find( 'p.wc_deliv_desc' );
                    
                    if ( desc_container.length ) {

                        desc_container.toggle();

                    } else {
    
                        $.post( ajax_url, { action: 'wc_delivery_ajax_get_desc', id: id }, function ( response ) {
                            object.after( '<p $attr>'+ response.msg +'</p>' );
                        } );

                    }

                } );
            } );
        " );

    }

    // our custom callback to hook into WordPress ajax
    public function ajax_get_product_delivery_time_desc () {
        
        $post_id = $_POST[ 'id' ];
        $delivery_time_desc = get_post_meta( $post_id, 'delivery_time_description', true );
        wp_send_json( [
            'msg' => $delivery_time_desc
        ] );
        wp_die();

    }

}


WPF_WC_DeliveryTime::init();

