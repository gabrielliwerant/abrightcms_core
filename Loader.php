<?php if ( ! defined('DENY_ACCESS')) exit('403: No direct file access allowed');

/**
 * A Bright CMS
 * 
 * Open source, lightweight, web application framework and content management 
 * system in PHP.
 * 
 * @package A Bright CMS
 * @author Gabriel Liwerant
 */

/**
 * Loader Class
 * 
 * We use this with spl_autoload_register to load our class files.
 * 
 * @subpackage core
 * @author Gabriel Liwerant
 */
class Loader
{
	/**
	 * Error codes for Loader
	 */
	const FILE_PATH_COULD_NOT_BE_OPENED		= 1001;
	
	/**
	 * Stored paths for use in our autoloader method.
	 *
	 * @var array $path
	 */
	public static $path = array();
	
	/**
	 * The constructor stores the valid paths in our class for later.
	 * 
	 * @param string array $path_arr Paths to store for autoloading later
	 */
	public function __construct($path_arr)
	{
		self::_setInitialPaths($path_arr);
	}
	
	/**
	 * Setter for inial paths
	 *
	 * @param array $path_arr 
	 */
	private function _setInitialPaths($path_arr)
	{
		self::$path = $path_arr;
	}
		
	/**
	 * Validate that our file path is accurate.
	 *
	 * @param string $file_path
	 * 
	 * @return boolean 
	 */
	private static function _isFilePathValid($file_path)
	{
		if (file_exists($file_path))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Open a file path and look through it for appropriate subdirectories.
	 *
	 * @param string $path
	 * 
	 * @return array Return null if no appropriate directories were found
	 */
	private static function _getSubdirectoryArrayFromFilePath($path)
	{
		$handle	= opendir($path);
		
		if ($handle)
		{
			// Must use strict comparison to false or we have silent issues
			while (($directory = readdir($handle)) !== false)
			{
				$has_period = strstr($directory, '.');
				$is_license	= strstr($directory, 'LICENSE');
				
				// We don't want anything with a period, as it's either a single
				// file or the period only directories.
				if ( ! $has_period AND ! $is_license)
				{
					$directory_arr[] = $directory;
				}
			}

			closedir($handle);
			
			return isset($directory_arr) ? $directory_arr : null;
		}
		else
		{
			throw ApplicationFactory::makeException('Loader Exception', self::FILE_PATH_COULD_NOT_BE_OPENED);
			//throw new Exception('Loader Exception', self::FILE_PATH_COULD_NOT_BE_OPENED);
		}
	}
	
	/**
	 * Search through all subdirectories in a given path recursively until no
	 * more are found and then attempt to load the class from the final leaf.
	 *
	 * @param string $path
	 * @param string $class_name
	 */
	private static function _loadFromSubdirectory($path, $class_name)
	{
		$sub_dir_arr = self::_getSubdirectoryArrayFromFilePath($path);

		if ( ! empty($sub_dir_arr))
		{
			foreach ($sub_dir_arr as $sub_dir)
			{
				self::_loadFromSubdirectory($path . '/' . $sub_dir, $class_name);
				self::_load(array($path, $sub_dir), $class_name);
			}
		}
	}
		
	/**
	 * From an array of directories listed outermost to innermost, built the
	 * appropriate file path for our class name.
	 *
	 * @param array $directory_arr
	 * @param string $file_name
	 * 
	 * @return string 
	 */
	private static function _buildFilePath($directory_arr, $file_name)
	{
		$path = null;
		
		foreach ($directory_arr as $directory)
		{
			$path .= $directory . '/';
		}
		
		return $path . $file_name . '.php';
	}
	
	/**
	 * Load a file after we've built the proper path and checked whether or not
	 * it exists.
	 *
	 * @param array $directory_arr
	 * @param string $file_name 
	 */
	private static function _load($directory_arr, $file_name)
	{
		if (PHP_VERSION < 5.3)
		{
			$file_name = self::convertFirstCharacterToLowerCase($file_name);
		}
		
		$file_path = self::_buildFilePath($directory_arr, $file_name);

		if (self::_isFilePathValid($file_path))
		{
			require $file_path;
		}
	}
	
	/**
	 * Set additional paths for our autoloader to call upon. Useful for 
	 * assigning conditional paths after construction, like development paths.
	 *
	 * @param string $key
	 * @param string $path 
	 * 
	 * return object Loader
	 */
	public function setAdditionalPath($key, $path)
	{
		self::$path[$key] = $path;
		
		return $this;
	}
	
	/**
	 * The method to find the proper paths for autoloading our classes.
	 * 
	 * We run through the list of paths given in the class property, check
	 * subdirectories of those, and attempt to build the full class path to load.
	 * 
	 * @param string $class_name
	 */
	public static function autoload($class_name)
	{
		foreach (self::$path as $main_dir)
		{
			if ($main_dir['search_sub_dir'])
			{
				self::_loadFromSubdirectory($main_dir['path'], $class_name);
			}

			self::_load(array($main_dir['path']), $class_name);
		}
	}

	/**
	 * PHP 5.2 safe way to convert first character in a string to lower case.
	 * 
	 * This is useful for loading classes in lower PHP versions as PHP 5.2 likes 
	 * to throw a tantrum over case when loading classes.
	 *
	 * @param string $string
	 * 
	 * @return string 
	 */
	public static function _convertFirstCharacterToLowerCase($string)
	{
		return strtolower(substr($string, 0, 1)) . substr($string, 1);
	}
}
// End of Loader Class

/* EOF system/core/Loader.php */