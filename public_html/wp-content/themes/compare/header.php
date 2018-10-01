<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<?php 
if( is_single() || ( is_page() && get_page_template_slug() !== 'page-tpl_search.php' ) ) {
    $image = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'post-thumbnail' );    
    if( is_singular( 'offer' ) && !has_post_thumbnail() ){
        $store_id = get_post_meta( get_the_ID(), 'offer_store', true ); 
        $image = wp_get_attachment_image_src( get_post_thumbnail_id( $store_id ), 'post-thumbnail' );
    }
    ?>
    <meta property="og:title" content="<?php the_title(); ?>" />
    <meta property="og:image" content="<?php echo !empty( $image ) ? esc_url( $image[0] ) : '' ?>" />
    <meta property="og:url" content="<?php the_permalink(); ?>" />

    <meta name="twitter:title" content="<?php the_title(); ?>" />
    <meta name="twitter:image" content="<?php echo !empty( $image ) ? esc_url( $image[0] ) : '' ?>" />    
    <?php
}
?>

<?php
if(is_page() && get_page_template_slug() == 'page-tpl_search.php' ){
    global $compare_slugs;
    $category_slug_name = get_query_var( $compare_slugs['product-cat'], '' );
    $brand_slug_name = get_query_var( $compare_slugs['product-brand'], '' );
    $tag_slug_name = get_query_var( $compare_slugs['product-tag'], '' );
    if( !empty( $category_slug_name ) ){
        $term = get_term_by( 'slug', $category_slug_name, 'product-cat'  );
    }
    else if( !empty( $brand_slug_name ) ){
        $term = get_term_by( 'slug', $brand_slug_name, 'product-brand'  );
    }
    else if( !empty( $tag_slug_name ) ){
        $term = get_term_by( 'slug', $tag_slug_name, 'product-tag'  );
    }
    if( !empty( $term ) ){
        echo '<meta name="title" content="'.esc_html( $term->name ).'">';
		echo '<meta name="description" content="'.esc_html( $term->description ).'">';    
		echo '<meta property="og:title" content="'.esc_html( $term->name ).'" />';
		echo '<meta property="og:url" content="'.get_permalink().'" />';
		echo '<meta property="twitter:title" content="'.esc_html( $term->name ).'" />';
    }
}
?>

<?php wp_head(); ?>
</head>
<?php

?>
<body <?php body_class() ?> >
<?php $site_logo = compare_get_option( 'site_logo' ); ?>


<?php
$show_top_bar = compare_get_option( 'show_top_bar' );
$locations = get_nav_menu_locations();
if( $show_top_bar == 'yes' ):
?>
<header class="top-bar">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <?php 
                $has_nav = isset( $locations[ 'top-left-navigation' ] ) ? true : false;
                if( $has_nav ):
                ?>
                    <div class="navbar navbar-default top-left-navigation" role="navigation">
                        <ul class="list-unstyled list-inline nav navbar-nav clearfix top-left-nav">
                            <li><a href="<?php echo esc_url( home_url('/') ) ?>"><i class="fa fa-home"></i></a></li>
                            <?php
                            if ( $has_nav ) {
                                wp_nav_menu( array(
                                    'theme_location'    => 'top-left-navigation',
                                    'menu_class'        => '',
                                    'container'         => false,
                                    'echo'              => true,
                                    'items_wrap'        => '%3$s',
                                    'depth'             => 10,
                                    'walker'            => new compare_walker,
                                ) );
                            }
                            ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <ul class="list-inline list-unstyled text-right">
                    <?php
                    $top_bar_mail = compare_get_option( 'top_bar_mail' );
                    if( !empty( $top_bar_mail ) ){
                        echo '<li><i class="fa fa-envelope-o"></i>'.$top_bar_mail.'</li>';
                    }

                    $top_bar_phone = compare_get_option( 'top_bar_phone' );
                    if( !empty( $top_bar_phone ) ){
                        echo '<li><i class="fa fa-phone"></i>'.$top_bar_phone.'</li>';
                    }

                    $top_bar_facebook_link = compare_get_option( 'top_bar_facebook_link' );
                    if( !empty( $top_bar_facebook_link ) ){
                        echo '<li class="top-bar-social"><a href="'.esc_url( $top_bar_facebook_link ).'"><i class="fa fa-facebook"></i></a></li>';
                    }

                    $top_bar_google_link = compare_get_option( 'top_bar_google_link' );
                    if( !empty( $top_bar_google_link ) ){
                        echo '<li class="top-bar-social"><a href="'.esc_url( $top_bar_google_link ).'"><i class="fa fa-google-plus"></i></a></li>';
                    }

                    $top_bar_twitter_link = compare_get_option( 'top_bar_twitter_link' );
                    if( !empty( $top_bar_twitter_link ) ){
                        echo '<li class="top-bar-social"><a href="'.esc_url( $top_bar_twitter_link ).'"><i class="fa fa-twitter"></i></a></li>';
                    }
                    ?>
                </ul>
            </div>            
        </div>
    </div>
