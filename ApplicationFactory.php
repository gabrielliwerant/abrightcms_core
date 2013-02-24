<?php if ( ! defined('DENY_ACCESS')) exit('403: No direct file access allowed');

/**
 * A Bright CMS
 * 
 * Core MVC/CMS framework used in TaskVolt and created for lightweight, custom
 * web applications.
 * 
 * @package A Bright CMS
 * @author Gabriel Liwerant
 */

/**
 * ApplicationFactory Class
 * 
 * @subpackage system/core
 * @author Gabriel Liwerant
 */
class ApplicationFactory
{
	/**
	 * Holds the storage type for model creation.
	 *
	 * @var string $_storage_type
	 */
	private $_storage_type;

	/**
	 * Tells us whether or not we should create a Database object during model
	 * creation.
	 *
	 * @var boolean $_has_database 
	 */
	private $_has_database;
	
	/**
	 * Set storage type for model upon construction.
	 *
	 * @param string $storage_type 
	 * @param boolean $has_database
	 */
	public function __construct($storage_type, $has_database)
	{
		$this->_setStorageType($storage_type)->_setHasDatabaseValue($has_database);
	}
	
	/**
	 * Exception factory
	 *
	 * @param string $msg
	 * @param string $code
	 * 
	 * @return object MyException 
	 */
	public static function makeException($msg = null, $code = null)
	{
		$logger = self::makeLogger();
		
		return new MyException($logger, $msg, $code);
	}
	
	/**
	 * Logger factory
	 *
	 * @return object Log
	 */
	public static function makeLogger()
	{
		return new Logger(IS_MODE_LOGGING);
	}
	
	/**
	 * Email factory
	 *
	 * @return object Email 
	 */
	public static function makeEmail()
	{
		return new Email();
	}
	
	/**
	 * Setter for storage type.
	 * 
	 * Make sure to capitalize correctly to match class name.
	 *
	 * @param string $storage_type
	 * 
	 * @return object ApplicationFactory 
	 */
	private function _setStorageType($storage_type)
	{
		// Some environments care about case when creating objects
		$storage_type = ucfirst(strtolower($storage_type));
		
		$this->_storage_type = $storage_type;
		
		return $this;
	}
	
	/**
	 * Setter for has database value
	 *
	 * @param boolean $has_database
	 * 
	 * @return object ApplicationFactory 
	 */
	private function _setHasDatabaseValue($has_database)
	{
		$this->_has_database = $has_database;
		
		return $this;
	}
	
	/**
	 * Database factory
	 * 
	 * @todo move has_database check to model creation
	 * @return object Database 
	 */
	private function _makeDatabase()
	{
		if ($this->_has_database)
		{
			return new Database();
		}
		else
		{
			return null;
		}
	}
	
	/**
	 * Factory for the storage type our model uses for retrieving template data.
	 *
	 * @return object $storage_type Storage object
	 */
	private function _makeTemplateStorage($storage_type)
	{		
		return new $storage_type();
	}
	
	/**
	 * Handles the creation of new model objects based upon the controller.
	 * 
	 * Before we create the model object, we create its dependencies. The model
	 * depends upon a storage object, so we create it and then pass it to the
	 * model constructor.
	 * 
	 * @param string $controller_name Allows us to make the correct model
	 * @param string $storage_type Type of storage object to make for model
	 * 
	 * @return object
	 */
	private function _makeModel($controller_name, $storage_type)
	{
		$model		= $controller_name . 'Model';
				
		$storage	= $this->_makeTemplateStorage($storage_type);
		$log		= $this->makeLogger();
		$db			= $this->_makeDatabase();
		
		return new $model($storage, $storage_type, $log, $db);
	}
	
	/**
	 * Handles the creation of new view objects based upon the controller.
	 * 
	 * @param string $controller_name Allows us to make the correct view
	 * 
	 * @return object
	 */
	private function _makeView($controller_name)
	{
		$view = $controller_name . 'View';

		return new $view();
	}
	
	/**
	 * Handles the creation of the new controller object.
	 * 
	 * Before we create the controller object, we create its dependencies. The
	 * controller depends upon the view and the model, so we first create the
	 * appropriate counterparts to the controller and then pass them to the
	 * controller constructor.
	 * 
	 * @param string $controller_name To construct the correct controller
	 * 
	 * @return object The controller with the name that matches the given URL.
	 */
	public function makeController($controller_name)
	{
		$model	= $this->_makeModel($controller_name, $this->_storage_type);
		$view	= $this->_makeView($controller_name);
		
		return new $controller_name($model, $view);
	}
}
// End of ApplicationFactory Class

/* EOF system/core/ApplicationFactory.php */