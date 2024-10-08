<?php
/*
 * Template Name: Submit Property
 */

global $inspiry_options;
$invalid_nonce = false;
$submitted_successfully = false;
$updated_successfully = false;

// Check if action field is set and user is logged in
if( isset( $_POST['action'] ) && is_user_logged_in() ) {

    /* the nonce */
    if( wp_verify_nonce( $_POST['property_nonce'], 'submit_property' ) ) {

        // Start with basic array
        $new_property = array(
            'post_type'	    =>	'property'
        );

        // Title
        if( isset ( $_POST['inspiry_property_title'] ) && ! empty ( $_POST['inspiry_property_title'] ) ) {
            $new_property['post_title']	= sanitize_text_field( $_POST['inspiry_property_title'] );
        }

        // Description
        if( isset ( $_POST['description'] ) && ! empty ( $_POST['description'] ) ) {

	        /*
	         * Allow iframe to pass so that people can embed things like virtual tour
	         */
	        $allowed_tags = wp_kses_allowed_html( 'post' );
	        $allowed_tags['iframe'] = array(
		        'src'             => array(),
		        'height'          => array(),
		        'width'           => array(),
		        'frameborder'     => array(),
		        'allowfullscreen' => array(),
	        );

            $new_property['post_content'] = wp_kses( $_POST['description'], $allowed_tags );
        }

        // Author
        $current_user = wp_get_current_user();
        $new_property['post_author'] = $current_user->ID;


        /* check the type of action */
        $action = $_POST['action'];
        $property_id = 0;

        if( $action == "add_property" ) {

            $default_submit_status = $inspiry_options[ 'inspiry_default_submit_status' ];
            if ( !empty( $default_submit_status ) ) {
                $new_property['post_status'] = $default_submit_status;
            } else {
                $new_property['post_status'] = 'pending';
            }
            $property_id = wp_insert_post( $new_property ); // Insert Property and get Property ID
            if( $property_id > 0 ){
                $submitted_successfully = true;
                do_action( 'wp_insert_post', 'wp_insert_post' ); // Post the Post
            }
        } elseif ( $action == "update_property" ) {
            $new_property['ID'] = intval( $_POST['property_id'] );
            $property_id = wp_update_post( $new_property ); // Update Property and get Property ID
            if( $property_id > 0 ){
                $updated_successfully = true;
            }
        }

        /*
         * Added / Updates ( In any case there should be a valid property id )
         */
        if( $property_id > 0 ) {

            // Attach Property Type with Newly Created Property
            if( isset( $_POST['type'] ) && ( $_POST['type'] != "-1" ) ) {
                wp_set_object_terms( $property_id, intval( $_POST['type'] ), 'property-type' );
            }

            // Attach Property City with Newly Created Property
            if( isset( $_POST['city'] ) && ( $_POST['city'] != "-1" ) ) {
                wp_set_object_terms( $property_id, intval( $_POST['city'] ), 'property-city' );
            }

            // Attach Property Status with Newly Created Property
            if( isset( $_POST['status'] ) && ( $_POST['status'] != "-1" ) ) {
                wp_set_object_terms( $property_id, intval( $_POST['status'] ), 'property-status' );
            }

            // Attach Property Features with Newly Created Property
            if( isset( $_POST['features'] ) ) {
                if( ! empty( $_POST['features'] ) && is_array( $_POST['features'] ) ) {
                    $property_features = array();
                    foreach( $_POST['features'] as $property_feature_id ) {
                        $property_features[] = intval( $property_feature_id );
                    }
                    wp_set_object_terms( $property_id , $property_features, 'property-feature' );
                }
            }

            // Attach Price Post Meta
            if( isset ( $_POST['price'] ) && !empty ( $_POST['price'] ) ) {
                update_post_meta( $property_id, 'REAL_HOMES_property_price', sanitize_text_field( $_POST['price'] ) );

                if( isset ( $_POST['price-postfix'] ) && ! empty ( $_POST['price-postfix'] ) ) {
                    update_post_meta( $property_id, 'REAL_HOMES_property_price_postfix', sanitize_text_field( $_POST['price-postfix'] ) );
                }
            }


            // Attach Size Post Meta
            if( isset ( $_POST['size'] ) && !empty ( $_POST['size'] ) ) {
                update_post_meta($property_id, 'REAL_HOMES_property_size', sanitize_text_field ( $_POST['size'] ) );

                if( isset ( $_POST['area-postfix'] ) && !empty ( $_POST['area-postfix'] ) ) {
                    update_post_meta( $property_id, 'REAL_HOMES_property_size_postfix', sanitize_text_field( $_POST['area-postfix'] ) );
                }
            }


            // Attach Bedrooms Post Meta
            if( isset ( $_POST['bedrooms'] ) && !empty ( $_POST['bedrooms'] ) ) {
                update_post_meta( $property_id, 'REAL_HOMES_property_bedrooms', floatval( $_POST['bedrooms'] ) );
            }

            // Attach Bathrooms Post Meta
            if( isset ( $_POST['bathrooms'] ) && !empty ( $_POST['bathrooms'] ) ) {
                update_post_meta( $property_id, 'REAL_HOMES_property_bathrooms', floatval( $_POST['bathrooms'] ) );
            }

            // Attach Garages Post Meta
            if( isset ( $_POST['garages'] ) && !empty ( $_POST['garages'] ) ) {
                update_post_meta( $property_id, 'REAL_HOMES_property_garage', floatval( $_POST['garages'] ) );
            }

            // Attach Address Post Meta
            if( isset ( $_POST['address'] ) && !empty ( $_POST['address'] ) ) {
                update_post_meta( $property_id, 'REAL_HOMES_property_address', sanitize_text_field( $_POST['address'] ) );
            }

            // Attach Address Post Meta
            if( isset ( $_POST['location'] ) && !empty ( $_POST['location'] ) ) {
                update_post_meta( $property_id, 'REAL_HOMES_property_location', $_POST['location'] );
            }

            // Agent Display Option
            if( isset ( $_POST['agent_display_option'] ) && ! empty ( $_POST['agent_display_option'] ) ) {
                update_post_meta( $property_id, 'REAL_HOMES_agent_display_option', $_POST['agent_display_option']);
                if ( ( $_POST['agent_display_option'] == "agent_info" ) && isset( $_POST['agent_id'] ) ) {
                    update_post_meta( $property_id, 'REAL_HOMES_agents', $_POST['agent_id'] );
                }
            }

            // Attach Property ID Post Meta
            if( isset ( $_POST['property-id'] ) && !empty ( $_POST['property-id'] ) ) {
                update_post_meta( $property_id, 'REAL_HOMES_property_id', sanitize_text_field( $_POST['property-id'] ) );
            }

            // Attach Virtual Tour Video URL Post Meta
            if( isset ( $_POST['video-url'] ) && !empty ( $_POST['video-url'] ) ) {
                update_post_meta( $property_id, 'REAL_HOMES_tour_video_url', esc_url_raw( $_POST['video-url'] ) );
            }

            // Attach additional details with property
            if( isset( $_POST['detail-titles'] ) && isset( $_POST['detail-values'] ) ) {

                $additional_details_titles = $_POST['detail-titles'];
                $additional_details_values = $_POST['detail-values'];

                $titles_count = count ( $additional_details_titles );
                $values_count = count ( $additional_details_values );

                // to skip empty values on submission
                if ( $titles_count == 1 && $values_count == 1 && empty ( $additional_details_titles[0] ) && empty ( $additional_details_values[0] ) ) {
                    // do nothing and let it go
                } else {

                    if( !empty( $additional_details_titles ) && !empty( $additional_details_values ) ) {
                        $additional_details = array_combine( $additional_details_titles, $additional_details_values );
                        update_post_meta( $property_id, 'REAL_HOMES_additional_details', $additional_details );
                    }

                }
            }

            // Attach Property as Featured Post Meta
            $featured = ( isset( $_POST['featured'] ) ) ? 1 : 0 ;
            if ( $featured ) {
                update_post_meta( $property_id, 'REAL_HOMES_featured', $featured );
            }

            // Tour video image - in case of update
            $tour_video_image = "";
            $tour_video_image_id = 0;
            if( $action == "update_property" ) {
                $tour_video_image_id = get_post_meta( $property_id, 'REAL_HOMES_tour_video_image', true );
                if ( ! empty ( $tour_video_image_id ) ) {
                    $tour_video_image_src = wp_get_attachment_image_src( $tour_video_image_id, 'property-detail-video-image' );
                    $tour_video_image = $tour_video_image_src[0];
                }
            }

            // if property is being updated, clean up the old meta information related to images
            if( $action == "update_property" ){
                delete_post_meta( $property_id, 'REAL_HOMES_property_images' );
                delete_post_meta( $property_id, '_thumbnail_id' );
            }

            // Attach gallery images with newly created property
            if ( isset( $_POST['gallery_image_ids'] ) ) {
                if( ! empty ( $_POST['gallery_image_ids'] ) && is_array ( $_POST['gallery_image_ids'] ) ) {
                    $gallery_image_ids = array();
                    foreach ( $_POST['gallery_image_ids'] as $gallery_image_id ) {
                        $gallery_image_ids[] = intval( $gallery_image_id );
                        add_post_meta( $property_id, 'REAL_HOMES_property_images', $gallery_image_id );
                    }
                    if ( isset( $_POST['featured_image_id'] ) ) {
                        $featured_image_id = intval( $_POST['featured_image_id'] );
                        if ( in_array( $featured_image_id, $gallery_image_ids ) ) {     // validate featured image id
                            update_post_meta ( $property_id, '_thumbnail_id', $featured_image_id );

                            /* if video url is provided but there is no video image then use featured image as video image */
                            if ( empty( $tour_video_image ) && !empty( $_POST['video-url'] ) ) {
                                update_post_meta( $property_id, 'REAL_HOMES_tour_video_image', $featured_image_id );
                            }
                        }
                    } elseif( !empty ( $gallery_image_ids ) ) {
                        update_post_meta ( $property_id, '_thumbnail_id', $gallery_image_ids[0] );
                    }
                }
            }


            if( "add_property" == $_POST['action'] ) {

                /*
                 * inspiry_submit_notice function is hooked here
                 */
                do_action( 'inspiry_after_property_submit', $property_id  );

            } elseif ( "update_property" == $_POST['action'] ) {

                /*
                 * no default theme function is hooked here for now
                 */
                do_action( 'inspiry_after_property_update', $property_id );

            }

            // redirect to my properties page
            if( !empty( $inspiry_options[ 'inspiry_my_properties_page' ]  ) ) {
                $my_properties_url = get_permalink( $inspiry_options[ 'inspiry_my_properties_page' ] );
                if ( !empty( $my_properties_url ) ) {
                    $separator = ( parse_url( $my_properties_url, PHP_URL_QUERY ) == NULL ) ? '?' : '&';
                    $parameter = ( $updated_successfully ) ? 'property-updated=true' : 'property-added=true';
                    wp_redirect( $my_properties_url . $separator . $parameter );
                }
            }

        }

    } else {
        $invalid_nonce = true;
    }
}

