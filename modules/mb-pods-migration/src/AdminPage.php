<?php
namespace MetaBox\Pods;

class AdminPage {
	public function __construct() {
		add_filter( 'rwmb_admin_menu', '__return_true' );
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
	}

	public function add_menu() {
		$page_hook = add_submenu_page(
			'meta-box',
			esc_html__( 'Pods Migration', 'mb-pods-migration' ),
			esc_html__( 'Pods Migration', 'mb-pods-migration' ),
			'manage_options',
			'mb-pods-migration',
			[ $this, 'render' ]
		);
		add_action( "admin_print_styles-$page_hook", [ $this, 'enqueue' ] );
	}

	public function enqueue() {
		wp_enqueue_style( 'mb-pods', plugins_url( 'assets/migrate.css', __DIR__ ), [], '1.0.0' );
		wp_enqueue_script( 'mb-pods', plugins_url( 'assets/migrate.js', __DIR__ ), [], '1.0.0', true );
		wp_localize_script( 'mb-pods', 'MbPods', [
			'notice'                 => __( 'The plugin will delete the Pods data. Always backup your database first before migrating. Will you continue to migrate?', 'mb-pods-migration' ),
			'start'                  => __( 'Start', 'mb-pods-migration' ),
			'done'                   => __( 'Done', 'mb-pods-migration' ),
			'migratingPostTypes'     => __( 'Migrating post types', 'mb-pods-migration' ),
			'migratingTaxonomies'    => __( 'Migrating taxonomies', 'mb-pods-migration' ),
			'migratingFieldGroups'   => __( 'Migrating field groups', 'mb-pods-migration' ),
			'migratingPosts'         => __( 'Migrating posts', 'mb-pods-migration' ),
			'migratingTerms'         => __( 'Migrating terms', 'mb-pods-migration' ),
			'migratingUsers'         => __( 'Migrating users', 'mb-pods-migration' ),
			'migratingRelationship'  => __( 'Migrating relationship', 'mb-pods-migration' ),
			'migratingSettingsPages' => __( 'Migrating settings pages ', 'mb-pods-migration' ),
		] );
	}

	public function render() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ) ?></h1>
			<p>
				<button class="button button-primary"
					id="process"><?php esc_html_e( 'Migrate', 'mb-pods-migration' ) ?></button>
			</p>
			<h2><?php esc_html_e( 'Notes:', 'mb-pods-migration' ) ?></h2>
			<ul>
				<li><?php esc_html_e( 'Always backup your database first as the plugin will remove/replace the existing pods data. If you find any problem, restore the database and report us. We can\'t help you if you don\'t backup the database and there\'s something wrong.', 'mb-pods-migration' ) ?>
				</li>
				<li><?php esc_html_e( 'Not all data types and settings in pods have an equivalent in Meta Box. The plugin will try to migrate as much as it can. But for such data or settings, the plugin will ignore. This includes: skype, post field types.', 'mb-pods-migration' ) ?>
				</li>
				<li>
					<?php esc_html_e( 'The plugin will attempt to migrate both field groups, custom fields data and relationships to Meta Box. You might need some premium extensions like:', 'mb-pods-migration' ) ?>
					<ul>
						<li><a href="https://metabox.io/plugins/meta-box-builder/" target="_blank">Meta Box Builder</a>:
							<?php esc_html_e( 'For managing field groups settings.', 'mb-pods-migration' ) ?>
						</li>
						<li><a href="https://metabox.io/plugins/mb-term-meta/" target="_blank">MB Term Meta</a>:
							<?php esc_html_e( 'For handling custom fields for taxonomies.', 'mb-pods-migration' ) ?>
						</li>
						<li><a href="https://metabox.io/plugins/mb-user-meta/" target="_blank">MB User Meta</a>:
							<?php esc_html_e( 'For handling custom fields for users.', 'mb-pods-migration' ) ?>
						</li>
						<li><a href="https://metabox.io/plugins/mb-relationships/" target="_blank">MB Relationships</a>:
							<?php esc_html_e( 'For handling relationships.', 'mb-pods-migration' ) ?>
						</li>
					</ul>
				</li>
				<li><?php esc_html_e( 'You need to update the code to output fields data on the frontend to make it work with Meta Box.', 'mb-pods-migration' ) ?>
				</li>
			</ul>
			<p><a href="https://docs.metabox.io/extensions/mb-pods-migration/"
					target="_blank"><?php esc_html_e( 'Read the documentation carefully before processing', 'mb-pods-migration' ) ?> &rarr;</a></p>
			<div id="status"></div>
		</div>
		<?php
	}
}
