<?php

global $COMPARE_SEARCH_URL;
$COMPARE_SEARCH_URL = compare_get_permalink_by_tpl( 'page-tpl_search' );
load_theme_textdomain( 'compare', get_template_directory() . '/languages' );
require_once( locate_template( 'includes/class-tgm-plugin-activation.php' ) );

require_once( locate_template( 'includes/google-fonts.php' ) );
require_once( locate_template( 'includes/awesome-icons.php' ) );
require_once( locate_template( 'includes//product-cat-meta.php' ) );
require_once( locate_template( 'includes/product-brand-meta.php' ) );
require_once( locate_template( 'includes/widgets.php' ) );
require_once( locate_template( 'includes/gallery.php' ) );
require_once( locate_template( 'includes/paypal.class.php' ) );
require_once( locate_template( 'includes/theme-options.php' ) );
require_once( locate_template( 'includes/menu-extender.php' ) );
require_once( locate_template( 'includes/import/import.php' ) );
require_once( locate_template( 'includes/mollie.php' ) );
require_once( locate_template( 'includes/radium-one-click-demo-install/init.php' ) );
if ( is_admin() ) {
	require_once( locate_template( 'includes/shortcodes.php' ) );
}
foreach ( glob( get_template_directory() . '/includes/shortcodes/*.php' ) as $filename ) {
	require_once( locate_template( 'includes/shortcodes/' . basename( $filename ) ) );
}


if ( ! function_exists( 'comapre_check_update_plugins' ) ) {
	function compare_check_update_plugins() {
		if ( function_exists( 'sm_init' ) ):
			$smeta_data = get_plugins( '/smeta' );
			if ( $smeta_data['smeta.php']['Version'] != '1.1' ):
				?>
				<div class="notice notice-success is-dismissible error">
					<p><?php esc_html_e( 'Reinstall Smeta plugin ( Delete it and theme will offer you to install it again )', 'compare' ); ?></p>
				</div>
				<?php
			endif;
		endif;
	}
}

add_action( 'admin_notices', 'compare_check_update_plugins' );

/*
Check for the required plugins
*/
if ( ! function_exists( 'compare_requred_plugins' ) ) {
	function compare_requred_plugins() {
		$plugins = array(
			array(
				'name'               => __( 'Redux Options', 'compare' ),
				'slug'               => 'redux-framework',
				'source'             => get_template_directory() . '/lib/plugins/redux-framework.zip',
				'required'           => true,
				'version'            => '',
				'force_activation'   => false,
				'force_deactivation' => false,
				'external_url'       => '',
			),
			array(
				'name'               => __( 'Smeta', 'compare' ),
				'slug'               => 'smeta',
				'source'             => get_template_directory() . '/lib/plugins/smeta.zip',
				'required'           => true,
				'version'            => '',
				'force_activation'   => false,
				'force_deactivation' => false,
				'external_url'       => '',
			),
			array(
				'name'               => __( 'User Avatars', 'compare' ),
				'slug'               => 'wp-user-avatar',
				'source'             => get_template_directory() . '/lib/plugins/wp-user-avatar.zip',
				'required'           => true,
				'version'            => '',
				'force_activation'   => false,
				'force_deactivation' => false,
				'external_url'       => '',
			),
			array(
				'name'               => __( 'Compare CPT', 'compare' ),
				'slug'               => 'compare-cpt',
				'source'             => get_template_directory() . '/lib/plugins/compare-cpt.zip',
				'required'           => true,
				'version'            => '',
				'force_activation'   => false,
				'force_deactivation' => false,
				'external_url'       => '',
			),
			array(
				'name'               => __( 'Compare Import', 'compare' ),
				'slug'               => 'compare-import',
				'source'             => get_template_directory() . '/lib/plugins/compare-import.zip',
				'required'           => true,
				'version'            => '',
				'force_activation'   => false,
				'force_deactivation' => false,
				'external_url'       => '',
			),
			array(
				'name'               => esc_html__( 'Envato Market', 'compare' ),
				'slug'               => 'envato-market',
				'source'             => 'https://envato.github.io/wp-envato-market/dist/envato-market.zip',
				'required'           => false,
				'version'            => '',
				'force_activation'   => false,
				'force_deactivation' => false,
				'external_url'       => 'https://envato.github.io/wp-envato-market/dist/envato-market.zip',
			),
		);

		/**
		 * Array of configuration settings. Amend each line as needed.
		 * If you want the default strings to be available under your own theme domain,
		 * leave the strings uncommented.
		 * Some of the strings are added into a sprintf, so see the comments at the
		 * end of each line for what each argument will be.
		 */
		$config = array(
			'domain'       => 'compare',
			'default_path' => '',
			'menu'         => 'install-required-plugins',
			'has_notices'  => true,
			'is_automatic' => false,
			'message'      => ''
		);

		tgmpa( $plugins, $config );
	}
}

add_action( 'tgmpa_register', 'compare_requred_plugins' );

/*
Check current version and make updates
if neccessary
*/
if ( ! function_exists( 'compare_check_version' ) ) {
	function compare_check_version() {
		$current_version = 14;
		$version         = get_option( 'compare_version' );
		if ( empty( $version ) ) {
			$version = 0;
		}
		if ( $version === 0 ) {
			compare_create_tables();
			get_option( 'compare_version', $current_version );
		}
		if ( $version < 14 ) {
			$new_options_value     = array();
			$search_bar_categories = compare_get_option( 'search_bar_categories' );
			if ( ! empty( $search_bar_categories ) ) {
				foreach ( $search_bar_categories as $cat_id ) {
					$category = get_term_by( 'id', $cat_id, 'product-cat' );
					if ( ! empty( $category ) && ! is_wp_error( $category ) ) {
						$new_options_value[] = $category->slug;
					}
				}
			}
			global $compare_opts;
			if ( class_exists( 'ReduxFramework' ) && ! empty( $new_options_value ) ) {
				$compare_opts->ReduxFramework->set( 'search_bar_categories', $new_options_value );
			}
		}

		update_option( 'compare_version', $current_version );
	}
}

add_action( 'init', 'compare_check_version' );

/*
Create custom tables for storing product feed
*/
if ( ! function_exists( 'compare_create_tables' ) ) {
	function compare_create_tables() {
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$wpdb->prefix}feed_list (
	  feed_id mediumint(9) NOT NULL AUTO_INCREMENT,
	  post_id mediumint(9) NOT NULL,
	  store_id mediumint(9) NOT NULL,
	  price double NOT NULL,
	  product_link text DEFAULT '' NOT NULL,
	  shipping double NOT NULL,
	  shipping_comment text DEFAULT '' NOT NULL,
	  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	  UNIQUE KEY feed_id (feed_id)
	) $charset_collate;";
		dbDelta( $sql );

		$sql = "CREATE TABLE {$wpdb->prefix}stores (
	  store_id mediumint(9) NOT NULL AUTO_INCREMENT,
	  store_slug varchar(255) NOT NULL,
	  store_logo varchar(10) NOT NULL,
	  store_url text NOT NULL,
	  store_name varchar(255) NOT NULL, 
	  store_clicks varchar(255) NOT NULL,
	  store_contact_name varchar(100),
	  store_contact_email varchar(50),
	  store_contact_phone varchar(50),
	  store_package varchar(50),
	  store_expire_time varchar(100),
	  store_xml_feed text,
	  store_status varchar(1),
	  store_update varchar(255),
	  store_parser varchar(100),
	  UNIQUE KEY store_id (store_id)
	) $charset_collate;";
		dbDelta( $sql );
	}
}

/*
Create custom submenu item under Products
*/
if ( ! function_exists( 'compare_create_menu_items' ) ) {
	function compare_create_menu_items() {
		add_submenu_page( 'edit.php?post_type=product', __( 'Stores', 'compare' ), __( 'Stores', 'compare' ), 'edit_posts', 'stores', 'compare_stores_list' );
		add_submenu_page( 'edit.php?post_type=product', esc_html__( 'Import / Export', 'compare' ), esc_html__( 'Import / Export', 'compare' ), 'edit_posts', 'cf-import', 'compare_cf_import_export' );
	}
}

add_action( 'admin_menu', 'compare_create_menu_items' );

/*
Delete store na its logo
*/
if ( ! function_exists( 'compare_stores_list_delete_store' ) ) {
	function compare_stores_list_delete_store( $store_id ) {
		global $wpdb;
		$store = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stores WHERE store_id = %d", $store_id ) );
		if ( ! empty( $store[0]->store_logo ) ) {
			wp_delete_attachment( $store[0]->store_logo, true );
		}
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}stores WHERE store_id = %d", $store_id ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}feed_list WHERE store_id = %d", $store_id ) );
	}
}

/*
Edit / add / list available stores
*/
if ( ! function_exists( 'compare_stores_list' ) ) {
	function compare_stores_list() {
		global $wpdb;
		$permalink = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$temp      = explode( '?', $permalink );
		$permalink = $temp[0] . '?post_type=product&page=stores';
		if ( isset( $_POST['stores'] ) ) {
			$stores = $_POST['stores'];
			if ( ! empty( $stores ) ) {
				foreach ( $stores as $store_id ) {
					compare_stores_list_delete_store( $store_id );
				}
			}
			$message = '<div class="updated"><p>' . __( 'Stores are deleted', 'compare' ) . '</p></div>';
		}
		if ( ! isset( $_GET['action'] ) ) {
			$stores = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}stores" );
			include( locate_template( 'includes/views/stores.php' ) );
		} else {
			$action = $_GET['action'];
			if ( $action == 'new' ) {
				$store         = '';
				$store_package = '';
				include( locate_template( 'includes/views/edit_add_store.php' ) );
			} else if ( $action == 'delete' ) {
				$store_id = $_GET['store_id'];
				compare_stores_list_delete_store( $store_id );
				$message = '<div class="updated"><p>' . __( 'Store is deleted', 'compare' ) . '</p></div>';
				$stores  = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}stores" );
				include( locate_template( 'includes/views/stores.php' ) );
			} else if ( $action == 'edit' ) {
				$store_id            = $_GET['store_id'];
				$store               = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stores WHERE store_id = %d", $store_id ) );
				$store               = array_shift( $store );
				$store_name          = $store->store_name;
				$store_slug          = $store->store_slug;
				$store_logo          = $store->store_logo;
				$store_url           = $store->store_url;
				$store_clicks        = $store->store_clicks;
				$store_contact_name  = $store->store_contact_name;
				$store_contact_email = ! empty( $store->store_contact_email ) ? $store->store_contact_email : '';
				$store_contact_phone = ! empty( $store->store_contact_phone ) ? $store->store_contact_phone : '';
				$store_package       = ! empty( $store->store_package ) ? $store->store_package : '';
				$store_expire_time   = ! empty( $store->store_expire_time ) ? $store->store_expire_time : '';
				$store_xml_feed      = ! empty( $store->store_xml_feed ) ? $store->store_xml_feed : '';
				$store_status        = ! empty( $store->store_status ) ? $store->store_status : 0;
				$store_parser        = ! empty( $store->store_parser ) ? $store->store_parser : '';
				include( locate_template( 'includes/views/edit_add_store.php' ) );
			} else if ( $action == 'save' ) {
				$store_id            = ! empty( $_POST['store_id'] ) ? esc_sql( $_POST['store_id'] ) : '';
				$store_name          = ! empty( $_POST['store_name'] ) ? esc_sql( $_POST['store_name'] ) : '';
				$store_slug          = ! empty( $_POST['store_slug'] ) ? esc_sql( $_POST['store_slug'] ) : $store_name;
				$store_logo          = ! empty( $_POST['store_logo'] ) ? esc_sql( $_POST['store_logo'] ) : '';
				$store_url           = ! empty( $_POST['store_url'] ) ? esc_sql( $_POST['store_url'] ) : '';
				$store_clicks        = ! empty( $_POST['store_clicks'] ) ? esc_sql( $_POST['store_clicks'] ) : 0;
				$store_contact_name  = ! empty( $_POST['store_contact_name'] ) ? esc_sql( $_POST['store_contact_name'] ) : '';
				$store_contact_email = ! empty( $_POST['store_contact_email'] ) ? esc_sql( $_POST['store_contact_email'] ) : '';
				$store_contact_phone = ! empty( $_POST['store_contact_phone'] ) ? esc_sql( $_POST['store_contact_phone'] ) : '';
				$store_package       = ! empty( $_POST['store_package'] ) ? esc_sql( $_POST['store_package'] ) : '';
				$store_expire_time   = ! empty( $_POST['store_expire_time'] ) ? esc_sql( strtotime( $_POST['store_expire_time'] ) ) : '';
				$store_xml_feed      = ! empty( $_POST['store_xml_feed'] ) ? esc_sql( $_POST['store_xml_feed'] ) : '';
				$store_status        = ! empty( $_POST['store_status'] ) ? esc_sql( $_POST['store_status'] ) : 0;
				$store_parser        = ! empty( $_POST['store_parser'] ) ? esc_sql( $_POST['store_parser'] ) : '';
				$check_email         = true;
				if ( ! empty( $store_contact_email ) && ! filter_var( $store_contact_email, FILTER_VALIDATE_EMAIL ) ) {
					$check_email = false;
				}
				if ( ! empty( $store_slug ) && ! empty( $store_logo ) && ! empty( $store_url ) && ! empty( $store_name ) && $check_email ) {
					$store_slug = sanitize_title( $store_slug );
					if ( empty( $_POST['old_slug'] ) || ( ! empty( $_POST['pld_slug'] ) && $_POST['old_slug'] !== $store_slug ) ) {
						$if_exists = $wpdb->query( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stores WHERE store_slug = %s", $store_slug ) );
					}
					if ( empty( $if_exists ) ) {
						if ( empty( $store_id ) ) {
							$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}stores VALUES( '', %s, %s, %s, %s, %d, %s, %s, %s, %s, %s, %s, %s, '', %s )", $store_slug, $store_logo, $store_url, $store_name, $store_clicks, $store_contact_name, $store_contact_email, $store_contact_phone, $store_package, $store_expire_time, $store_xml_feed, $store_status, $store_parser ) );
							$message = '<div class="updated"><p>' . __( 'New store is added', 'compare' ) . '</p></div>';
						} else {
							$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}stores SET 
									store_slug = %s, 
									store_logo = %s, 
									store_url = %s, 
									store_name = %s, 
									store_clicks = %d, 
									store_contact_name = %s, 
									store_contact_email = %s, 
									store_contact_phone = %s, 
									store_package = %s, 
									store_expire_time = %s, 
									store_xml_feed = %s, 
									store_status = %s, 
									store_parser = %s 
								WHERE store_id = %d", $store_slug, $store_logo, $store_url, $store_name, $store_clicks, $store_contact_name, $store_contact_email, $store_contact_phone, $store_package, $store_expire_time, $store_xml_feed, $store_status, $store_parser, $store_id ) );
							$stores  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stores WHERE store_id = %d", $store_id ) );
							$store   = array_shift( $stores );
							$message = '<div class="updated"><p>' . __( 'Store is updated', 'compare' ) . '</p></div>';
						}
					} else {
						if ( ! empty( $store_id ) ) {
							$stores = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stores WHERE store_id = %d", $store_id ) );
							$store  = array_shift( $stores );
						}
						$message = '<div class="error"><p>' . __( 'Store slug must be unique.', 'compare' ) . '</p></div>';
					}
				} else {
					if ( ! empty( $store_id ) ) {
						$stores = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stores WHERE store_id = %d", $store_id ) );
						$store  = array_shift( $stores );
					}
					$message = '<div class="error"><p>' . __( 'All fields are required.', 'compare' ) . '</p></div>';
				}
				include( locate_template( 'includes/views/edit_add_store.php' ) );
			} else if ( $action == 'import' ) {
				$store_id = $_GET['store_id'];
				$store    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stores WHERE store_id = %d", $store_id ) );
				$store    = array_shift( $store );
				if ( ! empty( $store ) ) {
					$store_id = $store->store_id;
					include( locate_template( 'includes/import/import-store.php' ) );
				}
			}
		}
	}
}

if ( ! isset( $content_width ) ) {
	$content_width = 1920;
}

/*
Allow shortcodes in the excerpt
*/
add_filter( 'the_excerpt', 'do_shortcode' );


