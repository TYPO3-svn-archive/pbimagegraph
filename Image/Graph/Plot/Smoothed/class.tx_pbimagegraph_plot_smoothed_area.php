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
 * @subpackage Plot
 * @author     Jesper Veggerby <pear.nosey@veggerby.dk>
 * @copyright  Copyright (C) 2003, 2004 Jesper Veggerby Hansen
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/tx_pbimagegraph
 */

/**
 * Include file Image/Graph/Plot/Smoothed/Bezier.php
 */
require_once(PATH_site.t3lib_extMgm::siteRelPath("pbimagegraph").'Image/Graph/Plot/Smoothed/class.tx_pbimagegraph_plot_smoothed_bezier.php');

/**
 * Bezier smoothed area chart
 *
 * Similar to an {@link tx_pbimagegraph_Plot_Area}, but the interconnecting lines
 * between two datapoints are smoothed using a Bezier curve, which enables the
 * chart to appear as a nice curved plot instead of the sharp edges of a
 * conventional {@link tx_pbimagegraph_Plot_Area}. Smoothed charts are only supported
 * with non-stacked types
 *
 * @category   Images
 * @package    tx_pbimagegraph
 * @subpackage Plot
 * @author     Jesper Veggerby <pear.nosey@veggerby.dk>
 * @copyright  Copyright (C) 2003, 2004 Jesper Veggerby Hansen
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/tx_pbimagegraph
 */
class tx_pbimagegraph_Plot_Smoothed_Area extends tx_pbimagegraph_Plot_Smoothed_Bezier
{

    /**
     * Perform the actual drawing on the legend.
     *
     * @param int $x0 The top-left x-coordinate
     * @param int $y0 The top-left y-coordinate
     * @param int $x1 The bottom-right x-coordinate
     * @param int $y1 The bottom-right y-coordinate
     * @access private
     */
    function _drawLegendSample($x0, $y0, $x1, $y1)
    {

        $this->_canvas->addVertex(array('x' => $x0, 'y' => $y1));
        $this->_addSamplePoints($x0, $y0, $x1, $y1);
        $this->_canvas->addVertex(array('x' => $x1, 'y' => $y1));
        $this->_canvas->polygon(array('connect' => true));
    }

    /**
     * Output the Bezier smoothed plot as an Area Chart
     *
     * @return bool Was the output 'good' (true) or 'bad' (false).
     * @access private
     */
    function _done()
    {
        if (parent::_done() === false) {
            return false;
        }

        $this->_canvas->startGroup(get_class($this) . '_' . $this->_title);
        $keys = array_keys($this->_dataset);
        foreach ($keys as $key) {
            $dataset =& $this->_dataset[$key];
            $dataset->_reset();
            $first = true;
            while ($p1 = $dataset->_next()) {
                $p0 = $dataset->_nearby(-2);
                $p2 = $dataset->_nearby(0);
                $p3 = $dataset->_nearby(1);
                if ($first) {
                    $p = $p1;
                    $p['Y'] = '#min_pos#';
                    $x = $this->_pointX($p);
                    $y = $this->_pointY($p);
                    $this->_canvas->addVertex(array('x' => $x, 'y' => $y));
                }

                if ($p2) {
                    $cp = $this->_getControlPoints($p1, $p0, $p2, $p3);
                    $this->_canvas->addSpline(
                    	array(
                        	'x' => $cp['X'],
                        	'y' => $cp['Y'],
                        	'p1x' => $cp['P1X'],
                        	'p1y' => $cp['P1Y'],
                        	'p2x' => $cp['P2X'],
                        	'p2y' => $cp['P2Y']
                        )
                    );
                } else {
                    $x = $this->_pointX($p1);
                    $y = $this->_pointY($p1);
                    $this->_canvas->addVertex(array('x' => $x, 'y' => $y));
                }
                $lastPoint = $p1;
                $first = false;
            }
            $lastPoint['Y'] = '#min_pos#';
            $x = $this->_pointX($lastPoint);
            $y = $this->_pointY($lastPoint);
            $this->_canvas->addVertex(array('x' => $x, 'y' => $y));

            $this->_getFillStyle($key);
            $this->_getLineStyle($key);
            $this->_canvas->polygon(array('connect' => true));
        }
        unset($keys);
        $this->_drawMarker();
        $this->_canvas->endGroup();
        return true;
    }

}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/Plot/Smoothed/class.tx_pbimagegraph_plot_smoothed_area.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/Plot/Smoothed/class.tx_pbimagegraph_plot_smoothed_area.php']);
}
?>