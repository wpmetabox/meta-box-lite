<?php
namespace MBB;

class About {
	public function __construct() {
		add_action( 'rwmb_about_tabs', [ $this, 'add_tabs' ] );
		add_action( 'rwmb_about_tabs_content', [ $this, 'add_tabs_content' ] );
	}

	public function add_tabs() {
		?>
		<a href="#custom-fields" class="nav-tab"><?php esc_html_e( 'Custom Fields', 'meta-box-builder' ); ?></a>
		<?php
	}

	public function add_tabs_content() {
		?>
		<div id="custom-fields" class="gt-tab-pane">
			<p class="about-description"><?php esc_html_e( 'Please follow this video tutorial to know how to create custom fields with Meta Box Builder:', 'meta-box-builder' ); ?></p>
			<div class="youtube-video-container">
				<iframe width="560" height="315" src="https://www.youtube.com/embed/_DaFUt92kYY" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			</div>
			<p><a class="button" href="https://docs.metabox.io/extensions/meta-box-builder/?utm_source=WordPress&utm_medium=link&utm_campaign=plugin" target="_blank"><?php esc_html_e( 'Go to the documentation', 'meta-box-builder' ) ?></a></p>
		</div>
		<?php
	}
}
