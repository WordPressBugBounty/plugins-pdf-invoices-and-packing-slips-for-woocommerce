<?php
/**
 * classic template.
 *
 * This template can be overriden by copying this file to your-theme/pdf-invoices-and-packing-slips-for-woocommerce-pro/packing-slip-templates/ps-classic.php
 *
 * @author 	Acowebs
 * @package pdf-invoices-and-packing-slips-for-woocommerce-pro/packing-slip-templates
 * @version 1.0.0
*/

// Don't allow direct access
if ( ! defined('ABSPATH') )
    exit;

$html = "<!DOCTYPE html>
<html ".($rtl ? "dir='rtl'" : '' ).">
    <head>
        <meta charset='utf-8'>
        <title>".__( 'Packing Slip', 'pdf-invoices-and-packing-slips-for-woocommerce' )."</title>
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
                color: ".$heading_color.";
            }
            h2 {
                margin: 0;
                padding: 0;
                line-height: 34px;
                font-size: 18px;
                font-weight: bold;
                color: ".$heading_color.";
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
            .pdf_prdlist_table {
                width: 100%;
                border-collapse: collapse;
                border: 1px solid #cccccc;
            }
            .pdf_prdlist_table th {
                padding: 16px 10px;
                border-right: 1px solid #191919;
                border-bottom: 1px solid #191919;
                border-top: 1px solid #191919;
                background-color: #020202;
                font-size: 14px;
                font-weight: bold;
                color: #fff;
                text-align: center;
            }
            .pdf_prdlist_table tr th:nth-child(1){
                border-left: 1px solid #191919; 
            }
            .pdf_prdlist_table td {
                padding: 10px;
                border-bottom: 1px solid #cccccc;
                border-right: 1px solid #cccccc;
                border-top: 0;
                font-size: 14px;
                font-weight: normal;
                color: rgb(27, 39, 51);
                text-align: center;
            }
            .pdf_prdlist_table td:nth-child(1){
                border-left: 1px solid #cccccc;
            }
            .pdf_prdlist_table img {
                height: auto;
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
            } ";
            if( isset( $this->packing_slip_settings['customCss'] ) && $this->packing_slip_settings['customCss'] != '' ):
                $html .= $this->packing_slip_settings['customCss'];
            endif;
        $html .= "</style>
    </head>
    <body ".($rtl ? "dir='rtl'" : '' ).">";
        $count = 1;
        foreach( $order_ids as $order_id ):
            $order_data = apifw_get_order_detail( $order_id );
            $order_prd_infos = $order_data['items'];
            if( $slip_per_item ):
                $c = 1;
                foreach( $order_prd_infos as $prd ):
                    $breake_avoid = 'page-break-after: always;';
                    if( $count == count($order_ids) && $c == count($order_prd_infos) ){
                        $breake_avoid = 'page-break-after: avoid;';
                    }

                    $html .= "<div class='pdf_wrapper ".($rtl ? 'doc_rtl' : '' )."' style='".$breake_avoid."'>
                        <div class='top_sec'>";
                            $html .= "<div class='logowrap'>";
                                if( $company_logo ):
                                    $html .= "<img src='".$company_logo."' alt='".$company_name."'/>";
                                else:
                                    $html .= "<h2>".$company_name."</h2>";
                                endif;     
                                $html .= "<p style='margin-top: 10px;'>".__( $order_no_lbl, 'pdf-invoices-and-packing-slips-for-woocommerce' ).' '.$order_data['order_number']."</p>";
                                $date = date_create( $order_data['created_at'] );
                                $html .= "<p>".__( $date_lbl, 'pdf-invoices-and-packing-slips-for-woocommerce' ).' '.date_format( $date, $date_format )."</p>";
                            $html .= "</div>";
                        
                            $html .= "<div class='from_address_wrap'>";
                                $html .= "<h4>".__( $from_address_title, 'pdf-invoices-and-packing-slips-for-woocommerce' )."</h4>";
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
                        </div>
                        <div class='pdf_midsec_wrap'>
                            <div class='billaddress_wrap'>";
                                $html .= "<h4>".__( $billing_address_title, 'pdf-invoices-and-packing-slips-for-woocommerce' )."</h4>";
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
                                            $html .= $order_data['billing_address']['city']."<br/>";
                                        endif;
                                        if( $order_data['billing_address']['formated_state'] != '' ):
                                            $html .= $order_data['billing_address']['formated_state']."<br/>";
                                        endif;
                                        if( $order_data['billing_address']['postcode'] != '' ):
                                            $html .= $order_data['billing_address']['postcode']."<br/>";
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
                                        $cb_fields = apply_filters( 'apifw_ps_custom_billing_fields', $custom_fields='', $order_id );
                                        $html .= $cb_fields;
                                    $html .= "</p>
                                </div>
                            </div>
                            <div class='shipping_address_wrap'>";
                                $html .= "<h4>".__( $shipping_address_title, 'pdf-invoices-and-packing-slips-for-woocommerce' )."</h4>";
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
                                            $html .= $order_data['shipping_address']['formated_country'];
                                        endif;
                                        $cs_fields = apply_filters( 'apifw_ps_custom_shipping_fields', $custom_fields='', $order_id );
                                        $html .= $cs_fields;
                                    $html .= "</p>";
                                    if( $this->packing_slip_settings['customer_note'] == true && $order_data['customer_note'] != '' ):
                                        $html .= "<p>".__( 'Customer Note', 'pdf-invoices-and-packing-slips-for-woocommerce' ).': '.$order_data['customer_note']."</p>";
                                    endif;
                                $html .= "</div>
                            </div>
                        </div>
                        <div class='pdf_bottom_wrap'>
                            <table class='pdf_prdlist_table'>
                                <thead>
                                    <tr>";
                                        if( $this->packing_slip_settings['prd_img'] == true ):
                                            $html .= "<th style='".$tbl_head_styles."'>".__( $tbl_img_lbl, 'pdf-invoices-and-packing-slips-for-woocommerce' )."</th>";
                                        endif;
                                        $html .= "<th style='".$tbl_head_styles."'>".__( $tbl_sku_lbl, 'pdf-invoices-and-packing-slips-for-woocommerce' )."</th>
                                        <th style='".$tbl_head_styles."'>".__( $tbl_prd_name_lbl, 'pdf-invoices-and-packing-slips-for-woocommerce' )."</th>
                                        <th style='".$tbl_head_styles."'>".__( $tbl_qty_lbl, 'pdf-invoices-and-packing-slips-for-woocommerce' )."</th>
                                        <th style='".$tbl_head_styles."'>".__( $tbl_weight_lbl, 'pdf-invoices-and-packing-slips-for-woocommerce' )."</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>";
                                        if( $this->packing_slip_settings['prd_img'] == true ):
                                            $html .= "<td style='".$tbl_body_styles."'>";
                                                if( $prd['product_thumbnail_url'] ) {
                                                    $prd_img = $prd['product_thumbnail_url'];
                                                } else {
                                                    $prd_img = plugin_dir_url( plugin_dir_path( __FILE__ ) ).'assets/images/woocommerce-placeholder.png';
                                                }
                                                $html .= "<img src='". $prd_img."' alt='".$prd['name']."' width='50px' />";
                                            $html .="</td>";
                                        endif;
                                        $html .= "<td style='".$tbl_body_styles."'>".($prd['sku'] ? $prd['sku']: '--')."</td>";
                                        $html .= "<td style='".$tbl_body_styles."'>";
                                            $html .= $prd['name'];
                                            if( $prd['meta'] ){
                                                $html .= "<br/><small>".$prd['meta']."</small>";
                                            }
                                        $html .= "</td>";
                                        $html .= "<td style='".$tbl_body_styles."'>".$prd['quantity']."</td>";
                                        $html .= "<td style='".$tbl_body_styles."'>".( $prd['total_weight'] > 0 ? $prd['total_weight'].' '.$prd['weight_unit'] : __( 'N/A', 'pdf-invoices-and-packing-slips-for-woocommerce' ) )."</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>";
                        if( $this->packing_slip_settings['add_footer'] == true && $footer_txt != '' ):
                            $html .= "<div class='pdf_footer_wrap'>
                                <p>".__($footer_txt, 'pdf-invoices-and-packing-slips-for-woocommerce')."</p>";
                                $c_data = apply_filters( 'apifw_ps_footer', $custom_text='', $order_id );
                                $html .= "<p>".$c_data."<p>";
                            $html .= "</div>";
                        endif;
                    $html .= "</div>";
                    $c++;
                endforeach;
            else:
                $breake_avoid = '';
                if( $count == count($order_ids) ){
                    $breake_avoid = 'page-break-after: avoid;';
                }

                $html .= "<div class='pdf_wrapper ".($rtl ? 'doc_rtl' : '' )."' style='".$breake_avoid."'>
                    <div class='top_sec'>";
                        $html .= "<div class='logowrap'>";
                            if( $company_logo ):
                                $html .= "<img src='".$company_logo."' alt='".$company_name."'/>";
                            else:
                                $html .= "<h2>".$company_name."</h2>";
                            endif;     
                            $html .= "<p style='margin-top: 10px;'>".__( $order_no_lbl, 'pdf-invoices-and-packing-slips-for-woocommerce' ).' '.$order_data['order_number']."</p>";
                            $date = date_create( $order_data['created_at'] );
                            $html .= "<p>".__( $date_lbl, 'pdf-invoices-and-packing-slips-for-woocommerce' ).' '.date_format( $date, $date_format )."</p>";
                        $html .= "</div>";
                    
                        $html .= "<div class='from_address_wrap'>";
                            $html .= "<h4>".__( $from_address_title, 'pdf-invoices-and-packing-slips-for-woocommerce' )."</h4>";
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
                    </div>
                    <div class='pdf_midsec_wrap'>
                        <div class='billaddress_wrap'>";
                            $html .= "<h4>".__( $billing_address_title, 'pdf-invoices-and-packing-slips-for-woocommerce' )."</h4>";
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
                                        $html .= $order_data['billing_address']['city']."<br/>";
                                    endif;
                                    if( $order_data['billing_address']['formated_state'] != '' ):
                                        $html .= $order_data['billing_address']['formated_state']."<br/>";
                                    endif;
                                    if( $order_data['billing_address']['postcode'] != '' ):
                                        $html .= $order_data['billing_address']['postcode']."<br/>";
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
                                    $cb_fields = apply_filters( 'apifw_ps_custom_billing_fields', $custom_fields='', $order_id );
                                    $html .= $cb_fields;
                                $html .= "</p>
                            </div>
                        </div>
                        <div class='shipping_address_wrap'>";
                            $html .= "<h4>".__( $shipping_address_title, 'pdf-invoices-and-packing-slips-for-woocommerce' )."</h4>";
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
                                        $html .= $order_data['shipping_address']['formated_country'];
                                    endif;
                                    $cs_fields = apply_filters( 'apifw_ps_custom_shipping_fields', $custom_fields='', $order_id );
                                    $html .= $cs_fields;
                                $html .= "</p>";
                                if( $this->packing_slip_settings['customer_note'] == true && $order_data['customer_note'] != '' ):
                                    $html .= "<p>".__( 'Customer Note', 'pdf-invoices-and-packing-slips-for-woocommerce' ).': '.$order_data['customer_note']."</p>";
                                endif;
                            $html .= "</div>
                        </div>
                    </div>
                    <div class='pdf_bottom_wrap'>
                        <table class='pdf_prdlist_table'>
                            <thead>
                                <tr>";
                                    if( $this->packing_slip_settings['prd_img'] == true ):
                                        $html .= "<th style='".$tbl_head_styles."'>".__( $tbl_img_lbl, 'pdf-invoices-and-packing-slips-for-woocommerce' )."</th>";
                                    endif;
                                    $html .= "<th style='".$tbl_head_styles."'>".__( $tbl_sku_lbl, 'pdf-invoices-and-packing-slips-for-woocommerce' )."</th>
                                    <th style='".$tbl_head_styles."'>".__( $tbl_prd_name_lbl, 'pdf-invoices-and-packing-slips-for-woocommerce' )."</th>
                                    <th style='".$tbl_head_styles."'>".__( $tbl_qty_lbl, 'pdf-invoices-and-packing-slips-for-woocommerce' )."</th>
                                    <th style='".$tbl_head_styles."'>".__( $tbl_weight_lbl, 'pdf-invoices-and-packing-slips-for-woocommerce' )."</th>
                                </tr>
                            </thead>
                            <tbody>";
                                foreach( $order_prd_infos as $prd ):
                                    $html .= "<tr>";
                                        if( $this->packing_slip_settings['prd_img'] == true ):
                                            $html .= "<td style='".$tbl_body_styles."'>";
                                                if( $prd['product_thumbnail_url'] ) {
                                                    $prd_img = $prd['product_thumbnail_url'];
                                                } else {
                                                    $prd_img = plugin_dir_url( plugin_dir_path( __FILE__ ) ).'assets/images/woocommerce-placeholder.png';
                                                }
                                                $html .= "<img src='". $prd_img."' alt='".$prd['name']."' width='50px' />";
                                            $html .="</td>";
                                        endif;
                                        $html .= "<td style='".$tbl_body_styles."'>".($prd['sku'] ? $prd['sku']: '--')."</td>";
                                        $html .= "<td style='".$tbl_body_styles."'>";
                                            $html .= $prd['name'];
                                            if( $prd['meta'] ){
                                                $html .= "<br/><small>".$prd['meta']."</small>";
                                            }
                                        $html .= "</td>";
                                        $html .= "<td style='".$tbl_body_styles."'>".$prd['quantity']."</td>";
                                        $html .= "<td style='".$tbl_body_styles."'>".( $prd['total_weight'] > 0 ? $prd['total_weight'].' '.$prd['weight_unit'] : __( 'N/A', 'pdf-invoices-and-packing-slips-for-woocommerce' ) )."</td>
                                    </tr>";
                                endforeach;
                            $html .= "</tbody>
                        </table>
                    </div>";
                    if( $this->packing_slip_settings['add_footer'] == true && $footer_txt != '' ):
                        $html .= "<div class='pdf_footer_wrap'>
                            <p>".__($footer_txt, 'pdf-invoices-and-packing-slips-for-woocommerce')."</p>";
                            $c_data = apply_filters( 'apifw_ps_footer', $custom_text='', $order_id );
                            $html .= "<p>".$c_data."<p>";
                        $html .= "</div>";
                    endif;
                $html .= "</div>";
            endif;
            $count++;
        endforeach;
    $html .= "</body>
</html>";

echo $html;