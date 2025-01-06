<?php

namespace MBDI\Templates;

abstract class Base {
	/**
	 * Value retrieved from meta box.
	 *
	 * @var string|array|object
	 */
	protected $value;

	/**
	 * Field settings.
	 *
	 * @var array
	 */
	protected $field;

	/**
	 * Raw value.
	 *
	 * @var bool
	 */
	protected $raw;

	/**
	 * Attributes.
	 *
	 * @var array
	 */
	protected $attrs;

	/**
	 * Constructor.
	 *
	 * @param string|array|object $args Value retrieved from meta box.
	 */
	public function __construct( $args = [] ) {
		$this->value = $args['value'];
		$this->field = $args['field'] ?? [];
		$this->raw   = $args['raw'] ?? true;
		$this->attrs = $args['attrs'] ?? [];
	}

	/**
	 * Get field value.
	 *
	 * @return string|array|object
	 */
	public function get_value() {
		return $this->value;
	}

	/**
	 * Get meta box field.
	 *
	 * @return array
	 */
	public function get_field(): array {
		return $this->field;
	}

	/**
	 * Get raw value.
	 *
	 * @return bool
	 */
	public function get_raw(): bool {
		return $this->raw;
	}

	/**
	 * Render output.
	 *
	 * @return string
	 */
	abstract public function render(): string;
}