/*
Register theme sidebars
*/
if ( ! function_exists( 'compare_widgets_init' ) ) {
	function compare_widgets_init() {

		register_sidebar( array(
			'name'          => __( 'Blog Sidebar', 'compare' ),
			'id'            => 'sidebar-blog',
			'before_widget' => '<div class="widget white-block owl-parent %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<div class="widget-title white-title clearfix"><div class="white-block-border clearfix">
								<div class="pull-left">' . compare_get_white_title_icon() . '
								<h3>',
			'after_title'   => '</h3></div>
						<div class="pull-right">
							<a href="javascript:;" class="list-left">
								<i class="fa fa-angle-left"></i>
							</a>
							<a href="javascript:;" class="list-right">
								<i class="fa fa-angle-right"></i>
							</a>
						</div></div></div>',
			'description'   => __( 'Appears on the right side of the blog.', 'compare' )
		) );

		register_sidebar( array(
			'name'          => __( 'Page Sidebar Right', 'compare' ),
			'id'            => 'sidebar-right',
			'before_widget' => '<div class="widget white-block owl-parent %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<div class="widget-title white-title clearfix"><div class="white-block-border clearfix">
								<div class="pull-left">' . compare_get_white_title_icon() . '
								<h3>',
			'after_title'   => '</h3></div>
						<div class="pull-right">
							<a href="javascript:;" class="list-left">
								<i class="fa fa-angle-left"></i>
							</a>
							<a href="javascript:;" class="list-right">
								<i class="fa fa-angle-right"></i>
							</a>
						</div></div></div>',
			'description'   => __( 'Appears on the right side of the page.', 'compare' )
		) );

		register_sidebar( array(
			'name'          => __( 'Page Sidebar Left', 'compare' ),
			'id'            => 'sidebar-left',
			'before_widget' => '<div class="widget white-block owl-parent %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<div class="widget-title white-title clearfix"><div class="white-block-border clearfix">
								<div class="pull-left">' . compare_get_white_title_icon() . '
								<h3>',
			'after_title'   => '</h3></div>
						<div class="pull-right">
							<a href="javascript:;" class="list-left">
								<i class="fa fa-angle-left"></i>
							</a>
							<a href="javascript:;" class="list-right">
								<i class="fa fa-angle-right"></i>
							</a>
						</div></div></div>',
			'description'   => __( 'Appears on the left side of the page.', 'compare' )
		) );

		register_sidebar( array(
			'name'          => __( 'Contact Page Sidebar', 'compare' ),
			'id'            => 'sidebar-contact',
			'before_widget' => '<div class="widget white-block owl-parent %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<div class="widget-title white-title clearfix"><div class="white-block-border clearfix">
								<div class="pull-left">' . compare_get_white_title_icon() . '
								<h3>',
			'after_title'   => '</h3></div>
						<div class="pull-right">
							<a href="javascript:;" class="list-left">
								<i class="fa fa-angle-left"></i>
							</a>
							<a href="javascript:;" class="list-right">
								<i class="fa fa-angle-right"></i>
							</a>
						</div></div></div>',
			'description'   => __( 'Appears on the right side of the contact page.', 'compare' )
		) );

		register_sidebar( array(
			'name'          => __( 'Register Store Page Sidebar', 'compare' ),
			'id'            => 'sidebar-register',
			'before_widget' => '<div class="widget white-block owl-parent %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<div class="widget-title white-title clearfix"><div class="white-block-border clearfix">
								<div class="pull-left">' . compare_get_white_title_icon() . '
								<h3>',
			'after_title'   => '</h3></div>
						<div class="pull-right">
							<a href="javascript:;" class="list-left">
								<i class="fa fa-angle-left"></i>
							</a>
							<a href="javascript:;" class="list-right">
								<i class="fa fa-angle-right"></i>
							</a>
						</div></div></div>',
			'description'   => __( 'Appears on the right side of the register store page.', 'compare' )
		) );

		register_sidebar( array(
			'name'          => __( 'Search Sidebar', 'compare' ),
			'id'            => 'sidebar-search',
			'before_widget' => '<div class="widget white-block owl-parent %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<div class="widget-title white-title clearfix"><div class="white-block-border clearfix">
								<div class="pull-left">' . compare_get_white_title_icon() . '
								<h3>',
			'after_title'   => '</h3></div>
						<div class="pull-right">
							<a href="javascript:;" class="list-left">
								<i class="fa fa-angle-left"></i>
							</a>
							<a href="javascript:;" class="list-right">
								<i class="fa fa-angle-right"></i>
							</a>
						</div></div></div>',
			'description'   => __( 'Appears on the left side of the search page.', 'compare' )
		) );

		register_sidebar( array(
			'name'          => __( 'Product Sidebar', 'compare' ),
			'id'            => 'sidebar-product',
			'before_widget' => '<div class="widget white-block owl-parent %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<div class="widget-title white-title clearfix"><div class="white-block-border clearfix">
								<div class="pull-left">' . compare_get_white_title_icon() . '
								<h3>',
			'after_title'   => '</h3></div>
						<div class="pull-right">
							<a href="javascript:;" class="list-left">
								<i class="fa fa-angle-left"></i>
							</a>
							<a href="javascript:;" class="list-right">
								<i class="fa fa-angle-right"></i>
							</a>
						</div></div></div>',
			'description'   => __( 'Appears on the right side of the product page.', 'compare' )
		) );

		register_sidebar( array(
			'name'          => __( 'Bottom Sidebar 1', 'compare' ),
			'id'            => 'sidebar-bottom-1',
			'before_widget' => '<div class="widget white-block owl-parent %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<div class="widget-title"><h4>',
			'after_title'   => '</h4></div>',
			'description'   => __( 'Appears at the bottom of the page.', 'compare' )
		) );

		register_sidebar( array(
			'name'          => __( 'Bottom Sidebar 2', 'compare' ),
			'id'            => 'sidebar-bottom-2',
			'before_widget' => '<div class="widget white-block owl-parent %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<div class="widget-title"><h4>',
			'after_title'   => '</h4></div>',
			'description'   => __( 'Appears at the bottom of the page.', 'compare' )
		) );

		register_sidebar( array(
			'name'          => __( 'Bottom Sidebar 3', 'compare' ),
			'id'            => 'sidebar-bottom-3',
			'before_widget' => '<div class="widget white-block owl-parent %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<div class="widget-title"><h4>',
			'after_title'   => '</h4></div>',
			'description'   => __( 'Appears at the bottom of the page.', 'compare' )
		) );

		$home_sidebars = compare_get_option( 'home_sidebars' );
		if ( empty( $home_sidebars ) ) {
			$home_sidebars = 2;
		}

		for ( $i = 1; $i <= $home_sidebars; $i ++ ) {
			register_sidebar( array(
				'name'          => __( 'Home Sidebar ', 'compare' ) . $i,
				'id'            => 'home-sidebar-' . $i,
				'before_widget' => '<div class="widget white-block owl-parent %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<div class="widget-title white-title clearfix"><div class="white-block-border clearfix">
									<div class="pull-left">' . compare_get_white_title_icon() . '
									<h3>',
				'after_title'   => '</h3></div>
							<div class="pull-right">
								<a href="javascript:;" class="list-left">
									<i class="fa fa-angle-left"></i>
								</a>
								<a href="javascript:;" class="list-right">
									<i class="fa fa-angle-right"></i>
								</a>
							</div></div></div>',
				'description'   => __( 'Used for the widget area on home page.', 'compare' )
			) );
		}

		$mega_menu_sidebars = compare_get_option( 'mega_menu_sidebars' );
		if ( empty( $mega_menu_sidebars ) ) {
			$mega_menu_sidebars = 5;
		}

		for ( $i = 1; $i <= $mega_menu_sidebars; $i ++ ) {
			register_sidebar( array(
				'name'          => __( 'Mega Menu Sidebar ', 'compare' ) . $i,
				'id'            => 'mega-menu-' . $i,
				'before_widget' => '<li class="widget white-block owl-parent %2$s">',
				'after_widget'  => '</li>',
				'before_title'  => '<div class="widget-title"><h4>',
				'after_title'   => '</h4></div>',
				'description'   => __( 'This will be shown as the dropdown menu in the navigation.', 'compare' )
			) );
		}
	}
}

add_action( 'widgets_init', 'compare_widgets_init' );

/*
Set direction of the site based on theme option
*/
if ( ! function_exists( 'compare_set_direction' ) ) {
	function compare_set_direction() {
		global $wp_locale, $wp_styles;

		$_user_id  = get_current_user_id();
		$direction = compare_get_option( 'direction' );
		if ( empty( $direction ) ) {
			$direction = 'ltr';
		}

		if ( $direction ) {
			update_user_meta( $_user_id, 'rtladminbar', $direction );
		} else {
			$direction = get_user_meta( $_user_id, 'rtladminbar', true );
			if ( false === $direction ) {
				$direction = isset( $wp_locale->text_direction ) ? $wp_locale->text_direction : 'ltr';
			}
		}

		$wp_locale->text_direction = $direction;
		if ( ! is_a( $wp_styles, 'WP_Styles' ) ) {
			$wp_styles = new WP_Styles();
		}
		$wp_styles->text_direction = $direction;
	}
}

add_action( 'init', 'compare_set_direction' );

/* get url by page template */
function compare_get_permalink_by_tpl( $template_name ) {
	$page = get_pages( array(
		'meta_key'   => '_wp_page_template',
		'meta_value' => $template_name . '.php'
	) );
	if ( ! empty( $page ) ) {
		return get_permalink( $page[0]->ID );
	} else {
		return "javascript:;";
	}
}

/*
Default options when redux plugin is disabled
*/
if ( ! function_exists( 'compare_defaults' ) ) {
	function compare_defaults( $id ) {
		$defaults = array(
			'trans_keyword'             => 'keyword',
			'trans_product'             => 'product',
			'trans_product_cat'         => 'product-cat',
			'trans_product_brand'       => 'product-brand',
			'trans_product_tag'         => 'product-tag',
			'enable_search_bar'         => 'no',
			'search_bar_categories'     => '',
			'error_img'                 => array( 'url' => '' ),
			'site_favicon'              => '',
			'white_title_icon'          => 'list-ul',
			'main_color'                => '#36a0c7',
			'main_color_font'           => '#fff',
			'secondary_color'           => '#00a88e',
			'secondary_font_color'      => '#fff',
			'secondary_color_hvr'       => '#008470',
			'secondary_font_color_hvr'  => '#fff',
			'top_bar_bg_color'          => '#162b32',
			'top_bar_font_color'        => '#889ca3',
			'submenu_bg_color'          => '#162b32',
			'submenu_font_color'        => '#ffffff',
			'categories_bg_color_hvr'   => '#162b32',
			'categories_font_color_hvr' => '#ffffff',
			'copyrights_bg_color'       => '#14272d',
			'copyrights_font_color'     => '#3b5a64',
			'copyrights_link_color'     => '#36a0c7',
			'font_family'               => 'Droid Sans',
			'show_top_bar'              => 'no',
			'top_bar_facebook_link'     => '',
			'top_bar_twitter_link'      => '',
			'top_bar_google_link'       => '',
			'top_bar_mail'              => '',
			'top_bar_phone'             => '',
			'site_logo'                 => array( 'url' => '' ),
			'site_logo_padding'         => '',
			'enable_sticky'             => 'no',
			'mega_menu_sidebars'        => '5',
			'mega_menu_min_height'      => '',
			'footer_copyrights'         => '',
			'footer_copyrights_image'   => array( 'url' => '' ),
			'search_sidebar_location'   => 'left',
			'search_categories_visible' => '10',
			'search_brands_visible'     => '10',
			'product_box_style'         => 'grid',
			'price_ranges'              => '',
			'all_categories_sortby'     => 'name',
			'all_categories_sort'       => 'asc',
			'all_brands_sortby'         => 'name',
			'all_brands_sort'           => 'asc',
			'packages'                  => '',
			'all_packages_link'         => '',
			'email_sender'              => '',
			'name_sender'               => '',
			'contact_mail'              => '',
			'contact_form_subject'      => '',
			'contact_map'               => '',
			'contact_map_scroll_zoom'   => 'no',
			'unit'                      => '',
			'main_unit_abbr'            => '',
			'unit_position'             => '',
			'paypal_mode'               => '',
			'paypal_username'           => '',
			'paypal_signature'          => '',
			'stripe_pk_client_id'       => '',
			'stripe_sk_client_id'       => '',
			'skrill_owner_mail'         => '',
			'skrill_secret_word'        => '',
			'bank_account_name'         => '',
			'bank_name'                 => '',
			'bank_account_number'       => '',
			'bank_sort_number'          => '',
			'bank_iban_number'          => '',
			'bank_bic_swift_number'     => '',
			'mollie_id'                 => '',
			'mail_chimp_api'            => '',
			'mail_chimp_list_id'        => '',
			'cron_enable'               => 'no',
			'cron_start_date'           => '',
			'cron_start_time'           => '',
			'cron_frequency'            => 'daily',
			'products_per_page'         => '',
			'products_single'           => 'style1',
			'similar_num'               => '5',
		);

		if ( isset( $defaults[ $id ] ) ) {
			return $defaults[ $id ];
		} else {

			return '';
		}
	}
}

/*
Get theme option value based on the id
*/
if ( ! function_exists( 'compare_get_option' ) ) {
	function compare_get_option( $id ) {
		global $compare_options;
		if ( isset( $compare_options[ $id ] ) ) {
			$value = $compare_options[ $id ];
			if ( isset( $value ) ) {
				return $value;
			} else {
				return '';
			}
		} else {
			return compare_defaults( $id );
		}
	}
}

/*
Initiate startup theme setup
*/
if ( ! function_exists( 'compare_setup' ) ) {
	function compare_setup() {
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( "title-tag" );
		add_theme_support( 'html5', array(
			'comment-form',
			'comment-list'
		) );
		register_nav_menu( 'top-navigation', __( 'Top Navigation', 'compare' ) );
		register_nav_menu( 'top-left-navigation', __( 'Top Left Navigation', 'compare' ) );

		add_theme_support( 'post-thumbnails' );

		set_post_thumbnail_size( 848, 477, true );
		if ( function_exists( 'add_image_size' ) ) {
			add_image_size( 'compare-shop-logo', 150 );
			add_image_size( 'compare-single-product-2', 250, 188, true );
			add_image_size( 'compare-slider-image', 848, 487, true );
			add_image_size( 'compare-offer-box', 400, 300, true );
			add_image_size( 'compare-blog-box', 400, 225, true );
			add_image_size( 'compare-quote-box', 408, 437, true );
		}

		add_theme_support( 'custom-header' );
		add_theme_support( 'custom-background' );
		add_theme_support( 'post-formats', array(
			'aside',
			'gallery',
			'link',
			'image',
			'quote',
			'status',
			'video',
			'audio',
			'chat'
		) );
		add_editor_style();
	}
}

add_action( 'after_setup_theme', 'compare_setup' );

/*
Generate URL for google font enqueue
*/
if ( ! function_exists( 'compare_load_google_font' ) ) {
	function compare_load_google_font( $font_family ) {
		$font_url = '';
		if ( 'off' !== _x( 'on', 'Google font: on or off', 'studio' ) ) {
			$font_url = add_query_arg( 'family', urlencode( $font_family . ':100,300,400,700,900,100italic,300italic,400italic,700italic,900italic' ), "//fonts.googleapis.com/css" );
		}

		return $font_url;
	}
}


/*
Enqueue required scripts and styles
*/
if ( ! function_exists( 'compare_scripts_styles' ) ) {
	function compare_scripts_styles() {
		wp_enqueue_style( 'compare-awesome', get_template_directory_uri() . '/css/font-awesome.min.css' );

		wp_enqueue_style( 'compare-bootstrap', get_template_directory_uri() . '/css/bootstrap.min.css' );
		wp_enqueue_style( 'compare-owl-carousel', get_template_directory_uri() . '/css/owl.carousel.css' );
		wp_enqueue_style( 'compare-bootstrap', get_template_directory_uri() . '/css/bootstrap-table.min.css' );
		wp_enqueue_style( 'compare-magnific-popup', get_template_directory_uri() . '/css/magnific-popup.css' );

		$font_family = compare_get_option( 'font_family' );
		$protocol    = is_ssl() ? 'https' : 'http';
		if ( ! empty( $font_family ) ) {
			wp_enqueue_style( 'compare-font', compare_load_google_font( $font_family ), array(), '1.0.0' );
		}

		if ( is_page_template( 'page-tpl_contact.php' ) ) {
			wp_enqueue_script( 'compare-googlemap', $protocol . '://maps.googleapis.com/maps/api/js?sensor=false', false, false, true );
		}

		wp_enqueue_script( 'compare-bootstrap-js', get_template_directory_uri() . '/js/bootstrap.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'compare-bootstrap-multilevel-js', get_template_directory_uri() . '/js/bootstrap-dropdown-multilevel.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'compare-bootstrap', get_template_directory_uri() . '/js/bootstrap-table.min.js', array( 'jquery' ), false, true );

		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}

		if ( is_page_template( 'page-tpl_register_store.php' ) ) {
			wp_enqueue_script( 'compare-stripe', 'https://checkout.stripe.com/checkout.js', false, false, true );
		}

		wp_enqueue_script( 'compare-owl-carousel', get_template_directory_uri() . '/js/owl.carousel.min.js', array( 'jquery' ), false, true );

		wp_enqueue_script( 'compare-magnific-popup', get_template_directory_uri() . '/js/jquery.magnific-popup.min.js', array( 'jquery' ), false, true );

		wp_enqueue_script( 'compare-custom', get_template_directory_uri() . '/js/custom.js', array( 'jquery' ), false, true );

	}
}

add_action( 'wp_enqueue_scripts', 'compare_scripts_styles', 2 );

/*
Load main style and custom CSS
generated based on the options
*/
if ( ! function_exists( 'compare_load_color_schema' ) ) {
	function compare_load_color_schema() {
		wp_enqueue_style( 'compare-style', get_stylesheet_uri(), array() );
		ob_start();
		include( locate_template( 'css/main-color.css.php' ) );
		$custom_css = ob_get_contents();
		ob_end_clean();
		wp_add_inline_style( 'compare-style', $custom_css );
	}
}

add_action( 'wp_enqueue_scripts', 'compare_load_color_schema', 4 );

