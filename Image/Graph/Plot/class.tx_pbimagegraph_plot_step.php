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
 * Include file Image/Graph/Plot/Bar.php
 */
require_once(PATH_site.t3lib_extMgm::siteRelPath("pbimagegraph").'Image/Graph/Plot/class.tx_pbimagegraph_plot_bar.php');

/**
 * Step chart.
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
class tx_pbimagegraph_Plot_Step extends tx_pbimagegraph_Plot
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
        $dx = abs($x1 - $x0) / 3;
        $dy = abs($y1 - $y0) / 3;
        $this->_canvas->addVertex(array('x' => $x0, 'y' => $y1));
        $this->_canvas->addVertex(array('x' => $x0, 'y' => $y0 + $dy));

        $this->_canvas->addVertex(array('x' => $x0 + $dx, 'y' => $y0 + $dy));
        $this->_canvas->addVertex(array('x' => $x0 + $dx, 'y' => $y0));

        $this->_canvas->addVertex(array('x' => $x0 + 2*$dx, 'y' => $y0));
        $this->_canvas->addVertex(array('x' => $x0 + 2*$dx, 'y' => $y0 + 2*$dy));

        $this->_canvas->addVertex(array('x' => $x1, 'y' => $y0 + 2*$dy));
        $this->_canvas->addVertex(array('x' => $x1, 'y' => $y1));
        $this->_canvas->polygon(array('connect' => true));
    }

    /**
     * PlotType [Constructor]
     *
     * A 'normal' step chart is 'stacked'
     *
     * @param Dataset $dataset The data set (value containter) to plot
     * @param string $multiType The type of the plot
     * @param string $title The title of the plot (used for legends,
     *   {@link tx_pbimagegraph_Legend})
     */
    function tx_pbimagegraph_Plot_Step(& $dataset, $multiType = 'stacked', $title = '')
    {
        $multiType = strtolower($multiType);
        if (($multiType != 'stacked') && ($multiType != 'stacked100pct')) {
            $multiType = 'stacked';
        }
        parent::tx_pbimagegraph_Plot($dataset, $multiType, $title);
    }

    /**
     * Output the plot
     *
     * @return bool Was the output 'good' (true) or 'bad' (false).
     * @access private
     */
    function _done()
    {
        if (tx_pbimagegraph_Plot::_done() === false) {
            return false;
        }

        $this->_canvas->startGroup(get_class($this) . '_' . $this->_title);
        
        if ($this->_multiType == 'stacked100pct') {
            $total = $this->_getTotals();
        }

        if ($this->_parent->_horizontal) {
            $width = $this->height() / ($this->_maximumX() + 2) / 2;
        }
        else {
            $width = $this->width() / ($this->_maximumX() + 2) / 2;
        }

        reset($this->_dataset);
        $key = key($this->_dataset);
        $dataset =& $this->_dataset[$key];

        $first = $dataset->first();
        $last = $dataset->last();

        $point = array ('X' => $first['X'], 'Y' => '#min_pos#');
        $firstY = $this->_pointY($point) + ($this->_parent->_horizontal ? $width : 0);
        $base[] = $firstY; 
        $firstX = $this->_pointX($point) - ($this->_parent->_horizontal ? 0 : $width);
        $base[] = $firstX;
        
        $point = array ('X' => $last['X'], 'Y' => '#min_pos#');
        $base[] = $this->_pointY($point) - ($this->_parent->_horizontal ? $width : 0);
        $base[] = $this->_pointX($point) + ($this->_parent->_horizontal ? 0 : $width);

        $first = ($this->_parent->_horizontal ? $firstY : $firstX);

        $keys = array_keys($this->_dataset);
        foreach ($keys as $key) {
            $dataset =& $this->_dataset[$key];
            $dataset->_reset();
            $polygon = array_reverse($base);
            unset ($base);
            $last = $first;
            while ($point = $dataset->_next()) {
                $x = $point['X'];
                $p = $point;

                if (!isset($current[$x])) {
                    $current[$x] = 0;
                }

                if ($this->_multiType == 'stacked100pct') {
                    $p['Y'] = 100 * ($current[$x] + $point['Y']) / $total['TOTAL_Y'][$x];
                } else {
                    $p['Y'] += $current[$x];
                }
                $current[$x] += $point['Y'];
                $point = $p;

                if ($this->_parent->_horizontal) {
                    $x0 = $this->_pointX($point);
                    $y0 = $last;
                    $x1 = $this->_pointX($point);
                    $last = $y1 = $this->_pointY($point) - $width;
                }
                else {
                    $x0 = $last;
                    $y0 = $this->_pointY($point);
                    $last = $x1 = $this->_pointX($point) + $width;
                    $y1 = $this->_pointY($point);
                }            
                $polygon[] = $x0; $base[] = $y0;
                $polygon[] = $y0; $base[] = $x0;
                $polygon[] = $x1; $base[] = $y1;
                $polygon[] = $y1; $base[] = $x1;
            }

            while (list(, $x) = each($polygon)) {
                list(, $y) = each($polygon);
                $this->_canvas->addVertex(array('x' => $x, 'y' => $y));
            }

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
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/Plot/class.tx_pbimagegraph_plot_step.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/Plot/class.tx_pbimagegraph_plot_step.php']);
}
?>