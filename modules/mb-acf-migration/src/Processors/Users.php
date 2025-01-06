<?php
namespace MetaBox\ACF\Processors;

class Users extends Base {
	protected $object_type = 'user';

	protected function get_items() {
		$field_group_ids = $this->get_field_group_ids();
		if ( empty( $field_group_ids ) ) {
			return [];
		}

		$users = get_users( [
			'number'      => $this->threshold,
			'offset'      => isset( $_SESSION['processed'] ) ? (int) $_SESSION['processed'] : 0,
			'count_total' => false,
			'fields'      => 'ID',
		] );

		return $users;
	}

	protected function migrate_item() {
		$this->migrate_fields();
	}

	private function migrate_fields() {
		$fields = new Data\Fields( $this->get_field_group_ids(), $this );
		$fields->migrate_fields();
	}
}
