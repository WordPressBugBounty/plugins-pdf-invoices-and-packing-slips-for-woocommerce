<?php
if (!defined('ABSPATH'))
    exit;
use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
class APIFW_Backend
{
    /**
     * @var    object
     * @access  private
     * @since    1.0.0
    */
    private static $_instance = null;

    /**
     * The version number.
     * @var     string
     * @access  public
     * @since   1.0.0
    */
    public $_version;

    /**
     * The token.
     * @var     string
     * @access  public
     * @since   1.0.0
    */
    public $_token;

    /**
     * The main plugin file.
     * @var     string
     * @access  public
     * @since   1.0.0
    */
    public $file;

    /**
     * The main plugin directory.
     * @var     string
     * @access  public
     * @since   1.0.0
    */
    public $dir;

    /**
     * The plugin assets directory.
     * @var     string
     * @access  public
     * @since   1.0.0
    */
    public $assets_dir;

    /**
     * Suffix for Javascripts.
     * @var     string
     * @access  public
     * @since   1.0.0
    */
    public $script_suffix;

    /**
     * The plugin assets URL.
     * @var     string
     * @access  public
     * @since   1.0.0
    */
    public $assets_url;
    /**
     * The plugin hook suffix.
     * @var     array
     * @access  public
     * @since   1.0.0
    */
    public $hook_suffix = array();

    /**
     * The Invoice Settings
     * @var     array
     * @access  public
     * @since   1.0.0
    */
    public $invoice_settings;

    /**
     * The Packing Slip Settings
     * @var     array
     * @access  public
     * @since   1.0.0
    */
    public $packing_slip_settings;

    /**
     * The Delivery Note Settings
     * @var     array
     * @access  public
     * @since   1.0.0
    */
    public $delivery_note_settings;

    /**
     * The Shipping Label Settings
     * @var     array
     * @access  public
     * @since   1.0.0
    */
    public $shipping_label_settings;

    /**
     * The Dispatch Label Settings
     * @var     array
     * @access  public
     * @since   1.0.0
    */
    public $dispatch_label_settings;

