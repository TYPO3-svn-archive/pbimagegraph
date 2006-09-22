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

require_once(PATH_site.t3lib_extMgm::siteRelPath("pbimagegraph").'Image/class.tx_pbimagegraph.php');
require_once(PATH_site.t3lib_extMgm::siteRelPath("pbimagegraph").'Image/class.tx_pbimagegraph_canvas.php');

/**
 * Library for the 'pbimagegraph' extension.
 *
 * @author Patrick Broens <patrick@patrickbroens.nl>
 * @package TYPO3
 * @subpackage pbsurvey
 */
class tx_pbimagegraph_ts {
	
	/**
	 * Initialisation of the ImageGraph object. Checks if the file is already generated,
	 * otherwise generation of the file is not necessary.
	 *
	 * @param	array		TS Configuration of the image
	 * @return	string		The img tag
	 */
	function make($arrConf)	{
		define('IMAGE_CANVAS_SYSTEM_FONT_PATH', PATH_site);
		if ($arrConf) {
			$strFileName = tx_pbimagegraph_ts::fileName('ImageGraph/',$arrConf,$arrConf['factory']);
			if (!@file_exists($strFileName)) {
				tx_pbimagegraph_ts::canvas($arrConf);
				$this->objGraph->done(array('filename' => $strFileName));
			}
			$strAltParam = tx_pbimagegraph_ts::getAltParam($arrConf);
			$strOutput = '<img src="'.$strFileName.'" '.$strAltParam.' />';
		}
		return $strOutput;
	}
	
	/**
	 * Initialisation of the ImageGraph canvas, according to the TS configuration.
	 * Call cObjGet to fill the canvas with content.
	 *
	 * @param	array		TS Configuration of the image
	 */
	function canvas($arrConf) {
		$objEmpty = null;
		$strFactory = $arrConf['factory']?$arrConf['factory']:'png';
    	$arrParams['width'] = $arrConf['width']?$arrConf['width']:'400';
    	$arrParams['height'] = $arrConf['height']?$arrConf['height']:'300';
    	$arrParams['left'] = $arrConf['left'];
    	$arrParams['top'] = $arrConf['top'];
    	$arrParams['top'] = $arrConf['top'];
    	$arrParams['noalpha'] = $arrConf['noalpha'];
    	/* When antialias = native is used Image_Graph is going to call the PHP function imageantialias
    	 * It checks on GD installation but not on the PHP version. 
    	 * The function imageantialias became available in PHP version 4.3.2
    	 * I didn't change this in the PEAR package itself because I want the code as untouched as possible
    	 * because that way it is easier to implement updates from the PEAR package.
    	 */
    	if (function_exists('imageantialias') && $arrConf['antialias']=='native') {
 			$arrConf['antialias'] = 'native';
		} else {
 			$arrConf['antialias'] = 'off';
		}
    	$arrParams['antialias'] = $arrConf['antialias']?$arrConf['antialias']:'off';
		$Canvas =& tx_pbimagegraph_Canvas::factory($strFactory, $arrParams);    
		$this->objGraph =& tx_pbimagegraph::factory('graph', $Canvas);
		tx_pbimagegraph_ts::setElementProperties($this->objGraph,$arrConf);
		$objLayout = tx_pbimagegraph_ts::cObjGet($arrConf,$objEmpty);
		$this->objGraph->add($objLayout);
	}
	
	/**
	 * Calculates the ImageGraph output filename/path based on a serialized, hashed value of $arrConf
	 *
	 * @param	string		Filename prefix, eg. "ImageGraph/"
	 * @param	array		TS Configuration of the image
	 * @param	string		Filename extension, eg. "png"
	 * @return	string		The relative filepath (relative to PATH_site)
	 * @access private
	 */
	function fileName($strPre,$arrConf,$strExtension) {
		$tempPath = 'typo3temp/'; // Path to the temporary directory
		return $tempPath.$strPre.t3lib_div::shortMD5(serialize($arrConf)).'.'.$strExtension;
	}
	
	/**
	 * An abstraction method which creates an alt or title parameter for an HTML img tag.
	 * From the $arrConf array it implements the properties "altText", "titleText" and "longdescURL"
	 *
	 * @param	array		TypoScript configuration properties
	 * @return	string		Parameter string containing alt and title parameters (if any)
	 */
	function getAltParam($arrConf)	{
		$strAltText = $arrConf['altText'];
		$strTitleText = $arrConf['titleText'];
		$strLongDesc = $arrConf['longdescURL'];
		$strAltParam = ' alt="'.htmlspecialchars(strip_tags($strAltText)).'"';
		$strEmptyTitleHandling = 'useAlt';
		if ($arrConf['emptyTitleHandling'])	{
				// choices: 'keepEmpty' | 'useAlt' | 'removeAttr'
			$strEmptyTitleHandling = $arrConf['emptyTitleHandling'];
		}
		if ($strTitleText || $strEmptyTitleHandling == 'keepEmpty')	{
			$strAltParam.= ' title="'.htmlspecialchars(strip_tags($strTitleText)).'"';
		} elseif (!$strTitleText && $strEmptyTitleHandling == 'useAlt')	{
			$strAltParam.= ' title="'.htmlspecialchars(strip_tags($strAltText)).'"';
		}
		if ($strLongDesc)	{
			$strAltParam.= ' longdesc="'.htmlspecialchars(strip_tags($strLongDesc)).'"';
		}
		return $strAltParam;
	}

	/**
	 * Rendering of a "numerical array" of cObjects from TypoScript
	 * Will call ->cObjGetSingle() for each cObject found and accumulate the output.
	 *
	 * @param	array		Array with cObjects as values.
	 * @param	object		Reference object.
	 * @return	object		The object.
	 */	

	function cObjGet($arrSetup,&$objRef) {
		if (is_array($arrSetup)) {
			$arrSortedKeys=t3lib_TStemplate::sortedKeyList($arrSetup);
			foreach($arrSortedKeys as $strKey) {
				$strCobjName=$arrSetup[$strKey];
				if (intval($strKey) && !strstr($strKey,'.')) {
					$arrConf=$arrSetup[$strKey.'.'];
					$objOutput = tx_pbimagegraph_ts::cObjGetSingle($strCobjName,$arrConf,$objRef);
				}
			}
		}
		return $objOutput;
	}
	
