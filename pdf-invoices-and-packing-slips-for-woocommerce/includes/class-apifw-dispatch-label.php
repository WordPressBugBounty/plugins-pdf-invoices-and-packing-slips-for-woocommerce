<?php
if (!defined('ABSPATH'))
    exit;

class APIFW_Dispatch_Label
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
    public $dispatch_label_settings;

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
        $this->dispatch_label_settings = maybe_unserialize( get_option( $this->_token.'_dispatch_label_settings' ) );

        //calling packing slip pdf genereator
        $this->generate_dispatch_lbl_pdf();
    }

    /**
     * Generating pdf file
     * @access  public
    */
    public function generate_dispatch_lbl_pdf()
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
        $html = $this->get_dispatch_html_template();
        $mpdf->WriteHTML( $html );
        $mpdf->Output( 'dispatch-label.pdf', 'I' );
    }

    /**
     * Generating html template for pdf 
     * @access  public
    */
    public function get_dispatch_html_template()
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

        //html code for pdf
        $html = "<!DOCTYPE html>
        <html ".($rtl ? "dir='rtl'" : '' ).">
            <head>
                <meta charset='utf-8'>
                <title>".__( 'Dispatch Label', 'pdf-invoices-and-packing-slips-for-woocommerce' )."</title>
                <style>
					@page :first {
                        margin-top: 0;
                    }
                    body {
                        position: relative;
                        color: rgb(84, 93, 102);
                    }
                    p {
                        line-height: 1.8;
                        margin: 0;
                        padding: 0;
                        font-size: 13px;
                        font-weight: normal;
                        color: rgb(84, 93, 102);
                    }
                    h4 {
                        line-height: 1.8;
                        margin: 0 0 4px;
                        padding: 0;
                        font-size: 15px;
                        font-weight: bold;
                        color: #020202;
                    }
                    h2 {
                        margin: 0;
                        padding: 0;
                        line-height: 34px;
                        font-size: 18px;
                        font-weight: bold;
                        color: #020202;
                    }
                    .pdf_wrapper {
                        width: 93%;
                        padding: 20px 3.5% 0;
                        background: #ffffff;";
                        if( count( $order_ids ) > 1 ):
                            $html .= "page-break-after: always;";
                        endif;
                        $html .= "
                    }
                    .top_sec {
                        width: 100%; 
                        clear:both;
                        margin-top: 14px;
                        margin-bottom: 20px;
                    }
                    .logowrap {
                        float:left;
                        width: 48%;
                    }
                    .logowrap img {
                        max-width: 100%;
                    }
                    .logowrap p {
                        width: 100%
                    }
                    .from_address_wrap {
                        float:left; 
                        width: 48%;
                        padding-left: 4%;
                    }
                    .from_address_inner {
                        width: 100%;
                        clear: both;
                    }
                    .from_address_inner p {
                        float: left;
                        width: 49%;
                        padding-right: 1%;
                    }
                    .pdf_midsec_wrap {
                        width: 100%; 
                        clear:both;
                        margin-bottom: 30px;
                    }
                    .billaddress_wrap {
                        float:left; 
                        width: 48%;
                    }
                    .shipping_address_wrap {
                        float:left; 
                        width: 48%;
                        padding-left: 4%;
                    }
                    .pdf_bottom_wrap {
                        width: 100%;
                    }
                    .pdf_prdlist_wrap {
                        width: 100%;
                    }
                    .pdf_prdlist_table {
                        width: 100%;
                        border-collapse: collapse;
                        border: 1px solid #cccccc;
                    }
                    .pdf_prdlist_table th {
                        padding: 10px;
                        border-left: 1px solid #191919;
                        background-color: #020202;
                        font-size: 13px;
                        font-weight: bold;
                        color: #fff;
                        text-align: center;
                    }
                    .pdf_prdlist_table td {
                        padding: 10px;
                        border-bottom: 1px solid #cccccc;
                        border-right: 1px solid #cccccc;
                        border-top: 0;
                        font-size: 13px;
                        font-weight: normal;
                        color: rgb(27, 39, 51);
                        text-align: center;
                    }
                    .pdf_prdlist_table img {
                        height: auto;
                    }
                    .pdf_prdsubtbl_wrap {
                        width: 100%;
                        clear: both;
                        margin-top: 5px;
                    }
                    .pdf_prdlist_subtable {
                        width: 240px;
                        border-collapse: collapse;
                    }
                    .pdf_prdlist_subtable td {
                        padding: 5px 10px;
                        border-bottom-width: 0px;
                        font-size: 13px;
                        font-weight: normal;
                        color: rgb(27, 39, 51);
                        text-align: right;
                    }
                    .pdf_prdlist_subtable tr:nth-child(6) td {
                        padding-bottom: 10px;
                    }
                    .pdf_prdlist_subtable tr:nth-child(7) td {
                        border-top-style: solid;
                        border-top-width: 1px;
                        border-top-color: #cccccc;
                        padding-top: 10px;
                    }
                    .pdf_footer_wrap {
                        width: 100%;
                        clear: both;
                        margin-bottom: 12px;
                        margin-top: 10px;
                    }
                    .pdf_footer_wrap p {
                        font-size: 13px;
                        font-weight: normal;
                        color: rgb(84, 93, 102);
                        text-align: left;
                        line-height: 24px;
                        text-align: center;
                    }
                    /*.doc_rtl h2 {
                        line-height: 36px;
                        font-size: 24px;
                    }
                    .doc_rtl h4 {
                        font-size: 20px;
                    }
                    .doc_rtl p {
                        font-size: 17px;
                        line-height: 1.4;
                    }
                    .doc_rtl .pdf_prdlist_table th {
                        font-size: 18px;
                    }
                    .doc_rtl .pdf_prdlist_table td {
                        font-size: 18px;
                    }
                    .doc_rtl .pdf_prdlist_subtable td {
                        font-size: 18px;
                        text-align: right;
                    }
                    .doc_rtl .pdf_footer_wrap p {
                        font-size: 18px;
                    }*/
                    .doc_rtl .logowrap {
                        float: right;
                    }
                    .doc_rtl .from_address_wrap {
                        float: right;
                        padding-left: 0%;
                        padding-right: 4%;
                    }
                    .doc_rtl .from_address_inner p {
                        float: right;
                        padding-right: 1%;
                        padding-left: 0%;
                    }
                    .doc_rtl .billaddress_wrap {
                        float: right; 
                    }
                    .doc_rtl .shipping_address_wrap {
                        float: right;
                        padding-left: 0%;
                        padding-right: 4%;
                    }
                    .doc_rtl .pdf_prdlist_subtable {
                        width: 205px;
                    }
                </style>
            </head>
            <body ".($rtl ? "dir='rtl'" : '' ).">";
                $count = 1;
                foreach( $order_ids as $order_id ):
                    $breake_avoid = '';
                    if( $count == count($order_ids) ){
                        $breake_avoid = 'page-break-after: avoid;';
                    }
                    $order_data = apifw_get_order_detail( $order_id );
                    $order_prd_infos = $order_data['items'];
                    //fee
                    $order_fee = '0.00';
                    $fee_lines = $order_data['fee_lines'];
                    if( $fee_lines ) {
                        foreach( $fee_lines as $f ) {
                            $t = $f['total'] + $f['total_tax'];
                            $order_fee = $order_fee + $t;
                        }
                    }
                    $order_fee = wc_format_decimal( $order_fee );

                    //template html
                    $html .= "<div class='pdf_wrapper ".($rtl ? 'doc_rtl' : '' )."' style='".$breake_avoid."'>
                        <div class='top_sec'>";
                            $html .= "<div class='logowrap'>";
                                if( $company_logo ):
                                    $html .= "<img src='".$company_logo."' alt='".$company_name."'/>";
                                else:
                                    $html .= "<h2>".$company_name."</h2>";
                                endif;
                                $order = wc_get_order($order_id );
                                // $inv_no = get_post_meta( $order_id, $this->_token.'_ord_invoice_no', true );
                                $inv_no = $order->get_meta($this->_token.'_ord_invoice_no', true);

                                if( $inv_no && $inv_no != '' ):
                                    $html .= "<p style='margin-top: 10px;'>".__( 'Invoice No', 'pdf-invoices-and-packing-slips-for-woocommerce' ).': '.$inv_no."</p>";
                                endif;
                                $html .= "<p>".__( 'Order No', 'pdf-invoices-and-packing-slips-for-woocommerce' ).': '.$order_data['order_number']."</p>";
                                $date = date_create( $order_data['created_at'] );
                                $html .= "<p>".__( 'Date', 'pdf-invoices-and-packing-slips-for-woocommerce' ).': '.date_format( $date, 'd/M/Y' )."</p>";
                                if( $tax_reg_no ):
                                    $html .= "<p>".__('Tax Reg. No', 'pdf-invoices-and-packing-slips-for-woocommerce').": ".$tax_reg_no."</p>";
                                endif;
                            $html .= "</div>";
                        
                            $html .= "<div class='from_address_wrap'>";
                                $html .= "<h4>".__( 'From Address', 'pdf-invoices-and-packing-slips-for-woocommerce' )."</h4>";
                                $html .= "<div class='from_address_inner'>";
                                    if( $sender_name ):
                                        $html .= "<p>".$sender_name."</p>";
                                    endif;
                                    if( $sender_addr1 ):   
                                        $html .= "<p>".$sender_addr1."</p>";
                                    endif;
                                    if( $sender_addr2 ):
                                        $html .= "<p>".$sender_addr2."</p>";
                                    endif;
                                    if( $sender_city ):
                                        $html .= "<p>".$sender_city."</p>";
                                    endif;
                                    if( $sender_country ):
                                        $html .= "<p>".$sender_country."</p>";
                                    endif;
                                    if( $sender_postal_code ):
                                        $html .= "<p>".$sender_postal_code."</p>";
                                    endif;
                                    if( $sender_number ):
                                        $html .= "<p>".$sender_number."</p>";
                                    endif;
                                    if( $sender_email ):
                                        $html .= "<p>".$sender_email."</p>";
                                    endif;
                                    
                                $html .= "</div>
                            </div>
                        </div>
                        <div class='pdf_midsec_wrap'>
                            <div class='billaddress_wrap'>";
                                $html .= "<h4>".__( 'Billing Address', 'pdf-invoices-and-packing-slips-for-woocommerce' )."</h4>";
                                $html .= "<div>
                                    <p>";
                                        if( $order_data['billing_address']['first_name'] != '' ):
                                            $html .= $order_data['billing_address']['first_name'];
                                        endif;
                                        if( $order_data['billing_address']['last_name'] != '' ):
                                            $html .= " ".$order_data['billing_address']['last_name']."<br/>";
                                        endif;
                                        if( $order_data['billing_address']['company'] != '' ):
                                            $html .= $order_data['billing_address']['company']."<br/>";
                                        endif;
                                        if( $order_data['billing_address']['address_1'] != '' ):
                                            $html .= $order_data['billing_address']['address_1']."<br/>";
                                        endif;
                                        if( $order_data['billing_address']['address_2'] != '' ):
                                            $html .= $order_data['billing_address']['address_2']."<br/>";
                                        endif;
                                        if( $order_data['billing_address']['city'] != '' ):
                                            $html .= $order_data['billing_address']['city'].", ";
                                        endif;
                                        if( $order_data['billing_address']['postcode'] != '' ):
                                            $html .= $order_data['billing_address']['postcode']."<br/>";
                                        endif;
                                        if( $order_data['billing_address']['formated_state'] != '' ):
                                            $html .= $order_data['billing_address']['formated_state'].", ";
                                        endif;
                                        if( $order_data['billing_address']['formated_country'] != '' ):
                                            $html .= $order_data['billing_address']['formated_country']."<br/>";
                                        endif;
                                        if( $order_data['billing_address']['email'] != '' ):
                                            $html .= $order_data['billing_address']['email']."<br/>";
                                        endif;
                                        if( $order_data['billing_address']['phone'] != '' ):
                                            $html .= $order_data['billing_address']['phone'];
                                        endif;
                                    $html .= "</p>
                                </div>
                            </div>
                            <div class='shipping_address_wrap'>";
                                $html .= "<h4>".__( 'Shipping Address', 'pdf-invoices-and-packing-slips-for-woocommerce' )."</h4>";
                                $html .= "<div>
                                    <p>";
                                        if( $order_data['shipping_address']['first_name'] != '' ):
                                            $html .= $order_data['shipping_address']['first_name'];
                                        endif;
                                        if( $order_data['shipping_address']['last_name'] != '' ):
                                            $html .= " ".$order_data['shipping_address']['last_name']."<br/>";
                                        endif;
                                        if( $order_data['shipping_address']['company'] != '' ):
                                            $html .= $order_data['shipping_address']['company']."<br/>";
                                        endif;
                                        if( $order_data['shipping_address']['address_1'] != '' ):
                                            $html .= $order_data['shipping_address']['address_1']."<br/>";
                                        endif;
                                        if( $order_data['shipping_address']['address_2'] != '' ):
                                            $html .= $order_data['shipping_address']['address_2']."<br/>";
                                        endif;
                                        if( $order_data['shipping_address']['city'] != '' ):
                                            $html .= $order_data['shipping_address']['city'].", ";
                                        endif;
                                        if( $order_data['shipping_address']['postcode'] != '' ):
                                            $html .= $order_data['shipping_address']['postcode']."<br/>";
                                        endif;
                                        if( $order_data['shipping_address']['formated_state'] != '' ):
                                            $html .= $order_data['shipping_address']['formated_state'].", ";
                                        endif;
                                        if( $order_data['shipping_address']['formated_country'] != '' ):
                                            $html .= $order_data['shipping_address']['formated_country'];
                                        endif;
                                    $html .= "</p>";
                                    if( $this->dispatch_label_settings['customer_note'] == true && $order_data['customer_note'] != '' ):
                                        $html .= "<p>".__( 'Customer Note', 'pdf-invoices-and-packing-slips-for-woocommerce' ).': '.$order_data['customer_note']."</p>";
                                    endif;
                                $html .= "</div>
                            </div>
                        </div>
                        <div class='pdf_bottom_wrap'>
                            <div class='pdf_prdlist_wrap'>
                                <table class='pdf_prdlist_table'>
                                    <thead>
                                        <tr>
                                            <th width='11%'>".__( 'SKU', 'pdf-invoices-and-packing-slips-for-woocommerce' )."</th>
                                            <th width='22%'>".__( 'Product', 'pdf-invoices-and-packing-slips-for-woocommerce' )."</th>
                                            <th>".__( 'Quantity', 'pdf-invoices-and-packing-slips-for-woocommerce' )."</th>
                                            <th>".__( 'Price', 'pdf-invoices-and-packing-slips-for-woocommerce' )."</th>
                                            <th>".__( 'Tax Rate', 'pdf-invoices-and-packing-slips-for-woocommerce' )."</th>
                                            <th>".__( 'Tax Type', 'pdf-invoices-and-packing-slips-for-woocommerce' )."</th>
                                            <th>".__( 'Tax Value', 'pdf-invoices-and-packing-slips-for-woocommerce' )."</th>
                                            <th>".__( 'Total', 'pdf-invoices-and-packing-slips-for-woocommerce' )."</th>
                                        </tr>
                                    </thead>
                                    <tbody>";
                                        $cart_subtotal = 0;
                                        foreach( $order_prd_infos as $prd ):
                                            $html .= "<tr>";
                                                $html .= "<td width='11%'>".($prd['sku'] ? $prd['sku']: '--')."</td>";
                                                $html .= "<td width='22%'>";
                                                    $html .= $prd['name'];
                                                    if( $prd['meta'] ){
                                                        $html .= "<br/><small>".$prd['meta']."</small>";
                                                    }
                                                $html .= "</td>";
                                                $html .= "<td>".$prd['quantity']."</td>";
                                                $html .= "<td>".wc_price($prd['price'], array('currency' => $order_data['currency']))."</td>";
                                                $html .= "<td>".$prd['tax_percent']."%</td>";
                                                $html .= "<td>".$prd['tax_label']."</td>";
                                                $html .= "<td>".wc_price($prd['subtotal_tax'], array('currency' => $order_data['currency']))."</td>";
                                                $product_total = $prd['subtotal'] + $prd['subtotal_tax'];
                                                $cart_subtotal = $cart_subtotal + $product_total;
                                                $html .= "<td>".wc_price($product_total, array('currency' => $order_data['currency']))."</td>
                                            </tr>";
                                        endforeach;
                                    $html .= "</tbody>
                                </table>
                            </div>
                            <div class='pdf_prdsubtbl_wrap'>
                                <table class='pdf_prdlist_subtable' ".($rtl ? "align='left'" : "align='right'" ).">
                                    <tbody>
                                        <tr>
                                            <td>".__('Subtotal', 'pdf-invoices-and-packing-slips-for-woocommerce')."</td>";
                                            $html .= "<td>".wc_price($cart_subtotal, array('currency' => $order_data['currency']))."</td>
                                        </tr>
                                        <tr>
                                            <td>".__('Discount', 'pdf-invoices-and-packing-slips-for-woocommerce')."</td>
                                            <td>"."- ".wc_price($order_data['total_discount'], array('currency' => $order_data['currency']))."</td>
                                        </tr>
                                        <tr>
                                            <td>".__('Discount Tax', 'pdf-invoices-and-packing-slips-for-woocommerce')."</td>
                                            <td>"."- ".wc_price($order_data['discount_tax'], array('currency' => $order_data['currency']))."</td>
                                        </tr>
                                        <tr>
                                            <td>".__('Shipping', 'pdf-invoices-and-packing-slips-for-woocommerce')."</td>
                                            <td>".wc_price($order_data['total_shipping'], array('currency' => $order_data['currency']))."</td>
                                        </tr>
                                        <tr>
                                            <td>".__('Shipping Tax', 'pdf-invoices-and-packing-slips-for-woocommerce')."</td>
                                            <td>".wc_price($order_data['shipping_tax'], array('currency' => $order_data['currency']))."</td>
                                        </tr>
                                        <tr>
                                            <td>".__('Fee', 'pdf-invoices-and-packing-slips-for-woocommerce')."</td>
                                            <td>".wc_price($order_fee, array('currency' => $order_data['currency']))."</td>
                                        </tr>
                                        <tr>
                                            <td>".__('Total', 'pdf-invoices-and-packing-slips-for-woocommerce')."</td>
                                            <td>".wc_price($order_data['total'], array('currency' => $order_data['currency']))."</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>";
                        if( $this->dispatch_label_settings['add_footer'] == true && $footer_txt != '' ):
                            $html .= "<div class='pdf_footer_wrap'>
                                <p>".__($footer_txt, 'pdf-invoices-and-packing-slips-for-woocommerce')."</p>
                            </div>";
                        endif;
                    $html .= "</div>";
                    $count++;
                endforeach;
            $html .= "</body>
        </html>";
        //returning html
        return $html;
    }
}