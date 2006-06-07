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
 * Representation of a element on a plotarea.
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
class tx_pbimagegraph_Plotarea_Element extends tx_pbimagegraph_Element
{

    /**
     * Get the X pixel position represented by a value
     *
     * @param double $point the value to get the pixel-point for
     * @return double The pixel position along the axis
     * @access private
     */
    function _pointX($point)
    {
        return $this->_parent->_pointX($point);
    }

    /**
     * Get the Y pixel position represented by a value
     *
     * @param double $point the value to get the pixel-point for
     * @return double The pixel position along the axis
     * @access private
     */
    function _pointY($point)
    {
        return $this->_parent->_pointY($point);
    }

    /**
     * Get the X and Y pixel position represented by a value
     *
     * @param array $point the values to get the pixel-point for
     * @return array The (x, y) pixel position along the axis
     * @access private
     */
    function _pointXY($point)
    {
        return array ('X' => $this->_pointX($point), 'Y' => $this->_pointY($point));
    }

}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/Plotarea/class.tx_pbimagegraph_plotarea_element.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/Plotarea/class.tx_pbimagegraph_plotarea_element.php']);
}
?>