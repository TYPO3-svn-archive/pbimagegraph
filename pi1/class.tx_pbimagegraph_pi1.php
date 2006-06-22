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
//require_once(PATH_t3lib.'class.t3lib_stdGraphic.php');
require_once(PATH_site.t3lib_extMgm::siteRelPath("pbimagegraph").'Image/class.tx_pbimagegraph.php');
require_once(PATH_site.t3lib_extMgm::siteRelPath("pbimagegraph").'Image/class.tx_pbimagegraph_canvas.php');

/**
 * Frontend Module for the 'pbimagegraph' extension.
 *
 * @author Patrick Broens <patrick@patrickbroens.nl>
 * @package TYPO3
 * @subpackage pbsurvey
 */
class tx_pbimagegraph_pi1 extends tslib_pibase {

	var $tempPath = 'typo3temp/'; // Path to the temporary directory
	var $combinedTextStrings = array(); // Contains all text strings used on this image
	
	function main($strContent,$arrConf)	{
		if ($arrConf) {
			$strFileName = self::fileName('ImageGraph/',$arrConf,$arrConf['factory']);
			if (!@file_exists($strFileName)) {
				t3lib_stdGraphic::createTempSubDir('ImageGraph/');
				self::make($arrConf);
				$this->Graph->done(array('filename' => $strFileName));
			}
			$strOutput = '<img src="'.$strFileName.'" alt="" title="" />';
			return $strOutput;
		}
		return $this->pi_wrapInBaseClass($strOutput);
	}
	
	function make($arrConf) {
		$strFactory = $arrConf['factory'];
    	$arrParams['width'] = $arrConf['width'];
    	$arrParams['height'] = $arrConf['height'];
    	$arrParams['antialias'] = $arrConf['antialias'];
		$Canvas =& tx_pbimagegraph_Canvas::factory($strFactory, $arrParams);    
		$this->Graph =& tx_pbimagegraph::factory('graph', $Canvas);
		self::setElementProperties($this->Graph,$arrConf);
		$objLayout = self::cObjGet($arrConf);
		$this->Graph->add($objLayout);
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
		return $this->tempPath.$strPre.t3lib_div::shortMD5(serialize($arrConf)).'.'.$strExtension;
	}

	/**
	 * Rendering of a "numerical array" of cObjects from TypoScript
	 * Will call ->cObjGetSingle() for each cObject found and accumulate the output.
	 *
	 * @param	array		Array with cObjects as values.
	 * @param	object		Reference object.
	 * @return	object		The object.
	 */	

