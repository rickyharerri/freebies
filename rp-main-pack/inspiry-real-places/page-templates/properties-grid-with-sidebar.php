<?php
/*
 * Template Name: Properties Grid - With Sidebar
 */

get_header();

$display_google_map = get_post_meta( get_the_ID(), 'inspiry_display_google_map', true );

if ( $display_google_map ) {
    get_template_part( 'partials/header/map' );
} else {
    get_template_part( 'partials/header/banner' );
}
?>
    <div id="content-wrapper" class="site-content-wrapper site-pages">

        <div id="content" class="site-content layout-boxed">

            <div class="container">

                <div class="row">

                    <div class="col-md-9 site-main-content">

                        <main id="main" class="site-main">

                            <?php
                            global $paged;
                            if ( is_front_page() ) {
                                $paged = ( get_query_var('page') ) ? get_query_var( 'page' ) : 1;
                            }

                            $properties_grid_arg = array(
                                'post_type'         => 'property',
                                'paged'             => $paged,
                            );

                            // Apply properties filter
                            $properties_grid_arg = apply_filters( 'inspiry_properties_filter', $properties_grid_arg );

                            // Apply sorting filter
                            $properties_grid_arg = apply_filters( 'inspiry_sort_properties', $properties_grid_arg );

                            $properties_grid_query = new WP_Query( $properties_grid_arg );

                            /*
                             * Found properties heading and sorting controls
                             */
                            global $found_properties;
                            $found_properties = $properties_grid_query->found_posts;
                            get_template_part( 'partials/property/templates/listing-control' );

                            /**
                             * Page Content
                             */
                            if ( have_posts() ) {
                                while ( have_posts() ) {
                                    the_post();
                                    if ( ! empty( get_the_content() ) ) {
                                        get_template_part( 'partials/page/content' );
                                    }
                                }
                            }

                            /*
                             * Properties Grid
                             */
                            if ( $properties_grid_query->have_posts() ) :

                                global $property_grid_counter;
                                $property_grid_counter = 1;

                                echo '<div class="row">';

                                while ( $properties_grid_query->have_posts() ) :

                                    $properties_grid_query->the_post();

                                    // display property in list layout
                                    get_template_part( 'partials/property/templates/property-for-grid-with-sidebar' );

                                    $property_grid_counter++;

                                endwhile;

                                echo '</div>';

                                inspiry_pagination( $properties_grid_query );

                                wp_reset_postdata();

                            endif;
                            ?>

                        </main>
                        <!-- .site-main -->

                    </div>
                    <!-- .site-main-content -->

                    <div class="col-md-3 site-sidebar-content">

                        <?php get_sidebar( 'properties-grid' ); ?>

                    </div>
                    <!-- .site-sidebar-content -->

                </div>
                <!-- .row -->

            </div>
            <!-- .container -->

        </div>
        <!-- .site-content -->

    </div><!-- .site-content-wrapper -->

<?php
get_footer();
?>
