<?php echo '<?xml version="1.0"?>' . PHP_EOL; ?>
<?php printf( '<?xml-stylesheet type="text/xsl" href="%s"?>' . PHP_EOL, esc_url( plugins_url( 'well-known-feeds.xsl', __FILE__ ) ) ); ?>

<opml version="1.0">
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
		<outline text="Blog">
		<?php
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$feeds = \Well_Known_Feeds\get_blog_feeds();
		foreach ( (array) $feeds as $feed ) :
			?>
<outline text="<?php echo esc_attr( $feed['text'] ); ?>" description="<?php echo esc_attr( $feed['description'] ); ?>" type="rss" xmlUrl="<?php echo esc_url( $feed['href'] ); ?>" version="<?php echo esc_attr( strtoupper( $feed['version'] ) ); ?>"/>
			<?php
		endforeach; // $bookmarks
		?>
		</outline>
	</body>
</opml>