/*
Enqueue resources for the admin backend
*/
if ( ! function_exists( 'compare_admin_resources' ) ) {
	function compare_admin_resources() {
		global $post;
		wp_enqueue_style( 'compare-awesome', get_template_directory_uri() . '/css/font-awesome.min.css' );
		wp_enqueue_script( 'compare-admin-script', get_template_directory_uri() . '/js/admin.js', false, false, true );
		wp_enqueue_style( 'compare-admin-style', get_template_directory_uri() . '/css/admin.css' );

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-dialog' );

		wp_enqueue_style( 'compare-jquery-ui', 'http://code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_style( 'compare-shortcodes-style', get_template_directory_uri() . '/css/admin.css' );
		wp_enqueue_script( 'compare-multidropdown', get_template_directory_uri() . '/js/multidropdown.js', false, false, true );
		wp_enqueue_media();

		if ( strpos( $_SERVER['REQUEST_URI'], 'widget' ) !== false ) {
			wp_enqueue_script( 'compare-shortcodes', get_template_directory_uri() . '/js/shortcodes.js', false, false, true );
		}
	}
}

add_action( 'admin_enqueue_scripts', 'compare_admin_resources' );

/*
Make ajaxurl available on the front end
*/
if ( ! function_exists( 'compare_custom_head' ) ) {
	function compare_custom_head() {
		echo '<script type="text/javascript">var ajaxurl = \'' . admin_url( 'admin-ajax.php' ) . '\';</script>';
	}
}

add_action( 'wp_head', 'compare_custom_head' );

/*
Get array of image ids from the
custom gallery meta field
*/
if ( ! function_exists( 'compare_smeta_images' ) ) {
	function compare_smeta_images( $meta_key, $post_id, $default ) {
		if ( class_exists( 'SM_Frontend' ) ) {
			global $sm;

			return $result = $sm->sm_get_meta( $meta_key, $post_id );
		} else {
			return $default;
		}
	}
}

/*
Add custommeta fields to certain post types
*/
if ( ! function_exists( 'compare_custom_meta_boxes' ) ) {
	function compare_custom_meta_boxes() {
		$post_meta_standard = array(
			array(
				'id'   => 'iframe_standard',
				'name' => __( 'Embed URL', 'compare' ),
				'type' => 'text',
				'desc' => __( 'Input custom URL which will be embeded as the blog post media.', 'compare' )
			),
		);

		$meta_boxes[] = array(
			'title'  => __( 'Standard Post Information', 'compare' ),
			'pages'  => 'post',
			'fields' => $post_meta_standard,
		);

		$post_meta_gallery = array(
			array(
				'id'         => 'gallery_images',
				'name'       => __( 'Gallery Images', 'compare' ),
				'type'       => 'image',
				'repeatable' => 1,
				'desc'       => __( 'Add images for the gallery post format. Drag and drop to change their order.', 'compare' )
			)
		);

		$meta_boxes[] = array(
			'title'  => __( 'Gallery Post Information', 'compare' ),
			'pages'  => 'post',
			'fields' => $post_meta_gallery,
		);


		$post_meta_audio = array(
			array(
				'id'   => 'iframe_audio',
				'name' => __( 'Audio URL', 'compare' ),
				'type' => 'text',
				'desc' => __( 'Input url to the audio source which will be media for the audio post format.', 'compare' )
			),

			array(
				'id'      => 'audio_type',
				'name'    => __( 'Audio Type', 'compare' ),
				'type'    => 'select',
				'options' => array(
					'embed'  => __( 'Embed', 'compare' ),
					'direct' => __( 'Direct Link', 'compare' )
				),
				'desc'    => __( 'Select format of the audio URL ( Direct Link - for mp3, Embed - for the links from SoundCloud, MixCloud,... ).', 'compare' )
			),
		);

		$meta_boxes[] = array(
			'title'  => __( 'Audio Post Information', 'compare' ),
			'pages'  => 'post',
			'fields' => $post_meta_audio,
		);

		$post_meta_video = array(
			array(
				'id'   => 'video',
				'name' => __( 'Video URL', 'compare' ),
				'type' => 'text',
				'desc' => __( 'Input url to the video source which will be media for the audio post format.', 'compare' )
			),
			array(
				'id'      => 'video_type',
				'name'    => __( 'Video Type', 'compare' ),
				'type'    => 'select',
				'options' => array(
					'remote' => __( 'Embed', 'compare' ),
					'self'   => __( 'Direct Link', 'compare' ),
				),
				'desc'    => __( 'Select format of the video URL ( Direct Link - for ogg, mp4..., Embed - for the links from YouTube, Vimeo,... ).', 'compare' )
			),
		);

		$meta_boxes[] = array(
			'title'  => __( 'Video Post Information', 'compare' ),
			'pages'  => 'post',
			'fields' => $post_meta_video,
		);

		$post_meta_quote = array(
			array(
				'id'   => 'blockquote',
				'name' => __( 'Input Quotation', 'compare' ),
				'type' => 'textarea',
				'desc' => __( 'Input quote as blog media for the quote post format.', 'compare' )
			),
			array(
				'id'   => 'cite',
				'name' => __( 'Input Quoted Person\'s Name', 'compare' ),
				'type' => 'text',
				'desc' => __( 'Input quoted person\'s name for the quote post format.', 'compare' )
			),
		);

		$meta_boxes[] = array(
			'title'  => __( 'Quote Post Information', 'compare' ),
			'pages'  => 'post',
			'fields' => $post_meta_quote,
		);

		$post_meta_link = array(
			array(
				'id'   => 'link',
				'name' => __( 'Input Link', 'compare' ),
				'type' => 'text',
				'desc' => __( 'Input link as blog media for the link post format.', 'compare' )
			),
		);

		$meta_boxes[] = array(
			'title'  => __( 'Link Post Information', 'compare' ),
			'pages'  => 'post',
			'fields' => $post_meta_link,
		);

		$product_meta = array(
			array(
				'id'   => 'product_short',
				'name' => __( 'Product In Short', 'compare' ),
				'type' => 'wysiwyg',
				'desc' => __( 'Input short description of the product.', 'compare' )
			),
			array(
				'id'      => 'product_clicks',
				'name'    => __( 'Product Clicks', 'compare' ),
				'type'    => 'text',
				'desc'    => __( 'This field is auto populated and it is used to order products by most popular. It represents number of clicks to product single page.', 'compare' ),
				'default' => 0
			),
			array(
				'id'      => 'product_store_clicks',
				'name'    => __( 'Product Store Clicks', 'compare' ),
				'type'    => 'text',
				'desc'    => __( 'This field is auto populated and it is used to order products by best sellers. It represents number of clicks to "Visit Website" on product single page.', 'compare' ),
				'default' => 0
			),
			array(
				'id'      => 'product_unique_id',
				'name'    => __( 'Product Unique ID', 'compare' ),
				'type'    => 'text',
				'desc'    => __( 'This is product unique ID. Field is populated via feed import.', 'compare' ),
				'default' => 0
			),
		);

		$meta_boxes[] = array(
			'title'  => __( 'Product Details', 'compare' ),
			'pages'  => 'product',
			'fields' => $product_meta,
		);

		$slider_meta = array(
			array(
				'id'         => 'slides',
				'name'       => __( 'Slides', 'jonas' ),
				'type'       => 'group',
				'fields'     => array(
					array(
						'id'   => 'slider_image',
						'name' => __( 'Image', 'jonas' ),
						'type' => 'image',
					),
					array(
						'id'   => 'slider_link',
						'name' => __( 'Link', 'jonas' ),
						'type' => 'text',
					),
				),
				'repeatable' => 1,
			),
			array(
				'id'      => 'slider_cat',
				'name'    => __( 'Show Slider On Category', 'jonas' ),
				'type'    => 'select',
				'options' => compare_get_categories_list()
			),
			array(
				'id'      => 'slider_cat_all',
				'name'    => __( 'Show Slider On All Categories Seach', 'jonas' ),
				'type'    => 'select',
				'options' => array(
					'no'  => __( 'No', 'compare' ),
					'yes' => __( 'Yes', 'compare' )
				)
			),
		);

		$post_meta_video = array(
			array(
				'id'   => 'video',
				'name' => __( 'Video URL', 'compare' ),
				'type' => 'text',
				'desc' => __( 'Input url to the video source which will be media for the audio post format.', 'compare' )
			),
			array(
				'id'      => 'video_type',
				'name'    => __( 'Video Type', 'compare' ),
				'type'    => 'select',
				'options' => array(
					'remote' => __( 'Embed', 'compare' ),
					'self'   => __( 'Direct Link', 'compare' ),
				),
				'desc'    => __( 'Select format of the video URL ( Direct Link - for ogg, mp4..., Embed - for the links from YouTube, Vimeo,... ).', 'compare' )
			),
		);

		$meta_boxes[] = array(
			'title'  => __( 'Video Post Information', 'compare' ),
			'pages'  => 'post',
			'fields' => $post_meta_video,
		);


		$meta_boxes[] = array(
			'title'  => __( 'Slider Data', 'jonas' ),
			'pages'  => 'slider',
			'fields' => $slider_meta,
		);

		/* PARSER META */
		$parser_meta = array(
			array(
				'id'      => 'parser_format',
				'name'    => __( 'Parser Format', 'compare' ),
				'type'    => 'select',
				'options' => array(
					'xml' => __( 'XML', 'compare' ),
					'csv' => __( 'CSV', 'compare' ),
				),
				'desc'    => __( 'Select Parser Format. ( required )', 'compare' )
			),
			array(
				'id'      => 'parser_product_root',
				'name'    => __( 'Product Root', 'compare' ),
				'type'    => 'text',
				'desc'    => __( 'Input feed product root. ( required ) If format is CSV then write here products/product as this will be root element after the CSV to XML conversion.', 'compare' ),
				'default' => ''
			),
			array(
				'id'      => 'parser_product_name',
				'name'    => __( 'Product Name', 'compare' ),
				'type'    => 'text',
				'desc'    => __( 'Input feed product name. ( required )', 'compare' ),
				'default' => ''
			),
			array(
				'id'      => 'parser_product_cats',
				'name'    => __( 'Product Categories', 'compare' ),
				'type'    => 'text',
				'desc'    => __( 'Input feed product categories. ( required )', 'compare' ),
				'default' => ''
			),
			array(
				'id'      => 'parser_cats_separator',
				'name'    => __( 'Parser Categories Separator', 'compare' ),
				'type'    => 'text',
				'desc'    => __( 'Input separator of the categories', 'compare' ),
				'default' => ''
			),
			array(
				'id'      => 'parser_product_tags',
				'name'    => __( 'Product Tags', 'compare' ),
				'type'    => 'text',
				'desc'    => __( 'Input feed product tags.', 'compare' ),
				'default' => ''
			),
			array(
				'id'      => 'parser_tags_separator',
				'name'    => __( 'Parser Tags Separator', 'compare' ),
				'type'    => 'text',
				'desc'    => __( 'Input separator of the tags', 'compare' ),
				'default' => ''
			),
			array(
				'id'      => 'parser_product_brand',
				'name'    => __( 'Product Brand', 'compare' ),
				'type'    => 'text',
				'desc'    => __( 'Input feed product brand. ( required )', 'compare' ),
				'default' => ''
			),
			array(
				'id'      => 'parser_product_price',
				'name'    => __( 'Product Price', 'compare' ),
				'type'    => 'text',
				'desc'    => __( 'Input feed product price. ( required )', 'compare' ),
				'default' => ''
			),
			array(
				'id'      => 'parser_product_url',
				'name'    => __( 'Product URL', 'compare' ),
				'type'    => 'text',
				'desc'    => __( 'Input feed product url.', 'compare' ),
				'default' => ''
			),
			array(
				'id'      => 'parser_product_id',
				'name'    => __( 'Product ID', 'compare' ),
				'type'    => 'text',
				'desc'    => __( 'Input feed product id. ( required )', 'compare' ),
				'default' => ''
			),
			array(
				'id'      => 'parser_desc',
				'name'    => __( 'Product Description', 'compare' ),
				'type'    => 'text',
				'desc'    => __( 'Input feed description.', 'compare' ),
				'default' => ''
			),
			array(
				'id'      => 'parser_short_desc',
				'name'    => __( 'Product Short Description', 'compare' ),
				'type'    => 'text',
				'desc'    => __( 'Input feed short description.', 'compare' ),
				'default' => ''
			),
			array(
				'id'      => 'parser_shipping',
				'name'    => __( 'Product Shipping', 'compare' ),
				'type'    => 'text',
				'desc'    => __( 'Input feed product shipping.', 'compare' ),
				'default' => ''
			),
			array(
				'id'      => 'parser_shipping_comment',
				'name'    => __( 'Product Shipping', 'compare' ),
				'type'    => 'text',
				'desc'    => __( 'Input feed short shipping comment.', 'compare' ),
				'default' => ''
			),
			array(
				'id'      => 'parser_product_image',
				'name'    => __( 'Product Image', 'compare' ),
				'type'    => 'text',
				'desc'    => __( 'Input feed product image.', 'compare' ),
				'default' => ''
			),
		);

		$meta_boxes[] = array(
			'title'  => __( 'Feed Details', 'compare' ),
			'pages'  => 'parser',
			'fields' => $parser_meta,
		);

		return $meta_boxes;
	}

	add_filter( 'sm_meta_boxes', 'compare_custom_meta_boxes' );
}

/*
Get list of the categories
for the theme options
*/
if ( ! function_exists( 'compare_get_categories_list' ) ) {
	function compare_get_categories_list() {
		$terms      = get_terms( 'product-cat', array( 'hide_empty' => false ) );
		$terms_list = array(
			'' => __( 'None', 'compare' )
		);
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$terms_list[ $term->slug ] = $term->name;
			}
		}

		return $terms_list;
	}
}

/*
Generate HEX code of the RGB color
*/
if ( ! function_exists( 'compare_hex2rgb' ) ) {
	function compare_hex2rgb( $hex ) {
		$hex = str_replace( "#", "", $hex );

		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );

		return $r . ", " . $g . ", " . $b;
	}
}

/*
Allow XML files to be uploaded
in order to use them for product feed import
*/
if ( ! function_exists( 'compare_custom_upload_xml' ) ) {
	function compare_custom_upload_xml( $mimes ) {
		$mimes = array_merge( $mimes, array( 'xml' => 'application/xml' ) );

		return $mimes;
	}
}
add_filter( 'upload_mimes', 'compare_custom_upload_xml' );

/*
Create custom walker for generating menu
*/

class compare_walker extends Walker_Nav_Menu {

	/**
	 * @see Walker::start_lvl()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat( "\t", $depth );
		$output .= "\n$indent<ul role=\"menu\" class=\" dropdown-menu\">\n";
	}

	/**
	 * @see Walker::start_el()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Menu item data object.
	 * @param int $depth Depth of menu item. Used for padding.
	 * @param int $current_page Menu item ID.
	 * @param object $args
	 */
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		/**
		 * Dividers, Headers or Disabled
		 * =============================
		 * Determine whether the item is a Divider, Header, Disabled or regular
		 * menu item. To prevent errors we use the strcasecmp() function to so a
		 * comparison that is not case sensitive. The strcasecmp() function returns
		 * a 0 if the strings are equal.
		 */
		if ( strcasecmp( $item->attr_title, 'divider' ) == 0 && $depth === 1 ) {
			$output .= $indent . '<li role="presentation" class="divider">';
		} else if ( strcasecmp( $item->title, 'divider' ) == 0 && $depth === 1 ) {
			$output .= $indent . '<li role="presentation" class="divider">';
		} else if ( strcasecmp( $item->attr_title, 'dropdown-header' ) == 0 && $depth === 1 ) {
			$output .= $indent . '<li role="presentation" class="dropdown-header">' . esc_attr( $item->title );
		} else if ( strcasecmp( $item->attr_title, 'disabled' ) == 0 ) {
			$output .= $indent . '<li role="presentation" class="disabled"><a href="#">' . esc_attr( $item->title ) . '</a>';
		} else {

			$mega_menu_custom = get_post_meta( $item->ID, 'mega-menu-set', true );

			$class_names = $value = '';
			$classes     = empty( $item->classes ) ? array() : (array) $item->classes;
			if ( ! empty( $mega_menu_custom ) ) {
				$classes[] = 'mega_menu_li';
			}
			$classes[]   = 'menu-item-' . $item->ID;
			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );

			if ( $args->has_children ) {
				$class_names .= ' dropdown';
			}

			$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';
			$id          = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args );
			$id          = $id ? ' id="' . esc_attr( $id ) . '"' : '';

			$output .= $indent . '<li' . $id . $value . $class_names . '>';

			$atts           = array();
			$atts['title']  = ! empty( $item->title ) ? $item->title : '';
			$atts['target'] = ! empty( $item->target ) ? $item->target : '';
			$atts['rel']    = ! empty( $item->xfn ) ? $item->xfn : '';

			// If item has_children add atts to a.
			$atts['href'] = ! empty( $item->url ) ? $item->url : '';
			if ( $args->has_children ) {
				$atts['data-toggle']   = 'dropdown';
				$atts['class']         = 'dropdown-toggle';
				$atts['data-hover']    = 'dropdown';
				$atts['aria-haspopup'] = 'true';
			}

			$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args );

			$attributes = '';
			foreach ( $atts as $attr => $value ) {
				if ( ! empty( $value ) ) {
					$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
					$attributes .= ' ' . $attr . '="' . $value . '"';
				}
			}

			$item_output = $args->before;

			/*
			* Glyphicons
			* ===========
			* Since the the menu item is NOT a Divider or Header we check the see
			* if there is a value in the attr_title property. If the attr_title
			* property is NOT null we apply it as the class name for the glyphicon.
			*/

			$item_output .= '<a' . $attributes . '>';
			if ( ! empty( $item->attr_title ) ) {
				$item_output .= '<div class="menu-tooltip">' . $item->attr_title . '</div>';
			}

			$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
			if ( ! empty( $mega_menu_custom ) ) {
				$registered_widgets = wp_get_sidebars_widgets();
				$count              = count( $registered_widgets[ $mega_menu_custom ] );
				$item_output .= ' <i class="fa fa-angle-down"></i>';
				$item_output .= '</a>';

				$item_output .= '<ul class="list-unstyled mega_menu col-' . $count . '" data-child-width="' . esc_attr( 100 / $count ) . '">';
				ob_start();
				if ( is_active_sidebar( $mega_menu_custom ) ) {
					dynamic_sidebar( $mega_menu_custom );
				}
				$item_output .= ob_get_contents();
				ob_end_clean();
				$item_output .= '</ul>';
			} else {
				if ( $args->has_children && 0 === $depth ) {
					$item_output .= ' <i class="fa fa-angle-down"></i>';
				}
				$item_output .= '</a>';
			}
			$item_output .= $args->after;

			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}
	}

	/**
	 * Traverse elements to create list from elements.
	 *
	 * Display one element if the element doesn't have any children otherwise,
	 * display the element and its children. Will only traverse up to the max
	 * depth and no ignore elements under that depth.
	 *
	 * This method shouldn't be called directly, use the walk() method instead.
	 *
	 * @see Walker::start_el()
	 * @since 2.5.0
	 *
	 * @param object $element Data object
	 * @param array $children_elements List of elements to continue traversing.
	 * @param int $max_depth Max depth to traverse.
	 * @param int $depth Depth of current element.
	 * @param array $args
	 * @param string $output Passed by reference. Used to append additional content.
	 *
	 * @return null Null on failure with no changes to parameters.
	 */
	public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
		if ( ! $element ) {
			return;
		}

		$id_field = $this->db_fields['id'];

		// Display this element.
		if ( is_object( $args[0] ) ) {
			$args[0]->has_children = ! empty( $children_elements[ $element->$id_field ] );
		}

		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}

	/**
	 * Menu Fallback
	 * =============
	 * If this function is assigned to the wp_nav_menu's fallback_cb variable
	 * and a manu has not been assigned to the theme location in the WordPress
	 * menu manager the function with display nothing to a non-logged in user,
	 * and will add a link to the WordPress menu manager if logged in as an admin.
	 *
	 * @param array $args passed from the wp_nav_menu function.
	 *
	 */
	public static function fallback( $args ) {
		if ( current_user_can( 'manage_options' ) ) {

			extract( $args );

			$fb_output = null;

			if ( $container ) {
				$fb_output = '<' . $container;

				if ( $container_id ) {
					$fb_output .= ' id="' . $container_id . '"';
				}

				if ( $container_class ) {
					$fb_output .= ' class="' . $container_class . '"';
				}

				$fb_output .= '>';
			}

			$fb_output .= '<ul';

			if ( $menu_id ) {
				$fb_output .= ' id="' . $menu_id . '"';
			}

			if ( $menu_class ) {
				$fb_output .= ' class="' . $menu_class . '"';
			}

			$fb_output .= '>';
			$fb_output .= '<li><a href="' . admin_url( 'nav-menus.php' ) . '">Add a menu</a></li>';
			$fb_output .= '</ul>';

			if ( $container ) {
				$fb_output .= '</' . $container . '>';
			}

			echo $fb_output;
		}
	}
}

/*
Set tag sizes
*/
if ( ! function_exists( 'compare_custom_tag_cloud_widget' ) ) {
	function compare_custom_tag_cloud_widget( $args ) {
		$args['largest']  = 13; //largest tag
		$args['smallest'] = 13; //smallest tag
		$args['unit']     = 'px'; //tag font unit
		return $args;
	}
}
add_filter( 'widget_tag_cloud_args', 'compare_custom_tag_cloud_widget' );

/*
format wp_link_pages so it has the right css applied to it
*/
if ( ! function_exists( 'compare_link_pages' ) ) {
	function compare_link_pages() {
		$post_pages = wp_link_pages( array(
			'before'         => '',
			'after'          => '',
			'link_before'    => '<span>',
			'link_after'     => '</span>',
			'next_or_number' => 'number',
			'separator'      => ' ',
			'echo'           => 0
		) );
		/* format pages that are not current ones */
		$post_pages = str_replace( '<a', '<li><a', $post_pages );
		$post_pages = str_replace( '</span></a>', '</a></li>', $post_pages );
		$post_pages = str_replace( '><span>', '>', $post_pages );

		/* format current page */
		$post_pages = str_replace( '<span>', '<li class="active"><a href="javascript:;">', $post_pages );
		$post_pages = str_replace( '</span>', '</a></li>', $post_pages );

		return $post_pages;
	}

	/*
	Get list of thag for the posts
	*/
	function compare_tags_list() {
		$tags     = get_the_tags();
		$tag_list = array();
		if ( ! empty( $tags ) ) {
			foreach ( $tags as $tag ) {
				$tag_list[] = '<a href="' . esc_url( get_tag_link( $tag->term_id ) ) . '">' . $tag->name . '</a>';
			}
		}

		return join( ', ', $tag_list );
	}
}

