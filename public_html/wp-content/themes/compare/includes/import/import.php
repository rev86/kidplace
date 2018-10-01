<?php
	function compare_csv_to_xml( $link ){
		$r = "<products>\n\r";
	    $row = 0;
	    $cols = 0;
	    $titles = array();

	    $handle = fopen( $link, 'r' );
	    if (!$handle) return $handle;

	    while ( ( $data = fgetcsv( $handle, 0, ',' ) ) !== false ){
	         if ($row > 0) $r .= "\t<product>";
	         if (!$cols) $cols = count($data);
	         for ($i = 0; $i < $cols; $i++){
	              if ($row == 0){
	                   $titles[$i] = $data[$i];
	                   continue;
	              }

	              $r .= "\t\t<{$titles[$i]}>";
	              $r .= "<![CDATA[".$data[$i]."]]>";
	              $r .= "</{$titles[$i]}>\n\r";
	         }
	         if ($row > 0){
	         	$r .= "\t</product>\n\r";
	         }
	         $row++;
	    }
	    fclose( $handle );
	    $r .= "</products>";

	    return $r;
	}


	/* Display messages during import process */
	function compare_display_import_info( $message ){
		echo '<br/>'.$message;
	}

	/* Assign existing or create a new store for the offer */
	function compare_validate_store( $offer, $offer_id ){

	}

	function compare_if_image_exists( $filename ){
		global $wpdb;
		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value LIKE %s",
				'%'.$filename.'%'
			)
		);

		if( !empty( $result ) ){
			$result = array_shift( $result );
			return $result->post_id;
		}
		else{
			return false;
		}

	}

	/* This handles import of the images via URL */
	function compare_import_image( $image_url, $product_id ){
		if( !empty( $image_url ) ){
			$image_url = trim( $image_url );
			preg_match('/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $image_url, $matches);
			if( !empty( $matches ) ){
				$basename = basename($matches[0]);
			}
			else if( stristr( $image_url, '?' ) ){
				$tt = explode( '?', $image_url );
				$tt2 = explode( '/', $tt[0] );
				$basename = $tt2[sizeof($tt2) - 1].'.jpg';
			}
			$image_id = compare_if_image_exists( $basename );
			if( !$image_id ){
				$tmp = download_url( (string)$image_url );

				compare_display_import_info( __( 'Downloading image: ', 'compare' ).$basename );

				$file_array = array();
				$file_array['name'] = $basename;
				$file_array['tmp_name'] = $tmp;
				// If error storing temporarily, unlink
				if ( is_wp_error( $tmp ) ) {
					@unlink($file_array['tmp_name']);
					compare_display_import_info( $tmp->get_error_message() );
					$file_array['tmp_name'] = '';
					$image_id = '';
				}

				// do the validation and storage stuff
				$id = media_handle_sideload( $file_array, 0 );

				// If error storing permanently, unlink
				if ( is_wp_error($id) ) {
					compare_display_import_info( $id->get_error_message() );
					@unlink($file_array['tmp_name']);
					$image_id = '';
				}

				$image_id =  $id;
			}
			else{
				compare_display_import_info( __( 'Image ', 'compare' ).$basename.__( ' exists, skipping import and using existing one.', 'compare' ) );
			}

			if( !empty( $image_id ) ){
				set_post_thumbnail( $product_id, $image_id );
			}
		}
	}

	/* check if product already exists */
	function compare_if_product_exists( $pid ){
		$if_exists = get_posts(
			array(
				'post_type' => 'product',
				'posts_per_page' => '-1',
				'post_status' => 'publish',
				'meta_query' => array(
					array(
						'key' => 'product_unique_id',
						'value' => $pid,
						'compare' => '='
					)
				)
			)
		);
		return $if_exists;
	}

	/* add new product to the list of products */
	function compare_insert_new_product( $name, $description ){
		$product_id = wp_insert_post(
			array(
				'post_type' => 'product',
				'post_status' => 'publish',
				'post_title' => $name,
				'post_content' => $description,
			)
		);

		return $product_id;
	}

	/* add meta values for the product */
	function compare_add_meta_values( $pid, $short_desc, $product_id ){
		update_post_meta( $product_id, 'product_unique_id', $pid );
		update_post_meta( $product_id, 'product_short', $short_desc );
		update_post_meta( $product_id, 'product_clicks', '0' );
		update_post_meta( $product_id, 'product_store_clicks', '0' );
	}

	/* add row to the feed table and delete old one if exists*/
	function compare_add_feed_row( $product_id, $store_id, $url, $price, $shipping, $shipping_comment, $updated ){
		global $wpdb;
		$wpdb->get_results(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}feed_list WHERE post_id = %d AND store_id = %d",
				$product_id,
				$store_id
			)
		);

		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$wpdb->prefix}feed_list VALUES ( '', %d, %d, %f, %s, %f, %s, %s )",
				$product_id,
				$store_id,
				$price,
				$url,
				$shipping,
				$shipping_comment,
				$updated
			)
		);
	}

	/*
	Assign to the existing or create other categories.
	Since it is nesting first it checks for the last category name in the array so if it exists add it to that category
	and skip checking others
	*/
	function compare_process_categories( $product_cat, $product_id, $separator ){
		$product_cats = explode( $separator, $product_cat );
		$last_parent = 0;
		$category_hierarchicy = array();
		foreach( $product_cats as $product_cat ){
			$term = term_exists( $product_cat, 'product-cat');
			if( empty( $term ) ){
				$term = wp_insert_term(
					$product_cat,
					'product-cat',
					array(
						'parent' => $last_parent
					)
				);
			}

			if( !is_wp_error( $term ) ){
				$last_parent = $term['term_id'];
				$category_hierarchicy[] = $term['term_id'];
			}
		}

		if( !empty( $category_hierarchicy ) ){
			wp_set_post_terms( $product_id, $category_hierarchicy, 'product-cat', true );
		}
	}

	/* processing tags */
	function compare_process_tags( $product_tags, $product_id, $separator ){
		if( !empty( $product_tags ) ){
			$product_tags = explode( $separator, $product_tags );
			wp_set_post_terms( $product_id, $product_tags, 'product-tag', true );
		}
	}

	/* handles adding locations in the same way as categories */
	function compare_process_brand( $brand, $product_id ){
		$term = term_exists( $brand, 'product-brand');
		if ( empty( $term ) ) {
			$term = wp_insert_term( $brand, 'product-brand' );
		}
		if( !is_wp_error( $term ) ){
			wp_set_post_terms( $product_id, $term['term_id'], 'product-brand', true );
		}
	}

	function compare_core_import( $name, $description, $categories, $tags, $brand, $image, $pid, $short_desc, $url, $price, $shipping, $shipping_comment, $store_id, $cat_separator = ',', $tag_separator = ',' ){
		if( !empty( $name ) ){
			if( !empty( $categories ) ){
				if( !empty( $brand ) ){
					if( !empty( $price ) ){
						if( !empty( $pid ) ){
							/* check if exists else create one */
							$product_exists = compare_if_product_exists( $pid );
							if( empty( $product_exists ) ){
								$product_id = compare_insert_new_product( $name, $description );
								compare_add_meta_values( $pid, $short_desc, $product_id );
								compare_process_categories( $categories, $product_id, $cat_separator );
								compare_process_tags( $tags, $product_id, $tag_separator );
								compare_process_brand( $brand, $product_id );
								compare_import_image( $image, $product_id );
								compare_display_import_info( __( 'Product ', 'compare' ).'<strong>'.$name.'</strong>'.__( ' has been added.', 'compare' ) );
							}
							else{
								$product_id = $product_exists[0]->ID;
							}

							/* add to feed list */
              $date = date("Y-m-d H:i:s");  
							compare_add_feed_row( $product_id, $store_id, $url, $price, $shipping, $shipping_comment, $date );
							compare_display_import_info( __( 'Price feed for ', 'compare' ).'<strong>'.$name.'</strong>'.__( ' has been added.', 'compare' ) );
							compare_display_import_info( '------------------------------------------------------------------------' );

						}
						else{
							compare_display_import_info( __( 'Product unique ID is required, skipping this product', 'compare' ) );
						}
					}
					else{
						compare_display_import_info( __( 'Product price is required, skipping this product', 'compare' ) );
					}
				}
				else{
					compare_display_import_info( __( 'Product brand is required, skipping this product', 'compare' ) );
				}
			}
			else{
				compare_display_import_info( __( 'Product category is required, skipping this product', 'compare' ) );
			}
		}
		else{
			compare_display_import_info( __( 'Product name is required, skipping this product', 'compare' ) );
		}
	}

	function compare_time2string($time) {
	    $d = floor($time/86400);
	    $_d = ($d < 10 ? '0' : '').$d;

	    $h = floor(($time-$d*86400)/3600);
	    $_h = ($h < 10 ? '0' : '').$h;

	    $m = floor(($time-($d*86400+$h*3600))/60);
	    $_m = ($m < 10 ? '0' : '').$m;

	    $s = $time-($d*86400+$h*3600+$m*60);
	    $_s = ($s < 10 ? '0' : '').$s;

	    $time_str = $_d.( $_d == 1 ? __( ' day', 'compare' ) : __( ' days', 'compare' ) ).' '.$_h.':'.$_m.':'.$_s;

	    return $time_str;
	}

	function compare_inform_store( $message, $mail, $subject ){
	    $headers   = array();
	    $headers[] = "MIME-Version: 1.0";
	    $headers[] = "Content-Type: text/html; charset=UTF-8";

	    $email_sender = compare_get_option( 'email_sender' );
	    $name_sender = compare_get_option( 'name_sender' );

		if( !empty( $email_sender ) && !empty( $name_sender ) ){
			$headers[] = "From: ".$from_name." <".$from_mail.">";
		}

	    $info = @wp_mail( $mail, $subject, $message, $headers );
	}

	/* parse xml file and import offers */
	function compare_store_parse_and_import( $link, $store_id, $store_custom_parser ){
		$internal_errors = libxml_use_internal_errors(true);

		$parser_format = get_post_meta( $store_custom_parser, 'parser_format', true );
		if( $parser_format == 'csv' || stristr( $link, '.csv' ) ){
			$feed_content = compare_csv_to_xml( $link );
		}
		else{
			WP_Filesystem();
			global $wp_filesystem;
			$feed_content = $wp_filesystem->get_contents( $link );
			if( empty( $feed_content ) ){
				$feed_content = file_get_contents( $link );
			}
		}
		$feed_content = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $feed_content);

		$dom = new DOMDocument;
		$old_value = null;
		if ( function_exists( 'libxml_disable_entity_loader' ) ) {
			$old_value = libxml_disable_entity_loader( true );
		}

		$success = $dom->loadXML( $feed_content );
		if ( ! is_null( $old_value ) ) {
			libxml_disable_entity_loader( $old_value );
		}

		if ( ! $success || isset( $dom->doctype ) ) {
			return new WP_Error( 'SimpleXML_parse_error', __( 'There was an error when reading this WXR file', 'compare' ), libxml_get_errors() );
		}

		$xml = simplexml_import_dom( $dom );
		unset( $dom );

		// halt if loading produces an error
		if ( ! $xml ){
			return new WP_Error( 'SimpleXML_parse_error', __( 'There was an error when reading this WXR file', 'compare' ), libxml_get_errors() );
		}
		set_time_limit(0);
		if( empty( $store_custom_parser ) ){
			foreach ( $xml->xpath('/products/product') as $product ) {
				compare_display_import_info( '------------------------------------------------------------------------' );
				compare_display_import_info( __( 'Importing feed for product: <strong>', 'compare' ).$product->name.'</strong>' );

				/* main post attributes */
				$name = !empty( $product->name ) ? trim( (string)$product->name ) : '';
				$description = !empty( $product->description ) ? trim( (string)$product->description ) : '';
				$categories = !empty( $product->categories ) ? trim( (string)$product->categories ) : '';
				$tags = !empty( $product->tags ) ? trim( (string)$product->tags ) : '';
				$brand = !empty( $product->brand ) ? trim( (string)$product->brand ) : '';

				/* featured image */
				$image = !empty( $product->image ) ? trim( (string)$product->image ) : '';

				/* meta values */
				$pid = !empty( $product->pid ) ? trim( (string)$product->pid ) : '';
				$short_desc = !empty( $product->short_desc ) ? trim( (string)$product->short_desc ) : '';

				/* feed values */
				$url = !empty( $product->url ) ? trim( (string)$product->url ) : '';
				$price = !empty( $product->price ) ? trim( (string)$product->price ) : '';
				$shipping = !empty( $product->shipping ) ? trim( (string)$product->shipping ) : '';
				$shipping_comment = !empty( $product->shipping_comment ) ? trim( (string)$product->shipping_comment ) : '';

				compare_core_import( $name, $description, $categories, $tags, $brand, $image, $pid, $short_desc, $url, $price, $shipping, $shipping_comment, $store_id );

			}
		}
		else{
			$parser_product_root = get_post_meta( $store_custom_parser, 'parser_product_root', true );
			$parser_product_name = get_post_meta( $store_custom_parser, 'parser_product_name', true );
			$parser_product_cats = get_post_meta( $store_custom_parser, 'parser_product_cats', true );
			$parser_cats_separator = get_post_meta( $store_custom_parser, 'parser_cats_separator', true );
			$parser_product_tags = get_post_meta( $store_custom_parser, 'parser_product_tags', true );
			$parser_tags_separator = get_post_meta( $store_custom_parser, 'parser_tags_separator', true );
			$parser_product_brand = get_post_meta( $store_custom_parser, 'parser_product_brand', true );
			$parser_product_price = get_post_meta( $store_custom_parser, 'parser_product_price', true );
			$parser_product_url = get_post_meta( $store_custom_parser, 'parser_product_url', true );
			$parser_product_id = get_post_meta( $store_custom_parser, 'parser_product_id', true );
			$parser_desc = get_post_meta( $store_custom_parser, 'parser_desc', true );
			$parser_short_desc = get_post_meta( $store_custom_parser, 'parser_short_desc', true );
			$parser_shipping = get_post_meta( $store_custom_parser, 'parser_shipping', true );
			$parser_shipping_comment = get_post_meta( $store_custom_parser, 'parser_shipping_comment', true );
			$parser_product_image = get_post_meta( $store_custom_parser, 'parser_product_image', true );

			foreach ( $xml->xpath('/'.$parser_product_root ) as $product ) {
				$name = $product->xpath('./'.$parser_product_name );
				$name = !empty( $name ) ? (string)$name[0] : '';

				compare_display_import_info( '------------------------------------------------------------------------' );
				compare_display_import_info( __( 'Importing feed for product: <strong>', 'compare' ).$name.'</strong>' );

				/* main post attributes */
				$description = $product->xpath('./'.$parser_desc );
				$description = !empty( $description ) ? trim( (string)$description[0] ) : '';

				$categories = $product->xpath('./'.$parser_product_cats );
				$categories = !empty( $categories ) ? trim( (string)$categories[0] ) : '';

				$name = $product->xpath('./'.$parser_product_name );
				$name = !empty( $name ) ? trim( (string)$name[0] ) : '';

				$tags = $product->xpath('./'.$parser_product_tags );
				$tags = !empty( $tags ) ? trim( (string)$tags[0] ) : '';

				$brand = $product->xpath('./'.$parser_product_brand );
				$brand = !empty( $brand ) ? trim( (string)$brand[0] ) : '';

				/* featured image */
				$image = $product->xpath('./'.$parser_product_image );
				$image = !empty( $image ) ? trim( (string)$image[0] ) : '';

				/* meta values */
				$pid = $product->xpath('./'.$parser_product_id );
				$pid = !empty( $pid ) ? trim( (string)$pid[0] ) : '';

				$short_desc = $product->xpath('./'.$parser_short_desc );
				$short_desc = !empty( $short_desc ) ? trim( (string)$short_desc[0] ) : '';

				/* feed values */
				$url = $product->xpath('./'.$parser_product_url );
				$url = !empty( $url ) ? trim( (string)$url[0] ) : '';

				$price = $product->xpath('./'.$parser_product_price );
				$price = !empty( $price ) ? trim( (string)$price[0] ) : '';

				$shipping = $product->xpath('./'.$parser_shipping );
				$shipping = !empty( $shipping ) ? trim( (string)$shipping[0] ) : '';

				$shipping_comment = $product->xpath('./'.$parser_shipping_comment );
				$shipping_comment = !empty( $shipping_comment ) ? trim( (string)$shipping_comment[0] ) : '';

				compare_core_import( $name, $description, $categories, $tags, $brand, $image, $pid, $short_desc, $url, $price, $shipping, $shipping_comment, $store_id, $parser_cats_separator, $parser_tags_separator );

			}
		}
	}
?>
