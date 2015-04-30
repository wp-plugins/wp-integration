<?php
/**
 *  @package   Theme_Provider_Connector_for_WP
 *  @author    Inveo s.r.o. <inqueries@inveoglobal.com>
 *  @copyright 2009-2015 Inveo s.r.o.
 *  @license   LGPLv2.1
 */

/**
 * Plugin Name: WP Integration
 * Plugin URI: http://www.inveostore.com
 * Description: Integrates Wordpress to any application with just one simple click.
 * Version: 1.4.04
 * Author: Inveo s.r.o.
 * Author URI: http://www.inveostore.com
 * License: LGPLv2.1
 */

// Make sure we don't expose any info if called directly
if(!function_exists('add_action'))
{
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit();
}

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'WebAppsDetector.php');
WebAppsDetector::initStatic(dirname(dirname(dirname(dirname(__FILE__)))));

define('THEMEPROVIDER_CONN_LOADED', true);
define('THEMEPROVIDER_CONN_VERSION', '1.4.03');
define('THEMEPROVIDER_CONN_REQVERSION', '1.3.00');
define('THEMEPROVIDER_CONN_APP', 'WordPress');
define('THEMEPROVIDER_CONN_APPABBR', 'WP');
define('THEMEPROVIDER_CONN_NAME', THEMEPROVIDER_CONN_APP.' integration');
define('THEMEPROVIDER_CONN_NAME_ABBR', THEMEPROVIDER_CONN_APPABBR.' integration');

if(WebAppsDetector::providerFound())
	require_once(WebAppsDetector::getProviderFile());

if(defined('THEMEPROVIDER_INIT'))
{
	class ThemeProviderConnector
		extends ThemeProviderConnectorCore
			implements ThemeProviderConnectorInterface
			{
				public static function init($parseHtml)
				{
					$options = Wpcon::getOptions();
					if(parent::$__revision < 3 || empty($options['apikey']))
						return false;

					$path = dirname(dirname($_SERVER['SCRIPT_NAME']));
					if(is_admin())
						$path = dirname($path);
					$path = (($path == '/') ? '' : $path).'/'.WebAppsDetector::getPath();
					return parent::__core(
								$path,
								(string)$options['apikey'],
								strtolower(THEMEPROVIDER_CONN_APP),
								(isset($options['internalcss']) ? (bool)$options['internalcss'] : false),
								(isset($options['externalcss']) ? (bool)$options['externalcss'] : false),
								$parseHtml,
								(int)$options['mode'],
								__CLASS__,
								(isset($options['experimental']) ? (bool)$options['experimental'] : false)
					);
				}
				
				public static function attach(&$content)
				{
					return parent::__adapt($content);
				}
		}

	if(isset($_GET[strtolower(THEMEPROVIDER_CONN_APP).'_constyle']) && WebAppsDetector::providerIsAdvance())
		add_action('init', array('Wpcon', 'wpCss'));

}