    /**
     * Constructor function.
     * @access  public
     * @return  void
     * @since   1.0.0
    */
    public function __construct( $file = '', $version = '1.0.0' )
    {
        $this->_version = $version;
        $this->_token = APIFW_TOKEN;
        $this->file = $file;
        $this->dir = dirname( $this->file );
        $this->assets_dir = trailingslashit( $this->dir ) . 'assets';
        $this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );
        $this->script_suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
        $plugin = plugin_basename($this->file);
        //add action links to link to link list display on the plugins page
        add_filter( "plugin_action_links_$plugin", array( $this, 'add_settings_link' ) );
        // post type reg
        add_action( 'init', array ( $this, 'apifw_posttypes' ) );
        //reg activation hook
        register_activation_hook( $this->file, array( $this, 'install' ) );
        //reg admin menu
        add_action( 'admin_menu', array( $this, 'register_root_page' ), 30 );
        //enqueue scripts & styles
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );
        //add metabox in order page
        add_action( 'add_meta_boxes', array( $this, 'add_order_page_meta_box' ), 11 );
        //add new column in order table
        add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_new_orderlist_column' ) );
        //process data for new column in order table
        add_action( 'manage_shop_order_posts_custom_column', array( $this, 'new_orderlist_column_content' ), 10,2 );

        add_filter('manage_woocommerce_page_wc-orders_columns',array($this,'add_new_orderlist_column'));
		add_action( 'manage_woocommerce_page_wc-orders_custom_column', array($this,'newone_orderlist_column_content'), 10, 2);

        // adding bulk actions to order table
        add_filter( 'bulk_actions-edit-shop_order', array( $this, 'custom_bulk_actions_ordertable' ), 20, 1 );
		add_filter( 'bulk_actions-woocommerce_page_wc-orders', array( $this, 'custom_bulk_actions_ordertable' ), 20, 1 );
        // PHP fallback for handling bulk actions
        add_filter( 'handle_bulk_actions-edit-shop_order', array( $this, 'handle_bulk_actions_native' ), 10, 3 );
        add_filter( 'handle_bulk_actions-woocommerce_page_wc-orders', array( $this, 'handle_bulk_actions_native' ), 10, 3 );
        add_filter( 'handle_bulk_actions-edit-awcdp_payment', array( $this, 'handle_bulk_actions_native' ), 10, 3 );
        // ajax function for bulk action
        add_action( 'wp_ajax_apifw_handle_bulk_action_urls', array( $this, 'handle_bulk_actions_ordertable' ) );
		//add_action( 'wp_ajax_wc_ajax_apifw_handle_bulk_action_urls', array( $this, 'handle_bulk_actions_ordertable' ) );
		add_action( 'wp_ajax_nopriv_apifw_handle_bulk_action_urls', array( $this, 'handle_bulk_actions_ordertable' ) );


        // add meta box on deposit page payments page
        add_action( 'add_meta_boxes', array( $this, 'add_deposit_page_meta_box' ), 11 );
        //add new column in deposit payments table
        add_filter( 'manage_edit-awcdp_payment_columns', array( $this, 'add_new_awcdp_list_column' ) );
        //process data for new column in deposit payments table
        add_action( 'manage_awcdp_payment_posts_custom_column', array( $this, 'new_awcdp_list_column_content' ),10,2 );
        // adding bulk actions to deposit payments table
        add_filter( 'bulk_actions-edit-awcdp_payment', array( $this, 'custom_bulk_actions_awcdp_table' ), 20, 1 );
        // adding script to admin footer for handling bulk action
        add_action( 'admin_footer', array( $this, 'admin_footer_scripts' ) );


        //reg deactivation hook
        register_deactivation_hook( $this->file, array( $this, 'apifw_deactivation' ) );
        // deactivation form
        add_action( 'admin_footer', array($this, 'apifw_deactivation_form') );
        add_action( 'wp_ajax_apifw_deactivation_feedback', array($this, 'apifw_deactivation_feedback') );
    }

    /**
     * Ensures only one instance of APIFw is loaded or can be loaded.
     * @return Main APIFw instance
     * @see WordPress_Plugin_Template()
     * @since 1.0.0
     * @static
    */
    public static function instance($file = '', $version = '1.0.0')
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($file, $version);
        }
        return self::$_instance;
    }

    /**
     * Adding new link(Configure) in plugin listing page section
    */
    public function add_settings_link($links)
    {
        $config = '<a href="' . admin_url( 'admin.php?page='.APIFW_TOKEN.'-admin-ui/' ) . '">' . __( 'Configure', 'pdf-invoices-and-packing-slips-for-woocommerce' ) . '</a>';
        $upgrade = '<a href="https://acowebs.com/woocommerce-pdf-invoices-and-packing-slips/" style="color:#006AFF;">' . __( 'Upgrade to Pro', 'pdf-invoices-and-packing-slips-for-woocommerce' ) . '</a>';
        array_push( $links, $config, $upgrade );

        return $links;
    }

    /**
     * Installation. Runs on activation.
     * @access  public
     * @return  void
     * @since   1.0.0
    */
    public function install()
    {
        if ( $this->is_woocommerce_activated() === false ) {
			add_action( 'admin_notices', array ( $this, 'notice_need_woocommerce' ) );
			return;
        }
        $this->add_settings_options();
        $this->create_secure_upload_dir();
        $this->register_custom_cron();
    }

    /**
	 * Check if woocommerce is activated
     * @access  public
     * @return  boolean woocommerce install status
	*/
    public function is_woocommerce_activated()
    {
		$blog_plugins = get_option( 'active_plugins', array() );
		$site_plugins = is_multisite() ? (array) maybe_unserialize( get_site_option('active_sitewide_plugins' ) ) : array();

		if ( in_array( 'woocommerce/woocommerce.php', $blog_plugins ) || isset( $site_plugins['woocommerce/woocommerce.php'] ) ) {
			return true;
		} else {
			return false;
		}
    }

    /**
	 * WooCommerce not active notice.
     * @access  public
	 * @return string Fallack notice.
	*/
    public function notice_need_woocommerce()
    {
		$error = sprintf( __( APIFW_PLUGIN_NAME.' requires %sWooCommerce%s to be installed & activated!' , 'pdf-invoices-and-packing-slips-for-woocommerce' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>' );
		$message = '<div class="error"><p>' . $error . '</p></div>';
		echo $message;
    }

    /**
	 * Add plugin basic settings
     * @access private
	*/
    private function add_settings_options()
    {
        // Log the plugin version number
        if ( false === get_option( $this->_token.'_version' ) ){
            add_option( $this->_token.'_version', $this->_version, '', 'yes' );
        } else {
            update_option( $this->_token . '_version', $this->_version );
        }
        // getting woocommerce store address
        $woo_store_address = $this->get_woo_store_address();
        //general settings array
        $general_settings_array = array();
        $general_settings_array['company_name'] = '';
        $general_settings_array['company_logo'] = '';
        $general_settings_array['gen_footer'] = '';
        $general_settings_array['sender_name'] = '';
        $general_settings_array['sender_addr1'] = $woo_store_address['address1'];
        $general_settings_array['sender_addr2'] = $woo_store_address['address2'];
        $general_settings_array['sender_city'] = $woo_store_address['city'];
        $general_settings_array['sender_country'] = $woo_store_address['country'].', '.$woo_store_address['state'];
        $general_settings_array['sender_postal_code'] = $woo_store_address['postcode'];
        $general_settings_array['sender_number'] = '';
        $general_settings_array['sender_email'] = '';
        $general_settings_array['gen_vat'] = '';
        $general_settings_array['rtl_support'] = false;
        // $general_settings_serialize = maybe_serialize( $general_settings_array );
        // adding general settings options
        if ( false === get_option($this->_token.'_general_settings') ){
            add_option( $this->_token.'_general_settings', $general_settings_array, '', 'yes' );
        }

        // invoice settings array
        $ord_sts = array();
        $ord_sts[] =  array(
            'value' => 'wc-processing',
            'label' => 'Processing',
        );
        $invoice_settings_array = array();
        $invoice_settings_array['status'] = true;
        $invoice_settings_array['label'] = 'Invoice';
        $invoice_settings_array['invoice_date'] = false;
        $invoice_settings_array['order_status'] = $ord_sts;
        $invoice_settings_array['next_no'] = 1;
        $invoice_settings_array['no_length'] = 3;
        $invoice_settings_array['no_prefix'] = '';
        $invoice_settings_array['no_suffix'] = '';
        $invoice_settings_array['attach_email'] = true;
        $invoice_settings_array['print_customer'] = false;
        $invoice_settings_array['invoice_logo'] = '';
        $invoice_settings_array['number_format'] = '[number]';
        $invoice_settings_array['freeOrder'] = false;
        $invoice_settings_array['freeLineItems'] = false;
        $invoice_settings_array['userRole'] = '';
        $invoice_settings_array['customCss'] = '';
        // $invoice_settings_serialize = maybe_serialize( $invoice_settings_array );
        // adding invoice settings options
        if ( false === get_option($this->_token.'_invoice_settings') ){
            add_option( $this->_token.'_invoice_settings', $invoice_settings_array, '', 'yes' );
        }

        // packing slip settings array
        $packing_slip_settings_array = array();
        $packing_slip_settings_array['status'] = true;
        $packing_slip_settings_array['prd_img'] = true;
        $packing_slip_settings_array['customer_note'] = false;
        $packing_slip_settings_array['add_footer'] = false;
        $packing_slip_settings_array['dateFormat'] = 'd/M/Y';
        $packing_slip_settings_array['perItem'] = false;
        $packing_slip_settings_array['tableheadBgClr'] = '#020202';
        $packing_slip_settings_array['tableheadBorderClr'] = '#191919';
        $packing_slip_settings_array['tableheadFontClr'] = '#ffffff';
        $packing_slip_settings_array['tableBodyBgClr'] = '#ffffff';
        $packing_slip_settings_array['tableBodyBorderClr'] = '#cccccc';
        $packing_slip_settings_array['tableBodyFontClr'] = '#1b2733';
        $packing_slip_settings_array['customCss'] = '';
        $packing_slip_settings_array['fromAddressTitle'] = 'From Address';
        $packing_slip_settings_array['billingAddressTitle'] = 'Billing Address';
        $packing_slip_settings_array['shippingAddressTitle'] = 'Shipping Address';
        $packing_slip_settings_array['imgLabel'] = 'Image';
        $packing_slip_settings_array['skuLabel'] = 'SKU';
        $packing_slip_settings_array['prdNameLabel'] = 'Product';
        $packing_slip_settings_array['qtyLabel'] = 'Quantity';
        $packing_slip_settings_array['weightLabel'] = 'Total Weight';
        $packing_slip_settings_array['dateLabel'] = 'Date:';
        $packing_slip_settings_array['orderNoLabel'] = 'Order No:';
        // $packing_slip_settings_serialize = maybe_serialize( $packing_slip_settings_array );
        // adding packing slip settings options
        if ( false === get_option($this->_token.'_packing_slip_settings') ){
            add_option( $this->_token.'_packing_slip_settings', $packing_slip_settings_array, '', 'yes' );
        }

        // shipping label settings array
        $shipping_label_settings_array = array();
        $shipping_label_settings_array['status'] = true;
        $shipping_label_settings_array['add_footer'] = false;
        $shipping_label_settings_array['showLogo'] = false;
        $shipping_label_settings_array['barcode'] = true;
        $shipping_label_settings_array['dateFormat'] = 'd/M/Y';
        $shipping_label_settings_array['customCss'] = '';
        $shipping_label_settings_array['orderNoLabel'] = 'Order No:';
        $shipping_label_settings_array['weightLabel'] = 'Weight:';
        $shipping_label_settings_array['toLabel'] = 'To';
        $shipping_label_settings_array['fromLabel'] = 'From';
        $shipping_label_settings_array['emailLabel'] = 'Email:';
        $shipping_label_settings_array['phoneLabel'] = 'Phone No:';
        $shipping_label_settings_array['trackingNoLabel'] = 'Tracking Number:';
        // $shipping_label_settings_serialize = maybe_serialize( $shipping_label_settings_array );
        // adding shipping label settings options
        if ( false === get_option($this->_token.'_shipping_label_settings') ){
            add_option( $this->_token.'_shipping_label_settings', $shipping_label_settings_array, '', 'yes' );
        }

        // delivery note settings array
        $delivery_note_settings_array = array();
        $delivery_note_settings_array['status'] = true;
        $delivery_note_settings_array['prd_img'] = true;
        $delivery_note_settings_array['customer_note'] = false;
        $delivery_note_settings_array['add_footer'] = false;
        // $delivery_note_settings_serialize = maybe_serialize( $delivery_note_settings_array );
        // adding delivery note settings options
        if ( false === get_option($this->_token.'_delivery_note_settings') ){
            add_option( $this->_token.'_delivery_note_settings', $delivery_note_settings_array, '', 'yes' );
        }

        // dispatch label settings array
        $dispatch_label_settings_array = array();
        $dispatch_label_settings_array['status'] = true;
        $dispatch_label_settings_array['customer_note'] = false;
        $dispatch_label_settings_array['add_footer'] = false;
        // $dispatch_label_settings_serialize = maybe_serialize( $dispatch_label_settings_array );
        // adding dispatch label settings options
        if ( false === get_option($this->_token.'_dispatch_label_settings') ){
            add_option( $this->_token.'_dispatch_label_settings', $dispatch_label_settings_array, '', 'yes' );
        }

        // handling invoice templates settings
        $default_invoice_template = array(
            'thumbnail' => $this->assets_url.'images/invoice-temp1.png',
            'color' => '#4647C6',
            'fontFamily' => 'Roboto',
            'logo' =>
            array (
                'status' => true,
                'display' => 'Company Logo',
                'url' => '',
                'width' => 150,
                'height' => '',
                'fontFamily' => 'Roboto',
                'fontSize' => 20,
                'fontWeight' => 'bold',
                'fontStyle' => 'normal',
                'fontColor' => '#4647C6',
                'extra' =>
                array (
                    'content' => '',
                    'fontFamily' => 'Roboto',
                    'fontSize' => 14,
                    'fontWeight' => 'normal',
                    'fontStyle' => 'normal',
                    'fontColor' => '#545d66',
                ),
            ),
            'invoiceNumber' =>
            array (
                'status' => true,
                'label' => 'INVOICE:',
                'fontFamily' => 'Roboto',
                'fontSize' => 14,
                'fontWeight' => 'normal',
                'fontStyle' => 'normal',
                'NumColor' => '#545d66',
                'labelColor' => '#545d66',
            ),
            'orderNumber' =>
            array (
                'status' => true,
                'label' => 'Order No:',
                'fontFamily' => 'Roboto',
                'fontSize' => 14,
                'fontWeight' => 'normal',
                'fontStyle' => 'normal',
                'NumColor' => '#545d66',
                'labelColor' => '#545d66',
            ),
            'invoiceDate' =>
            array (
                'status' => true,
                'label' => 'Invoice Date:',
                'format' => 'd/M/Y',
                'fontFamily' => 'Roboto',
                'fontSize' => 14,
                'fontWeight' => 'normal',
                'fontStyle' => 'normal',
                'dateColor' => '#545d66',
                'labelColor' => '#545d66',
            ),
            'orderDate' =>
            array (
                'status' => true,
                'label' => 'Order Date:',
                'format' => 'd/M/Y',
                'fontFamily' => 'Roboto',
                'fontSize' => 14,
                'fontWeight' => 'normal',
                'fontStyle' => 'normal',
                'dateColor' => '#545d66',
                'labelColor' => '#545d66',
            ),
            'customerNote' =>
            array (
                'status' => true,
                'label' => 'Customer Note:',
                'fontFamily' => 'Roboto',
                'fontSize' => 14,
                'fontWeight' => 'normal',
                'fontStyle' => 'normal',
                'contentColor' => '#545d66',
                'labelColor' => '#545d66',
            ),
            'fromAddress' =>
            array (
                'status' => true,
                'title' =>
                array (
                    'value' => 'From Address',
                    'fontFamily' => 'Roboto',
                    'fontSize' => 16,
                    'fontWeight' => 'bold',
                    'fontStyle' => 'normal',
                    'fontColor' => '#4647C6',
                    'aligns' => 'left',
                ),
                'content' =>
                array (
                    'fontFamily' => 'Roboto',
                    'fontSize' => 14,
                    'fontWeight' => 'normal',
                    'fontStyle' => 'normal',
                    'fontColor' => '#545d66',
                    'aligns' => 'left',
                ),
                'vatLabel' => 'VAT Registration Number:',
                'visbility' =>
                array (
                    'sender' => true,
                    'addr1' => true,
                    'addr2' => true,
                    'city' => true,
                    'country' => true,
                    'postCode' => true,
                    'email' => true,
                    'phone' => true,
                    'vat' => true,
                ),
            ),
            'billingAddress' =>
            array (
                'status' => true,
                'title' =>
                array (
                    'value' => 'Billing Address',
                    'fontFamily' => 'Roboto',
                    'fontSize' => 16,
                    'fontWeight' => 'bold',
                    'fontStyle' => 'normal',
                    'fontColor' => '#4647C6',
                    'aligns' => 'left',
                ),
                'content' =>
                array (
                    'fontFamily' => 'Roboto',
                    'fontSize' => 14,
                    'fontWeight' => 'normal',
                    'fontStyle' => 'normal',
                    'fontColor' => '#545d66',
                    'aligns' => 'left',
                ),
            ),
            'shippingAddress' =>
            array (
                'status' => true,
                'title' =>
                array (
                    'value' => 'Shipping Address',
                    'fontFamily' => 'Roboto',
                    'fontSize' => 16,
                    'fontWeight' => 'bold',
                    'fontStyle' => 'normal',
                    'fontColor' => '#4647C6',
                    'aligns' => 'left',
                ),
                'content' =>
                array (
                    'fontFamily' => 'Roboto',
                    'fontSize' => 14,
                    'fontWeight' => 'normal',
                    'fontStyle' => 'normal',
                    'fontColor' => '#545d66',
                    'aligns' => 'left',
                ),
            ),
            'paymentMethod' =>
            array (
                'status' => true,
                'label' => 'Payment Method:',
                'fontFamily' => 'Roboto',
                'fontSize' => 14,
                'fontWeight' => 'normal',
                'fontStyle' => 'normal',
                'methodColor' => '#545d66',
                'labelColor' => '#545d66',
            ),
            'shippingMethod' =>
            array (
                'status' => true,
                'label' => 'Shipping Method:',
                'fontFamily' => 'Roboto',
                'fontSize' => 14,
                'fontWeight' => 'normal',
                'fontStyle' => 'normal',
                'methodColor' => '#545d66',
                'labelColor' => '#545d66',
            ),
            'footer' =>
            array (
                'status' => true,
                'fontFamily' => 'Roboto',
                'fontSize' => 14,
                'fontWeight' => 'normal',
                'fontStyle' => 'normal',
                'aligns' => 'left',
                'color' => '#545d66',
            ),
            'productTable' =>
            array (
                'status' => true,
                'elements' =>
                array (
                    'sku' =>
                    array (
                        'status' => true,
                        'label' => 'SKU',
                    ),
                    'productName' =>
                    array (
                        'status' => true,
                        'label' => 'Product',
                    ),
                    'quantity' =>
                    array (
                        'status' => true,
                        'label' => 'Quantity',
                    ),
                    'price' =>
                    array (
                        'status' => true,
                        'label' => 'Price',
                    ),
                    'taxrate' =>
                    array (
                        'status' => true,
                        'label' => 'Tax Rate',
                    ),
                    'taxtype' =>
                    array (
                        'status' => true,
                        'label' => 'Tax Type',
                    ),
                    'taxvalue' =>
                    array (
                        'status' => true,
                        'label' => 'Tax Value',
                    ),
                    'total' =>
                    array (
                        'status' => true,
                        'label' => 'Total',
                    ),
                ),
                'head' =>
                array (
                    'bgcolor' => '#fff',
                    'fontColor' => '#4647C6',
                    'fontFamily' => 'Roboto',
                    'fontSize' => 14,
                    'fontWeight' => 'bold',
                    'fontStyle' => 'normal',
                    'aligns' => 'center',
                    'borderColor' => '#4647C6',
                ),
                'body' =>
                array (
                    'bgcolor' => '#fff',
                    'fontColor' => '#1B2733',
                    'fontFamily' => 'Roboto',
                    'fontSize' => 14,
                    'fontWeight' => 'normal',
                    'fontStyle' => 'normal',
                    'aligns' => 'center',
                    'borderColor' => '#dddee0',
                ),
            ),
        );
        // $invoice_template_serialize = maybe_serialize( $default_invoice_template );

        // adding default invoice template && saving its id
        if ( false === get_option($this->_token.'_invoice_active_template_id') ){
            $inv_templt_post = array(
                'post_type' => 'apifw_inv_templates',
                'post_title'    => __( 'Invoice 001', 'pdf-invoices-and-packing-slips-for-woocommerce' ),
                'post_status'   => 'publish',
            );
            $inv_templt_id = wp_insert_post( $inv_templt_post );
            if( $inv_templt_id ) {
                add_post_meta( $inv_templt_id, $this->_token.'_invoice_template', $default_invoice_template );
                add_post_meta( $inv_templt_id, $this->_token.'_invoice_template_status', true );
                add_option( $this->_token.'_invoice_active_template_id', $inv_templt_id, '', 'yes' );
            }
        }
    }

    /**
	 * getting woocommerce store address
     * @access  public
     * @return array store address
	*/
    public function get_woo_store_address()
    {
        $store_address = array();
        $store_address['address1'] = get_option( 'woocommerce_store_address' );
        $store_address['address2'] = get_option( 'woocommerce_store_address_2' );
        $store_address['city'] = get_option( 'woocommerce_store_city' );
        $store_address['postcode'] = get_option( 'woocommerce_store_postcode' );
        // The country/state codes
        $store_raw_country = get_option( 'woocommerce_default_country' );
        // Split the country/state codes
        $split_country = explode( ":", $store_raw_country );
        $store_country_code = $split_country[0];
        // get country name from code
        $store_country_name = WC()->countries->countries[$store_country_code];
        // get state name from codes
        if( isset( $split_country[1] ) ) {
            $store_state_code = $split_country[1];
            $country_states = WC()->countries->get_states( $store_country_code );
            $store_state_name = !empty( $country_states[$store_state_code] ) ? $country_states[$store_state_code] : '';
        } else {
            $store_state_name = '';
        }
        // getting country and state to store address array
        $store_address['country'] = $store_country_name;
        $store_address['state'] = $store_state_name;

        return $store_address;
    }

    /**
     * Adding post types
    */
    public function apifw_posttypes() {
        if( !post_type_exists( 'apifw_inv_templates' ) ) {
            register_post_type('apifw_inv_templates', array(
                'label' => __('Invoice Templates', 'pdf-invoices-and-packing-slips-for-woocommerce'),
                'description' => '',
                'public' => false,
                'show_ui' => false,
                'show_in_menu' => false,
                'capability_type' => 'post',
                'hierarchical' => false,
                'query_var' => true,
                'exclude_from_search' => true,
                'supports' => array('title', 'thumbnail'),
                'show_in_rest' => true,
                'menu_icon'   => 'dashicons-buddicons-topics',
                'labels' => array(
                    'name' => __('Invoice Templates', 'pdf-invoices-and-packing-slips-for-woocommerce'),
                    'singular_name' => __('Invoice Templates', 'pdf-invoices-and-packing-slips-for-woocommerce'),
                    'menu_name' => __('Invoice Templates', 'pdf-invoices-and-packing-slips-for-woocommerce'),
                    'add_new' => __('Add New Template', 'pdf-invoices-and-packing-slips-for-woocommerce'),
                    'add_new_item' => __('Add New Template', 'pdf-invoices-and-packing-slips-for-woocommerce'),
                    'edit' => __('Edit Template', 'pdf-invoices-and-packing-slips-for-woocommerce'),
                    'edit_item' => __('Edit Template', 'pdf-invoices-and-packing-slips-for-woocommerce'),
                    'new_item' => __('New Template', 'pdf-invoices-and-packing-slips-for-woocommerce'),
                    'view' => __('View Template', 'pdf-invoices-and-packing-slips-for-woocommerce'),
                    'view_item' => __('View Template', 'pdf-invoices-and-packing-slips-for-woocommerce'),
                    'search_items' => __('Search Templates', 'pdf-invoices-and-packing-slips-for-woocommerce'),
                    'not_found' => __('No Templates Found', 'pdf-invoices-and-packing-slips-for-woocommerce'),
                    'not_found_in_trash' => __('No Templates Found in Trash', 'pdf-invoices-and-packing-slips-for-woocommerce'),
                    'parent' => __('Parent Template', 'pdf-invoices-and-packing-slips-for-woocommerce')
                )
            ));
        }
    }

    /**
     * Creating upload directory
     * Secure directory with htaccess
    */
    public function create_secure_upload_dir()
    {
        //creating directory
        if( !is_dir( APIFW_UPLOAD_TEMPLATE_DIR ) )
        {
            @mkdir( APIFW_UPLOAD_TEMPLATE_DIR, 0700 );
        }

        $files_to_create = array('.htaccess' => 'deny from all', 'index.php'=>'<?php // acowebs');
        foreach( $files_to_create as $file=>$file_content )
        {
            if( !file_exists( APIFW_UPLOAD_TEMPLATE_DIR.'/'.$file ) )
            {
                $fh = @fopen( APIFW_UPLOAD_TEMPLATE_DIR.'/'.$file, "w" );
                if( is_resource( $fh ) )
                {
                    fwrite( $fh, $file_content );
                    fclose( $fh );
                }
            }
        }

        //Invoice upload directory
        if( !is_dir( APIFW_UPLOAD_INVOICE_DIR ) )
        {
            @mkdir( APIFW_UPLOAD_INVOICE_DIR, 0700 );
        }
    }

    /**
     * Registering Crons
    */
    public function register_custom_cron()
    {
        //Schedule an action if it's not already scheduled
        if ( ! wp_next_scheduled( 'apifw_invoice_delete_cron' ) ) {
            $start_timestamp = strtotime( "tomorrow 3am" );
            wp_schedule_event( $start_timestamp, 'daily', 'apifw_invoice_delete_cron' );
        }
    }

    /**
     * Creating admin pages
    */
    public function register_root_page()
    {
        $this->hook_suffix[] = add_submenu_page(
            'woocommerce',
            __( 'PDF Invoice For WooCommerce', 'pdf-invoices-and-packing-slips-for-woocommerce' ),
            __( 'PDF Invoice', 'pdf-invoices-and-packing-slips-for-woocommerce' ),
            'manage_woocommerce',
            APIFW_TOKEN.'-admin-ui',
            array( $this, 'admin_ui' )
        );
        // getting document settings
        $this->get_document_settings();
    }

    /**
     * Calling view function for admin page components
    */
    public function admin_ui()
    {
        APIFW_Backend::view('admin-root', []);
    }

    /**
     * Including View templates
    */
    static function view( $view, $data = array() )
    {
        //extract( $data );
        include( plugin_dir_path(__FILE__) . 'views/' . $view . '.php' );
    }

    /**
     * Getting document(invoice, packing slip etc) settings
    */
    public function get_document_settings()
    {
        $this->invoice_settings = maybe_unserialize( get_option( $this->_token.'_invoice_settings' ) );
        $this->packing_slip_settings = maybe_unserialize( get_option( $this->_token.'_packing_slip_settings' ) );
        $this->delivery_note_settings = maybe_unserialize( get_option( $this->_token.'_delivery_note_settings' ) );
        $this->shipping_label_settings = maybe_unserialize( get_option( $this->_token.'_shipping_label_settings' ) );
        $this->dispatch_label_settings = maybe_unserialize( get_option( $this->_token.'_dispatch_label_settings' ) );
    }

    /**
     * Load admin CSS.
     * @access  public
     * @return  void
     * @since   1.0.0
    */
    public function admin_enqueue_styles($hook = '')
    {
        wp_register_style($this->_token . '-admin', esc_url($this->assets_url) . 'css/backend.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-admin');
    }

    /**
     * Load admin Javascript.
     * @access  public
     * @return  void
     * @since   1.0.0
    */
    public function admin_enqueue_scripts($hook = '')
    {
        if (!isset($this->hook_suffix) || empty($this->hook_suffix)) {
            return;
        }

        $screen = get_current_screen();

        wp_enqueue_script('jquery');
        // deactivation form js
        if ( $screen->id == 'plugins' ) {
            wp_enqueue_style( 'acowebs-feedback-ui', esc_url( $this->assets_url ) . 'acowebs-ui-kit/acowebs-feedback-ui.css', array(), $this->_version );
            wp_enqueue_script( $this->_token . '-deactivation-message', esc_url( $this->assets_url ) . 'js/message.js', array(), $this->_version, true );
            wp_localize_script( $this->_token . '-deactivation-message', 'apifwDeactivation', array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'apifw_deactivation_feedback' ),
                'slug'    => APIFW_SLUG,
            ) );
        }

        if ( in_array( $screen->id, $this->hook_suffix ) ) {
            // Enqueue WordPress media scripts
            if ( !did_action( 'wp_enqueue_media' ) ) {
                wp_enqueue_media();
            }
            //transilation script
            if ( !wp_script_is( 'wp-i18n', 'registered' ) ) {
                wp_register_script( 'wp-i18n', esc_url( $this->assets_url ) . 'js/i18n.min.js', array('jquery'), $this->_version, true );
            }
            //Enqueue custom backend script
            wp_enqueue_script( $this->_token . '-backend', esc_url( $this->assets_url ) . 'js/backend.js', array('wp-i18n'), $this->_version, true );
            //Localize a script.
            wp_localize_script( $this->_token . '-backend',
                'apifw_object', array(
                    'api_nonce' => wp_create_nonce('wp_rest'),
                    'root' => rest_url('apifw/v1/'),
                    'assets_url' => $this->assets_url,
                    'text_domain' => 'pdf-invoices-and-packing-slips-for-woocommerce',
                    'invoice_sample_url' => admin_url( '?apifw_document=true&type=invoice_sample&action=preview' )
                )
            );

            // backend js transilations
            if( APIFW_WP_VERSION >= 5 ) {
                $plugin_lang_path = trailingslashit( $this->dir ) . 'languages';
                wp_set_script_translations( $this->_token . '-backend', 'pdf-invoices-and-packing-slips-for-woocommerce' );
            }
        }
    }

    /**
     * Creating order page metabox
    */
    public function add_order_page_meta_box()
    {
        $screen = wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
		? wc_get_page_screen_id( 'shop-order' )
		: 'shop_order';

        add_meta_box( 'apifw-order-metabox', __( 'Invoice & Labels', 'pdf-invoices-and-packing-slips-for-woocommerce' ), array( $this, 'get_order_metabox_content' ), $screen, 'side', 'high' );
    }

    /**
     * Handling order page metabox content
    */
    public function get_order_metabox_content($post_or_order_object)
    {
        //  global $post;
        //  $order_id = $post->ID;
        $order = ( $post_or_order_object instanceof WP_post) ? wc_get_order( $post_or_order_object->ID ) : wc_get_order( get_the_id() );
        $post_or_order_object;
        // $order_invoice_no = get_post_meta( $order->get_id(), $this->_token.'_ord_invoice_no', true );
        $order_invoice_no = $order->get_meta($this->_token . '_ord_invoice_no', true);

        $orderids_array = array($order->get_id() );
        $orderid_enc = (urlencode(json_encode( $orderids_array )) );

        //meta box content
        $content = '<div class="apifw_ordermetabox">';
        //invoice btns
        if( $this->invoice_settings['status'] == true ):
            if( ! empty( $order_invoice_no ) ) {
                $not_formatted_flag = get_post_meta( get_the_id(), $this->_token.'_invnum_not_formatted_flag', true );
                if( ! empty( $not_formatted_flag ) ) {
                    // generating class obj
                    $invoice_generator = new APIFW_Invoice();
                    $order_invoice_no = $invoice_generator->format_invoice_number( $order_invoice_no, get_the_id() );
                }
                // content
                $content .= '<p>'.__( 'Invoice Number', 'pdf-invoices-and-packing-slips-for-woocommerce' ).': '.$order_invoice_no.'</p>';
                $inv_preview_url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=invoice&action=preview' );
                $inv_download_url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=invoice&action=download' );
                $content .= '<div class="apifw_ormta_btn">';
                    $content .= '<a href="'.$inv_preview_url.'" class="apifw_orinvpmt_btn" target="_blank">'.__( 'Preview Invoice', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'</a>';
                $content .= '</div>';
                $content .= '<div class="apifw_ormta_btn">';
                    $content .= '<a href="'.$inv_download_url.'" class="apifw_orinvdmt_btn">'.__( 'Download Invoice', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'</a>';
                $content .= '</div>';
            } else {
                $inv_gen_url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=invoice&action=preview' );
                $content .= '<div class="apifw_ormta_btn apifw_ormta_igenbtn">';
                    $content .= '<a href="'."javascript:window.open('$inv_gen_url');location.reload()".'" class="apifw_orinvpmt_btn">'.__( 'Generate Invoice', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'</a>';
                $content .= '</div>';
            }
        endif;
        //packing slip btn
        if( $this->packing_slip_settings['status'] == true ):
            $packing_slip_url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=packing_slip&action=preview' );
            $content .= '<div class="apifw_ormta_btn">';
                $content .= '<a href="'.$packing_slip_url.'" class="apifw_orpsmt_btn" target="_blank">'.__( 'Packing Slip', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'</a>';
            $content .= '</div>';
        endif;
        //delivery note btn
        if( $this->delivery_note_settings['status'] == true ):
            $delivery_note_url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=delivery_note&action=preview' );
            $content .= '<div class="apifw_ormta_btn">';
                $content .= '<a href="'.$delivery_note_url.'" class="apifw_ordnmt_btn" target="_blank">'.__( 'Delivery Note', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'</a>';
            $content .= '</div>';
        endif;
        //shipping label btn
        if( $this->shipping_label_settings['status'] == true ):
            $shipping_label_url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=shipping_label&action=preview' );
            $content .= '<div class="apifw_ormta_btn">';
            $content .= '<a href="'.$shipping_label_url.'" class="apifw_orslmt_btn" target="_blank">'.__( 'Shipping Label', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'</a>';
        $content .= '</div>';
        endif;
        // dispatch label btn
        if( $this->dispatch_label_settings['status'] == true ):
            $dispatch_label_url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=dispatch_label&action=preview' );
            $content .= '<div class="apifw_ormta_btn">';
                $content .= '<a href="'.$dispatch_label_url.'" class="apifw_ordlmt_btn" target="_blank">'.__( 'Dispatch Label', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'</a>';
            $content .= '</div>';
        endif;
        $content .= '</div>';
        //end
        echo $content;
    }

    /**
     * Adding New Column In Admin Order List Table
    */
    public function add_new_orderlist_column( $columns )
    {
        $columns['apifw_doc_links'] = __( 'Documents', 'pdf-invoices-and-packing-slips-for-woocommerce' );
        return $columns;
    }

    /**
     * Handling Content For New Column In Admin Order List Table
    */
    public function new_orderlist_column_content( $column,$post_or_order_object )
	{
        //global $post;
        $order = ( $post_or_order_object instanceof WP_post) ? wc_get_order( $post_or_order_object->ID ) : wc_get_order( get_the_id() );
		$post_or_order_object;
		//$order_invoice_no = get_post_meta(get_the_id(), $this->_token.'_ord_invoice_no', true );

        $orderids_array = array( get_the_id());
        $orderid_enc = (urlencode(json_encode( $orderids_array )) );

		if( $column == 'apifw_doc_links' ) {
            $content = '<div class="apifw_order_col_links">';
            //invoice link
            if( $this->invoice_settings['status'] == true && get_post_meta(get_the_id(), $this->_token.'_ord_invoice_no', true ) ):
                $inv_preview_url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=invoice&action=preview' );
                $content .= '<a href="'.$inv_preview_url.'" class="apifw_ordtbl_inv_link" target="_blank" title="'.__( 'Invoice', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'">'.__( 'Preview Invoice', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'</a>';
            endif;
            //packing slip btn
            if( $this->packing_slip_settings['status'] == true ):
                $packing_slip_url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=packing_slip&action=preview' );
                $content .= '<a href="'.$packing_slip_url.'" class="apifw_ordtbl_ps_link" target="_blank" title="'.__( 'Packing Slip', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'">'.__( 'Packing Slip', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'</a>';
            endif;
            // delivery note btn
            if( $this->delivery_note_settings['status'] == true ):
                $delivery_note_url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=delivery_note&action=preview' );
                $content .= '<a href="'.$delivery_note_url.'" class="apifw_ordtbl_dn_link" target="_blank" title="'.__( 'Delivery Note', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'">'.__( 'Delivery Note', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'</a>';
            endif;
            // Shipping label btn
            if( $this->shipping_label_settings['status'] == true ):
                $shipping_label_url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=shipping_label&action=preview' );
                $content .= '<a href="'.$shipping_label_url.'" class="apifw_ordtbl_sl_link" target="_blank" title="'.__( 'Shipping Label', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'">'.__( 'Shipping Label', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'</a>';
            endif;
            //other doc btns
            if( $this->dispatch_label_settings['status'] == true ):
                $dispatch_label_url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=dispatch_label&action=preview' );
                $content .= '<a href="'.$dispatch_label_url.'" class="apifw_ordtbl_dl_link" target="_blank" title="'.__( 'Dispatch Label', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'">'.__( 'Dispatch Label', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'</a>';
            endif;
            //end
            $content .= '</div>';

            echo $content;
        }
    }

    public function newone_orderlist_column_content( $column,$order_id)
	{
		//global $post;
        $order = wc_get_order($order_id);
        $orderids_array = array($order->get_id());
     
        // $order_invoice_no = $order->get_meta($this->_token.'_ord_invoice_no', true );
		if ( ! is_object( $order ) || is_numeric( $order ) ) {
			$orderids_array = array( $order->get_id());
		}

		$orderids_array = array($order ->get_id());
		$orderid_enc = (urlencode(json_encode( $orderids_array )) );
		if( $column == 'apifw_doc_links' ) {
			$content = '<div class="apifw_order_col_links">';
			//invoice link
			if( $this->invoice_settings['status'] == true && $order->get_meta($this->_token . '_ord_invoice_no', true)  ):
			$inv_preview_url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=invoice&action=preview' );
			$content .= '<a href="'.$inv_preview_url.'" class="apifw_ordtbl_inv_link" target="_blank" title="'.__( 'Invoice', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'">'.__( 'Preview Invoice', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'</a>';
			endif;
			//packing slip btn
			if( $this->packing_slip_settings['status'] == true ):
			$packing_slip_url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=packing_slip&action=preview' );
			$content .= '<a href="'.$packing_slip_url.'" class="apifw_ordtbl_ps_link" target="_blank" title="'.__( 'Packing Slip', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'">'.__( 'Packing Slip', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'</a>';
			endif;
			// delivery note btn
			if( $this->delivery_note_settings['status'] == true ):
			$delivery_note_url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=delivery_note&action=preview' );
			$content .= '<a href="'.$delivery_note_url.'" class="apifw_ordtbl_dn_link" target="_blank" title="'.__( 'Delivery Note', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'">'.__( 'Delivery Note', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'</a>';
			endif;
			// Shipping label btn
			if( $this->shipping_label_settings['status'] == true ):
			$shipping_label_url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=shipping_label&action=preview' );
			$content .= '<a href="'.$shipping_label_url.'" class="apifw_ordtbl_sl_link" target="_blank" title="'.__( 'Shipping Label', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'">'.__( 'Shipping Label', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'</a>';
			endif;
			//other doc btns
			if( $this->dispatch_label_settings['status'] == true ):
			$dispatch_label_url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=dispatch_label&action=preview' );
			$content .= '<a href="'.$dispatch_label_url.'" class="apifw_ordtbl_dl_link" target="_blank" title="'.__( 'Dispatch Label', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'">'.__( 'Dispatch Label', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'</a>';
			endif;
			//end
			$content .= '</div>';

			echo $content;
		}
	}

    /**
     * Adding Bulk Actions Admin Order List Table
     * @param actions array
    */
    public function custom_bulk_actions_ordertable( $actions )
    {
        if( $this->invoice_settings['status'] == true ) {
            $actions['apifw_invoice_preview'] = __( 'Preview Invoice', 'pdf-invoices-and-packing-slips-for-woocommerce' );
            $actions['apifw_invoice_download'] = __( 'Download Invoice', 'pdf-invoices-and-packing-slips-for-woocommerce' );
        }
        if( $this->packing_slip_settings['status'] == true ) {
            $actions['apifw_packing_slip'] = __( 'Packing Slip', 'pdf-invoices-and-packing-slips-for-woocommerce' );
        }
        if( $this->delivery_note_settings['status'] == true ) {
            $actions['apifw_delivery_note'] = __( 'Delivery Note', 'pdf-invoices-and-packing-slips-for-woocommerce' );
        }
        if( $this->shipping_label_settings['status'] == true ) {
            $actions['apifw_shipping_label'] = __( 'Shipping Label', 'pdf-invoices-and-packing-slips-for-woocommerce' );
        }
        if( $this->dispatch_label_settings['status'] == true ) {
            $actions['apifw_dispatch_label'] = __( 'Dispatch Label', 'pdf-invoices-and-packing-slips-for-woocommerce' );
        }
        return $actions;
    }

    /**
     * Bulk actions order table url generator
    */
    public function handle_bulk_actions_ordertable()
    {
        $order_ids = $_POST['order_id'];
        $action = $_POST['type'];
        $order_ids_array = explode( ",", $order_ids );
        $response = array();

        if ( $action == 'apifw_invoice_preview' ) {
            $orderid_enc = urlencode(json_encode( $order_ids_array ) );
            $url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=invoice&action=preview' );

        } elseif( $action == 'apifw_invoice_download' ) {
            $orderid_enc = urlencode(json_encode( $order_ids_array ) );
            $url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=invoice&action=download' );

        } elseif( $action == 'apifw_packing_slip' ) {
            $orderid_enc = urlencode(json_encode( $order_ids_array ) );
            $url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=packing_slip&action=preview' );

        } elseif( $action == 'apifw_delivery_note' ) {
            $orderid_enc = urlencode(json_encode( $order_ids_array ) );
            $url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=delivery_note&action=preview' );

        } elseif( $action == 'apifw_shipping_label' ) {
            $orderid_enc = urlencode(json_encode( $order_ids_array ) );
            $url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=shipping_label&action=preview' );

        } elseif( $action == 'apifw_dispatch_label' ) {
            $orderid_enc = urlencode(json_encode( $order_ids_array ) );
            $url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=dispatch_label&action=preview' );
        } else {
            $url = '';
        }

        if( ! empty( $url ) ){
            $response['status'] = 1;
            $response['url'] = $url;
        } else {
            $response['status'] = 0;
        }

        echo $url;
        wp_die();

    }

    /**
     * Native WordPress / WooCommerce bulk actions handler
    */
    public function handle_bulk_actions_native( $redirect_to, $action, $order_ids )
    {
        $actions_map = array(
            'apifw_invoice_preview'  => array( 'type' => 'invoice', 'action' => 'preview' ),
            'apifw_invoice_download' => array( 'type' => 'invoice', 'action' => 'download' ),
            'apifw_packing_slip'     => array( 'type' => 'packing_slip', 'action' => 'preview' ),
            'apifw_delivery_note'    => array( 'type' => 'delivery_note', 'action' => 'preview' ),
            'apifw_shipping_label'   => array( 'type' => 'shipping_label', 'action' => 'preview' ),
            'apifw_dispatch_label'   => array( 'type' => 'dispatch_label', 'action' => 'preview' ),
        );

        if ( isset( $actions_map[ $action ] ) && ! empty( $order_ids ) ) {
            $order_ids_array = array_values( array_map( 'intval', (array) $order_ids ) );
            $orderid_enc = urlencode( json_encode( $order_ids_array ) );
            return admin_url( '?apifw_document=true&order_id=' . $orderid_enc . '&type=' . $actions_map[ $action ]['type'] . '&action=' . $actions_map[ $action ]['action'] );
        }

        return $redirect_to;
    }

    /**
     * Creating deposit payments page metabox for documents
    */
    public function add_deposit_page_meta_box()
    {
        add_meta_box( 'apifw-deposit-metabox', __( 'Invoice', 'pdf-invoices-and-packing-slips-for-woocommerce' ), array( $this, 'get_deposit_metabox_content' ), 'awcdp_payment', 'side', 'default' );

    }

    /**
     * Handling order page documents metabox content
    */
    public function get_deposit_metabox_content($post_or_order_object)
    {
        //global $post;
        $order = ( $post_or_order_object instanceof WP_post) ? wc_get_order( $post_or_order_object->ID ) : wc_get_order( get_the_id() );
		$post_or_order_object;
		$order_invoice_no = get_post_meta( $order->get_id(), $this->_token.'_ord_invoice_no', true );
        $order_status = get_post_status( get_the_id() );
        $orderids_array = array( get_the_id() );
        $orderid_enc = urlencode(json_encode( $orderids_array ) );
        //meta box content
        $content = '<div class="apifw_ordermetabox">';
        //invoice btns
        if( $this->invoice_settings['status'] == true ):
            if( ! empty( $order_invoice_no ) ) {
                $not_formatted_flag = get_post_meta( get_the_id(), $this->_token.'_invnum_not_formatted_flag', true );
                if( ! empty( $not_formatted_flag ) ) {
                    // generating class obj
                    $invoice_generator = new APIFW_Invoice();
                    $order_invoice_no = $invoice_generator->format_invoice_number( $order_invoice_no, get_the_id() );
                }
                // content
                $content .= '<p>'.__( 'Invoice Number', 'pdf-invoices-and-packing-slips-for-woocommerce' ).': '.$order_invoice_no.'</p>';
                $inv_preview_url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=invoice&action=preview' );
                $inv_download_url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=invoice&action=download' );
                // content
                $content .= '<div class="apifw_ormta_btn">';
                    $content .= '<a href="'.$inv_preview_url.'" class="apifw_orinvpmt_btn" target="_blank">'.__( 'Preview Invoice', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'</a>';
                $content .= '</div>';
                $content .= '<div class="apifw_ormta_btn">';
                    $content .= '<a href="'.$inv_download_url.'" class="apifw_orinvdmt_btn">'.__( 'Download Invoice', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'</a>';
                $content .= '</div>';
            } else {
                $inv_gen_url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=invoice&action=preview' );
                if( empty( $this->invoice_settings['proforma'] ) ){
                    $content .= '<div class="apifw_ormta_btn apifw_ormta_igenbtn">';
                        $content .= '<a href="'."javascript:window.open('$inv_gen_url');location.reload()".'" class="apifw_orinvpmt_btn">'.__( 'Generate Invoice', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'</a>';
                    $content .= '</div>';
                }
            }
        endif;
		$content .= '</div>';
        //end
        echo $content;
	}

    /**
     * Adding New Column In Admin Deposit Payments List Table
    */
    public function add_new_awcdp_list_column( $columns )
    {
        $columns['apifw_doc_links'] = __( 'Invoice', 'pdf-invoices-and-packing-slips-for-woocommerce' );
        return $columns;
    }

    /**
     * Adding Bulk Actions Admin Deposit Payments List Table
     * @param actions array
    */
    public function custom_bulk_actions_awcdp_table( $actions )
    {
        if( $this->invoice_settings['status'] == true ) {
            $actions['apifw_invoice_preview'] = __( 'Preview Invoice', 'pdf-invoices-and-packing-slips-for-woocommerce' );
            $actions['apifw_invoice_download'] = __( 'Download Invoice', 'pdf-invoices-and-packing-slips-for-woocommerce' );
        }

        return $actions;
    }


    /**
     * Handling Content For New Column In Admin eposit Payments List Table
    */
    public function new_awcdp_list_column_content( $column ,$post_or_order_object )
	{
        //global $post;
        $order = ( $post_or_order_object instanceof WP_post) ? wc_get_order( $post_or_order_object->ID ) : wc_get_order( get_the_id() );
		$post_or_order_object;
        $orderids_array = array(  get_the_id() );
        $orderid_enc = urlencode(json_encode ( $orderids_array ) );
		if( $column == 'apifw_doc_links' ) {
            $content = '<div class="apifw_order_col_links">';
                //invoice link
                if( $this->invoice_settings['status'] == true && get_post_meta(  get_the_id(), $this->_token.'_ord_invoice_no', true ) ):
                    $inv_preview_url = admin_url( '?apifw_document=true&order_id='.$orderid_enc.'&type=invoice&action=preview' );
                    $content .= '<a href="'.$inv_preview_url.'" class="apifw_ordtbl_inv_link" target="_blank" title="'.__( 'Invoice', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'">'.__( 'Preview Invoice', 'pdf-invoices-and-packing-slips-for-woocommerce' ).'</a>';
                endif;
            //end
            $content .= '</div>';

            echo $content;
        }
    }

    /**
     * Admin footer scripts
    */
    public function admin_footer_scripts()
    {
        $currentScreen = get_current_screen();
        if ( $currentScreen && in_array( $currentScreen->id, array( 'edit-shop_order', 'edit-awcdp_payment', 'woocommerce_page_wc-orders' ), true ) ) { ?>
            <script>
                jQuery(document).ready(function($) {
                    // bulk action handle
                    $('#doaction, #doaction2').on('click', function(e) {
                        let action_select_name = $(this).attr("id") === 'doaction2' ? 'action2' : 'action';
		                let action = $('select[name="' + action_select_name + '"]').val();
                        var actions_list = ['apifw_invoice_preview', 'apifw_invoice_download', 'apifw_packing_slip', 'apifw_delivery_note', 'apifw_shipping_label', 'apifw_dispatch_label'];
                        if ( actions_list.includes(action) ) {
                            e.preventDefault();

                            let order_ids = [];
                            $('tbody .check-column input[type="checkbox"]:checked, tbody input[name="id[]"]:checked, tbody input[name="post[]"]:checked').each( function() {
                                let val = $(this).val();
                                if ( val && !order_ids.includes(val) ) {
                                    order_ids.push(val);
                                }
                            });

                            if ( order_ids.length > 0 ) {
                                var doc_type = 'invoice';
                                var doc_action = 'preview';

                                if ( action === 'apifw_invoice_download' ) {
                                    doc_type = 'invoice';
                                    doc_action = 'download';
                                } else if ( action === 'apifw_packing_slip' ) {
                                    doc_type = 'packing_slip';
                                    doc_action = 'preview';
                                } else if ( action === 'apifw_delivery_note' ) {
                                    doc_type = 'delivery_note';
                                    doc_action = 'preview';
                                } else if ( action === 'apifw_shipping_label' ) {
                                    doc_type = 'shipping_label';
                                    doc_action = 'preview';
                                } else if ( action === 'apifw_dispatch_label' ) {
                                    doc_type = 'dispatch_label';
                                    doc_action = 'preview';
                                }

                                var orderid_enc = encodeURIComponent(JSON.stringify(order_ids));
                                var baseUrl = <?php echo json_encode( admin_url( '?apifw_document=true' ) ); ?>;
                                var finalUrl = baseUrl + '&order_id=' + orderid_enc + '&type=' + doc_type + '&action=' + doc_action;

                                window.open(finalUrl, '_blank');
                            }
                        }
                    });
                });
            </script>
            <?php
        }
    }


    /**
     * Deactivation feedback popup (Acowebs feedback UI kit), printed on plugins.php only.
     */
    public function apifw_deactivation_form()
    {
      $screen = get_current_screen();
      if ( ! $screen || 'plugins' !== $screen->id ) {
          return;
      }

      $user        = wp_get_current_user();
      $plugin_name = __( 'PDF Invoices and Packing Slips', 'pdf-invoices-and-packing-slips-for-woocommerce' );
      $reasons     = array(
          array( __( "I couldn't get it to work", 'pdf-invoices-and-packing-slips-for-woocommerce' ), __( 'What went wrong? Any error message helps.', 'pdf-invoices-and-packing-slips-for-woocommerce' ) ),
          array( __( 'I found a better plugin', 'pdf-invoices-and-packing-slips-for-woocommerce' ), __( 'Which plugin did you switch to? What does it do better?', 'pdf-invoices-and-packing-slips-for-woocommerce' ) ),
          array( __( "It's missing a feature I need", 'pdf-invoices-and-packing-slips-for-woocommerce' ), __( 'Which feature were you looking for?', 'pdf-invoices-and-packing-slips-for-woocommerce' ) ),
          array( __( 'It broke my site or conflicts with another plugin', 'pdf-invoices-and-packing-slips-for-woocommerce' ), __( 'What broke, or which plugin does it conflict with?', 'pdf-invoices-and-packing-slips-for-woocommerce' ) ),
          array( __( 'I only needed it for a short time', 'pdf-invoices-and-packing-slips-for-woocommerce' ), __( 'Thanks! Anything we could improve?', 'pdf-invoices-and-packing-slips-for-woocommerce' ) ),
          array( __( 'Other', 'pdf-invoices-and-packing-slips-for-woocommerce' ), __( 'Please tell us a little more.', 'pdf-invoices-and-packing-slips-for-woocommerce' ) ),
      );
      ?>
      <div class="acofb acofb-overlay" id="apifw-deactivation" data-acofb="deactivation" hidden>
        <div class="acofb-modal" role="dialog" aria-modal="true" aria-labelledby="apifw-dx-title" aria-describedby="apifw-dx-desc">
          <form class="acofb-modal-form" novalidate>
            <div class="acofb-modal-head">
              <span class="acofb-badge" aria-hidden="true">
                <svg viewBox="-8 -1.5 86 86" focusable="false"><path fill="#00DFB1" fill-rule="evenodd" d="M2.85642 22.0022C6.72119 18.0569 13.0525 17.9916 16.9978 21.8564L41.4978 45.8564C43.4052 47.7249 44.4861 50.2785 44.4999 52.9485C44.5136 55.6185 43.4591 58.1831 41.5711 60.0711L21.5711 80.0711C17.6658 83.9763 11.3342 83.9763 7.42894 80.0711C3.52369 76.1658 3.52369 69.8342 7.42894 65.9289L20.2846 53.0733L3.0022 36.1436C-0.943088 32.2788 -1.00836 25.9475 2.85642 22.0022Z"></path><path fill="#00B2FF" fill-rule="evenodd" d="M60.1791 3.124C64.5289 7.36505 64.6171 14.3293 60.376 18.6791L48.4585 30.9022L65.7782 48.2218C70.0739 52.5176 70.0739 59.4824 65.7782 63.7782C61.4824 68.0739 54.5176 68.0739 50.2218 63.7782L25.2218 38.7782C20.9645 34.5208 20.9209 27.6318 25.124 23.3209L44.624 3.3209C48.8651 -1.02889 55.8293 -1.11705 60.1791 3.124Z"></path><path fill="#0A84FF" fill-rule="evenodd" d="M43.5968 57.1543L25.2218 38.7786C22.1371 35.6937 21.2645 31.2271 22.6168 27.3607L41.4978 45.8572C43.4052 47.7257 44.4861 50.2794 44.4999 52.9495C44.5074 54.4143 44.1935 55.8473 43.5968 57.1543Z"></path></svg>
              </span>
              <div>
                <h2 class="acofb-title" id="apifw-dx-title"><?php
                  /* translators: %s: plugin name */
                  echo esc_html( sprintf( __( 'Quick question before you deactivate %s', 'pdf-invoices-and-packing-slips-for-woocommerce' ), $plugin_name ) );
                ?></h2>
                <p class="acofb-sub" id="apifw-dx-desc"><?php esc_html_e( 'If you have a moment, tell us why. The Acowebs team reads every answer and uses it to improve the plugin.', 'pdf-invoices-and-packing-slips-for-woocommerce' ); ?></p>
              </div>
            </div>

            <div class="acofb-modal-body">
              <fieldset class="acofb-reasons">
                <legend class="acofb-sr"><?php
                  /* translators: %s: plugin name */
                  echo esc_html( sprintf( __( 'Why are you deactivating %s?', 'pdf-invoices-and-packing-slips-for-woocommerce' ), $plugin_name ) );
                ?></legend>
                <?php foreach ( $reasons as $i => $reason ) : $n = $i + 1; ?>
                <div class="acofb-reason">
                  <input class="acofb-radio" type="radio" name="reason" id="apifw-dx-r<?php echo (int) $n; ?>" value="<?php echo esc_attr( $reason[0] ); ?>" required>
                  <label class="acofb-reason-card" for="apifw-dx-r<?php echo (int) $n; ?>"><?php echo esc_html( $reason[0] ); ?></label>
                  <div class="acofb-details">
                    <label class="acofb-sr" for="apifw-dx-d<?php echo (int) $n; ?>"><?php esc_html_e( 'Tell us more (optional)', 'pdf-invoices-and-packing-slips-for-woocommerce' ); ?></label>
                    <textarea class="acofb-textarea" id="apifw-dx-d<?php echo (int) $n; ?>" name="reason_details" rows="3" maxlength="5000" placeholder="<?php echo esc_attr( $reason[1] ); ?>"></textarea>
                  </div>
                </div>
                <?php endforeach; ?>
              </fieldset>

              <div class="acofb-contact">
                <input class="acofb-check" type="checkbox" name="consent" id="apifw-dx-contact" value="1" checked>
                <label class="acofb-check-label" for="apifw-dx-contact"><?php esc_html_e( 'Acowebs can contact me about this feedback', 'pdf-invoices-and-packing-slips-for-woocommerce' ); ?></label>
                <div class="acofb-contact-fields">
                  <div class="acofb-field">
                    <label class="acofb-label" for="apifw-dx-name"><?php esc_html_e( 'Name', 'pdf-invoices-and-packing-slips-for-woocommerce' ); ?></label>
                    <input class="acofb-input" type="text" id="apifw-dx-name" name="name" value="<?php echo esc_attr( $user->display_name ); ?>" maxlength="255" autocomplete="name">
                  </div>
                  <div class="acofb-field">
                    <label class="acofb-label" for="apifw-dx-email"><?php esc_html_e( 'Email', 'pdf-invoices-and-packing-slips-for-woocommerce' ); ?></label>
                    <input class="acofb-input" type="email" id="apifw-dx-email" name="email" value="<?php echo esc_attr( $user->user_email ); ?>" maxlength="320" autocomplete="email">
                  </div>
                </div>
              </div>

              <p class="acofb-privacy">
                <svg viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path fill="currentColor" d="M8 1a3.5 3.5 0 0 0-3.5 3.5V6H4a1.5 1.5 0 0 0-1.5 1.5v6A1.5 1.5 0 0 0 4 15h8a1.5 1.5 0 0 0 1.5-1.5v-6A1.5 1.5 0 0 0 12 6h-.5V4.5A3.5 3.5 0 0 0 8 1Zm2 5V4.5a2 2 0 1 0-4 0V6h4Z"></path></svg>
                <span><?php esc_html_e( 'We only send this if you click Send: your answer, your site address, name and language, the plugin version and your WordPress, WooCommerce and PHP versions. Your name and email are included only while the box above is ticked.', 'pdf-invoices-and-packing-slips-for-woocommerce' ); ?> <a class="acofb-link" href="<?php echo esc_url( 'https://acowebs.com/privacy-policy/' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Privacy policy', 'pdf-invoices-and-packing-slips-for-woocommerce' ); ?><span class="acofb-sr"> <?php esc_html_e( '(opens in a new tab)', 'pdf-invoices-and-packing-slips-for-woocommerce' ); ?></span></a></span>
              </p>
            </div>

            <div class="acofb-modal-foot">
              <button type="submit" class="acofb-btn acofb-btn--primary acofb-btn--send" data-acofb-action="send"><?php esc_html_e( 'Send & deactivate', 'pdf-invoices-and-packing-slips-for-woocommerce' ); ?></button>
              <button type="button" class="acofb-btn acofb-btn--text" data-acofb-action="skip"><?php esc_html_e( 'Skip & deactivate', 'pdf-invoices-and-packing-slips-for-woocommerce' ); ?></button>
              <button type="button" class="acofb-btn acofb-btn--text acofb-btn--cancel" data-acofb-action="cancel"><?php esc_html_e( 'Cancel', 'pdf-invoices-and-packing-slips-for-woocommerce' ); ?></button>
            </div>
          </form>
        </div>
      </div>
      <?php
    }

    /**
     * AJAX: forward deactivation feedback to the Acowebs Feedback API.
     */
    public function apifw_deactivation_feedback()
    {
      check_ajax_referer( 'apifw_deactivation_feedback', 'nonce' );

      if ( ! current_user_can( 'activate_plugins' ) ) {
          wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
      }

      // Shared client is only loaded on PHP 7.1+ (see start.php).
      if ( ! class_exists( 'Acowebs_Feedback_Client' ) ) {
          wp_send_json_success();
      }

      // phpcs:disable WordPress.Security.NonceVerification.Missing -- verified above.
      $consent = isset( $_POST['consent'] ) && rest_sanitize_boolean( wp_unslash( $_POST['consent'] ) );
      $args    = array(
          'plugin'         => APIFW_FEEDBACK_PLUGIN,
          'edition'        => APIFW_EDITION,
          'plugin_version' => APIFW_VERSION,
          'plugin_slug'    => APIFW_SLUG,
          'reason'         => isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '',
          'reason_details' => isset( $_POST['reason_details'] ) ? sanitize_textarea_field( wp_unslash( $_POST['reason_details'] ) ) : '',
          'consent'        => $consent,
          'name'           => null,
          'email'          => null,
      );

      // Name and email only while the contact box is ticked.
      if ( $consent ) {
          $args['name']  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
          $email         = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
          $args['email'] = is_email( $email ) ? $email : '';
      }
      // phpcs:enable

      // The client fills missing keys with '' but only strips null, so turn empty strings into null:
      // the API never receives "" (an empty email would fail validation and lose the feedback).
      foreach ( $args as $key => $value ) {
          if ( '' === $value ) {
              $args[ $key ] = null;
          }
      }

      Acowebs_Feedback_Client::send_deactivation( $args );

      wp_send_json_success();
    }

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

    /**
     * Deactivation hook
    */
    public function apifw_deactivation()
    {
        wp_clear_scheduled_hook( 'apifw_invoice_delete_cron' );
    }
}
