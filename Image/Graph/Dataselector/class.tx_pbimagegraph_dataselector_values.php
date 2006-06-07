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
 * @subpackage DataSelector
 * @author     Jesper Veggerby <pear.nosey@veggerby.dk>
 * @copyright  Copyright (C) 2003, 2004 Jesper Veggerby Hansen
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/tx_pbimagegraph
 */

/**
 * Include file Image/Graph/DataSelector.php
 */
require_once(PATH_site.t3lib_extMgm::siteRelPath("pbimagegraph").'Image/Graph/class.tx_pbimagegraph_dataselector.php');

/**
 * Filter out all but the specified values.
 *
 * @category   Images
 * @package    tx_pbimagegraph
 * @subpackage DataSelector
 * @author     Jesper Veggerby <pear.nosey@veggerby.dk>
 * @copyright  Copyright (C) 2003, 2004 Jesper Veggerby Hansen
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/tx_pbimagegraph
 */
class tx_pbimagegraph_DataSelector_Values extends tx_pbimagegraph_DataSelector {

    /**
     * The array with values that should be included
     * @var array
     * @access private
     */
    var $_values;

    /**
     * ValueArray [Constructor]
     *
     * @param array $valueArray The array to use as filter (default empty) 
     */
    function &tx_pbimagegraph_DataSelector_Values($values)
    {
        parent::tx_pbimagegraph_DataSelector();
        $this->_values = $values;
    }

    /**
     * Sets the array to use
     */
    function setValueArray($values)
    {
        $this->_values = $values;
    }

    /**
     * Check if a specified value should be 'selected', ie shown as a marker
     *
     * @param array $values The values to check
     * @return bool True if the Values should cause a marker to be shown, false
     *   if not
     * @access private
     */
     function _select($values)
     {
        return ( in_array($values['Y'], $this->_values) );
     }
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/Dataselector/class.tx_pbimagegraph_dataselector_values.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/Dataselector/class.tx_pbimagegraph_dataselector_values.php']);
}
?>