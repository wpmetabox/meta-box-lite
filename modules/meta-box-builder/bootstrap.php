<?php
namespace MBB;

// Prevent loading this file directly.
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

// Show Meta Box admin menu.
add_filter( 'rwmb_admin_menu', '__return_true' );
load_plugin_textdomain( 'meta-box-builder', false, plugin_basename( MBB_DIR ) . '/languages/' );

new PostType();
new Upgrade\Manager();
new Register();
new RestApi\Generator();
new RestApi\Save();
new RestApi\ThemeCode\ThemeCode();

new RestApi\Fields( new Registry() );

if ( Helpers\Data::is_extension_active( 'meta-box-include-exclude' ) ) {
	new RestApi\IncludeExclude();
}
if ( Helpers\Data::is_extension_active( 'meta-box-show-hide' ) ) {
	new RestApi\ShowHide();
}

if ( Helpers\Data::is_extension_active( 'mb-blocks' ) ) {
	new Extensions\Blocks\Data();
	new Extensions\Blocks\Json\Register();
	new Extensions\Blocks\Json\Generator();
	new Extensions\Blocks\Json\Overrider();
	new Extensions\Blocks\Json\Path();
	new Extensions\Blocks\CodeToCallbackTransformer();
}

if ( Helpers\Data::is_extension_active( 'mb-settings-page' ) ) {
	new Extensions\SettingsPage\Manager();
}

if ( Helpers\Data::is_extension_active( 'mb-relationships' ) ) {
	new Extensions\Relationships\Manager();
}

if ( Helpers\Data::is_extension_active( 'mb-custom-table' ) ) {
	new Extensions\CustomTable();
}

new Extensions\AdminColumns();

new Integrations\WPML\Manager();
new Integrations\Polylang\Manager();

new Extensions\Columns();
new Extensions\ConditionalLogic();
new Extensions\Group();
new Extensions\Tabs();
new Extensions\Tooltip();
new Extensions\RestApi();
new Extensions\FrontendSubmission();
new Extensions\TextLimiter();

new LocalJson();
if ( is_admin() ) {
	new Import();
	new Export();
	new Edit( 'meta-box' );
	new AdminColumns();

	new ToggleStatusColumn();
}