	/**
	 * Renders a content object
	 *
	 * @param	string		The content object name, eg. "CANVAS" or "PLOTAREA" or "LEGEND"
	 * @param	array		The array with TypoScript properties for the content object
	 * @param	object		Reference object.
	 * @return	object		The object
	 */
	function cObjGetSingle($strCobjName,$arrConf,&$objRef)	{
		$objEmpty = null;
		$GLOBALS['TSFE']->cObjectDepthCounter--;
		if ($GLOBALS['TSFE']->cObjectDepthCounter>0)	{
			$strCobjName = trim($strCobjName);
			if (substr($strCobjName,0,1)=='<')	{
				$strKey = trim(substr($strCobjName,1));
				$objTSparser = t3lib_div::makeInstance('t3lib_TSparser');
				$arrOldConf=$arrConf;
				list($strCobjName, $arrConf) = $objTSparser->getVal($strKey,$GLOBALS['TSFE']->tmpl->setup);
				if (is_array($arrOldConf) && count($arrOldConf))	{
					$conf = $this->joinTSarrays($arrConf,$arrOldConf);
				}
				$GLOBALS['TT']->incStackPointer();
				$objOutput =& tx_pbimagegraph_ts::cObjGetSingle($strCobjName,$arrConf,$objEmpty);
				$GLOBALS['TT']->decStackPointer();
			} else {	
				switch($strCobjName) {
					case 'PLOTAREA':
						$objOutput =& tx_pbimagegraph_ts::PLOTAREA($arrConf);
						break;
					case 'AXIS_MARKER':
						tx_pbimagegraph_ts::AXIS_MARKER($objRef,$arrConf);
						break;
					// Types of charts
					case 'LINE':
					case 'AREA':
					case 'BAR':
					case 'BOXWHISKER':
					case 'CANDLESTICK':
					case 'SMOOTH_LINE':
					case 'SMOOTH_AREA':
					case 'ODO':
					case 'PIE':
					case 'RADAR':
					case 'STEP':
					case 'IMPULSE':
					case 'DOT':
					case 'SCATTER':
					case 'BAND':
					case 'SMOOTH_RADAR':
					case 'FIT_LINE':
						tx_pbimagegraph_ts::PLOT($strCobjName,$objRef,$arrConf);
						break;
					// Data
					case 'DATASET':
						$objOutput = tx_pbimagegraph_ts::DATASET($arrConf);
						break;
					case 'RANDOM':
						$objOutput = tx_pbimagegraph_ts::RANDOM($arrConf);
						break;
					case 'FUNCTION':
						$objOutput = tx_pbimagegraph_ts::FUNCTIO($arrConf);
						break;
					case 'VECTOR':
						$objOutput = tx_pbimagegraph_ts::VECTOR($arrConf);
						break;
					// Axis
					case 'CATEGORY':
						$objOutput = tx_pbimagegraph_ts::CATEGORY($arrConf);
						break;
					case 'AXIS':
						$objOutput = tx_pbimagegraph_ts::AXIS($arrConf);
						break;
					case 'AXIS_LOG':
						$objOutput = tx_pbimagegraph_ts::AXIS_LOG($arrConf);
						break;
					// Title
					case 'TITLE':
						$objOutput = tx_pbimagegraph_ts::TITLE($arrConf);
						break;
					// Grids
					case 'GRID':
						tx_pbimagegraph_ts::GRID($objRef,$arrConf);
						break;
					// Various
					case 'LEGEND':
						$objOutput = tx_pbimagegraph_ts::LEGEND($objRef,$arrConf);
						break;
					// Layout
					case 'VERTICAL':
					case 'HORIZONTAL':
						$objOutput = tx_pbimagegraph_ts::VERT_HOR($arrConf,$strCobjName);
						break;
					case 'MATRIX':
						$objOutput = tx_pbimagegraph_ts::MATRIX($arrConf);
						break;
				}
			}
		}
			// Increasing on exit...
		$GLOBALS['TSFE']->cObjectDepthCounter++;
		return $objOutput;
	}

	/**
	 * Draws the Plot Area
	 *
	 * @param	array		The array with TypoScript properties for the content object
	 * @return	object		The Plot Area object
	 */	
	function PLOTAREA($arrConf) {
		$id = $arrConf['id'];
		switch($arrConf['type']) {
			case 'radar':
				$Plotarea = tx_pbimagegraph::factory('tx_pbimagegraph_Plotarea_Radar');
				break;
			default:
				$strAxisX = $arrConf['axis.']['x.']['type']?'tx_pbimagegraph_Axis_'.ucfirst($arrConf['axis.']['x.']['type']):'tx_pbimagegraph_Axis_Category';
				$strAxisY = $arrConf['axis.']['y.']['type']?'tx_pbimagegraph_Axis_'.ucfirst($arrConf['axis.']['y.']['type']):'tx_pbimagegraph_Axis';
				$strDirection = $arrConf['direction']?$arrConf['direction']:'vertical';
				$Plotarea = tx_pbimagegraph::factory('plotarea',array($strAxisX,$strAxisY,$strDirection));
		}
		tx_pbimagegraph_ts::cObjGet($arrConf,$Plotarea);
		tx_pbimagegraph_ts::setPlotareaProperties($Plotarea,$arrConf);
		tx_pbimagegraph_ts::setElementProperties($Plotarea,$arrConf);
		
		if ($id) {
			eval("\$this->".$id." =& \$Plotarea;");
		}
		return $Plotarea;
	}

	/**
	 * Draws a Axis Marker
	 *
	 * @param	array		The array with TypoScript properties for the content object
	 */		
	function AXIS_MARKER(&$objRef,$arrConf) {
		$strType = $arrConf['type'];
		$intAxis = IMAGE_GRAPH_AXIS_Y;
		eval("\$intAxis = IMAGE_GRAPH_AXIS_".strtoupper($arrConf['axis']).";");
		$Marker =& $objRef->addNew('tx_pbimagegraph_Axis_Marker_'.ucfirst($strType), null, $intAxis);
		tx_pbimagegraph_ts::setElementProperties($Marker,$arrConf);
		switch($strType) {
			case 'area':
				tx_pbimagegraph_ts::setAxisMarkerAreaProperties($Marker,$arrConf);
			break;
			case 'line':
				tx_pbimagegraph_ts::setAxisMarkerLineProperties($Marker,$arrConf);
			break;
		}		
	}

