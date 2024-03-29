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
 * @subpackage Legend
 * @author     Jesper Veggerby <pear.nosey@veggerby.dk>
 * @copyright  Copyright (C) 2003, 2004 Jesper Veggerby Hansen
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/tx_pbimagegraph
 */

/**
 * Include file Image/Graph/Layout.php
 */
require_once(PATH_site.t3lib_extMgm::siteRelPath("pbimagegraph").'Image/Graph/class.tx_pbimagegraph_layout.php');

/**
 * Displays a legend for a plotarea.
 *
 * A legend can be displayed in two ways:
 *
 * 1 As an overlayed box within the plotarea
 *
 * 2 Layout'ed on the canvas smewhere next to the plotarea.
 *
 * @category   Images
 * @package    tx_pbimagegraph
 * @subpackage Legend
 * @author     Jesper Veggerby <pear.nosey@veggerby.dk>
 * @copyright  Copyright (C) 2003, 2004 Jesper Veggerby Hansen
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/tx_pbimagegraph
 */
class tx_pbimagegraph_Legend extends tx_pbimagegraph_Layout
{
    
    /**
     * Alignment of the text
     * @var int
     * @access private
     */
    var $_alignment = false;

    /**
     * The plotarea(s) to show the legend for
     * @var array
     * @access private
     */
    var $_plotareas = array();

    /**
     * Should markers be shown or not on this legend
     * @var bool
     * @access private
     */
    var $_showMarker = false;

    /**
     * tx_pbimagegraph_Legend [Constructor]
     */
    function tx_pbimagegraph_Legend()
    {
        parent::tx_pbimagegraph_Layout();
        $this->_padding = 5;
    }

    /**
     * The number of actual plots in the plot area
     *
     * @return int The number of plotes
     * @access private
     */
    function _plotCount()
    {
        $count = 0;
        $keys = array_keys($this->_plotareas);
        foreach($keys as $key) {
            $plotarea =& $this->_plotareas[$key];
            if (is_a($plotarea, 'tx_pbimagegraph_Plotarea')) {
                $keys2 = array_keys($plotarea->_elements);
                foreach ($keys2 as $key) {
                    $element =& $plotarea->_elements[$key];
                    if (is_a($element, 'tx_pbimagegraph_Plot')) {
                        $count ++;
                    }
                }
                unset($keys2);
            }
        }
        unset($keys);
        return $count;
    }
    
    /**
     * Get a default parameter array for legendSamples
     * @param bool $simulate Whether the array should be used for simulation or
     * not
     * @return array Default parameter array
     * @access private
     */
    function _parameterArray($simulate = false)
    {
        $param['left'] = $this->_left + $this->_padding;
        $param['top'] = $this->_top + $this->_padding;
        $param['right'] = $this->_right - $this->_padding;
        $param['bottom'] = $this->_bottom - $this->_padding;
        $param['align'] = $this->_alignment;
        $param['x'] = $this->_left + $this->_padding;
        $param['y'] = $this->_top + $this->_padding;
        $param['width'] = 16;
        $param['height'] = 16;
        $param['show_marker'] = $this->_showMarker;
        $param['maxwidth'] = 0;
        $param['font'] = $this->_getFont();
        if ($simulate) {
            $param['simulate'] = true;
        }
            
        return $param;
    }

    /**
     * The height of the element on the canvas
     *
     * @return int Number of pixels representing the height of the element
     * @access private
     */
    function _height()
    {
        $parent = (is_object($this->_parent) ? get_class($this->_parent) : $this->_parent);

        if (strtolower($parent) == 'tx_pbimagegraph_plotarea') {
            $param = $this->_parameterArray(true);
            $param['align'] = IMAGE_GRAPH_ALIGN_VERTICAL;
            $param0 = $param;
            $keys = array_keys($this->_plotareas);
            foreach($keys as $key) {
                $plotarea =& $this->_plotareas[$key];
                $keys2 = array_keys($plotarea->_elements);
                foreach($keys2 as $key) {
                    $element =& $plotarea->_elements[$key];
                    if (is_a($element, 'tx_pbimagegraph_Plot')) {
                        $element->_legendSample($param);
                    }
                }
                unset($keys2);
            }
            unset($keys);
            return abs($param['y'] - $param0['y']) + 2*$this->_padding;
        } else {
            return parent::height();
        }
    }

    /**
     * The width of the element on the canvas
     *
     * @return int Number of pixels representing the width of the element
     * @access private
     */
    function _width()
    {
        $parent = (is_object($this->_parent) ? get_class($this->_parent) : $this->_parent);

        if (strtolower($parent) == 'tx_pbimagegraph_plotarea') {
            $param = $this->_parameterArray(true);
            $param['align'] = IMAGE_GRAPH_ALIGN_VERTICAL;
            $keys = array_keys($this->_plotareas);
            foreach($keys as $key) {
                $plotarea =& $this->_plotareas[$key];
                $keys2 = array_keys($plotarea->_elements);
                foreach($keys2 as $key) {
                    $element =& $plotarea->_elements[$key];
                    if (is_a($element, 'tx_pbimagegraph_Plot')) {
                        $element->_legendSample($param);
                    }
                }
                unset($keys2);
            }
            unset($keys);
            return $param['maxwidth'];
        } else {
            return parent::width();
        }
    }

