<?php
namespace MBDI;

class FieldQuery {

	protected $registry;

	protected $filters;

	private $unsupported_fields = [
		'divider',
		'tab',
	];

	private $fields = [];

	public function __construct( $filters = [] ) {
		$this->filters = $filters;

		$this->query();
	}

	/**
	 * Get all fields from all meta boxes.
	 */
	public function query(): self {
		$meta_box_registry = rwmb_get_registry( 'meta_box' );
		$meta_boxes        = $meta_box_registry->all();

		$fields = [];

		foreach ( $meta_boxes as $meta_box ) {
			$meta_box = $meta_box->meta_box;
			$fields   = array_merge( $fields, $this->flatten( $meta_box['fields'] ) );
		}

		$this->fields = $fields;

		return $this;
	}

	/**
	 * Get fields from a meta box.
	 */
	public function get(): array {
		return $this->fields;
	}

	public function to_array(): array {
		return $this->fields;
	}

	/**
	 * Get fields from a meta box and pluck a specific key.
	 *
	 * @param string  $value The key to pluck.
	 * @param ?string $key   The key to use as the array key.
	 */
	public function pluck( string $value, ?string $key = null ): array {
		$fields = $this->fields;

		$output = [];

		foreach ( $fields as $field ) {
			if ( ! isset( $field[ $value ] ) || empty( $field[ $value ] ) ) {
				continue;
			}

			if ( isset( $key ) ) {
				$output[ $field[ $key ] ] = $field[ $value ];
			} else {
				$output[] = $field[ $value ];
			}
		}

		return $output;
	}

	/**
	 * Get all fields from all meta boxes, flatten the fields, and filter out unsupported fields.
	 */
	public function flatten( array $fields, ?array $parent = null ): array {
		$output = [];

		foreach ( $fields as $field ) {
			if ( 'group' === $field['type'] ) {
				$children = $this->flatten( $field['fields'], $field );
				$output   = array_merge( $output, $children );
			}

			if ( in_array( $field['type'], $this->unsupported_fields, true ) ) {
				continue;
			}

			if ( $parent ) {
				$field['name'] = $parent['name'] . ': ' . $field['name'];
				$field['id']   = $parent['id'] . '.' . $field['id'];
			}

			$accepted_filters = [ 'clone', 'type' ];

			foreach ( $accepted_filters as $filter ) {
				if ( isset( $this->filters[ $filter ] ) && $field[ $filter ] != $this->filters[ $filter ] ) {
					continue 2;
				}
			}

			if ( isset( $this->filters['not_type'] ) && in_array( $field['type'], $this->filters['not_type'], true ) ) {
				continue;
			}

			$output[] = $field;
		}

		return $output;
	}
}
