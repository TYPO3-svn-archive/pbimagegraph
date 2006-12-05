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
 * @version    CVS: $Id: class.tx_pbimagegraph_datapreprocessor_function.php 3766 2006-09-22 14:43:52Z patrickbroens $
 * @link       http://pear.php.net/package/tx_pbimagegraph
 */

/**
 * Include file Image/Graph/DataPreprocessor.php
 */
require_once(PATH_site.t3lib_extMgm::siteRelPath("pbimagegraph").'Image/Graph/class.tx_pbimagegraph_datapreprocessor.php');

/**
 * Formatting a value using a userdefined function.
 *
 * Use this method to convert/format a value to a 'displayable' lable using a (perhaps)
 * more complex function. An example could be (not very applicable though) if one would
 * need for values to be displayed on the reverse order, i.e. 1234 would be displayed as
 * 4321, then this method can solve this by creating the function that converts the value
 * and use the FunctionData datapreprocessor to make tx_pbimagegraph use this function.
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
class tx_pbimagegraph_DataPreprocessor_Function extends tx_pbimagegraph_DataPreprocessor
{

    /**
     * The name of the PHP function
     * @var string
     * @access private
     */
    var $_dataFunction;

    /**
     * Create a FunctionData preprocessor
     *
     * @param string $function The name of the PHP function to use as
     *   a preprocessor, this function must take a single parameter and return a
     *   formatted version of this parameter
     */
    function tx_pbimagegraph_DataPreprocessor_Function($function)
    {
        parent::tx_pbimagegraph_DataPreprocessor();
        $this->_dataFunction = $function;
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
        $function = $this->_dataFunction;
        return $function ($value);
    }

}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/DataPreprocessor/class.tx_pbimagegraph_datapreprocessor_function.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/DataPreprocessor/class.tx_pbimagegraph_datapreprocessor_function.php']);
}
?>