	function cObjGet($arrSetup,&$objRef='') {
		if (is_array($arrSetup)) {
			$arrSortedKeys=t3lib_TStemplate::sortedKeyList($arrSetup);
			foreach($arrSortedKeys as $strKey) {
				$strCobjName=$arrSetup[$strKey];
				if (intval($strKey) && !strstr($strKey,'.')) {
					$arrConf=$arrSetup[$strKey.'.'];
					$objOutput = self::cObjGetSingle($strCobjName,$arrConf,$objRef);
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
	function cObjGetSingle($strCobjName,$arrConf,&$objRef='')	{
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
				$objOutput =& self::cObjGetSingle($strCobjName,$arrConf);
				$GLOBALS['TT']->decStackPointer();
			} else {	
				switch($strCobjName) {
					case 'PLOTAREA':
						$objOutput =& self::PLOTAREA($arrConf);
					break;
					// Types of charts
					case 'LINE':
						self::LINE($objRef,$arrConf);
					break;
					case 'AREA':
						self::AREA($objRef,$arrConf);
					break;
					case 'BAR':
						self::BAR($objRef,$arrConf);
					break;
					case 'SMOOTH_LINE':
						self::SMOOTH_LINE($objRef,$arrConf);
					break;
					case 'SMOOTH_AREA':
						self::SMOOTH_AREA($objRef,$arrConf);
					break;
					case 'PIE':
						self::PIE($objRef,$arrConf);
					break;
					case 'RADAR':
						self::RADAR($objRef,$arrConf);
					break;
					case 'STEP':
						self::STEP($objRef,$arrConf);
					break;
					case 'IMPULSE':
						self::IMPULSE($objRef,$arrConf);
					break;
					case 'DOT':
						self::DOT($objRef,$arrConf);
					break;
					case 'SCATTER':
						self::SCATTER($objRef,$arrConf);
					break;
					// Data
					case 'DATASET':
						$objOutput = self::DATASET($arrConf);
					break;
					case 'RANDOM':
						$objOutput = self::RANDOM($arrConf);
					break;
					case 'FUNCTION':
						$objOutput = self::FUNCTIO($arrConf);
					break;
					case 'VECTOR':
						$objOutput = self::VECTOR($arrConf);
					break;
					// Axis
					case 'CATEGORY':
						$objOutput = self::CATEGORY($arrConf);
					break;
					case 'AXIS':
						$objOutput = self::AXIS($arrConf);
					break;
					case 'AXIS_LOG':
						$objOutput = self::AXIS_LOG($arrConf);
					break;
					// Title
					case 'TITLE':
						$objOutput = self::TITLE($arrConf);
					break;
					// Grids
					case 'GRID':
						self::GRID($objRef,$arrConf);
					break;
					// Various
					case 'LEGEND':
						$objOutput = self::LEGEND($objRef,$arrConf);
					break;
					// Layout
					case 'VERTICAL':
					case 'HORIZONTAL':
						$objOutput = self::VERT_HOR($this->Graph,$arrConf,$strCobjName);
					break;
				}
			}
		}
			// Increasing on exit...
		$GLOBALS['TSFE']->cObjectDepthCounter++;
		return $objOutput;
	}
	
	function PLOTAREA($arrConf) {
		$id = $arrConf['id'];
		$strAxisX = $arrConf['axis.']['x.']['type']?'tx_pbimagegraph_Axis_'.ucfirst($arrConf['axis.']['x.']['type']):'tx_pbimagegraph_Axis_Category';
		$strAxisY = $arrConf['axis.']['y.']['type']?'tx_pbimagegraph_Axis_'.ucfirst($arrConf['axis.']['y.']['type']):'tx_pbimagegraph_Axis';
		$strDirection = $arrConf['direction']?$arrConf['direction']:'vertical';
		$Plotarea = tx_pbimagegraph::factory('plotarea',array($strAxisX,$strAxisY,$strDirection));
		self::setElementProperties($Plotarea,$arrConf);
		self::setPlotareaProperties($Plotarea,$arrConf);
		self::cObjGet($arrConf,$Plotarea);
		if ($id) {
			eval("\$this->".$id." =& \$Plotarea;");
		}
		return $Plotarea;
	}
	
	function LINE(&$objRef,$arrConf) {
		$arrDatasets = self::setDatasets($arrConf['dataset.']);	
		$objLine =& $objRef->addNew('line', $arrDatasets[0]);
		self::setElementProperties($objLine,$arrConf);
		self::setMarker($objLine,$arrConf);
	}
	
	function AREA(&$objRef,$arrConf) {
		$strPlotType = $arrConf['plottype']; // normal, stacked, stacked100pct	
		$arrDatasets = self::setDatasets($arrConf['dataset.']);	
		$objArea =& $objRef->addNew('area', array($arrDatasets,$strPlotType));
		self::setElementProperties($objArea,$arrConf);
		self::setMarker($objArea,$arrConf);
	}
	
	function BAR(&$objRef,$arrConf) {
		$strPlotType = $arrConf['plottype']; // normal, stacked, stacked100pct
		$arrDatasets = self::setDatasets($arrConf['dataset.']);	
		$objBar =& $objRef->add(tx_pbimagegraph::factory('bar',array($arrDatasets,$strPlotType)));
		self::setElementProperties($objBar,$arrConf);
		self::setMarker($objBar,$arrConf);
	}
	
	function SMOOTH_AREA(&$objRef,$arrConf) {
		$arrDatasets = self::setDatasets($arrConf['dataset.']);
		$objSmoothArea =& $objRef->addNew('smooth_area', array(&$arrDatasets));
		self::setElementProperties($objSmoothArea,$arrConf);
		self::setMarker($objSmoothArea,$arrConf);
	}
	
	function PIE(&$objRef,$arrConf) {
		$arrDatasets = self::setDatasets($arrConf['dataset.']);
		$objPie =& $objRef->addNew('pie', array(&$arrDatasets));
		self::setElementProperties($objPie,$arrConf);
	}
	
	function SCATTER(&$objRef,$arrConf) {
		$arrDatasets = self::setDatasets($arrConf['dataset.']);	
		$objScatter =& $objRef->add(tx_pbimagegraph::factory('scatter',array($arrDatasets[0])));
		self::setElementProperties($objScatter,$arrConf);
		self::setMarker($objScatter,$arrConf);
	}
	
	function VERT_HOR(&$Graph,$arrConf,$strCobjName) {
		$percentage = $arrConf['percentage'];
		$Vert_Hor = '';
		$cObjCount = 1;	
		if (is_array($arrConf)) {
			$sKeyArray=t3lib_TStemplate::sortedKeyList($arrConf);
			foreach($sKeyArray as $theKey) {		
				$theValue=$arrConf[$theKey];
				if (intval($theKey) && !strstr($theKey,'.')) {
					$conf=$arrConf[$theKey.'.'];
					if ($cObjCount == 1) {
						$objTopLeft = self::cObjGetSingle($theValue,$conf);
						$cObjCount++;
					} elseif ($cObjCount == 2) {
						$objBottomRight = self::cObjGetSingle($theValue,$conf);
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
	
	function TITLE($arrConf) {
		$intSize = $arrConf['size'];
		$intAngle = $arrConf['angle'];
		$strColor = $arrConf['color'];
		$Title = tx_pbimagegraph::factory('title', array('Title', array('size' => $intSize, 'angle' => $intAngle, 'color' => $strColor)));
		self::setElementProperties($Title,$arrConf);
		self::setTitleProperties($Title,$arrConf);
		return $Title;
	}
	
	function GRID(&$objRef,$arrConf) {
		$strType = $arrConf['type'].'_grid';
		$strAxis = $arrConf['axis'];
		$intAxis = 1;
		eval("\$intAxis = IMAGE_GRAPH_AXIS_".strtoupper($strAxis).";");
		$Grid =& $objRef->addNew($strType, $intAxis);
		self::setElementProperties($Grid,$arrConf);
	}
	
	function LEGEND(&$objRef='',$arrConf) {
		if ($objRef) {
			$Legend =& $objRef->addNew('legend');
		} else {
			$Legend = tx_pbimagegraph::factory('legend');
		}
		self::setElementProperties($Legend,$arrConf);
		self::setLegendProperties($Legend,$arrConf);
		return $Legend;		
	}
	
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
							self::datasetTrivial($objDatasets[$intCount],$arrConf[$strKey.'.']);
						break;
						case 'random':
							$objDatasets[$intCount] = self::datasetRandom($arrConf[$strKey.'.']);
						break;
					}
					$intCount++;
				}
			}
		}
		return $objDatasets;
	}
	
	function datasetTrivial(&$objRef,$arrConf) {
		if (is_array($arrConf)) {
			$arrKeys=t3lib_TStemplate::sortedKeyList($arrConf);
			foreach($arrKeys as $strKey) {		
				$strValue=$arrConf[$strKey];
				if (intval($strKey) && !strstr($strKey,'.')) {
					if($strValue=='point') {
						$mixX = $arrConf[$strKey.'.']['x'];
						$mixY = $arrConf[$strKey.'.']['y'];
						$strId = $arrConf[$strKey.'.']['id'];
						$objRef->addPoint($mixX, $mixY, $strId);
					}
				}
			}
		}
	}
	
	/**
	 * Create a random dataset
	 *
	 * @param	array		Array of TypoScript properties
	 * @return	obj		    Single dataset
	 */		
	function datasetRandom($arrConf) {
		$intCount = $arrConf['count'];
		$intMinimum = $arrConf['minimum'];
		$intMaximum = $arrConf['maximum'];
		$boolIncludeZero = $arrConf['includeZero'];
		$strName = $arrConf['name'];
		$objRandom = tx_pbimagegraph::factory('random', array($intCount, $intMinimum, $intMaximum, $boolIncludeZero));
		$objRandom->setName($strName);
		return $objRandom;
	}
	
	function setMarker(&$objRef,$arrConf) {
		if ($arrConf['marker']) {
			switch($arrConf['marker']) {
				case 'value':
					$intAxis = 0;
					eval("\$intAxis = IMAGE_GRAPH_".strtoupper($arrConf['marker.']['useValue']).";");
					$objMarker =& $objRef->addNew('tx_pbimagegraph_Marker_'.ucfirst($arrConf['marker']),$intAxis);
				break;
				default:
					$objMarker =& $objRef->addNew('tx_pbimagegraph_Marker_'.ucfirst($arrConf['marker']));
			}
			self::setMarkerProperties($objMarker,$arrConf['marker.']);
			self::setElementProperties($objMarker,$arrConf['marker.']);
			if ($arrConf['marker.']['pointing']) {
				$objPointing =& $objRef->addNew('tx_pbimagegraph_Marker_Pointing_'.ucfirst($arrConf['marker.']['pointing']), array($arrConf['marker.']['pointing.']['radius'], $objMarker));		
				$objSetMarker =& $objPointing;
			} else {
				$objSetMarker =& $objMarker;
			}

			$objRef->setMarker($objSetMarker);
		}
	}
	
	function setMarkerProperties(&$objRef,$arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			switch($strKey) {
				// element
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
					self::setDataPreprocessor($objRef,$strValue,$arrConf['dataPreProcessor.']);
				break;
			}
		}	
	}
	
	function setDataPreprocessor(&$objRef,$strType,$arrConf) {
		switch($strType) {
			case 'array':
			
			break;
			default:
				$objRef->setDataPreProcessor(tx_pbimagegraph::factory('tx_pbimagegraph_DataPreprocessor_'.ucfirst($strType), $arrConf['format']));
		}
	}
	
	function setElementProperties(&$objRef,$arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			$strKey = rtrim($strKey, '.');
			switch($strKey) {
				// element
				case 'background':
					self::setBackground($objRef,$strValue,$arrConf[$strKey.'.']);
				break;
				case 'shadow':
					self::showShadow($objRef,$arrConf[$strKey.'.']);
				break;
				case 'borderStyle':
					// setBorderStyle(& $borderStyle)
				break;
				case 'borderColor':
					$objRef->setBorderColor($strValue);
				break;
				case 'lineStyle':
					// setLineStyle(& $lineStyle)
				break;
				case 'lineColor':
					$objRef->setLineColor($strValue);
				break;
				case 'fillStyle':
					self::setFillStyle($objRef,$strValue,$arrConf[$strKey.'.']);
				break;
				case 'fillColor':
					$objRef->setFillColor($strValue);
				break;
				case 'font';
					self::setFont($objRef,$strValue,$arrConf[$strKey.'.']);
				break;
				case 'padding':
					$objRef->setPadding($strValue);
				break;
			}
		}
	}

