/**
 * Feed Form block: editor side.
 *
 * Plain JS without a build step. The form itself is rendered on the server,
 * the editor only shows a (disabled) preview and the source setting.
 */
( function ( wp ) {
	var el = wp.element.createElement;
	var __ = wp.i18n.__;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var RangeControl = wp.components.RangeControl;
	var Disabled = wp.components.Disabled;
	var ServerSideRender = wp.serverSideRender;

	wp.blocks.registerBlockType( 'well-known-feeds/feed-form', {
		edit: function ( props ) {
			return el(
				'div',
				useBlockProps(),
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Settings', 'wellknownfeeds' ) },
						el( SelectControl, {
							label: __( 'Feeds of', 'wellknownfeeds' ),
							value: props.attributes.source,
							options: [
								{ value: 'category', label: __( 'Categories', 'wellknownfeeds' ) },
								{ value: 'post_tag', label: __( 'Tags', 'wellknownfeeds' ) },
								{ value: 'post_format', label: __( 'Post formats', 'wellknownfeeds' ) },
							],
							onChange: function ( source ) {
								props.setAttributes( { source: source } );
							},
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true,
						} ),
						'post_tag' === props.attributes.source &&
							el( RangeControl, {
								label: __( 'Number of tags', 'wellknownfeeds' ),
								help: __( 'The most used tags, sorted by name.', 'wellknownfeeds' ),
								value: props.attributes.tagLimit,
								min: 5,
								max: 200,
								onChange: function ( tagLimit ) {
									props.setAttributes( { tagLimit: tagLimit } );
								},
								__nextHasNoMarginBottom: true,
								__next40pxDefaultSize: true,
							} )
					)
				),
				el(
					Disabled,
					null,
					el( ServerSideRender, {
						block: 'well-known-feeds/feed-form',
						attributes: props.attributes,
					} )
				)
			);
		},
	} );
} )( window.wp );
