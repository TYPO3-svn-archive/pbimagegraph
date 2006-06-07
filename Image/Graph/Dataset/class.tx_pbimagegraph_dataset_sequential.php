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
 * @subpackage Dataset
 * @author     Jesper Veggerby <pear.nosey@veggerby.dk>
 * @copyright  Copyright (C) 2003, 2004 Jesper Veggerby Hansen
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/tx_pbimagegraph
 */

/**
 * Include file Image/Graph/Dataset/Trivial.php
 */
require_once(PATH_site.t3lib_extMgm::siteRelPath("pbimagegraph").'Image/Graph/Dataset/class.tx_pbimagegraph_dataset_trivial.php');

/**
 * Sequential data set, simply add points (y) 1 by 1.
 *
 * This is a single point (1D) dataset, all points are of the type (0, y1), (1,
 * y2), (2,  y3)... Where the X-value is implicitly incremented. This is useful
 * for example for barcharts, where you could fx. use an {@link
 * tx_pbimagegraph_Dataset_Array} datapreprocessor to make sence of the x-values.
 *
 * @category   Images
 * @package    tx_pbimagegraph
 * @subpackage Dataset
 * @author     Jesper Veggerby <pear.nosey@veggerby.dk>
 * @copyright  Copyright (C) 2003, 2004 Jesper Veggerby Hansen
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/tx_pbimagegraph
 */
class tx_pbimagegraph_Dataset_Sequential extends tx_pbimagegraph_Dataset_Trivial
{

    /**
     * tx_pbimagegraph_SequentialDataset [Constructor]
     */
    function tx_pbimagegraph_Dataset_Sequential($dataArray = false)
    {
        parent::tx_pbimagegraph_Dataset_Trivial();
        if (is_array($dataArray)) {
            reset($dataArray);
            foreach ($dataArray as $value) {
                $this->addPoint($value);
            }
        }
    }

    /**
     * Add a point to the dataset
     *
     * @param int $y The Y value to add, can be omited
     * @param var $ID The ID of the point
     */
    function addPoint($y, $ID = false)
    {
        parent::addPoint($this->count(), $y);
    }

    /**
     * Gets a X point from the dataset
     *
     * @param var $x The variable to return an X value from, fx in a
     *   vector function data set
     * @return var The X value of the variable
     * @access private
     */
    function _getPointX($x)
    {
        return ((int) $x);
    }

    /**
     * The minimum X value
     * @return var The minimum X value
     */
    function minimumX()
    {
        return 0;
    }

    /**
     * The maximum X value
     * @return var The maximum X value
     */
    function maximumX()
    {
        return $this->count();
    }

}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/Dataset/class.tx_pbimagegraph_dataset_sequential.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/Dataset/class.tx_pbimagegraph_dataset_sequential.php']);
}
?>