	/**
	 * Create a gradient background
	 *
	 * @param	object 		Reference object
	 * @param	string		Type of background
	 * @param	array		Configuration of the background
	 * 
	 * Gradient direction values:
	 * 1: Horizontally	
	 * 2: Vertically
	 * 3: Mirrored horizontally (the color grades from a-b-a horizontally)
	 * 4: Mirrored vertically (the color grades from a- b-a vertically)
	 * 5: Diagonally from top-left to right-bottom
	 * 6: Diagonally from bottom-left to top-right
	 * 7: Radially (concentric circles in the center)
	 */	
	function setBackground(&$objRef,$strValue,$arrConf) {
		$intDirection = IMAGE_GRAPH_GRAD_HORIZONTAL;
		eval("\$intDirection = IMAGE_GRAPH_GRAD_".strtoupper($arrConf['direction']).";");
		$strStartColor = $arrConf['startColor'];
		$strEndColor = $arrConf['endColor'];
		$intSolidColor = $arrConf['color'];
		if ($strValue=='gradient') {
			$objRef->setBackground(tx_pbimagegraph::factory('gradient', array($intDirection, $strStartColor, $strEndColor)));
		} elseif ($strValue=='solid') {
			$objRef->setBackgroundColor($intSolidColor);
		}
	}

