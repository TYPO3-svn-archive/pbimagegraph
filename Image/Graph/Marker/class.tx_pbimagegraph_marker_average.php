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
 * @subpackage Marker
 * @author     Jesper Veggerby <pear.nosey@veggerby.dk>
 * @copyright  Copyright (C) 2003, 2004 Jesper Veggerby Hansen
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/tx_pbimagegraph
 */

/**
 * Include file Image/Graph/Marker.php
 */
require_once(PATH_site.t3lib_extMgm::siteRelPath("pbimagegraph").'Image/Graph/class.tx_pbimagegraph_marker.php');

/**
 * A marker displaying the 'distance' to the datasets average value.
 *
 * @category   Images
 * @package    tx_pbimagegraph
 * @subpackage Marker
 * @author     Jesper Veggerby <pear.nosey@veggerby.dk>
 * @copyright  Copyright (C) 2003, 2004 Jesper Veggerby Hansen
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/tx_pbimagegraph
 */
class tx_pbimagegraph_Marker_Average extends tx_pbimagegraph_Marker
{

    /**
     * Draw the marker on the canvas
     *
     * @param int $x The X (horizontal) position (in pixels) of the marker on
     *   the canvas
     * @param int $y The Y (vertical) position (in pixels) of the marker on the
     *   canvas
     * @param array $values The values representing the data the marker 'points'
     *   to
     * @access private
     */
    function _drawMarker($x, $y, $values = false)
    {
        if ((isset($values['AVERAGE_Y'])) &&
            (is_a($this->_parent, 'tx_pbimagegraph_Plot')))
        {
            $point = $this->_pointXY(
                array(
                    'X' => $values['APX'],
                    'Y' => $values['AVERAGE_Y']
                )
            );
            $this->_getLineStyle();
            $this->_canvas->line(array('x0' => $x, 'y0' => $y, 'x1' => $point['X'], 'y1' => $point['Y']));

            $this->_getLineStyle();
            $this->_canvas->line(
            	array(
                	'x0' => $point['X'] - 2,
                	'y0' => $point['Y'],
                	'x1' => $point['X'] + 2,
                	'y1' => $point['Y']
                )
            );
        }
        parent::_drawMarker($x, $y, $values);
    }

}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/Marker/class.tx_pbimagegraph_marker_average.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/Marker/class.tx_pbimagegraph_marker_average.php']);
}
?>