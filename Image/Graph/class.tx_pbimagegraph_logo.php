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
 * @subpackage Logo
 * @author     Jesper Veggerby <pear.nosey@veggerby.dk>
 * @copyright  Copyright (C) 2003, 2004 Jesper Veggerby Hansen
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/tx_pbimagegraph
 */

/**
 * Include file Image/Graph/Element.php
 */
require_once(PATH_site.t3lib_extMgm::siteRelPath("pbimagegraph").'Image/Graph/class.tx_pbimagegraph_element.php');

/**
 * Displays a logo on the canvas.
 *
 * By default the logo is displayed in the top-right corner of the canvas.
 * 
 * @category   Images
 * @package    tx_pbimagegraph
 * @subpackage Logo
 * @author     Jesper Veggerby <pear.nosey@veggerby.dk>
 * @copyright  Copyright (C) 2003, 2004 Jesper Veggerby Hansen
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/tx_pbimagegraph
 */
class tx_pbimagegraph_Logo extends tx_pbimagegraph_Element
{

    /**
     * The file name
     * @var stirng
     * @access private
     */
    var $_filename;

    /**
     * The GD Image resource
     * @var resource
     * @access private
     */
    var $_image;

    /**
     * Alignment of the logo
     * @var int
     * @access private
     */
    var $_alignment;

    /**
     * Logo [Constructor]
     *
     * @param string $filename The filename and path of the image to use for logo
     */
    function tx_pbimagegraph_Logo($filename, $alignment = IMAGE_GRAPH_ALIGN_TOP_RIGHT)
    {
        parent::tx_pbimagegraph_Element();
        $this->_filename = $filename;
        $this->_alignment = $alignment;
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
        $this->_setCoords(
            $this->_parent->_left,
            $this->_parent->_top,
            $this->_parent->_right,
            $this->_parent->_bottom
        );
    }

    /**
     * Output the logo
     *
     * @return bool Was the output 'good' (true) or 'bad' (false).
     * @access private
     */
    function _done()
    {
        if (parent::_done() === false) {
            return false;
        }

		$align = array();

        if ($this->_alignment & IMAGE_GRAPH_ALIGN_LEFT) {
            $x = $this->_parent->_left + 2;
            $align['horizontal'] = 'left';
        } elseif ($this->_alignment & IMAGE_GRAPH_ALIGN_RIGHT) {
            $x = $this->_parent->_right - 2;
            $align['horizontal'] = 'right';
        } else {
            $x = ($this->_parent->_left + $this->_parent->_right) / 2;
            $align['horizontal'] = 'center';
        }

        if ($this->_alignment & IMAGE_GRAPH_ALIGN_TOP) {
            $y = $this->_parent->_top + 2;
            $align['vertical'] = 'top';
        } elseif ($this->_alignment & IMAGE_GRAPH_ALIGN_BOTTOM) {
            $y = $this->_parent->_bottom - 2;
            $align['vertical'] = 'bottom';
        } else {
            $y = ($this->_parent->_top + $this->_parent->_bottom) / 2;
            $align['vertical'] = 'center';
        }

        $this->_canvas->image(
            array(
            	'x' => $x,
            	'y' => $y,
            	'filename' => $this->_filename,
	            'alignment' => $align
	        )
        );
        return true;
    }

}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/class.tx_pbimagegraph_logo.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/class.tx_pbimagegraph_logo.php']);
}
?>