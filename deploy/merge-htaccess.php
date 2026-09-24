<?php
/**
 * Deploy helper: merge the repo's .htaccess into the live one without clobbering it.
 *
 *   php deploy/merge-htaccess.php <repo .htaccess> <live .htaccess>
 *
 * - Replaces (or adds, at the top) the "# BEGIN EPIF Security … # END EPIF Security" block.
 * - Adds the "# BEGIN WordPress" block only if the live file has none.
 * - Leaves every other block alone (LiteSpeed Cache, Loginizer, SpeedyCache, host rules).
 * - Keeps a backup of the previous file as .htaccess.epif-bak.
 */

if ( PHP_SAPI !== 'cli' || $argc !== 3 ) {
	fwrite( STDERR, "usage: php merge-htaccess.php <repo .htaccess> <live .htaccess>\n" );
	exit( 1 );
}

function epif_block( $text, $marker ) {
	$pattern = '/^# BEGIN ' . preg_quote( $marker, '/' ) . '\R.*?^# END ' . preg_quote( $marker, '/' ) . '$/ms';
	return preg_match( $pattern, $text, $m ) ? $m[0] : '';
}

$source = file_get_contents( $argv[1] );
$epif   = epif_block( $source, 'EPIF Security' );
$wp     = epif_block( $source, 'WordPress' );
if ( '' === $epif || '' === $wp ) {
	fwrite( STDERR, "Repo .htaccess is missing its EPIF Security or WordPress block.\n" );
	exit( 1 );
}

$target  = $argv[2];
$current = is_file( $target ) ? file_get_contents( $target ) : '';
$updated = $current;

if ( '' !== epif_block( $updated, 'EPIF Security' ) ) {
	$updated = str_replace( epif_block( $updated, 'EPIF Security' ), $epif, $updated );
} else {
	$updated = $epif . "\n\n" . ltrim( $updated );
}

if ( '' === epif_block( $updated, 'WordPress' ) ) {
	$updated = rtrim( $updated ) . "\n\n" . $wp . "\n";
}

if ( $updated === $current ) {
	echo ".htaccess already up to date\n";
	exit( 0 );
}
if ( '' !== $current ) {
	copy( $target, $target . '.epif-bak' );
}
file_put_contents( $target, $updated );
echo ".htaccess updated\n";
