( ( $, fields, rwmb, document ) => {
	var module = {
		timeout: undefined,

		// Load plugin and add hooks.
		load: () => {

			// Make sure clone fields are added.
			getClonedFields();

			// Update SEO By Rank Math analyzer when fields are updated.
			fields.map( module.listenToField );

			wp.hooks.addFilter( 'rank_math_content', 'rank-math', module.addContent );

			// Make the SEO By Rank Math analyzer works for existing content when page loads.
			module.update();

		},

		onClone: () => {
			setTimeout( () => {

				// Make sure clone fields are added.
				getClonedFields();

				// Update SEO By Rank Math analyzer when fields are updated.
				fields.map( module.listenToField );

			}, 500 );
		},
		// Add content to SEO By Rank Math Analyzer.
		addContent: ( content ) => {
			fields.map( ( fieldId ) => {
				content += ' ' + getFieldContent( fieldId );
			} );
			return content;
		},

		// Listen to field change and update SEO By Rank Math analyzer.
		listenToField: ( fieldId ) => {
			if ( isEditor( fieldId ) ) {
				tinymce.get( fieldId ).on( 'keyup', module.update );
				return;
			}
			var field = document.getElementById( fieldId );
			if ( field ) {
				field.addEventListener( 'keyup', module.update );
			}
		},

		// Update the SEO By Rank Math result. Use debounce technique, which triggers only when keys stop being pressed.
		update: () => {
			clearTimeout( module.timeout );
			module.timeout = setTimeout( () => {
				rankMathEditor.refresh( 'content' );
			}, 250 );
		},

		/**
		 * Add new cloned field to the list and listen to its change.
		 */
		addNewField: () => {
			if ( -1 === fields.indexOf( this.id ) ) {
				fields.push( this.id );
				module.listenToField( this.id );
			}
		}
	};

	/**
	 * Get clone fields.
	 */
	getClonedFields = () => {
		fields.map( ( fieldId ) => {
			var elements = document.querySelectorAll( '[id^=' + fieldId + '_]' );
			Array.prototype.forEach.call( elements, ( element ) => {
				if ( -1 === fields.indexOf( element.id ) ) {
					fields.push( element.id );
				}
			} );
		} );
	}

	/**
	 * Get field content.
	 * Works for normal inputs and TinyMCE editors.
	 *
	 * @param fieldId The field ID
	 * @returns string
	 */
	getFieldContent = ( fieldId ) => {
		var field = document.getElementById( fieldId );
		if ( field ) {
			var content = isEditor( fieldId ) ? tinymce.get( fieldId ).getContent() : field.value;
			return content ? content : '';
		}
		return '';
	}

	/**
	 * Check if the field is a TinyMCE editor.
	 *
	 * @param fieldId The field ID
	 * @returns boolean
	 */
	isEditor = ( fieldId ) => {
		return typeof tinymce !== 'undefined' && tinymce.get( fieldId ) !== null;
	}

	// Run on document ready.
	$( () => {
		setTimeout( () => {
			$( module.load );
		}, 500 );
	} );

	rwmb.$document
		.on( 'click', '.add-clone', module.onClone )
		.on( 'click', '.remove-clone', module.onClone );

} )( jQuery, MBRankMath, rwmb, document );
