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
 * Controller Class
 * 
 * Base controller for the entire application.
 * 
 * Holds instance of model and view.
 * 
 * @subpackage core
 * @author Gabriel Liwerant
 */
class Controller
{	
	/**
	 * Holds an instance of the base model
	 *
	 * @var object $_model
	 */
	protected $_model;
	
	/**
	 * Holds an instance of the base view
	 *
	 * @var object $_view
	 */
	protected $_view;
	
	/**
	 * Upon construction we store the model object and the view object.
	 *  
	 * @param object $model Model object we store and use in the controller
	 * @param object $view View object we store and use in the controller
	 */
	public function __construct($model, $view)
	{
		$this->_setModel($model)->_setView($view);
	}
	
	/**
	 * Model setter
	 *
	 * @param object $model 
	 * 
	 * @return object Controller
	 */
	protected function _setModel($model)
	{
		$this->_model = $model;
		
		return $this;
	}
	
	/**
	 * View setter
	 *
	 * @param object $view 
	 * 
	 * @return object Controller
	 */
	protected function _setView($view)
	{
		$this->_view = $view;
		
		return $this;
	}
	
	/**
	 * Generic setter for view properties
	 *
	 * @param string $property Name of property to set in view
	 * @param string $data
	 * 
	 * @return object Controller 
	 */
	protected function _setViewProperty($property, $data)
	{
		$this->_view->$property = $data;
		
		return $this;
	}
	
    /**
     * Set the view properties for the rendering of the basic HTML head 
     * information.
     * 
     * @param string array $head_data HTML document data for head section
	 * 
	 * @return object Controller
     */
    private function _setHeadDoc($head_data)
    {
        foreach($head_data as $key => $value)
		{
			$this->_setViewProperty($key, $value);
		}
		
		return $this;
    }
	
    /**
     * Set the view property for the rendering of the head meta tags.
     * 
     * @param string array $head_meta Meta types and values for building
	 * 
	 * @return object Controller
     */
    private function _setHeadMeta($head_meta)
    {
        $property_data = null;
        
        foreach ($head_meta as $content_type => $content_data)
        {
            foreach($content_data as $type => $value)
			{
				$meta_content	= $content_type . '=' . $type;
				$property_data	.= $this->_view->buildHeadMeta($meta_content, $value);
			}
        }
		
		return $this->_setViewProperty('meta', $property_data);
    }
    
	/**
	 * Set the view property for the head CSS link tags.
	 *
	 * @param array $include_data Data with CSS file names and other info
	 * @param string $cache_buster Optional random string to force re-caching
	 * 
	 * @return object Controller
	 */
	private function _setHeadIncludesCss($include_data, $cache_buster)
	{
		$property_data = null;
        
        foreach ($include_data as $name => $css_data)
        {
            $property_data .= $this->_view->buildHeadCss($name, $css_data, $cache_buster);
        }

		return $this->_setViewProperty('css', $property_data);
	}

	/**
	 * Set a view property for JavaScript script tags.
	 *
	 * @param string $property Name of property to set in view
	 * @param array $include_data JS file names and other info
	 * @param string $cache_buster Optional random string to force re-caching
	 * 
	 * @return object Controller
	 */
	private function _setJs($property, $include_data, $cache_buster)
	{
		$property_data = null;
		
		foreach ($include_data as $name => $js_data)
		{
			$property_data .= $this->_view->buildJs($js_data, $cache_buster);
		}
		
		return $this->_setViewProperty($property, $property_data);
	}	
	
	/**
	 * Set view property for head links with built HTML.
	 *
	 * @param array $link_data
	 * @param string $cache_buster
	 * 
	 * @return object Controller 
	 */
	protected function _setHeadIncludesLink($link_data, $cache_buster)
	{
		$links = null;
		
		foreach ($link_data as $property_name => $data)
		{		
			if (isset($data['attributes']))
			{
				$attribute_data = $data['attributes'];
			}
			else
			{
				$attribute_data = null;
			}		

			if ((boolean)$data['is_image'])
			{
				$href = IMAGES_PATH . '/' . $data['href'];
			}
			else
			{
				$href = $data['href'];
			}
			
			$links .= $this->_view->buildHeadLink($data['rel'], $href, $attribute_data, $cache_buster);
		}
		
		return $this->_setViewProperty('head_links', $links);
	}
	
