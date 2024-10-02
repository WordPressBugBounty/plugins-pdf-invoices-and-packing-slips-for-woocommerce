<?php
/**
 * wordpress cron for deleting old invoice pdf files
*/

// action that runs on cron
add_action( 'apifw_invoice_delete_cron', 'apifw_invoice_delete_cron_callback' );
function apifw_invoice_delete_cron_callback() {
    $inv_dir = APIFW_UPLOAD_INVOICE_DIR;
    $files = glob( $inv_dir . '/*' );
    foreach( $files as $file ){
        if( is_file( $file ) ){
            $ext = pathinfo( $file, PATHINFO_EXTENSION );
            if( $ext == 'pdf' ) {
                $file_time = filemtime( $file );
                $hour_ago = strtotime('-1 hour');
                if( $file_time < $hour_ago ) {
                    unlink( $file );
                }
            }
        }
    }
}