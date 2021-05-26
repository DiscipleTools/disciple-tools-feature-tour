<?php

function dt_tour_scripts()
{
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script('tour', 'https://cdn.jsdelivr.net/npm/shepherd.js@5.0.1/dist/js/shepherd.js', false, '5.0.1', true);
    $relative_file_path = 'assets/js/setup-tour.js';
    wp_enqueue_script( 'setup-tour', plugin_dir_url( __FILE__ ) . $relative_file_path, [ 
        'jquery',
        'tour',
    ], filemtime( plugin_dir_path( __FILE__ ) . $relative_file_path ), true );

    $post_type = get_post_type();
    $post_type = $post_type ?: dt_get_post_type();
    $post_settings = DT_Posts::get_post_settings( $post_type );
    $translations = [
        'done' => __( 'Done', 'disciple_tools_feature_tour' ),
        'next' => __( 'Next', 'disciple_tools_feature_tour' ),
        'back' => __( 'Back', 'disciple_tools_feature_tour' ),
        'close_tour' => __( 'Close Tour', 'disciple_tools_feature_tour' ),
    ];

    $list_tour_types = apply_filters( 'dt_tour_list_pages', DT_Posts::get_post_types() );

    $list_tour_translations = in_array( $post_type, $list_tour_types, true ) ? [
        'create_post_tour' => sprintf( _x( "Click here to create a new %s", 'Click here to create a new contact', 'disciple_tools_feature_tour' ), $post_settings["label_singular"] ),
        'filter_posts_tour' => sprintf( _x( "You can filter to find %s you need.", 'You can filter to find contacts you need.', 'disciple_tools_feature_tour' ), $post_settings["label_plural"] ),
        'view_posts_tour' => sprintf( _x( "%s appear here and can be clicked on to view more.", 'Contacts appear here and can be clicked on to view more.', 'disciple_tools_feature_tour' ), $post_settings["label_plural"] ),
    ] : [];

    wp_localize_script( 'setup-tour', 'tour_settings', array(
        'translations' => apply_filters( 'dt_tour_translations', $translations ),
        'list_tour_translations' => $list_tour_translations,
        'completed_tours' => get_user_meta( get_current_user_id(), 'dt_product_tour' ),
    ) );
}

add_action( 'wp_enqueue_scripts', 'dt_tour_scripts', 999 );

function dt_tour_add_api_routes()
{
    $version = 1;
    $context = 'dt_tour';
    register_rest_route(
        "$context/v$version", '/users/disable_product_tour/', [
            'methods' => "POST",
            'callback' => 'disable_product_tour',
            'permission_callback' => '__return_true',
        ]
    );
}

function disable_product_tour( WP_REST_Request $request ) {
    $params = $request->get_json_params();

    $tour_ids = [
        'list_tour',
    ];
    if ( !isset( $params['tour_id'] ) ) {
        return new WP_Error( "missing_error", "Missing fields", [ 'status' => 400 ] );
    }

    if ( !in_array( $params['tour_id'], $tour_ids, true ) ) {
        return new WP_Error( "invalid_tour", "Invalid tour id", [ 'status' => 400 ] );
    }

    $completed_tours = get_user_meta( get_current_user_id(), 'dt_product_tour' );
    if ( in_array( $params['tour_id'], $completed_tours ) ) {
        return new WP_Error( "tour_complete", "Tour already complete", [ 'status' => 400 ] );
    }

    return update_user_meta( get_current_user_id(), 'dt_product_tour', $params['tour_id'] );
}

add_action( 'rest_api_init', 'dt_tour_add_api_routes' );
