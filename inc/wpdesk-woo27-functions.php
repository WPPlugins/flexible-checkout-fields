<?php

if ( !function_exists( 'wpdesk_get_order_id' ) ) {
	function wpdesk_get_order_id( $order ) {
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			return $order->id;
		} else {
			return $order->get_id();
		}
	}
}


if ( !function_exists( 'wpdesk_get_order_item_meta_data' ) ) {
	function wpdesk_get_order_item_meta_data( WC_Order $order, $item_id, $convert_to_array = false ) {
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			return $order->has_meta( $item_id );
		}
		else {
			if ( $convert_to_array ) {
				$metas = $order->get_item( $item_id )->get_meta_data();
				$ret = array();
				foreach ( $metas as $meta ) {
					$ret[] = array( 'id' => $meta->id, 'meta_id' => $meta->id, 'meta_key' => $meta->key, 'meta_value' => $meta->value );
				}
				return $ret;
			}
			else {
				return $order->get_item( $item_id )->get_meta_data();
			}
		}
	}
}

if ( !function_exists( 'wpdesk_get_order_meta' ) ) {
	function wpdesk_get_order_meta( $order, $meta_key, $single = false ) {
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			$load_order = false;
			if ( in_array( $meta_key, array( 'order_date', 'customer_note' ) ) ) {
				$load_order = true;
			}
			if ( is_numeric( $order ) && ! $load_order ) {
				if ( $meta_key == '_currency' ) {
					$meta_key = '_order_currency';
				}
				return get_post_meta( $order, $meta_key, $single );
			} else {
				switch ( $meta_key ) {
					case 'order_date':
						return $order->order_date;
					case 'customer_note':
						return $order->customer_note;
					default:
						return get_post_meta( $order->id, $meta_key, $single );
				}
			}
		} else {
			if ( is_numeric( $order ) ) {
				$order = wc_get_order( $order );
			}

			switch ( $meta_key ) {
				case '_parent_id':
					return $order->get_parent_id();
					break;
				case '_status':
					return $order->get_status();
					break;
				case '_order_currency':
				case '_currency':
					return $order->get_currency();
					break;
				case '_version':
					return $order->get_version();
					break;
				case '_prices_include_tax':
					return $order->get_prices_include_tax();
					break;
				case '_date_created':
					return date( "Y-m-d H:i:s", get_date_created()->getTimestamp() );
					break;
				case '_date_modified':
					return date( "Y-m-d H:i:s", $order->get_date_modified()->getTimestamp() );
					break;
				case '_discount_total':
					return $order->get_discount_total();
					break;
				case '_discount_tax':
					return $order->get_discount_tax();
					break;
				case '_shipping_total':
					return $order->get_shipping_total();
					break;
				case '_shipping_tax':
					return $order->get_shipping_tax();
					break;
				case '_cart_tax':
					return $order->get_cart_tax();
					break;
				case '_total':
					return $order->get_total();
					break;
				case '_total_tax':
					return $order->get_total_tax();
					break;
				case '_customer_id':
					return $order->get_customer_id();
					break;
				case '_order_key':
					return $order->get_order_key();
					break;
				case '_billing_first_name':
					return $order->get_billing_first_name();
					break;
				case '_billing_last_name':
					return $order->get_billing_last_name();
					break;
				case '_billing_company':
					return $order->get_billing_company();
					break;
				case '_billing_address_1':
					return $order->get_billing_address_1();
					break;
				case '_billing_address_2':
					return $order->get_billing_address_2();
					break;
				case '_billing_city':
					return $order->get_billing_city();
					break;
				case '_billing_state':
					return $order->get_billing_state();
					break;
				case '_billing_postcode':
					return $order->get_billing_postcode();
					break;
				case '_billing_country':
					return $order->get_billing_country();
					break;
				case '_billing_email':
					return $order->get_billing_email();
					break;
				case '_billing_phone':
					return $order->get_billing_phone();
					break;

				case '_shipping_first_name':
					return $order->get_shipping_first_name();
					break;
				case '_shipping_last_name':
					return $order->get_shipping_last_name();
					break;
				case '_shipping_company':
					return $order->get_shipping_company();
					break;
				case '_shipping_address_1':
					return $order->get_shipping_address_1();
					break;
				case '_shipping_address_2':
					return $order->get_shipping_address_2();
					break;
				case '_shipping_city':
					return $order->get_shipping_city();
					break;
				case '_shipping_state':
					return $order->get_shipping_state();
					break;
				case '_shipping_postcode':
					return $order->get_shipping_postcode();
					break;
				case '_shipping_country':
					return $order->get_shipping_country();
					break;

				case '_payment_method':
					return $order->get_payment_method();
					break;
				case '_payment_method_title':
					return $order->get_payment_method_title();
					break;
				case '_transaction_id':
					return $order->get_transaction_id();
					break;
				case '_customer_ip_address':
					return $order->get_customer_ip_address();
					break;
				case '_customer_user_agent':
					return $order->get_customer_user_agent();
					break;
				case '_created_via':
					return $order->get_created_via();
					break;
				case '_customer_note':
					return $order->get_customer_note();
					break;
				case '_completed_date':
				case '_date_completed':
					$date_completed = $order->get_date_completed();
					if ( isset( $date_completed ) ) {
						return date( "Y-m-d H:i:s", $date_completed->getTimestamp() );
					}
					return null;
					break;
				case '_date_paid':
					return $order->get_date_paid();
					break;
				case '_cart_hash':
					return $order->get_cart_hash();
					break;

				case 'order_date':
					return date( "Y-m-d H:i:s", $order->get_date_created()->getTimestamp() );
					break;

				default:
					$ret = $order->get_meta( $meta_key, $single );
					return $ret;
			}
		}
	}
}