	/**
	 * Draws one of the Plot Types
	 *
	 * @param	string		Name of the content object
	 * @param	object		The parent object
	 * @param	array		The array with TypoScript properties for the content object
	 */	
	function PLOT($strCobjName,&$objRef,$arrConf) {
		$arrClassAlias = array(			
			'AREA'           => 'tx_pbimagegraph_Plot_Area',
			'BAND'           => 'tx_pbimagegraph_Plot_Band',
			'BAR'            => 'tx_pbimagegraph_Plot_Bar',
			'BOXWHISKER'     => 'tx_pbimagegraph_Plot_BoxWhisker',
			'CANDLESTICK'    => 'tx_pbimagegraph_Plot_CandleStick',
			'DOT'            => 'tx_pbimagegraph_Plot_Dot',
			'IMPULSE'        => 'tx_pbimagegraph_Plot_Impulse',
			'LINE'           => 'tx_pbimagegraph_Plot_Line',
			'ODO'            => 'tx_pbimagegraph_Plot_Odo',
			'PIE'            => 'tx_pbimagegraph_Plot_Pie',
			'RADAR'          => 'tx_pbimagegraph_Plot_Radar',
			'SCATTER'        => 'tx_pbimagegraph_Plot_Dot',
			'STEP'           => 'tx_pbimagegraph_Plot_Step', 
            'SMOOTH_AREA'    => 'tx_pbimagegraph_Plot_Smoothed_Area',
            'SMOOTH_LINE'    => 'tx_pbimagegraph_Plot_Smoothed_Line',			
            'SMOOTH_RADAR'   => 'tx_pbimagegraph_Plot_Smoothed_Radar',
            'FIT_LINE'       => 'tx_pbimagegraph_Plot_Fit_Line',
		);
		$intAxis = false;
		if (isset($arrConf['axis'])) {
			eval("\$intAxis = IMAGE_GRAPH_AXIS_".strtoupper($arrConf['axis']).";");
		}
		$arrParams[] = tx_pbimagegraph_ts::setDatasets($arrConf['dataset.']);
		$arrParams[] = isset($arrConf['plottype'])?$arrConf['plottype']:'normal'; // normal, stacked, stacked100pct
		$strClass = $arrClassAlias[$strCobjName];
		$objPlot =& $objRef->addNew($strClass,$arrParams,$intAxis);
		tx_pbimagegraph_ts::setPlotProperties($objPlot,$arrConf);
		tx_pbimagegraph_ts::setElementProperties($objPlot,$arrConf);
		tx_pbimagegraph_ts::setMarker($objPlot,$arrConf);
		switch($strCobjName) {
			case 'BAR':
				tx_pbimagegraph_ts::setBarProperties($objPlot,$arrConf);
				break;
			case 'BOXWHISKER':	
				tx_pbimagegraph_ts::setBoxWhiskerProperties($objPlot,$arrConf);
				break;
			case 'ODO':
				tx_pbimagegraph_ts::setOdoProperties($objPlot,$arrConf);
				break;
			case 'PIE':
				tx_pbimagegraph_ts::setPieProperties($objPlot,$arrConf);
				break;
		}
	}
	
	/**
	 * Divide the Plot Area into Vertical and/or Horizontal parts
	 *
	 * @param	array		The array with TypoScript properties for the content object
	 * @param	string		Name of the content object
	 * @return	object		The Vertical or Horizontal object
	 */	
	function VERT_HOR($arrConf,$strCobjName) {
		$objEmpty = null;
		$percentage = $arrConf['percentage']?$arrConf['percentage']:'50';
		$Vert_Hor = '';
		$cObjCount = 1;	
		if (is_array($arrConf)) {
			$sKeyArray=t3lib_TStemplate::sortedKeyList($arrConf);
			foreach($sKeyArray as $theKey) {		
				$theValue=$arrConf[$theKey];
				if (intval($theKey) && !strstr($theKey,'.')) {
					$conf=$arrConf[$theKey.'.'];
					if ($cObjCount == 1) {
						$objTopLeft = tx_pbimagegraph_ts::cObjGetSingle($theValue,$conf,$objEmpty);
						$cObjCount++;
					} elseif ($cObjCount == 2) {
						$objBottomRight = tx_pbimagegraph_ts::cObjGetSingle($theValue,$conf,$objEmpty);
						$cObjCount++;
					} else {
						break;
					}
				}
			}
		}
		eval("\$Vert_Hor = tx_pbimagegraph::".strtolower($strCobjName)."(\$objTopLeft,\$objBottomRight,\$percentage);");
	    return $Vert_Hor;
	}

	/**
	 * Divide the Plot Area into a Matrix
	 *
	 * @param	array		The array with TypoScript properties for the content object
	 * @param	string		Name of the content object
	 * @return	object		The Matrix object
	 */		
	function MATRIX($arrConf) {
		$intCols = 0;
		$intRows = 0;
		$objEmpty = null;
		$boolAutoCreate = $arrConf['autoCreate']?$arrConf['autoCreate']:true;
		if (is_array($arrConf)) {
			foreach ($arrConf as $strRow => $mixRow) {
				if (intval(rtrim($strRow, '.'))) {
					$intRows++;
					$intThisCols = count(t3lib_TStemplate::sortedKeyList($mixRow));
					if ($intCols==0) {
						$intCols = $intThisCols;
					} elseif ($intThisCols<$intCols) {
						$intCols = $intThisCols;
					}
				}
			}
		}
		$objMatrix = tx_pbimagegraph::factory('tx_pbimagegraph_Layout_Matrix', array($intRows, $intCols,$boolAutoCreate));
		$intRow = 0;
		if (is_array($arrConf)) {
			foreach ($arrConf as $strRow => $mixRow) {
				if (intval(rtrim($strRow, '.'))) {
					$arrSortedCols = t3lib_TStemplate::sortedKeyList($mixRow);
					foreach($arrSortedCols as $intCol=>$intColKey) {	
						$strcObj=$mixRow[$intColKey];
						$arrcObjProperties = $mixRow[$intColKey.'.'];
						if (intval($intColKey) && !strstr($intColKey,'.') && $strcObj=='PLOTAREA') {
							if ($strcObj=='PLOTAREA') {
								$objPlotarea =& $objMatrix->getEntry($intRow, $intCol);
								tx_pbimagegraph_ts::cObjGet($arrcObjProperties,$objPlotarea);
							} else {
								tx_pbimagegraph_ts::cObjGetSingle($strcObj,$arrcObjProperties,$objEmpty);
							}
						}
					}
				$intRow++;
				}
			}
		}		
		return $objMatrix;
	}
	
