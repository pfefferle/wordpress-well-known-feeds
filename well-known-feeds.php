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

/**
 * Feed types registered with WordPress.
 *
 * Returns the feed slugs WordPress knows about (`rdf`, `rss`, `rss2`, `atom`
 * plus anything added via {@see add_feed()}), excluding the generic `feed`
 * rewrite base which is just an alias for the site default.
 *
 * @return string[] List of feed type slugs.
 */
function get_feed_types() {
	global $wp_rewrite;

	$feeds = ! empty( $wp_rewrite->feeds ) ? $wp_rewrite->feeds : array( 'rdf', 'rss', 'rss2', 'atom' );

	$feeds = \array_values( \array_diff( $feeds, array( 'feed' ) ) );

	/**
	 * Filters the list of feed types exposed in the feed listings.
	 *
	 * @param string[] $feeds List of feed type slugs.
	 */
	return \apply_filters( 'well_known_feed_types', $feeds );
}

/**
 * Collect the feeds associated with the blog.
 *
 * Emits every registered feed variant (see {@see get_feed_types()}) for each
 * endpoint: the theme's post-format archives (only those that actually have
 * content), all posts and all comments. Values are returned raw; consumers are
 * responsible for escaping for their output format.
 *
 * @param array $args Optional. Title/separator overrides.
 *
 * @return array[] List of feed descriptors (`text`, `description`, `href`, `version`).
 */
function get_blog_feeds( $args = array() ) {
	$defaults = array(
		/* translators: Separator between blog name and feed type in feed links */
		'separator'     => \_x( '-', 'feed link', 'wellknownfeeds' ),
		/* translators: 1: blog name, 2: separator(raquo), 3: post type */
		'posttypetitle' => \__( '%1$s Post-Type %2$s %3$s Feed', 'wellknownfeeds' ),
		/* translators: 1: Site title, 2: Separator (raquo). */
		'feedtitle'     => \__( '%1$s %2$s %3$s Feed', 'wellknownfeeds' ),
		/* translators: 1: Site title, 2: Separator (raquo). */
		'comstitle'     => \__( 'Comments %1$s %2$s Feed', 'wellknownfeeds' ),
	);

	$args       = \wp_parse_args( $args, $defaults );
	$feeds      = array();
	$feed_types = get_feed_types();

	// Theme-supported post formats, plus the synthetic "standard" bucket.
	$post_formats   = \get_theme_support( 'post-formats' );
	$post_formats   = $post_formats ? \current( $post_formats ) : array();
	$post_formats[] = 'standard';

	foreach ( $post_formats as $post_format ) {
		// Only advertise formats that actually have an archive, i.e. content.
		if ( ! get_post_format_link( $post_format ) ) {
			continue;
		}

		$label = \get_post_format_string( $post_format );

		foreach ( $feed_types as $type ) {
			$href = get_post_format_archive_feed_link( $post_format, $type );

			if ( ! $href ) {
				continue;
			}

			$feeds[] = array(
				'text'        => $label,
				'description' => \sprintf( $args['posttypetitle'], $label, $args['separator'], \strtoupper( $type ) ),
				'href'        => $href,
				'version'     => $type,
			);
		}
	}

	$all_posts    = \__( 'All Posts', 'wellknownfeeds' );
	$all_comments = \__( 'All Comments', 'wellknownfeeds' );

	foreach ( $feed_types as $type ) {
		$feeds[] = array(
			'text'        => $all_posts,
			'description' => \sprintf( $args['feedtitle'], $all_posts, $args['separator'], \strtoupper( $type ) ),
			'href'        => \get_feed_link( $type ),
			'version'     => $type,
		);

		$feeds[] = array(
			'text'        => $all_comments,
			'description' => \sprintf( $args['comstitle'], $args['separator'], \strtoupper( $type ) ),
			'href'        => \get_feed_link( 'comments_' . $type ),
			'version'     => $type,
		);
	}

	return $feeds;
}

/**
 * Build the "feed menu" structure as defined in draft-nottingham-feed-menu-00.
 *
 * All registered variants of the same feed are merged into a single feed
 * object: the RSS family under the spec's `rss` member (preferring RSS 2.0),
 * plus `atom` and any other registered type (json, as1, as2, …) under a member
 * named after its slug. Clients ignore members they do not recognise.
 *
 * @see https://www.ietf.org/archive/id/draft-nottingham-feed-menu-00.html
 *
 * @param array $args Optional. Arguments passed on to {@see get_blog_feeds()}.
 *
 * @return array The feed menu object.
 */
