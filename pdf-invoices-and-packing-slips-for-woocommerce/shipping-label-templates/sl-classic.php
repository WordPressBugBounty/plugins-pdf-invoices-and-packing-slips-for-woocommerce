<?php
/**
 * classic template.
 *
 * This template can be overriden by copying this file to your-theme/pdf-invoices-and-packing-slips-for-woocommerce-pro/shipping-label-templates/sl-classic.php
 *
 * @author 	Acowebs
 * @package pdf-invoices-and-packing-slips-for-woocommerce-pro/shipping-label-templates
 * @version 1.0.0
*/

// Don't allow direct access
if ( ! defined('ABSPATH') )
    exit;

$html = "<!DOCTYPE html>
<html ".($rtl ? "dir='rtl'" : '' ).">
    <head>
        <meta charset='utf-8'>
        <title>".__( 'Shipping Label', 'pdf-invoices-and-packing-slips-for-woocommerce' )."</title>
        <style>
            body {
                position: relative;
                color: #000000;
            }
            p {
                margin: 0;
                padding: 0;
                font-size: 13px;
                font-weight: normal;
                line-height: 1.8;
                color: #000000;
            }
            h4 {
                line-height: 1.8;
                margin: 0 0 4px;
                padding: 0;
                font-size: 16px;
                font-weight: normal;
                color: #000000;
            }
            .pdf_wrapper {
                width: 93%;
                padding: 20px 3.5% 10px;
                background: #ffffff;
                border: 1px solid #000;";   
                if( count( $order_ids ) > 1 ):
                    $html .= "page-break-after: always;";
                endif;
                $html .= "
            }
            .logowrap {
                margin-bottom: 14px;
            }
            .top_sec {
                width: 100%; 
                clear:both;
                margin-bottom: 10px;
            }
            .from_address_wrap {
                float:left; 
                width: 40.333%;
            }
            .from_address_inner {
                width: 100%;
                clear: both;
            }
            .shipping_address_wrap {
                float:left; 
                width: 40.333%;
                padding-left: 2.5%;
            }
            .top_order_meta {
                float:left;
                width: 14.333%;
                padding-left: 2.5%;
                text-align: right;
            }
            .pdf_order_barcode {
                text-align: center;
            }
            .pdf_order_barcode p {
                margin-bottom: 5px;
            }
            .pdf_footer_wrap {
                width: 100%;
                margin-bottom: 12px;
                margin-top: 10px;
            }
            .pdf_footer_wrap p {
                font-size: 13px;
                font-weight: normal;
                color: #000000;
                text-align: left;
                line-height: 24px;
                text-align: center;
            }
            .doc_rtl .logowrap {
                text-align: right;
            }
            .doc_rtl .from_address_wrap {
                float: right;
            }
            .doc_rtl .shipping_address_wrap {
                float: right; 
                padding-left: 0%;
                padding-right: 2.5%;
            }
            .doc_rtl .top_order_meta {
                float: right;
                padding-left: 0%;
                padding-right: 2.5%;
            }";
            if( isset( $this->shipping_label_settings['customCss'] ) && $this->shipping_label_settings['customCss'] != '' ):
                $html .= $this->shipping_label_settings['customCss'];
            endif;
        $html .= "</style>
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
            $order = wc_get_order($order_id);
            // $order_invoice_no = get_post_meta( $order_id, $this->_token.'_ord_invoice_no', true );
            $order_invoice_no = $order->get_meta($this->_token.'_ord_invoice_no', true);
            //order total weight calculation
            $ord_total_weight = 0;
            foreach( $order_prd_infos as $prd ) {
                if( $prd['total_weight'] && is_numeric( $prd['total_weight'] ) ){
                    $ord_total_weight = $ord_total_weight + $prd['total_weight'];
                }
            }
            // tracking number
            // $tracking_number = get_post_meta( $order_id, 'apifw_order_tracking_number', true );
            $tracking_number = $order->get_meta('apifw_order_tracking_number', true);
            if( !$tracking_number ){
                $tracking_number = $order_id;
            }

            $html .= "<div class='pdf_wrapper ".($rtl ? 'doc_rtl' : '' )."' style='".$breake_avoid."'>";
                if( $show_logo && $company_logo ):
                    $html .= "<div class='logowrap'>";
                        $html .= "<img src='".$company_logo."' alt='".$company_name."'/>";
                    $html .= "</div>";
                endif;
                $html .= "<div class='top_sec'>";
                    $html .= "<div class='from_address_wrap'>";
                        $html .= "<h4>".__( $from_lbl, 'pdf-invoices-and-packing-slips-for-woocommerce' )."</h4>";
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
                            if( $sender_postal_code ):
                                $html .= "<p>".$sender_postal_code."</p>";
                            endif;
                            if( $sender_country ):
                                $html .= "<p>".$sender_country."</p>";
                            endif;
                            if( $sender_email ):
                                $html .= "<p>".$sender_email."</p>";
                            endif;
                            if( $sender_number ):
                                $html .= "<p>".$sender_number."</p>";
                            endif;
                            // if( $tax_reg_no ):
                            //     $html .= "<p>".$tax_reg_no."</p>";
                            // endif;
                        $html .= "</div>
                    </div>
                    <div class='shipping_address_wrap'>";
                        $html .= "<h4>".__( $to_lbl, 'pdf-invoices-and-packing-slips-for-woocommerce' )."</h4>";
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
                                    $html .= $order_data['shipping_address']['city']."<br/>";
                                endif;
                                if( $order_data['shipping_address']['formated_state'] != '' ):
                                    $html .= $order_data['shipping_address']['formated_state']."<br/>";
                                endif;
                                if( $order_data['shipping_address']['postcode'] != '' ):
                                    $html .= $order_data['shipping_address']['postcode']."<br/>";
                                endif;
                                if( $order_data['shipping_address']['formated_country'] != '' ):
                                    $html .= $order_data['shipping_address']['formated_country']."<br/>";
                                endif;
                                if( $order_data['billing_address']['email'] != '' ):
                                    $html .= __( $email_lbl, 'pdf-invoices-and-packing-slips-for-woocommerce' ).' '.$order_data['billing_address']['email']."<br/>";
                                endif;
                                if( $order_data['billing_address']['phone'] != '' ):
                                    $html .= __( $phone_lbl, 'pdf-invoices-and-packing-slips-for-woocommerce' ).' '.$order_data['billing_address']['phone'];
                                endif;
                                $cs_fields = apply_filters( 'apifw_sl_custom_shipping_fields', $custom_fields='', $order_id );
                                $html .= $cs_fields;
                            $html .= "</p>
                        </div>
                    </div>
                    <div class='top_order_meta'>";
                        $html .= "<p>".__( $order_no_lbl, 'pdf-invoices-and-packing-slips-for-woocommerce' ).' '.$order_data['order_number']."</p>";
                        $html .= "<p>".__( $weight_lbl, 'pdf-invoices-and-packing-slips-for-woocommerce' ).' '.($ord_total_weight > 0 ? $ord_total_weight.' '.$prd['weight_unit'] : __('N/A', 'pdf-invoices-and-packing-slips-for-woocommerce') )."</p>";
                    $html .= "</div>
                </div>";
                $html .= "<div class='pdf_order_barcode'>";
                    $html .= "<p>".__( $tracking_no_lbl, 'pdf-invoices-and-packing-slips-for-woocommerce' ).' '.$tracking_number."</p>";
                    if( $show_barcode ):
                        $html .= "<barcode code='".$tracking_number."' type='C39E+' size='1' text='0' height='1'/>";
                    endif;
                $html .= "</div>";
                if( $this->shipping_label_settings['add_footer'] == true && $footer_txt != '' ):
                    $html .= "<div class='pdf_footer_wrap'>
                        <p>".__($footer_txt, 'pdf-invoices-and-packing-slips-for-woocommerce')."</p>";
                        $c_data = apply_filters( 'apifw_shipping_footer', $custom_text='', $order_id );
                        $html .= "<p>".$c_data."<p>";
                    $html .= "</div>";
                endif;
            $html .= "</div>";
            $count++;
        endforeach;
    $html .= "</body>
</html>";

echo $html;