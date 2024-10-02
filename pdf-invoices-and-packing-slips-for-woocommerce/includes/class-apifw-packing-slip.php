<?php
if (!defined('ABSPATH'))
    exit;

class APIFW_Packing_Slip
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
    public $packing_slip_settings;

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
    public function __construct( $order_ids )
    {
        $this->_token = APIFW_TOKEN;
        if ( !$order_ids )
            return false;

        $this->order_ids = $order_ids;
        
        // getting general settings
        $this->general_settings = maybe_unserialize( get_option( $this->_token.'_general_settings' ) );

        // getting invoice settings
        $this->packing_slip_settings = maybe_unserialize( get_option( $this->_token.'_packing_slip_settings' ) );

        //calling packing slip pdf genereator
        $this->generate_ps_pdf();
    }

    /**
     * Generating pdf file
     * @access  public
    */
    public function generate_ps_pdf()
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
                'margin_left' => 0,
                'margin_right' => 0,
                'margin_top' => 10,
                'margin_bottom' => 10,
            ]
        );
        $html = $this->get_ps_html_template();
        $mpdf->WriteHTML( $html );
        $mpdf->Output( 'packing-slip.pdf', 'I' );
    }

    /**
     * Generating html template for pdf 
     * @access  public
    */
    public function get_ps_html_template()
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
        // packing slip settings
        $slip_per_item = isset( $this->packing_slip_settings['perItem'] ) ? $this->packing_slip_settings['perItem'] : false;

        $heading_color = '#020202';
        if( isset( $this->packing_slip_settings['headingColor'] ) ){
            $heading_color = $this->packing_slip_settings['headingColor'];
        }

        $tbl_head_styles = '';
        if( isset( $this->packing_slip_settings['tableheadBgClr'] ) ){
            $tbl_head_styles = 'background-color: '.$this->packing_slip_settings['tableheadBgClr'].'; ';
        }
        if( isset( $this->packing_slip_settings['tableheadBorderClr'] ) ){
            $tbl_head_styles .= 'border-color: '.$this->packing_slip_settings['tableheadBorderClr'].'; ';
        }
        if( isset( $this->packing_slip_settings['tableheadFontClr'] ) ){
            $tbl_head_styles .= 'color: '.$this->packing_slip_settings['tableheadFontClr'].';';
        }

        $tbl_body_styles = '';
        if( isset( $this->packing_slip_settings['tableBodyBgClr'] ) ){
            $tbl_body_styles = 'background-color: '.$this->packing_slip_settings['tableBodyBgClr'].'; ';
        }
        if( isset( $this->packing_slip_settings['tableBodyBorderClr'] ) ){
            $tbl_body_styles .= 'border-color: '.$this->packing_slip_settings['tableBodyBorderClr'].'; ';
        }
        if( isset( $this->packing_slip_settings['tableBodyFontClr'] ) ){
            $tbl_body_styles .= 'color: '.$this->packing_slip_settings['tableBodyFontClr'].';';
        }

        $date_format = 'd/M/Y';
        if( isset( $this->packing_slip_settings['dateFormat'] ) ){
            $date_format = $this->packing_slip_settings['dateFormat'];
        }

        $date_lbl = 'Date:';
        if( isset( $this->packing_slip_settings['dateLabel'] ) ){
            $date_lbl = $this->packing_slip_settings['dateLabel'];
        }

        $order_no_lbl = 'Order No:';
        if( isset( $this->packing_slip_settings['orderNoLabel'] ) ){
            $order_no_lbl = $this->packing_slip_settings['orderNoLabel'];
        }

        $from_address_title = 'From Address';
        if( isset( $this->packing_slip_settings['fromAddressTitle'] ) ){
            $from_address_title = $this->packing_slip_settings['fromAddressTitle'];
        }

        $billing_address_title = 'Billing Address';
        if( isset( $this->packing_slip_settings['billingAddressTitle'] ) ){
            $billing_address_title = $this->packing_slip_settings['billingAddressTitle'];
        }

        $shipping_address_title = 'Shipping Address';
        if( isset( $this->packing_slip_settings['shippingAddressTitle'] ) ){
            $shipping_address_title = $this->packing_slip_settings['shippingAddressTitle'];
        }

        $tbl_img_lbl = 'Image';
        if( isset( $this->packing_slip_settings['imgLabel'] ) ){
            $tbl_img_lbl = $this->packing_slip_settings['imgLabel'];
        }

        $tbl_sku_lbl = 'SKU';
        if( isset( $this->packing_slip_settings['skuLabel'] ) ){
            $tbl_sku_lbl = $this->packing_slip_settings['skuLabel'];
        }

        $tbl_prd_name_lbl = 'Product';
        if( isset( $this->packing_slip_settings['prdNameLabel'] ) ){
            $tbl_prd_name_lbl = $this->packing_slip_settings['prdNameLabel'];
        }

        $tbl_qty_lbl = 'Quantity';
        if( isset( $this->packing_slip_settings['qtyLabel'] ) ){
            $tbl_qty_lbl = $this->packing_slip_settings['qtyLabel'];
        }

        $tbl_weight_lbl = 'Total Weight';
        if( isset( $this->packing_slip_settings['weightLabel'] ) ){
            $tbl_weight_lbl = $this->packing_slip_settings['weightLabel'];
        }
        
        //html code for pdf
        ob_start();
        $template_path = $this->get_packing_slip_template('ps-classic.php');
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
     * 1. /themes/your-theme/pdf-invoices-and-packing-slips-for-woocommerce-pro/packing-slip-templates/$template_name
     * 2. /themes/theme/$template_name
     * 3. /plugins/pdf-invoices-and-packing-slips-for-woocommerce-pro/packing-slip-templates/$template_name.
     *
     * @since 1.0.0
     *
     * @param 	string 	$template_name			Template to load.
     * @param 	string 	$string $template_path	Path to templates.
     * @param 	string	$default_path			Default path to template files.
     * @return 	string 							Path to the template file.
    */
    public function get_packing_slip_template( $template_name, $template_path = '', $default_path = '' )
    {
        // Set variable to search in woocommerce-plugin-templates folder of theme.
        if ( ! $template_path ) :
            $template_path = 'pdf-invoices-and-packing-slips-for-woocommerce-pro/packing-slip-templates/';
        endif;
    
        // Set default plugin templates path.
        if ( ! $default_path ) :
            $default_path = plugin_dir_path(__DIR__) . 'packing-slip-templates/'; // Path to the template folder
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