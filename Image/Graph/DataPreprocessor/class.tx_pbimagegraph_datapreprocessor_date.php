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
 * @subpackage DataPreprocessor
 * @author     Jesper Veggerby <pear.nosey@veggerby.dk>
 * @copyright  Copyright (C) 2003, 2004 Jesper Veggerby Hansen
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    CVS: $Id: class.tx_pbimagegraph_datapreprocessor_date.php 3766 2006-09-22 14:43:52Z patrickbroens $
 * @link       http://pear.php.net/package/tx_pbimagegraph
 */

/**
 * Include file Image/Graph/DataPreprocessor.php
 */
require_once(PATH_site.t3lib_extMgm::siteRelPath("pbimagegraph").'Image/Graph/class.tx_pbimagegraph_datapreprocessor.php');

/**
 * Formats Unix timestamp as a date using specified format.
 *
 * @category   Images
 * @package    tx_pbimagegraph
 * @subpackage DataPreprocessor
 * @author     Jesper Veggerby <pear.nosey@veggerby.dk>
 * @copyright  Copyright (C) 2003, 2004 Jesper Veggerby Hansen
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/tx_pbimagegraph
 */
class tx_pbimagegraph_DataPreprocessor_Date extends tx_pbimagegraph_DataPreprocessor
{

    /**
     * The format of the Unix time stamp.
     * See <a href = 'http://www.php.net/manual/en/function.date.php'>PHP
     * Manual</a> for a description
     * @var string
     * @access private
     */
    var $_format;

    /**
     * Create a DateData preprocessor [Constructor]
     *
     * @param string $format See  {@link http://www.php.net/manual/en/function.date.php
     *   PHP Manual} for a description
     */
    function tx_pbimagegraph_DataPreprocessor_Date($format)
    {
        parent::tx_pbimagegraph_DataPreprocessor();
        $this->_format = $format;
    }

    /**
     * Process the value
     *
     * @param var $value The value to process/format
     * @return string The processed value
     * @access private
     */
    function _process($value)
    {
        if (!$value) {
            return false;
        } else {
            return date($this->_format, $value);
        }
    }

}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/DataPreprocessor/class.tx_pbimagegraph_datapreprocessor_date.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/DataPreprocessor/class.tx_pbimagegraph_datapreprocessor_date.php']);
}
?>