	/**
	 * Create the Title
	 *
	 * @param	array		The array with TypoScript properties for the content object
	 * @return	object		The Title object
	 */		
	function TITLE($arrConf) {
		$intSize = $arrConf['size'];
		$intAngle = $arrConf['angle'];
		$strColor = $arrConf['color'];
		$Title = tx_pbimagegraph::factory('title', array('Title', array('size' => $intSize, 'angle' => $intAngle, 'color' => $strColor)));
		tx_pbimagegraph_ts::setElementProperties($Title,$arrConf);
		tx_pbimagegraph_ts::setTitleProperties($Title,$arrConf);
		return $Title;
	}
	
	/**
	 * Create a Grid
	 *
	 * @param	object		The parent object
	 * @param	array		The array with TypoScript properties for the content object
	 */	
	function GRID(&$objRef,$arrConf) {
		$strType = $arrConf['type'].'_grid';
		$strAxis = $arrConf['axis'];
		$intAxis = 1;
		eval("\$intAxis = IMAGE_GRAPH_AXIS_".strtoupper($strAxis).";");
		$Grid =& $objRef->addNew($strType, $intAxis);
		tx_pbimagegraph_ts::setElementProperties($Grid,$arrConf);
	}
	
	/**
	 * Create the Legend
	 *
	 * @param	object		The parent object
	 * @param	array		The array with TypoScript properties for the content object
	 * @return	object		The Legend object
	 */	
	function LEGEND(&$objRef,$arrConf) {
		if ($objRef) {
			$Legend =& $objRef->addNew('legend');
		} else {
			$Legend = tx_pbimagegraph::factory('legend');
		}
		tx_pbimagegraph_ts::setElementProperties($Legend,$arrConf);
		tx_pbimagegraph_ts::setLegendProperties($Legend,$arrConf);
		return $Legend;		
	}
	
	/**
	 * Set the datasets
	 *
	 * @param	array		The array with TypoScript properties for the object
	 * @return	object		The Dataset object
	 */
	function setDatasets($arrConf) {
		$intCount = 0;
		if (is_array($arrConf)) {
			$arrKeys=t3lib_TStemplate::sortedKeyList($arrConf);
			foreach($arrKeys as $strKey) {		
				$strValue=$arrConf[$strKey];
				if (intval($strKey) && !strstr($strKey,'.')) {
					switch($strValue) {
						case 'trivial':
							$objDatasets[$intCount] =& tx_pbimagegraph::factory('dataset');
							tx_pbimagegraph_ts::datasetTrivial($objDatasets[$intCount],$arrConf[$strKey.'.']);
						break;
						case 'random':
							$objDatasets[$intCount] = tx_pbimagegraph_ts::datasetRandom($arrConf[$strKey.'.']);
						break;
					}
					$intCount++;
				}
			}
		}
		return $objDatasets;
	}
	
	/**
	 * Set a single trivial dataset
	 *
	 * @param	object		The parent Dataset object
	 * @param	array		The array with TypoScript properties for the object
	 */
	function datasetTrivial(&$objRef,$arrConf) {
		if (is_array($arrConf)) {
			$arrKeys=t3lib_TStemplate::sortedKeyList($arrConf);
			foreach($arrKeys as $strKey) {		
				$strValue=$arrConf[$strKey];
				if (intval($strKey) && !strstr($strKey,'.')) {
					if($strValue=='point') {
						$mixX = $arrConf[$strKey.'.']['x'];
						if ($arrConf[$strKey.'.']['y']=='null') {
							$mixY = null;
						} elseif (is_array($arrConf[$strKey.'.']['y.'])) {
							$mixY = $arrConf[$strKey.'.']['y.'];
						} else {
							$mixY = $arrConf[$strKey.'.']['y'];
						}
						//$mixY = ($arrConf[$strKey.'.']['y']=='null')?null:$arrConf[$strKey.'.']['y'];
						$strId = $arrConf[$strKey.'.']['id'];
						$objRef->addPoint($mixX, $mixY, $strId);
					}
				}
			}
		}
	}
	
	/**
	 * Create a single random dataset
	 *
	 * @param	array		The array with TypoScript properties for the object
	 * @return	object		Single dataset
	 */		
	function datasetRandom($arrConf) {
		$intCount = $arrConf['count'];
		$intMinimum = $arrConf['minimum'];
		$intMaximum = $arrConf['maximum'];
		$boolIncludeZero = $arrConf['includeZero']=='true'?true:false;
		$strName = $arrConf['name'];
		$objRandom = tx_pbimagegraph::factory('random', array($intCount, $intMinimum, $intMaximum, $boolIncludeZero));
		$objRandom->setName($strName);
		return $objRandom;
	}
	
	/**
	 * Set a marker
	 *
	 * @param	object		The parent cObject
	 * @param	array		The array with TypoScript properties for the object
	 */
	function setMarker(&$objRef,$arrConf) {
		if ($arrConf['marker']) {
			switch($arrConf['marker']) {
				case 'value':
					$intAxis = 0;
					eval("\$intAxis = IMAGE_GRAPH_".strtoupper($arrConf['marker.']['useValue']).";");
					$objMarker =& $objRef->addNew('tx_pbimagegraph_Marker_'.ucfirst($arrConf['marker']),$intAxis);
					break;
				case 'array':
					$objMarker =& $objRef->addNew('tx_pbimagegraph_Marker_Array');
					if (is_array($arrConf['marker.'])) {
						$arrKeys=t3lib_TStemplate::sortedKeyList($arrConf['marker.']);
						foreach($arrKeys as $strKey) {		
							$strType=$arrConf['marker.'][$strKey];
							if (intval($strKey) && !strstr($strKey,'.')) {
								switch($strType) {
									case 'icon':
									//$Marker->addNew('icon_marker', './images/audi.png');
										$objArrayMarker[$strKey] =& tx_pbimagegraph::factory('tx_pbimagegraph_Marker_Icon',PATH_site.$arrConf['marker.'][$strKey.'.']['image']);
										break;
									default:
										$objArrayMarker[$strKey] =& tx_pbimagegraph::factory('tx_pbimagegraph_Marker_'.ucfirst($strType));
								}
								tx_pbimagegraph_ts::setMarkerProperties($objArrayMarker[$strKey],$arrConf['marker.'][$strKey.'.']);
								tx_pbimagegraph_ts::setElementProperties($objArrayMarker[$strKey],$arrConf['marker.'][$strKey.'.']);
								$objMarker->add($objArrayMarker[$strKey]);
							}
						}
					}
					break;
				case 'icon':
					$objMarker =& $objRef->addNew('tx_pbimagegraph_Marker_Icon',PATH_site.$arrConf['marker.']['image']);
					break;
				default:
					$objMarker =& $objRef->addNew('tx_pbimagegraph_Marker_'.ucfirst($arrConf['marker']));
			}
			tx_pbimagegraph_ts::setMarkerProperties($objMarker,$arrConf['marker.']);
			tx_pbimagegraph_ts::setElementProperties($objMarker,$arrConf['marker.']);
			if ($arrConf['marker.']['pointing']) {
				$objPointing =& $objRef->addNew('tx_pbimagegraph_Marker_Pointing_'.ucfirst($arrConf['marker.']['pointing']), array($arrConf['marker.']['pointing.']['radius'], $objMarker));		
				$objSetMarker =& $objPointing;
			} else {
				$objSetMarker =& $objMarker;
			}
			$objRef->setMarker($objSetMarker);
		}
	}
	
