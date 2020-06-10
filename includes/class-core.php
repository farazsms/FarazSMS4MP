<?php
/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

class FARAZSMS4MP extends FARAZSMS4MP_BASE {
	private static $_instance = null;
	private static $_url = "https://ippanel.com/api/select";

	public function __construct() {
		$this->include_all();
		add_action( 'init', array( $this, 'add_languages_dir' ) );
		add_action( 'activate_plugin', [ $this, 'activate_plugin' ] );
		if ( get_transient( 'farazsms4mp-admin_notice' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notices_activated' ] );
		}
		add_filter( 'mihanpanel_sms_providers', function ( $providers ) {
			$providers['FARAZSMS4MP'] = [
				'title' => __( 'FARAZSMS4MP SMS', 'farazsms4mp' ),
				'class' => FARAZSMS4MP::class,
				'path'  => FARAZSMS4MP_DIR . 'includes/class-core.php'
			];

			return $providers;
		} );

	}

	private function include_all() {
	}

	static function activate_plugin() {
		set_transient( 'farazsms4mp-admin_notice', true, 10 );
	}

	static function add_languages_dir() {
		load_plugin_textdomain( 'farazsms4mp',
			false,
			basename( dirname( FARAZSMS4MP_INDEX_FILE ) ) . '/languages' );
	}

	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new FARAZSMS4MP();
		}