if (is_admin()) // admin actions
{
	add_action('admin_menu', array('Wpcon', 'menu'));

	if(version_compare(get_bloginfo('version'), '2.7', '>='))
	{
		add_action('admin_init', array('Wpcon', 'admin'));
		
		if(WebAppsDetector::appFound() && WebAppsDetector::providerFound() && (basename($_SERVER['SCRIPT_NAME']) == 'plugins.php' || $_GET['page'] == 'wpcon_plugin') && version_compare(THEMEPROVIDER_VERSION, WebAppsDetector::getLatest(), '<'))
			add_action('admin_notices', array('Wpcon', 'update_found'));

		if(isset($_GET['page']) && $_GET['page'] == 'wpcon_plugin')
		{
			if(!WebAppsDetector::appFound())
			{
				add_action('admin_notices', array('Wpcon', 'no_supported_app'));
			}
			elseif(!WebAppsDetector::providerFound())
			{
				add_action('admin_notices', array('Wpcon', 'no_theme_provider'));
			}
			else
			{
				if(defined('THEMEPROVIDER_LOADED'))
				{
					if(defined('THEMEPROVIDER_INIT'))
					{
						if(version_compare(THEMEPROVIDER_VERSION, THEMEPROVIDER_CONN_REQVERSION, '<'))
						{
							add_action('admin_notices', array('Wpcon', 'found_provider'));
							add_action('admin_notices', array('Wpcon', 'no_required_provider'));
						}
						else
						{
							$options = Wpcon::getOptions();
							if(empty($options['apikey']))
							{
								add_action('admin_notices', array('Wpcon', 'no_apikey'));
							}
							else
							{
								add_action('admin_notices', array('Wpcon', 'allok_provider'));
								if(!WebAppsDetector::providerIsAdvance())
									add_action('admin_notices', array('Wpcon', 'no_advance'));
							}
						}
					}
					else
					{
						add_action('admin_notices', array('Wpcon', 'found_provider'));
						add_action('admin_notices', array('Wpcon', 'no_active_theme_provider'));
					}
				}
				else
				{
					add_action('admin_notices', array('Wpcon', 'no_loaded_provider'));
				}
			}
		}
		
		add_filter('plugin_action_links_'.plugin_basename(__FILE__), array('Wpcon', 'settings_link') );

		register_activation_hook(__FILE__, array('Wpcon', 'install'));
		register_uninstall_hook(__FILE__, array('Wpcon', 'uninstall'));
	} else {
		add_action('admin_notices', array('Wpcon', 'no_required_wp'));
	}
} else {
	if(defined('THEMEPROVIDER_INIT') && basename($_SERVER['SCRIPT_NAME']) == 'index.php')
		add_action('init', array('Wpcon', 'wpInit'));
}

class Wpcon {

	private static $_optionName = 'wpcon_options';

	private static $_options = array(
					'apikey' => 'string',
					'mode' => 'int',
					'internalcss' => 'int',
					'externalcss' => 'int',
					'experimental' => 'int'
	);
	
	private static $_settings = null;

	public static function getOptions()
	{
		if(!isset(self::$_settings))
			self::$_settings = get_option(self::$_optionName);
		if(!WebAppsDetector::providerIsAdvance())
			self::$_settings['mode'] = 1;
		return self::$_settings;
	}

	public static function wpInit()
	{
		ThemeProviderConnector::init(true);
	}

	public static function wpCss()
	{
		ThemeProviderConnector::init(false);
		ThemeProviderConnector::deliverCss(
				dirname(dirname(dirname(dirname(__FILE__)))), // connector absolute path
				$_GET[strtolower(THEMEPROVIDER_CONN_APP).'_constyle'], // css file relative path
				array(
					'wp-content/plugins',
					'wp-content/themes',
					'wp-includes/css'
				) // allowed dirs with CSS files
		);
	}

	public static function admin()
	{
		register_setting(
					'wpcon_options-group',
					self::$_optionName,
					array(__CLASS__, 'options_validate')
				);
	}

	public static function menu() {
		$admin_page = add_options_page(
						THEMEPROVIDER_CONN_NAME,
						THEMEPROVIDER_CONN_NAME_ABBR,
						'manage_options',
						'wpcon_plugin',
						array(__CLASS__, 'option_page')
				);
		add_action('load-'.$admin_page, array(__CLASS__, 'help'));
	}

	public static function options_validate($input)
	{
		$input['apikey'] = trim($input['apikey']);
		if(!preg_match('/^[A-Z0-9]{16}$/i', $input['apikey']))
		{
			$input['apikey'] = '';
		}
		foreach(self::$_options as $name => $type)
		{
			if(!isset($input[$name]))
			{
				$input[$name] = self::_initializeOption($type);
			}
			else
			{
				$input[$name] = self::_sanitizeOption($input[$name], $type);
			}
		}
		return $input;
	}
	
