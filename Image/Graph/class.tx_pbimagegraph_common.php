<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * tx_pbimagegraph - Main class for the graph creation.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This library is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 2.1 of the License, or (at your
 * option) any later version. This library is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser
 * General Public License for more details. You should have received a copy of
 * the GNU Lesser General Public License along with this library; if not, write
 * to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 * 02111-1307 USA
 *
 * @category   Images
 * @package    tx_pbimagegraph
 * @author     Jesper Veggerby <pear.nosey@veggerby.dk>
 * @copyright  Copyright (C) 2003, 2004 Jesper Veggerby Hansen
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/tx_pbimagegraph
 */

if (!function_exists('is_a')) {

    /**
     * Check if an object is of a given class, this function is available as of PHP 4.2.0, so if it exist it will not be declared
     *
     * @link http://www.php.net/manual/en/function.is-a.php PHP.net Online Manual for function is_a()
     * @param object $object The object to check class for
     * @param string $class_name The name of the class to check the object for
     * @return bool Returns TRUE if the object is of this class or has this class as one of its parents
     */
    function is_a($object, $class_name)
    {
        if (empty ($object)) {
            return false;
        }
        $object = is_object($object) ? get_class($object) : $object;
        if (strtolower($object) == strtolower($class_name)) {
            return true;
        }
        return is_a(get_parent_class($object), $class_name);
    }
}

/**
 * Include file Image/Canvas.php
 */
require_once(PATH_site.t3lib_extMgm::siteRelPath("pbimagegraph").'Image/class.tx_pbimagegraph_canvas.php');

/**
 * The ultimate ancestor of all tx_pbimagegraph classes.
 *
 * This class contains common functionality needed by all tx_pbimagegraph classes.
 *
 * @category   Images
 * @package    tx_pbimagegraph
 * @author     Jesper Veggerby <pear.nosey@veggerby.dk>
 * @copyright  Copyright (C) 2003, 2004 Jesper Veggerby Hansen
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/tx_pbimagegraph
 * @abstract
 */
class tx_pbimagegraph_Common
{

    /**
     * The parent container of the current tx_pbimagegraph object
     *
     * @var tx_pbimagegraph_Common
     * @access private
     */
    var $_parent = null;

    /**
     * The sub-elements of the current tx_pbimagegraph container object
     *
     * @var array
     * @access private
     */
    var $_elements;

    /**
     * The canvas for output.
     *
     * Enables support for multiple output formats.
     *
     * @var tx_pbimagegraph_Canvas
     * @access private
     */
    var $_canvas = null;
    
    /**
     * Is the object visible?
     * 
     * @var bool
     * @access private
     */
    var $_visible = true;

    /**
     * Constructor [tx_pbimagegraph_Common]
     */
    function tx_pbimagegraph_Common()
    {
    }

    /**
     * Resets the elements
     *
     * @access private
     */
    function _reset()
    {
        if (is_array($this->_elements)) {
            $keys = array_keys($this->_elements);
            foreach ($keys as $key) {
                if (is_object($this->_elements[$key])) {
                    $this->_elements[$key]->_setParent($this);
                    $result =& $this->_elements[$key]->_reset();
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                }
            }
            unset($keys);
        }
        return true;
    }

    /**
     * Sets the parent. The parent chain should ultimately be a GraPHP object
     *
     * @see tx_pbimagegraph_Common
     * @param tx_pbimagegraph_Common $parent The parent
     * @access private
     */
    function _setParent(& $parent)
    {
        $this->_parent =& $parent;
        $this->_canvas =& $this->_parent->_getCanvas();

        if (is_array($this->_elements)) {
            $keys = array_keys($this->_elements);
            foreach ($keys as $key) {
                if (is_object($this->_elements[$key])) {
                    $this->_elements[$key]->_setParent($this);
                }
            }
            unset($keys);
        }
    }

    /**
     * Hide the element
     */
    function hide()
    {
        $this->_visible = false;
    }        

