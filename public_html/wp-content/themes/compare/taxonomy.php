<?php
get_header();

global $wp_query;
$cur_page = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1; //get curent page

$page_links_total =  $wp_query->max_num_pages;
$page_links = paginate_links( 
    array(
        'prev_next' => true,
        'end_size' => 2,
        'mid_size' => 2,
        'total' => $page_links_total,
        'current' => $cur_page, 
        'prev_next' => true,
        'prev_text' => __( 'Prev', 'compare' ),
        'next_text' => __( 'Next', 'compare' ),
        'type' => 'array'
    )
);

$pagination = compare_format_pagination( $page_links );
$counter = 0;
$product_ids = wp_list_pluck( $wp_query->posts, 'ID' );
$product_metas = compare_product_item_meta( $product_ids );

$description = term_description();
?>
<section>
    <div class="container">
        <?php if( !empty( $description ) ): ?>
            <div class="white-block taxonomy-description">
                <div class="white-block-content">
                    <?php echo $description; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php       
        if( have_posts() ){
            ?>
            <div class="row">
            <?php
            while( have_posts() ){
                the_post();
                $has_media = compare_has_media();
                if( $counter == 4 ){
                    echo '</div><div class="row">';
                    $counter = 0;
                }
                $counter++;
                echo '<div class="col-sm-3">';
                    include( locate_template('includes/product-box.php') );
                echo '</div>';
            }
            ?>
            </div>
            <?php
        }
        else{
            ?>
            <div class="white-block">
                <div class="white-block-content">
                    <?php _e( 'No results found', 'compare' ) ?>
                </div>
            </div>
            <?php
        }

        if( !empty( $pagination ) ) {
            ?>
            <div class="white-block pagination">
                <ul class="list-unstyled">
                    <?php echo  $pagination; ?>
                </ul>
            </div>
            <?php
        }
        ?>
    </div>
</section>

<?php get_footer(); ?>