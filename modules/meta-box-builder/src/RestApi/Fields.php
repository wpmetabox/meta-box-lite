<?php
namespace MBB\RestApi;

use WP_REST_Request;
use RWMB_Field;

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

		$ids = [];
		foreach ( $posts as $post ) {
			$value    = [
				'link'  => get_edit_post_link( $post ),
				'title' => $post->post_title,
			];
			$fields   = get_post_meta( $post->ID, 'fields', true ) ?: [];
			$settings = get_post_meta( $post->ID, 'settings', true ) ?: [];
			$prefix   = $settings['prefix'] ?? '';

			foreach ( $fields as $field ) {
				if ( empty( $field['id'] ) ) {
					continue;
				}
				// Final ID is the ID with the prefix.
				$field_id = $prefix . $field['id'];

				// Get _id to check if the same fields.
				$ids[ $field_id ] = array_merge( $value, [
					'_id' => $field['_id'] ?? ( $field['id'] ?? '' ),
				] );
			}
		}

		return $ids;
	}

	public function get_field_types() {
		$general_tab    = [ 'type', 'name', 'id' ];
		$appearance_tab = [ 'label_description', 'desc' ];
		$validation_tab = [ 'validation' ];
		$advanced_tab   = [ 'class', 'before', 'after', 'save_field', 'sanitize_callback', 'attributes', 'custom_settings' ];

		$field_types = [
			'autocomplete'      => [
				'title'       => __( 'Autocomplete', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge(
					[ 'clone_settings' ],
					array_merge( $general_tab, [ 'options' ] ),
					[ 'label_description', 'desc' ],
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'Text input that uses an autocomplete library to suggest user input. Not recommended. Use the Select or Select advanced field type instead.', 'meta-box-builder' ),
			],
			'background'        => [
				'title'       => __( 'Background', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge(
					[ 'clone_settings' ],
					$general_tab,
					$appearance_tab,
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'Set background properties', 'meta-box-builder' ),
			],
			'button'            => [
				'title'       => __( 'Button', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge(
					array_merge( $general_tab, [ 'std' ] ),
					$appearance_tab
				),
				'description' => __( 'A simple button, usually used for JavaScript triggers', 'meta-box-builder' ),
			],
			'button_group'      => [
				'title'       => __( 'Button Group', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'options', 'std', 'multiple' ] ),
					array_merge( [ 'inline' ], $appearance_tab ),
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'Select one or multiple choices by enabling button(s) from a group', 'meta-box-builder' ),
			],
			'checkbox'          => [
				'title'       => __( 'Checkbox', 'meta-box-builder' ),
				'category'    => 'basic',
				'controls'    => array_merge(
					[ 'required' ],
					array_merge( $general_tab, [ 'std' ] ),
					$appearance_tab,
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'A simple checkbox, usually used for Yes/No question', 'meta-box-builder' ),
			],
			'checkbox_list'     => [
				'title'       => __( 'Checkbox List', 'meta-box-builder' ),
				'category'    => 'basic',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'options', 'std' ] ),
					array_merge( [ 'inline', 'select_all_none' ], $appearance_tab ),
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'A list of checkboxes where you can select multiple choices', 'meta-box-builder' ),
			],
			'color'             => [
				'title'       => __( 'Color Picker', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'std', 'alpha_channel' ] ),
					$appearance_tab,
					$validation_tab,
					array_merge( [ 'js_options' ], $advanced_tab )
				),
				'description' => __( 'Color picker', 'meta-box-builder' ),
			],
			'custom_html'       => [
				'title'       => __( 'Custom HTML', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge(
					[ 'type', 'name', 'std', 'callback' ],
					$appearance_tab
				),
				'description' => __( 'Output custom HTML content', 'meta-box-builder' ),
			],
			'date'              => [
				'title'       => __( 'Date Picker', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'std', 'format', 'timestamp', 'save_format', 'input_attributes' ] ),
					[ 'inline', 'label_description', 'desc', 'placeholder', 'size', 'prepend_append' ],
					$validation_tab,
					array_merge( [ 'js_options' ], $advanced_tab )
				),
				'description' => __( 'Date picker', 'meta-box-builder' ),
			],
			'datetime'          => [
				'title'       => __( 'Datetime Picker', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'std', 'format', 'timestamp', 'save_format', 'input_attributes' ] ),
					[ 'inline', 'label_description', 'desc', 'placeholder', 'size', 'prepend_append' ],
					$validation_tab,
					array_merge( [ 'js_options' ], $advanced_tab )
				),
				'description' => __( 'Date and time picker', 'meta-box-builder' ),
			],
			'divider'           => [
				'title'       => __( 'Divider', 'meta-box-builder' ),
				'category'    => 'layout',
				'controls'    => [ 'type', 'class', 'before', 'after' ],
				'description' => __( 'Simple horizontal line', 'meta-box-builder' ),
			],
			'email'             => [
				'title'       => __( 'Email', 'meta-box-builder' ),
				'category'    => 'html5',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'std', 'input_attributes' ] ),
					[ 'label_description', 'desc', 'placeholder', 'size', 'prepend_append' ],
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'For entering an email address with browser validation', 'meta-box-builder' ),
			],
			'fieldset_text'     => [
				'title'       => __( 'Fieldset Text', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'options' ] ),
					$appearance_tab,
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'Group of text inputs. Not recommended. Use the Group field type instead.', 'meta-box-builder' ),
			],
			'file'              => [
				'title'       => __( 'File', 'meta-box-builder' ),
				'category'    => 'upload',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'max_file_uploads', 'upload_dir', 'force_delete' ] ),
					$appearance_tab,
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'Simple file upload with default UI like <input type="file" />. Not recommended. Use File advanced instead.', 'meta-box-builder' ),
			],
			'file_advanced'     => [
				'title'       => __( 'File Advanced', 'meta-box-builder' ),
				'category'    => 'upload',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'mime_type', 'max_file_uploads', 'max_status', 'force_delete' ] ),
					$appearance_tab,
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'Multiple file uploads with WordPress media popup', 'meta-box-builder' ),
			],
			'file_input'        => [
				'title'       => __( 'File Input', 'meta-box-builder' ),
				'category'    => 'upload',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'std' ] ),
					[ 'label_description', 'desc', 'placeholder', 'size', 'prepend_append' ],
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'A text input for entering a file URL with the ability to select a file from the Media Library', 'meta-box-builder' ),
			],
			'file_upload'       => [
				'title'       => __( 'File Upload', 'meta-box-builder' ),
				'category'    => 'upload',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'max_file_size', 'mime_type', 'max_file_uploads', 'max_status', 'force_delete' ] ),
					$appearance_tab,
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'Multiple file uploads with a drag and drop area', 'meta-box-builder' ),
			],
			'map'               => [
				'title'       => __( 'Google Maps', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'api_key', 'address_field', 'std', 'language', 'region', 'marker_draggable' ] ),
					$appearance_tab,
					$advanced_tab
				),
				'description' => __( 'Google Maps', 'meta-box-builder' ),
			],
			'heading'           => [
				'title'       => __( 'Heading', 'meta-box-builder' ),
				'category'    => 'layout',
				'controls'    => array_merge(
					[ 'type', 'name', 'desc' ],
					[ 'class', 'before', 'after' ],
				),
				'description' => __( 'Heading text', 'meta-box-builder' ),
			],
			'hidden'            => [
				'title'       => __( 'Hidden', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge(
					[ 'type', 'id', 'std' ],
					[ 'class', 'before', 'after', 'custom_settings' ],
				),
				'description' => __( 'For storing a default hidden value', 'meta-box-builder' ),
			],
			'icon'              => [
				'title'       => __( 'Icon', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'icon_set', 'icon_file', 'icon_dir', 'icon_css', 'std' ] ),
					[ 'label_description', 'desc', 'placeholder' ],
					$advanced_tab
				),
				'description' => __( 'Icon with FontAwesome set', 'meta-box-builder' ),
			],
			'image'             => [
				'title'       => __( 'Image', 'meta-box-builder' ),
				'category'    => 'upload',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'max_file_uploads', 'upload_dir', 'force_delete' ] ),
					$appearance_tab,
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'Simple image upload with default UI like <input type="file" />. Not recommended. Use Image advanced instead.', 'meta-box-builder' ),
			],
			'image_advanced'    => [
				'title'       => __( 'Image Advanced', 'meta-box-builder' ),
				'category'    => 'upload',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'image_size', 'max_file_uploads', 'max_status', 'force_delete', 'add_to' ] ),
					$appearance_tab,
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'Multiple image uploads with WordPress media popup, usually used for a gallery', 'meta-box-builder' ),
			],
			'image_select'      => [
				'title'       => __( 'Image Select', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'options', 'std', 'multiple' ] ),
					$appearance_tab,
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'Select a choice with images', 'meta-box-builder' ),
			],
			'image_upload'      => [
				'title'       => __( 'Image Upload', 'meta-box-builder' ),
				'category'    => 'upload',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'max_file_size', 'image_size', 'max_file_uploads', 'max_status', 'force_delete', 'add_to' ] ),
					$appearance_tab,
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'Multiple image uploads with a drag and drop area', 'meta-box-builder' ),
			],
			'key_value'         => [
				'title'       => __( 'Key Value', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge(
					[ 'required' ],
					$general_tab,
					[ 'label_description', 'desc', 'placeholder_key', 'placeholder_value' ],
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'Add an unlimited group of key-value pairs', 'meta-box-builder' ),
			],
			'number'            => [
				'title'       => __( 'Number', 'meta-box-builder' ),
				'category'    => 'html5',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'minmax', 'step', 'std', 'input_attributes' ] ),
					[ 'label_description', 'desc', 'placeholder', 'size', 'prepend_append' ],
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'For entering a number with browser validation', 'meta-box-builder' ),
			],
			'oembed'            => [
				'title'       => __( 'oEmbed', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'std', 'not_available_string', 'input_attributes' ] ),
					[ 'label_description', 'desc', 'placeholder', 'size', 'prepend_append' ],
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'Input for media from Youtube, Vimeo, and all supported sites by WordPress', 'meta-box-builder' ),
			],
			'osm'               => [
				'title'       => __( 'Open Street Maps', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'std', 'address_field', 'language', 'region', 'marker_draggable' ] ),
					$appearance_tab,
					$advanced_tab
				),
				'description' => __( 'Open Street Maps', 'meta-box-builder' ),
			],
			'password'          => [
				'title'       => __( 'Password', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'std' ] ),
					[ 'label_description', 'desc', 'placeholder', 'size', 'prepend_append' ],
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'For entering a password', 'meta-box-builder' ),
			],
			'post'              => [
				'title'       => __( 'Post', 'meta-box-builder' ),
				'category'    => 'wordpress',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'post_type', 'field_type', 'add_new', 'multiple', 'parent', 'query_args' ] ),
					[ 'select_all_none', 'label_description', 'desc', 'placeholder' ],
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'For selecting posts', 'meta-box-builder' ),
			],
			'radio'             => [
				'title'       => __( 'Radio', 'meta-box-builder' ),
				'category'    => 'basic',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'options', 'std' ] ),
					array_merge( [ 'inline' ], $appearance_tab ),
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'Radio input where you can select only one choice', 'meta-box-builder' ),
			],
			'range'             => [
				'title'       => __( 'Range', 'meta-box-builder' ),
				'category'    => 'html5',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'minmax', 'step', 'std' ] ),
					$appearance_tab,
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'A slider for selecting a number', 'meta-box-builder' ),
			],
			'select'            => [
				'title'       => __( 'Select', 'meta-box-builder' ),
				'category'    => 'basic',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'options', 'std', 'multiple' ] ),
					[ 'select_all_none', 'label_description', 'desc', 'placeholder' ],
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'Select dropdown where you can select one or multiple choice', 'meta-box-builder' ),
			],
			'select_advanced'   => [
				'title'       => __( 'Select Advanced', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'options', 'std', 'multiple' ] ),
					[ 'select_all_none', 'label_description', 'desc', 'placeholder' ],
					$validation_tab,
					array_merge( [ 'js_options' ], $advanced_tab )
				),
				'description' => __( 'Beautiful select dropdown using select2 library', 'meta-box-builder' ),
			],
			'sidebar'           => [
				'title'       => __( 'Sidebar', 'meta-box-builder' ),
				'category'    => 'wordpress',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'std', 'field_type' ] ),
					[ 'label_description', 'desc', 'placeholder' ],
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'For selecting sidebars', 'meta-box-builder' ),
			],
			'single_image'      => [
				'title'       => __( 'Single Image', 'meta-box-builder' ),
				'category'    => 'upload',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'force_delete', 'image_size' ] ),
					$appearance_tab,
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'Single image upload with WordPress media popup', 'meta-box-builder' ),
			],
			'slider'            => [
				'title'       => __( 'jQuery UI Slider', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'std' ] ),
					[ 'label_description', 'desc', 'prefix_suffix' ],
					$validation_tab,
					array_merge( [ 'js_options' ], $advanced_tab )
				),
				'description' => __( 'jQuery UI slider', 'meta-box-builder' ),
			],
			'switch'            => [
				'title'       => __( 'Switch', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge(
					[ 'required' ],
					array_merge( $general_tab, [ 'std' ] ),
					[ 'label_description', 'desc', 'style', 'on_off' ],
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'On/off switch with iOS style', 'meta-box-builder' ),
			],
			'taxonomy'          => [
				'title'       => __( 'Taxonomy', 'meta-box-builder' ),
				'category'    => 'wordpress',
				'controls'    => array_merge(
					[ 'required' ],
					array_merge( $general_tab, [ 'taxonomy', 'field_type', 'add_new', 'remove_default', 'multiple', 'query_args' ] ),
					[ 'select_all_none', 'label_description', 'desc', 'placeholder' ],
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'For selecting taxonomy terms. Doesn\'t save term IDs in post meta, but set post terms.', 'meta-box-builder' ),
			],
			'taxonomy_advanced' => [
				'title'       => __( 'Tax. Advanced', 'meta-box-builder' ),
				'category'    => 'wordpress',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'taxonomy', 'field_type', 'add_new', 'remove_default', 'multiple', 'query_args' ] ),
					[ 'select_all_none', 'label_description', 'desc', 'placeholder' ],
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'For selecting taxonomy terms and saving term IDs in post meta as a comma-separated string. It doesn\'t set post terms.', 'meta-box-builder' ),
			],
			'text'              => [
				'title'       => __( 'Text', 'meta-box-builder' ),
				'category'    => 'basic',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'std', 'input_attributes' ] ),
					[ 'label_description', 'desc', 'placeholder', 'size', 'prepend_append' ],
					$validation_tab,
					array_merge( [ 'datalist_choices' ], $advanced_tab )
				),
				'description' => __( 'A single-line text input', 'meta-box-builder' ),
			],
			'text_list'         => [
				'title'       => __( 'Text List', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'options' ] ),
					$appearance_tab,
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'Group of text inputs. Similar to Fieldset text, but has a different UI. Not recommended. Use the Group field type instead.', 'meta-box-builder' ),
			],
			'textarea'          => [
				'title'       => __( 'Textarea', 'meta-box-builder' ),
				'category'    => 'basic',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'std', 'input_attributes' ] ),
					[ 'label_description', 'desc', 'placeholder', 'textarea_size' ],
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'A paragraph text input', 'meta-box-builder' ),
			],
			'time'              => [
				'title'       => __( 'Time Picker', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'std', 'input_attributes' ] ),
					[ 'inline', 'label_description', 'desc', 'placeholder', 'size', 'prepend_append' ],
					$validation_tab,
					array_merge( [ 'js_options' ], $advanced_tab )
				),
				'description' => __( 'Time picker', 'meta-box-builder' ),
			],
			'user'              => [
				'title'       => __( 'User', 'meta-box-builder' ),
				'category'    => 'wordpress',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'field_type', 'add_new', 'multiple', 'query_args' ] ),
					[ 'select_all_none', 'label_description', 'desc', 'placeholder' ],
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'For selecting users', 'meta-box-builder' ),
			],
			'url'               => [
				'title'       => __( 'URL', 'meta-box-builder' ),
				'category'    => 'html5',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'std', 'input_attributes' ] ),
					[ 'label_description', 'desc', 'placeholder', 'size', 'prepend_append' ],
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'An input for URL with browser validation', 'meta-box-builder' ),
			],
			'video'             => [
				'title'       => __( 'Video', 'meta-box-builder' ),
				'category'    => 'upload',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'max_file_uploads', 'max_status', 'force_delete' ] ),
					$appearance_tab,
					$validation_tab,
					$advanced_tab
				),
				'description' => __( 'Multiple video uploads with WordPress media popup', 'meta-box-builder' ),
			],
			'wysiwyg'           => [
				'title'       => __( 'WYSIWYG Editor', 'meta-box-builder' ),
				'category'    => 'advanced',
				'controls'    => array_merge(
					[ 'required', 'clone_settings' ],
					array_merge( $general_tab, [ 'std', 'raw' ] ),
					$appearance_tab,
					$validation_tab,
					array_merge( [ 'options' ], $advanced_tab )
				),
				'description' => __( 'WordPress editor', 'meta-box-builder' ),
			],
		];

		$field_types = apply_filters( 'mbb_field_types', $field_types );

		// Register default controls with field types for the type selector
		$this->registry->register_default_controls( $field_types );

		foreach ( $field_types as $type => $field_type ) {
			$field_type['controls'] = apply_filters( 'mbb_field_controls', $field_type['controls'], $type );
			$this->registry->add_field_type( $type, $field_type );
		}

		$this->registry->transform_controls();

		return $this->registry->get_field_types();
	}

	public function get_field_html( WP_REST_Request $request ) {
		$field = $request->get_param( 'field' );

		$field = RWMB_Field::call( 'normalize', $field );

		$meta = RWMB_Field::call( $field, 'meta', 0, false );
		$html = RWMB_Field::call( $field, 'html', $meta );

		return $html;
	}
}
