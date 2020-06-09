<?php
/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

class FARAZSMS4MP_BASE {
	static function err_log( $obj ) {
		if ( ! WP_DEBUG ) {
			return;
		}
		$log = print_r( $obj, true );
		echo "
					    <script>
						    console.debug('$log');
						</script>
    ";
		error_log( $log );
	}
}