</header>
<?php endif; ?>


<?php 
$site_logo = compare_get_option( 'site_logo' );
$has_nav = isset( $locations[ 'top-navigation' ] ) ? true : false;
if( !empty( $site_logo['url'] ) || $has_nav ):
?>
    <header class="navigation <?php echo !class_exists('ReduxFramework') && is_home() ? 'navigation-blog-home' : ''; ?>" data-enable_sticky="<?php echo esc_attr( compare_get_option( 'enable_sticky' ) ) ?>">
        <div class="container">
            <div class="clearfix">
                <div class="pull-left">
                    <?php if( !empty( $site_logo['url'] ) ): ?>
                        <a href="<?php echo esc_url( home_url('/') ); ?>" class="site-logo">    
                            <img src="<?php echo esc_url( $site_logo['url'] ); ?>" height="<?php echo esc_attr( $site_logo['height'] ); ?>" width="<?php echo esc_attr( $site_logo['width'] ); ?>" title="" alt="">
                        </a>
                    <?php endif; ?>
                    <?php if( class_exists('ReduxFramework') ): ?>
                        <a href="<?php echo esc_url( compare_get_permalink_by_tpl( 'page-tpl_register_store' ) ) ?>" class="small-screen-register-store">
                            <i class="fa fa-unlock-alt"></i><?php _e( 'REGISTER YOUR STORE', 'compare' ) ?>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="pull-right">
                    <a href="javascript:;" class="toggle-navigation">
                        <i class="fa fa-bars"></i>
                    </a>
                    <div class="navbar navbar-default" role="navigation">
                        <div class="collapse navbar-collapse pull-right">
                            <?php
                            if ( $has_nav ) {
                                wp_nav_menu( array(
                                    'theme_location'    => 'top-navigation',
                                    'menu_class'        => 'nav navbar-nav clearfix',
                                    'container'         => false,
                                    'echo'              => true,
                                    'items_wrap'        => '<ul class="%2$s">%3$s'.( class_exists('ReduxFramework') ? '<li class="register-nav"><a href="'.esc_url( compare_get_permalink_by_tpl( 'page-tpl_register_store' ) ).'"><i class="fa fa-unlock-alt"></i>'.__( 'REGISTER YOUR STORE', 'compare' ).'</a></li>' : '' ).'</ul>',
                                    'depth'             => 10,
                                    'walker'            => new compare_walker,
                                ) );
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
<?php endif; ?>

<?php
$enable_search_bar = compare_get_option( 'enable_search_bar' );
if( $enable_search_bar == 'yes' ):
?>
    <header class="search_bar">
        <div class="container">
            <a href="javascript:;" class="toggle-categories">
                <i class="fa fa-list-ul"></i>
            </a>        
            <div class="dropdown <?php echo is_front_page() ? 'always-open' : '' ?>">
                <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    <i class="fa fa-bars"></i>
                    <?php _e( 'Categories', 'compare' ) ?>
                </button>
                <ul class="dropdown-menu category_mega_menu" aria-labelledby="dropdownMenu1">
                    <?php compare_main_categories_list(); ?>
                </ul>
            </div>
            <form method="get" action=<?php echo compare_get_permalink_by_tpl( 'page-tpl_search' ) ?>>
                <input type="text" name="keyword" placeholder="<?php _e( 'What are you shopping for?', 'compare' ) ?>">
                <a href="javascript:;" class="submit-form"><i class="fa fa-search"></i></a>
            </form>
        </div>
    </header>
<?php endif; ?>


<?php 
$breadcrumbs = compare_get_breadcrumbs();
if( !empty( $breadcrumbs ) ): ?>
<section class="breadcrumbs-section">
    <div class="container">
        <?php echo  $breadcrumbs ?>
    </div>
</section>
<?php endif; ?>