get_header();

get_template_part( 'partials/header/banner' );
?>
    <div id="content-wrapper" class="site-content-wrapper site-pages">

        <div id="content" class="site-content layout-boxed">

            <div class="container">

                <div class="row">

                    <div class="col-xs-12 site-main-content">

                        <main id="main" class="site-main">

                            <div class="white-box submit-property-box">

                                <?php
                                /*
                                 * Display page contents if any
                                 */
                                if ( have_posts() ):
                                    while ( have_posts() ):
                                        the_post();
                                        $content = get_the_content();
                                        if ( !empty( $content ) ) {
                                            ?>
                                            <article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?> >
                                                <div class="entry-content clearfix">
                                                    <?php the_content(); ?>
                                                </div>
                                            </article>
                                            <?php
                                        }
                                    endwhile;
                                endif;

                                /*
                                 * Property submit and update stuff
                                 */
                                if ( is_user_logged_in() ) {
                                    if ( $invalid_nonce ) {
                                        inspiry_message( __( 'Oops','inspiry' ), __( 'Security check failed!', 'inspiry' ) );
                                    } else {
                                        if ( $submitted_successfully ) {
                                            inspiry_message( __( 'Submitted','inspiry' ), __( 'Property successfully submitted.', 'inspiry' ) );
                                        } else if ( $updated_successfully ) {
                                            inspiry_message( __('Updated','inspiry'), __('Property updated successfully.', 'inspiry' ) );
                                        } else {
                                            if( isset( $_GET['edit_property'] ) && ! empty( $_GET['edit_property'] ) ) { // if passed parameter is properly set to edit property
                                                get_template_part( 'partials/property/templates/edit-form' );
                                            } else {
                                                get_template_part( 'partials/property/templates/submit-form' );
                                            }
                                        }
                                    }
                                } else {
                                    inspiry_message( __( 'Login Required', 'inspiry' ), __( 'You need to login to submit a property!', 'inspiry' ) );
                                }
                                ?>

                            </div>
                            <!-- .submit-property-box -->


                        </main>
                        <!-- .site-main -->

                    </div>
                    <!-- .site-main-content -->

                </div>
                <!-- .row -->

            </div>
            <!-- .container -->

        </div>
        <!-- .site-content -->

    </div><!-- .site-content-wrapper -->

<?php
/*
 * Footer
 */
get_footer();