/*
Liimit Excerpt
*/
if ( ! function_exists( 'compare_the_excerpt' ) ) {
	function compare_the_excerpt() {
		$excerpt = get_the_excerpt();
		if ( strlen( $excerpt ) > 167 ) {
			$excerpt = substr( $excerpt, 0, 167 );
			$excerpt = substr( $excerpt, 0, strripos( $excerpt, " " ) );
			$excerpt = $excerpt . '...';
		}

		return '<p>' . $excerpt . '</p>';
	}
}


/*
Create list of categories for the posts
*/
if ( ! function_exists( 'compare_categories_list' ) ) {
	function compare_categories_list() {
		$category_list = get_the_category();
		$categories    = array();
		if ( ! empty( $category_list ) ) {
			foreach ( $category_list as $category ) {
				$categories[] = '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '">' . $category->name . '</a>';
			}
		}

		return join( ', ', $categories );
	}
}
/*
Format pagination so it has correct style applied to it
*/
if ( ! function_exists( 'compare_format_pagination' ) ) {
	function compare_format_pagination( $page_links ) {
		$list = '';
		if ( ! empty( $page_links ) ) {
			foreach ( $page_links as $page_link ) {
				$page_link = str_replace( "<span class='page-numbers current'>", '<a href="javascript:;">', $page_link );
				$page_link = str_replace( '</span>', '</a>', $page_link );
				if ( stristr( $page_link, 'prev' ) ) {
					$list .= '<li class="prev">' . $page_link . '</li>';
				} else if ( stristr( $page_link, 'next' ) ) {
					$list .= '<li class="next">' . $page_link . '</li>';
				} else if ( stristr( $page_link, 'javascript:;' ) ) {
					$list .= '<li class="active">' . $page_link . '</li>';
				} else {
					$list .= '<li>' . $page_link . '</li>';
				}
			}
		}

		return $list;
	}
}

/*
Generate random string for the usage in shortcodes
*/
if ( ! function_exists( 'compare_random_string' ) ) {
	function compare_random_string( $length = 10 ) {
		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$random     = '';
		for ( $i = 0; $i < $length; $i ++ ) {
			$random .= $characters[ rand( 0, strlen( $characters ) - 1 ) ];
		}

		return $random;
	}
}


/*
Add the ... at the end of the excerpt
*/
if ( ! function_exists( 'compare_new_excerpt_more' ) ) {
	function compare_new_excerpt_more( $more ) {
		return '';
	}
}
add_filter( 'excerpt_more', 'compare_new_excerpt_more' );

/*
Create options for the select box in the category icon select
*/
if ( ! function_exists( 'compare_icons_list' ) ) {
	function compare_icons_list( $value ) {
		$icons_list = compare_awesome_icons_list();

		$select_data = '';

		foreach ( $icons_list as $key => $label ) {
			$select_data .= '<option value="' . esc_attr( $key ) . '" ' . ( $value == $key ? 'selected="selected"' : '' ) . '>' . $label . '</option>';
		}

		return $select_data;
	}
}

/*
Send mail from the contact form
*/
if ( ! function_exists( 'compare_send_contact' ) ) {
	function compare_send_contact() {
		$errors  = array();
		$name    = isset( $_POST['name'] ) ? esc_sql( $_POST['name'] ) : '';
		$email   = isset( $_POST['email'] ) ? esc_sql( $_POST['email'] ) : '';
		$message = isset( $_POST['message'] ) ? esc_sql( $_POST['message'] ) : '';
		if ( isset( $_POST['captcha'] ) ) {
			if ( ! empty( $name ) && ! empty( $email ) && ! empty( $message ) ) {
				if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
					$email_to = compare_get_option( 'contact_mail' );
					$subject  = compare_get_option( 'contact_form_subject' );
					if ( ! empty( $email_to ) ) {
						$message = "
						" . __( 'Name: ', 'compare' ) . " {$name} \n
						" . __( 'Email: ', 'compare' ) . " {$email} \n
						" . __( 'Message: ', 'compare' ) . "\n {$message} \n
					";
						$info    = @wp_mail( $email_to, $subject, $message );
						if ( $info ) {
							echo json_encode( array(
								'success' => __( 'Your message was successfully submitted.', 'compare' ),
							) );
							die();
						} else {
							echo json_encode( array(
								'error' => __( 'Unexpected error while attempting to send e-mail.', 'compare' ),
							) );
							die();
						}
					} else {
						echo json_encode( array(
							'error' => __( 'Message is not send since the recepient email is not yet set.', 'compare' ),
						) );
						die();
					}
				} else {
					echo json_encode( array(
						'error' => __( 'Email is not valid.', 'compare' ),
					) );
					die();
				}
			} else {
				echo json_encode( array(
					'error' => __( 'All fields are required.', 'compare' ),
				) );
				die();
			}
		} else {
			echo json_encode( array(
				'error' => __( 'Captcha is wrong.', 'compare' ),
			) );
			die();
		}
	}
}
add_action( 'wp_ajax_contact', 'compare_send_contact' );
add_action( 'wp_ajax_nopriv_contact', 'compare_send_contact' );

/*
Send subscription details to MailChimp
*/
if ( ! function_exists( 'compare_send_subscription' ) ) {
	function compare_send_subscription( $email = '' ) {
		$email    = ! empty( $email ) ? $email : $_POST["email"];
		$response = array();
		if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			require_once( locate_template( 'includes/mailchimp.php' ) );
			$chimp_api     = compare_get_option( "mail_chimp_api" );
			$chimp_list_id = compare_get_option( "mail_chimp_list_id" );
			if ( ! empty( $chimp_api ) && ! empty( $chimp_list_id ) ) {
				$mc     = new Compare_MailChimp( $chimp_api );
				$result = $mc->call( 'lists/subscribe', array(
					'id'    => $chimp_list_id,
					'email' => array( 'email' => $email )
				) );

				if ( $result === false ) {
					$response['error'] = __( 'There was an error contacting the API, please try again.', 'compare' );
				} else if ( isset( $result['status'] ) && $result['status'] == 'error' ) {
					$response['error'] = json_encode( $result );
				} else {
					$response['success'] = __( 'You have successfully subscribed to the newsletter.', 'compare' );
				}

			} else {
				$response['error'] = __( 'API data are not yet set.', 'compare' );
			}
		} else {
			$response['error'] = __( 'Email is empty or invalid.', 'compare' );
		}

		echo json_encode( $response );
		die();
	}
}
add_action( 'wp_ajax_subscribe', 'compare_send_subscription' );
add_action( 'wp_ajax_nopriv_subscribe', 'compare_send_subscription' );


/*
Extract avatar URL from the code
*/
if ( ! function_exists( 'compare_get_avatar_url' ) ) {
	function compare_get_avatar_url( $get_avatar ) {
		preg_match( "/src='(.*?)'/i", $get_avatar, $matches );
		if ( empty( $matches[1] ) ) {
			preg_match( "/src=\"(.*?)\"/i", $get_avatar, $matches );
		}

		return $matches[1];
	}
}

/*
Add video container div
arround embeded videos
*/
if ( ! function_exists( 'compare_embed_html' ) ) {
	function compare_embed_html( $html ) {
		return '<div class="video-container">' . $html . '</div>';
	}
}
add_filter( 'embed_oembed_html', 'compare_embed_html', 10, 3 );
add_filter( 'video_embed_html', 'compare_embed_html' ); // Jetpack

/*
Create comments tree
*/
if ( ! function_exists( 'compare_comments' ) ) {
	function compare_comments( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		$add_below          = '';
		global $compare_can_review;
		if ( $comment->user_id == get_current_user_id() ) {
			$compare_can_review = 'already_reviewed';
		}
		?>
		<!-- comment-1 -->
		<div class="media <?php echo $depth > 1 ? esc_attr( 'media-reply' ) : '' ?>">
			<div class="media-left">
				<?php
				$avatar = compare_get_avatar_url( get_avatar( $comment, 75 ) );
				if ( ! empty( $avatar ) ): ?>
					<a href="javascript:;">
						<img src="<?php echo esc_url( $avatar ); ?>" class="media-object img-circle comment-avatar"
						     title="" alt="">
					</a>
				<?php endif; ?>
			</div>
			<div class="media-body">
				<div class="pull-left">
					<h4><?php comment_author(); ?></h4>
					<span><?php printf( _x( '%s ago', '%s = human-readable time difference', 'compare' ), human_time_diff( get_comment_time( 'U' ), current_time( 'timestamp' ) ) ); ?></span>
					<?php if ( is_singular( 'product' ) ): ?>
						<?php
						$rate = get_comment_meta( $comment->comment_ID, 'review', true );
						echo compare_rating_display( $rate );
						?>
					<?php endif; ?>
				</div>
				<?php if ( ! is_singular( 'product' ) ): ?>
					<div class="pull-right">
						<?php
						comment_reply_link( array_merge( $args, array(
							'reply_text' => 'REPLY',
							'add_below'  => $add_below,
							'depth'      => $depth,
							'max_depth'  => $args['max_depth']
						) ) );
						?>
					</div>
				<?php endif; ?>
				<div class="clearfix"></div>
				<hr/>
				<?php
				if ( $comment->comment_approved != '0' ) {
					?>
					<p><?php echo get_comment_text(); ?></p>
					<?php
				} else { ?>
					<p><?php _e( 'Your comment is awaiting moderation.', 'compare' ); ?></p>
					<?php
				}
				?>
			</div>

		</div>
		<!-- .comment-1 -->
		<?php
	}
}

/*
Do not put anything at the end of comments
*/
if ( ! function_exists( 'compare_end_comments' ) ) {
	function compare_end_comments() {
		return "";
	}
}

/*
Check if post has media
*/
if ( ! function_exists( 'compare_has_media' ) ) {
	function compare_has_media() {
		$post_format = get_post_format();
		switch ( $post_format ) {
			case 'aside' :
				return has_post_thumbnail() ? true : false;
				break;

			case 'audio' :
				$iframe_audio = get_post_meta( get_the_ID(), 'iframe_audio', true );
				if ( ! empty( $iframe_audio ) ) {
					return true;
				} else if ( has_post_thumbnail() ) {
					return true;
				} else {
					return false;
				}
				break;

			case 'chat' :
				return has_post_thumbnail() ? true : false;
				break;

			case 'gallery' :
				$post_meta      = get_post_custom();
				$gallery_images = compare_smeta_images( 'gallery_images', get_the_ID(), array() );
				if ( ! empty( $gallery_images ) ) {
					return true;
				} else if ( has_post_thumbnail() ) {
					return true;
				} else {
					return false;
				}
				break;

			case 'image':
				return has_post_thumbnail() ? true : false;
				break;

			case 'link' :
				$link = get_post_meta( get_the_ID(), 'link', true );
				if ( ! empty( $link ) ) {
					return true;
				} else {
					return false;
				}
				break;

			case 'quote' :
				$blockquote = get_post_meta( get_the_ID(), 'blockquote', true );
				$cite       = get_post_meta( get_the_ID(), 'cite', true );
				if ( ! empty( $blockquote ) || ! empty( $cite ) ) {
					return true;
				} else if ( has_post_thumbnail() ) {
					return true;
				} else {
					return false;
				}
				break;

			case 'status' :
				return has_post_thumbnail() ? true : false;
				break;

			case 'video' :
				$video = get_post_meta( get_the_ID(), 'video', true );
				if ( ! empty( $video ) ) {
					return true;
				} else if ( has_post_thumbnail() ) {
					return true;
				} else {
					return false;
				}
				break;

			default:
				$iframe_standard = get_post_meta( get_the_ID(), 'iframe_standard', true );
				if ( ! empty( $iframe_standard ) ) {
					return true;
				} else if ( has_post_thumbnail() ) {
					return true;
				} else {
					return false;
				}
				break;
		}
	}
}


/*
Parse URL based on the source
*/
if ( ! function_exists( 'compare_parse_url' ) ) {
	function compare_parse_url( $url ) {
		if ( stripos( $url, 'youtube' ) ) {
			$temp = explode( '?v=', $url );

			return 'https://www.youtube.com/embed/' . $temp[1];
		} else if ( stripos( $url, 'vimeo' ) ) {
			$temp = explode( 'vimeo.com/', $url );

			return '//player.vimeo.com/video/' . $temp[1];
		} else {
			return $url;
		}
	}
}

/*
Append style to shortcodes
*/
if ( ! function_exists( 'compare_shortcode_style' ) ) {
	function compare_shortcode_style( $style_css ) {
		$style_css = str_replace( '<style', '<style scoped', $style_css );

		return $style_css;
	}
}

/*
Generate list of categories
for the main search dropdown
*/
if ( ! function_exists( 'compare_main_categories_list' ) ) {
	function compare_main_categories_list() {
		global $COMPARE_SEARCH_URL;
		global $compare_slugs;
		$product_cat           = get_query_var( $compare_slugs['product-cat'], '' );
		$search_bar_categories = compare_get_option( 'search_bar_categories' );
		if ( ! empty( $search_bar_categories ) ) {
			foreach ( $search_bar_categories as $category_id ) {
				if ( is_numeric( $category_id ) ) {
					$category = get_term_by( 'id', $category_id, 'product-cat' );
				} else {
					$category = get_term_by( 'slug', $category_id, 'product-cat' );
				}
				if ( ! empty( $category ) && ! is_wp_error( $category ) ) {
					$term_meta     = get_option( 'taxonomy_' . $category->term_id );
					$category_icon = ! empty( $term_meta['category_icon'] ) ? $term_meta['category_icon'] : '';
					echo '<li><a href="' . esc_url( compare_add_query_arg( array( $compare_slugs['product-cat'] => $category->slug ), $COMPARE_SEARCH_URL ) ) . '"><span>+</span><i class="fa fa-' . esc_attr( $category_icon ) . ' fa-fw"></i>' . $category->name . '</a>';
					if ( ! empty( $product_cat ) && $category->slug == $category ) {
						echo 'fsd';
						compare_main_categories_sublist( $category );
					}
					compare_main_categories_megamenu( $category );
					echo '</li>';
				}
			}
		}
	}
}

/*
Generate mega menu for the each item
in the main category search
*/
if ( ! function_exists( 'compare_main_categories_megamenu' ) ) {
	function compare_main_categories_megamenu( $category ) {
		global $COMPARE_SEARCH_URL;
		global $compare_slugs;
		$term_meta      = get_option( 'taxonomy_' . $category->term_id );
		$category_image = ! empty( $term_meta['category_image'] ) ? $term_meta['category_image'] : '';
		$style          = '';
		if ( ! empty( $category_image ) ) {
			$image_data = wp_get_attachment_image_src( $category_image, 'full' );
			$style      = 'background-image: url(' . $image_data[0] . ')';
		}

		$subterms = get_terms( 'product-cat', array(
			'parent'     => $category->term_id,
			'hide_empty' => false
		) );
		if ( ! empty( $subterms ) ) {
			?>
			<div class="category_mega_menu_wrap" style="<?php echo esc_attr( $style ) ?>">
				<h4><?php echo esc_html( $category->name ) ?></h4>
				<ul class="list-unstyled sub_category_mega_menu">
					<?php
					foreach ( $subterms as $subterm ) {
						?>
						<li>
							<a href="<?php echo esc_url( compare_add_query_arg( array( $compare_slugs['product-cat'] => $subterm->slug ), $COMPARE_SEARCH_URL ) ) ?>"><?php echo esc_html( $subterm->name ); ?></a>
						</li>
						<?php
					}
					?>
				</ul>
			</div>
			<?php
		}
	}
}

/*
Generate subitems list for the
compare_main_categories_megamenu() function
*/
if ( ! function_exists( 'compare_main_categories_sublist' ) ) {
	function compare_main_categories_sublist( $category ) {
		global $COMPARE_SEARCH_URL;
		global $compare_slugs;
		$subterms = get_terms( 'product-cat', array(
			'parent'     => $category->term_id,
			'hide_empty' => false,
			'orderby'    => 'name'
		) );
		if ( ! empty( $subterms ) ) {
			?>
			<ul class="list-unstyled sub_categories_mega_menu">
				<?php
				foreach ( $subterms as $subterm ) {
					?>
					<li>
						<a href="<?php echo esc_url( compare_add_query_arg( array( $compare_slugs['product-cat'] => $subterm->slug ), $COMPARE_SEARCH_URL ) ) ?>"><?php echo esc_html( $subterm->name ); ?></a>
					</li>
					<?php
				}
				?>
			</ul>
			<?php
		}
	}
}

/*
Get list of the post type
*/
if ( ! function_exists( 'compare_get_post_list' ) ) {
	function compare_get_post_list( $post_type, $direction = 'right' ) {
		$args = array(
			'post_status'    => 'publish',
			'posts_per_page' => - 1,
			'post_type'      => $post_type
		);

		$posts_list = array();

		$posts = get_posts( $args );

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				if ( $direction == 'right' ) {
					$posts_list[ $post->ID ] = $post->post_title;
				} else {
					$posts_list[ $post->post_title ] = $post->ID;
				}
			}
		}

		return $posts_list;
	}
}

/*
Get icon for the white block titles
*/
if ( ! function_exists( 'compare_get_white_title_icon' ) ) {
	function compare_get_white_title_icon() {
		$white_title_icon = compare_get_option( 'white_title_icon' );
		if ( ! empty( $white_title_icon ) ) {
			return '<i class="fa fa-' . esc_attr( $white_title_icon ) . '"></i>';
		}

		return '';
	}
}

/*
Get list of taxonomies
*/
if ( ! function_exists( 'compare_get_taxonomy_list' ) ) {
	function compare_get_taxonomy_list( $taxonomy, $direction = 'right', $args = array() ) {
		$terms_list = array();
		$args       = array( 'hide_empty' => false ) + $args;
		$terms      = get_terms( $taxonomy, $args );
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				if ( $direction == 'right' ) {
					$terms_list[ $term->slug ] = $term->name;
				} else {
					$terms_list[ $term->name ] = $term->slug;
				}
			}
		}

		return $terms_list;
	}
}

function compare_get_db_cats() {
	global $wpdb;
	$terms_list = array();

	$terms = $wpdb->get_results( "SELECT t.name, t.slug FROM {$wpdb->prefix}term_taxonomy AS tt LEFT JOIN {$wpdb->prefix}terms AS t ON tt.term_id = t.term_id WHERE tt.taxonomy = 'product-cat' AND tt.parent = 0" );
	if ( ! empty( $terms ) ) {
		foreach ( $terms as $term ) {
			$terms_list[ $term->slug ] = $term->name;
		}
	}

	return $terms_list;
}