	/**
	 * Sets the fill style of an element
	 *
	 * @param	object 		Reference object
	 * @param	string		Type of fill style
	 * @param	array		Configuration of the fill style
	 */	
	function setFillStyle(&$objRef,$strValue,$arrConf) {
		$intDirection = IMAGE_GRAPH_GRAD_HORIZONTAL;
		eval("\$intDirection = IMAGE_GRAPH_GRAD_".strtoupper($arrConf['direction']).";");
		$strStartColor = $arrConf['startColor'];
		$strEndColor = $arrConf['endColor'];
		$intSolidColor = $arrConf['color'];
		$strImage = $arrConf['image'];
		if ($strValue=='gradient') {
			$objRef->setFillStyle(tx_pbimagegraph::factory('gradient', array($intDirection, $strStartColor, $strEndColor)));
		} elseif ($strValue=='fill_array') {
			$objFillArray =& tx_pbimagegraph::factory('tx_pbimagegraph_Fill_Array');
			if (is_array($arrConf)) {
				$arrKeys=t3lib_TStemplate::sortedKeyList($arrConf);
				foreach($arrKeys as $strKey) {		
					$strType=$arrConf[$strKey];
					if (intval($strKey) && !strstr($strKey,'.')) {
						switch($strType) {
							case 'addColor':
								$strColor = $arrConf[$strKey.'.']['color'];
								$objFillArray->addColor($strColor);
							break;
							case 'gradient':
								eval("\$intDirection = IMAGE_GRAPH_GRAD_".strtoupper($arrConf[$strKey.'.']['direction']).";");
								$strStartColor = $arrConf[$strKey.'.']['startColor'];
								$strEndColor = $arrConf[$strKey.'.']['endColor'];
								$intSolidColor = $arrConf[$strKey.'.']['color'];
								$strId = $arrConf[$strKey.'.']['id'];
								$objFillArray->addNew('gradient', array($intDirection, $strStartColor, $strEndColor), $strId);
							break;
						}
					}
				}
			}	
			$objRef->setFillStyle($objFillArray);
		} elseif ($strValue=='image') {
			$objImage =& Image_Graph::factory('Image_Graph_Fill_Image',$strImage);
			$objRef->setFillStyle($objImage);	
		}
	}

