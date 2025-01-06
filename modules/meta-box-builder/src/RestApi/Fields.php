<?php
namespace MBB\RestApi;

class Fields extends Base {
	private $registry;

	public function __construct( $registry ) {
		parent::__construct();
		$this->registry = $registry;
	}

	public function get_fields_ids(): array {
		$args  = [
			'post_type'              => 'meta-box',
			'posts_per_page'         => -1, // Retrieve all posts
			'update_post_term_cache' => false,
			'no_found_rows'          => true,
		];
		$posts = get_posts( $args );

		$fields = [];
		foreach ( $posts as $post ) {
			$value       = [
				'link'  => get_edit_post_link( $post ),
				'title' => $post->post_title,
			];
			$post_fields = get_post_meta( $post->ID, 'fields', true ) ?: [];

			foreach ( $post_fields as $field ) {
				if ( ! empty( $field['id'] ) ) {
					$fields[ $field['id'] ] = $value;
				}
			}
		}

		return $fields;
	}

	public function get_field_categories() {
		$categories = [
			[
				'slug'  => 'basic',
				'title' => __( 'Basic', 'meta-box-builder' ),
			],
			[
				'slug'  => 'advanced',
				'title' => __( 'Advanced', 'meta-box-builder' ),
			],
			[
				'slug'  => 'html5',
				'title' => __( 'HTML5', 'meta-box-builder' ),
			],
			[
				'slug'  => 'wordpress',
				'title' => __( 'WordPress', 'meta-box-builder' ),
			],
			[
				'slug'  => 'upload',
				'title' => __( 'Upload', 'meta-box-builder' ),
			],
			[
				'slug'  => 'layout',
				'title' => __( 'Layout', 'meta-box-builder' ),
			],
		];

		$categories = apply_filters( 'mbb_field_categories', $categories );

		return $categories;
	}

