<?php
if (!defined('ABSPATH'))
    exit;

class APIFW_Shipping_Label
{
    /**
     * The token.
     * @var     string
     * @access  public
     * @since   1.0.0
    */
    public $_token;

    /**
     * @var array
     * @access  public
     * @since   1.0.0
    */
    public $general_settings;

    /**
     * @var array
     * @access  public
     * @since   1.0.0
    */
    public $shipping_label_settings;

    /**
     * @var array
     * @access  public
     * @since   1.0.0
    */
    public $order_ids;

    /**
     * Constructor function.
     * @access  public
     * @return  void
     * @since   1.0.0
    */
    public function __construct( $order_ids = '' )
    {
        $this->_token = APIFW_TOKEN;
        if ( !$order_ids )
            return false;

        $this->order_ids = $order_ids;
        
        // getting general settings
        $this->general_settings = maybe_unserialize( get_option( $this->_token.'_general_settings' ) );

        // getting invoice settings
        $this->shipping_label_settings = maybe_unserialize( get_option( $this->_token.'_shipping_label_settings' ) );

        //calling shipping label pdf genereator
        $this->generate_shipping_label_pdf();
    }

    /**
     * Generating pdf file
     * @access  public
    */
    public function generate_shipping_label_pdf()
    {
        $rtl = $this->general_settings['rtl_support'];
        if( $rtl ){
            $ft_family = 'markazitext';
        } else {
            $ft_family = 'roboto';
        }

        require_once __DIR__ . '/vendor/autoload.php';
        $mpdf = new \Mpdf\Mpdf(
            [
                'format' => 'A4-P',
                'debugfonts'=> false,
                'mode' => 'utf-8',
                'autoScriptToLang'=>true,
                'autoLangToFont' => true,
                'default_font_size' => 13,
                //'default_font' => $ft_family,
                'margin_left' => 5,
                'margin_right' => 5,
                'margin_top' => 5,
                'margin_bottom' => 5,
            ]
        );
        $html = $this->get_shipping_label_html_template();
        $mpdf->WriteHTML( $html );
        $mpdf->Output( 'shipping-label.pdf', 'I' );
    }

    /**
     * Generating html template for pdf 
     * @access  public
    */
    public function get_shipping_label_html_template()
    {
        $order_ids = $this->order_ids;
        //general datas
        $company_name = $this->general_settings['company_name'];
        $company_logo = $this->general_settings['company_logo'];
        $sender_name = $this->general_settings['sender_name'];
        $sender_addr1 = $this->general_settings['sender_addr1'];
        $sender_addr2 = $this->general_settings['sender_addr2'];
        $sender_city = $this->general_settings['sender_city'];
        $sender_country = $this->general_settings['sender_country'];
        $sender_postal_code = $this->general_settings['sender_postal_code'];
        $sender_email = $this->general_settings['sender_email'];
        $sender_number = $this->general_settings['sender_number'];
        $tax_reg_no = $this->general_settings['gen_vat'];
        $footer_txt = $this->general_settings['gen_footer'];
        $rtl = $this->general_settings['rtl_support'];
        // shipping label settings
        $show_barcode = true;
        if( isset( $this->shipping_label_settings['barcode'] ) ){
            $show_barcode = $this->shipping_label_settings['barcode'];
        }

        $show_logo = false;
        if( isset( $this->shipping_label_settings['showLogo'] ) ){
            $show_logo = $this->shipping_label_settings['showLogo'];
        }

        $order_no_lbl = 'Order No:';
        if( isset( $this->shipping_label_settings['orderNoLabel'] ) ){
            $order_no_lbl = $this->shipping_label_settings['orderNoLabel'];
        }

        $weight_lbl = 'Weight:';
        if( isset( $this->shipping_label_settings['weightLabel'] ) ){
            $weight_lbl = $this->shipping_label_settings['weightLabel'];
        }

        $to_lbl = 'To';
        if( isset( $this->shipping_label_settings['toLabel'] ) ){
            $to_lbl = $this->shipping_label_settings['toLabel'];
        }

        $from_lbl = 'From';
        if( isset( $this->shipping_label_settings['fromLabel'] ) ){
            $from_lbl = $this->shipping_label_settings['fromLabel'];
        }

        $email_lbl = 'Email:';
        if( isset( $this->shipping_label_settings['emailLabel'] ) ){
            $email_lbl = $this->shipping_label_settings['emailLabel'];
        }

        $phone_lbl = 'Phone No:';
        if( isset( $this->shipping_label_settings['phoneLabel'] ) ){
            $phone_lbl = $this->shipping_label_settings['phoneLabel'];
        }

        $tracking_no_lbl = 'Tracking Number:';
        if( isset( $this->shipping_label_settings['trackingNoLabel'] ) ){
            $tracking_no_lbl = $this->shipping_label_settings['trackingNoLabel'];
        }

        //html code for pdf
        ob_start();
        $template_path = $this->get_shipping_label_template('sl-classic.php');
        // including template
		if( $template_path ) {
			include $template_path;
		} else {
			echo 'Not found';
		}

		// getting template html from o/p buffer
        $template_html = ob_get_contents();
        ob_end_clean();
		
        //returning html
        return $template_html;
    }

    
    /**
     * Locate template.
     *
     * Locate the called template.
     * Search Order:
     * 1. /themes/your-theme/pdf-invoices-and-packing-slips-for-woocommerce-pro/shipping-label-templates/$template_name
     * 2. /themes/theme/$template_name
     * 3. /plugins/pdf-invoices-and-packing-slips-for-woocommerce-pro/shipping-label-templates/$template_name.
     *
     * @since 1.0.0
     *
     * @param 	string 	$template_name			Template to load.
     * @param 	string 	$string $template_path	Path to templates.
     * @param 	string	$default_path			Default path to template files.
     * @return 	string 							Path to the template file.
    */
    public function get_shipping_label_template( $template_name, $template_path = '', $default_path = '' )
    {
        // Set variable to search in woocommerce-plugin-templates folder of theme.
        if ( ! $template_path ) :
            $template_path = 'pdf-invoices-and-packing-slips-for-woocommerce-pro/shipping-label-templates/';
        endif;
    
        // Set default plugin templates path.
        if ( ! $default_path ) :
            $default_path = plugin_dir_path(__DIR__) . 'shipping-label-templates/'; // Path to the template folder
        endif;
    
        // Search template file in theme folder.
        $template = locate_template( array(
            $template_path . $template_name,
            $template_name
        ) );
    
        // Get plugins template file.
        if ( ! $template ) :
            $template = $default_path . $template_name;
        endif;

        // check file exisistence
        if ( ! file_exists( $template ) ) :
            return false;
        endif;
    
        return $template;
    }
}