	private static function _sanitizeOption($var, $type)
	{
		switch($type)
		{
		
			case 'int':
				$var = intval($var);
			break;
			
			case 'string':
				$var = strval($var);
			break;
		
		}
		
		return $var;
	}
	
	private static function _initializeOption($type) {
		switch($type)
		{
		
			case 'int':
				$var = 0;
			break;
			
			case 'string':
				$var = '';
			break;
		
		}
		
		return self::_sanitizeOption($var, $type);
	}
	
	public static function help()
	{
		// add_help_tab() & set_help_sidebar() since WP 3.3
		// get_current_screen() since WP 3.1
		if(version_compare(get_bloginfo('version'), '3.3', '>='))
		{
			$overview = '<p>' . sprintf(__('API security key is required, otherwise the %1$s integration to %2$s will not work.</p>', 'wpcon'), THEMEPROVIDER_CONN_APP, WebAppsDetector::getName()) .
			'<p>' . __('If you are in doubts about the Mode to choose, we recommend going with the Direct cache access mode.', 'wpcon') . '</p>' .
			'<p>' . __('You must click the Save Changes button at the bottom of the screen for the new settings to come into effect.', 'wpcon');
			
			$about = '<p>'.__('Inveo can create a fast, secure and highly reliable ecommerce site with a high degree of variability, or a user-friendly website featuring modern graphic design that will enhance your prestige.', 'wpcon') . '</p>'.
			'<p>'.__('copyright', 'wpcon').' (c) 2012-'.date('Y').' Inveo s.r.o., <a href="http://www.inveoglobal.com/">www.inveoglobal.com</a></p>'.
			'<p>'.__('You can find more plugins and modules at', 'wpcon').' <a href="http://www.inveostore.com/">www.inveostore.com</a>.</p>';

			get_current_screen()->add_help_tab(
							array(
									'id'      => 'overview',
									'title'   => __('Overview', 'wpcon'),
									'content' => $overview,
								)
							);
			get_current_screen()->add_help_tab(
							array(
									'id'      => 'about',
									'title'   => __('About', 'wpcon'),
									'content' => $about,
								)
							);
			get_current_screen()->set_help_sidebar(
								'<p><strong>' . __('For more information:', 'wpcon') . '</strong></p>' .
								'<p><a href="http://www.inveostore.com/">' . __('Inveostore.com', 'wpcon') . '</a></p>' .
								'<p><a href="http://www.inveostore.com/community/">' . __('Support Forums', 'wpcon') . '</a></p>'
							);
		}
	}

	public static function settings_link($links)
	{
		if(defined('THEMEPROVIDER_INIT') && !WebAppsDetector::providerIsAdvance())
			array_unshift(
				$links,
				'<a href="http://www.inveostore.com/theme-providers-advanced">'.__('Upgrade features', 'wpcon').' &raquo;</a>'
			);
		
		array_unshift(
			$links,
			'<a href="options-general.php?page=wpcon_plugin">'.__('Settings', 'wpcon').'</a>'
		);
		
		return $links; 
	}
	
	public static function install()
	{
		$options = array();
		foreach(self::$_options as $name => $type)
		{
			$options[$name] = self::_initializeOption($type);
			if($type == 'int')
				$options[$name] = '1';
		}
		$options['internalcss'] = $options['experimental'] = '0';
		add_option(self::$_optionName, $options, '', 'yes');
	}
	
	public static function uninstall()
	{
		delete_option(self::$_optionName);
	}
	
	public static function modes()
	{
		return array(
				'1' => array(
						'name' => 'Isolated runtime',
						'desc' => '(best compatibility)'
					),
				'2' => array(
						'name' => 'Direct cache access',
						'desc' => '(best performance)'
					),
				'3' => array(
						'name' => 'Shared runtime',
						'desc' => '(best experience)'
					)
			);
	}
	
