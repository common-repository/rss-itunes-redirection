<?php

class iTunesRSSRedirection {

	/**
	 * loads the translations into wordpress
	 */
	function load_translation() {
		load_plugin_textdomain( 'rss-itunes-redirection' );
	}

	public function init_translation() {
		// call the load_translation function into the action hook
		add_action( 'init', array( $this, 'load_translation' ) );
	}

	public function create_wp_menu() {
		add_action( 'admin_menu', array( $this, 'wp_menu' ) );
	}

	public function wp_menu( $id ) {
		add_options_page(
			__( 'iTunes Redirect', 'rss-itunes-redirection' ),
			__( 'iTunes Redirect', 'rss-itunes-redirection' ),
			'administrator',
			__FILE__,
			array(
				$this,
				'settings_page'
			)
		);
	}

	function settings_page() {
		?>
        <div class="wrap">
            <h1><?php _e( 'iTunes Redirect', 'rss-itunes-redirection' ) ?></h1>
			<?php _e( 'If you have submitted an RSS-Feed to iTunes and want to change the url to a new feed address you may know that iTunes does not provide a form or something like this. Instead you need a little code-snippet in your RSS 2.0-Feed to change the URL. This plugin will help you to do this.', 'rss-itunes-redirection' ); ?>
            <form action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" method="post">
				<?php
				settings_fields( 'rss_itunes_redirect_options' );
				do_settings_sections( __FILE__ );
				submit_button();
				?>
            </form>

            <h2><?php _e( 'Check', 'rss-itunes-redirection' ); ?></h2>
            <form action="https://validator.w3.org/feed/check.cgi" method="get" target="_blank">
                <input type="hidden" name="url"
                       value="<?php echo esc_attr( esc_url( get_bloginfo( 'rss2_url' ) ) ); ?>"/>

                <input name="Submit" type="submit" class="button"
                       value="<?php echo __( 'Check my feed with the W3C Feed Validator', 'rss-itunes-redirection' ); ?>"/>
            </form>
        </div>
		<?php
	}

	public function create_wp_settings() {
		add_action( 'admin_init', array( $this, 'wp_settings' ) );
	}

	function wp_settings() {
		register_setting(
			'rss_itunes_redirect_options',
			'rss_itunes_redirect_options'
		);

		add_settings_section(
			'rss_itunes_redirect_section',
			__( 'RSS iTunes Redirection Settings',
				'rss-itunes-redirection' ),
			array(
				$this,
				'sectionText'
			),
			__FILE__
		);

		add_settings_field(
			'iTRSSR_url',
			__( 'New URL to redirect', 'rss-itunes-redirection' ),
			array(
				$this,
				'iTRSSR_url'
			),
			__FILE__,
			'rss_itunes_redirect_section'
		);

		add_settings_field(
			'iTRSSR_namespace_add',
			__( 'Add the namespace?', 'rss-itunes-redirection' ),
			array(
				$this,
				'iTRSSR_namespace_add'
			),
			__FILE__,
			'rss_itunes_redirect_section'
		);
	}

	function iTRSSR_url() {
		$options = get_option( 'rss_itunes_redirect_options', array() );
		printf(
			'<input id="iTRSSR_url" name="rss_itunes_redirect_options[iTRSSR_url]" size="40" type="text" value="%s" />',
			array_key_exists( 'iTRSSR_url', $options ) ? sanitize_text_field( $options['iTRSSR_url'] ) : ''
		);
	}

	function iTRSSR_namespace_add() {
		$options = get_option( 'rss_itunes_redirect_options', array() );

		printf(
			'<input id="iTRSSR_namespace_add" name="rss_itunes_redirect_options[iTRSSR_namespace_add]" type="checkbox" value="1" %s /><br />',
			checked( array_key_exists( 'iTRSSR_namespace_add', $options ) && 1 === intval( $options['iTRSSR_namespace_add'] ), true, false )
		);

		printf(
			__( 'Will add the namespace: %s', 'rss-itunes-redirection' ),
			'<strong>xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"</strong>'
		);
	}

	function sectionText() {
		echo '<p>' . __( 'If you are using a podcast plugin please check if this plugin adds the namespace. If so, you do not need it again. If you are not using any of these plugins you normally need the namespace. Just check your feed whether you get an error or not:', 'rss-itunes-redirection' ) . ' <a href="http://validator.w3.org/feed/check.cgi?url=' . urlencode( get_bloginfo( 'rss2_url' ) ) . '">' . __( 'Check my feed with the W3C Feed Validator', 'rss-itunes-redirection' ) . '</a></p>';
		echo '<p>' . __( 'If you are using feedburner: Click on Troubleshootize -> Resync to update your feed on this platform as well.', 'rss-itunes-redirection' ) . '</p>';
	}

	function add_itunes_namespace_fn() {
		$options = get_option( 'rss_itunes_redirect_options', array() );
		if ( array_key_exists( 'iTRSSR_namespace_add', $options ) && $options['iTRSSR_namespace_add'] == 1 ) {
			echo 'xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"' . "\n";
		}
	}

	function add_itunes_redirection_fn() {
		$options = get_option( 'rss_itunes_redirect_options', array() );
		if ( array_key_exists( 'iTRSSR_url', $options ) && ! empty( $options['iTRSSR_url'] ) ) {
			echo "<itunes:new-feed-url>" . $options['iTRSSR_url'] . "</itunes:new-feed-url>\n";
		}
	}

	public function redirection() {
		add_action( 'rss2_ns', array( $this, 'add_itunes_namespace_fn' ) );
		add_action( 'rss2_head', array( $this, 'add_itunes_redirection_fn' ) );
	}

}