	/**
	 * Set the range marker for ODO cObject
	 *
	 * @param	object		The parent cObject
	 * @param	array		The array with TypoScript properties for the object
	 */
	function setRangeMarker(&$objRef,$arrConf) {
		foreach($arrConf as $strKey=>$strValue) {		
			$mixId = $strValue['id']?$strValue['id']:false;
			$objRef->addRangeMarker($strValue['min'], $strValue['max'], $mixId);
		}
	}
	
	/**
	 * Preprocess data before entering in a marker
	 *
	 * @param	object		The parent cObject
	 * @param	string		Type of preprocessing
	 * @param	array		The array with TypoScript properties for the object
	 */
	function setDataPreprocessor(&$objRef,$strType,$arrConf) {
		switch($strType) {
			case 'array':
					$objRef->setDataPreProcessor(tx_pbimagegraph::factory('tx_pbimagegraph_DataPreprocessor_Array', array($arrConf)));
			break;
			default:
				$objRef->setDataPreProcessor(tx_pbimagegraph::factory('tx_pbimagegraph_DataPreprocessor_'.ucfirst($strType), $arrConf['format']));
		}
	}

	/**
	 * Sets the fill style of an element
	 *
	 * @param	object 		Reference object
	 * @param	string		Type of fill style
	 * @param	array		Configuration of the fill style
	 */	
	function setFillStyle(&$objRef,$strValue,$arrConf,$strAction) {
		$intDirection = IMAGE_GRAPH_GRAD_HORIZONTAL;
		eval("\$intDirection = IMAGE_GRAPH_GRAD_".strtoupper($arrConf['direction']).";");
		$strStartColor = $arrConf['startColor'];
		$strEndColor = $arrConf['endColor'];
		$intSolidColor = $arrConf['color'];
		$strImage = $arrConf['image'];
		switch ($strValue) {
			case 'gradient':
				$objFillStyle =& tx_pbimagegraph::factory('gradient', array($intDirection, $strStartColor, $strEndColor));
				break;
			case 'fill_array':
				$objFillStyle =& tx_pbimagegraph::factory('tx_pbimagegraph_Fill_Array');
				if (is_array($arrConf)) {
					$arrKeys=t3lib_TStemplate::sortedKeyList($arrConf);
					foreach($arrKeys as $strKey) {		
						$strType=$arrConf[$strKey];
						if (intval($strKey) && !strstr($strKey,'.')) {
							switch($strType) {
								case 'addColor':
									$strColor = $arrConf[$strKey.'.']['color'];
									$strId = $arrConf[$strKey.'.']['id']?$arrConf[$strKey.'.']['id']:false;
									$objFillStyle->addColor($strColor,$strId);
								break;
								case 'gradient':
									eval("\$intDirection = IMAGE_GRAPH_GRAD_".strtoupper($arrConf[$strKey.'.']['direction']).";");
									$strStartColor = $arrConf[$strKey.'.']['startColor'];
									$strEndColor = $arrConf[$strKey.'.']['endColor'];
									$intSolidColor = $arrConf[$strKey.'.']['color'];
									$strId = $arrConf[$strKey.'.']['id'];
									$objFillStyle->addNew('gradient', array($intDirection, $strStartColor, $strEndColor), $strId);
								break;
							}
						}
					}
				}	
				break;
			case 'image':
				$objFillStyle =& tx_pbimagegraph::factory('tx_pbimagegraph_Fill_Image',PATH_site.$strImage);
				break;
		}
		switch ($strAction) {
			case 'setBackground':
				$objRef->setBackground($objFillStyle);
				break;
			case 'setFillStyle':
				$objRef->setFillStyle($objFillStyle);
				break;
			case 'setArrowFillStyle':
				$objRef->setArrowFillStyle($objFillStyle);
				break;
			case 'setRangeMarkerFillStyle':	
				$objRef->setRangeMarkerFillStyle($objFillStyle);
				break;
		}
	}
	
	/**
	 * Sets the line style of an element
	 *
	 * @param	object 		Reference object
	 * @param	string		Type of line style
	 * @param	array		Configuration of the line style
	 */	
	function setLineStyle(&$objRef,$strValue,$arrConf,$strAction) {
		$arrConf['color'] = $arrConf['color']?$arrConf['color']:'red';
		$arrConf['color1'] = $arrConf['color1']?$arrConf['color1']:'red';
		$arrConf['color2'] = $arrConf['color2']?$arrConf['color2']:'white';
		switch($strValue) {
			case 'dashed':
				$objLineStyle = & tx_pbimagegraph::factory('tx_pbimagegraph_Line_Dashed', array($arrConf['color1'], $arrConf['color2']));
				break;
			case 'dotted':
				$objLineStyle = & tx_pbimagegraph::factory('tx_pbimagegraph_Line_Dotted', array($arrConf['color1'], $arrConf['color2']));
				break;
			case 'solid':
				$objLineStyle = & tx_pbimagegraph::factory('tx_pbimagegraph_Line_Solid', $arrConf['color']);
				if (isset($arrConf['thickness'])) {
					$objLineStyle->setThickness($arrConf['thickness']);
				}
				break;
			case 'array':
				$objLineStyle = & tx_pbimagegraph::factory('tx_pbimagegraph_Line_Array');
				if (is_array($arrConf)) {
					$arrKeys=t3lib_TStemplate::sortedKeyList($arrConf);
					foreach($arrKeys as $strKey) {		
						$strType=$arrConf[$strKey];
						if (intval($strKey) && !strstr($strKey,'.')) {
							switch($strType) {
								case 'addColor':
									$strColor = $arrConf[$strKey.'.']['color']?$arrConf[$strKey.'.']['color']:'red';
									$strId = $arrConf[$strKey.'.']['id']?$arrConf[$strKey.'.']['id']:false;
									$objLineStyle->addColor($strColor,$strId);
								break;
							}
						}
					}
				}	
				break;
		}
		switch($strAction) {
			case 'setBorderStyle':
				$objRef->setBorderStyle($objLineStyle);
				break;
			case 'setLineStyle':
				$objRef->setLineStyle($objLineStyle);
				break;
			case 'setArrowLineStyle':
				$objRef->setArrowLineStyle($objLineStyle);
				break;
		}
	}