	/**
	 * Shows shadow of the element
	 *
	 * @param	object 		Reference object
	 * @param	array		Configuration of the shadow
	 */		
	function showShadow(&$objRef,$arrConf) {
		$strColor = $arrConf['color'];
		$intSize = $arrConf['size'];
		$strTransparency = $arrConf['transparency'];
		$objRef->showShadow($strColor.'@'.$strTransparency,$intSize);
	}
		
	function setFont(&$objRef,$strValue,$arrConf) {
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
	
	function setAlignment(&$objRef,$strValue) {
		$strAlignment = IMAGE_GRAPH_ALIGN_CENTER_X;
		eval("\$strAlignment = IMAGE_GRAPH_ALIGN_".strtoupper($strValue).";");
		$objRef->setAlignment($strAlignment);
	}
	
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
	
	function setPlotareaProperties(&$objRef,$arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			$strKey = rtrim($strKey, '.');
			switch($strKey) {
				case 'axis':
					self::getAxis($objRef,$strValue) ;  
				break;
				case 'hideAxis':
					$objRef->hideAxis($strValue);
				break;
				case 'clearAxis':
					$objRef->clearAxis();
				break;
				case 'axisPadding':
					//$objRef->setAxisPadding($value, $position = false);
				break;
			}
		}
	}
	
	function getAxis(&$objRef,$arrConf) {
		$intAxis = 1;
		foreach($arrConf as $strKey => $strValue) {
			$strKey = rtrim($strKey, '.');
			eval("\$intAxis = IMAGE_GRAPH_AXIS_".strtoupper($strKey).";");
			$objAxis =& $objRef->getAxis($intAxis);
			self::setAxisProperties($objAxis,$strValue);
			self::setElementProperties($objAxis,$strValue);
			if (is_array($strValue)) {	
				$arrKeys=t3lib_TStemplate::sortedKeyList($strValue);
				foreach($arrKeys as $strKey) {	
					$strCobjName=$strValue[$strKey];
					if (intval($strKey) && !strstr($strKey,'.')) {
						$arrConfAxis=$strValue[$strKey.'.'];
						switch($strCobjName) {
							case 'marker':
								self::setAxisMarker($objRef,$intAxis,$arrConfAxis);
							break;
						}
					}
				}
			}
		}   
	}
	
