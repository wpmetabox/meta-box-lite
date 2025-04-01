<?php
namespace MBB\Helpers;

use MBB\LocalJson;

class Template {
	public static function render_diff_dialog() {
		$show_on_screens = [
			'meta-box',
			'edit-meta-box',
		];
		// Only show the dialog in the meta box edit screen.
		if ( ! in_array( get_current_screen()->id, $show_on_screens, true ) ) {
			return;
		}

		if ( ! LocalJson::is_enabled() ) {
			return;
		}
		?>
		<dialog id="mbb-diff-dialog">
			<div class="mbb-diff-dialog-wrapper">
				<header>
					<h2 tabindex="0"><?php esc_html_e( 'Review changes', 'meta-box-builder' ) ?></h2>
					<button id="mbb-diff-dialog-close" class="button-link" role="button">&times;</button>
				</header>

				<div class="mbb-diff-dialog-main">
					<div class="mbb-diff-dialog-button-group" data-split-views="true">
						<div>
							<h3><?php esc_html_e( 'Database', 'meta-box-builder' ) ?></h3>
							<p><?php esc_html_e( 'Last updated:', 'meta-box-builder' ) ?> <span data-bind="database.modified"></span></p>
							<span data-bind="database.newer"></span>
						</div>

						<div>
							<h3>
								<?php esc_html_e( 'JSON', 'meta-box-builder' ) ?>
								<small><?php esc_html_e( '(Always in use)', 'meta-box-builder' ) ?></small>
							</h3>
							<p>
								<?php esc_html_e( 'Last updated:', 'meta-box-builder' ) ?>
								<span data-bind="local.modified"></span>
								<span data-bind="local.newer"></span>
							</p>
						</div>
					</div>

					<div class="mbb-diff-dialog-content"></div>

					<template id="sync-success">
						<div class="sync-success-wrapper">
							<div class="sync-success-content sync-status-text">
								<p><?= esc_html__( 'All changes synced!', 'meta-box-builder' ); ?></p>
							</div>
						</div>
					</template>

					<template id="sync-error">
						<div class="sync-error-wrapper">
							<div class="sync-error-content sync-status-text">
								<p><?= esc_html__( 'Error during syncing data, please check folder permission or file format!', 'meta-box-builder' ); ?>
								</p>
							</div>
						</div>
					</template>

					<template id="no-changes">
						<section class="no-changes-content sync-status-text">
							<p><?= esc_html__( 'No changes detected.', 'meta-box-builder' ); ?></p>
						</section>
					</template>
				</div>
				<footer>
					<button type="button" class="button-primary button-sync" data-use="json">
						<?php esc_html_e( 'Sync changes', 'meta-box-builder' ) ?>
					</button>
				</footer>
			</div>
		</dialog>
		<?php
	}
}