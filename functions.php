<?php

function dt_tour_scripts()
{
    error_log( '*** file path = ' . plugin_dir_path( __FILE__ ) . 'assets/js/setup-tour.js' );
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
