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
 * Model Class
 * 
 * Base model for entire application.
 * 
 * @subpackage system/core
 * @author Gabriel Liwerant
 */
class Model
{
	/**
	 * Holds an instance of the storage object.
	 *
	 * @var object $_storage
	 */	
	protected $_storage;
	
	/**
	 * Holds an instance of the Logger object.
	 *
	 * @var object $_logger 
	 */
	protected $_logger;
	
	/**
	 * Holds an instance of the Database object.
	 *
	 * @var object $_db
	 */
	protected $_db;
	
	/**
	 * Holds an instance of the KeyGenerator object.
	 *
	 * @var object $key_gen
	 */
	protected $_key_gen;
	
	/**
	 * Email object property
	 *
	 * @var object $_email
	 */
	protected $_email;	
	
	/**
	 * Upon instantiation, we pass all the objects we want our model object to
	 * contain. We also load all data required according to storage type.
	 * 
	 * @param object $storage_obj Data storage object
	 * @param string $storage_type The way data is stored and retrieved
	 * @param object $logger_obj
	 * @param object $db
	 */
	public function __construct($storage_obj, $storage_type, $logger_obj, $db = null)
	{
		$this->_setStorageObject($storage_obj)->_setLogger($logger_obj)->_setDatabase($db);

		$storage_type = strtolower($storage_type);
		
		switch ($storage_type)
		{
			case 'json' :
				$this->setFilesFromDirectoryIntoStorage(JSON_PATH, $storage_type);
				break;
			case 'xml' :
				$this->setFilesFromDirectoryIntoStorage(XML_PATH, $storage_type);
				break;
		}
	}
	
	/**
	 * Setter for storage object
	 *
	 * @param object $storage_obj
	 * 
	 * @return object Model 
	 */
	private function _setStorageObject($storage_obj)
	{
		$this->_storage	= $storage_obj;
		
		return $this;
	}

	/**
	 * Setter for Logger object
	 *
	 * @param object $logger_obj
	 * 
	 * @return object Model 
	 */
	private function _setLogger($logger_obj)
	{
		$this->_logger = $logger_obj;
		
		return $this;
	}
	
	/**
	 * Setter for Database object
	 *
	 * @param object $db
	 * 
	 * @return object Model 
	 */
	private function _setDatabase($db)
	{
		$this->_db = $db;
		
		return $this;
	}

	/**
	 * Email setter
	 *
	 * @return object IndexModel
	 */
	public function setEmail()
	{
		$this->_email = ApplicationFactory::makeEmail();

		return $this;
	}	
	
	/**
	 * KeyGenerator factory
	 *
	 * @return object KeyGenerator
	 */
	private function _makeKeyGenerator()
	{
		return new KeyGenerator();
	}
	
	/**
	 * Builds log message from data and sends to log for writing.
	 *
	 * @param string $msg
	 * @param string $type
	 * @param string $file_name
	 * 
	 * @return boolean Success or failure of log writing
	 */
	protected function _writeLog($msg, $type, $file_name)
	{
		return $this->_logger->writeLogToFile($msg, $type, $file_name);
	}
	
	/**
	 * Create a log message from an array of data.
	 *
	 * @param array $data_to_log
	 * 
	 * @return string 
	 */
	protected function _buildLogMessageFromArray($data_to_log)
	{
		$log_msg = null;
		
		foreach ($data_to_log as $key => $value)
		{
			$log_msg .= $key . ' => ' . $value . ', ';
		}

		$log_msg = rtrim($log_msg, ', ');
		
		return $log_msg;
	}
	
	/**
	 * Find all files in a given directory and set them into our storage method.
	 *
	 * @param string $dir
	 * @param string $file_type
	 * 
	 * @return object Model 
	 */
	public function setFilesFromDirectoryIntoStorage($dir, $file_type)
	{
		$file_arr = scandir($dir);
		
		foreach ($file_arr as $file)
		{
			if (preg_match('/\.' . $file_type . '/', $file))
			{
				$file_name	= explode('.', $file);
				$file_path	= $dir . '/' . $file;
				
				$this->setDataFromStorage($file_path, $file_name[0]);
			}
		}
		
		return $this;
	}
	
	/**
	 * Adds a data file to the storage property.
	 *
	 * @param string $data_file_path Path of data file to set
	 * @param string $key Name of key to reference data file in array
	 * 
	 * @return object Model
	 */
	public function setDataFromStorage($data_file_path, $key)
	{
		$this->_storage->setFileAsArray($data_file_path, $key);
		
		return $this;
	}
	
