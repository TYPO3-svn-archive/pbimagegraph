<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class for axis handling.
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
 * @subpackage Axis
 * @author     Jesper Veggerby <pear.nosey@veggerby.dk>
 * @copyright  Copyright (C) 2003, 2004 Jesper Veggerby Hansen
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/tx_pbimagegraph
 */
 
/**
 * Include file Image/Graph/Axis.php
 */
require_once(PATH_site.t3lib_extMgm::siteRelPath("pbimagegraph").'Image/Graph/class.tx_pbimagegraph_axis.php');

/**
 * Diplays a logarithmic axis (either X- or Y-axis).
 *
 * @category   Images
 * @package    tx_pbimagegraph
 * @subpackage Axis
 * @author     Jesper Veggerby <pear.nosey@veggerby.dk>
 * @copyright  Copyright (C) 2003, 2004 Jesper Veggerby Hansen
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/tx_pbimagegraph
 */
class tx_pbimagegraph_Axis_Logarithmic extends tx_pbimagegraph_Axis
{

    /**
     * tx_pbimagegraph_AxisLogarithmic [Constructor].
     *
     * Normally a manual creation should not be necessary, axis are
     * created automatically by the {@link tx_pbimagegraph_Plotarea} constructor
     * unless explicitly defined otherwise
     *
     * @param int $type The type (direction) of the Axis, use IMAGE_GRAPH_AXIS_X
     *   for an X-axis (default, may be omitted) and IMAGE_GRAPH_AXIS_Y for Y-
     *   axis)
     */
    function tx_pbimagegraph_Axis_Logarithmic($type = IMAGE_GRAPH_AXIS_X)
    {
        parent::tx_pbimagegraph_Axis($type);
        $this->showLabel(IMAGE_GRAPH_LABEL_MINIMUM + IMAGE_GRAPH_LABEL_MAXIMUM);
    }

    /**
     * Calculate the label interval
     *
     * If explicitly defined this will be calucated to an approximate best.
     *
     * @return double The label interval
     * @access private
     */
    function _calcLabelInterval()
    {
        $result = parent::_calcLabelInterval();
        $this->_axisValueSpan = $this->_value($this->_axisSpan);                
        return $result;
    }

    /**
     * Forces the minimum value of the axis.
     *
     * For an logarithimc axis this is always 0
     *
     * @param double $minimum The minumum value to use on the axis
     */
    function forceMinimum($minimum)
    {
        parent::forceMinimum(0);
    }

    /**
     * Gets the minimum value the axis will show.
     *
     * For an logarithimc axis this is always 0
     *
     * @return double The minumum value
     * @access private
     */
    function _getMinimum()
    {
        return 0;
    }

    /**
     * Preprocessor for values, ie for using logarithmic axis
     *
     * @param double $value The value to preprocess
     * @return double The preprocessed value
     * @access private
     */
    function _value($value)
    {
        return log10($value);
    }

    /**
     * Get next label point
     *
     * @param doubt $point The current point, if omitted or false, the first is
     *   returned
     * @return double The next label point
     * @access private
     */
    function _getNextLabel($currentLabel = false, $level = 1)
    {
        if (is_array($this->_labelOptions[$level]['interval'])) {
            return parent::_getNextLabel($currentLabel, $level);
        }

        if ($currentLabel !== false) {
            $value = log10($currentLabel);
            $base = floor($value);
            $frac = $value - $base;
            for ($i = 2; $i < 10; $i++) {
                if ($frac <= (log10($i)-0.01)) {                    
                    $label = pow(10, $base)*$i;
                    if ($label > $this->_getMaximum()) {
                        return false;
                    } else {
                        return $label;
                    }
                }
            }
            return pow(10, $base+1);
        }

        return 1;
    }

    /**
     * Get the axis intersection pixel position
     *
     * This is only to be called prior to output! I.e. between the user
     * invokation of tx_pbimagegraph::done() and any actual output is performed.
     * This is because it can change the axis range.
     *
     * @param double $value the intersection value to get the pixel-point for
     * @return double The pixel position along the axis
     * @access private
     */
    function _intersectPoint($value)
    {        
        if (($value <= 0) && ($value !== 'max') && ($value !== 'min')) {
            $value = 1;
        }
        return parent::_intersectPoint($value);
    }
    
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/Axis/class.tx_pbimagegraph_axis_logarithmic.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/Axis/class.tx_pbimagegraph_axis_logarithmic.php']);
}
?>