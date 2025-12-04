jQuery( function ( $ ) {
	'use strict';

	let Limiter = function ( $el ) {
		this.$el = $el;
	};

	Limiter.prototype = {
		// Initialize everything.
		init: function () {
			this.initElements();
			this.addListeners();
			this.$input.trigger( 'input' );
		},

		// Initialize elements.
		initElements: function () {
			this.$input = this.$el.siblings( '.rwmb-text' );
			let textWrap = this.$el.siblings( '.rwmb-input-group' );
			if ( textWrap.length > 0 ) {
				this.$input = textWrap.find( '.rwmb-text' );
			}
			if ( !this.$input.length ) {
				this.$input = this.$el.siblings( '.rwmb-textarea' );
			}

			this.isTinymce = false;
			if ( !this.$input.length ) {
				let tmce = this.$el.siblings( '.tmce-active' ).contents().filter( '.wp-editor-container' ).contents().filter( 'textarea' );

				if ( tmce.length > 0 ) {
					// wysiwyg in tmce mode
					this.$tmceEditorId = tmce[ 0 ].id;
					this.$input = $( '#' + this.$tmceEditorId );
					this.isTinymce = true;
				} else {
					// wysiwyg in html mode
					this.$input = this.$el.siblings( '.html-active' ).contents().filter( '.wp-editor-container' ).contents().filter( 'textarea' );
				}
				this.switchBtn = this.$el.siblings( '.wp-editor-wrap' ).contents().filter( '.wp-editor-tools' ).contents().filter( '.wp-editor-tabs' ).contents().filter( '.wp-switch-editor' );
			}

			this.$counter = this.$el.find( '.counter' );

			this.type = this.$el.data( 'limit-type' );
			this.max = parseInt( this.$el.find( '.maximum' ).text() );
		},

		// Add event listeners for 'input'.
		addListeners: function () {
			const that = this;

			if ( !that.isTinymce ) {
				this.$input.on( 'input', function () {
					let value = this.value,
						length = that.count( value, that.type );

					if ( length > that.max && !that.isTinymce ) {
						value = that.cut( that, value );
						length = that.max;
						this.value = value;
					}

					that.$counter.html( length );
				} );
			} else {
				this.$input.on( 'input change', function () {
					let tmceEditor = tinyMCE.get( that.$tmceEditorId );
					let value = tmceEditor ? tmceEditor.getContent() : '';
					let length = that.count( value, that.type );

					if ( length > that.max ) {
						value = that.cut( that, value );
						length = that.max;
						this.value = value;

						tinyMCE.get( that.$tmceEditorId ).setContent( value, { format: 'html' } );
						tinyMCE.activeEditor.selection.select( tinyMCE.activeEditor.getBody(), true );

						// set cursor to end of value
						tinyMCE.activeEditor.selection.collapse( false );
					}

					that.$counter.html( length );
				} );
			}
			if ( that.switchBtn ) {
				that.switchBtn.on( 'mouseup', function () {
					setTimeout( () => {
						that.initElements();
						that.addListeners();
						that.$input.trigger( 'input' );
					}, 200 );
				} );
			}
		},

		// Cut the content to the max length.
		cut: ( obj, content ) => {
			let length = obj.count( content, obj.type );

			while ( length > obj.max ) {
				content = content.substring( 0, content.length - 1 );
				length = obj.count( content, obj.type );
			}

			return content;
		},

		// Count the length of the content, without HTML.
		count: function ( content, type ) {
			// Remove HTML.
			content = content.replace( /<[^>]+>/ig, '' );

			if ( $.trim( content ) == '' ) {
				return 0;
			}

			return 'word' === type ? content.match( /\S+/g ).length : content.length;
		},

		// Get subString for text by word or characters.
		subStr: function ( val, start, len, type ) {
			if ( 'word' !== type ) {
				return val.substr( start, len );
			}

			var lastIndexSpace = val.lastIndexOf( ' ' );

			return val.substr( start, lastIndexSpace );
		}
	};

	function update() {
		$( '.text-limiter' ).each( function () {
			var $this = $( this ),
				controller = $this.data( 'limiterController' );
			if ( controller ) {
				return;
			}

			controller = new Limiter( $this );
			controller.init();
			$this.data( 'limiterController', controller );
		} );
	}

	update();
	$( '.rwmb-input' ).on( 'clone', update );
} );