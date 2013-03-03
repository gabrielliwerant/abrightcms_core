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
 * ErrorHandler Class
 * 
 * This class allows us to report errors, record errors, and send users to a 
 * friendly error page. Intended for use with register_shutdown_function.
 * 
 * @subpackage core
 * @author Gabriel Liwerant
 */
class ErrorHandler
{
	/**
	 * Holds an instance of the log class for error logging
	 *
	 * @var object $_log 
	 */
	private $_log;
	
	/**
	 * Stores an email object
	 *
	 * @var object $_email
	 */
	private $_email;
	
	/**
	 * Upon construction, pass in dependencies
	 *
	 * @param object $log 
	 * @param object $email 
	 */
	public function __construct(Logger $log, Email $email)
	{
		$this->_setLog($log)->_setEmail($email);
	}

	/**
	 * Setter for log
	 *
	 * @param object $log
	 * 
	 * @return object ErrorHandler 
	 */
	private function _setLog($log)
	{
		$this->_log = $log;
		
		return $this;
	}
	
	/**
	 * Setter for email
	 *
	 * @param object $email
	 * 
	 * @return object ErrorHandler 
	 */
	private function _setEmail($email)
	{
		$this->_email = $email;
		
		return $this;
	}
	
	/**
	 * Allows us to send an email notification upon fatal error.
	 *
	 * @param string $subject Subject line for email
	 * @param string $msg Email message body
	 * 
	 * @return boolean 
	 */
	private function _sendEmail($subject, $msg)
	{
		$is_successful = $this->_email
			->setEmailAddress(EMAIL_ADDRESS)
			->setSubject($subject)
			->setMessage($msg)
			->setReplyTo(EMAIL_ADDRESS)			
			->sendMessage(EMAIL_HEADERS);
		
		return $is_successful;
	}
	
	/**
	 * Allows us to log our fatal errors.
	 *
	 * @param string $msg Message to prepend to log message
	 * 
	 * @return object ErrorHandler
	 */
	private function _makeLogMessage($msg)
	{
		$log_message = $msg;
		
		foreach (error_get_last() as $key => $value)
		{
			$log_message .= $key . ' => ' . $value . ', ';
		}
		
		$log_message = rtrim($log_message, ', ');
		
		//$this->_log->writeLogToFile($log_message, 'error', 'errorLog');
		
		return $log_message;
	}
	
	/**
	 * Prepare an email notification for fatal error.
	 *
	 * @param string $subject Subject line for email
	 * @param string $msg Message to prepend to email message
	 * 
	 * @return string
	 */
	private function _makeEmailMessage($msg)
	{
		$email_message = $msg . '<br />';
		
		foreach (error_get_last() as $key => $value)
		{
			$email_message .= $key . ' => ' . $value . '<br />';
		}

		return $email_message;
	}
	
	/**
	 * Display a friendly page upon fatal error.
	 * 
	 * Useful when we are hiding normal error reporting. If we have an error, we 
	 * log it, email it, and load our error page.
	 */
	public function showFatalErrorPage()
	{
		$error_last = error_get_last();
		
		if ( ! empty($error_last) )
		{
			$log_message = $this->_makeLogMessage('Encountered fatal error: ');
			$this->_log->writeLogToFile($log_message, 'error', 'errorLog');
			
			$email_message = $this->_makeEmailMessage('Encountered fatal error: ');
			$this->_sendEmail(DOMAIN_NAME . ' Fatal Error', $email_message);
			
			header('Location:' . ERROR_HANDLER_PAGE_PATH);
		}
	}
}
// End of ErrorHandler Class

/* EOF system/core/ErrorHandler.php */