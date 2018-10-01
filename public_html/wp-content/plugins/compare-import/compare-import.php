<?php
/*
Plugin Name: Compare Import
Description: Make sure that you have installed WP All Import in order to use this functionality
Version: 1.0
Author: PowerThemes
*/

include "rapid-addon.php";

header('Content-type: text/html; charset=utf-8');

$compare_addon = new RapidAddon('Compare Product', 'compare_addon');
/*
Add fields
*/
$compare_addon->add_title( __( 'Product Details', 'compare' ) );
$compare_addon->add_text( __( 'All fields for importing offers are located in this section and you do not need to set anything in the Custom Fields section regarding offers. All required fields are marked with *', 'compare' ) );

$compare_addon->add_field( 'store_id', __( 'Store ID', 'compare' ), 'text', null, __( 'Input Store ID which you can find on the store listing page', 'compare' ) );
$compare_addon->add_field( 'price', __( 'Product Price', 'compare' ), 'text' );
$compare_addon->add_field( 'short_description', __( 'Product Short Description', 'compare' ), 'text' );
$compare_addon->add_field( 'link', __( 'Product Link', 'compare' ), 'text' );
$compare_addon->add_field( 'shipping', __( 'Product Shipping', 'compare' ), 'text' );
$compare_addon->add_field( 'shipping_comment', __( 'Product Shipping Comment', 'compare' ), 'text' );
$compare_addon->add_field( 'product_id', __( 'Product ID', 'compare' ), 'text', null, __( 'This field is used to compare if two products are the same. So two stores offering same product must have same ID ( you can also put some other field for which you know that it is common )', 'compare' ) );

$compare_addon->disable_default_images();
$compare_addon->add_field( 'product_image', 'Product Image', 'image' );

/*
Starting import functions and handling of import data
*/
$compare_addon->set_import_function('compare_p_import');

$compare_addon->admin_notice( __( 'Compare now can work with WP All Import in order to import feeds', 'compare' ) );

$compare_addon->run(
	array(
		"post_types" => array( "product" )
	)
); 

function compare_p_import($post_id, $data, $import_options) {
	global $compare_addon, $wpdb;

	$store = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stores WHERE store_id = %d", $data['store_id'] ) );
	if( !empty( $store ) ){
		$store = array_shift( $store );
		if( $store->store_status == '1' ){
			if( empty( $store->store_expire_time ) || current_time( 'timestamp' ) <= $store->store_expire_time ){
				$remain = $store->store_expire_time - current_time( 'timestamp' );
				if( $remain <= 86400*3 ){
					/* send mail that store package will soon expire with some link to prolongate */
					$remain  = compare_time2string( $remain );
					$wpdb->query(
						$wpdb->prepare(
							"UPDATE {$wpdb->prefix}stores SET store_update = %s WHERE store_id = %d",
							$hash,
							$store_id
						)
					);			
					$link = add_query_arg( array( 'hash' => $hash ), compare_get_permalink_by_tpl( 'page-tpl_register_store' ) );

					$message = __( 'Your store will expire and feed associated with your store will be deleted unless you prolongate your package.', 'compare' )."<br/><br/>";
					$message .= __( 'Time remaining: ', 'compare' ).$remain."<br/><br/>";
					$message .= __( 'In order to prolongate your store visit link bellow and select your new package.', 'compare' )."<br/><br/>";
					$message .= '<a href="'.esc_attr( $link ).'" target="_blank">'.$link.'</a>';

					compare_inform_store( $message, $store->store_contact_email, __( 'Store Is About To Expire - Notification', 'compare' ) );
					$compare_addon->log( __( 'Store is about to expire, owner has been informed.', 'compare' ) );
				}

				$product_exists = compare_if_product_exists( $data['product_id'] );
				if( empty( $product_exists ) ){
					$product_id = $post_id;
					update_post_meta( $product_id, 'product_unique_id', $data['product_id']  );
					update_post_meta( $product_id, 'product_short', $data['short_description'] );
					if( !empty( $data['product_image']['attachment_id'] ) ){
						set_post_thumbnail( $post_id, $data['product_image']['attachment_id'] );
					}
				}
				else{
					if( $post_id !== $product_exists[0]->ID ){
						wp_delete_post( $post_id, true );
					}
					$product_id = $product_exists[0]->ID;
				}
				$compare_addon->log( $product_id );
				compare_add_feed_row( $product_id, $data['store_id'], $data['link'], $data['price'], $data['shipping'], $data['shipping_comment'], date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) );

			}
			else{
				/*store has expired delete its feeds*/
				$wpdb->query(
					$wpdb->prepare(
						"DELETE FROM {$wpdb->prefix}feed_list WHERE store_id = %d",
						$store_id
					)
				);

				/* mark store as not paid and set available for update */
				$wpdb->query(
					$wpdb->prepare(
						"UPDATE {$wpdb->prefix}stores SET store_status = '0', store_update = %s WHERE store_id = %d",
						$hash,
						$store_id
					)
				);
				/* inform user that store has expired, feeds deleted and add some links where he can prolongate this */
				$link = add_query_arg( array( 'hash' => $hash ), compare_get_permalink_by_tpl( 'page-tpl_register_store' ) );

				$message = __( 'Your store has been expired and feed associated with your store have been deleted.', 'compare' )."<br/><br/>";
				$message .= __( 'In order to activate your store again visit link bellow and select your new package.', 'compare' )."<br/><br/>";	
				$message .= '<a href="'.esc_attr( $link ).'" target="_blank">'.$link.'</a>';

				compare_inform_store( $message, $store->store_contact_email, __( 'Store Has Expired - Feeds Removed', 'compare' ) );
				$compare_addon->log( __( 'Store has expired, owner has been informed.', 'compare' ) );
			}
		}
		else{
			$message = __( 'Store ', 'compare' ).'<strong>'.$store->store_name.'</strong>'.__( ' is disabled since it is not paid for.', 'compare' );
			$compare_addon->log( $message );
			wp_delete_post( $post_id, true );
		}
	}
	else{
		wp_delete_post( $post_id, true );
	}
}
?>