	public static function no_supported_app()
	{
		echo self::_error_message(
					sprintf(
						__('No supported web application was found. Please make sure the %1$s is installed in a subdirectory such as %2$s or %3$s (%4$s or %5$s directory on the FTP).', 'wpcon'),
						THEMEPROVIDER_CONN_APP,
						'<strong>/blog/</strong>',
						'<strong>/news/</strong>',
						'/public_html/blog/',
						'/public_html/news/'
						)
					);
	}

	public static function no_theme_provider()
	{
		echo self::_error_message(sprintf(__('As the last step please download and install the appropriate <a href="%s">Theme provider</a> module.', 'wpcon'), 'http://www.inveostore.com/theme-providers'));
	}
	
	public static function no_active_theme_provider()
	{
		echo self::_error_message(sprintf(__('The %s Theme Provider module is not activated!', 'wpcon'), WebAppsDetector::getName()));
	}
	
	public static function no_required_provider()
	{
		echo self::_error_message(sprintf(__('The %s Theme Provider module version is too old!', 'wpcon'), WebAppsDetector::getName()));
	}

	public static function no_loaded_provider()
	{
		echo self::_error_message(sprintf(__('The %s Theme Provider module was not loaded.', 'wpcon'), WebAppsDetector::getName()));
	}
	
	public static function allok_provider()
	{
		echo self::_success_notice(sprintf(__('The %s Theme Provider module is connected!', 'wpcon'), WebAppsDetector::getName()));
	}
	
	public static function update_found()
	{
		echo self::_error_message(sprintf(__('An update of the %s Theme Provider module has been found - please update!', 'wpcon'), WebAppsDetector::getName()));
	}
	
	public static function no_advance()
	{
		echo self::_error_message(
						sprintf(
							__('To enable all features, upgrade to the paid <a href="%1$s">%2$s Theme Provider module %3$s</a>', 'wpcon'),
							'http://www.inveostore.com/theme-providers-advanced',
							WebAppsDetector::getName(),
							'&raquo;'
						)
					);
	}

	public static function found_provider()
	{
		echo self::_success_notice(__('The', 'wpcon').' '.WebAppsDetector::getName().' '.__('Theme Provider module found!', 'wpcon'));
	}
	
	public static function no_required_wp()
	{
		echo self::_error_message(__('The', 'wpcon').' '.WebAppsDetector::getName().' '.__('Theme Provider module requires WordPress 2.7+.', 'wpcon'));
	}
	
	public static function no_apikey()
	{
		echo self::_error_message(__('API security key is required.', 'wpcon'));
	}

	private static function _error_message($text)
	{
		return '<div class="error"><p>'.$text.'</p></div>';
	}
	
	private static function _success_notice($text)
	{
		return '<div class="updated"><p>'.$text.'</p></div>';
	}

