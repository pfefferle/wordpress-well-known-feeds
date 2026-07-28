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