	function setAxisMarker(&$objRef,$intAxis,$arrConf) {
		$strType = $arrConf['type'];
		$Marker =& $objRef->addNew('tx_pbimagegraph_Axis_Marker_'.ucfirst($strType), null, $intAxis);
		self::setElementProperties($Marker,$arrConf);
		switch($strType) {
			case 'area':
				self::setAxisMarkerAreaProperties($Marker,$arrConf);
			break;
			case 'line':
				self::setAxisMarkerLineProperties($Marker,$arrConf);
			break;
		}		
	}
	
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
	
	function setAxisProperties(&$objRef,$arrConf,$intLevel=1) {
		foreach($arrConf as $strKey => $strValue) {
			$strKey = rtrim($strKey, '.');
			switch($strKey) {
				case 'level':
					self::getAxisLevel($objRef,$strValue);
				break;
				case 'label':
					$objRef->showLabel($strValue);  
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
					self::setLabelInterval($objRef,$strValue,$intLevel);
				break;
				case 'labelOption':
					//$objRef->setLabelOption($option, $value, $level = 1);
				break;
				case 'labelOptions':
					self::setLabelOptions($objRef,$strValue,$intLevel);
				break;
				case 'title':
					//$objRef->setTitle($title, $font = false);
				break;
				case 'fixedSize':
					$objRef->setFixedSize($strValue);
				break;
				case 'addMark':
					self::axisAddMark($objRef,$strValue);
				break;
				case 'tickOptions':
					$objRef->setTickOptions($strValue['start'], $strValue['end'], $intLevel);
				break;
				case 'inverted':
					$objRef->setInverted($strValue);
				break;
				case '':
					//$objRef->setAxisIntersection($intersection, $axis = 'default');
				break;
			}
		}		
	}
	
	/**
	 * 
	 * 'showtext' true or false
	 * 'showoffset' true or false
	 * 'font' The font options as an associated array
	 * 'position' 'inside' or 'outside'
	 * 'format' To format the label text according to a sprintf statement
	 * 'dateformat' To format the label as a date, fx. j. M Y = 29. Jun 2005
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
	
	function getAxisLevel(&$objRef,$arrConf) {
		if (is_array($arrConf)) {
			foreach ($arrConf as $strKey => $strValue) {
				if (intval(rtrim($strKey, '.'))) {
					self::setAxisProperties(&$objRef,$strValue,intval(rtrim($strKey, '.')));
				}
			}
		}
	}
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
	
	function setLegendProperties(&$objRef,$arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			$strKey = rtrim($strKey, '.');
			switch($strKey) {
				case 'alignment':
					self::setAlignment($objRef,$strValue);
				break;
				case 'plotarea':
					self::setPlotarea($objRef,$strValue,$arrConf[$strKey.'.']);
				break;
				case 'showMarker':
					$objRef->setShowMarker($strValue);
				break;
			}
		}	
	}
	
	function setCanvasProperties(&$objRef,$arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			$strKey = rtrim($strKey, '.');
			switch($strKey) {
				case 'lineThickness':
					//self::setLineThickness($thickness)
				break;
				case 'fillImage':
					//self::setFillImage($filename)
				break;
				case 'gradientFill':
					//self::setGradientFill($gradient)
				break;
				// title
				case 'alignment':
					self::setAlignment($objRef,$strValue);
				break;
				case 'text':
					$objRef->setText($strValue);
				break;
			}
		}
	}
	
	function setTitleProperties(&$objRef,$arrConf) {
		foreach($arrConf as $strKey => $strValue) {
			$strKey = rtrim($strKey, '.');
			switch($strKey) {
				case 'alignment':
					self::setAlignment($objRef,$strValue);
				break;
				case 'text':
					$objRef->setText($strValue);
				break;
			}
		}
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/pi1/class.tx_pbimagegraph_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/pi1/class.tx_pbimagegraph_pi1.php']);
}
?>