    /**
     * Get the canvas
     *
     * @return tx_pbimagegraph_Canvas The canvas
     * @access private
     */
    function &_getCanvas()
    { 
        if (($this->_canvas !== null) || ($this->_canvas !== false)) {
            return $this->_canvas;
        } elseif (is_a($this->_parent, 'tx_pbimagegraph_Common')) {
            $this->_canvas =& $this->_parent->_getCanvas();
            return $this->_canvas;
        } else {
            $this->_error('Invalid canvas');
            $result = null;
            return $result;
        }
    }

    /**
     * Adds an element to the objects element list.
     *
     * The new tx_pbimagegraph_elements parent is automatically set.
     *
     * @param tx_pbimagegraph_Common $element The new tx_pbimagegraph_element
     * @return tx_pbimagegraph_Common The new tx_pbimagegraph_element
     */
    function &add(& $element)
    {
        if (!is_a($element, 'tx_pbimagegraph_Font')) {
            $this->_elements[] = &$element;
        }
        $element->_setParent($this);
        return $element;
    }

    /**
     * Creates an object from the class and adds it to the objects element list.
     *
     * Creates an object from the class specified and adds it to the objects
     * element list. If only one parameter is required for the constructor of
     * the class simply pass this parameter as the $params parameter, unless the
     * parameter is an array or a reference to a value, in that case you must
     * 'enclose' the parameter in an array. Similar if the constructor takes
     * more than one parameter specify the parameters in an array.
     *
     * @see tx_pbimagegraph::factory()
     * @param string $class The class for the object
     * @param mixed $params The paramaters to pass to the constructor
     * @return tx_pbimagegraph_Common The new tx_pbimagegraph_element
     */
    function &addNew($class, $params = null, $additional = false)
    {
        include_once(PATH_site.t3lib_extMgm::siteRelPath("pbimagegraph").'Image/class.tx_pbimagegraph.php');
        $element =& tx_pbimagegraph::factory($class, $params);
        if ($additional === false) {
            $obj =& $this->add($element);
        } else {
            $obj =& $this->add($element, $additional);
        }
        return $obj;
    }

    /**
     * Shows an error message box on the canvas
     *
     * @param string $text The error text
     * @param array $params An array containing error specific details
     * @param int $error_code Error code
     * @access private
     */
    function _error($text, $params = false, $error_code = IMAGE_GRAPH_ERROR_GENERIC)
    {       
        foreach ($params as $name => $key) {
            if (isset($parameters)) {
                $parameters .= ' ';
            }
            $parameters .= $name . '=' . $key;
        }        
        $error =& PEAR::raiseError(
            $text .
            ($error_code != IMAGE_GRAPH_ERROR_GENERIC ? ' error:' . IMAGE_GRAPH_ERROR_GENERIC : '') .
            (isset($parameters) ? ' parameters:[' . $parameters . ']' : '')            
        );         
    }

    /**
     * Causes the object to update all sub elements coordinates
     *
     * (tx_pbimagegraph_Common, does not itself have coordinates, this is basically
     * an abstract method)
     *
     * @access private
     */
    function _updateCoords()
    {
        if (is_array($this->_elements)) {
            $keys = array_keys($this->_elements);
            foreach ($keys as $key) {
                if (is_object($this->_elements[$key])) {
                    $this->_elements[$key]->_updateCoords();
                }
            }
            unset($keys);
        }
        return true;
    }

    /**
     * Causes output to canvas
     *
     * The last method to call. Calling Done causes output to the canvas. All
     * sub elements done() method will be invoked
     *
     * @return bool Was the output 'good' (true) or 'bad' (false).
     * @access private
     */
    function _done()
    {
        if (($this->_canvas == null) || (!is_a($this->_canvas, 'tx_pbimagegraph_Canvas'))) {
            return false;
        }

        if (is_array($this->_elements)) {
            $keys = array_keys($this->_elements);
            foreach ($keys as $key) {
                if (($this->_elements[$key]->_visible) && ($this->_elements[$key]->_done() === false)) {
                    return false;
                }
            }
            unset($keys);
        }
        return true;
    }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/class.tx_pbimagegraph_common.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/class.tx_pbimagegraph_common.php']);
}
?>