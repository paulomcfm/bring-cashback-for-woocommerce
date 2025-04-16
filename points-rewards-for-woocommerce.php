<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wpswings.com/
 * @since             1.0.0
 * @package           points-and-rewards-for-wooCommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Bring Cashback
 * Description:       <code><strong>Bring Cashback</strong></code> plugin allow merchants to reward their loyal customers with referral rewards points on store activities. <a href="https://wpswings.com/woocommerce-plugins/?utm_source=wpswings-shop-page&utm_medium=par-org-backend&utm_campaign=more-plugin" target="_blank"> Elevate your e-commerce store by exploring more on <strong> WP Swings </strong></a>
 * Version:           2.6.2
 * Author:            WP Swings
 * Author URI:        https://wpswings.com/?utm_source=wpswings-par-official&utm_medium=par-org-backend&utm_campaign=official
 * Plugin URI:        https://wordpress.org/plugins/points-and-rewards-for-woocommerce/
 * Text Domain:       points-and-rewards-for-woocommerce
 * Domain Path:       /languages
 * Requires Plugins: woocommerce
 *
 * Requires at least    : 5.5.0
 * Tested up to         : 6.7.2
 * WC requires at least : 5.5.0
 * WC tested up to      : 9.6.2
 *
 * License:           GNU General Public License v3.0
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 */

// Carregar o autoloader do Composer (se necessário).
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
	require_once __DIR__ . '/vendor/autoload.php';
}

// Carregar variáveis de ambiente do arquivo .env.
if (class_exists('Dotenv\Dotenv')) {
	$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
	$dotenv->safeLoad();
}

// Definir a constante global para o link do backend.
if (!defined('CASHBACK_API_URL')) {
	define('CASHBACK_API_URL', getenv('CASHBACK_API_URL') ?: '');
}

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
	exit;
}

// hpos.
use Automattic\WooCommerce\Utilities\OrderUtil;
require_once ABSPATH . 'wp-admin/includes/plugin.php';

// To Activate plugin only when WooCommerce is active.
$activated = true;
$active_plugins = (array) get_option('active_plugins', array());

// Multisite Compatibility.
if (is_multisite()) {
	$active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
}

// check woo activate.
if (!(array_key_exists('woocommerce/woocommerce.php', $active_plugins) || in_array('woocommerce/woocommerce.php', $active_plugins))) {
	$activated = false;
}