function get_feed_menu( $args = array() ) {
	$items = array();

	foreach ( get_grouped_feeds( $args ) as $title => $variants ) {
		$item = array( 'feed-title' => $title );

		foreach ( $variants as $feed ) {
			// The spec defines `rss` and `atom` members and tells clients to
			// ignore members they do not recognise, so map the RSS family (rss,
			// rss2, rdf) to the `rss` member and expose every other registered
			// feed type (atom, json, as1, as2, …) under a member named after its
			// slug.
			$key = \in_array( $feed['version'], array( 'rss', 'rss2', 'rdf' ), true ) ? 'rss' : $feed['version'];

			// Prefer RSS 2.0 for the `rss` member; other RSS variants only fill
			// an empty slot.
			if ( 'rss' === $key && isset( $item['rss'] ) && 'rss2' !== $feed['version'] ) {
				continue;
			}

			$item[ $key ] = $feed['href'];
		}

		$items[] = $item;
	}

	$menu = array(
		'$schema'   => \plugins_url( 'feed-menu-schema.json', __FILE__ ),
		/* translators: %s: Site title. */
		'feed-menu' => \sprintf( \__( '%s Feeds', 'wellknownfeeds' ), \get_bloginfo( 'name' ) ),
		'items'     => $items,
	);

	/**
	 * Filters the feed menu object before it is served.
	 *
	 * @param array $menu The feed menu object.
	 */
	return \apply_filters( 'well_known_feed_menu', $menu );
}

/**
 * Group the blog feeds by their source title.
 *
 * Each group collects the registered feed variants (RSS, Atom, JSON, …) that
 * point at the same content, so the OPML document can nest them under a single
 * parent outline, mirroring the JSON feed menu's feed objects.
 *
 * @param array $args Optional. Passed on to {@see get_blog_feeds()}.
 *
 * @return array[] Map of source title => list of feed variant descriptors.
 */
function get_grouped_feeds( $args = array() ) {
	$groups = array();

	foreach ( get_blog_feeds( $args ) as $feed ) {
		$groups[ $feed['text'] ][] = $feed;
	}

	return $groups;
}

/**
 * Adds support for "standard" Post-Format
 *
 * @param string $post_format the post format slug
 *
 * @return void
 */
function get_post_format_archive_feed_link( $post_format, $feed = '' ) {
	$default_feed = \get_default_feed();
	if ( empty( $feed ) ) {
		$feed = $default_feed;
	}

	$link = get_post_format_link( $post_format );
	if ( ! $link ) {
		return false;
	}

	if ( \get_option( 'permalink_structure' ) ) {
		$link  = \trailingslashit( $link );
		$link .= 'feed/';
		if ( $feed !== $default_feed ) {
			$link .= "$feed/";
		}
	} else {
		$link = \add_query_arg( 'feed', $feed, $link );
	}

	/**
	 * Filters the post type archive feed link.
	 *
	 * @param string $link The post type archive feed link.
	 * @param string $feed Feed type. Possible values include 'rss2', 'atom'.
	 */
	return \apply_filters( 'post_format_archive_feed_link', $link, $feed );
}

/**
 * Resolve a post-format archive link.
 *
 * Delegates to core for real post formats, so a format without content (no
 * term yet) resolves to `false` and is skipped by the callers. Adds support for
 * the synthetic "standard" post-format, which core does not handle, by building
 * its archive link from the rewrite permastruct.
 *
 * @param string $post_format The post format slug.
 *
 * @return string|false The archive link, or false if it cannot be resolved.
 */
function get_post_format_link( $post_format ) {
	if ( 'standard' !== $post_format ) {
		return \get_post_format_link( $post_format );
	}

	global $wp_rewrite;

	$termlink = $wp_rewrite->get_extra_permastruct( 'post_format' );

	if ( empty( $termlink ) ) {
		$termlink = \home_url( '?post_format=' . \rawurlencode( 'standard' ) );
	} else {
		$termlink = \str_replace( '%post_format%', 'standard', $termlink );
		$termlink = \home_url( \user_trailingslashit( $termlink, 'category' ) );
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
	if ( ! array_key_exists( 'well-known', $wp->query_vars ) ) {
		return;
	}

	switch ( $wp->query_vars['well-known'] ) {
		case 'feeds':
			header( 'Content-Type: text/xml; charset=' . \get_option( 'blog_charset' ), true );

			\load_template( \plugin_dir_path( __FILE__ ) . '/well-known-feeds-template.php', true );
			exit;

		case 'feed-menu':
			header( 'Content-Type: application/json; charset=' . \get_option( 'blog_charset' ), true );

			echo \wp_json_encode( get_feed_menu(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
			exit;
	}
}
\add_action( 'parse_request', __NAMESPACE__ . '\parse_request' );

/**
 * Accept .well-known query variables.
 */
function query_vars( $vars ) {
	$vars[] = 'well-known';
	return $vars;
}
\add_action( 'query_vars', __NAMESPACE__ . '\query_vars' );

/**
 * Add rewrite rules for .well-known/feeds.
 */
function rewrite_rules() {
	add_rewrite_rule( '^.well-known/feed-menu\.json$', 'index.php?well-known=feed-menu', 'top' );
	add_rewrite_rule( '^.well-known/feeds', 'index.php?well-known=feeds', 'top' );
}
\add_action( 'init', __NAMESPACE__ . '\rewrite_rules', 15 );

/**
 * Add rewrite rules for .well-known/feeds.
 */
function flush_rewrite_rules() {
	namespace\rewrite_rules();
	\flush_rewrite_rules();
}
\register_activation_hook( __FILE__, __NAMESPACE__ . '\flush_rewrite_rules' );
\register_deactivation_hook( __FILE__, __NAMESPACE__ . '\flush_rewrite_rules' );