		return self::$_instance;
	}

	static function admin_notices_activated() {
		$suggest = __( '<h2> Special Offer </h2>
                 By registering in the sales cooperation system and introducing the Faraz SMS SMS system to your friends, 30% cooperation fee in
                 Get sales.
                 <a href="https://farazsms.com/affiliate" target="_blank" rel="noopener"> More info and start earning money
                     Internet </a>', 'farazsms4mp' );

		?>
        <div class='notice notice-success is-dismissible'>
            <p> <?php echo $suggest; ?> </p>
        </div>
		<?php
	}

	static function send( $to, $msg ) {
		$pass = self::get_pass();
		$uname = self::get_uname();
		$number = self::get_number();
		if ( strtolower(self::get_with_pattern()) === 'checked'){
			$url = "https://ippanel.com/patterns/pattern?username=".$uname."&password=".urlencode( $pass )."&from=".$number."&to=".json_encode([$to]). "&input_data=".urlencode(json_encode(['code'=>$msg]))."&pattern_code=".self::get_pattern_code();
			$body       = array
			(
				'uname'   => $uname,
				'pass'    => $pass,
				'from'    => $number,
				'message' => $msg,
				'to'      => $to,
				'op'      => 'send'
			);
			$resp       = wp_remote_post( $url, array(
					'method'    => 'POST',
					'headers'   => array(
						'Content-type: application/x-www-form-urlencoded'
					),
					'sslverify' => false,
					'body'      => http_build_query( ['code'=>$msg] )
				)
			);
			return json_decode($resp['body']);

        }
		$body       = array
		(
			'uname'   => $uname,
			'pass'    => $pass,
			'from'    => $number,
			'message' => $msg,
			'to'      => $to,
			'op'      => 'send'
		);
		$resp       = wp_remote_post( "https://ippanel.com/services.jspd", array(
				'method'    => 'POST',
				'headers'   => array(
					'Content-type: application/x-www-form-urlencoded'
				),
				'sslverify' => false,
				'body'      => http_build_query( $body )
			)
		);
		$resp       = json_decode( $resp['body'] );
		try {
			$phone_book = self::get_phone_book();
			if ( intval( $phone_book ) > 1 ) {
				$body     = array(
					"uname"       => $uname,
					"pass"        => $pass,
					"phoneBookId" => $phone_book,
					"op"          => "phoneBookAdd",
					"number"      => $to
				);
				$response = wp_remote_post( self::$_url, array(
						'method'      => 'POST',
						'headers'     => [
							'Content-Type' => 'application/json',
						],
						'data_format' => 'body',
						'body'        => json_encode( $body )
					)
				);
			}
		} catch ( Exception $exception ) {
		}

		return $resp;

	}

	public static function get_phone_book() {
		return self::get_option( 'phone_book' );
	}

	public static function get_option( $key ) {
		$option_key = "farazsms4mp_$key";
		$option     = get_option( $option_key );
		if ( ! $option ) {
			add_option( $option_key, null );
			$option = get_option( $option_key );
		}

		return $option;
	}

	public static function get_uname() {
		return self::get_option( 'uname' );
	}

	public static function get_pass() {
		return self::get_option( 'pass' );
	}

	public static function get_number() {
		return self::get_option( 'number' );
	}

	static function render_settings() {
		$phone_book   = intval( self::get_phone_book() );
		$number       = self::get_number();
		$uname        = self::get_uname();
		$pass         = self::get_pass();
		$with_pattern = self::get_with_pattern();
		$pattern_code = self::get_pattern_code();
		$credit_rial  = false;
		$body         = array(
			"uname" => $uname,
			"pass"  => $pass,
			'op'    => 'credit'
		);
		$response     = wp_remote_post( self::$_url, array(
				'method'      => 'POST',
				'headers'     => [
					'Content-Type' => 'application/json',
				],
				'data_format' => 'body',
				'body'        => json_encode( $body )
			)
		);
		$response     = json_decode( $response['body'] );
		if ( $response[0] !== 0 ) {
			$credit_rial = false;
		} else {
			$credit_rial = explode( ".", $response[1] )[0];
			$credit_rial = substr( $credit_rial, 0, - 1 );
		}

		if ( ! $credit_rial ) {
			echo '<div class="mp_option_single" dir="auto">' . __( 'It seems user name and password are not working yet are you saved them?', 'farazsms4mp' ) .
			     '</div>';
		}
		if ( $credit_rial ) {
			$msg = sprintf( __( 'Your credit is: %s Toman', 'farazsms4mp' ), $credit_rial );
			echo '<div class="mp_option_single" dir="auto">' . $msg . '</div>';
		}
		?>

        <div class="mp_option_single">
            <label for="farazsms4mp_uname"><?php _e( "َUser Name", "farazsms4mp" ); ?></label>
            <input dir="auto" value="<?php echo $uname; ?>" type="text" name="farazsms4mp_uname" id="farazsms4mp_uname">
        </div>
        <div class="mp_option_single">
            <label for="farazsms4mp_pass"><?php _e( "Password", "farazsms4mp" ); ?></label>
            <input dir="auto" type="text" value="<?php echo $pass; ?>" name="farazsms4mp_pass" id="farazsms4mp_pass">
        </div>
        <div class="mp_option_single">
            <label for="farazsms4mp_pass"><?php echo __( "Originator", "farazsms4mp" ); ?></label>
            <input dir="auto" type="text" value="<?php echo $number; ?>" name="farazsms4mp_number"
                   id="farazsms4mp_number">
        </div>
        <div class="mp_option_single">
            <label for="farazsms4mp_with_pattern"><?php echo __( "With Pattern", "farazsms4mp" ); ?></label>
            <input dir="auto" type="checkbox" <?php echo $with_pattern; ?> value="checked"
                   name="farazsms4mp_with_pattern"
                   id="farazsms4mp_with_pattern"
                   onchange="show_farazsms4mp_pattern_code()"
            >
        </div>
        <div class="mp_option_single" id="show_farazsms4mp_pattern_code">
            <label for="farazsms4mp_pattern_code"><?php echo __( "Pattern Code", "farazsms4mp" ); ?></label>
            <input dir="auto" type="text" value="<?php echo $pattern_code; ?>"
                   name="farazsms4mp_pattern_code"
                   id="farazsms4mp_pattern_code">
        </div>
        <script>
            function show_farazsms4mp_pattern_code() {
                document.querySelector('#farazsms4mp_pattern_code')
                    .disabled = !document.querySelector('#farazsms4mp_with_pattern').checked;
            }

            show_farazsms4mp_pattern_code();

        </script>


		<?php
		if ( $credit_rial ) {
			$phone_book_list = self::_get_phone_book_list();
			if ( $phone_book_list && sizeof( $phone_book_list ) > 0 ) {
				$phtext = __( "Phonebook", "farazsms4mp" );
				echo '<div class="mp_option_single">
                    <label for="farazsms4mp_phone_book">' . $phtext . '</label>
                    <select id="farazsms4mp_phone_book" name="farazsms4mp_phone_book" type="number">';
				foreach ( $phone_book_list as $pb ) {
					$selected = '';
					$value    = intval( $pb->id );
					$title    = $pb->title;
					if ( $value === $phone_book ) {
						$selected = "selected";
					}
					echo "<option value=$value $selected>$title</option>";
				}
				echo "</select></div>";
			}
		}

	}

	public static function get_with_pattern() {
		return self::get_option( 'with_pattern' );
	}

	public static function get_pattern_code() {
		return self::get_option( 'pattern_code' );
	}

	private static function _get_phone_book_list() {
		$body = array(
			'uname' => self::get_uname(),
			'pass'  => self::get_pass(),
			'op'    => 'booklist'
		);
		$resp = wp_remote_post( self::$_url, array(
				'method'      => 'POST',
				'headers'     => [
					'Content-Type' => 'application/json',
				],
				'data_format' => 'body',
				'body'        => json_encode( $body )
			)
		);
		$resp = json_decode( $resp['body'] );
		if ( intval( $resp[0] ) != 0 ) {
			return false;
		}

		return json_decode( $resp[1] );
	}

	static function get_provider_settings() {
		return [
			"farazsms4mp_uname",
			"farazsms4mp_pass",
			"farazsms4mp_phone_book",
			"farazsms4mp_number",
			"farazsms4mp_with_pattern",
			"farazsms4mp_pattern_code"
		];
	}

	static function validate_send_message( $response ) {
		if ( sizeof( $response ) != 2 && intval( $response[0] ) !== 0 ) {
			return false;
		}
		$res['status'] = 200;
		$res['msg']    = $response[1];

		return $res;
	}
}

FARAZSMS4MP::get_instance();
