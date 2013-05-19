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
 * View Class
 * 
 * Base view for application.
 * 
 * Renders the standard template pages.
 * 
 * @subpackage core
 * @author Gabriel Liwerant
 */
class View
{
	/**
	 * Error codes for View
	 */
	const INCORRECT_DATA_TYPE_FOR_META_TAG	= 1001;
	
	/**
	 * Store application HTTP root path
	 *
	 * @var string $_http_root_path
	 */
	protected $_http_root_path;
	
	/**
	 * Store application CSS path
	 *
	 * @var string $_css_path
	 */
	protected $_css_path;
	
	/**
	 * Store application JavaScript path
	 *
	 * @var string $_js_path
	 */
	protected $_js_path;
	
	/**
	 * Store application images path
	 *
	 * @var string $_images_path
	 */
	protected $_images_path;
	
	/**
	 * Set some properties to constants here to help us with refactoring.
	 */
	public function __construct()
	{
		$this->_http_root_path = HTTP_ROOT_PATH;
		$this->_css_path = CSS_PUBLIC_PATH;
		$this->_js_path = JS_PUBLIC_PATH;
		$this->_images_path = IMAGES_PATH;
	}
	
	/**
	 * Build IE conditional tags with embeded code.
	 *
	 * @param string $conditional The conditional statement to use
	 * @param string $embed Any code to embed in the statement
	 * 
	 * @return string Built IE conditional with embeded code
	 */
	private function _buildIeConditional($conditional, $embed)
	{
		$conditional = '<!--[if ' . $conditional . ']>' . $embed . '<![endif]-->';
		
		return $conditional;
	}
	
	/**
	 * Take an array of key => values and build a list of attributes out of it.
	 *
	 * @param array $attribute_data
	 * 
	 * @return string 
	 */
	private function _buildAttributeList($attribute_data)
	{
		$attribute_list = null;
		
		foreach ($attribute_data as $key => $value)
		{
			if ( ! empty($value))
			{
				$attribute		= $key . '="' . $value . '"';
				$attribute_list	.= $attribute . ' ';
			}
		}
		
		return $attribute_list;
	}

	/**
	 * Build subpage title string.
	 *
	 * @param string $sub_title
	 * @param string|void $separator
	 * 
	 * @return string 
	 */
	public function buildTitleSubpage($sub_title, $separator = null)
	{
		return ' ' . $separator . ' ' . $sub_title;
	}	
	
	/**
	 * Builds a standard HTML tag with optional class and/or id.
	 *
	 * @kludge I might be a bad person for writing this method.
	 * 
	 * @param string $tag
	 * @param string $text
	 * @param string|void $class
	 * @param string|void $id
	 * 
	 * @return string HTML 
	 */
	public function buildGenericHtmlWrapper($tag, $text, $class = null, $id = null)
	{
		if ( ! empty($class))
		{
			$class_attr = ' class="' . $class . '"';
		}
		else
		{
			$class_attr = null;
		}
		
		if ( ! empty($id))
		{
			$id_attr = ' id="' . $id . '"';
		}
		else
		{
			$id_attr = null;
		}
		
		return '<' . $tag . ' ' . $id_attr . $class_attr . '>' . $text . '</' . $tag . '>';
	}
	
	/**
	 * Builds the meta tags for the head view.
	 * 
	 * @param string $type Type section of meta tag
     * @param string $value Content section of meta tag
	 * 
     * @return string Completed meta tag or error message
	 */
	public function buildHeadMeta($type, $value)
	{
		if (is_string($type) AND is_string($value))
		{
            $meta = '<meta ' . $type . ' content="' . $value . '" />';
		}
		else
		{	
			throw ApplicationFactory::makeException('View Exception', self::INCORRECT_DATA_TYPE_FOR_META_TAG);
			//throw new Exception('View Exception', self::INCORRECT_DATA_TYPE_FOR_META_TAG);
		}
        
        return $meta;
	}
	
