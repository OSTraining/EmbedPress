<?php
namespace EmbedPress\Ends\Back\Settings;

class EmbedpressSettings {
	var $page_slug = '';
	/**
	 * @var int|string
	 */
	protected $file_version;

	public function __construct($page_slug = 'embedpress-new') {
		$this->page_slug = $page_slug;
		$this->file_version = defined( 'WP_DEBUG') && WP_DEBUG ? time() : EMBEDPRESS_VERSION;
		add_action('admin_enqueue_scripts', [$this, 'handle_scripts_and_styles']);
		add_action('admin_menu', [$this, 'register_menu']);
		add_action( 'init', [$this, 'save_settings']);

		// ajax
		add_action( 'wp_ajax_embedpress_elements_action', [$this, 'update_elements_list']);

		// Migration
		$option = 'embedpress_elements_updated'; // to update initially for backward compatibility
		if ( !get_option( $option, false) ) {
			$elements_initial_states = [
				'gutenberg' => [
					'google-docs-block' => 'google-docs-block',
					'document' => 'document',
					'embedpress' => 'embedpress',
					'google-sheets-block' => 'google-sheets-block',
					'google-slides-block' => 'google-slides-block',
					'youtube-block' => 'youtube-block',
					'google-forms-block' => 'google-forms-block',
					'google-drawings-block' => 'google-drawings-block',
					'google-maps-maps' => 'google-maps-maps',
					'twitch-block' => 'twitch-block',
					'wistia-block' => 'wistia-block',
					'vimeo-block' => 'vimeo-block',
				],
				'elementor' => [
					'embedpress-document' => 'embedpress-document',
					'embedpress' => 'embedpress',
				],

				'classic' => [
					'frontend-preview' => 'frontend-preview',
					'backend-preview' => 'backend-preview',
				],
			];
			update_option( EMBEDPRESS_PLG_NAME.":elements", $elements_initial_states);
			update_option( $option, true);
		}

	}

	public function update_elements_list() {
		if ( !empty($_POST['_wpnonce'] && wp_verify_nonce( $_POST['_wpnonce'], 'embedpress_elements_action')) ) {
			$option = EMBEDPRESS_PLG_NAME.":elements";
			$elements = (array) get_option( $option, []);
			$type = !empty( $_POST['element_type']) ? sanitize_text_field( $_POST['element_type']) : '';
			$name = !empty( $_POST['element_name']) ? sanitize_text_field( $_POST['element_name']) : '';
			$checked = !empty( $_POST['checked']) ? $_POST['checked'] : false;
			if ( 'false' != $checked ) {
				$elements[$type][$name] = $name;
			}else{
				if( isset( $elements[$type]) && isset( $elements[$type][$name])){
					unset( $elements[$type][$name]);
				}
			}
			update_option( $option, $elements);
			wp_send_json_success();
		}
		wp_send_json_error();
	}

	public function register_menu() {
		add_menu_page( __('EmbedPress Settings', 'embedpress'), 'EmbedPress New', 'manage_options', $this->page_slug,
			[ $this, 'render_settings_page' ], null, 64 );
	}

	public function handle_scripts_and_styles() {
		if ( !empty( $_REQUEST['page']) && $this->page_slug === $_REQUEST['page'] ) {
			$this->enqueue_styles();
			$this->enqueue_scripts();
		}
	}

	public function enqueue_scripts() {
		if ( !did_action( 'wp_enqueue_media') ) {
			wp_enqueue_media();
		}
		wp_register_script( 'ep-settings-script', EMBEDPRESS_SETTINGS_ASSETS_URL.'js/settings.js', ['jquery', 'wp-color-picker' ], $this->file_version, true );
		wp_enqueue_script( 'ep-settings', EMBEDPRESS_URL_ASSETS . 'js/settings.js', ['jquery', 'wp-color-picker' ], $this->file_version, true );
		wp_localize_script( 'ep-settings-script', 'embedpressObj', array(
			'nonce'  => wp_create_nonce('embedpress_elements_action'),
		) );

		wp_enqueue_script( 'ep-settings-script');
	}

