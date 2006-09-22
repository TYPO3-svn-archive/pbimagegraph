<?php
/*  Copyright notice
*  
*  (c) 2005 Patrick Broens (patrick@patrickbroens.nl)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is 
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
* 
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_site.t3lib_extMgm::siteRelPath("pbimagegraph").'class.tx_pbimagegraph_ts.php');

/**
 * Frontend Module for the 'pbimagegraph' extension.
 *
 * @author Patrick Broens <patrick@patrickbroens.nl>
 * @package TYPO3
 * @subpackage pbsurvey
 */
class tx_pbimagegraph_pi1 extends tslib_pibase {
	var $prefixId = 'tx_pbsurvey_pi1';
	
	function main($strContent,$arrConf)	{
		if ($arrConf) {
			$strOutput = tx_pbimagegraph_ts::make($arrConf);
		}
		return $this->pi_wrapInBaseClass($strOutput);
	}
}
	
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/pi1/class.tx_pbimagegraph_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/pi1/class.tx_pbimagegraph_pi1.php']);
}
?>