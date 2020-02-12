jQuery( function ( $ ) {

	$( document ).ready( function () {
		$( document.body ).on( 'click', '[id^="wpc_add_shortcode"]', function () {
			var shutterBox = $( this );
			shutterBox.shutter_box( {
				view_type: 'lightbox',
				width: '800px',
				height: '1500px',
				class: 'wpc_shortcodes_and_placeholders_box',
				type: 'ajax',
				dataType: 'json',
				href: wpc_var.ajax_url,
				ajax_data: "action=wpc_get_shortcodes_and_placeholders",
				self_init: false,
				setAjaxResponse: function ( data ) {
					$( '.sb_lightbox_content_title' ).html( data.title );
					$( '.sb_lightbox_content_body' ).html( data.content );
					$( '#wpc_shortcodes_and_placeholders .wpc_accordion' ).accordion( {
						heightStyle: "content",
					} );
				}
			} );
			$( 'body' ).data( 'add_shortcode_popup', shutterBox );
			shutterBox.shutter_box( 'show' );
		} );
	} );

	//Choice shortcode
	$( 'body' ).on( 'click', '#wpc_list_shortcodes .wpc_accordion_link', function () {

		$( '.action_buttons_block' ).show();
		$( '.add_shortcode_button' ).show();
		$( '.add_placeholder_button' ).hide();

		$( '.wpc_accordion_link.wpc_accordion_link_active' ).removeClass( 'wpc_accordion_link_active' );
		$( this ).addClass( 'wpc_accordion_link_active' );

		var name = $( this ).data( 'name' );
		if ( undefined !== name && '' !== name ) {
			$.ajax( {
				type: 'POST',
				url: wpc_var.ajax_url,
				data: 'action=wpc_get_shortcode_attributes_form&shortcode=' + name,
				dataType: "json",
				success: function ( data ) {
					$( 'div.wpc_right_side_content' ).html( data );
					$( '#wpc_item_name' ).val( name );
				},
				error: function ( data ) {
					$( 'div.wpc_right_side_content' ).html( 'Something wrong. Please, try again.' );
					$( '#wpc_item_name' ).val( '' );
				}
			} );
		}
	} );

	//Add shortcode
	jQuery( document ).on( 'click', '.add_shortcode_button', function () {
		var attr_array = jQuery( this ).parents( 'form' ).values();
		var wpc_shortcode = jQuery( this ).parents( 'form' ).find( '#wpc_item_name' ).val();
		var attr_string = '';
		var temp_obj = { };
		for ( key in attr_array ) {
			if ( jQuery( 'div.wpc_right_side_content *[name="' + key + '"]' ).parent().is( ':visible' ) ) {
				var data_key = jQuery( 'div.wpc_right_side_content *[name="' + key + '"]' ).data( 'key' );
				if ( data_key == '' )
					continue;
				if ( typeof temp_obj[ data_key ] == 'undefined' ) {
					temp_obj[ data_key ] = [ ];
				}
				temp_obj[ data_key ].push( attr_array[ key ] );
			}
		}
		for ( key in temp_obj ) {
			attr_string += key + '="' + temp_obj[ key ].join( ',' ) + '" ';
		}
		var close_tag = '';
		if ( typeof wpc_shortcodes[ wpc_shortcode ].content != 'undefined' && wpc_shortcodes[ wpc_shortcode ].content != '' ) {
			close_tag = wpc_shortcodes[ wpc_shortcode ].content + '[/' + wpc_shortcode + ']';
		} else if ( typeof wpc_shortcodes[ wpc_shortcode ].close_tag != 'undefined' && wpc_shortcodes[ wpc_shortcode ].close_tag ) {
			close_tag = '[/' + wpc_shortcode + ']';
		}
		wpc_shortcode = '[' + wpc_shortcode + ' ' + attr_string + ( close_tag == '' ? '/' : '' ) + ']' + close_tag;

		var editor_id = $( 'body' ).data( 'add_shortcode_popup' ).data( 'editor' );

		if ( $( '#wp-' + editor_id + '-wrap' ).hasClass( 'tmce-active' ) ) {
			if ( typeof CKEDITOR != 'undefined' ) {
				//for CKE-Editor WP plugin compatibility
				CKEDITOR.instances[ editor_id ].insertText( wpc_shortcode );
			} else {
				var editor = tinymce.get( editor_id );
				if ( editor && editor instanceof tinymce.Editor ) {
					editor.insertContent( wpc_shortcode );
				}
			}
		} else if ( editor_id && /wpc_input_add_shortcode/i.test( editor_id ) ) {
			var editor = document.getElementById( editor_id );
			for ( var i in editor ) {
				if ( /__reactEventHandlers/i.test( i ) ) {
					editor[i].onChange( {
						target: { value: wpc_shortcode }
					} );
				}
			}
		} else {
			insertAtCursor( document.getElementById( editor_id ), wpc_shortcode );
		}

		$( 'body' ).data( 'add_shortcode_popup' ).shutter_box( 'close' );
	} );

	//Choice placeholder
	$( 'body' ).on( 'click', '#wpc_list_placeholders .wpc_accordion_link', function () {
		$( '.action_buttons_block' ).show();
		$( '.add_placeholder_button' ).show();
		$( '.add_shortcode_button' ).hide();

		$( '.wpc_accordion_link.wpc_accordion_link_active' ).removeClass( 'wpc_accordion_link_active' );
		$( this ).addClass( 'wpc_accordion_link_active' );

		var name = $( this ).data( 'name' );
		var description = $( this ).data( 'description' );
		if ( undefined !== name && '' !== name ) {
			$( 'div.wpc_right_side_content' ).html( '<div class="wpc_options">' + name + '</div>' );
			if ( undefined !== description && '' !== description ) {
				$( 'div.wpc_right_side_content' ).append( '<br><span class="description">' + description + '</span>' );
			}
			$( '.action_buttons_block' ).show();
			$( '#wpc_item_name' ).val( name );
		}
	} );

	//Add placeholder
	jQuery( document ).on( 'click', '.add_placeholder_button', function () {
		var wpc_placeholder = jQuery( this ).parents( 'form' ).find( '#wpc_item_name' ).val();
		var editor = tinymce.get( 'content' );
		var editor_id = $( 'body' ).data( 'add_shortcode_popup' ).data( 'editor' );

		if ( editor && editor instanceof tinymce.Editor && $( '#wp-content-wrap' ).hasClass( 'tmce-active' ) ) {
			editor.insertContent( wpc_placeholder );
		} else if ( editor_id && /wpc_input_add_shortcode/i.test( editor_id ) ) {
			var editor = document.getElementById( editor_id );
			for ( var i in editor ) {
				if ( /__reactEventHandlers/i.test( i ) ) {
					editor[i].onChange( {
						target: { value: wpc_placeholder }
					} );
				}
			}
		} else if ( jQuery( '#content' ).length ) {
			var content = jQuery( '#content' ).val();
			jQuery( '#content' ).val( content + wpc_placeholder );
		}
		$( 'body' ).data( 'add_shortcode_popup' ).shutter_box( 'close' );
	} );

	jQuery( document ).on( 'click', '.cancel_shortcode_button', function () {
		$( 'body' ).data( 'add_shortcode_popup' ).shutter_box( 'close' );
	} );

	jQuery( document ).on( 'change', '.wpc_attr_field', function () {
		var name = jQuery( this ).data( 'key' );
		var value = jQuery( this ).val();
		jQuery( '.wpc_has_parent_' + name ).parents( 'div.wpc_right_side_content tr' ).hide();
		jQuery( '.wpc_has_parent_' + name + '.' + name + ( typeof ( value ) == 'string' && value.length > 0 ? '_' + md5( value ) : '' ) ).parents( 'div.wpc_right_side_content tr' ).show();
	} );

	$( 'body' ).on( 'click', '#wpc_shortcodes_and_placeholders #wpc_placeholders', function () {
		$( '.wpc_left_side #wpc_list_shortcodes' ).css( 'display', 'none' );
		$( '.wpc_left_side #wpc_list_placeholders' ).css( 'display', 'block' );
		$( '.wpc_right_side_content' ).html( '' );
		$( '.action_buttons_block' ).hide();
		$( '.wpc_accordion_link.wpc_accordion_link_active' ).removeClass( 'wpc_accordion_link_active' );
	} );

	$( 'body' ).on( 'click', '#wpc_shortcodes_and_placeholders #wpc_shortcodes', function () {
		$( '.wpc_left_side #wpc_list_placeholders' ).css( 'display', 'none' );
		$( '.wpc_left_side #wpc_list_shortcodes' ).css( 'display', 'block' );
		$( '.wpc_right_side_content' ).html( '' );
		$( '.action_buttons_block' ).hide();
		$( '.wpc_accordion_link.wpc_accordion_link_active' ).removeClass( 'wpc_accordion_link_active' );
	} );

	function insertAtCursor( myField, myValue ) {
		//IE support
		if ( document.selection ) {
			myField.focus();
			sel = document.selection.createRange();
			sel.text = myValue;
		}
		//MOZILLA and others
		else if ( myField.selectionStart || myField.selectionStart == '0' ) {
			var startPos = myField.selectionStart;
			var endPos = myField.selectionEnd;
			myField.value = myField.value.substring( 0, startPos )
					+ myValue
					+ myField.value.substring( endPos, myField.value.length );
		} else {
			myField.value += myValue;
		}
	}

} );