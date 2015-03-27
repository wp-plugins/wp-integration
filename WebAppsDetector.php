<?php
/**
  *  @author    Inveo s.r.o. <inqueries@inveoglobal.com>
  *  @copyright 2009-2015 Inveo s.r.o.
  *  @license   LGPL
  */

/** @class: WebAppsDetector
  * @project: Theme Provider
  * @date: 2015-03-14
  * @compatibility: PHP 5 >= 5.0.0
  * @version: 1.0.6
  */
class WebAppsDetector
{
	const PROVIDER_FILE = 'ThemeProvider.php';

	private static $_appFound = false;
	private static $_appName = 'host web application';
	private static $_appNameAbbr = 'web app';
	private static $_providerPath = '';
	private static $_providerFilePath = '';
	private static $_providerFound = false;
	private static $_connectorPath = '';
	
	private static $_appListAr = array();
	
	public static function initStatic($connectorPath)
	{
		self::$_connectorPath = $connectorPath;
		self::$_appListAr = array(
						array(
							'name' => 'PrestaShop',
							'abbr' => 'PS',
							'path' => 'modules/psthemeprovider',
							'files' => array('config/settings.inc.php', 'config/defines.inc.php')
						)
						// more apps comming soon
			);
		foreach(self::$_appListAr as $provider)
		{
			foreach($provider['files'] as $file) // app detection
				if(!file_exists(self::_makeFsPath($file)))
					continue(2);
			
			self::$_appFound = true;
			self::$_appName = $provider['name'];
			self::$_appNameAbbr = $provider['abbr'];
			self::$_providerPath = $provider['path'];
			$path = str_replace('/' , DIRECTORY_SEPARATOR, $provider['path']);
			if(file_exists(self::_makeFsPath(self::PROVIDER_FILE, $path)))
			{
				self::$_providerFound = true;
				self::$_providerFilePath = self::_makeFsPath(self::PROVIDER_FILE, $path);
				return true;
			}
			return false;
		}
		return false;
	}
	
	public static function appFound()
	{
		return self::$_appFound;
	}
	
	public static function providerFound()
	{
		return self::$_providerFound;
	}

	public static function getPath()
	{
		return self::$_providerPath;
	}
	
	public static function getProviderFile()
	{
		return self::$_providerFilePath;
	}
	
	public static function getName()
	{
		return self::$_appName;
	}
	
	public static function getNameAbbr()
	{
		return self::$_appNameAbbr;
	}
	
	private static function _makeFsPath($file, $path = '')
	{
		return dirname(self::$_connectorPath).(!empty($path) ? DIRECTORY_SEPARATOR.$path : '' ).DIRECTORY_SEPARATOR.$file;
	}

}

?>