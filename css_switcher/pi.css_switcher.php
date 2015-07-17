<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
Copyright (C) 2004 - 2015 EllisLab, Inc.

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


/**
 * Css_switcher Class
 *
 * @package			ExpressionEngine
 * @category		Plugin
 * @author          EllisLab
 * @copyright       Copyright (c) 2004 - 2015, EllisLab, Inc.
 * @link			https://github.com/EllisLab/Css-Switcher
 */

Class Css_switcher {

	public $get_varname    = 'css_skin';								// Name of $_GET variable checked
	public $post_varname   = 'css_skin';								// Name of $_POST variable checked
	public $cookie_varname = 'css_skin';								// Name of $_COOKIE variabled checked and set
	public $default        = '1';										// Default stylesheet name
	public $return_data    = '';										// Return data

	public $css            = array(
								'1' => "http://example.com/index.php?css=weblog/weblog_css",
								'2' => "http://example.com/index.php?css=weblog/weblog2_css",
								'3' => "http://example.com/index.php?css=weblog/weblog3_css",
								'4' => "http://example.com/index.php?css=weblog/weblog4_css"
							);

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	void
	 */
    function __construct()
    {
    	$setting	= ( ! ee()->TMPL->fetch_param('setting')) ? 'normal' : ee()->TMPL->fetch_param('setting');
    	$files		= ( ! ee()->TMPL->fetch_param('files')) ? '' : ee()->TMPL->fetch_param('files');

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

    	if (ee()->input->get($this->get_varname, 'GET'))
        {
        	$name = ee()->input->get($this->get_varname, 'GET');
        }
        elseif (ee()->input->post($this->post_varname, 'POST'))
        {
        	$name = ee()->input->post($this->post_varname, 'POST');
        }
        elseif (ee()->input->cookie($this->cookie_varname, 'COOKIE'))
        {
        	$name = ee()->input->cookie($this->cookie_varname, 'COOKIE');
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

    			switch(date('D',ee()->localize->now))
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
    			$stylesheet = ( ! isset($this->css[date('D',ee()->localize->now)])) ? $stylesheet : $this->css[date('D',ee()->localize->now)];

    		break;
    	}

    	// -------------------------------------
    	//  Set Cookie
    	// -------------------------------------
		ee()->input->set_cookie($this->cookie_varname, $name, 60*60*24*90); // 3 Month cookie

    	// -------------------------------------
    	//  Find and Replace
    	// -------------------------------------

    	$this->return_data = str_replace('{file}',$stylesheet, ee()->TMPL->tagdata);

	}
}