	public function get_field_types() {
		$this->registry->register_default_controls();

		$general  = [ 'name', 'id', 'type', 'label_description', 'desc' ];
		$advanced = [ 'before', 'after', 'class', 'sanitize_callback', 'save_field', 'attributes', 'validation', 'custom_settings' ];
		$clone    = [ 'clone', 'sort_clone', 'clone_default', 'clone_as_multiple', 'clone_empty_start', 'min_clone', 'max_clone', 'add_button' ];
		$date     = [ 'std', 'placeholder', 'size', 'save_format', 'timestamp', 'inline', 'required', 'disabled', 'readonly', 'js_options' ];
		$map      = [ 'std', 'address_field', 'language', 'region', 'required' ];
		$taxonomy = [ 'taxonomy', 'field_type', 'placeholder', 'add_new', 'remove_default', 'multiple', 'select_all_none', 'required', 'query_args' ];
		$post     = [ 'post_type', 'field_type', 'add_new', 'multiple', 'select_all_none', 'parent', 'required', 'placeholder', 'query_args' ];
		$user     = [ 'field_type', 'placeholder', 'add_new', 'multiple', 'select_all_none', 'required', 'query_args' ];
		$upload   = [ 'max_file_uploads', 'max_status', 'force_delete', 'required' ];
		$input    = [ 'required', 'disabled', 'readonly' ];
		$html5    = [ 'std', 'placeholder', 'size', 'required', 'disabled', 'readonly' ];
		$icon     = [ 'icon_set', 'icon_file', 'icon_dir', 'icon_css' ];

		$field_types = [
			'autocomplete'      => [
				'title'       => __( 'Autocomplete', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge( $general, [ 'options', 'size' ], $clone, $advanced ),
				'description' => __( 'Text input that uses an autocomplete library to suggest user input. Not recommended. Use the Select or Select advanced field type instead.', 'meta-box-builder' ),
			],
			'background'        => [
				'title'       => __( 'Background', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge( $general, $clone, $advanced ),
				'description' => __( 'Set background properties', 'meta-box-builder' ),
			],
			'button'            => [
				'title'       => __( 'Button', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge( $general, [ 'std', 'disabled' ], $advanced ),
				'description' => __( 'A simple button, usually used for JavaScript triggers', 'meta-box-builder' ),
			],
			'button_group'      => [
				'title'       => __( 'Button Group', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge( $general, [ 'options', 'std', 'inline', 'multiple', 'required' ], $clone, $advanced ),
				'description' => __( 'Select one or multiple choices by enabling button(s) from a group', 'meta-box-builder' ),
			],
			'checkbox'          => [
				'title'       => __( 'Checkbox', 'meta-box-builder' ),
				'category'    => 'basic',
				'controls'    => array_merge( $general, [ 'std', 'required' ], $clone, $advanced ),
				'description' => __( 'A simple checkbox, usually used for Yes/No question', 'meta-box-builder' ),
			],
			'checkbox_list'     => [
				'title'       => __( 'Checkbox List', 'meta-box-builder' ),
				'category'    => 'basic',
				'controls'    => array_merge( $general, [ 'options', 'std', 'inline', 'select_all_none', 'required' ], $clone, $advanced ),
				'description' => __( 'A list of checkboxes where you can select multiple choices', 'meta-box-builder' ),
			],
			'color'             => [
				'title'       => __( 'Color Picker', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge( $general, [ 'std', 'js_options', 'alpha_channel' ], $input, $clone, $advanced ),
				'description' => __( 'Color picker', 'meta-box-builder' ),
			],
			'custom_html'       => [
				'title'       => __( 'Custom HTML', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge( array_diff( $general, [ 'id' ] ), [ 'std', 'callback' ], $advanced ),
				'description' => __( 'Output custom HTML content', 'meta-box-builder' ),
			],
			'date'              => [
				'title'       => __( 'Date Picker', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge( $general, $date, $clone, $advanced ),
				'description' => __( 'Date picker', 'meta-box-builder' ),
			],
			'datetime'          => [
				'title'       => __( 'Datetime Picker', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge( $general, $date, $clone, $advanced ),
				'description' => __( 'Date and time picker', 'meta-box-builder' ),
			],
			'divider'           => [
				'title'       => __( 'Divider', 'meta-box-builder' ),
				'category'    => 'layout',
				'controls'    => [ 'type', 'before', 'after' ],
				'description' => __( 'Simple horizontal line', 'meta-box-builder' ),
			],
			'email'             => [
				'title'       => __( 'Email', 'meta-box-builder' ),
				'category'    => 'html5',
				'controls'    => array_merge( $general, $html5, [ 'prepend', 'append' ], $clone, $advanced ),
				'description' => __( 'For entering an email address with browser validation', 'meta-box-builder' ),
			],
			'fieldset_text'     => [
				'title'       => __( 'Fieldset Text', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge( $general, [ 'options', 'required' ], $clone, $advanced ),
				'description' => __( 'Group of text inputs. Not recommended. Use the Group field type instead.', 'meta-box-builder' ),
			],
			'file'              => [
				'title'       => __( 'File', 'meta-box-builder' ),
				'category'    => 'upload',
				'controls'    => array_merge( $general, [ 'max_file_uploads', 'force_delete', 'upload_dir', 'required' ], $clone, $advanced ),
				'description' => __( 'Simple file upload with default UI like <input type="file" />. Not recommended. Use File advanced instead.', 'meta-box-builder' ),
			],
			'file_advanced'     => [
				'title'       => __( 'File Advanced', 'meta-box-builder' ),
				'category'    => 'upload',
				'controls'    => array_merge( $general, [ 'mime_type' ], $upload, $clone, $advanced ),
				'description' => __( 'Multiple file uploads with WordPress media popup', 'meta-box-builder' ),
			],
			'file_input'        => [
				'title'       => __( 'File Input', 'meta-box-builder' ),
				'category'    => 'upload',
				'controls'    => array_merge( $general, $html5, $clone, $advanced ),
				'description' => __( 'A text input for entering a file URL with the ability to select a file from the Media Library', 'meta-box-builder' ),
			],
			'file_upload'       => [
				'title'       => __( 'File Upload', 'meta-box-builder' ),
				'category'    => 'upload',
				'controls'    => array_merge( $general, [ 'mime_type', 'max_file_size' ], $upload, $clone, $advanced ),
				'description' => __( 'Multiple file uploads with a drag and drop area', 'meta-box-builder' ),
			],
			'map'               => [
				'title'       => __( 'Google Maps', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge( $general, [ 'api_key' ], $map, $clone, $advanced ),
				'description' => __( 'Google Maps', 'meta-box-builder' ),
			],
			'heading'           => [
				'title'       => __( 'Heading', 'meta-box-builder' ),
				'category'    => 'layout',
				'controls'    => array_merge( [ 'type', 'name', 'desc' ], $advanced ),
				'description' => __( 'Heading text', 'meta-box-builder' ),
			],
			'hidden'            => [
				'title'       => __( 'Hidden', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge( [ 'id', 'type', 'std' ], $advanced ),
				'description' => __( 'For storing a default hidden value', 'meta-box-builder' ),
			],
			'icon'              => [
				'title'       => __( 'Icon', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge( $general, [ 'std', 'placeholder' ], $input, $clone, $advanced, $icon ),
				'description' => __( 'Icon with FontAwesome set', 'meta-box-builder' ),
			],
			'image'             => [
				'title'       => __( 'Image', 'meta-box-builder' ),
				'category'    => 'upload',
				'controls'    => array_merge( $general, [ 'max_file_uploads', 'force_delete', 'upload_dir', 'required' ], $clone, $advanced ),
				'description' => __( 'Simple image upload with default UI like <input type="file" />. Not recommended. Use Image advanced instead.', 'meta-box-builder' ),
			],
			'image_advanced'    => [
				'title'       => __( 'Image Advanced', 'meta-box-builder' ),
				'category'    => 'upload',
				'controls'    => array_merge( $general, $upload, [ 'image_size', 'add_to' ], $clone, $advanced ),
				'description' => __( 'Multiple image uploads with WordPress media popup, usually used for a gallery', 'meta-box-builder' ),
			],
			'image_select'      => [
				'title'       => __( 'Image Select', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge( $general, [ 'options', 'std', 'multiple', 'required' ], $clone, $advanced ),
				'description' => __( 'Select a choice with images', 'meta-box-builder' ),
			],
			'image_upload'      => [
				'title'       => __( 'Image Upload', 'meta-box-builder' ),
				'category'    => 'upload',
				'controls'    => array_merge( $general, [ 'max_file_size', 'image_size', 'add_to' ], $upload, $clone, $advanced ),
				'description' => __( 'Multiple image uploads with a drag and drop area', 'meta-box-builder' ),
			],
			'key_value'         => [
				'title'       => __( 'Key Value', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge( $general, [ 'placeholder_key', 'placeholder_value', 'required' ], $advanced ),
				'description' => __( 'Add an unlimited group of key-value pairs', 'meta-box-builder' ),
			],
			'number'            => [
				'title'       => __( 'Number', 'meta-box-builder' ),
				'category'    => 'html5',
				'controls'    => array_merge( $general, [ 'min', 'max', 'step' ], $html5, [ 'prepend', 'append' ], $clone, $advanced ),
				'description' => __( 'For entering a number with browser validation', 'meta-box-builder' ),
			],
			'oembed'            => [
				'title'       => __( 'oEmbed', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge( $general, $html5, [ 'not_available_string' ], $clone, $advanced ),
				'description' => __( 'Input for media from Youtube, Vimeo, and all supported sites by WordPress', 'meta-box-builder' ),
			],
			'osm'               => [
				'title'       => __( 'Open Street Maps', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge( $general, $map, $clone, $advanced ),
				'description' => __( 'Open Street Maps', 'meta-box-builder' ),
			],
			'password'          => [
				'title'       => __( 'Password', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge( $general, $html5, $clone, $advanced ),
				'description' => __( 'For entering a password', 'meta-box-builder' ),
			],
			'post'              => [
				'title'       => __( 'Post', 'meta-box-builder' ),
				'category'    => 'wordpress',
				'controls'    => array_merge( $general, $post, $clone, $advanced ),
				'description' => __( 'For selecting posts', 'meta-box-builder' ),
			],
			'radio'             => [
				'title'       => __( 'Radio', 'meta-box-builder' ),
				'category'    => 'basic',
				'controls'    => array_merge( $general, [ 'options', 'std', 'inline', 'required' ], $clone, $advanced ),
				'description' => __( 'Radio input where you can select only one choice', 'meta-box-builder' ),
			],
			'range'             => [
				'title'       => __( 'Range', 'meta-box-builder' ),
				'category'    => 'html5',
				'controls'    => array_merge( $general, [ 'std', 'min', 'max', 'step' ], $input, $clone, $advanced ),
				'description' => __( 'A slider for selecting a number', 'meta-box-builder' ),
			],
			'select'            => [
				'title'       => __( 'Select', 'meta-box-builder' ),
				'category'    => 'basic',
				'controls'    => array_merge( $general, [ 'options', 'std', 'placeholder', 'multiple', 'select_all_none' ], $input, $clone, $advanced ),
				'description' => __( 'Select dropdown where you can select one or multiple choice', 'meta-box-builder' ),
			],
			'select_advanced'   => [
				'title'       => __( 'Select Advanced', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge( $general, [ 'options', 'std', 'placeholder', 'multiple', 'select_all_none' ], $input, [ 'js_options' ], $clone, $advanced ),
				'description' => __( 'Beautiful select dropdown using select2 library', 'meta-box-builder' ),
			],
			'sidebar'           => [
				'title'       => __( 'Sidebar', 'meta-box-builder' ),
				'category'    => 'wordpress',
				'controls'    => array_merge( $general, [ 'std', 'placeholder', 'field_type' ], $input, $clone, $advanced ),
				'description' => __( 'For selecting sidebars', 'meta-box-builder' ),
			],
			'single_image'      => [
				'title'       => __( 'Single Image', 'meta-box-builder' ),
				'category'    => 'upload',
				'controls'    => array_merge( $general, [ 'force_delete', 'image_size', 'required' ], $clone, $advanced ),
				'description' => __( 'Single image upload with WordPress media popup', 'meta-box-builder' ),
			],
			'slider'            => [
				'title'       => __( 'jQuery UI Slider', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge( $general, [ 'std', 'prefix', 'suffix', 'required', 'js_options' ], $clone, $advanced ),
				'description' => __( 'jQuery UI slider', 'meta-box-builder' ),
			],
			'switch'            => [
				'title'       => __( 'Switch', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge( $general, [ 'style', 'on_label', 'off_label', 'std', 'required' ], $clone, $advanced ),
				'description' => __( 'On/off switch with iOS style', 'meta-box-builder' ),
			],
			'taxonomy'          => [
				'title'       => __( 'Taxonomy', 'meta-box-builder' ),
				'category'    => 'wordpress',
				'controls'    => array_merge( $general, $taxonomy, $advanced ),
				'description' => __( 'For selecting taxonomy terms. Doesn\'t save term IDs in post meta, but set post terms.', 'meta-box-builder' ),
			],
			'taxonomy_advanced' => [
				'title'       => __( 'Taxonomy Advanced', 'meta-box-builder' ),
				'category'    => 'wordpress',
				'controls'    => array_merge( $general, $taxonomy, $clone, $advanced ),
				'description' => __( 'For selecting taxonomy terms and saving term IDs in post meta as a comma-separated string. It doesn\'t set post terms.', 'meta-box-builder' ),
			],
			'text'              => [
				'title'       => __( 'Text', 'meta-box-builder' ),
				'category'    => 'basic',
				'controls'    => array_merge( $general, $html5, [ 'prepend', 'append', 'datalist_choices' ], $clone, $advanced ),
				'description' => __( 'A single-line text input', 'meta-box-builder' ),
			],
			'text_list'         => [
				'title'       => __( 'Text List', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge( $general, [ 'options', 'required' ], $clone, $advanced ),
				'description' => __( 'Group of text inputs. Similar to Fieldset text, but has a different UI. Not recommended. Use the Group field type instead.', 'meta-box-builder' ),
			],
			'textarea'          => [
				'title'       => __( 'Textarea', 'meta-box-builder' ),
				'category'    => 'basic',
				'controls'    => array_merge( $general, [ 'std', 'placeholder', 'rows', 'cols' ], $input, $clone, $advanced ),
				'description' => __( 'A paragraph text input', 'meta-box-builder' ),
			],
			'time'              => [
				'title'       => __( 'Time Picker', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge( $general, array_diff( $date, [ 'timestamp' ] ), $clone, $advanced ),
				'description' => __( 'Time picker', 'meta-box-builder' ),
			],
			'user'              => [
				'title'       => __( 'User', 'meta-box-builder' ),
				'category'    => 'wordpress',
				'controls'    => array_merge( $general, $user, $clone, $advanced ),
				'description' => __( 'For selecting users', 'meta-box-builder' ),
			],
			'url'               => [
				'title'       => __( 'URL', 'meta-box-builder' ),
				'category'    => 'html5',
				'controls'    => array_merge( $general, $html5, [ 'prepend', 'append' ], $clone, $advanced ),
				'description' => __( 'An input for URL with browser validation', 'meta-box-builder' ),
			],
			'video'             => [
				'title'       => __( 'Video', 'meta-box-builder' ),
				'category'    => 'upload',
				'controls'    => array_merge( $general, $upload, $clone, $advanced ),
				'description' => __( 'Multiple video uploads with WordPress media popup', 'meta-box-builder' ),
			],
			'wysiwyg'           => [
				'title'       => __( 'WYSIWYG Editor', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge( $general, [ 'std', 'raw', 'options', 'required' ], $clone, $advanced ),
				'description' => __( 'WordPress editor', 'meta-box-builder' ),
			],
		];

		$field_types = apply_filters( 'mbb_field_types', $field_types );

		foreach ( $field_types as $type => $field_type ) {
			$field_type['controls'] = apply_filters( 'mbb_field_controls', $field_type['controls'], $type );
			$this->registry->add_field_type( $type, $field_type );
		}

		$this->registry->transform_controls();

		return $this->registry->get_field_types();
	}
}