	/**
	 * We grab the data from a data file, using our storage object.
	 *
	 * @param string $data_file_name Name of the file to load as view data
	 * 
	 * @return array Data from storage file
	 */
	public function getDataFromStorage($data_file_name)
	{		
		return $this->_storage->getFileAsArray($data_file_name);
	}
	
	/**
	 * Grabs all the data from our storage.
	 *
	 * @return array All data as array of arrays
	 */
	public function getAllDataFromStorage()
	{
		return $this->_storage->getAllDataAsArray();
	}
	
	/**
	 *
	 * @param type $string
	 * 
	 * @todo must generalize encoding function names, or this method lies
	 * 
	 * @return type 
	 */
	public function getEncodedStringInStorageFormat($string)
	{
		return $this->_storage->getEncodedDataAsString($string);
	}
	
	/**
	 * Convert string from encoded data to boolean using our storage object.
	 *
	 * @param string $psuedo_boolean
	 * 
	 * @return boolean 
	 */
	public function getStringValueAsBoolean($psuedo_boolean)
	{
		return $this->_storage->getStringValueAsBoolean($psuedo_boolean);
	}
	
	/**
	 * KeyGenerator setter
	 *
	 * @return object Model
	 */
	public function setKeyGenerator()
	{
		$this->_key_gen = $this->_makeKeyGenerator();
		
		return $this;
	}
	
	/**
	 * Return a standard key from key generator object.
	 *
	 * @param integer $length Size of key
	 * @param array $type_arr Kind of key to generate
	 * 
	 * @return string 
	 */
	public function createStandardKeyFromKeyGenerator($length, $type_arr)
	{
		return $this->_key_gen->generateKeyFromStandard($length, $type_arr);
	}
	
	/**
	 * Destroy the data after it is no longer needed. We may also want to log it 
	 * from here in the future.
	 *
	 * @param string &$data Data to destroy
	 * 
	 * @return object Model
	 */
	public function destroyData(&$data)
	{
		$data = null;
		
		return $this;
	}
	
	/**
	 * Sanitize data
	 *
	 * @param string/array $data
	 * 
	 * @return string/array
	 */
	public function sanitizeData($data)
	{
		if (is_array($data))
		{
			foreach ($data as $key => $value)
			{
				$clean_data[$key] = strip_tags($value);
			}
		}
		else
		{
			$clean_data = strip_tags($data);
		}
		
		return $clean_data;
	}
	
	/**
	 * Take data from URL that was passed as serialized string through AJAX and
	 * make it into a properly-formatted POST array.
	 *
	 * @param string $url_data
	 * 
	 * @return array
	 */
	public function getDataAsPostArrayFromSerializedUrlString($url_data)
	{
		foreach($url_data as $value)
		{
			$get_arr			= explode('=', $value);
			$post[$get_arr[0]]	= $get_arr[1];
		}
		
		return $post;
	}
	
	/**
	 * Set up our email and get it ready to send.
	 *
	 * @param string $message
	 * @param string $subject
	 * @param string $reply_to
	 * @param string $address
	 * 
	 * @return object Model 
	 */
	public function prepareEmail($message, $subject = null, $reply_to = null, $address = EMAIL_ADDRESS)
	{
		$this->_email
			->setMessage($message)
			->setSubject($subject)
			->setReplyTo($reply_to)
			->setEmailAddress($address);
		
		return $this;
	}
	
	/**
	 * Validates a given email address
	 *
	 * @param string $email_address Email address to validate
	 * 
	 * @return boolean Result of validation process
	 */
	public function validateEmail($email_address)
	{
		return $this->_email->validateEmailAddress($email_address);
	}
	
	/**
	 * Runs the send message method on our email object and log a message with
	 * the email data if the sending failed.
	 *
	 * @param array $data_to_log Email data to log in the event of failure
	 * 
	 * @return boolean Whether or not the email was sent successfully by PHP
	 */
	public function sendEmail($data_to_log)
	{
		$is_successful = $this->_email->sendMessage(EMAIL_HEADERS, IS_MODE_PRODUCTION);

		if ( ! $is_successful)
		{
			$log_msg = $this->_buildLogMessageFromArray($data_to_log);
			$this->_writeLog($log_msg, 'email', 'emailLog');
		}

		return $is_successful;
	}	
}
// End of Model Class

/* EOF system/core/Model.php */