	public function enqueue_styles() {
		wp_enqueue_style( 'ep-settings-style', EMBEDPRESS_SETTINGS_ASSETS_URL.'css/style.css', null, $this->file_version );
		wp_enqueue_style( 'ep-settings-icon-style', EMBEDPRESS_SETTINGS_ASSETS_URL.'css/icon/style.css', null, $this->file_version );
		wp_enqueue_style( 'wp-color-picker' );

	}

	public function render_settings_page(  ) {
		$page_slug = $this->page_slug; // make this available for included template
		$template = !empty( $_GET['page_type'] ) ? sanitize_text_field( $_GET['page_type']) : 'general';
		$nonce_field = wp_nonce_field('ep_settings_nonce', 'ep_settings_nonce', true, false);
		$ep_page = admin_url('admin.php?page='.$this->page_slug);
		$gen_menu_template_names = apply_filters('ep_general_menu_tmpl_names', ['general', 'youtube', 'vimeo', 'wistia', 'twitch']);
		$brand_menu_template_names = apply_filters('ep_brand_menu_templates', ['custom-logo', 'branding',]);
		$pro_active = is_embedpress_pro_active();
		include_once EMBEDPRESS_SETTINGS_PATH . 'templates/main-template.php';
	}

	public function save_settings() {
		if ( !empty( $_POST['ep_settings_nonce']) && wp_verify_nonce( $_POST['ep_settings_nonce'], 'ep_settings_nonce') ) {
			$submit_type = !empty( $_POST['submit'] ) ? $_POST['submit'] : '';
			$save_handler_method  = "save_{$submit_type}_settings";
			if ( method_exists( $this, $save_handler_method ) ) {
				$this->$save_handler_method();
			}
		}
	}

	public function save_general_settings() {
		$settings = (array) get_option( EMBEDPRESS_PLG_NAME);
		$settings ['enableEmbedResizeWidth'] = isset( $_POST['enableEmbedResizeWidth']) ? intval( $_POST['enableEmbedResizeWidth']) : 600;
		$settings ['enableEmbedResizeHeight'] = isset( $_POST['enableEmbedResizeHeight']) ? intval( $_POST['enableEmbedResizeHeight']) : 550;

		// Pro will handle g_loading_animation settings and other
		$settings = apply_filters( 'ep_general_settings_before_save', $settings, $_POST);

		update_option( EMBEDPRESS_PLG_NAME, $settings);
		do_action( 'ep_general_settings_after_save', $settings, $_POST);
	}

	public function save_youtube_settings() {
		$option_name = EMBEDPRESS_PLG_NAME.':youtube';
		$settings = get_option( $option_name);
		$settings['autoplay'] = !empty( $_POST['autoplay']) ? sanitize_text_field( $_POST['autoplay']) : 0;
		$settings['controls'] = !empty( $_POST['controls']) ? sanitize_text_field( $_POST['controls']) : 0;
		$settings['fs'] = !empty( $_POST['fs']) ? sanitize_text_field( $_POST['fs']) : 0;
		$settings['iv_load_policy'] = !empty( $_POST['iv_load_policy']) ? sanitize_text_field( $_POST['iv_load_policy']) : 1;

		// Pro will handle g_loading_animation settings and other
		$settings = apply_filters( 'ep_youtube_settings_before_save', $settings);
		update_option( $option_name, $settings);
		do_action( 'ep_youtube_settings_after_save', $settings);

	}

