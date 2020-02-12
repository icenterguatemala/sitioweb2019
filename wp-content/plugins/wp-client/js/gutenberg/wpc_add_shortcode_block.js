/**
 * Gutenberg wp-client/add-shortcode-block
 */

wp.blocks.registerBlockType( 'wp-client/add-shortcode-block', {
	title: 'WP-Client Shortcodes',
	description: 'Add Shortcode or Placeholder',
	category: "widgets",
	icon: 'editor-paste-word',
	attributes: {
		text: {
			type: "string",
			source: "text"
		}
	},
	supports: {
		customClassName: !1,
		className: !1,
		html: !1
	},
	edit: function ( props ) {
		var content = wp.element.createElement( "div", {
			className: "wp-block-shortcode"
		},
				wp.element.createElement( "label", {
					for : "wpc_input_add_shortcode__".concat( props.clientId )
				},
						wp.element.createElement( 'button', {
							'className': "button",
							'data-editor': "wpc_input_add_shortcode__".concat( props.clientId ),
							'id': "wpc_add_shortcode__".concat( props.clientId ),
							'type': "button",
							style: {
								height: '3.7em'
							}
						},
								wp.element.createElement( 'img', {
									'src': "/wp-content/plugins/wp-client/client-icon.png",
									style: {
										position: 'relative',
										marginRight: '0.5em',
										top: '0.3em'
									}
								} ),
								"Add Shortcode" ) ),
				wp.element.createElement( "textarea", {
					className: "input-control",
					id: "wpc_input_add_shortcode__".concat( props.clientId ),
					placeholder: "Write shortcode here…",
					value: props.attributes.text,
					style: {
						height: '3.7em',
						overflowWrap: 'break-word',
						resize: 'none',
						width: '100%'
					},
					onChange: function ( newContent ) {
						props.setAttributes( {
							text: typeof( newContent ) === 'string' ? newContent : newContent.target.value
						} );
						return;
					}
				} ) );
		return content;
	},
	save: function ( props ) {
		var content = wp.element.createElement( wp.element.RawHTML, null, props.attributes.text );
		return content;
	}
} );