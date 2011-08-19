<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
Copyright (C) 2004 - 2011 EllisLab, Inc.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
ELLISLAB, INC. BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Except as contained in this notice, the name of EllisLab, Inc. shall not be
used in advertising or otherwise to promote the sale, use or other dealings
in this Software without prior written authorization from EllisLab, Inc.
*/

$plugin_info = array(
						'pi_name'			=> 'CSS Switcher',
						'pi_version'		=> '1.1',
						'pi_author'			=> 'Paul Burdick',
						'pi_author_url'		=> 'http://www.expressionengine.com/',
						'pi_description'	=> 'Allows switching of the CSS file for a page',
						'pi_usage'			=> Css_switcher::usage()
					);
					
/**
 * Css_switcher Class
 *
 * @package			ExpressionEngine
 * @category		Plugin
 * @author			ExpressionEngine Dev Team
 * @copyright		Copyright (c) 2004 - 2011, EllisLab, Inc.
 * @link			http://expressionengine.com/downloads/details/css_switcher/
 */

Class Css_switcher {

	var $get_varname	= 'css_skin';								// Name of $_GET variable checked
	var $post_varname	= 'css_skin';								// Name of $_POST variable checked 
	var $cookie_varname = 'css_skin';								// Name of $_COOKIE variabled checked and set
	var $default		= '1';										// Default stylesheet name
	var $return_data	= '';										// Return data
	
	var $css = array(
						'1'		=> "http://example.com/index.php?css=weblog/weblog_css",
						'2' 	=> "http://example.com/index.php?css=weblog/weblog2_css",
						'3' 	=> "http://example.com/index.php?css=weblog/weblog3_css",
						'4' 	=> "http://example.com/index.php?css=weblog/weblog4_css"
					);

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	void
	 */
	
    function Css_switcher()
    {
   		$this->EE =& get_instance();

    	$setting	= ( ! $this->EE->TMPL->fetch_param('setting')) ? 'normal' : $this->EE->TMPL->fetch_param('setting');
    	$files		= ( ! $this->EE->TMPL->fetch_param('files')) ? '' : $this->EE->TMPL->fetch_param('files');
    	
    	if ($files != '')
    	{
    		$this->css = explode('|',$files);
    		
    		// Push array values up with an unshift
    		// Since we want array to start with '1'
    		array_unshift($this->css,$this->css['0']);
    		
    		// Remove ['0'], we no likey
    		unset($this->css['0']);
    	}
    	
    	if (count($this->css) == 0)
    	{
    		return;
    	}
    	
    	// -------------------------------------
    	//  Incoming Stylesheet Requests
    	// -------------------------------------
    	
    	if ($this->EE->input->get($this->get_varname, 'GET'))
        {
        	$name = $this->EE->input->get($this->get_varname, 'GET');
        }
        elseif ($this->EE->input->post($this->post_varname, 'POST'))
        {
        	$name = $this->EE->input->post($this->post_varname, 'POST');
        }
        elseif ($this->EE->input->cookie($this->cookie_varname, 'COOKIE'))
        {
        	$name = $this->EE->input->cookie($this->cookie_varname, 'COOKIE');
        }
        else
        {
        	$name = &$this->default;
        }
        
        // -------------------------------------
    	//  Find Requested Stylesheet
    	// -------------------------------------
    	
    	if ( ! isset($this->css[$name]))
    	{
    		if ($name == $this->default || ! isset($this->css[$this->default]))
    		{
    			sort($this->css);
    			$name = '0';
    			$stylesheet = &$this->css['0'];
    		}
    		else
    		{
    			$stylesheet = &$this->css[$this->default];
    			$name = &$this->default;
    		}
    	}
    	else
    	{
    		$stylesheet = &$this->css[$name];
    	}
    	
    	// -------------------------------------
    	//  Setting Override?
    	// -------------------------------------
    	
    	switch($setting)
    	{
    		case 'random':
    		
    			// ------------------------------
    			//  Choose random stylesheet
    			// ------------------------------
    			
    			sort($this->css);
    			
    			if (is_php(4.2))
    			{
                	mt_srand();
            	}
            	else
                {
                	mt_srand(hexdec(substr(md5(microtime()), -8)) & 0x7fffffff);
                }
                
                $name = rand(1,count($this->css)) - 1;
                
                $stylesheet = &$this->css[$name];
                
    		break;  
    		case 'day':
    		
    			// -------------------------------    			
    			// Stylsheet via day
    			// -------------------------------
    			
    			// Sunday => 1, Monday => 2, etc.
    			
    			switch(date('D',$this->EE->localize->now))
    			{
    				case 'Sun':
    					$name = '1';
    				break;
    				case 'Mon':
    					$name = '2';
    				break;
    				case 'Tue':
    					$name = '3';
    				break;
    				case 'Wed':
    					$name = '4';
    				break;
    				case 'Thu':
    					$name = '5';
    				break;
    				case 'Fri':
    					$name = '6';
    				break;
    				case 'Sat':
    					$name = '7';
    				break;
    				default:
    					$name = '1';
    				break;
    			}
    			
    			// Numeric key, i.e. 1, 2, 3...
				$stylesheet = ( ! isset($this->css[$name])) ? $stylesheet : $this->css[$name];    
		
				// Text key, i.e. Mon, Tue, Wed...
				// Take priority so is last check
    			$stylesheet = ( ! isset($this->css[date('D',$this->EE->localize->now)])) ? $stylesheet : $this->css[date('D',$this->EE->localize->now)];
    			
    		break;  
    	}   
    	
    	// -------------------------------------
    	//  Set Cookie
    	// -------------------------------------
		$this->EE->functions->set_cookie($this->cookie_varname, $name, 60*60*24*90); // 3 Month cookie
    	
    	// -------------------------------------
    	//  Find and Replace
    	// -------------------------------------
    	
    	$this->return_data = str_replace('{file}',$stylesheet, $this->EE->TMPL->tagdata);    	
    	
	}

	// --------------------------------------------------------------------
	
	/**
	 * Usage
	 *
	 * Plugin Usage
	 *
	 * @access	public
	 * @return	string
	 */

	function usage()
	{
		ob_start(); 
		?>

		The CSS Switcher plugin is a simple way to allow skinning on a CSS 
		formatted site.

		The tag will look for an incoming $_GET (url), $_POST (forms), or 
		$_COOKIE (cookies) variable, which the name of can be specified in 
		the plugin file, to see if a CSS file has been requested.  If it has, 
		then it will be pulled from an array of CSS file locations in the 
		plugin file and outputted as the {file} variable.  Also, it will 
		store the name of that CSS file in a cookie so it can be retrieved 
		whenever the user visits the site again.

		Here is a quick example using a form.

		1.  You update the $css array in the CSS_Switch class in pi.css_switcher.php:

		var $css = array(
								'1'		  => "http://example.com/index.php?css=weblog/weblog_css",
								'apples'  => "http://example.com/index.php?css=weblog/weblog2_css",
								'oranges' => "http://example.com/index.php?css=weblog/weblog3_css",
								'4' 	  => "http://example.com/index.php?css=weblog/weblog4_css"
							);
					
		2.  You put the following in the <head> tags of your pages:


		{exp:css_switcher}
		<link rel='stylesheet' type='text/css' media='all' href='{file}' /> 
		<style type='text/css' media='screen'>@import "{file}";</style>
		{/exp:css_switcher}


		3.  You set up a form on your front page where the user chooses their 
		skin and submits the form:

		<form action="index.php" method="post">
		<select name="css_skin" id="css_skin">
		<option value="1">Default</option>
		<option value="apples">Apples</option>
		<option value="oranges">Oranges</option>
		<option value="4">Pineapples</option>
		</select>
		</form>

		4.  CSS Switcher finds the requested member of the $css array and puts
		in the requested CSS file's URL.  Then, a cookie is set that will 
		have that CSS file presented the next time the site is viewed.


		PARAMETERS:

		The tag also has two optional parameters.  

		1. setting=""

		Used to change how the CSS files are chosen. Three different possible 
		settings. 
	
			a. setting="normal" : Relies on the $_GET, $_POST, or $_COOKIE 
			variables to choose a member of the array.  If neither of those
			are found or the one found is not in the array, then it chooses
			the default member of the array
	
			b. setting="random" : Will randomly choose any member of the 
			$css array in the plugin file.
	
			c.  setting="day" : Will choose a stylesheet depending on the 
			day of the week.  The keys of the array must either be a number
			or abbreviation for the day of the week. Possible array keys are:
			1, Sun, 2, Mon, 3, Tue, 4, Wed, 5, Thu, 6, Fri, 7, Sat.

		2. files=""

		Creates the $css array on load using the stylesheet URLs listed
		in the parameter.  Separate each URL with a pipe ("|").  The first 
		stylesheet will be given an array key of 1, and the rest after will 
		be incremented by a value of one (2, 3, 4, etc.) by their order.


		Version 1.1
		******************
		- Updated plugin to be 2.0 compatible


		<?php
		$buffer = ob_get_contents();
	
		ob_end_clean(); 

		return $buffer;
	}

	// --------------------------------------------------------------------
	
}
// END CLASS

/* End of file pi.css_switcher.php */
/* Location: ./system/expressionengine/third_party/css_switcher/pi.css_switcher.php */