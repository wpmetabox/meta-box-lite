<?php
namespace MetaBox\ACF\Processors\FieldGroups;

class IncludeExclude {
	private $location;

	public function __construct( $location ) {
		$this->location = $location;
	}

	public function migrate() {
		$include = $this->migrate_type( 'include' );
		$exclude = $this->migrate_type( 'exclude' );

		if ( ! empty( $include ) ) {
			$include_exclude['type'] = 'include';
			$rules                   = $include;
		} else {
			$include_exclude['type'] = 'exclude';
			$rules                   = $exclude;
		}

		$include_exclude = [
			'relation' => count( $this->location ) === 1 ? 'AND' : 'OR',
			'rules'    => [],
		];
		foreach ( $rules as $rule ) {
			$id         = uniqid();
			$rule['id'] = $id;

			$include_exclude['rules'][ $id ] = $rule;
		}

		return $include_exclude;
	}

	private function migrate_type( $type ) {
		$items = [];

		if ( count( $this->location ) === 1 ) {
			// 1 group.
			$items = reset( $this->location );
		} else {
			// Many groups: take first rule from each group.
			foreach ( $this->location as $group ) {
				$items[] = reset( $group );
			}
		}

		$operator   = $type === 'include' ? '==' : '!=';
		$rules      = [];
		$rule_names = [
			'post_template'     => 'template',
			'post_format'       => 'post_format',
			'post_category'     => 'taxonomy',
			'post_taxonomy'     => 'taxonomy',
			'post'              => 'ID',
			'page_template'     => 'template',
			'page_parent'       => 'parent',
			'page'              => 'ID',
			'current_user'      => 'user_id',
			'current_user_role' => 'user_role',
			'user_role'         => 'edited_user_role',
		];
		foreach ( $items as $rule ) {
			if ( $rule['operator'] !== $operator ) {
				continue;
			}

			$param = $rule['param'];

			// Not supported rule.
			if ( ! isset( $rule_names[ $param ] ) && ! taxonomy_exists( $param ) ) {
				continue;
			}

			$name = $rule_names[ $param ];

			$rules[] = [
				'name'  => $this->get_name( $name, $rule['value'] ),
				'value' => [ $this->get_value( $name, $rule['value'] ) ],
				'label' => [ $this->get_label( $name, $rule['value'] ) ],
			];
		}

		return $rules;
	}

	private function get_name( $name, $value ) {
		switch ( $name ) {
			case 'taxonomy':
				list( $taxonomy ) = explode( ':', $value );
				return $taxonomy;
			default:
				return $name;
		}
	}

	private function get_label( $name, $value ) {
		switch ( $name ) {
			case 'ID':
				return get_post( $value )->post_title;
			case 'taxonomy':
				list( $taxonomy, $slug ) = explode( ':', $value );
				$term                    = get_term_by( 'slug', $slug, $taxonomy );
				return $term->name;
			default:
				return $value;
		}
	}

	private function get_value( $name, $value ) {
		switch ( $name ) {
			case 'taxonomy':
				list( $taxonomy, $slug ) = explode( ':', $value );
				$term                    = get_term_by( 'slug', $slug, $taxonomy );
				return $term->term_id;
			default:
				return $value;
		}
	}
}