if ( !function_exists( 'wpdesk_update_order_meta' ) ) {
	function wpdesk_update_order_meta( $order, $meta_key, $meta_value ) {
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			if ( is_numeric( $order ) ) {
				$order_id = $order;
			}
			else {
				$order_id = $order->id;
			}
			update_post_meta( $order_id, $meta_key, $meta_value );
		}
		else {
			if ( is_numeric( $order ) ) {
				$order_id = $order;
				$order = wc_get_order( $order_id );
			}
			$order->update_meta_data( $meta_key, $meta_value );
			$order->save();
		}
	}
}


if ( !function_exists( 'wpdesk_get_product_id' ) ) {
	function wpdesk_get_product_id( WC_Product $product ) {
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			return $product->id;
		} else {
			return $product->get_id();
		}
	}
}

if ( !function_exists( 'wpdesk_get_product_variation_id' ) ) {
	function wpdesk_get_product_variation_id( WC_Product_Variation $product ) {
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			return $product->variation_id;
		} else {
			return $product->get_id();
		}
	}
}

if ( !function_exists( 'wpdesk_get_variation_id' ) ) {
	function wpdesk_get_variation_id( WC_Product_Variation $product ) {
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			return $product->variation_id;
		} else {
			return $product->get_id();
		}
	}
}

if ( !function_exists( 'wpdesk_get_variation_parent_id' ) ) {
	function wpdesk_get_variation_parent_id( WC_Product_Variation $product ) {
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			return $product->id;
		} else {
			return $product->get_parent_id();
		}
	}
}

if ( !function_exists( 'wpdesk_get_price_including_tax' ) ) {
	function wpdesk_get_price_including_tax( WC_Product $product, $qty = 1, $price = '' ) {
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			return $product->get_price_including_tax( $qty, $price );
		}
		else {
			$args = array( 'qty' => $qty, 'price' => $price );
			return wc_get_price_including_tax( $product, $args );
		}
	}
}

if ( !function_exists( 'wpdesk_get_price_excluding_tax' ) ) {
	function wpdesk_get_price_excluding_tax( WC_Product $product, $qty = 1, $price = '' ) {
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			return $product->get_price_excluding_tax( $qty, $price );
		}
		else {
			$args = array( 'qty' => $qty, 'price' => $price );
			return wc_get_price_excluding_tax( $product, $args );
		}
	}
}

if ( !function_exists( 'wpdesk_reduce_stock_levels' ) ) {
	function wpdesk_reduce_stock_levels( WC_Order $order ) {
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			return $order->reduce_order_stock();
		} else {
			wc_reduce_stock_levels( $order->get_id() );
		}
	}
}


if ( !function_exists( 'wpdesk_get_product_meta' ) ) {
	function wpdesk_get_product_meta( $product, $meta_key, $single = false ) {
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			if ( is_numeric( $product ) ) {
				return get_post_meta( $product, $meta_key, $single );
			} else {
				return get_post_meta( $product->id, $meta_key, $single );
			}
		} else {
			if ( is_numeric( $product ) ) {
				$product = wc_get_product( $product );
			}
			switch ( $meta_key ) {
				case '_stock_status' :
					return $product->get_stock_status();
				default:
					break;
			}
			return $product->get_meta( $meta_key, $single );
		}
	}
}


if ( !function_exists( 'wpdesk_update_product_meta' ) ) {
	function wpdesk_update_product_meta( $product, $meta_key, $meta_value ) {
		$product_id = false;
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			if ( is_numeric( $product ) ) {
				$product_id = $product;
			}
			else {
				$product_id = $product->id;
			}
			update_post_meta( $product_id, $meta_key, $meta_value );
		}
		else {
			if ( is_numeric( $product ) ) {
				$product_id = $product;
				$product = wc_get_product( $product_id );
			}
			$product->update_meta_data( $meta_key, $meta_value );
			$product->save();
		}
	}
}

if ( !function_exists( 'wpdesk_get_variation_meta' ) ) {
    function wpdesk_get_variation_meta( $variation, $meta_key, $single = false ) {
        if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
            if ( is_numeric( $variation ) ) {
                return get_post_meta( $variation, $meta_key, $single );
            } else {
                return get_post_meta( $variation->variation_id, $meta_key, $single );
            }
        } else {
            if ( is_numeric( $variation ) ) {
                $variation = wc_get_product( $variation );
            }
            switch ( $meta_key ) {
                case '_stock_status' :
                    return $variation->get_stock_status();
                default:
                    break;
            }
            return $variation->get_meta( $meta_key, $single );
        }
    }
}


if ( !function_exists( 'wpdesk_get_product_post' ) ) {
	function wpdesk_get_product_post( WC_Product $product ) {
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			return $product->get_post_data();
		}
		else {
			return get_post( $product->get_id() );
		}
	}
}