/*
Format number as price for frontend usage
*/
if ( ! function_exists( 'compare_format_currency_number' ) ) {
	function compare_format_currency_number( $value ) {
		$unit          = compare_get_option( 'unit' );
		$unit_position = compare_get_option( 'unit_position' );
		$value         = trim( $value );
		if ( $unit_position == 'front' ) {
			return $unit . ' ' . number_format( $value );
		} else {
			return number_format( $value ) . ' ' . $unit;
		}
	}
}

/*
Create price ranges for the search filter
on search page
*/
if ( ! function_exists( 'compare_format_price_range' ) ) {
	function compare_format_price_range( $price_range ) {
		if ( stristr( $price_range, '-' ) !== false ) {
			$temp = explode( '-', $price_range );

			return compare_format_currency_number( $temp[0] ) . ' - ' . compare_format_currency_number( $temp[1] );
		} else {
			$price_range = str_replace( '+', '', $price_range );

			return compare_format_currency_number( $price_range ) . '+';
		}
	}
}

/*
Create site breadcrumbs
*/
if ( ! function_exists( 'compare_get_breadcrumbs' ) ) {
	function compare_get_breadcrumbs() {
		$page_on_front = get_option( 'page_on_front' );
		$breadcrumbs   = '';
		global $compare_slugs;
		if ( ( is_home() && empty( $page_on_front ) ) || is_front_page() ) {
			return $breadcrumbs;
		} else {
			$breadcrumbs = '<ul class="list-unstyled list-inline breadcrumbs">';
			$breadcrumbs .= '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . __( 'Home', 'compare' ) . '</a></li>';
			if ( is_home() ) {
				$breadcrumbs .= '<li>' . get_the_title( get_option( 'page_for_posts' ) ) . '</li>';
			} else if ( is_404() ) {
				$breadcrumbs .= '<li>' . __( '404 Page', 'compare' ) . '</li>';
			} else if ( is_category() ) {
				$breadcrumbs .= '<li><a href="' . get_permalink( get_option( 'page_for_posts' ) ) . '">' . get_the_title( get_option( 'page_for_posts' ) ) . '</a></li>';
				$breadcrumbs .= '<li>' . __( 'Category - ', 'compare' ) . single_cat_title( ' ', false ) . '</li>';
			} else if ( is_tag() ) {
				$breadcrumbs .= '<li><a href="' . get_permalink( get_option( 'page_for_posts' ) ) . '">' . get_the_title( get_option( 'page_for_posts' ) ) . '</a></li>';
				$breadcrumbs .= '<li>' . __( 'Tag - ', 'compare' ) . get_query_var( 'tag' ) . '</li>';
			} else if ( is_tax( 'product-cat' ) ) {
				$term = get_term_by( 'slug', get_query_var( 'term' ), 'product-cat' );
				if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
					$breadcrumbs .= '<li>' . __( 'Category - ', 'compare' ) . $term->name . '</li>';
				}
			} else if ( is_tax( 'product-brand' ) ) {
				$term = get_term_by( 'slug', get_query_var( 'term' ), 'product-brand' );
				if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
					$breadcrumbs .= '<li>' . __( 'Brand - ', 'compare' ) . $term->name . '</li>';
				}
			} else if ( is_tax( 'product-tag' ) ) {
				$term = get_term_by( 'slug', get_query_var( 'term' ), 'product-tag' );
				if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
					$breadcrumbs .= '<li>' . __( 'Tag - ', 'compare' ) . $term->name . '</li>';
				}
			} else if ( is_author() ) {
				if ( ! empty( $_GET['post_type'] ) ) {
					$breadcrumbs .= '<li><a href="' . reviews_get_permalink_by_tpl( 'page-tpl_search' ) . '">' . __( 'Search', 'compare' ) . '</a></li>';
					$breadcrumbs .= '<li>' . __( 'Reviews written by ', 'compare' ) . get_the_author_meta( 'display_name' ) . '</li>';
				} else {
					$breadcrumbs .= '<li><a href="' . get_permalink( get_option( 'page_for_posts' ) ) . '">' . get_the_title( get_option( 'page_for_posts' ) ) . '</a></li>';
					$breadcrumbs .= '<li>' . __( 'Posts written by ', 'compare' ) . get_the_author_meta( 'display_name' ) . '</li>';
				}
			} else if ( is_search() ) {
				$breadcrumbs .= '<li><a href="' . get_permalink( get_option( 'page_for_posts' ) ) . '">' . get_the_title( get_option( 'page_for_posts' ) ) . '</a></li>';
				$breadcrumbs .= '<li>' . __( 'Search results for: ', 'compare' ) . ' ' . get_search_query() . '</li>';
			} else if ( is_archive() ) {
				$breadcrumbs .= '<li><a href="' . get_permalink( get_option( 'page_for_posts' ) ) . '">' . get_the_title( get_option( 'page_for_posts' ) ) . '</a></li>';
				$breadcrumbs .= '<li>' . __( 'Archive for ', 'compare' ) . single_month_title( ' ', false ) . '</li>';
			} else if ( is_page_template( 'page-tpl_search.php' ) ) {
				$breadcrumbs .= '<li>' . __( 'Product search results', 'compare' );
				$product_cat   = get_query_var( $compare_slugs['product-cat'], '' );
				$product_tag   = get_query_var( $compare_slugs['product-tag'], '' );
				$product_brand = get_query_var( $compare_slugs['product-brand'], '' );
				$keyword       = get_query_var( $compare_slugs['keyword'], '' );
				if ( ! empty( $product_cat ) && ! empty( $product_cat[0] ) ) {
					if ( is_array( $product_cat ) ) {
						$list = array();
						foreach ( $product_cat as $cat_slug ) {
							$term   = get_term_by( 'slug', $cat_slug, 'product-cat' );
							$list[] = $term->name;
						}

						$breadcrumbs .= __( ' in ', 'compare' ) . join( ', ', $list );
					} else {
						$term = get_term_by( 'slug', $product_cat, 'product-cat' );
						$breadcrumbs .= __( ' in ', 'compare' ) . $term->name;
					}
				}
				if ( ! empty( $product_tag ) ) {
					$term = get_term_by( 'slug', $product_tag, 'product-tag' );
					$breadcrumbs .= __( ' tagged with ', 'compare' ) . $term->name;
				}
				if ( ! empty( $product_brand ) && ! empty( $product_brand[0] ) ) {
					if ( is_array( $product_brand ) ) {
						$list = array();
						foreach ( $product_brand as $brand_slug ) {
							$term   = get_term_by( 'slug', $brand_slug, 'product-brand' );
							$list[] = $term->name;
						}

						$breadcrumbs .= ( sizeof( $list ) == 1 ? __( ' from brand ', 'compare' ) : __( ' from brands ', 'compare' ) ) . join( ', ', $list );
					} else {
						$term = get_term_by( 'slug', $product_brand, 'product-brand' );
						$breadcrumbs .= __( ' from brand ', 'compare' ) . $term->name;
					}
				}
				if ( ! empty( $keyword ) ) {
					$breadcrumbs .= __( ' for', 'compare' ) . '"' . $keyword . '"';
				}
				$breadcrumbs .= '</li>';
			} else if ( is_singular( 'post' ) ) {
				$breadcrumbs .= '<li><a href="' . get_permalink( get_option( 'page_for_posts' ) ) . '">' . get_the_title( get_option( 'page_for_posts' ) ) . '</a></li>';
				$category = get_the_category();
				if ( $category[0] ) {
					$breadcrumbs .= '<li><a href="' . get_category_link( $category[0]->term_id ) . '">' . $category[0]->cat_name . '</a></li>';
				}
				$breadcrumbs .= '<li>' . get_the_title() . '</li>';
			} else if ( is_singular( 'product' ) ) {
				$terms = compare_get_object_terms_hierarchical( get_the_ID(), 'product-cat' );
				if ( ! empty( $terms ) ) {
					$breadcrumbs .= compare_list_categories_li( $terms );
				}
				$breadcrumbs .= '<li>' . get_the_title() . '</li>';
			} else {
				$breadcrumbs .= '<li>' . get_the_title() . '</li>';
			}
			$breadcrumbs .= '</ul>';
		}

		return $breadcrumbs;
	}
}

/*
Generate hierarchy of the categories
for the breadcrumbs
*/
if ( ! function_exists( 'compare_get_object_terms_hierarchical' ) ) {
	function compare_get_object_terms_hierarchical( $object_ids, $taxonomies, $args = array() ) {

		$tree  = array();
		$terms = wp_get_object_terms( $object_ids, $taxonomies, $args );

		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				if ( $term->parent == 0 ) {
					$tree[ $term->term_id ]           = $term;
					$tree[ $term->term_id ]->children = compare_get_child_terms( $term->term_id, $terms );
				}
			}
		}

		return $tree;
	}
}

/*
Generate children for the
compare_get_object_terms_hierarchical() function
*/
if ( ! function_exists( 'compare_get_child_terms' ) ) {
	function compare_get_child_terms( $parent_id, $terms ) {
		$children = array();
		foreach ( $terms as $term ) {
			if ( $term->parent == $parent_id ) {
				$children[ $term->term_id ]           = $term;
				$children[ $term->term_id ]->children = compare_get_child_terms( $term->term_id, $terms );
			}
		}

		return $children;

	}
}

/*
Create list of the categories with links for the breadcrumbs
*/
if ( ! function_exists( 'compare_list_categories_li' ) ) {
	function compare_list_categories_li( $terms ) {
		$list = '';
		global $compare_slugs;
		foreach ( $terms as $term ) {
			$list .= '<li><a href="' . esc_url( compare_add_query_arg( array( $compare_slugs['product-cat'] => $term->slug ), compare_get_permalink_by_tpl( 'page-tpl_search' ) ) ) . '">' . $term->name . '</a></li>';
			if ( ! empty( $term->children ) ) {
				$list .= compare_list_categories_li( $term->children );
			}
		}

		return $list;
	}
}


/*
Check if review is populated
if we are on the single product page
*/
if ( ! function_exists( 'compare_verify_comment_meta_data' ) ) {
	function compare_verify_comment_meta_data( $commentdata ) {
		if ( empty( $_POST['review'] ) ) {
			wp_die( __( 'Error: please fill the required field (review).', 'compare' ) );
		}

		return $commentdata;
	}
}

add_filter( 'preprocess_comment', 'compare_verify_comment_meta_data' );

/*
Save review values and update rating
*/
if ( ! function_exists( 'compare_save_comment_meta_data' ) ) {
	function compare_save_comment_meta_data( $comment_id ) {
		if ( $_POST['review'] !== '-1' ) {
			add_comment_meta( $comment_id, 'review', $_POST['review'] );
			compare_calculate_average_rating( $_POST['post_id'] );
		}
	}
}

add_action( 'comment_post', 'compare_save_comment_meta_data' );

/*
When review comment is deleted
remove rating and recalculate ratings of the product
*/
if ( ! function_exists( 'compare_delete_comment' ) ) {
	function compare_delete_comment( $comment_id ) {
		delete_comment_meta( $comment_id, 'review' );
		$comment = get_comment( $comment_id );
		compare_calculate_average_rating( $comment->comment_post_ID );
	}
}

add_action( 'delete_comment', 'compare_delete_comment' );

/*
Calcualte rating of the product
*/
if ( ! function_exists( 'compare_calculate_average_rating' ) ) {
	function compare_calculate_average_rating( $post_id ) {
		global $wpdb;
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT AVG(commentmeta1.meta_value) AS rate, COUNT(commentmeta1.meta_value) AS counts FROM {$wpdb->commentmeta} AS commentmeta1 WHERE commentmeta1.meta_key = 'review' AND commentmeta1.comment_id IN ( SELECT comment_ID FROM {$wpdb->comments} WHERE comment_post_ID = %d )", $post_id ) );
		$result = array_shift( $result );
		update_post_meta( $post_id, 'average_review', round( $result->rate, 2 ) );
		update_post_meta( $post_id, 'review_count', $result->counts );
	}
}

/*
Retrieve rating HTMl of the product
*/
if ( ! function_exists( 'compare_get_ratings' ) ) {
	function compare_get_ratings( $post_id = '' ) {
		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}

		$count = get_post_meta( $post_id, 'review_count', true );
		if ( empty( $count ) ) {
			$count = '0';
		}
		compare_rating_display( get_post_meta( $post_id, 'average_review', true ), $count );
	}
}

/*
Create HTML for the rating dispaly
*/
if ( ! function_exists( 'compare_rating_display' ) ) {
	function compare_rating_display( $average, $count = '' ) {
		if ( empty( $average ) ) {
			$average = 0;
		}
		$stars = array();
		if ( $average < 0.5 ) {
			for ( $i = 0; $i < 5; $i ++ ) {
				$stars[] = '<i class="fa fa-star-o"></i>';
			}
		} else if ( $average < 1 ) {
			$stars[] = '<i class="fa fa-star"></i>';
			for ( $i = 0; $i < 5; $i ++ ) {
				$stars[] = '<i class="fa fa-star-o"></i>';
			}
		} else {
			$flag = false;
			for ( $i = 1; $i <= 5; $i += 0.5 ) {
				if ( $i <= $average ) {
					if ( floor( $i ) == $i ) {
						$stars[] = '<i class="fa fa-star"></i>';
					}
				} else {
					if ( ! $flag ) {
						if ( floor( $i ) == $i ) {
							$stars[] = '<i class="fa fa-star-half-o"></i>';
						}
						$flag = true;
					} else {
						if ( floor( $i ) == $i ) {
							$stars[] = '<i class="fa fa-star-o"></i>';
						}
					}
				}
			}
		}


		echo join( "", $stars );
		if ( ! empty( $count ) || $count == '0' ) {
			echo '<span>(' . $count . ')</span>';
		}

	}
}

/*
Sort custom taxonomies
*/
if ( ! function_exists( 'compare_sort_terms_hierarchicaly' ) ) {
	function compare_sort_terms_hierarchicaly( Array &$cats, Array &$into, $parentId = 0 ) {
		foreach ( $cats as $i => $cat ) {
			if ( $cat->parent == $parentId ) {
				$into[ $cat->term_id ] = $cat;
				unset( $cats[ $i ] );
			}
		}

		foreach ( $into as $topCat ) {
			$topCat->children = array();
			compare_sort_terms_hierarchicaly( $cats, $topCat->children, $topCat->term_id );
		}
	}
}

/*
Get organized list of custom taxonomy in form parent->child
for the front end listing
*/
if ( ! function_exists( 'compare_get_organized' ) ) {
	function compare_get_organized( $taxonomy ) {
		$categories         = get_terms( $taxonomy, array( 'hide_empty' => false ) );
		$taxonomy_organized = array();
		compare_sort_terms_hierarchicaly( $categories, $taxonomy_organized );
		$taxonomy_organized = (array) $taxonomy_organized;

		if ( $taxonomy == 'product-cat' ) {
			$sortby = compare_get_option( 'all_categories_sortby' );
			$sort   = compare_get_option( 'all_categories_sort' );
		} else if ( $taxonomy == 'product-brand' ) {
			$sortby = compare_get_option( 'all_brands_sortby' );
			$sort   = compare_get_option( 'all_brands_sort' );
		}


		if ( $sort == 'asc' ) {
			switch ( $sortby ) {
				case 'name' :
					usort( $taxonomy_organized, "compare_organized_sort_name_asc" );
					break;
				case 'slug' :
					usort( $taxonomy_organized, "compare_organized_sort_slug_asc" );
					break;
				case 'count' :
					usort( $taxonomy_organized, "compare_organized_sort_count_asc" );
					break;
				default :
					usort( $taxonomy_organized, "compare_organized_sort_name_asc" );
					break;
			}

		} else {
			switch ( $sortby ) {
				case 'name' :
					usort( $taxonomy_organized, "compare_organized_sort_name_desc" );
					break;
				case 'slug' :
					usort( $taxonomy_organized, "compare_organized_sort_slug_desc" );
					break;
				case 'count' :
					usort( $taxonomy_organized, "compare_organized_sort_count_desc" );
					break;
				default :
					usort( $taxonomy_organized, "compare_organized_sort_name_desc" );
					break;
			}
		}

		return $taxonomy_organized;

	}
}

/*
Sort taxonomy terms by name ASC
*/
if ( ! function_exists( 'compare_organized_sort_name_asc' ) ) {
	function compare_organized_sort_name_asc( $a, $b ) {
		return strcmp( $a->name, $b->name );
	}
}

/*
Sort taxonomy terms by name DESC
*/
if ( ! function_exists( 'compare_organized_sort_name_desc' ) ) {
	function compare_organized_sort_name_desc( $a, $b ) {
		return strcmp( $b->name, $a->name );
	}
}

/*
Sort taxonomy terms by slug ASC
*/
if ( ! function_exists( 'compare_organized_sort_slug_asc' ) ) {
	function compare_organized_sort_slug_asc( $a, $b ) {
		return strcmp( $a->slug, $b->slug );
	}
}

/*
Sort taxonomy terms by slug DESC
*/
if ( ! function_exists( 'compare_organized_sort_slug_desc' ) ) {
	function compare_organized_sort_slug_desc( $a, $b ) {
		return strcmp( $b->slug, $a->slug );
	}
}

/*
Sort taxonomy terms by count ASC
*/
if ( ! function_exists( 'compare_organized_sort_count_asc' ) ) {
	function compare_organized_sort_count_asc( $a, $b ) {
		return strcmp( $a->count, $b->count );
	}
}

/*
Sort taxonomy terms by count DESC
*/
if ( ! function_exists( 'compare_organized_sort_count_desc' ) ) {
	function compare_organized_sort_count_desc( $a, $b ) {
		return strcmp( $b->count, $a->count );
	}
}

/*
Generate select box with nested tree
*/
if ( ! function_exists( 'compare_display_select_tree' ) ) {
	function compare_display_select_tree( $cat, $selected = '', $level = 0 ) {
		if ( ! empty( $cat->children ) ) {
			echo '<option value="" disabled>' . str_repeat( '&nbsp;&nbsp;', $level ) . '' . $cat->name . '</option>';
			$level ++;
			foreach ( $cat->children as $key => $child ) {
				compare_display_select_tree( $child, $selected, $level );
			}
		} else {
			echo '<option value="' . $cat->term_id . '" ' . ( $cat->term_id == $selected ? 'selected="selected"' : '' ) . '>' . str_repeat( '&nbsp;&nbsp;', $level ) . '' . $cat->name . '</option>';
		}
	}
}


/*
Display custom taxonomy on their listing pages
All Categories, All Brands
*/
if ( ! function_exists( 'compare_display_tree' ) ) {
	function compare_display_tree( $cat, $taxonomy ) {
		global $compare_slugs;
		echo '<ul class="list-unstyled">';
		foreach ( $cat->children as $key => $child ) {
			echo '<li>
				<a href="' . esc_url( compare_add_query_arg( array( $compare_slugs[ $taxonomy ] => $child->slug ), compare_get_permalink_by_tpl( 'page-tpl_search' ) ) ) . '">' . $child->name . '</a>
				<span class="count">' . $child->count . '</span>';
			if ( ! empty( $child->children ) ) {
				compare_display_tree( $child, $taxonomy );
			}
			echo '</li>';
		}
		echo '</ul>';

	}
}