	public function save_wistia_settings() {
		$option_name = EMBEDPRESS_PLG_NAME.':wistia';
		$settings = get_option( $option_name);
		$settings['autoplay'] = isset( $_POST['autoplay']) ? sanitize_text_field( $_POST['autoplay']) : '';
		$settings['display_fullscreen_button'] = isset( $_POST['display_fullscreen_button']) ? sanitize_text_field( $_POST['display_fullscreen_button']) : '';
		$settings['small_play_button'] = isset( $_POST['small_play_button']) ? sanitize_text_field( $_POST['small_play_button']) : '';
		$settings['player_color'] = isset( $_POST['player_color']) ? sanitize_text_field( $_POST['player_color']) : '';
		$settings['plugin_resumable'] = isset( $_POST['plugin_resumable']) ? sanitize_text_field( $_POST['plugin_resumable']) : '';
		$settings['plugin_focus'] = isset( $_POST['plugin_focus']) ? sanitize_text_field( $_POST['plugin_focus']) : '';

		// Pro will handle g_loading_animation settings and other
		$settings = apply_filters( 'ep_wistia_settings_before_save', $settings);
		update_option( $option_name, $settings);
		do_action( 'ep_wistia_settings_after_save', $settings);
	}

	public function save_vimeo_settings() {
		$option_name = EMBEDPRESS_PLG_NAME.':vimeo';
		$settings = get_option( $option_name);
		$settings['autoplay'] = isset( $_POST['autoplay']) ? sanitize_text_field( $_POST['autoplay']) : 0;
		$settings['color'] = isset( $_POST['color']) ? sanitize_text_field( $_POST['color']) : '#00adef';
		$settings['display_title'] = isset( $_POST['display_title']) ? sanitize_text_field( $_POST['display_title']) : 1;

		// Pro will handle g_loading_animation settings and other
		$settings = apply_filters( 'ep_vimeo_settings_before_save', $settings);
		update_option( $option_name, $settings);
		do_action( 'ep_vimeo_settings_after_save', $settings);
	}

	public function save_twitch_settings() {
		$option_name = EMBEDPRESS_PLG_NAME.':twitch';
		$settings = get_option( $option_name);
		$settings['embedpress_pro_twitch_autoplay'] = isset( $_POST['autoplay']) ? sanitize_text_field( $_POST['autoplay']) : '';
		$settings['embedpress_pro_fs'] = isset( $_POST['fs']) ? sanitize_text_field( $_POST['fs']) : '';

		// Pro will handle g_loading_animation settings and other
		$settings = apply_filters( 'ep_twitch_settings_before_save', $settings);
		update_option( $option_name, $settings);
		do_action( 'ep_twitch_settings_after_save', $settings);
	}

	public function save_custom_logo_settings() {
		$yt_option_name = EMBEDPRESS_PLG_NAME.':youtube';
		$yt_settings = (array) get_option( $yt_option_name, []);
		$yt_settings['branding'] = !empty( $_POST['yt_branding']) ? sanitize_text_field( $_POST['yt_branding']) : 'no';
		$yt_settings['logo_xpos'] = !empty( $_POST['yt_logo_xpos']) ? intval( $_POST['yt_logo_xpos']) : 10;
		$yt_settings['logo_ypos'] = !empty( $_POST['yt_logo_ypos']) ? intval( $_POST['yt_logo_ypos']) : 10;
		$yt_settings['logo_opacity'] = !empty( $_POST['yt_logo_opacity']) ? intval( $_POST['yt_logo_opacity']) : 0;
		$yt_settings['logo_id'] = !empty( $_POST['yt_logo_id']) ? intval( $_POST['yt_logo_id']) : '';
		$yt_settings['logo_url'] = !empty( $_POST['yt_logo_url']) ? esc_url_raw( $_POST['yt_logo_url']) : '';
		$yt_settings['cta_url'] = !empty( $_POST['yt_cta_url']) ? esc_url_raw( $_POST['yt_cta_url']) : '';
		// save branding
		$settings = (array) get_option( EMBEDPRESS_PLG_NAME, []);
		$settings['embedpress_document_powered_by'] = !empty( $_POST['embedpress_document_powered_by']) ? sanitize_text_field( $_POST['embedpress_document_powered_by']) : 'no';
		update_option( EMBEDPRESS_PLG_NAME, $settings);

		// Pro will handle g_loading_animation settings and other
		$yt_settings = apply_filters( 'ep_youtube_branding_before_save', $yt_settings);
		update_option( $yt_option_name, $yt_settings);
		do_action( 'ep_youtube_branding_after_save', $yt_settings);
	}
}