	/**
	 * Shows shadow of the element
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the shadow
	 */		
	function showShadow(&$objRef,$arrConf) {
		$strColor = $arrConf['color']?$arrConf['color']:'black@0.2';
		$intSize = $arrConf['size']?$arrConf['size']:'5';
		$objRef->showShadow($strColor,$intSize);
	}
	
	/**
	 * Sets the font of an element
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the font
	 */		
	function setFont(&$objRef,$arrConf) {
		$strDefaultFont = $arrConf['default'];
		$strDefaultColor = $arrConf['default.']['color'];
		$intDefaultSize = $arrConf['default.']['size'];
		$intDefaultAngle = $arrConf['default.']['angle'];
		
		if ($strDefaultFont) {
			$Font =& $objRef->addNew('font',$strDefaultFont);
			$Font->setColor($strDefaultColor);
			$Font->setSize($intDefaultSize);
			$Font->setAngle($intDefaultAngle);
			$objRef->setFont($Font);
		}
		
		$strColor = $arrConf['color'];
		$intSize = $arrConf['size'];
		$intAngle = $arrConf['angle'];
		
		$objRef->setFontColor($strColor);
		$objRef->setFontSize($intSize);
		$objRef->setFontAngle($intAngle);	
	}
	
	/**
	 * Sets the alignment of an element
	 *
	 * @param	object 		Reference object
	 * @param	string		Alignment
	 */	
	function setAlignment(&$objRef,$strValue) {
		$strAlignment = IMAGE_GRAPH_ALIGN_CENTER_X;
		eval("\$strAlignment = IMAGE_GRAPH_ALIGN_".strtoupper($strValue).";");
		$objRef->setAlignment($strAlignment);
	}
	
	/**
	 * Sets the plot area for the legend
	 *
	 * @param	object 		Reference object
	 * @param	string		Name of the plot area
	 * @param	array		Array with multiple plot area's
	 */	
	function setPlotarea(&$objRef,$strValue,$arrConf) {
		$Plotarea = '';
		if (is_array($arrConf)) {
			foreach ($arrConf as $strValue) {
				eval("\$Plotarea =& \$this->".$strValue.";");
				$objRef->setPlotarea($Plotarea);
			}
		} else {
			eval("\$Plotarea =& \$this->".$strValue.";");
			$objRef->setPlotarea($Plotarea);
		}
	}
	
	/**
	 * Sets the dataselector to specify which data should be displayed on the
     * plot as markers and which are not
	 *
	 * @param	object 		Reference object
	 * @param	string 		Type of selector
	 * @param	array		Configuration of the data selector
	 */
	function setDataSelector(&$objRef,$strValue,$arrConf) {
		switch($strValue) {
			case 'noZeros':
				$objRef->setDataSelector(tx_pbimagegraph::factory('tx_pbimagegraph_DataSelector_NoZeros'));
				break;
		}
	}
	
	/**
	 * Get Axis
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the data selector
	 */
	function getAxis(&$objRef,$arrConf) {
		$intAxis = 1;		
		foreach($arrConf as $strKey => $strValue) {
			$strKey = rtrim($strKey, '.');
			eval("\$intAxis = IMAGE_GRAPH_AXIS_".strtoupper($strKey).";");
			$objAxis =& $objRef->getAxis($intAxis);
			tx_pbimagegraph_ts::setAxisProperties($objAxis,$strValue);
			tx_pbimagegraph_ts::setElementProperties($objAxis,$strValue);
			if (is_array($strValue)) {	
				$arrKeys=t3lib_TStemplate::sortedKeyList($strValue);
				foreach($arrKeys as $strKey) {	
					$strCobjName=$strValue[$strKey];
					if (intval($strKey) && !strstr($strKey,'.')) {
						$arrConfAxis=$strValue[$strKey.'.'];
						switch($strCobjName) {
							case 'marker':
								tx_pbimagegraph_ts::setAxisMarker($objRef,$intAxis,$arrConfAxis);
							break;
						}
					}
				}
			}
		}   
	}
	
	/**
	 * Set options for the label at a specific level
	 * 
	 * 'showtext' true or false
	 * 'showoffset' true or false
	 * 'font' The font options as an associated array
	 * 'position' 'inside' or 'outside'
	 * 'format' To format the label text according to a sprintf statement
	 * 'dateformat' To format the label as a date, fx. j. M Y = 29. Jun 2005
	 * 
	 * @param	object 		Reference object
	 * @param	array		Configuration of the label
	 * @param	integer		Level
	 */
	function setLabelOptions(&$objRef,$arrConf,$intLevel) {
		if (is_array($arrConf)) {
			foreach ($arrConf as $strKey => $strValue) {
				if ($strKey=='showtext' || $strKey=='showoffset') {
					$strValue=$strValue==1?true:false;
				}
				$arrLabelOptions[rtrim($strKey, '.')] = $strValue;
			}
		}
		$objRef->setLabelOptions($arrLabelOptions, $intLevel);
	}
	
	/**
	 * Set an interval for where labels are shown on the axis
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the label
	 * @param	integer		Level
	 */
	function setLabelInterval(&$objRef,$arrConf,$intLevel) {
		if (is_array($arrConf)) {
			foreach ($arrConf as $strKey => $strValue) {
				if (intval(rtrim($strKey, '.'))) {
					$arrInterval[] = $strValue['value'];
				}
			}
			$objRef->setLabelInterval($arrInterval,$intLevel);
		} else {
			$objRef->setLabelInterval($arrConf,$intLevel);
		}
	}
	
