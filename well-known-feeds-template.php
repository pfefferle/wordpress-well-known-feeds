<?php echo '<?xml version="1.0"?>' . PHP_EOL; ?>
<?php printf( '<?xml-stylesheet type="text/xsl" href="%s"?>' . PHP_EOL, esc_url( plugins_url( 'well-known-feeds.xsl', __FILE__ ) ) ); ?>

<opml version="1.0">
<?php
/*
 * The script shows the OPML in browsers without XSLT. The labels are
 * translated here and passed to the script in a JSON script element.
 */
$well_known_feeds_l10n = array(
	'intro'       => sprintf(
		/* translators: %1$s: opening strong tag; %2$s: closing strong tag. */
		esc_html__( '%1$sThis website publishes web feeds.%2$s Feeds let you follow updates from a site in a feed reader.', 'wellknownfeeds' ),
		'<strong>',
		'</strong>'
	),
	'collects'    => __( 'This page collects the available feeds in one predictable place, so feed readers and other tools can find them without guessing.', 'wellknownfeeds' ),
	'choose'      => __( 'Choose a feed below or copy its URL into your feed reader.', 'wellknownfeeds' ),
	'aboutFeeds'  => sprintf(
		/* translators: %1$s: opening link tag to aboutfeeds.com; %2$s: closing link tag. */
		esc_html__( 'New to feeds? Learn more at %1$sAbout Feeds%2$s.', 'wellknownfeeds' ),
		'<a href="https://aboutfeeds.com/">',
		'</a>'
	),
	'icon'        => __( 'OPML icon', 'wellknownfeeds' ),
	/* translators: %s: Date the list was created. */
	'lastUpdated' => __( 'Last updated %s', 'wellknownfeeds' ),
	'subscribe'   => __( 'Subscribe', 'wellknownfeeds' ),
	'subscribeIn' => __( 'Subscribe in your feed reader', 'wellknownfeeds' ),
);

printf(
	"\t" . '<script xmlns="http://www.w3.org/1999/xhtml" type="application/json" id="well-known-feeds-l10n">%s</script>' . "\n",
	htmlspecialchars( wp_json_encode( $well_known_feeds_l10n ), ENT_XML1 | ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped for XML.
);

printf(
	"\t" . '<script xmlns="http://www.w3.org/1999/xhtml" src="%s" defer=""/>' . "\n", // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- An OPML document has no wp_head().
	esc_url( add_query_arg( 'ver', filemtime( __DIR__ . '/assets/js/well-known-feeds.js' ), plugins_url( 'assets/js/well-known-feeds.js', __FILE__ ) ) )
);
?>
	<!--
	Headlines dance in waves,
	RSS whispers news today,
	Words in cyberspace.
	-->
	<head>
		<?php /* translators: %s: Site title. */ ?>
		<title><?php printf( __( '%s Feeds', 'wellknownfeeds' ), esc_attr( get_bloginfo( 'name', 'display' ) ) ); ?></title>
		<dateCreated><?php echo gmdate( 'D, d M Y H:i:s' ); ?> GMT</dateCreated>
		<?php
		/**
		 * Fires in the OPML header.
		 *
		 * @since 3.0.0
		 */
		do_action( 'opml_head' );
		?>
	</head>
	<body>
		<?php
		$groups = \Well_Known_Feeds\get_grouped_feeds();
		foreach ( (array) $groups as $group_title => $variants ) :
			?>
		<outline text="<?php echo esc_attr( $group_title ); ?>">
			<?php
			foreach ( (array) $variants as $feed ) :
				$version = strtoupper( $feed['version'] );
				?>
			<outline text="<?php echo esc_attr( sprintf( '%1$s (%2$s)', $feed['text'], $version ) ); ?>" description="<?php echo esc_attr( $feed['description'] ); ?>" type="rss" xmlUrl="<?php echo esc_url( $feed['href'] ); ?>" version="<?php echo esc_attr( $version ); ?>"/>
			<?php endforeach; ?>
		</outline>
			<?php
		endforeach;
		?>
	</body>
</opml>