/*
Lilst categories on the search page
left sidebar
*/
if ( ! function_exists( 'compare_list_filter_categories' ) ) {
	function compare_list_filter_categories( $ancestors = array(), $parent = 0 ) {
		$categories = get_terms( 'product-cat', array(
			'hide_empty' => false,
			'parent'     => $parent
		) );

		global $compare_slugs;

		$search_categories_visible = compare_get_option( 'search_categories_visible' );
		$counter                   = 0;

		if ( ! empty( $categories ) ) {
			foreach ( $categories as $category ) {
				$counter ++;
				echo '<li class="' . ( ! empty( $search_categories_visible ) && $counter > $search_categories_visible ? 'hidden' : '' ) . '">';
				$checked = '';
				if ( ! empty( $ancestors ) && in_array( $category->term_id, $ancestors ) ) {
					$checked = 'checked="checked"';
				}
				echo '
			<div class="checkbox checkbox-inline">
			    <input type="checkbox" id="product-cat-' . esc_attr( $category->slug ) . '" name="' . esc_attr( $compare_slugs['product-cat'] ) . '[]" value="' . esc_attr( $category->slug ) . '" ' . $checked . '>
			    <label for="product-cat-' . esc_attr( $category->slug ) . '">
			        ' . $category->name . '
			    </label>
			</div>
			';
				if ( in_array( $category->term_id, $ancestors ) ) {
					$children = get_terms( 'product-cat', array(
						'parent'     => $category->term_id,
						'hide_empty' => false,
					) );
					echo '<ul class="list-unstyled">';
					compare_display_category_children( $children, $ancestors );
					echo '</ul>';
				}
				echo '</li>';
			}
		}
	}
}

/*
Dispaly children of the selected category
on search page left sidebar
*/
if ( ! function_exists( 'compare_display_category_children' ) ) {
	function compare_display_category_children( $children, $ancestors ) {
		global $compare_slugs;
		if ( ! empty( $children ) ) {
			foreach ( $children as $term ) {
				echo '<li>';
				$checked = '';
				if ( ! empty( $ancestors ) && in_array( $term->term_id, $ancestors ) ) {
					$checked = 'checked="checked"';
				}
				echo '
			<div class="checkbox checkbox-inline">
			    <input type="checkbox" id="product-cat-' . esc_attr( $term->slug ) . '" name="' . esc_attr( $compare_slugs['product-cat'] ) . '[]" value="' . esc_attr( $term->slug ) . '" ' . $checked . '>
			    <label for="product-cat-' . esc_attr( $term->slug ) . '">
			        ' . $term->name . '
			    </label>
			</div>
			';
				if ( in_array( $term->term_id, $ancestors ) ) {
					$children = get_terms( 'product-cat', array( 'parent' => $term->term_id, 'hide_empty' => false, ) );
					echo '<ul class="list-unstyled">';
					compare_display_category_children( $children, $ancestors );
					echo '</ul>';
				}
				echo '</li>';
			}
		}
	}
}

/*
Display list of the brands
on search page left sidebar
*/
if ( ! function_exists( 'compare_list_filter_brands' ) ) {
	function compare_list_filter_brands() {
		$brands = get_terms( 'product-brand', array(
			'hide_empty' => false,
			'parent'     => 0
		) );

		global $compare_slugs;

		if ( ! empty( $brands ) ) {
			$search_brands_visible = compare_get_option( 'search_brands_visible' );
			$counter               = 0;
			foreach ( $brands as $brand ) {
				$counter ++;
				echo '<li class="' . ( ! empty( $search_brands_visible ) && $counter > $search_brands_visible ? 'hidden' : '' ) . '">';
				$checked       = '';
				$product_brand = get_query_var( $compare_slugs['product-brand'], '' );
				if ( ! empty( $product_brand ) && in_array( $brand->slug, (array) $product_brand ) ) {
					$checked = 'checked="checked"';
				}
				echo '
			<div class="checkbox checkbox-inline">
			    <input type="checkbox" id="product-brand-' . esc_attr( $brand->slug ) . '" name="' . esc_attr( $compare_slugs['product-brand'] ) . '[]" value="' . esc_attr( $brand->slug ) . '" ' . $checked . '>
			    <label for="product-brand-' . esc_attr( $brand->slug ) . '">
			        ' . $brand->name . '
			    </label>
			</div>
			';
				echo '</li>';
			}
		}
	}
}

/*
Escape array of ids in order
to be used in the SQL query
*/
if ( ! function_exists( 'compare_escape_array' ) ) {
	function compare_escape_array( $ids ) {
		global $wpdb;
		$escaped = array();
		foreach ( $ids as $id ) {
			$escaped[] = $wpdb->prepare( '%d', $id );
		}

		return implode( ',', $escaped );
	}
}

/*
Grab meta values for the currently dispalyed
products on shortcode or search page
*/
if ( ! function_exists( 'compare_product_item_meta' ) ) {
	function compare_product_item_meta( $ids ) {
		global $wpdb;
		$meta_data = array();

		if ( ! empty( $ids ) ) {
			$results = $wpdb->get_results( "SELECT COUNT(*) as stores, MIN(price) as minPrice, post_id FROM {$wpdb->prefix}feed_list WHERE post_id IN ( " . compare_escape_array( $ids ) . " ) GROUP BY post_id" );
			if ( ! empty( $results ) ) {
				foreach ( $results as $result ) {
					$meta_data[ $result->post_id ] = array(
						'stores'   => $result->stores,
						'minPrice' => $result->minPrice,
					);
				}
			}
		}

		return $meta_data;
	}
}

/*
Register click on the product single page
*/
if ( ! function_exists( 'compare_register_click' ) ) {
	function compare_register_click( $post_id = '' ) {
		if ( empty( $post_id ) ) {
			$post_id = $_POST['post_id'];
		}
		if ( ! empty( $post_id ) ) {
			$clicks = get_post_meta( $post_id, 'product_clicks', true );
			if ( ! empty( $clicks ) ) {
				$clicks = $clicks + 1;
			} else {
				$clicks = 1;
			}
			update_post_meta( $post_id, 'product_clicks', $clicks );
		}
	}
}

add_action( 'wp_ajax_register_click', 'compare_register_click' );
add_action( 'wp_ajax_nopriv_register_click', 'compare_register_click' );

/*
If user clicked on the store link from product single
register that click and send user to store
*/
if ( ! function_exists( 'compare_redirect_product' ) ) {
	function compare_redirect_product() {
		if ( isset( $_GET['post_id'] ) && isset( $_GET['store_id'] ) ) {
			global $wpdb;
			$post_id  = $_GET['post_id'];
			$store_id = $_GET['store_id'];

			$store = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stores WHERE store_id = %d", $store_id ) );

			$store = array_shift( $store );
			if ( ! empty( $store ) ) {

				/* INCREASE STORE CLICKS */
				$clicks = get_post_meta( $store_id, 'product_store_clicks', true );
				if ( ! empty( $clicks ) ) {
					$clicks = $clicks + 1;
				} else {
					$clicks = 1;
				}
				update_post_meta( $store_id, 'product_store_clicks', $clicks );

				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}stores SET store_clicks = %d WHERE store_id = %d", $clicks, $store_id ) );

				/* CHECK PRODUCT URL ELSE CHECK FOR STORE URL ELSE REDIRECT BACK TO */
				$feed = $wpdb->get_results( $wpdb->prepare( "SELECT product_link FROM {$wpdb->prefix}feed_list WHERE post_id = %d AND store_id = %d", $post_id, $store_id ) );
				$feed = array_shift( $feed );
				if ( ! empty( $feed ) && ! empty( $feed->product_link ) ) {
					$link = $feed->product_link;
				} else if ( ! empty( $store->store_url ) ) {
					$link = $store->store_url;
				} else {
					$link = get_the_permalink( $post_id );
				}

			} else {
				$link = get_the_permalink( $post_id );
			}
			if ( stristr( $link, 'http' ) == false ) {
				$link = get_the_permalink( $post_id );
			}
			wp_redirect( $link );
		}
	}
}

add_action( 'template_redirect', 'compare_redirect_product', 0 );

/*
Add meta box on product edit page
to manipulate with the feeds
*/
if ( ! function_exists( 'compare_product_stores' ) ) {
	function compare_product_stores() {
		add_meta_box( 'compare_product_stores', __( 'Manage Stores', 'compare' ), 'compare_products_stores_box_populate', 'product' );
	}
}

add_action( 'add_meta_boxes', 'compare_product_stores' );

/*
Create form for the Manage Store box on product edit apge
*/
if ( ! function_exists( 'compare_product_stores_form' ) ) {
	function compare_product_stores_form( $feed = '', $post_id = '' ) {
		global $wpdb;
		$stores         = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}stores" );
		$stores_options = '';
		if ( ! empty( $stores ) ) {
			foreach ( $stores as $store ) {
				$stores_options .= '<option value="' . esc_attr( $store->store_id ) . '" ' . ( ! empty( $feed ) && $feed->store_id == $store->store_id ? 'selected="selected"' : '' ) . '>' . $store->store_name . '</option>';
			}
		}
		?>
		<div class="store-feed-form" style="display: none;">
			<a href="javascript:;" class="close-store-feed-form">X</a>
			<div class="form-group">
				<label for="store_id"><?php _e( 'Select Store', 'compare' ) ?></label>
				<select name="store_id" id="store_id">
					<?php echo $stores_options ?>
				</select>
			</div>

			<input type="hidden" name="feed_post_id" value="<?php if ( ! empty( $post_id ) ) {
				echo esc_attr( $post_id );
			} else {
				echo ! empty( $feed ) ? $feed->post_id : $_GET['post'];
			} ?>">

			<?php if ( ! empty( $feed ) ): ?>
				<input type="hidden" name="feed_id" value="<?php echo esc_attr( $feed->feed_id ) ?>">
			<?php endif; ?>

			<div class="form-group">
				<label for="price"><?php _e( 'Input Price ( just number )', 'compare' ) ?></label>
				<input type="text" name="price" id="price"
				       value="<?php echo( ! empty( $feed ) ? $feed->price : '' ) ?>">
			</div>

			<div class="form-group">
				<label
					for="product_link"><?php _e( 'Input Link To Product Or Leave empty For Store URL', 'compare' ) ?></label>
				<input type="text" name="product_link" id="product_link"
				       value="<?php echo( ! empty( $feed ) ? $feed->product_link : '' ) ?>">
			</div>

			<div class="form-group">
				<label for="shipping"><?php _e( 'Input Shipping Fee (just number)', 'compare' ) ?></label>
				<input type="text" name="shipping" id="shipping"
				       value="<?php echo( ! empty( $feed ) ? $feed->shipping : '' ) ?>">
			</div>

			<div class="form-group">
				<label for="shipping_comment"><?php _e( 'Input Shipping Comment', 'compare' ) ?></label>
				<input type="text" name="shipping_comment" id="shipping_comment"
				       value="<?php echo( ! empty( $feed ) ? $feed->shipping_comment : '' ) ?>">
			</div>

			<a href="javascript:;" class="button save-feed" data-save="<?php esc_attr_e( 'Save', 'compare' ) ?>">
				<?php echo ! empty( $feed ) ? __( 'Update', 'compare' ) : __( 'Save', 'compare' ); ?>
			</a>

		</div>
		<?php
	}
}

/*
Populate Manage Store box
*/
if ( ! function_exists( 'compare_prodcut_stores_box_populate' ) ) {
	function compare_products_stores_box_populate() {
		if ( isset( $_GET['action'] ) && $_GET['action'] == 'edit' ) {
			global $wpdb;
			include( locate_template( 'includes/views/price-list-core.php' ) );
			compare_product_stores_form();
		} else {
			_e( 'Options will be visible once you create post', 'compare' );
		}
	}
}

/*
Add new feed from the product edit page
*/
if ( ! function_exists( 'compare_products_stores_add' ) ) {
	function compare_products_stores_add() {
		global $wpdb;

		$feed_id          = ! empty( $_POST['feed_id'] ) ? esc_sql( $_POST['feed_id'] ) : '';
		$store_id         = ! empty( $_POST['store_id'] ) ? esc_sql( $_POST['store_id'] ) : '';
		$post_id          = ! empty( $_POST['feed_post_id'] ) ? esc_sql( $_POST['feed_post_id'] ) : '';
		$price            = ! empty( $_POST['price'] ) ? esc_sql( $_POST['price'] ) : '';
		$product_link     = ! empty( $_POST['product_link'] ) ? esc_sql( $_POST['product_link'] ) : '';
		$shipping         = ! empty( $_POST['shipping'] ) ? esc_sql( $_POST['shipping'] ) : '';
		$shipping_comment = ! empty( $_POST['shipping_comment'] ) ? esc_sql( $_POST['shipping_comment'] ) : '';
		$time             = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );


		if ( ! empty( $store_id ) && ! empty( $price ) ) {
			if ( ! empty( $feed_id ) ) {
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}feed_list SET post_id = %d, store_id = %d, price = %f, product_link = %s, shipping = %f, shipping_comment = %s, time = %s WHERE feed_id = %d", $post_id, $store_id, $price, $product_link, $shipping, $shipping_comment, $time, $feed_id ) );
				compare_product_stores_form( $feed );
			} else {
				$info = $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}feed_list VALUES ( '', %d, %d, %f, %s, %f, %s, %s )", $post_id, $store_id, $price, $product_link, $shipping, $shipping_comment, $time ) );

				compare_product_stores_form( '', $post_id );
			}
		}

		include( locate_template( 'includes/views/price-list-core.php' ) );

		die();
	}
}

add_action( 'wp_ajax_add_product_store', 'compare_products_stores_add' );

/*
Update feed from the product edit page
*/
if ( ! function_exists( 'compare_products_stores_update' ) ) {
	function compare_products_stores_update() {
		global $wpdb;
		$feed_id = ! empty( $_POST['feed_id'] ) ? esc_sql( $_POST['feed_id'] ) : '';
		$feed    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}feed_list WHERE feed_id = %d", $feed_id ) );
		if ( ! empty( $feed ) ) {
			$post_id = $feed[0]->post_id;
			include( locate_template( 'includes/views/price-list-core.php' ) );
			compare_product_stores_form( $feed[0] );
		}

		die();
	}
}

add_action( 'wp_ajax_update_product_store', 'compare_products_stores_update' );

/*
Delete feed from the product edit page
*/
if ( ! function_exists( 'compare_products_stores_delete' ) ) {
	function compare_products_stores_delete() {
		global $wpdb;
		$feed_id = ! empty( $_POST['feed_id'] ) ? esc_sql( $_POST['feed_id'] ) : '';
		$post_id = ! empty( $_POST['post_id'] ) ? esc_sql( $_POST['post_id'] ) : '';

		if ( ! empty( $feed_id ) ) {
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}feed_list WHERE feed_id = %d", $feed_id ) );
		}

		include( locate_template( 'includes/views/price-list-core.php' ) );
		compare_product_stores_form();
		die();
	}
}

add_action( 'wp_ajax_delete_product_store', 'compare_products_stores_delete' );

/*
List of the available packages on the Register Store page
*/
if ( ! function_exists( 'compare_list_packages' ) ) {
	function compare_list_packages( $selected = '' ) {
		$options = '';
		if ( empty( $selected ) ) {
			$selected = isset( $_GET['package'] ) ? str_replace( '-', '|', $_GET['package'] ) : '';
		}
		$packages = compare_get_option( 'packages' );
		if ( ! empty( $packages ) ) {
			$packages = explode( PHP_EOL, $packages );
			foreach ( $packages as $package ) {
				$package = trim( preg_replace( '/\s+/', ' ', $package ) );
				$temp    = explode( "|", $package );
				$options .= '<option value="' . $temp[1] . '|' . $temp[2] . '" ' . ( $selected == $temp[1] . '|' . $temp[2] ? 'selected="selected"' : '' ) . '>' . $temp[0] . ' ( ' . compare_format_currency_number( $temp[1] ) . __( ' & ', 'compare' ) . $temp[2] . __( ' days', 'compare' ) . ' )</option>';
			}
		}

		return $options;
	}
}

/*
register store from the Register Store page
*/
if ( ! function_exists( 'compare_register_store' ) ) {
	function compare_register_store() {
		$errors    = array();
		$fields    = array();
		$validated = true;
		global $wpdb;
		$store_name          = isset( $_POST['store_name'] ) ? esc_sql( $_POST['store_name'] ) : '';
		$store_url           = isset( $_POST['store_url'] ) ? esc_sql( $_POST['store_url'] ) : '';
		$store_contact_name  = isset( $_POST['store_contact_name'] ) ? esc_sql( $_POST['store_contact_name'] ) : '';
		$store_contact_phone = isset( $_POST['store_contact_phone'] ) ? esc_sql( $_POST['store_contact_phone'] ) : '';
		$store_contact_email = isset( $_POST['store_contact_email'] ) ? esc_sql( $_POST['store_contact_email'] ) : '';
		$store_xml_feed      = isset( $_POST['store_xml_feed'] ) ? esc_sql( $_POST['store_xml_feed'] ) : '';
		$store_logo          = isset( $_POST['store_logo'] ) ? esc_sql( $_POST['store_logo'] ) : '';
		$store_package       = isset( $_POST['store_package'] ) ? esc_sql( $_POST['store_package'] ) : '';
		if ( isset( $_POST['captcha'] ) ) {
			$store_logo = compare_import_store_logo( $store_logo );
			if ( empty( $store_name ) ) {
				$fields[] = 'store_name';
			}
			if ( empty( $store_url ) ) {
				$fields[] = 'store_url';
			}
			if ( empty( $store_contact_name ) ) {
				$fields[] = 'store_contact_name';
			}
			if ( empty( $store_contact_phone ) ) {
				$fields[] = 'store_contact_phone';
			}
			if ( ! filter_var( $store_contact_email, FILTER_VALIDATE_EMAIL ) ) {
				$fields[] = 'store_contact_email';
			}
			if ( empty( $store_xml_feed ) ) {
				$fields[] = 'store_xml_feed';
			}
			if ( empty( $store_logo ) ) {
				$fields[] = 'store_logo';
			}
			if ( empty( $store_package ) ) {
				$fields[] = 'store_package';
			}

			if ( ! empty( $fields ) ) {
				$validated = false;
			}

			if ( $validated ) {
				$store_slug = sanitize_title( $store_name );
				$if_exists  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stores WHERE store_slug = %s", $store_slug ) );
				if ( empty( $if_exists ) ) {
					$store_status      = '0';
					$store_expire_time = '';
					$temp              = explode( '|', $store_package );
					$price             = $temp[0];
					$days              = $temp[1];
					if ( empty( $price ) ) {
						$store_status = '1';
					}
					if ( $days !== '0' ) {
						$store_expire_time = current_time( 'timestamp' ) + $days * 24 * 60 * 60;
					}
					$wpdb->insert( $wpdb->prefix . 'stores', array(
						'store_slug'          => $store_slug,
						'store_logo'          => $store_logo,
						'store_url'           => $store_url,
						'store_name'          => $store_name,
						'store_clicks'        => 0,
						'store_contact_name'  => $store_contact_name,
						'store_contact_email' => $store_contact_email,
						'store_contact_phone' => $store_contact_phone,
						'store_package'       => $store_package,
						'store_expire_time'   => $store_expire_time,
						'store_xml_feed'      => $store_xml_feed,
						'store_status'        => $store_status,
						'store_update'        => ''
					), array(
						'%s',
						'%s',
						'%s',
						'%s',
						'%d',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
					) );

					if ( $store_status == '0' ) {
						echo json_encode( array(
							'info'     => __( 'Store is saved. In order to be available you need to pay the submission using one of the available methods bellow.', 'compare' ),
							'payments' => compare_create_payments( $wpdb->insert_id, $price )
						) );
					} else {
						echo json_encode( array(
							'success' => __( 'Store is registered. Thank you.', 'compare' ),
						) );
					}
					die();
				} else {
					echo json_encode( array(
						'error' => __( 'Store is already registered.', 'compare' ),
					) );
					die();
				}
			} else {
				echo json_encode( array(
					'error'  => __( 'All fields are required.', 'compare' ),
					'fields' => $fields
				) );
				die();
			}
		} else {
			echo json_encode( array(
				'error' => __( 'Captcha is wrong.', 'compare' ),
			) );
			die();
		}
	}
}

