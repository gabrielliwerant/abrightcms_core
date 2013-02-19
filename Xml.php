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
 * Xml Class
 * 
 * @subpackage system/core
 * @author Gabriel Liwerant
 */
class Xml
{
	/**
	 * Error codes for Xml
	 */
	const XML_NOT_INSTANCE_OF_SIMPLE_XML_ELEMENT	= 1001;
	const INVALID_XML_FILE							= 1002;
	const COULD_NOT_CONVERT_TO_BOOELAN				= 1003;
	
	/**
	 * Stores array of arrays for xml files with their associated data.
	 *
	 * @var array $_xml
	 */
	private $_xml = array();
	
	/**
	 * Nothing to see here...
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Uses DOMDocument to check for errors in XML file.
	 *
	 * @param string $file_path
	 * 
	 * @todo consider logging errors without coupling logger class
	 * 
	 * @return boolean 
	 */
	public function isXmlFileValid($file_path)
	{
		// Allow us to retrieve internal errors
		libxml_use_internal_errors(true);

		$dom_doc = new DOMDocument('1.0', 'UTF-8');
		$dom_doc->loadXML($file_path);
		
		$errors = libxml_get_errors();
		
		if (empty($errors))
		{
			return true;
		}
		
		// @kludge error code 4 is giving an issue with XML when there is no 
		//		discerinble problem. The internet does not know what is going on 
		//		either. We throw our hands up for now and pretend that code 4 
		//		doesn't	matter.
		if ($errors[0]->level < 3 OR $errors[0]->code === 4)
		{
			// Should also be fine, but we may want to log
			//Debug::printArray($errors);
			return true;
		}
		else
		{
			//Debug::printArray($errors);
			return false;
		}
	}
	
	/**
	 * Converts SimpleXMLElement object to an array using recursion to loop
	 * through all the nested objects.
	 *
	 * @param SimpleXMLElement $xml
	 * 
	 * @return array 
	 */
	public function convertSimpleXmlElementToArray($xml) 
	{
		if ($xml instanceof SimpleXMLElement)
		{
			$children	= $xml->children();			
			//$xml_arr	= null; // Is there a way we can end without an initialized array?
		}
		else
		{
			throw ApplicationFactory::makeException('Xml Exception', self::XML_NOT_INSTANCE_OF_SIMPLE_XML_ELEMENT);
			//throw new Exception('Xml Exception', self::XML_NOT_INSTANCE_OF_SIMPLE_XML_ELEMENT);
		}

		foreach ($children as $key => $value)
		{
			if ( ! $value instanceof SimpleXMLElement)
			{
				continue;
			}
			
			$values = (array)$value->children();

			if (count($values) > 0)
			{
				$xml_arr[$key] = $this->convertSimpleXmlElementToArray($value);
			}
			else
			{
				if ( ! isset($xml_arr[$key]))
				{
					$xml_arr[$key] = (string)$value;
				}
				else
				{
					if ( ! is_array($xml_arr[$key]))
					{
						$xml_arr[$key] = array($xml_arr[$key], (string)$value);
					}
					else
					{
						$xml_arr[$key][] = (string)$value;
					}
				}
			}
		}

		return $xml_arr;
	} 
	
	/**
	 * Handles XML decoding.
	 *
	 * @param string $file_path
	 * 
	 * @return array
	 */
	public function getXmlDecode($file_path)
	{
		$is_xml_valid = $this->isXmlFileValid($file_path);

		if ($is_xml_valid)
		{
			$xml = simplexml_load_file($file_path);
		}
		else
		{
			throw ApplicationFactory::makeException('Xml Exception', self::INVALID_XML_FILE);
			//throw new Exception('Xml Exception', self::INVALID_XML_FILE);
		}

		return $this->convertSimpleXmlElementToArray($xml);
	}
	
	/**
	 * Placeholder until we have a working method. For now, use Json if this 
	 * functionality is required.
	 */
	public function getEncodedDataAsString()
	{
		throw ApplicationFactory::makeException('Xml Exception: ' . __METHOD__ . ' not yet built.');
		//throw new Exception('Xml Exception: ' . __METHOD__ . ' not yet built.');
	}
	
	/**
	 * Loads an XML file and then stores the decoded data into an array.
	 * 
	 * @param string $path Path to the XML file we want data from
	 * @param string $key Allows us to set a name for the XML array
	 * 
	 * @return object Json
	 */
	public function setFileAsArray($path, $key)
	{
		//$file_path = XML_PATH . '/' . $file_name . '.xml';

		$this->_xml[$key] = $this->getXmlDecode($path);
		
		return $this;
	}
	
	/**
	 * Get an XML file as an array from our property.
	 * 
	 * @param string $xml_key Is the name of the XML file
	 * 
	 * @return array Gets us the specific array we want 
	 */
	public function getFileAsArray($xml_key)
	{
		return $this->_xml[$xml_key];
	}
	
	/**
	 * Get all XML files as an array of arrays.
	 *
	 * @return array
	 */
	public function getAllDataAsArray()
	{
		return $this->_xml;
	}

	/**
	 * We sometimes need a true or false value passed with a string. We may 
	 * attempt to pass "true" and "false" and this function will convert them to 
	 * their intended boolean. Use with care.
	 *
	 * @param string $psuedo_boolean String we attempt to convert to boolean
	 * 
	 * @return boolean Successfully converted boolean value
	 */
	public function getStringValueAsBoolean($psuedo_boolean)
	{       
		if ($psuedo_boolean === 'true')
		{
			$real_boolean = true;
		}
		elseif ($psuedo_boolean === 'false')
		{
			$real_boolean = false;
		}
		else
		{
			throw ApplicationFactory::makeException('Xml Exception', self::COULD_NOT_CONVERT_TO_BOOELAN);
			//throw new Exception('Json Exception', self::COULD_NOT_CONVERT_TO_BOOELAN);
		}

		return $real_boolean;
	}
}
// End of Xml Class

/* EOF system/core/Xml.php */