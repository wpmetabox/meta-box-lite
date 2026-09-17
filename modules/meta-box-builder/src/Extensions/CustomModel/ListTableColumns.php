<?php
namespace MBB\Extensions\CustomModel;

class ListTableColumns {
	private const PREFIX = 'mbb-model-';

	private const COLUMNS = [ 'slug', 'table' ];

	public function __construct() {
		add_action( 'admin_head-edit.php', [ $this, 'init' ] );
	}

	public function init(): void {
		$screen = get_current_screen();
		if ( ! $screen || 'edit-mb-model' !== $screen->id ) {
			return;
		}

		$this->output_css();

		add_filter( 'manage_mb-model_posts_columns', [ $this, 'columns' ] );
		add_action( 'manage_mb-model_posts_custom_column', [ $this, 'output' ], 10, 2 );
	}

	public function columns( array $columns ): array {
		unset( $columns['date'] );

		$labels = [
			'slug'  => __( 'Slug', 'meta-box-builder' ),
			'table' => __( 'Table name', 'meta-box-builder' ),
		];

		$new_columns = [];
		foreach ( self::COLUMNS as $column ) {
			$new_columns[ self::PREFIX . $column ] = $labels[ $column ];
		}

		$result = [];
		foreach ( $columns as $key => $label ) {
			$result[ $key ] = $label;
			if ( 'title' === $key ) {
				$result += $new_columns;
			}
		}

		if ( ! isset( $columns['title'] ) ) {
			$result += $new_columns;
		}

		return $result;
	}

	public function output( string $column, int $post_id ): void {
		$column = str_replace( self::PREFIX, '', $column );

		if ( ! in_array( $column, self::COLUMNS, true ) ) {
			return;
		}

		$model = get_post_meta( $post_id, 'model', true );
		$model = is_array( $model ) ? $model : [];

		if ( 'slug' === $column ) {
			echo esc_html( (string) ( $model['name'] ?? '' ) );
			return;
		}

		if ( 'table' === $column ) {
			echo esc_html( (string) ( $model['table'] ?? '' ) );
		}
	}

	private function output_css(): void {
		?>
		<style>
			.column-<?php echo esc_html( self::PREFIX ); ?>slug { width: 15%; }
			.column-<?php echo esc_html( self::PREFIX ); ?>table { width: 25%; }
		</style>
		<?php
	}
}