add_action( 'wp_ajax_register_store', 'compare_register_store' );
add_action( 'wp_ajax_nopriv_register_store', 'compare_register_store' );

/*
Update store from the Register Store page
if user arrived to prolongate its store
*/
if ( ! function_exists( 'compare_update_store' ) ) {
	function compare_update_store() {
		if ( isset( $_POST['captcha'] ) ) {
			$store_id      = isset( $_POST['store_id'] ) ? esc_sql( $_POST['store_id'] ) : '';
			$store_package = isset( $_POST['store_package'] ) ? esc_sql( $_POST['store_package'] ) : '';
			if ( ! empty( $store_id ) && ! empty( $store_package ) ) {
				global $wpdb;
				$store = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stores WHERE store_id = %s", $store_id ) );
				$store = array_shift( $store );
				if ( ! empty( $store ) ) {
					$temp  = explode( '|', $store_package );
					$price = $temp[1];
					$days  = $temp[0];

					/* make sure that on time it is added the max amount of 3 days */
					$remain = $store->store_expire_time - current_time( 'timestamp' );
					if ( $remain < 0 ) {
						$remain = 0;
					}

					if ( $price == '0' ) {
						$store_status = '1';
					}
					$store_expire_time = '';
					if ( $days !== '0' ) {
						$store_expire_time = $remain + current_time( 'timestamp' ) + $days * 24 * 60 * 60;
					}

					$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}stores SET store_package = %s, store_expire_time = %s, store_update = '' WHERE store_id = %d", $store_package, $store_expire_time, $store_id ) );

					echo json_encode( array(
						'info'     => __( 'Store is saved. In order to be available you need to pay the submittion using one of the available methods bellow.', 'compare' ),
						'payments' => compare_create_payments( $store_id, $price )
					) );
				} else {
					echo json_encode( array(
						'error' => __( 'Wrong store ID.', 'compare' ),
					) );
				}
			} else {
				echo json_encode( array(
					'error' => __( 'All fields are required.', 'compare' ),
				) );
			}
		} else {
			echo json_encode( array(
				'error' => __( 'Captcha is wrong.', 'compare' ),
			) );
		}
		die();
	}
}

add_action( 'wp_ajax_update_store', 'compare_update_store' );
add_action( 'wp_ajax_nopriv_update_store', 'compare_update_store' );


/*
Create list of payments for the Register Store page
*/
if ( ! function_exists( 'compare_create_payments' ) ) {
	function compare_create_payments( $store_id, $amount ) {
		$payments  = '';
		$permalink = compare_get_permalink_by_tpl( 'page-tpl_register_store' );
		$currency  = compare_get_option( 'main_unit_abbr' );
		/* CHECK IF PAYPAL PAYMENT IS AVAILABLE */
		$paypal_username = compare_get_option( 'paypal_username' );
		if ( ! empty( $paypal_username ) ) {
			$paypal = new Compare_PayPal( array(
				'username'  => $paypal_username,
				'password'  => compare_get_option( 'paypal_password' ),
				'signature' => compare_get_option( 'paypal_signature' ),
				'cancelUrl' => add_query_arg( array( 'cancel' => 'yes', 'store_id' => $store_id ), $permalink ),
				'returnUrl' => add_query_arg( array( 'payment' => 'paypal', 'store_id' => $store_id ), $permalink ),
			) );

			$pdata = array(
				'PAYMENTREQUEST_0_PAYMENTACTION' => "SALE",
				'L_PAYMENTREQUEST_0_NAME0'       => __( 'Store XML Submition', 'compare' ),
				'L_PAYMENTREQUEST_0_NUMBER0'     => uniqid(),
				'L_PAYMENTREQUEST_0_DESC0'       => __( 'Payment for displaying products via feed.', 'compare' ),
				'L_PAYMENTREQUEST_0_AMT0'        => $amount,
				'L_PAYMENTREQUEST_0_QTY0'        => 1,
				'NOSHIPPING'                     => 1,
				'PAYMENTREQUEST_0_CURRENCYCODE'  => $currency,
				'PAYMENTREQUEST_0_AMT'           => $amount
			);

			$response = $paypal->SetExpressCheckout( $pdata );
			if ( ! isset( $response['error'] ) ) {
				$payments .= '<a href="' . esc_url( $response['url'] ) . '"><img src="' . get_template_directory_uri() . '/images/paypal.png" alt="" /></a>';
			}
		}

		/* CHECK IF STRIPE PAYMENT IS AVAILABLE */
		$stripe_pk_client_id = compare_get_option( 'stripe_pk_client_id' );
		if ( ! empty( $stripe_pk_client_id ) ) {
			$site_logo = compare_get_option( 'site_logo' );
			$logo_link = '';
			if ( ! empty( $site_logo['url'] ) ) {
				$logo_link = $site_logo['url'];
			}
			$stripe_amount = $amount * 100;

			$payments .= '<a href="javascript:;" class="stripe-payment" data-genearting_string="' . esc_attr__( 'Processing...', 'compare' ) . '" data-pk="' . esc_attr( $stripe_pk_client_id ) . '" data-store_id="' . esc_attr( $store_id ) . '" data-image="' . esc_url( $logo_link ) . '" data-name="' . esc_attr__( 'Store XML Submition', 'compare' ) . '" data-description="' . esc_attr__( 'Payment for displaying products via feed.', 'compare' ) . '" data-amount="' . esc_attr( $stripe_amount ) . '" data-currency="' . esc_attr( $currency ) . '">
			<img src="' . get_template_directory_uri() . '/images/stripe.png" alt="" />
		</a>';
		}
		/* CHECK IF SKRILL PAYMENT IS AVAILABLE */
		$skrill_owner_mail = compare_get_option( 'skrill_owner_mail' );
		if ( ! empty( $skrill_owner_mail ) ) {
			$payments .= '
			<a href="javascript:;" class="skrill-payment">
				<img src="' . get_template_directory_uri() . '/images/skrill.png" alt="" />
			</a>
			<form class="hidden skrill-form" action="https://www.moneybookers.com/app/payment.pl" method="post">
				<input type="hidden" name="pay_to_email" value="' . esc_attr( $skrill_owner_mail ) . '"/>
				<input type="hidden" name="status_url" value="' . add_query_arg( array(
					'payment'  => 'skrill-verify',
					'store_id' => $store_id
				), $permalink ) . '"/> 
				<input type="hidden" name="language" value="EN"/>
				<input type="hidden" name="return_url" value="' . add_query_arg( array(
					'payment'  => 'skrill',
					'store_id' => $store_id
				), $permalink ) . '"/>
				<input type="hidden" name="amount" value="' . esc_attr( $amount ) . '"/>
				<input type="hidden" name="currency" value="' . esc_attr( $currency ) . '"/>
				<input type="hidden" name="detail1_text " value="' . esc_attr__( 'Store XML Submition', 'compare' ) . '"/>	
			</form>
		';
		}

		/* CHECK IF BANK TRANSFER PAYMENT IS AVAILABLE */
		$bank_name = compare_get_option( 'bank_name' );
		if ( ! empty( $bank_name ) ) {
			$payments .= '
			<a href="' . esc_url( add_query_arg( array( 'payment' => 'bank' ), $permalink ) ) . '">
				<img src="' . get_template_directory_uri() . '/images/bank.png" alt="" />
			</a>';
		}

		/* CHECK IF IDEAL IS AVAILABLE */
		$mollie_id = compare_get_option( 'mollie_id' );
		if ( ! empty( $mollie_id ) ) {
			$iDEAL = new Compare_Mollie_iDEAL_Payment ( $mollie_id );

			$bank_array = $iDEAL->getBanks();
			if ( $bank_array ) {
				$payments .= '<a href="javascript:;" class="submit-ideal-payment"><img src="' . get_template_directory_uri() . '/images/ideal.png" alt="" /></a>';
				$payments .= '<form method="post" class="ideal-payment">
				<select name="bank_id">
					<option value="">' . __( 'Select Your Bank', 'compare' ) . '</option>';
				foreach ( $bank_array as $bank_id => $bank_name ) {
					$payments .= '<option value="' . esc_attr( $bank_id ) . '">' . $bank_name . '</option>';
				}
				$payments .= '<input type="hidden" name="store_id" value="' . esc_attr( $store_id ) . '"><input type="hidden" name="action" value="ideal_link"></select></form>';
			}
		}

		return $payments;

	}
}

/*
Listen for the payment gateways returns
*/
if ( ! function_exists( 'compare_payment_result' ) ) {
	function compare_payment_result() {
		$payment = '';
		if ( isset( $_GET['payment'] ) ) {
			$payment = $_GET['payment'];
		} else if ( isset( $_POST['payment'] ) ) {
			$payment = $_POST['payment'];
		}
		switch ( $payment ) {
			case 'paypal' :
				$response = compare_pay_with_paypal();
				break;
			case 'skrill' :
				$response = compare_pay_with_skrill();
				break;
			case 'skrill-verify' :
				$response = compare_skrill_payment_confirmation();
				break;
			case 'bank' :
				$response = compare_pay_with_bank();
				break;
			case 'ideal' :
				$response = compare_pay_with_ideal();
				break;
			case 'ideal-verify' :
				$response = compare_ideal_payment_confirmation();
				break;
			default :
				$response = '';
		}

		return $response;
	}
}

/*
Check if store exists for the payments
*/
if ( ! function_exists( 'compare_check_if_store_exists' ) ) {
	function compare_check_if_store_exists( $store_id ) {
		global $wpdb;
		$store = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stores WHERE store_id = %d", $store_id ) );
		$store = array_shift( $store );
		if ( ! empty( $store ) ) {
			$price  = explode( '|', $store->store_package );
			$amount = $price[1];

			return $amount;
		}

		return false;
	}
}

/*
Mark store as paid after successfull
payment check
*/
if ( ! function_exists( 'compare_mark_store_as_paid' ) ) {
	function compare_mark_store_as_paid( $store_id ) {
		/* first check if it is update store_update flag will not be empty */
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}stores SET store_status = '1' WHERE store_id = %d", $store_id ) );
	}
}

/*
Do PayPal payment processing
*/
if ( ! function_exists( 'compare_pay_with_paypal' ) ) {
	function compare_pay_with_paypal() {
		if ( isset( $_GET['store_id'] ) ) {
			$store_id = $_GET['store_id'];
			$amount   = compare_check_if_store_exists( $store_id );

			if ( $amount ) {
				$paypal = new Compare_PayPal( array(
					'username'  => compare_get_option( 'paypal_username' ),
					'password'  => compare_get_option( 'paypal_password' ),
					'signature' => compare_get_option( 'paypal_signature' ),
					'cancelUrl' => add_query_arg( array(
						'cancel'   => 'yes',
						'store_id' => $store_id
					), compare_get_permalink_by_tpl( 'page-tpl_register_store' ) ),
					'returnUrl' => add_query_arg( array(
						'payment'  => 'paypal',
						'store_id' => $store_id
					), compare_get_permalink_by_tpl( 'page-tpl_register_store' ) ),
				) );

				$pdata = array(
					'TOKEN'                          => $_GET['token'],
					'PAYERID'                        => $_GET['PayerID'],
					'PAYMENTREQUEST_0_PAYMENTACTION' => "SALE",
					'L_PAYMENTREQUEST_0_NAME0'       => __( 'Store XML Submition', 'compare' ),
					'L_PAYMENTREQUEST_0_NUMBER0'     => uniqid(),
					'L_PAYMENTREQUEST_0_DESC0'       => __( 'Payment for displaying products via feed.', 'compare' ),
					'L_PAYMENTREQUEST_0_AMT0'        => $amount,
					'L_PAYMENTREQUEST_0_QTY0'        => 1,
					'NOSHIPPING'                     => 1,
					'PAYMENTREQUEST_0_CURRENCYCODE'  => compare_get_option( 'main_unit_abbr' ),
					'PAYMENTREQUEST_0_AMT'           => $amount
				);

				$response = $paypal->DoExpressCheckoutPayment( $pdata );
				if ( ! isset( $response['error'] ) && ! isset( $response['L_ERRORCODE0'] ) ) {
					compare_mark_store_as_paid( $store_id );

					return '<div class="alert alert-success no-margin">' . __( 'Your store is successfully registered. You will be contacted soon.', 'compare' ) . '</div>';
				} else if ( isset( $response['L_ERRORCODE0'] ) && $response['L_ERRORCODE0'] === '11607' ) {
					return '<div class="alert alert-danger no-margin">' . __( 'You have already registered store with this tranaction ID.', 'compare' ) . '</div>';
				} else {
					return '<div class="alert alert-danger no-margin">' . __( 'There was an error processing yor request. Please contact administration of the site with this error message:', 'compare' ) . '<br /><strong>' . $response['error'] . '</strong></div>';
				}
			} else {
				return '<div class="alert alert-danger no-margin">' . __( 'Wrong store', 'compare' ) . '</div>';
			}
		}
	}
}

/*
Do Stripe payment processing
*/
if ( ! function_exists( 'compare_pay_with_stripe' ) ) {
	function compare_pay_with_stripe() {
		$token    = $_POST['token'];
		$store_id = $_POST['store_id'];
		$amount   = compare_check_if_store_exists( $store_id );

		if ( $amount ) {
			$response = wp_remote_post( 'https://api.stripe.com/v1/charges', array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array(
					'Authorization' => 'Bearer ' . compare_get_option( 'stripe_sk_client_id' )
				),
				'body'        => array(
					'amount'        => $amount * 100,
					'currency'      => strtolower( compare_get_option( 'main_unit_abbr' ) ),
					'card'          => $token['id'],
					'receipt_email' => $token['email'],
				),
				'cookies'     => array()
			) );

			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				echo '<div class="alert alert-danger no-margin">' . __( 'Something went wrong: ', 'compare' ) . $error_message . '</div>';
			} else {
				$data = json_decode( $response['body'], true );
				if ( empty( $data['error'] ) ) {
					compare_mark_store_as_paid( $store_id );
					echo '<div class="alert alert-success stripe-complete no-margin">' . __( 'Your store is successfully registered. You will be contacted soon.', 'compare' ) . '</div>';
				} else {
					echo '<div class="alert alert-danger no-margin">' . json_encode( $data ) . '</div>';
				}
			}
		} else {
			echo '<div class="alert alert-danger no-margin">' . __( 'Wrong store', 'compare' ) . '</div>';
		}
		die();
	}
}

add_action( 'wp_ajax_pay_with_stripe', 'compare_pay_with_stripe' );
add_action( 'wp_ajax_nopriv_pay_with_stripe', 'compare_pay_with_stripe' );


/*
Do iDEAL payment processing
*/
if ( ! function_exists( 'compare_pay_with_ideal_link' ) ) {
	function compare_pay_with_ideal_link() {
		$bank_id   = $_POST['bank_id'];
		$store_id  = $_POST['store_id'];
		$amount    = compare_check_if_store_exists( $store_id );
		$permalink = compare_get_permalink_by_tpl( 'page-tpl_register_store' );

		if ( $amount ) {
			$mollie_id  = compare_get_option( 'mollie_id' );
			$return_url = add_query_arg( array( 'payment' => 'ideal', 'store_id' => $store_id ), $permalink );
			$report_url = add_query_arg( array( 'payment' => 'ideal-verify', 'store_id' => $store_id ), $permalink );
			$iDEAL      = new Compare_Mollie_iDEAL_Payment ( $mollie_id );

			$payment = $iDEAL->createPayment( $bank_id, $amount * 100, __( 'Payment for displaying products via feed.', 'compare' ), $return_url, $report_url );

			if ( $payment ) {
				echo esc_url( $iDEAL->getBankURL() );
			} else {
				echo '<div class="alert alert-danger no-margin">' . __( 'Could not retrive bank URL', 'compare' ) . ' ' . $iDEAL->getErrorMessage() . '</div>';
			}

		} else {
			echo '<div class="alert alert-danger no-margin">' . __( 'Wrong store', 'compare' ) . '</div>';
		}
		die();
	}
}

add_action( 'wp_ajax_ideal_link', 'compare_pay_with_ideal_link' );
add_action( 'wp_ajax_nopriv_ideal_link', 'compare_pay_with_ideal_link' );

/*
Return message for the iDEAL confirmation
*/
if ( ! function_exists( 'compare_pay_with_ideal' ) ) {
	function compare_pay_with_ideal() {
		return '<div class="alert alert-success clearfix">' . __( 'Once iDEAL confirms payment you will receive mail notification on the email address you have inputed during registration. Thank You.', 'compare' ) . '</div>';
	}
}