	/**
	 * Setter for the title page view property
	 *
	 * @param string $sub_title
	 * @param string|void $separator Optional separator subpage
	 * 
	 * @return object Controller 
	 */
	protected function _setTitleSubpage($sub_title, $separator = null)
	{
		$sub_title = $this->_view->buildTitleSubpage($sub_title, $separator);

		return $this->_setViewProperty('title_subpage', $sub_title);
	}
	
    /**
     * Set the view property for the rendering of the navigation.
     * 
	 * @param string $nav_property
     * @param array $nav_data Name and other information for the nav
	 * @param string|void $separator Optional separator in HTML for nav items
	 * 
	 * @return object Controller
     */
    protected function _setNav($nav_property, $nav_data, $separator = null)
    {
        $property_data	= null;
		$i				= 1;        

		foreach ($nav_data as $nav => $data)
        {
            if ($i === 1)
			{
				$list_class = 'first';
			}			
			elseif ($i === count($nav_data))
			{
				$list_class = 'last';
				$separator	= null;
			}
			else
			{
				$list_class= null;
			}
			
			if ((boolean)$data['is_anchor'])
			{			
				$nav_item = $this->_view->buildAnchorTag(
					$data['text'],
					$data['path'], 
					$data['is_internal'], 
					$data['target'],
					$data['title'],
					$data['class'],
					$data['id']
				);
			}
			else
			{				
				$nav_item = $data['text'];
			}
			
			$property_data .= $this->_view->buildNav($nav_item, $list_class, $separator);

			$i++;
        }
		
		return $this->_setViewProperty($nav_property, $property_data);
    }

	/**
	 * Set sub navigation into view property.
	 * 
	 * Finds all the content files corresponding to the sub section, loop
	 * through them to prepare the necessary data for the navigation, and then
	 * build the navigation items for display.
	 *
	 * @param string $sub_directory Directory where the files are
	 * @param string $storage_type Type of files for pathing, file reading
	 * @param string $api_path Used to build href
	 * @param string $sub_section_title Current section title to compare
	 * 
	 * @return object Controller 
	 */
	protected function _setSubNav($sub_directory, $storage_type, $api_path, $sub_section_title)
	{
		$dir = $this->_model->getStorageTypePath($storage_type) . $sub_directory;		
		$this->_model->setFilesFromDirectoryIntoStorage($dir, $storage_type);
		
		$dir_files_arr		= $this->_model->getStorageFilesFromDirectory($dir, $storage_type);
		$nav_arr_to_build	= $this->_model->getSortedSubNavArray($dir_files_arr, $api_path);
		
		$this->_view->sub_nav	= null;

		foreach ($nav_arr_to_build as $nav)
		{
			if ($nav['nav'] === $sub_section_title)
			{
				$class = 'active';
			}
			else
			{
				$class = null;
			}
			
			$nav_item				= $this->_view->buildAnchorTag($nav['nav'], $nav['partial_href'], true, '_self', null, $class);
			$this->_view->sub_nav	.= $this->_view->buildNav($nav_item, null);
		}
		
		return $this;
	}
	
	/**
	 * Set the view property for the rendering of the logo.
	 *
	 * @param string $property_name
	 * @param array $logo_data
	 * 
	 * @return object Controller 
	 */
	protected function _setLogo($property_name, $logo_data)
	{
		$logo = $this->_view->buildBrandingLogo(
			$logo_data['src'],
			$logo_data['alt'],
			$logo_data['id']
		);
		
		if ((boolean)$logo_data['is_anchor'])
		{
			$property_data = $this->_view->buildAnchorTag(
				$logo, 
				$logo_data['path'], 
				(boolean)$logo_data['is_internal'], 
				$logo_data['target'], 
				$logo_data['title'],
				$logo_data['class'],
				$logo_data['id']
			);
		}
		else
		{
			$property_data = $logo;
		}
		
		return $this->_setViewProperty($property_name, $property_data);
	}
	
