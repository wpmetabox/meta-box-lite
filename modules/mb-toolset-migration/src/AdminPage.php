<?php
namespace MetaBox\TS;

class AdminPage {
	public function __construct() {
		add_filter( 'rwmb_admin_menu', '__return_true' );
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
	}

	public function add_menu() {
		$page_hook = add_submenu_page(
			'meta-box',
			esc_html__( 'Toolset Migration', 'mb-toolset-migration' ),
			esc_html__( 'Toolset Migration', 'mb-toolset-migration' ),
			'manage_options',
			'mb-toolset-migration',
			[ $this, 'render' ]
		);
		add_action( "admin_print_styles-$page_hook", [ $this, 'enqueue' ] );
	}

	public function enqueue() {
		wp_enqueue_style( 'mb-toolset', plugins_url( 'assets/migrate.css', __DIR__ ), [], '1.0.0' );
		wp_enqueue_script( 'mb-toolset', plugins_url( 'assets/migrate.js', __DIR__ ), [], '1.0.0', true );
		wp_localize_script( 'mb-toolset', 'MbTs', [
			'start'                 => __( 'Start', 'mb-toolset-migration' ),
			'done'                  => __( 'Done', 'mb-toolset-migration' ),
			'migratingPostTypes'    => __( 'Migrating post types', 'mb-toolset-migration' ),
			'migratingTaxonomies'   => __( 'Migrating taxonomies', 'mb-toolset-migration' ),
			'migratingFieldGroups'  => __( 'Migrating field groups', 'mb-toolset-migration' ),
			'migratingPosts'        => __( 'Migrating posts', 'mb-toolset-migration' ),
			'migratingTerms'        => __( 'Migrating terms', 'mb-toolset-migration' ),
			'migratingUsers'        => __( 'Migrating users', 'mb-toolset-migration' ),
			'migratingRelationship' => __( 'Migrating relationship', 'mb-toolset-migration' ),
		] );
	}

	public function render() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ) ?></h1>
			<p>
				<button class="button button-primary" id="process"><?php esc_html_e( 'Migrate', 'mb-toolset-migration' ) ?></button>
			</p>
			<h2><?php esc_html_e( 'Notes:', 'mb-toolset-migration' ) ?></h2>
			<ul>
				<li><?php esc_html_e( 'Always backup your database first as the plugin will remove/replace the existing Toolset data. If you find any problem, restore the database and report us. We can\'t help you if you don\'t backup the database and there\'s something wrong.', 'mb-toolset-migration' ) ?></li>
				<li><?php esc_html_e( 'Not all data types and settings in Toolset have an equivalent in Meta Box. The plugin will try to migrate as much as it can. But for such data or settings, the plugin will ignore. This includes: skype, post field types.', 'mb-toolset-migration' ) ?></li>
				<li>
					<?php esc_html_e( 'The plugin will attempt to migrate both field groups, custom fields data and relationships to Meta Box. You might need some premium extensions like:', 'mb-toolset-migration' ) ?>
					<ul>
						<li><a href="https://metabox.io/plugins/meta-box-builder/" target="_blank">Meta Box Builder</a>: <?php esc_html_e( 'For managing field groups settings.', 'mb-toolset-migration' ) ?></li>
						<li><a href="https://metabox.io/plugins/mb-term-meta/" target="_blank">MB Term Meta</a>: <?php esc_html_e( 'For handling custom fields for taxonomies.', 'mb-toolset-migration' ) ?></li>
						<li><a href="https://metabox.io/plugins/mb-user-meta/" target="_blank">MB User Meta</a>: <?php esc_html_e( 'For handling custom fields for users.', 'mb-toolset-migration' ) ?></li>
						<li><a href="https://metabox.io/plugins/mb-relationships/" target="_blank">MB Relationships</a>: <?php esc_html_e( 'For handling relationships.', 'mb-toolset-migration' ) ?></li>
					</ul>
				</li>
				<li><?php esc_html_e( 'You need to update the code to output fields data on the frontend to make it work with Meta Box.', 'mb-toolset-migration' ) ?></li>
			</ul>
			<p><a href="https://docs.metabox.io/extensions/mb-toolset-migration/" target="_blank"><?php esc_html_e( 'Read the documentation carefully before processing', 'mb-toolset-migration' ) ?> &rarr;</a></p>
			<div id="status"></div>
		</div>
		<?php
	}
}