    /**
     * Set the alignment of the legend
     *
     * @param int $alignment The alignment
     */
    function setAlignment($alignment)
    {
        $this->_alignment = $alignment;
    }

    /**
     * Update coordinates
     *
     * @access private
     */
    function _updateCoords()
    {
        parent::_updateCoords();

        $parent = (is_object($this->_parent) ? get_class($this->_parent) : $this->_parent);

        if (strtolower($parent) == 'tx_pbimagegraph_plotarea') {
            $w = $this->_width();
            $h = $this->_height();
            
            if ($this->_alignment === false) {
                $this->_alignment = IMAGE_GRAPH_ALIGN_TOP + IMAGE_GRAPH_ALIGN_RIGHT;
            }

            if (($this->_alignment & IMAGE_GRAPH_ALIGN_BOTTOM) != 0) {
                $y = $this->_parent->_fillBottom() - $h - $this->_padding;
            } else {
                $y = $this->_parent->_fillTop() + $this->_padding;
            }

            if (($this->_alignment & IMAGE_GRAPH_ALIGN_LEFT) != 0) {
                $x = $this->_parent->_fillLeft() + $this->_padding;
            } else {
                $x = $this->_parent->_fillRight() - $w - $this->_padding;
            }

            $this->_setCoords($x, $y, $x + $w, $y + $h);
        }
    }

    /**
     * Sets Plotarea
     *
     * @param tx_pbimagegraph_Plotarea $plotarea The plotarea
     */
    function setPlotarea(& $plotarea)
    {
        if (is_a($plotarea, 'tx_pbimagegraph_Plotarea')) {
            $this->_plotareas[] =& $plotarea;
        }
    }

    /**
     * Sets the parent. The parent chain should ultimately be a GraPHP object
     *
     * @see tx_pbimagegraph
     * @param tx_pbimagegraph_Common $parent The parent
     * @access private
     */
    function _setParent(& $parent)
    {
        parent::_setParent($parent);
        if (count($this->_plotareas) == 0) {
            $this->setPlotarea($parent);
        }
    }

    /**
     * Set if this legends should show markers
     *
     * @param bool $showMarker True if markers are to be shown, false is not
     */
    function setShowMarker($showMarker)
    {
        $this->_showMarker = $showMarker;
    }


    /**
     * Output the plot
     *
     * @return bool Was the output 'good' (true) or 'bad' (false).
     * @access private
     */
    function _done()
    {

        if (tx_pbimagegraph_Element::_done() === false) {
            return false;
        }
        
        $this->_canvas->startGroup(get_class($this));

        $param = $this->_parameterArray();

        $parent = (is_object($this->_parent) ?
            get_class($this->_parent) :
            $this->_parent
        );

        if (strtolower($parent) == 'tx_pbimagegraph_plotarea') {                    
            $this->_getFillStyle();
            $this->_getLineStyle();
            $this->_canvas->rectangle(
            	array(
                	'x0' => $this->_left,
                	'y0' => $this->_top,
                	'x1' => $this->_right,
                	'y1' => $this->_bottom
                )
            );

            $param = $this->_parameterArray();
            
            $keys = array_keys($this->_plotareas);
            foreach($keys as $key) {
                $plotarea =& $this->_plotareas[$key];
                $keys2 = array_keys($plotarea->_elements);
                foreach($keys2 as $key) {
                    $element =& $plotarea->_elements[$key];
                    if (is_a($element, 'tx_pbimagegraph_Plot')) {
                        $element->_legendSample($param);
                    }
                }
                unset($keys2);
            }
            unset($keys);
        } else {
            $param0 = $param;
            $param0['simulate'] = true;
            $keys = array_keys($this->_plotareas);
            foreach($keys as $key) {
                $plotarea =& $this->_plotareas[$key];
                $keys2 = array_keys($plotarea->_elements);
                foreach($keys2 as $key) {
                    $element =& $plotarea->_elements[$key];
                    if (is_a($element, 'tx_pbimagegraph_Plot')) {
                        $element->_legendSample($param0);
                    }
                }
                unset($keys2);
            }
            unset($keys);
            if (($this->_alignment & IMAGE_GRAPH_ALIGN_VERTICAL) != 0) {
                if ($param0['x'] == $param['x']) {
                    $param['y'] = $param['y'] + ($this->_height() - ($param0['y'] - $param['y']))/2;
                }
            } else {
                if ($param0['y'] == $param['y']) {
                    $param['x'] = $param['x'] + ($this->_width() - ($param0['x'] - $param['x']))/2;
                }
            }

            $keys = array_keys($this->_plotareas);
            foreach($keys as $key) {
                $plotarea =& $this->_plotareas[$key];
                $keys2 = array_keys($plotarea->_elements);
                foreach($keys2 as $key) {
                    $element =& $plotarea->_elements[$key];
                    if (is_a($element, 'tx_pbimagegraph_Plot')) {
                        $element->_legendSample($param);
                    }
                }
                unset($keys2);
            }
            unset($keys);
        }
        
        $this->_canvas->endGroup();
        
        return true;
    }
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/class.tx_pbimagegraph_legend.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/class.tx_pbimagegraph_legend.php']);
}
?>