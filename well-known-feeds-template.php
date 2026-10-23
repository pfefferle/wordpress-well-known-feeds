<?php echo '<?xml version="1.0"?>' . PHP_EOL; ?>

<opml version="1.0">
<?php
/*
 * The script shows the OPML as a page in the browser. Translations are
 * loaded the WordPress way: the JSON of the script is passed in a script
 * element with the id "well-known-feeds-translations".
 */
$well_known_feeds_scripts      = \Well_Known_Feeds\get_script_urls();
$well_known_feeds_translations = load_script_textdomain( \Well_Known_Feeds\SCRIPT, 'wellknownfeeds' );

if ( $well_known_feeds_translations ) {
	printf(
		"\t" . '<script xmlns="http://www.w3.org/1999/xhtml" type="application/json" id="well-known-feeds-translations">%s</script>' . "\n",
		htmlspecialchars( $well_known_feeds_translations, ENT_XML1 | ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped for XML.
	);
}

foreach ( $well_known_feeds_scripts as $well_known_feeds_src ) {
	printf(
		"\t" . '<script xmlns="http://www.w3.org/1999/xhtml" src="%s" defer=""/>' . "\n", // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- An OPML document has no wp_head().
		htmlspecialchars( $well_known_feeds_src, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped for XML.
	);
}
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