	public static function option_page()
{
		$options = self::getOptions();
?>

<script type="text/javascript">
//<![CDATA[
	jQuery(document).ready(function($){
		var selected = <?php echo (int)$options['mode']; ?>;
		$('.wpcon_mode').click(function(){
			selected = mode = $(this).val();
			animateDescription($(this).val());
		});
		
		$('#wpcon_fields label').mouseover(function(){
			mode = $(this).children().val();
			animateDescription();
		});
		$('#wpcon_fields').mouseleave(function(){
			mode = selected;
			animateDescription();
		});
		
		animateDescription = function() {
			var idAr = ['1', '2', '3'];
			for (index = 0; index < idAr.length; ++index) {
				if($('#wpcon_mode'+idAr[index]).val() != mode)
					$('#wpcon_mode'+idAr[index]+'_desc').slideUp(400, function() { $('#wpcon_mode'+mode+'_desc').slideDown(); });
			}
		}
	});
//]]>
</script>
<style type="text/css">
.wpcon-notice {
	margin: 5px 0 15px;
	background: #ffffe1;
	border-left: 4px solid #fff;
	-webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
	box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
	padding: 1px 12px;
}

.wpcon-notice p {
	margin: .5em 0 !important;
	padding: 2px;
	font-size: 13px !important;
	line-height: 1.5;
}

.wpcon-notice {
	border-color: #dd3d36;
}
.wpcon-warning {
	border-color: #ffba00;
}
</style>

<div class="wrap">
<h2><?php echo THEMEPROVIDER_CONN_NAME.(WebAppsDetector::appFound() ? sprintf(__(' to %s', 'wpcon'), WebAppsDetector::getName()) : ''); ?></h2>
<form method="post" action="options.php" novalidate="novalidate">
<?php

settings_fields('wpcon_options-group');

?>
<table class="form-table">

<tr>
<th scope="row"><label for="wpcon_apikey"><?php _e('API security key', 'wpcon'); ?></label></th>
<td><input name="wpcon_options[apikey]" type="text" id="wpcon_apikey" value="<?php echo $options['apikey']; ?>" maxlength="16" class="regular-text" />
<p class="description"><?php _e('Enter the security key provided by the Theme Provider module.', 'wpcon'); ?></p></td>
</tr>
</table>
<hr />
<table class="form-table">
<tr>
<th scope="row"><?php _e('Mode', 'wpcon'); ?></th>
<td><fieldset id="wpcon_fields"><legend class="screen-reader-text"><span><?php _e('Mode', 'wpcon'); ?></span></legend><p>
<?php

$modes = Wpcon::modes();
$radioAr = array();
foreach($modes as $mid => $mname)
	$radioAr[] = '<label><input name="wpcon_options[mode]" type="radio" value="'.(int)$mid.'" id="wpcon_mode'.$mid.'" '.checked($mid, $options['mode'], false).' '.disabled(($mid != 1 && !WebAppsDetector::providerIsAdvance()), true, false).' class="wpcon_mode" />'.__($mname['name'], 'wpcon').' <strong>'.__($mname['desc'], 'wpcon').'</strong></label>';
echo implode('<br />'."\n", $radioAr);

?>
</p></fieldset>

<p class="description" id="wpcon_mode1_desc" <?php echo ($options['mode'] != 1) ? 'style="display:none"' : ''; ?>><?php
printf(__('Isolated runtime mode provides moderate performance, features perfect compatibility and keeps '.
' %1$s and %2$s in a completely sandboxed runtime environment.'.
' We recommend that you activate this mode only if you require 100%% sandboxed runtime environment of'.
' %1$s and %2$s.', 'wpcon'), THEMEPROVIDER_CONN_APP, WebAppsDetector::getName());
?></p>

<p class="description" id="wpcon_mode2_desc" <?php echo ($options['mode'] != 2) ? 'style="display:none"' : ''; ?>><?php
printf(__('Direct cache access mode provides excellent performance, features very good compatibility but requires a limited amount of the'.
' %1$s core to be loaded and executed in the same runtime environment to enable a direct cache access of the'.
' %1$s Theme Provider Module.'.
' We recommend that you activate this mode if you require a blazing-fast performance of'.
' %2$s.', 'wpcon'), WebAppsDetector::getName(), THEMEPROVIDER_CONN_APP);
?></p>

<p class="description" id="wpcon_mode3_desc" <?php echo ($options['mode'] != 3) ? 'style="display:none"' : ''; ?>><?php
printf(__('Shared runtime provides very good performance, features good compatibility and does not need any caching but it requires the entire'.
' %s core to be loaded and executed in the same runtime environment.'.
' We used many advanced programming techniques to keep the runtime environment safe &amp; stable under such conditions.'.
' We recommend that you activate this mode if you do not want to use any caching and want to get the best user experience.', 'wpcon'), WebAppsDetector::getName());
?></p>
<br /><div class="wpcon-notice"><p><?php _e('Changing this option will significantly improve performance and user experience.', 'wpcon'); ?></p></div>
</td>
</tr>
</table>
<hr />
<table class="form-table">
<tr>
<th scope="row"><?php _e('Adjust CSS stylesheets', 'wpcon') ?></th>
<td><fieldset><legend class="screen-reader-text"><span><?php _e('Adjust CSS stylesheets', 'wpcon') ?></span></legend>
<label for="wpcon_internalcss">
<input name="wpcon_options[internalcss]" type="checkbox" id="wpcon_internalcss" value="1" <?php checked('1', $options['internalcss']); ?> <?php  disabled(WebAppsDetector::providerIsAdvance(), false); ?> />
<?php printf(__('Internal Cascading Style Sheets (CSS inside the %s tag)', 'wpcon'), '<code>&lt;style&gt;</code>'); ?></label>
<br />
<label for="wpcon_externalcss"><input name="wpcon_options[externalcss]" type="checkbox" id="wpcon_externalcss" value="1" <?php checked('1', $options['externalcss']); ?> <?php  disabled(WebAppsDetector::providerIsAdvance(), false); ?> />
<?php printf(__('External Cascading Style Sheets (CSS referenced with the %s tag)', 'wpcon'), '<code>&lt;link&gt;</code>'); ?></label>
</fieldset>
<p class="description "><?php printf(__('Automatically prepends on-the-fly all selectors with %1$s and restricts styles (including those referenced by %2$s at-rule) only to %3$s elements.', 'wpcon'), '<code>#'.strtolower(THEMEPROVIDER_CONN_APP).'</code>', '<code>@import</code>', THEMEPROVIDER_CONN_APP); ?><br /><?php __('Supports CSS 1, 2, 2.1 &amp; 3 or any later backward compatible CSS version.', 'wpcon') ?></p>
<br /><div class="wpcon-notice"><p><?php printf(__('Enabling these options will instantly fix the design issues and the page layout of any %s template (it will resolve the CSS issues &amp; conflicting styles).', 'wpcon'), THEMEPROVIDER_CONN_APP); ?></p></div>
</td>
</tr>
</table>
<hr />
<table class="form-table">
<tr>
<th scope="row"><?php _e('Monkey patching ', 'wpcon'); ?></th>
<td><fieldset><legend class="screen-reader-text"><span><?php _e('Monkey patching ', 'wpcon'); ?></span></legend>
<label for="wpcon_experimental">
<input name="wpcon_options[experimental]" type="checkbox" id="wpcon_experimental" value="1" <?php checked('1', $options['experimental']); ?> <?php  disabled(WebAppsDetector::providerIsAdvance(), false); ?> />
<?php _e('Enable', 'wpcon'); ?></label>
</fieldset>
<p class="description"><?php
echo __('Turns on support for extensions affecting PHP\'s behaviour, which may help resolve certain issues.', 'wpcon').
'<br />'.__('Supported extensions: ', 'wpcon').' <a href="http://php.net/manual/intro.uopz.php">uopz</a>, <a href="http://php.net/manual/intro.runkit.php">runkit</a>.'.
'</p><br /><div class="wpcon-notice"><p>'.__('Enabling this option may improve the compatibility.', 'wpcon').'</p></div>';
?>
</td>
</tr>

</table>
<?php

submit_button();

?>
</form>
<hr />

<p style="text-align: right">
copyright &copy; 2012-<?php echo date('Y'); ?> Inveo s.r.o.
<br /><?php _e('Modules &amp; plugins:', 'wpcon'); ?> <a href="http://www.inveostore.com">www.inveostore.com</a> | <?php _e('eCommerce Services:', 'wpcon'); ?> <a href="http://www.inveoglobal.com">www.inveoglobal.com</a>
<br />
<?php echo THEMEPROVIDER_CONN_NAME.' v'.THEMEPROVIDER_CONN_VERSION; ?>
</p>

</div>
<?php
	}

}

?>