<?php
/**
 * Plugin Name: .well-known/feeds
 * Plugin URI: https://github.com/pfefferle/wordpress-well-known-feeds
 * Description: <link /> is fine, but I feel like there should be a standard for a site, not a page, to share a “list of feeds associated with a site”.
 * Author: Matthias Pfefferle
 * Author URI: https://notiz.blog/
 * Version: 1.0.0
 * License: GPL-2.0-or-later
 * License URI: https://opensource.org/license/gpl-2-0/
 * Text Domain: wellknownfeeds
 * Update URI: https://github.com/pfefferle/wordpress-well-known-feeds
 */

namespace Well_Known_Feeds;

function get_blog_feeds( $args = array() ) {
	$defaults = array(
		/* translators: Separator between blog name and feed type in feed links */
		'separator'     => _x( '-', 'feed link', 'wellknownfeeds' ),
		/* translators: 1: blog name, 2: separator(raquo), 3: post type */
		'posttypetitle' => __( '%1$s Post-Type %2$s %3$s Feed', 'wellknownfeeds' ),
		/* translators: 1: Site title, 2: Separator (raquo). */
		'feedtitle'     => __( '%1$s %2$s %3$s Feed', 'wellknownfeeds' ),
		/* translators: 1: Site title, 2: Separator (raquo). */
		'comstitle'     => __( 'Comments %1$s %2$s Feed', 'wellknownfeeds' ),
	);

	$args  = wp_parse_args( $args, $defaults );
	$feeds = array();

	// does theme support post formats
	$post_formats = get_theme_support( 'post-formats' );

	if ( $post_formats ) {
		$post_formats = current( $post_formats );
	} else {
		$post_formats = array();
	}

	$post_formats[] = 'standard';

	foreach ( $post_formats as $post_format ) {
		$feeds[] = array(
			'title'   => sprintf( $args['posttypetitle'], get_post_format_string( $post_format ), $args['separator'], esc_attr( strtoupper( get_default_feed() ) ) ),
			'href'    => get_post_format_archive_feed_link( $post_format ),
			'version' => get_default_feed(),
		);
	}

	// Add "standard" post-format feed discovery
	global $wp_query;
	if (
		is_archive() &&
		isset( $wp_query->query['post_format'] ) &&
		'post-format-standard' === $wp_query->query['post_format']
	) {
		$feeds[] = array(
			'title'   => sprintf( $args['posttypetitle'], get_post_format_string( 'standard' ), $args['separator'], esc_attr( strtoupper( get_default_feed() ) ) ),
			'href'    => get_post_format_archive_feed_link( 'standard' ),
			'version' => get_default_feed(),
		);
	}

	foreach ( array( 'rss2', 'atom' ) as $type ) {
		$feeds[] = array(
			'title'   => esc_attr( sprintf( $args['feedtitle'], __( 'All Posts', 'wellknownfeeds' ), $args['separator'], esc_attr( strtoupper( $type ) ) ) ),
			'href'    => esc_url( get_feed_link( $type ) ),
			'version' => $type,
		);

		$feeds[] = array(
			'title'   => esc_attr( sprintf( $args['comstitle'], $args['separator'], esc_attr( strtoupper( $type ) ) ) ),
			'href'    => esc_url( get_feed_link( 'comments_' . $type ) ),
			'version' => $type,
		);
	}

	return $feeds;
}

/**
 * Adds support for "standard" Post-Format
 *
 * @param string $post_format the post format slug
 *
 * @return void
 */
function get_post_format_archive_feed_link( $post_format, $feed = '' ) {
	$default_feed = get_default_feed();
	if ( empty( $feed ) ) {
		$feed = $default_feed;
	}

	$link = get_post_format_link( $post_format );
	if ( ! $link ) {
		return false;
	}

	if ( get_option( 'permalink_structure' ) ) {
		$link  = trailingslashit( $link );
		$link .= 'feed/';
		if ( $feed !== $default_feed ) {
			$link .= "$feed/";
		}
	} else {
		$link = add_query_arg( 'feed', $feed, $link );
	}

	/**
	 * Filters the post type archive feed link.
	 *
	 * @param string $link The post type archive feed link.
	 * @param string $feed Feed type. Possible values include 'rss2', 'atom'.
	 */
	return apply_filters( 'post_format_archive_feed_link', $link, $feed );
}

/**
 * Adds support for "standard" post-format archive links.
 *
 * @param string $post_format
 *
 * @return void
 */
function get_post_format_link( $post_format ) {
	if ( 'standard' !== $post_format ) {
		return get_post_format_link( $post_format );
	}

	global $wp_rewrite;

	$termlink = $wp_rewrite->get_extra_permastruct( 'post_format' );

	if ( empty( $termlink ) ) {
		$termlink = '?post_format=standard';
		$termlink = home_url( $termlink );
	} else {
		$termlink = str_replace( '%post_format%', 'standard', $termlink );
		$termlink = home_url( user_trailingslashit( $termlink, 'category' ) );
	}

	return $termlink;
}


/**
 * Parse request for .well-known/feeds. This is the main entry point for handling
 * short URLs.
 *
 * @uses apply_filters() Calls 'hum_redirect' filter
 * @uses apply_filters() Calls 'hum_process_redirect' filter
 *
 * @param WP $wp the WordPress environment for the request
 */
function parse_request( $wp ) {
	if (
		! array_key_exists( 'well-known', $wp->query_vars ) ||
		! 'feeds' === $wp->query_vars['well-known']
	) {
		return;
	}
	header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );

	load_template( plugin_dir_path( __FILE__ ) . '/well-known-feeds-template.php', true );
	exit;
}
add_action( 'parse_request', __NAMESPACE__ . '\parse_request' );

/**
 * Accept .well-known query variables.
 */
function query_vars( $vars ) {
	$vars[] = 'well-known';
	return $vars;
}
add_action( 'query_vars', __NAMESPACE__ . '\query_vars' );

/**
 * Add rewrite rules for .well-known/feeds.
 */
function rewrite_rules() {
	add_rewrite_rule( '^.well-known/feeds', 'index.php?well-known=feeds', 'top' );
}
add_action( 'init', __NAMESPACE__ . '\rewrite_rules', 15 );

/**
 * Add rewrite rules for .well-known/feeds.
 */
function flush_rewrite_rules() {
	namespace\rewrite_rules();
	\flush_rewrite_rules();
}
register_activation_hook( __FILE__, __NAMESPACE__ . '\flush_rewrite_rules' );
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\flush_rewrite_rules' );