/*
Inform user once iDEAL returns result
*/
if ( ! function_exists( 'compare_ideal_payment_confirmation' ) ) {
	function compare_ideal_payment_confirmation() {
		if ( isset( $_GET['store_id'] ) && isset( $_GET['transaction_id'] ) ) {
			global $wpdb;
			$store_id = $_GET['store_id'];
			$store    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stores WHERE store_id = %d", $store_id ) );
			$store    = array_shift( $store );
			if ( ! empty( $store ) ) {
				$store_contact_email = $store->store_contact_email;

				$mollie_id = compare_get_option( 'mollie_id' );
				$iDEAL     = new Compare_Mollie_iDEAL_Payment( $mollie_id );
				$iDEAL->checkPayment( $_GET['transaction_id'] );

				$MBEmail = compare_get_option( 'skrill_owner_mail' );

				if ( $iDEAL->getPaidStatus() ) {
					compare_mark_store_as_paid( $store_id );
					$message = __( 'iDEAL payment verification was successfull. Your store is added to the list and you will be contacted shortly.', 'compare' );
				} else {
					$message = __( 'iDEAL payment verification has failed. If you think that this is an error contact our administration ', 'compare' ) . '<a href="' . esc_url( home_url() ) . '">' . __( 'here', 'compare' ) . '</a>.';
				}
				$headers   = array();
				$headers[] = "MIME-Version: 1.0";
				$headers[] = "Content-Type: text/html; charset=UTF-8";

				$email_sender = compare_get_option( 'email_sender' );
				$name_sender  = compare_get_option( 'name_sender' );

				if ( ! empty( $email_sender ) && ! empty( $name_sender ) ) {
					$headers[] = "From: " . $from_name . " <" . $from_mail . ">";
				}


				$info = @wp_mail( $store_contact_email, __( 'iDEAL Payment Confirmation', 'compare' ), $message, $headers );

			}
		}
	}
}

/*
Return message for the Skrill confirmation
*/
if ( ! function_exists( 'compare_pay_with_skrill' ) ) {
	function compare_pay_with_skrill() {
		return '<div class="alert alert-success clearfix">' . __( 'Once skrill confirms payment you will receive mail notification on the email address you have inputed during registration. Thank You.', 'compare' ) . '</div>';
	}
}

/*
Inform user once Skrill returns result
*/
if ( ! function_exists( 'compare_skrill_payment_confirmation' ) ) {
	function compare_skrill_payment_confirmation() {
		if ( isset( $_GET['store_id'] ) ) {
			global $wpdb;
			$store_id = $_GET['store_id'];
			$store    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stores WHERE store_id = %d", $store_id ) );
			$store    = array_shift( $store );
			if ( ! empty( $store ) ) {
				$store_contact_email = $store->store_contact_email;
				if ( isset( $_POST['merchant_id'] ) ) {
					$skrill_secret_word = compare_get_option( 'skrill_secret_word' );
					$concatFields       = $_POST['merchant_id'] . $_POST['transaction_id'] . strtoupper( md5( $skrill_secret_word ) ) . $_POST['mb_amount'] . $_POST['mb_currency'] . $_POST['status'];

					$MBEmail = compare_get_option( 'skrill_owner_mail' );

					if ( strtoupper( md5( $concatFields ) ) == $_POST['md5sig'] && $_POST['status'] == 2 && $_POST['pay_to_email'] == $MBEmail ) {
						compare_mark_store_as_paid( $store_id );
						$message = __( 'Skrill payment verification was successfull. Your store is added to the list and you will be contacted shortly.', 'compare' );
					} else {
						$message = __( 'Skrill payment verification has failed. If you think that this is an error contact our administration ', 'compare' ) . '<a href="' . esc_url( home_url() ) . '">' . __( 'here', 'compare' ) . '</a>.';
					}
					$headers   = array();
					$headers[] = "MIME-Version: 1.0";
					$headers[] = "Content-Type: text/html; charset=UTF-8";

					$email_sender = compare_get_option( 'email_sender' );
					$name_sender  = compare_get_option( 'name_sender' );

					if ( ! empty( $email_sender ) && ! empty( $name_sender ) ) {
						$headers[] = "From: " . $from_name . " <" . $from_mail . ">";
					}


					$info = @wp_mail( $store_contact_email, __( 'Skrill Payment Confirmation', 'compare' ), $message, $headers );
				}
			}
		}
	}
}

/*
Return message for the Bank Transfer
*/
if ( ! function_exists( 'compare_pay_with_bank' ) ) {
	function compare_pay_with_bank() {
		$bank_account_name     = compare_get_option( 'bank_account_name' );
		$bank_name             = compare_get_option( 'bank_name' );
		$bank_account_number   = compare_get_option( 'bank_account_number' );
		$bank_sort_number      = compare_get_option( 'bank_sort_number' );
		$bank_iban_number      = compare_get_option( 'bank_iban_number' );
		$bank_bic_swift_number = compare_get_option( 'bank_bic_swift_number' );

		return '<div class="alert alert-info no-margin">
		' . __( 'Make your payment directly into our bank account. Please use your Store name as the payment reference. Your order won’t be processed until the funds have cleared in our account.', 'compare' ) . '
		<h4>' . __( 'Our Bank Details', 'compare' ) . '</h4>
		' . $bank_account_name . ' - ' . $bank_name . '
		<ul class="list-unstyled list=inline">
			<li>
				' . __( 'ACCOUNT NUMBER', 'compare' ) . ':
				' . $bank_account_number . '
			</li>
			<li>
				' . __( 'SORT CODE', 'compare' ) . ':
				' . $bank_sort_number . '
			</li>
			<li>
				' . __( 'IBAN', 'compare' ) . ':
				' . $bank_iban_number . '
			</li>
			<li>
				' . __( 'BIC', 'compare' ) . ':
				' . $bank_bic_swift_number . '
			</li>
		</ul>
	</div>';
	}
}

/*
Import logo of the store to the media library
*/
if ( ! function_exists( 'compare_import_store_logo' ) ) {
	function compare_import_store_logo( $image_url ) {
		if ( empty( $image_url ) ) {
			return '';
		}
		$basename = basename( $image_url );
		$image_id = compare_if_image_exists( $basename );
		$tmp      = download_url( (string) $image_url );

		$file_array = array();
		preg_match( '/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $image_url, $matches );
		if ( empty( $matches[0] ) ) {
			return '';
		}
		$file_array['name']     = basename( $matches[0] );
		$file_array['tmp_name'] = $tmp;
		// If error storing temporarily, unlink
		if ( is_wp_error( $tmp ) ) {
			@unlink( $file_array['tmp_name'] );
			$file_array['tmp_name'] = '';

			return '';
		}

		// do the validation and storage stuff
		$id = media_handle_sideload( $file_array, 0 );

		// If error storing permanently, unlink
		if ( is_wp_error( $id ) ) {
			@unlink( $file_array['tmp_name'] );

			return '';
		}

		return $id;
	}
}

/*
Crete custom cron intervals
*/
if ( ! function_exists( 'compare_cron_add_weekly' ) ) {
	function compare_cron_add_weekly( $schedules ) {
		$schedules['weekly']  = array(
			'interval' => 604800,
			'display'  => __( 'Once Weekly', 'compare' )
		);
		$schedules['monthly'] = array(
			'interval' => 2419200,
			'display'  => __( 'Once Monthly', 'compare' )
		);

		return $schedules;
	}
}

add_filter( 'cron_schedules', 'compare_cron_add_weekly' );

/*
Check if cron should start or not
*/
if ( ! function_exists( 'compare_store_cron_job' ) ) {
	function compare_store_cron_job() {
		$start           = compare_get_option( 'cron_start_date' ) . ' ' . compare_get_option( 'cron_start_time' );
		$start_timestamp = strtotime( $start );
		$cron_frequency  = compare_get_option( 'cron_frequency' );
		$cron_enable     = compare_get_option( 'cron_enable' );
		if ( $start_timestamp ) {
			$start_timestamp += get_option( 'gmt_offset' ) * 3600;
			if ( ! wp_next_scheduled( 'store_cron_job' ) && $cron_enable == 'yes' ) {
				wp_schedule_event( $start_timestamp, $cron_frequency, 'store_cron_job' );
			} else if ( wp_next_scheduled( 'store_cron_job' ) && $cron_enable == 'no' ) {
				wp_clear_scheduled_hook( 'store_cron_job' );
			}
			add_action( 'store_cron_job', 'compare_start_store_cron_job' );
		}
	}
}

add_action( 'init', 'compare_store_cron_job' );

/*
start cron job
*/
if ( ! function_exists( 'compare_start_store_cron_job' ) ) {
	function compare_start_store_cron_job() {
		global $wpdb;
		$stores = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}stores WHERE store_status = '1' AND store_xml_feed <> ''" );
		if ( ! empty( $stores ) ) {
			$content = date_i18n( 'm/d/Y H:i', current_time( 'timestamp' ) );
			foreach ( $stores as $store ) {
				$store_id = $store->store_id;
				ob_start();
				include( locate_template( 'includes/import/import-store.php' ) );
				$content .= ob_get_contents();
				ob_end_clean();
				$content .= "\n\n";
			}
			$content  = str_replace( "<br/>", "\n", $content );
			$content  = str_replace( "<br />", "", $content );
			$log_file = get_template_directory() . '/store-import-log.txt';
			WP_Filesystem();
			global $wp_filesystem;
			if ( file_exists( $log_file ) ) {
				$old_content = $wp_filesystem->get_contents( $log_file );
				$content     = $content . $old_content . "\n\n\n\n";
			}
			$wp_filesystem->put_contents( $log_file, $content );
		}
	}
}

/*
Custom join for the Search page
*/
if ( ! function_exists( 'compare_join_price_range' ) ) {
	function compare_join_price_range( $join ) {
		global $wpdb;
		$join .= "LEFT JOIN {$wpdb->prefix}feed_list AS feed_list ON $wpdb->posts.ID = feed_list.post_id ";

		return $join;
	}
}

/*
Custom group by for the Search page
*/
if ( ! function_exists( 'compare_groupby_price_range' ) ) {
	function compare_groupby_price_range( $groupby ) {
		global $wpdb;
		$groupby             = " {$wpdb->posts}.ID ";
		$product_price_range = isset( $_GET['price'] ) ? (array) $_GET['price'] : '';

		$price_range_list = '';
		for ( $i = 0; $i < sizeof( $product_price_range ); $i ++ ) {
			$price_range = $product_price_range[ $i ];
			if ( empty( $price_range ) ) {
				$price_range = 0;
			}

			if ( ! empty( $price_range_list ) ) {
				$price_range_list .= " OR";
			}

			if ( stristr( $price_range, '-' ) !== false ) {
				$temp = explode( '-', $price_range );

				$price_range_list .= $wpdb->prepare( " ( MIN( feed_list.price ) >= %f AND MIN( feed_list.price ) <= %f ) ", $temp[0], $temp[1] );
			} else {
				$groupby .= $wpdb->prepare( " HAVING MIN(feed_list.price) >= %f ", $price_range );

				return $groupby;
			}
		}

		$groupby .= " HAVING " . $price_range_list . " ";

		return $groupby;
	}
}

/*
Custom order by for the Search page
*/
if ( ! function_exists( 'compare_orderby_price' ) ) {
	function compare_orderby_price( $orderby ) {
		$product_sort = ! empty( $_GET['product-sort'] ) ? $_GET['product-sort'] : '';
		$temp         = explode( '-', $product_sort );
		$orderby      = 'MIN(feed_list.price) ' . $temp[1];

		return $orderby;
	}
}

/*
Custom return values for the Search page
*/
if ( ! function_exists( 'compare_filter_posts_fields' ) ) {
	function compare_filter_posts_fields( $fields ) {
		$fields .= ', MIN(feed_list.price)';

		return $fields;
	}
}


/*
Translatable slugs
*/
global $compare_slugs;
$compare_slugs = array(
	'product'       => 'product',
	'product-cat'   => 'product-cat',
	'product-brand' => 'product-brand',
	'product-tag'   => 'product-tag',
	'keyword'       => 'keyword'
);

/*
Load translated values of the slugs
*/
if ( ! function_exists( 'compare_get_compare_slugs' ) ) {
	function compare_get_compare_slugs() {
		global $compare_slugs;
		foreach ( $compare_slugs as &$slug ) {
			$trans = compare_get_option( 'trans_' . str_replace( '-', '_', $slug ) );
			if ( ! empty( $trans ) ) {
				$slug = $trans;
			}
		}
	}
}

add_action( 'init', 'compare_get_compare_slugs', 1, 0 );

if ( ! function_exists( 'compare_add_query_arg' ) ) {
	function compare_add_query_arg( $args, $permalink = '' ) {
		global $compare_slugs;
		global $wp;
		if ( ! $permalink ) {
			$permalink = get_permalink();
		}

		// Map endpoint to options
		if ( get_option( 'permalink_structure' ) ) {
			if ( strstr( $permalink, '?' ) ) {
				$query_string = '?' . parse_url( $permalink, PHP_URL_QUERY );
				$permalink    = current( explode( '?', $permalink ) );
			} else {
				$query_string = '';
			}

			$permalink = trailingslashit( $permalink );
			if ( ! empty( $args ) ) {
				foreach ( $args as $key => $value ) {
					$permalink .= $key . '/' . $value . '/';
				}
			}
			$permalink .= $query_string;
		} else {
			$permalink = add_query_arg( $args, $permalink );
		}

		return $permalink;
	}
}


if ( ! function_exists( 'compare_add_rewrite_rules' ) ) {
	function compare_add_rewrite_rules() {
		global $wp_rewrite;
		global $compare_slugs;
		$new_rules    = array();
		$custom_rules = array();
		for ( $i = count( $compare_slugs ); $i >= 1; $i -- ) {
			$key = str_repeat( '(' . join( '|', $compare_slugs ) . ')/(.+?)/', $i );

			$key_1      = '([^/]*)/' . $key . '(page)/(.+?)/?$';
			$key_2      = '([^/]*)/' . $key . '?$';
			$rewrite_to = 'index.php?pagename=' . $wp_rewrite->preg_index( 1 );

			for ( $k = 2; $k <= ( $i * 2 ) + 1; $k += 2 ) {
				$rewrite_to .= '&' . $wp_rewrite->preg_index( $k ) . '=' . $wp_rewrite->preg_index( $k + 1 );
			}

			$custom_rules[ $key_1 ] = $rewrite_to . '&paged=' . $wp_rewrite->preg_index( $k + 1 );
			$custom_rules[ $key_2 ] = $rewrite_to;

		}

		$wp_rewrite->rules = $custom_rules + $wp_rewrite->rules;
	}
}

add_action( 'generate_rewrite_rules', 'compare_add_rewrite_rules' );


if ( ! function_exists( 'compare_rewrite_tag' ) ) {
	function compare_rewrite_tag() {
		global $compare_slugs;
		foreach ( $compare_slugs as $slug ) {
			add_rewrite_tag( '%' . $slug . '%', '([^&]+)' );
		}
	}
}

add_action( 'init', 'compare_rewrite_tag', 2, 0 );

/*
Get list of registered image sizes
*/
if ( ! function_exists( 'compare_get_image_sizes' ) ) {
	function compare_get_image_sizes() {
		$list  = array();
		$sizes = get_intermediate_image_sizes();
		foreach ( $sizes as $size ) {
			$list[ $size ] = $size;
		}

		return $list;
	}
}

/*
Add custom columns to the product
custom post type listing in admin
*/
if ( ! function_exists( 'compare_custom_product_columns' ) ) {
	add_filter( 'manage_edit-product_columns', 'compare_custom_product_columns' );
	function compare_custom_product_columns( $columns ) {
		$columns = array_slice( $columns, 0, count( $columns ) - 1, true ) + array(
				"product_clicks"       => __( 'Product Clicks', 'compare' ),
				"product_store_clicks" => __( 'Product Store Clicks', 'compare' ),
			) + array_slice( $columns, count( $columns ) - 1, count( $columns ) - 1, true );

		return $columns;
	}
}

/*
Populate custom columns on the
products custom post type listing page in admin
*/
if ( ! function_exists( 'compare_custom_product_columns_populate' ) ) {
	add_action( 'manage_product_posts_custom_column', 'compare_custom_product_columns_populate', 10, 2 );
	function compare_custom_product_columns_populate( $column, $post_id ) {
		switch ( $column ) {
			case 'product_clicks' :
				$product_clicks = get_post_meta( $post_id, 'product_clicks', true );
				if ( ! empty( $product_clicks ) ) {
					echo esc_html( $product_clicks );
				} else {
					echo 0;
				}
				break;
			case 'product_store_clicks' :
				$product_store_clicks = get_post_meta( $post_id, 'product_store_clicks', true );
				if ( ! empty( $product_store_clicks ) ) {
					echo esc_html( $product_store_clicks );
				} else {
					echo 0;
				}
				break;
		}
	}
}

/*
Make custom columns sortable on the product custom post type
listing in admin
*/
add_filter( 'manage_edit-product_sortable_columns', 'compare_sorting_product_columns' );
if ( ! function_exists( 'compare_sorting_product_columns' ) ) {
	function compare_sorting_product_columns( $columns ) {
		$custom = array(
			'product_clicks'       => 'product_clicks',
			'product_store_clicks' => 'product_store_clicks',
		);

		return wp_parse_args( $custom, $columns );
	}
}

/*
Sort by custom columns on the product custom post type
listing in admin
*/
if ( ! function_exists( 'compare_sort_products_columns' ) ) {
	add_action( 'pre_get_posts', 'compare_sort_products_columns' );
	function compare_sort_products_columns( $query ) {
		if ( ! is_admin() ) {
			return;
		}

		$orderby = $query->get( 'orderby' );
		if ( $orderby == 'product_clicks' || $orderby == 'product_store_clicks' ) {
			$query->set( 'meta_key', $orderby );
			$query->set( 'orderby', 'meta_value_num' );
		}
	}
}


/*
Add import / export feature to custom fields
*/
if ( ! function_exists( 'compare_cf_import_export' ) ) {
	function compare_cf_import_export() {
		include( locate_template( 'includes/cf-import.php' ) );
	}
}

/*
Export CF values
*/
if ( ! function_exists( 'compare_export_cf_values' ) ) {
	function compare_export_cf_values() {
		global $wpdb;
		$tables = array(
			'stores',
			'feed_list',
		);

		$exp_data = array();

		foreach ( $tables as $table ) {
			$table_data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}" . esc_sql( $table ) . "", ARRAY_A );
			if ( ! empty( $table_data ) ) {
				$exp_data[ $table ] = $table_data;
			}
		}

		echo '<textarea class="cd-import">' . json_encode( $exp_data ) . '</textarea>';
	}
}

/*
Import CF values
*/
if ( ! function_exists( 'compare_import_cf_values' ) ) {
	function compare_import_cf_values() {
		global $wpdb;
		$tables = array(
			'stores'    => array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s'
			),
			'feed_list' => array( '%d', '%d', '%d', '%d', '%s', '%d', '%s', '%s' ),
		);
		if ( ! empty( $_POST['compare_custom_data'] ) ) {
			$compare_custom_data = json_decode( stripslashes( $_POST['compare_custom_data'] ), true );
			if ( json_last_error() > 0 ) {
				$compare_custom_data = json_decode( $_POST['compare_custom_data'], true );
			}
			if ( ! empty( $compare_custom_data ) ) {
				foreach ( $compare_custom_data as $table => $data ) {
					foreach ( $data as $row ) {
						$info = $wpdb->insert( $wpdb->prefix . $table, $row, $tables[ $table ] );
					}
				}
				?>
				<div class="updated notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Import process finished', 'compare' ) ?></p>
					<button type="button" class="notice-dismiss"><span
							class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'compare' ) ?></span>
					</button>
				</div>
				<?php
			}
		}
	}
}