	/**
	 * Set the view property for the rendering of the fine print.
	 *
	 * @param array $fine_print_data Name and other information for footer nav
	 * @param string|void $separator Optional separator in HTML for nav items
	 * 
	 * @return object Controller
	 */
	protected function _setFinePrint($fine_print_data, $separator = null)
	{
		$property_data	= null;		
		$i				= 1;
		
		foreach ($fine_print_data as $nav => $data)
		{
			if ($nav === 'copyright')
			{
				$property_data .= $this->_view->buildCopyright(
					$data, 
					$separator, 
					(boolean)$data['show_current_date']
				);
			}
			else
			{				
				// If we use a separator, make the last one null
				if ($i === count($fine_print_data))
				{
					$separator = null;
				}
				
				$property_data .= $this->_view->buildNav($data, null, $separator);
			}
			
			$i++;
		}
		
		return $this->_setViewProperty('fine_print', $property_data);
	}
	
	/**
	 * Sets the view property for a link list column.
	 *
	 * @param array $link_data
	 * @param integer $max_columns Maximum number of link columns to make
	 * 
	 * @return object Controller
	 */
	protected function _setLinkListColumn($link_data, $max_columns)
	{
		$property_data	= null;
		$i				= 0;

		foreach ($link_data as $list_name => $list_data )
		{
			$i++;

			if ($i <= $max_columns)
			{
				$property_data .= $this->_view->buildLinkListColumn($list_name, $list_data);
			}
		}
		
		return $this->_setViewProperty('link_section', $property_data);
	}
	
	/**
	 * Allows us to force re-caching on included files like CSS and JS.
	 *
	 * @param boolean $is_mode_cache_busting
	 * @param string|void $preexisting_value Preexisting re-cache value to use
	 * 
	 * @return string The re-cache string to append to files
	 */
	protected function _cacheBuster($is_mode_cache_busting, $preexisting_value = null)
	{
		if ($is_mode_cache_busting)
		{
			if (empty($preexisting_value))
			{
				$cache_buster = $this->_model
					->setKeyGenerator()
					->createStandardKeyFromKeyGenerator('10', array('digital'));
			}
			else
			{
				$cache_buster = $preexisting_value;
			}
		}
		else
		{
			$cache_buster = null;
		}
		
		return '?' . $cache_buster;
	}
	
	/**
	 * Call any methods necessary to build out basic page elements and set them 
	 * as view properties for viewing.
	 * 
	 * @param array $data From storage to build out view properties
	 * @param string $cache_buster Allows us to force re-caching
	 * 
	 * @return object Controller 
	 */
	protected function _pageBuilder($data, $cache_buster)
	{
		$this
			->_setHeadDoc($data['head']['head_doc'])
			->_setHeadMeta($data['head']['head_meta'])
			->_setHeadIncludesCss($data['head']['head_includes']['head_css'], $cache_buster)
			->_setJs('head_js', $data['head']['head_includes']['head_js'], $cache_buster)
			->_setJs('footer_js', $data['footer']['footer_js'], $cache_buster);

		return $this;
	}
	
	/**
	 * Loads the views for our template from stored JSON data.
	 * 
	 * Call the page builder here to build out basic page elements into view
	 * properties. Then call the view to render the page along with the basic
	 * template pages.
	 *  
	 * @param string $page_name Name of the page we load as the view
	 */
	public function render($page_name)
	{
		$this->_setViewProperty('page', $page_name)->_view->renderPage($page_name);
	}
}
// End of Controller Class

/* EOF system/core/Controller.php */