	/**
	 * Build HTML head links from data.
	 * 
	 * @todo consider refactoring attribute data to contain all attributes, 
	 *		which will require re-working json/xml
	 *
	 * @param string $rel
	 * @param string $href
	 * @param boolean $is_image Helps us build href
	 * @param array|void $attribute_data Alternate attributes
	 * @param string|void $cache_buster
	 * 
	 * @return string HTML
	 */
	public function buildHeadLink($rel, $href, $is_image, $attribute_data = null, $cache_buster = null)
	{
		$attribute_list = null;
		
		if ( ! empty($attribute_data))
		{
			$attribute_list = ' ' . $this->_buildAttributeList($attribute_data);
		}
		
		$href = $is_image ? IMAGES_PATH . '/' . $href : $href;
		
		$link = '<link rel="' . $rel . '" href="' . $href . $cache_buster . '"' . $attribute_list . '/>';
		
		return $link;
	}
	
	/**
	 * Build the HTML link tag for CSS files.
	 *
	 * @param string $name CSS file name
	 * @param array $css_data CSS file associated data
	 * @param string|void $cache_buster Appends query string to bust cache
	 * 
	 * @return string Built CSS link tag
	 */
	public function buildHeadCss($name, $css_data, $cache_buster = null)
	{
		if ((boolean)$css_data['is_internal'])
		{			
			$css = '<link rel="stylesheet" href="' . $this->_css_path . '/' . $name . '.css' . $cache_buster . '" />';
		}
		else
		{
			$css = '<link rel="stylesheet" href="' . $css_data['href'] . $cache_buster . '" />';
		}
		
		if ($css_data['ie_conditional'] !== '')
		{
			$css = $this->_buildIeConditional($css_data['ie_conditional'], $css);
		}
        
        return $css;
	}
	
	/**
	 * Build the script tags for JS files in the head section.
	 *
	 * @param array $js_data JS file associated data
	 * @param string|void $cache_buster Appends query string to bust cache
	 * 
	 * @return string Built script tag for JS file
	 */
	public function buildJs($js_data, $cache_buster = null)
	{
		$cache_buster = null;
		
		if ((bool)$js_data['is_internal'])
		{
			if ( ! empty($js_data['src']))
			{
				$src = 'src="' . $this->_js_path . '/' . $js_data['src'] . '.js' . $cache_buster . '"';
			}
			else
			{
				$src = null;
			}
			
			if ( ! isset($js_data['code']) OR empty($js_data['code']))
			{
				$code = null;
			}
			else
			{
				$code = $js_data['code'];
			}
			
			$js = '<script ' . $src . '>' . $code . '</script>';
		}
		else
		{
			$src = 'src="' . $js_data['src'] . '"';
			
			if ( ! isset($js_data['code']) OR empty($js_data['code']))
			{
				$code = null;
			}
			else
			{
				$code = $js_data['code'];
			}
			
			$js = '<script ' . $src . '>' . $code . '</script>';
		}
		
		if ( ! empty($js_data['ie_conditional']))
		{
			$js = $this->_buildIeConditional($js_data['ie_conditional'], $js);
		}

        return $js;
	}
	
	/**
	 * Build an HTML link list column for display.
	 *
	 * @param string $list_name Name of the link list
	 * @param array $list_data Data to build the links in the list
	 * 
	 * @return string
	 */
	public function buildLinkListColumn($list_name, $list_data)
	{
		$list_items = null;
		
		foreach ($list_data as $text => $path)
		{
			$list_items .= $this->buildGenericHtmlWrapper('li', $this->buildAnchorTag($text, $path, false));
		}
		
		$list = '<div class="link-column"><p>' . $list_name . '</p><ul>' . $list_items . '</ul></div>';
		
		return $list;
	}
	
