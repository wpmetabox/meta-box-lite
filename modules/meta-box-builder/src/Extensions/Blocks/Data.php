<?php
namespace MBB\Extensions\Blocks;

use MBB\Helpers\Data as DataHelper;

class Data {
	public function __construct() {
		add_filter( 'mbb_app_data', [ $this, 'add_app_data' ] );
	}

	public function add_app_data( array $data ): array {
		$data['blockCategories'] = wp_list_pluck( get_block_categories( get_post() ), 'title', 'slug' );
		$data['settings']        = is_array( $data['settings'] ) ? $data['settings'] : [];

		$data['settings']['block_json'] = $data['settings']['block_json'] ?? [
			'enable'  => false,
			'version' => 'v' . time(),
			'path'    => '{{ theme.path }}/blocks',
		];

		$data['views']                  = DataHelper::get_views();
		$data['viewAddUrl']             = admin_url( 'post-new.php?post_type=mb-views' );
		$data['viewEditUrl']            = admin_url( 'post.php?post=' );

		return $data;
	}
}
