<?php
namespace MetaBox\ACF;

class AdminPage {
	public function __construct() {
		add_filter( 'rwmb_admin_menu', '__return_true' );
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
	}

	public function add_menu() {
		$page_hook = add_submenu_page(
			'meta-box',
			esc_html__( 'ACF Migration', 'mb-acf-migration' ),
			esc_html__( 'ACF Migration', 'mb-acf-migration' ),
			'manage_options',
			'mb-acf-migration',
			[ $this, 'render' ]
		);
		add_action( "admin_print_styles-$page_hook", [ $this, 'enqueue' ] );
	}

	public function enqueue() {
		wp_enqueue_style( 'mb-acf', plugins_url( 'assets/migrate.css', __DIR__ ), [], '1.0.0' );
		wp_enqueue_script( 'mb-acf', plugins_url( 'assets/migrate.js', __DIR__ ), [], '1.0.0', true );
		wp_localize_script( 'mb-acf', 'MbAcf', [
			'start'                  => __( 'Start', 'mb-acf-migration' ),
			'done'                   => __( 'Done', 'mb-acf-migration' ),
			'migratingPostTypes'     => __( 'Migrating post types', 'mb-acf-migration' ),
			'migratingTaxonomies'    => __( 'Migrating taxonomies', 'mb-acf-migration' ),
			'migratingFieldGroups'   => __( 'Migrating field groups', 'mb-acf-migration' ),
			'migratingPosts'         => __( 'Migrating posts', 'mb-acf-migration' ),
			'migratingTerms'         => __( 'Migrating terms', 'mb-acf-migration' ),
			'migratingUsers'         => __( 'Migrating users', 'mb-acf-migration' ),
			'migratingComments'      => __( 'Migrating comments', 'mb-acf-migration' ),
			'migratingSettingsPages' => __( 'Migrating settings pages', 'mb-acf-migration' ),
		] );
	}

	public function render() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ) ?></h1>
			<p>
				<button class="button button-primary" id="process"><?php esc_html_e( 'Migrate', 'mb-acf-migration' ) ?></button>
			</p>
			<h2><?php esc_html_e( 'Notes:', 'mb-acf-migration' ) ?></h2>
			<ul>
				<li><?php esc_html_e( 'Always backup your database first as the plugin will remove/replace the existing ACF data. If you find any problem, restore the database and report us. We can\'t help you if you don\'t backup the database and there\'s something wrong.', 'mb-acf-migration' ) ?></li>
				<li><?php esc_html_e( 'Not all data types and settings in ACF have an equivalent in Meta Box. The plugin will try to migrate as much as it can. But for such data or settings, the plugin will ignore. This includes: link, accordion, clone field types and complex location/conditional logic rules.', 'mb-acf-migration' ) ?></li>
				<li>
					<?php esc_html_e( 'The plugin will attempt to migrate both field groups and custom fields data to Meta Box. You might need some premium extensions like:', 'mb-acf-migration' ) ?>
					<ul>
						<li><a href="https://metabox.io/plugins/meta-box-builder/" target="_blank">Meta Box Builder</a>: <?php esc_html_e( 'For managing field groups settings.', 'mb-acf-migration' ) ?></li>
						<li><a href="https://metabox.io/plugins/mb-term-meta/" target="_blank">MB Term Meta</a>: <?php esc_html_e( 'For handling custom fields for taxonomies.', 'mb-acf-migration' ) ?></li>
						<li><a href="https://metabox.io/plugins/mb-user-meta/" target="_blank">MB User Meta</a>: <?php esc_html_e( 'For handling custom fields for users.', 'mb-acf-migration' ) ?></li>
						<li><a href="https://metabox.io/plugins/mb-settings-pages/" target="_blank">MB Settings Pages</a>: <?php esc_html_e( 'For handling custom fields for settings pages.', 'mb-acf-migration' ) ?></li>
					</ul>
				</li>
				<li><?php esc_html_e( 'You need to update the code to output fields data on the frontend to make it work with Meta Box.', 'mb-acf-migration' ) ?></li>
			</ul>
			<p><a href="https://docs.metabox.io/extensions/mb-acf-migration/" target="_blank"><?php esc_html_e( 'Read the documentation carefully before processing', 'mb-acf-migration' ) ?> &rarr;</a></p>
			<div id="status"></div>
		</div>
		<?php
	}
}
