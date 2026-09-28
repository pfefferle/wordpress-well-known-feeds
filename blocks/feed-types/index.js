/**
 * Feed Types block: editor side.
 *
 * Plain JS without a build step. The list itself is rendered on the server,
 * the editor only shows a preview and a checkbox per registered feed type.
 */
( function ( wp ) {
	var el = wp.element.createElement;
	var __ = wp.i18n.__;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var CheckboxControl = wp.components.CheckboxControl;
	var ServerSideRender = wp.serverSideRender;

	// `{ slug: label }`, added by the plugin via wp_add_inline_script().
	var feedTypes = window.wellKnownFeedsTypes || {};

	wp.blocks.registerBlockType( 'well-known-feeds/feed-types', {
		edit: function ( props ) {
			var selected = props.attributes.types;

			function toggle( type, checked ) {
				// An empty selection means "all types", so start from the full list.
				var current = selected.length ? selected : Object.keys( feedTypes );
				var types = checked
					? current.concat( [ type ] )
					: current.filter( function ( t ) {
						return t !== type;
					} );

				// Keep the registered order, and fall back to "all" when everything is checked.
				types = Object.keys( feedTypes ).filter( function ( t ) {
					return types.indexOf( t ) !== -1;
				} );

				props.setAttributes( {
					types: types.length === Object.keys( feedTypes ).length ? [] : types,
				} );
			}

			return el(
				'div',
				useBlockProps(),
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Feed types', 'wellknownfeeds' ) },
						Object.keys( feedTypes ).map( function ( type ) {
							return el( CheckboxControl, {
								key: type,
								label: feedTypes[ type ],
								checked: ! selected.length || selected.indexOf( type ) !== -1,
								// An empty list would mean "all" again, so keep at least one.
								disabled: selected.length === 1 && selected[ 0 ] === type,
								onChange: function ( checked ) {
									toggle( type, checked );
								},
								__nextHasNoMarginBottom: true,
							} );
						} )
					)
				),
				el( ServerSideRender, {
					block: 'well-known-feeds/feed-types',
					attributes: props.attributes,
				} )
			);
		},
	} );
} )( window.wp );