$plug = get_plugins();
if ($activated) {

	// HPOS Compatibility and cart and checkout block.
	// Declare HPOS compatibility.
	add_action(
		'before_woocommerce_init',
		function () {
			if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
			}
			if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
			}
		}
	);

	/**
	 * Define the constatant of the plugin.
	 *
	 * @name define_rewardeem_woocommerce_points_rewards_constants.
	 */
	function define_rewardeem_woocommerce_points_rewards_constants()
	{

		rewardeem_woocommerce_points_rewards_constants('REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION', '2.6.2');
		rewardeem_woocommerce_points_rewards_constants('WPS_RWPR_DIR_PATH', plugin_dir_path(__FILE__));
		rewardeem_woocommerce_points_rewards_constants('WPS_RWPR_DIR_URL', plugin_dir_url(__FILE__));
		rewardeem_woocommerce_points_rewards_constants('WPS_RWPR_HOME_URL', admin_url());
	}

	/**
	 * Callable function for defining plugin constants.
	 *
	 * @name rewardeem_woocommerce_points_rewards_constants.
	 * @param string $key key of the constant.
	 * @param string $value value of the constant.
	 */
	function rewardeem_woocommerce_points_rewards_constants($key, $value)
	{

		if (!defined($key)) {
			define($key, $value);
		}
	}

	add_filter('plugin_row_meta', 'wps_wpr_doc_and_premium_link', 10, 2);
	/**
	 * Callable function for adding plugin row meta.
	 *
	 * @name wps_wpr_doc_and_premium_link.
	 * @param string $links link of the constant.
	 * @param string $file name of the plugin.
	 */
	function wps_wpr_doc_and_premium_link($links, $file)
	{

		if (strpos($file, 'points-rewards-for-woocommerce.php') !== false) {

			$row_meta = array(
				'demo' => '<a target="_blank" href="https://demo.wpswings.com/points-and-rewards-for-woocommerce-pro/?utm_source=wpswings-par-demo&utm_medium=par-org-backend&utm_campaign=par-demo"><i></i><img src="' . esc_html(WPS_RWPR_DIR_URL) . 'admin/images/Demo.svg" class="wps-info-img" alt="Demo image">' . esc_html__('Demo', 'points-and-rewards-for-woocommerce') . '</a>',
				'docs' => '<a target="_blank" href="https://docs.wpswings.com/points-and-rewards-for-woocommerce/?utm_source=wpswings-par-doc&utm_medium=par-org-backend&utm_campaign=doc"><i></i><img src="' . esc_html(WPS_RWPR_DIR_URL) . 'admin/images/Documentation.svg" class="wps-info-img" alt="Demo image">' . esc_html__('Documentation', 'points-and-rewards-for-woocommerce') . '</a>',
				'videos' => '<a target="_blank" href="https://www.youtube.com/watch?v=9BFowjkTU2Q"><i></i><img src="' . esc_html(WPS_RWPR_DIR_URL) . 'admin/images/wps-youtube.svg" class="wps-wpr-img-youtube" alt="Demo image">' . esc_html__('Video', 'points-and-rewards-for-woocommerce') . '</a>',
				'support' => '<a target="_blank" href="https://wpswings.com/submit-query/?utm_source=wpswings-par-query&utm_medium=par-org-backend&utm_campaign=submit-query"><i></i><img src="' . esc_html(WPS_RWPR_DIR_URL) . 'admin/images/Support.svg" class="wps-info-img" alt="Demo image">' . esc_html__('Support', 'points-and-rewards-for-woocommerce') . '</a>',
				'services' => '<a target="_blank" href="https://wpswings.com/woocommerce-services/?utm_source=wpswings-par-services&utm_medium=par-org-backend&utm_campaign=woocommerce-services"><i></i><img src="' . esc_html(WPS_RWPR_DIR_URL) . 'admin/images/Services.svg" class="wps-info-img" alt="Demo image">' . esc_html__('Services', 'points-and-rewards-for-woocommerce') . '</a>',
			);
			return array_merge($links, $row_meta);
		}
		return (array) $links;
	}

	/**
	 * Dynamically Generate referral Code
	 *
	 * @name wps_wpr_create_referral_code
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	function wps_wpr_create_referral_code()
	{

		$length = 10;
		$pkey = '';
		$alphabets = range('A', 'Z');
		$numbers = range('0', '9');
		$final_array = array_merge($alphabets, $numbers);

		while ($length--) {
			$key = array_rand($final_array);
			$pkey .= $final_array[$key];
		}
		return $pkey;
	}

	/*** +++++++ Declare HPOS compatibility. ++++++++ */

	/**
	 * This function is used to check hpos enable.
	 *
	 * @return boolean
	 */
	function wps_wpr_is_hpos_enabled()
	{

		$is_hpos_enable = false;
		if (class_exists('Automattic\WooCommerce\Utilities\OrderUtil') && OrderUtil::custom_orders_table_usage_is_enabled()) {

			$is_hpos_enable = true;
		}
		return $is_hpos_enable;
	}

	/**
	 * This function is used to get post meta data.
	 *
	 * @param  string $id        id.
	 * @param  string $meta_key  meta key.
	 * @param  bool   $bool meta bool.
	 * @return string
	 */
	function wps_wpr_hpos_get_meta_data($id, $meta_key, $bool)
	{

		if (wps_wpr_is_hpos_enabled() && 'shop_order' === OrderUtil::get_order_type($id)) {

			$order = wc_get_order($id);
			$meta_value = $order->get_meta($meta_key, $bool);
		} else {

			$meta_value = get_post_meta($id, $meta_key, $bool);
		}
		return $meta_value;
	}

	/**
	 * This function is used to update meta data.
	 *
	 * @param string $id id.
	 * @param string $meta_key meta_key.
	 * @param string $meta_value meta_value.
	 * @return void
	 */
	function wps_wpr_hpos_update_meta_data($id, $meta_key, $meta_value)
	{

		if (wps_wpr_is_hpos_enabled() && 'shop_order' === OrderUtil::get_order_type($id)) {

			$order = wc_get_order($id);
			$order->update_meta_data($meta_key, $meta_value);
			$order->save();
		} else {

			update_post_meta($id, $meta_key, $meta_value);
		}
	}

	/**
	 * This function is used delete meta data.
	 *
	 * @param string $id       id.
	 * @param string $meta_key meta_key.
	 * @return void
	 */
	function wps_wpr_hpos_delete_meta_data($id, $meta_key)
	{

		if (wps_wpr_is_hpos_enabled() && 'shop_order' === OrderUtil::get_order_type($id)) {

			$order = wc_get_order($id);
			$order->delete_meta_data($meta_key);
			$order->save();
		} else {

			delete_post_meta($id, $meta_key);
		}
	}

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path(__FILE__) . 'includes/class-points-rewards-for-woocommerce.php';

	add_shortcode('MYCURRENTPOINT', 'wps_wpr_mytotalpoint_shortcode');
	/**
	 * Shortcode for the total points
	 *
	 * @name wps_wpr_mytotalpoint_shortcode
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	function wps_wpr_mytotalpoint_shortcode()
	{
		$user_ID = get_current_user_ID();
		$wps_wpr_other_settings = get_option('wps_wpr_other_settings', array());
		if (!empty($wps_wpr_other_settings['wps_wpr_other_shortcode_text'])) {
			$wps_wpr_shortcode_text_point = $wps_wpr_other_settings['wps_wpr_other_shortcode_text'];
		} else {
			$wps_wpr_shortcode_text_point = __('Your Current Point', 'points-and-rewards-for-woocommerce');
		}
		if (isset($user_ID) && !empty($user_ID)) {
			$get_points = (int) get_user_meta($user_ID, 'wps_wpr_points', true);
			return '<div class="wps_wpr_shortcode_wrapper">' . $wps_wpr_shortcode_text_point . ' ' . $get_points . '</div>';
		}
	}

	add_shortcode('MYCURRENTUSERLEVEL', 'wps_wpr_mycurrentlevel_shortcode');
	/**
	 * Display your Current Level by using shortcode
	 *
	 * @name wps_wpr_mycurrentlevel_shortcode
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	function wps_wpr_mycurrentlevel_shortcode()
	{
		$user_ID = get_current_user_ID();
		$wps_wpr_other_settings = get_option('wps_wpr_other_settings', array());
		if (!empty($wps_wpr_other_settings['wps_wpr_shortcode_text_membership'])) {
			$wps_wpr_shortcode_text_membership = $wps_wpr_other_settings['wps_wpr_shortcode_text_membership'];
		} else {
			$wps_wpr_shortcode_text_membership = __('Your Current Membership Level is', 'points-and-rewards-for-woocommerce');
		}
		if (isset($user_ID) && !empty($user_ID)) {
			$user_level = get_user_meta($user_ID, 'membership_level', true);
			if (isset($user_level) && !empty($user_level)) {
				return $wps_wpr_shortcode_text_membership . ' ' . $user_level;
			}
		}
	}

	add_shortcode('SIGNUPNOTIFICATION', 'wps_wpr_signupnotif_shortcode');
	/**
	 * Display the SIgnup Notification by using shortcode
	 *
	 * @name wps_wpr_signupnotif_shortcode
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	function wps_wpr_signupnotif_shortcode()
	{
		$general_settings = get_option('wps_wpr_settings_gallery', true);
		$enable_wps_signup = isset($general_settings['wps_wpr_general_signup']) ? intval($general_settings['wps_wpr_general_signup']) : 0;
		if ($enable_wps_signup && !is_user_logged_in()) {
			$wps_wpr_signup_value = isset($general_settings['wps_wpr_general_signup_value']) ? intval($general_settings['wps_wpr_general_signup_value']) : 1;

			return '<div class="woocommerce-message">' . esc_html__('You will get ', 'points-and-rewards-for-woocommerce') . esc_html($wps_wpr_signup_value) . esc_html__(' points for SignUp', 'points-and-rewards-for-woocommerce') . '</div>';
		}
	}

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_rewardeem_woocommerce_points_rewards()
	{
		define_rewardeem_woocommerce_points_rewards_constants();
		$plugin = new Points_Rewards_For_Woocommerce();
		$plugin->run();

	}
	run_rewardeem_woocommerce_points_rewards();

	// Add settings link on plugin page.
	add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'rewardeem_woocommerce_points_rewards_settings_link');
	/**
	 * Settings tab of the plugin.
	 *
	 * @name rewardeem_woocommerce_points_rewards_settings_link
	 * @param array $links array of the links.
	 * @since    1.0.0
	 */
	function rewardeem_woocommerce_points_rewards_settings_link($links)
	{
		$nonce = wp_create_nonce('par_main_setting');
		$my_link = array(
			'settings' => '<a href="' . admin_url('admin.php?page=wps-rwpr-setting&nonce=' . $nonce) . '">' . esc_html__('Settings', 'points-and-rewards-for-woocommerce') . '</a>',
		);
		$mfw_plugins = get_plugins();
		if (!isset($mfw_plugins['ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php'])) {

			$my_link['goPro'] = '<a class="wps-wpr-go-pro" target="_blank" href="https://wpswings.com/product/points-and-rewards-for-woocommerce-plugin/?utm_source=wpswings-par-pro&utm_medium=par-org-backend&utm_campaign=go-pro">' . esc_html__('GO PRO', 'points-and-rewards-for-woocommerce') . '</a>';
		}
		return array_merge($my_link, $links);
	}

	/**
	 * This function is used to return the date format as per WP settings
	 *
	 * @name wps_wpr_set_the_wordpress_date_format
	 * @param string $saved_date saved data in the WordPress formet.
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	function wps_wpr_set_the_wordpress_date_format($saved_date)
	{

		if (!empty($saved_date)) {

			if (get_locale() == 'zh_TW') {
				return $saved_date;
			}

			$saved_date = strtotime($saved_date);
			$date_format = get_option('date_format', 'Y-m-d');
			$time_format = get_option('time_format', 'g:i a');
			$wp_date = date_i18n($date_format, $saved_date);
			$wp_time = date_i18n($time_format, $saved_date);
			$return_date = $wp_date . ' ' . $wp_time;
			return $return_date;
		}
		return $saved_date;
	}

	if (!function_exists('array_key_first')) {
		/**
		 * This function is used to return the first key
		 *
		 * @name array_key_first
		 * @param array $arr optional parameter.
		 * @author WP Swings <webmaster@wpswings.com>
		 * @link https://www.wpswings.com/
		 */
		function array_key_first(array $arr)
		{
			foreach ($arr as $key => $unused) {
				return $key;
			}
			return null;
		}
	}

	register_activation_hook(__FILE__, 'wps_wpr_flush_rewrite_rules');
	register_deactivation_hook(__FILE__, 'wps_wpr_flush_rewrite_rules');
	/**
	 * This function is used to create points tab.
	 *
	 * @param string $network_wide network_wide.
	 * @return void
	 */
	function wps_wpr_flush_rewrite_rules($network_wide)
	{

		// Multisite compatibility.
		global $wpdb;
		if (is_multisite() && $network_wide) {
			// Get all blogs in the network and activate plugin on each one.
			$blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
			foreach ($blog_ids as $blog_id) {
				switch_to_blog($blog_id);

				add_rewrite_endpoint('points', EP_PAGES);
				add_rewrite_endpoint('view-log', EP_PAGES);
				flush_rewrite_rules();

				restore_current_blog();
			}
		} else {

			add_rewrite_endpoint('points', EP_PAGES);
			add_rewrite_endpoint('view-log', EP_PAGES);
			flush_rewrite_rules();
		}
	}

	/**
	 * Add endpoints when a new blog is created.
	 *
	 * @param object $new_site New site object.
	 * @return void
	 */
	function wps_wpr_on_create_blogs($new_site)
	{

		if (!function_exists('is_plugin_active_for_network')) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		if (is_plugin_active_for_network('points-rewards-for-woocommerce/points-rewards-for-woocommerce.php')) {
			$blog_id = $new_site->blog_id;
			switch_to_blog($blog_id);

			add_rewrite_endpoint('points', EP_PAGES);
			add_rewrite_endpoint('view-log', EP_PAGES);
			flush_rewrite_rules();

			restore_current_blog();
		}
	}
	add_action('wp_initialize_site', 'wps_wpr_on_create_blogs', 900);

	add_action('admin_notices', 'wps_banner_notification_plugin_html');
	if (!function_exists('wps_banner_notification_plugin_html')) {

		/**
		 * Common Function To show banner image.
		 *
		 * @return void
		 */
		function wps_banner_notification_plugin_html()
		{
			$screen = get_current_screen();
			if (isset($screen->id)) {
				$pagescreen = $screen->id;
			}

			if ((isset($pagescreen) && 'plugins' === $pagescreen) || ('wp-swings_page_home' == $pagescreen)) {
				$banner_id = get_option('wps_wgm_notify_new_banner_id', false);
				if (isset($banner_id) && '' !== $banner_id) {

					$hidden_banner_id = get_option('wps_wgm_notify_hide_baneer_notification', false);
					$banner_image = get_option('wps_wgm_notify_new_banner_image', '');
					$banner_url = get_option('wps_wgm_notify_new_banner_url', '');
					if (isset($hidden_banner_id) && $hidden_banner_id < $banner_id) {
						if ('' !== $banner_image && '' !== $banner_url) {

							?>
							<div class="wps-offer-notice notice notice-warning is-dismissible">
								<div class="notice-container">
									<a href="<?php echo esc_url($banner_url); ?>" target="_blank"><img src="<?php echo esc_url($banner_image); ?>"
											alt="Subscription cards" /></a>
								</div>
								<button type="button" class="notice-dismiss dismiss_banner" id="dismiss-banner"><span
										class="screen-reader-text">Dismiss this notice.</span></button>
							</div>

							<?php
						}
					}
				}
			}
		}
	}

	add_action('admin_notices', 'wps_wpr_banner_notify_html');
	/**
	 * Function to show banner image based on subscription.
	 *
	 * @return void
	 */
	function wps_wpr_banner_notify_html()
	{

		if (wp_verify_nonce(!empty($_GET['nonce']) ? sanitize_text_field(wp_unslash($_GET['nonce'])) : '', 'par_main_setting')) {
			if ((isset($_GET['page']) && 'wps-rwpr-setting' === $_GET['page'])) {

				$banner_id = get_option('wps_wgm_notify_new_banner_id', false);
				if (isset($banner_id) && '' !== $banner_id) {

					$hidden_banner_id = get_option('wps_wgm_notify_hide_baneer_notification', false);
					$banner_image = get_option('wps_wgm_notify_new_banner_image', '');
					$banner_url = get_option('wps_wgm_notify_new_banner_url', '');
					if (isset($hidden_banner_id) && $hidden_banner_id < $banner_id) {
						if ('' !== $banner_image && '' !== $banner_url) {

							?>
							<div class="wps-offer-notice notice notice-warning is-dismissible">
								<div class="notice-container">
									<a href="<?php echo esc_url($banner_url); ?>" target="_blank"><img src="<?php echo esc_url($banner_image); ?>"
											alt="Subscription cards" /></a>
								</div>
								<button type="button" class="notice-dismiss dismiss_banner" id="dismiss-banner"><span
										class="screen-reader-text">Dismiss this notice.</span></button>
							</div>
							<?php
						}
					}
				}
			}
		}
	}

	register_deactivation_hook(__FILE__, 'wps_wpr_remove_cron_for_banner_update');
	/**
	 * This function is used to remove banner schedule cron.
	 *
	 * @return void
	 */
	function wps_wpr_remove_cron_for_banner_update()
	{
		wp_clear_scheduled_hook('wps_wgm_check_for_notification_update');
	}

	/**
	 * This function is used to restrict user.
	 *
	 * @return bool
	 */
	function wps_wpr_restrict_user_fun()
	{

		$wps_wpr_restrict_user = get_user_meta(get_current_user_id(), 'wps_wpr_restrict_user', true);
		$check_enable = false;
		if ('yes' === $wps_wpr_restrict_user) {

			$check_enable = true;
		}
		return $check_enable;
	}

	/**
	 * This function is used to check whether subscription is active or not.
	 *
	 * @return bool
	 */
	function wps_wpr_check_is_subscription_plugin_active()
	{

		$flag = false;
		if (is_plugin_active('subscriptions-for-woocommerce/subscriptions-for-woocommerce.php') || is_plugin_active('woocommerce-subscriptions/woocommerce-subscriptions.php')) {

			$flag = true;
		}
		return $flag;
	}

	add_action('woocommerce_thankyou', 'wps_wpr_handle_cashback_usage');
	/**
	 * Envia informações do pedido e o link da loja após a conclusão do pedido.
	 *
	 * @param int $order_id ID do pedido.
	 */
	function wps_wpr_handle_cashback_usage($order_id)
	{
		// VERIFICA SE O USUARIO ESTA APTO A RECEBER CASHBACK
		// Obtém o pedido.
		$order = wc_get_order($order_id);

		// Verifica se o pedido é válido.
		if (!$order) {
			return;
		}

		// Verifica se o pedido utilizou cashback.
		$used_cashback = get_post_meta($order_id, 'wps_cart_discount#points', true);
		if (empty($used_cashback)) {
			return;
		}

		if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $user_data = array(
                'id' => $current_user->ID,
                'name' => $current_user->display_name,
                'email' => $current_user->user_email,
            );
        } else {
            // Caso o usuário não esteja logado, usar os dados de pagamento do pedido.
            $user_data = array(
                'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'email' => $order->get_billing_email(),
                'phone' => $order->get_billing_phone(),
            );
        }

		// Prepara os dados do pedido.
		$order_data = $order->get_data(); // Obtém os dados completos do pedido.
		$store_url = get_site_url(); // Obtém a URL da loja.

		// Monta o payload para o backend.
		$payload = array(
			'order' => $order_data, // Dados do pedido.
			'store' => array(
				'url' => $store_url,  // URL da loja.
			),
		);

		// Envia os dados para o backend.
		$response = wp_remote_post(CASHBACK_API_URL, array(
			'method' => 'POST',
			'body' => json_encode($payload),
			'headers' => array(
				'Content-Type' => 'application/json',
			),
		));

		// Verifica se houve erro na requisição.
		if (is_wp_error($response)) {
			error_log('Erro ao conectar ao backend de cashback: ' . $response->get_error_message());
			return;
		}

		// Verifica a resposta do backend.
		$response_body = wp_remote_retrieve_body($response);
		$decoded_response = json_decode($response_body, true);

		if (!isset($decoded_response['success']) || !$decoded_response['success']) {
			error_log('Erro ao processar cashback no backend: ' . $response_body);
			return;
		}

		// Log de sucesso.
		error_log('Cashback processado com sucesso no backend.');
	}

	add_action( 'wp_login', 'wps_wpr_update_user_balance_on_login', 10, 2 );

	/**
	 * Atualiza o saldo do usuário quando ele faz login.
	 *
	 * @param string $user_login Nome de usuário.
	 * @param WP_User $user Objeto do usuário.
	 */

	function wps_wpr_update_user_balance_on_login( $user_login, $user ) {
		try {
			$user_email = $user->user_email;

			$store_url = get_site_url();

			$encoded_email = urlencode( $user_email );
			$encoded_store_url = urlencode( $store_url );

			$api_url = "http://localhost:5000/api/balances/store-balance/{$encoded_email}/{$encoded_store_url}";

			$response = wp_remote_get( $api_url, array(
				'timeout' => 15,
				'headers' => array(
					'Content-Type' => 'application/json',
				),
			) );

			if ( is_wp_error( $response ) ) {
				throw new Exception( 'Erro ao conectar à API de saldo: ' . $response->get_error_message() );
			}

			$response_body = wp_remote_retrieve_body( $response );

			$decoded_response = json_decode( $response_body, true );

			if ( ! isset( $decoded_response['amount'] ) ) {
				throw new Exception( 'Resposta inválida da API de saldo: ' . $response_body );
			}

			$new_balance = (float) $decoded_response['amount'];

			$current_balance = (float) get_user_meta( $user->ID, 'wps_wpr_points', true );

			$sign = $new_balance > $current_balance ? '+' : '-';

			$points_difference = abs( $new_balance - $current_balance );

			update_user_meta( $user->ID, 'wps_wpr_points', $new_balance );

			$points_details = get_user_meta( $user->ID, 'points_details', true );
			$points_details = ! empty( $points_details ) && is_array( $points_details ) ? $points_details : array();

			$today_date = date_i18n( 'Y-m-d h:i:sa' );
			$new_entry = array(
				'admin_points' => (string) $points_difference,
				'date'         => $today_date,
				'sign'         => $sign,
				'reason'       => 'Saldo atualizado ao fazer login',
			);

			if ( ! isset( $points_details['admin_points'] ) || ! is_array( $points_details['admin_points'] ) ) {
				$points_details['admin_points'] = array();
			}

			$points_details['admin_points'][] = $new_entry;

			update_user_meta( $user->ID, 'points_details', $points_details );

			error_log( "Saldo atualizado para o usuário {$user->ID} ({$user_email}): Novo saldo: {$new_balance}, Diferença: {$points_difference}, Sinal: {$sign}" );
		} catch ( Exception $e ) {
			error_log( 'Erro na função wps_wpr_update_user_balance_on_login: ' . $e->getMessage() );
		}
	}
} else {

	// WooCommerce is not active so deactivate this plugin.
	add_action('admin_init', 'rewardeem_woocommerce_points_rewards_activation_failure');

	/**
	 * This function is used to deactivate plugin.
	 *
	 * @name rewardeem_woocommerce_points_rewards_activation_failure
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	function rewardeem_woocommerce_points_rewards_activation_failure()
	{
		deactivate_plugins(plugin_basename(__FILE__));
		unset($_GET['activate']);
		// Add admin error notice.
		add_action('admin_notices', 'rewardeem_woocommerce_points_rewards_activation_failure_admin_notice');
	}

	/**
	 * This function is used to deactivate plugin.
	 *
	 * @name rewardeem_woocommerce_points_rewards_activation_failure
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	function rewardeem_woocommerce_points_rewards_activation_failure_admin_notice()
	{
		// hide Plugin activated notice.
		if (!is_plugin_active('woocommerce/woocommerce.php')) {
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php esc_html_e('WooCommerce is not activated, Please activate WooCommerce first to activate Bring Cashback.', 'points-and-rewards-for-woocommerce'); ?>
				</p>
			</div>
			<?php
		}
	}
}