	/**
	 * Set specific level for the axis
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the label
	 */
	function getAxisLevel(&$objRef,$arrConf) {
		if (is_array($arrConf)) {
			foreach ($arrConf as $strKey => $strValue) {
				if (intval(rtrim($strKey, '.'))) {
					tx_pbimagegraph_ts::setAxisProperties(&$objRef,$strValue,intval(rtrim($strKey, '.')));
				}
			}
		}
	}
	
	/**
	 * Adds a mark to the axis at the specified value
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the mark
	 */
	function axisAddMark(&$objRef,$arrConf) {
		if (is_array($arrConf)) {
			foreach ($arrConf as $strKey => $strValue) {
				if (intval(rtrim($strKey, '.'))) {
					$strValue['value2'] = isset($strValue['value2'])?$strValue['value2']:false;
					$strValue['text'] = isset($strValue['text'])?$strValue['text']:false;
					$objRef->addMark($strValue['value'],$strValue['value2'],$strValue['text']);
				}
			}
		}
	}

	/**
	 * Sets the properties for the plot area
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the plot area
	 */
	function setPlotareaProperties(&$objRef,$arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			$strKey = rtrim($strKey, '.');
			switch($strKey) {
				case 'axis':
					tx_pbimagegraph_ts::getAxis($objRef,$strValue) ;  
				break;
				case 'hideAxis':
					$objRef->hideAxis($strValue);
				break;
				case 'clearAxis':
					$objRef->clearAxis();
				break;
				case 'axisPadding':
					$objRef->setAxisPadding($strValue);
				break;
			}
		}
	}
	
	/**
	 * Set the properties for all elements
	 *
	 * @param	object		The parent cObject
	 * @param	array		The array with TypoScript properties for the object
	 */
	function setElementProperties(&$objRef,$arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			$strKey = rtrim($strKey, '.');
			switch($strKey) {
				// element
				case 'background':
					tx_pbimagegraph_ts::setFillStyle($objRef,$strValue,$arrConf[$strKey.'.'],'setBackground');
				break;
				case 'backgroundColor':
					$objRef->setBackgroundColor($strValue);
				break;
				case 'shadow':
					tx_pbimagegraph_ts::showShadow($objRef,$arrConf[$strKey.'.']);
				break;
				case 'borderStyle':
					tx_pbimagegraph_ts::setLineStyle($objRef,$strValue,$arrConf[$strKey.'.'],'setBorderStyle');
				break;
				case 'borderColor':
					$objRef->setBorderColor($strValue);
				break;
				case 'lineStyle':
					tx_pbimagegraph_ts::setLineStyle($objRef,$strValue,$arrConf[$strKey.'.'],'setLineStyle');
				break;
				case 'lineColor':
					$objRef->setLineColor($strValue);
				break;
				case 'fillStyle':
					tx_pbimagegraph_ts::setFillStyle($objRef,$strValue,$arrConf[$strKey.'.'],'setFillStyle');
				break;
				case 'fillColor':
					$objRef->setFillColor($strValue);
				break;
				case 'font';
					tx_pbimagegraph_ts::setFont($objRef,$arrConf[$strKey.'.']);
				break;
				case 'padding':
					$objRef->setPadding($strValue);
				break;
			}
		}
	}
	
	/**
	 * Set the properties for all plot elements
	 *
	 * @param	object		The parent cObject
	 * @param	array		The array with TypoScript properties for the object
	 */
	function setPlotProperties(&$objRef,$arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			$strKey = rtrim($strKey, '.');
			switch($strKey) {
				// element
				case 'title':
					$objRef->setTitle($strValue);
					break;
				case 'dataSelector':
					tx_pbimagegraph_ts::setDataSelector($objRef,$strValue,$arrConf[$strKey.'.']);
					break;
			}
		}
	}
	
	/**
	 * Set the properties for the Axis
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the Axis
	 * @param	integer		Level
	 */
	function setAxisProperties(&$objRef,$arrConf,$intLevel=1) {
		foreach($arrConf as $strKey => $strValue) {
			if (strpos($strKey,'.') === false || (strpos($strKey,'.') !== false && !isset($arrConf[rtrim($strKey, '.')]))) {
				$strKey = rtrim($strKey, '.');
				switch($strKey) {
					case 'level':
						tx_pbimagegraph_ts::getAxisLevel($objRef,$strValue);
					break;
					case 'label':
						$intLabel = IMAGE_GRAPH_LABEL_ZERO;
						eval("\$intLabel = IMAGE_GRAPH_LABEL_".strtoupper($strValue).";");
						$objRef->showLabel($intLabel);  
					break;
					case 'dataPreProcessor':
						tx_pbimagegraph_ts::setDataPreprocessor($objRef,$strValue,$arrConf['dataPreProcessor.']);
					break;
					case 'forceMinimum':
						$objRef->forceMinimum($strValue);
					break;
					case 'forceMaximum':
						$objRef->forceMaximum($strValue);
					break;
					case 'showArrow':
						$objRef->showArrow();
					break;
					case 'hideArrow':
						$objRef->hideArrow();
					break;
					case 'labelInterval':
						tx_pbimagegraph_ts::setLabelInterval($objRef,$strValue,$intLevel);
					break;
					case 'labelOption':
						//$objRef->setLabelOption($option, $value, $level = 1);
					break;
					case 'labelOptions':
						tx_pbimagegraph_ts::setLabelOptions($objRef,$strValue,$intLevel);
					break;
					case 'title':
						$objRef->setTitle($strValue, $arrConf['title.']);
					break;
					case 'fixedSize':
						$objRef->setFixedSize($strValue);
					break;
					case 'addMark':
						tx_pbimagegraph_ts::axisAddMark($objRef,$strValue);
					break;
					case 'tickOptions':
						$objRef->setTickOptions($strValue['start'], $strValue['end'], $intLevel);
					break;
					case 'inverted':
						$objRef->setInverted($strValue);
					break;
					case 'axisIntersection':
						$objRef->setAxisIntersection($strValue);
					break;
				}
			}
		}		
	}
	
	/**
	 * Set the properties for the Axis Marker Area
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the Axis Marker Area
	 */
	function setAxisMarkerAreaProperties(&$objRef,$arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			$strKey = rtrim($strKey, '.');
			switch($strKey) {
				case 'lowerBound':
					$objRef->setLowerBound($strValue); 
				break;
				case 'upperBound':
					$objRef->setUpperBound($strValue); 
				break;
			}
		}
	}
	
	/**
	 * Set the properties for the Axis Marker Line
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the Axis Marker Line
	 */
	function setAxisMarkerLineProperties(&$objRef,$arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			$strKey = rtrim($strKey, '.');
			switch($strKey) {
				case 'value':
					$objRef->setValue($strValue); 
				break;
			}
		}
	}
	
	/**
	 * Set the properties for a marker
	 *
	 * @param	object		The parent cObject
	 * @param	array		The array with TypoScript properties for the object
	 */
	function setMarkerProperties(&$objRef,$arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			switch($strKey) {
				case 'size':
					$objRef->setSize($strValue);
					break;
				case 'secondaryMarker':
					//$objRef->setSecondaryMarker(& $secondaryMarker);
					break;
				case 'maxRadius':
					$objRef->setMaxRadius($strValue);
					break;
				case 'pointX':
					$objRef->setPointX($strValue);
					break;
				case 'pointY':
					$objRef->setPointY($strValue);
					break;
				case 'markerStart':
					//$objRef->setMarkerStart(& $markerStart);
					break;
				case 'dataPreProcessor':
					tx_pbimagegraph_ts::setDataPreprocessor($objRef,$strValue,$arrConf['dataPreProcessor.']);
					break;
			}
		}	
	}
	
	/**
	 * Sets the properties for the legend
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the legend
	 */
	function setLegendProperties(&$objRef,$arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			$strKey = rtrim($strKey, '.');
			switch($strKey) {
				case 'alignment':
					tx_pbimagegraph_ts::setAlignment($objRef,$strValue);
				break;
				case 'plotarea':
					tx_pbimagegraph_ts::setPlotarea($objRef,$strValue,$arrConf[$strKey.'.']);
				break;
				case 'showMarker':
					$objRef->setShowMarker($strValue);
				break;
			}
		}	
	}
	
	/**
	 * Sets the properties for the title
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the title
	 */
	function setTitleProperties(&$objRef,$arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			$strKey = rtrim($strKey, '.');
			switch($strKey) {
				case 'alignment':
					tx_pbimagegraph_ts::setAlignment($objRef,$strValue);
				break;
				case 'text':
					$objRef->setText($strValue);
				break;
			}
		}
	}
	
	/**
	 * Set the properties for cObject BAR
	 *
	 * @param	object		The parent BAR cObject
	 * @param	array		The array with TypoScript properties for the object
	 */
	function setBarProperties(&$objRef,$arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			switch($strKey) {
				case 'spacing':
					$objRef->setSpacing($strValue);
					break;
				case 'barWidth.':
					$strUnit = $strValue['unit']?$strValue['unit']:false;
					$objRef->setBarWidth($strValue['value'], $strUnit);
					break;
			}
		}
	}
	
	/**
	 * Set the properties for cObject BOXWHISKER
	 *
	 * @param	object		The parent BOXWHISKER cObject
	 * @param	array		The array with TypoScript properties for the object
	 */
	function setBoxWhiskerProperties(&$objRef,$arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			switch($strKey) {
				case 'whiskerSize':
					$strSize = $strValue?$strValue:false;
					$objRef->setWhiskerSize($strSize);
					break;
			}
		}
	}
	
	/**
	 * Set the properties for cObject PIE
	 *
	 * @param	object		The parent PIE cObject
	 * @param	array		The array with TypoScript properties for the object
	 */
	function setPieProperties(&$objRef,$arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			switch($strKey) {
				case 'explode.':
					$objRef->explode($strValue['radius'], isset($strValue['id'])?$strValue['id']:false);
					break;
				case 'startingAngle.':
					$strDirection = $strValue['direction']?$strValue['direction']:'ccw';
					$objRef->setStartingAngle($strValue['angle'], $strDirection);
					break;
				case 'diameter':
					$objRef->setDiameter($strValue);
					break;
			}
		}
	}
	
	/**
	 * Set the properties for cObject ODO
	 *
	 * @param	object		The parent ODO cObject
	 * @param	array		The array with TypoScript properties for the object
	 */
	function setOdoProperties(&$objRef,$arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			switch($strKey) {
				case 'center.':
					$objRef->setCenter($strValue['x'], $strValue['y']);
					break;
				case 'range.':
					$objRef->setRange($strValue['min'], $strValue['max']);
					break;
				case 'angles.':
					$objRef->setAngles($strValue['offset'], $strValue['width']);
					break;
				case 'radiusWidth':
					$objRef->setRadiusWidth($strValue);
					break;
				case 'arrowSize.':
					$objRef->setArrowSize($strValue['length'], $strValue['width']);
					break;
				case 'arrowMarker.':
					$intAxis = 0;
					eval("\$intAxis = IMAGE_GRAPH_".strtoupper($strValue['useValue']).";");
					$objMarker =& $objRef->addNew('tx_pbimagegraph_Marker_Value', $intAxis);
					$objRef->setArrowMarker(&$objMarker);
					tx_pbimagegraph_ts::setElementProperties($objMarker,$strValue);
					tx_pbimagegraph_ts::setMarkerProperties($objMarker,$strValue);
					break;	
				case 'tickLength':
					$objRef->setTickLength($strValue);
					break;
				case 'axisTicks':
					$objRef->setAxisTicks($strValue);
					break;
				case 'arrowLineStyle':
					tx_pbimagegraph_ts::setLineStyle($objRef,$strValue,$arrConf[$strKey.'.'],'setArrowLineStyle');
					break;
				case 'arrowFillStyle':
					tx_pbimagegraph_ts::setFillStyle($objRef,$strValue,$arrConf[$strKey.'.'],'setArrowFillStyle');
					break;
				case 'rangeMarker.':
					tx_pbimagegraph_ts::setRangeMarker($objRef,$strValue);
					break;
				case 'rangeMarkerFillStyle':
					tx_pbimagegraph_ts::setFillStyle($objRef,$strValue,$arrConf[$strKey.'.'],'setRangeMarkerFillStyle');
					break;
			}
		}
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/class.tx_pbimagegraph_ts.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/class.tx_pbimagegraph_ts.php']);
}
?>