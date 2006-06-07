<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * tx_pbimagegraph - PEAR PHP OO Graph Rendering Utility.
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
 * @subpackage Figure
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
 * Rectangle to draw on the canvas
 *
 * @category   Images
 * @package    tx_pbimagegraph
 * @subpackage Figure
 * @author     Jesper Veggerby <pear.nosey@veggerby.dk>
 * @copyright  Copyright (C) 2003, 2004 Jesper Veggerby Hansen
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/tx_pbimagegraph
 */
class tx_pbimagegraph_Figure_Rectangle extends tx_pbimagegraph_Element
{

    /**
     * Rectangle [Construcor]
     *
     * @param int $x The leftmost pixel of the box on the canvas
     * @param int $y The topmost pixel of the box on the canvas
     * @param int $width The width in pixels of the box on the canvas
     * @param int $height The height in pixels of the box on the canvas
     */
    function tx_pbimagegraph_Figure_Rectangle($x, $y, $width, $height)
    {
        parent::tx_pbimagegraph_Element();
        $this->_setCoords($x, $y, $x + $width, $y + $height);
    }

    /**
     * Output the box
     *
     * @return bool Was the output 'good' (true) or 'bad' (false).
     * @access private
     */
    function _done()
    {
        if (parent::_done() === false) {
            return false;
        }

        $this->_canvas->startGroup(get_class($this));
        
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
        
        $this->_canvas->endGroup();
        
        return true;
    }

}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/Figure/class.tx_pbimagegraph_figure_rectangle.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/Figure/class.tx_pbimagegraph_figure_rectangle.php']);
}
?>