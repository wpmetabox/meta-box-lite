<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use MBB\RestApi\ThemeCode\GroupVars;

$group_var = GroupVars::get_current_group_item_var();

if ( $in_group ) {
	// Displaying in group
	$this->out( "echo {$group_var}[ '" . $field['id'] . "' ] ?? '';" );
	if ( isset( $field['clone'] ) ) {
		// Displaying cloneable values:
		$this->out( "\$videos = {$group_var}[ '" . $field['id'] . "' ] ?? '';" );
		$this->out( '?>' );
		$this->out( '<h3>Uploaded videos</h3>' );
		$this->out( '<ul>' );
			$this->out( '<?php foreach ( $videos as $clone ) : ?>', 1 );
				$this->out( '<li>', 2 );
					$this->out( '<ul>', 3 );
						$this->out( '<?php foreach ( $clone as $video ) : ?>', 4 );
							$this->out( '<li><video src="<?php echo $video[\'src\']; ?>"></li>', 5 );
						$this->out( '<?php endforeach; ?>', 4 );
					$this->out( '</ul>', 3 );
				$this->out( '</li>', 2 );
			$this->out( '<?php endforeach; ?>', 1 );
		$this->out( '</ul>', 0, 0 );
		$this->out( '<?php' );

		return;
	}

	$this->out( '// Displaying videos with HTML5 player:' );
	$this->out( "\$videos = {$group_var}[ '" . $field['id'] . "' ] ?? '';" );
	$this->out( '?>' );
	$this->out( '<h3>Uploaded videos</h3>' );
	$this->out( '<ul>' );
		$this->out( '<?php foreach ( $videos as $video ) : ?>', 1 );
			$this->out( '<li><video src="<?php echo $video[\'src\']; ?>"></li>', 2 );
		$this->out( '<?php endforeach; ?>', 1 );
	$this->out( '</ul>', 0, 3 );
	$this->out( '<?php' );
	return;
}

if ( isset( $field['clone'] ) ) {
	// Displaying cloneable values:
	$this->out( '<?php' );
	$this->out( "\$videos = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
	$this->out( '?>' );
	$this->out( '<h3>Uploaded videos</h3>' );
	$this->out( '<ul>' );
		$this->out( '<?php foreach ( $videos as $clone ) : ?>', 1 );
			$this->out( '<li>', 2 );
				$this->out( '<ul>', 3 );
					$this->out( '<?php foreach ( $clone as $video ) : ?>', 4 );
						$this->out( '<li><video src="<?php echo $video[\'src\']; ?>"></li>', 5 );
					$this->out( '<?php endforeach; ?>', 4 );
				$this->out( '</ul>', 3 );
			$this->out( '</li>', 2 );
		$this->out( '<?php endforeach; ?>', 1 );
	$this->out( '</ul>', 0, 0 );

	return;
}

// Displaying videos with HTML5 player:
$this->out( '<?php' );
$this->out( '// Displaying videos with HTML5 player:' );
$this->out( "\$videos = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( '?>' );
$this->out( '<h3>Uploaded videos</h3>' );
$this->out( '<ul>' );
	$this->out( '<?php foreach ( $videos as $video ) : ?>', 1 );
		$this->out( '<li><video src="<?php echo $video[\'src\']; ?>"></li>', 2 );
	$this->out( '<?php endforeach; ?>', 1 );
$this->out( '</ul>', 0, 3 );

// Displaying only one video:
$this->out( '<?php' );
$this->out( '// Displaying only one video:' );
$this->out( "\$videos = rwmb_meta( '" . $this->get_encoded_value( $field['id'], [ 'limit' => 1 ] ) . ' );' );
$this->out( '$video = reset( $videos ); ' );
$this->out( '?>' );
$this->out( '<video src="<?php echo $video[\'src\'] ?>">', 0, 3 );

// Displaying videos in a player with a playlist:
$this->out( '<?php // Displaying videos in a player with a playlist: ?>' );
$this->out( '<h3>Videos</h3>', false );
$this->out( "<?php rwmb_the_value( '" . $this->get_encoded_value( $field['id'] ) . ' ); ?>', 0, 3 );

// Displaying list of videos with video player for each video:
$this->out( '<?php' );
$this->out( '// Displaying list of videos with video player for each video:' );
$this->out( "\$videos = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( '?>' );
$this->out( '<ul>' );
	$this->out( '<?php foreach ( $videos as $video ) : ?>', 1 );
		$this->out( '<?php echo wp_video_shortcode( $video ); ?>', 2 );
	$this->out( '<?php endforeach; ?>', 1 );
$this->out( '</ul>', 0, 0 );
