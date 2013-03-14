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
 * ApplicationStorageInterface Interface
 * 
 * Forces essential methods onto basic storage classes for our Application.
 * 
 * @subpackage core
 * @author Gabriel Liwerant
 */
interface ApplicationStorageInterface
{
	/**
	 * Store associative array of file contents.
	 * 
	 * @param string $data_file_path
	 * @param string $key
	 */
	public function setFileAsArray($data_file_path, $key);
	
	/**
	 * Retrieve file contents as associative array.
	 * 
	 * @param string $data_file_name
	 */
	public function getFileAsArray($data_file_name);
	
	/**
	 * Retrieve all file contents as associative array.
	 */
	public function getAllDataAsArray();
	
	/**
	 * Retrieve a string value encoded in our storage format.
	 * 
	 * @param string $string
	 */
	public function getEncodedDataAsString($string);
	
	/**
	 * Make string values of true or false into booleans.
	 * 
	 * @deprecated method is dangerous and unnecessary, we should use 0 | 1 and 
	 *		cast them as boolean instead
	 * 
	 * @param string $psuedo_boolean
	 */
	public function getStringValueAsBoolean($psuedo_boolean);
}
// End of ApplicationStorageInterface Interface

/* EOF system/core/ApplicationStorageInterface.php */