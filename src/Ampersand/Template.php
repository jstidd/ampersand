?php

/**
* Template (formerly Ampersand) is a web template engine for PHP
*
* Currently Ampersand combines templates files and runs the result
* thought mustache (https://github.com/bobthecow/mustache.php).
*
* PHP version 5.3
*
* License: MIT
*
* Copyright (c) 2015 Jason D Stidd 
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.

*/

namespace Ampersand;

class Template
{

	//hold data used in mustache	
	private $data;
	private $templatesDirectory;
	// associate array containing the template contents: template_name => "content"
	private $template_content_array = array();
	private $template_list_array = array();
	
	//can override mustache with the setMustache method
	private $mustache;

	/**
	* Get receives content from put. If there is no matching put
	* it will default to content inside the get
	*
	*
	* Example:
	* 
	* <& get content &>
	*		
	*	<p>Default content</p>
	* <& end content &>
	*	
	* <& put content &>
	* 	<& get menu &>
	* 		<p>Default content</p>
	*	<& end menu &>
	*	
	* <& end content &>
	*
	* <& put menu &>
	*	 ...
	* <& end menu &>
	*
	*
	*/

	// must use single quote to get back reference (\1) to work. 
	const REGEX_GET = '/<&\s*get(.*?)\s*&>(.*?)<&\s*end\s*\1\s*&>/s'; // with back reference match
	const REGEX_PUT = '/<&\s*put(.*?)\s*&>(.*?)<&\s*end\s*\1\s*&>/s'; //with back reference match
	

	/**
	* Obtains the list of templates in a file
	*
	* There should be only one template tag which can
	* contain multiple templates 
	*
	* Example:   
	*	<& template layout menu &>
	*
	*/

	const REGEX_TEMPLATE = '/<&\s*templates?\s(.*?)\s*&>/';
	const REGEX_REMOVE_TEMPLATE_TAGS = '/(<&\s*templates?\s.*?\s*&>)/';
	
	
	/**
	* set the templates directory
	*
 	* function name based on slim requirements
	*
	* @directory absolute path to directory of templates
	*/	
	
	public function setTemplatesDirectory($directory) {
		$this->templatesDirectory = $directory;
	}

	// name based on slim requirement
	public function setData($data) {
		$this->data = $data;
	}
	
	/**
	* render is the function to call Ampersand
	*
	*  
 	*
	* @template template name to rendered
	*
	* @return string with combined templates
	*/
	public function render($template, $data = array()) {
		$this->setData($data);	
		$this->load_all_templates($template);

		return $this->mustache($this->combine());
	}

	/**
	* Load all templates into an array
	*
	* @template name of the template to load
	*/
	private function load_all_templates($template) {
		$templates_added = false;
		

		
		// open first template provided
		if (count($this->template_content_array) === 0) {
			
			// open template			
			$this->template_content_array[$template] = $this->open_template($template);
			
			// if a template name is found change $teampltes_added to true
			$templates_added = $this->get_templates_list($this->template_content_array[$template]);
		}
		
		// run through templates to get full template list
		// each template can have multiple templates it depends on
		while($templates_added) {

			foreach($this->template_list_array as $t) {

				// if the template name is not already loaded
				if(!array_key_exists($t, $this->template_content_array)) {

					// open template	
					$this->template_content_array[$t] = $this->open_template($t);

					// get template list and add template name to array - sets $templates_added to true or false
					$templates_added = $this->get_templates_list($this->template_content_array[$t]);
				} 	
			}			
		}         	
	}

	private function open_template($template) {
		return file_get_contents($this->templatesDirectory.'/'.$template.'.html');
	}	


	/** 
	* adds template name to template_array if not already there
	*
	* @template string containing the template content (not template name)
	*
	* @return bool (true if template name was added to array)
	*/		
	private function get_templates_list($template) {
		
		$added_new_template = false;
		
		// get template names
		preg_match_all(self::REGEX_TEMPLATE, $template, $match);
		
		foreach($match[1] as $m) {
			$temp_array = explode(' ', $m);
			
			foreach($temp_array as $t) {	
				$t = trim($t);
				
				if (!in_array($t, $this->template_list_array)) {
					$this->template_list_array[] = $t;
					if (! $added_new_template ) {
						$added_new_template = true;	
					}
				}
			}
	
		}		
		
		return $added_new_template;
	}



	/**
	* Combine blocks of the templates
	* 
	* You can have multiple gets with same tag, but can only
	* have one put for each tag. Otherwise results will be unpredictable. 
	* 
	*/
	private function combine() {
		// $full_string is a single string with all templates combined
		$full_string = '';



		/*
		* Using foreach rather than implode to create one string to minimize the size in memory
		* by copying an element then usetting it from the array
		*/

		foreach($this->template_content_array as $key => $val) {
			$full_string .= $val."\n";
			unset($this->template_content_array[$key]);	
		}
		
		// create put_array and get_array
		preg_match_all(self::REGEX_PUT, $full_string, $put_array);
		preg_match_all(self::REGEX_GET, $full_string, $get_array);

		
		// $[get|put]_aray[0] full match
		// $[get|put]_aray[1] tags 
		// $[get|put]_aray[2] content between tags


		// use while, list and each so that they array gets reevaluated each time
		// that does not happen in a foreach loop
		while( list($key, $tag) = each($get_array[1])) {

			$put_key = array_search($tag, $put_array[1]);

			//strict compare so that an index of 0 does not cause false
			if( $put_key !== false ) {
				$full_string = str_replace($get_array[0][$key], $put_array[2][$put_key], $full_string);
			} else {
			
				//if no matching put, remove get to avoid endless loop
				// replace empty string with content of get. 	
				$full_string = str_replace($get_array[0][$key], $get_array[2][$key], $full_string);
			
			
			
			}
	
			preg_match_all(self::REGEX_PUT, $full_string, $put_array);
			preg_match_all(self::REGEX_GET, $full_string, $get_array);
		}


		// remove all put statements
		foreach( $put_array[1] as $key => $val ) {
			
			$full_string = str_replace($put_array[0][$key], '', $full_string);
		}

		// remove template tags
		$full_string = preg_replace(self::REGEX_REMOVE_TEMPLATE_TAGS, '', $full_string);

		return $full_string;

	}


	/**
	* Mustache
	*
	* @string the completed template contents to run through mustache
	*/
	private function mustache($string) {
		// will pass in mustache
		$this->mustache = new \Mustache_Engine;
		return $this->mustache->render($string, $this->data);
	}

	
}	