	/**
	 * Build an HTML anchor tag.
	 *
	 * @param string $text Text for anchor tag display
	 * @param string $path Used to build the href attribute
	 * @param boolean $is_internal If href is local or remote
	 * @param string $target
	 * @param string|void $title Title attribute
	 * @param string|void $class Class attribute
	 * @param string|void $id Id attribute
	 * 
	 * @return string Built HTML anchor tag
	 */
	public function buildAnchorTag(
		$text,
		$path, 
		$is_internal,
		$target		= '_blank',
		$title		= null,
		$class		= null,
		$id			= null
	)
	{
		if ((boolean)$is_internal)
		{
			$href = $this->_http_root_path . '/' . $path;
		}
		else
		{
			$href = $path;
		}
		
		// Array to loop through in attribute builder method
		$anchor_data['href']	= $href;
		$anchor_data['target']	= $target;
		$anchor_data['title']	= $title;
		$anchor_data['class']	= $class;
		$anchor_data['id']		= $id;
		
		$attribute_list	= $this->_buildAttributeList($anchor_data);		
		$anchor			= '<a ' . $attribute_list . '>' . $text . '</a>';
		
		return $anchor;
	}
	
	/**
	 * Builds the header navigation for the header view.
	 *
	 * @param string $nav The HTML for the navigation item
	 * @param string $list_class CSS class to use with list item 
	 * @param string|void $separator_string Separating HTML between items
	 * 
	 * @return string Built navigation item or error message
	 */
	public function buildNav($nav, $list_class, $separator_string = null)
	{
		if ( ! empty($separator_string))
		{
			$separator	= '<span class="separator">' . $separator_string . '</span>';
		}
		else
		{
			$separator = null;
		}
		
		$header_nav = $this->buildGenericHtmlWrapper('li', $nav . $separator, $list_class);
		
		return $header_nav;
	}
	
	/**
	 * Build the HTML for the copyright.
	 *
	 * @param array $copyright_data Data pertaining to copyright information
	 * @param string|void $separator Optional separator string to append
	 * @param boolean $show_current_date Whether or not we show the current date
	 * 
	 * @return string Built HTML copyright from data
	 */
	public function buildCopyright($copyright_data, $separator = null, $show_current_date = true)
	{
		$copyright	= $copyright_data['symbol'];
		$copyright	.= ' ' . $copyright_data['holder'];
		$copyright	.= ' ' . $copyright_data['start_date'];
		
		if ($show_current_date)
		{
			if ((int)$copyright_data['start_date'] < (int)date('Y'))
			{
				$copyright .= ' - ' . date('Y');
			}
		}
		
		$separator	= '<span class="separator">' . $separator . '</span>';	
		$copyright = $this->buildGenericHtmlWrapper('li', $copyright . $separator);
		
		return $copyright;
	}
    
	/**
	 * Builds the brand logo section.
	 *
	 * @param string $src Src attribute
	 * @param string $alt Alt attribute
	 * @param string|void $id Id attribute
	 * 
	 * @return string Prepared HTML for logo with anchor tag
	 */
	public function buildBrandingLogo($src, $alt, $id = null)
	{
		$src = $this->_images_path . '/' . $src;
		
		// Array to loop through in attribute builder method
		$img_data['src']	= $src;
		$img_data['alt']	= $alt;
		$img_data['id']		= $id;
		
		$attribute_list	= $this->_buildAttributeList($img_data);		
		$logo			= '<img ' . $attribute_list . '/>';	
		
		return $logo;
	}
	
	/**
	 * Renders the page using the default page setup.
	 * 
	 * Override to use a different page structure.
	 * 
	 * @param string $page_name Specific view page to load
	 */
	public function renderPage($page_name)
	{
		require_once TEMPLATE_PATH	. '/head.php'; 
		require_once TEMPLATE_PATH	. '/header.php';
		require_once VIEW_PATH		. '/' . $page_name . '/index.php';
		require_once TEMPLATE_PATH	. '/footer.php';
	}
}
// End of View Class

/* EOF system/core/View.php */