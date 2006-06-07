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

	function main($strContent,$arrConf)	{
		self::cObjGet($arrConf);
		t3lib_div::unlink_tempfile(PATH_site . 'typo3temp/' . 'antialias.png');
		$this->Graph->done(array('filename' => PATH_site . 'typo3temp/' . 'antialias.png'));
		$strOutput = '<img src="typo3temp/' . 'antialias.png' . '" alt="" title="" />';
		return $this->pi_wrapInBaseClass($strOutput);
	}

	/**
	 * Rendering of a "numerical array" of cObjects from TypoScript
	 * Will call ->cObjGetSingle() for each cObject found and accumulate the output.
	 *
	 * @param	array		$setup: Array with cObjects as values.
	 * @return	string		Rendered output from the cObjects in the array.
	 * @see cObjGetSingle()
	 */	
// Probleem is hier dat de functie wel alles doorloopt, maar ook regelmatig moet retourneren. Hoe gaan we dat doen?	 
	function cObjGet($setup,&$refObj='') {
		if (is_array($setup)) {
			$sKeyArray=t3lib_TStemplate::sortedKeyList($setup);
			foreach($sKeyArray as $theKey) {
				$theValue=$setup[$theKey];
				if (intval($theKey) && !strstr($theKey,'.')) {
					$conf=$setup[$theKey.'.'];
					$obj = self::cObjGetSingle($theValue,$conf,$refObj);
				}
			}
		}
		return $obj;
	}
	
	/**
	 * Renders a content object
	 *
	 * @param	string		The content object name, eg. "TEXT" or "USER" or "IMAGE"
	 * @param	array		The array with TypoScript properties for the content object
	 * @return	string		cObject output
	 */
	function cObjGetSingle($name,$conf,&$refObj='')	{
			// Checking that the function is not called eternally. This is done by interrupting at a depth of 100
		$GLOBALS['TSFE']->cObjectDepthCounter--;
		if ($GLOBALS['TSFE']->cObjectDepthCounter>0)	{
			$name = trim($name);
				// Checking if the COBJ is a reference to another object. (eg. name of 'blabla.blabla = < styles.something')
			if (substr($name,0,1)=='<')	{
				$key = trim(substr($name,1));
				$cF = t3lib_div::makeInstance('t3lib_TSparser');
					// $name and $conf is loaded with the referenced values.
				$old_conf=$conf;
				list($name, $conf) = $cF->getVal($key,$GLOBALS['TSFE']->tmpl->setup);
				if (is_array($old_conf) && count($old_conf))	{
					$conf = $this->joinTSarrays($conf,$old_conf);
				}
				$GLOBALS['TT']->incStackPointer();
				$obj = self::cObjGetSingle($name,$conf);
				$GLOBALS['TT']->decStackPointer();
			} else {
				switch($name) {
					case 'CANVAS':
						self::CANVAS($conf);
					break;
					case 'LAYOUT':
						self::LAYOUT($this->Graph,$conf);
					break;
					case 'FONT':
						self::FONT($this->Graph,$conf);
					break;
					case 'VERTICAL':
						$obj = self::VERTICAL($this->Graph,$conf);
					break;
					case 'TITLE':
						$obj = self::TITLE($conf);
					break;
					case 'PLOTAREA':
						$obj = self::PLOTAREA($conf);
					break;
					case 'GRID':
						self::GRID($refObj);
					break;
					case 'AREA':
						self::AREA($refObj,$conf);
					break;
					case 'DATASET':
						$obj = self::DATASET($conf);
					break;
					case 'FILL_ARRAY':
						$obj = self::FILL_ARRAY($conf);
					break;
					case 'ADDCOLOR':
						self::ADDCOLOR($refObj,$conf);
					break;
					case 'RANDOM':
						$obj = self::RANDOM($conf);
					break;
					case 'LEGEND':
						$obj = self::LEGEND($refObj);
					break;
				}
			}
		}
			// Increasing on exit...
		$GLOBALS['TSFE']->cObjectDepthCounter++;
		return $obj;
	}

	function CANVAS($arrConf) {
		$Canvas =& tx_pbimagegraph_Canvas::factory('png', array('width' => 600, 'height' => 300, 'antialias' => 'native'));    
		$this->Graph =& tx_pbimagegraph::factory('graph', $Canvas);
		$this->Graph->setBackground(tx_pbimagegraph::factory('gradient', array(IMAGE_GRAPH_GRAD_VERTICAL_MIRRORED, 'steelblue', 'lightcyan')));
		//$this->Graph->setBackgroundColor('green@0.2');
		$this->Graph->setBorderColor('black');
		$this->Graph->setPadding(10);
		self::cObjGet($arrConf);
	}

	function LAYOUT(&$Graph,$arrConf) {
		$obj = self::cObjGet($arrConf);
		$Graph->add($obj);		
	}
		
	function FONT(&$Graph,$arrConf) {
		$Font =& $Graph->addNew('font', 'Verdana');
		$Font->setColor('black');
		$Font->setSize(8);
		$Font->setAngle(0);
		$Graph->setFont($Font);		
	}
	
	function VERTICAL(&$Graph,$arrConf) {
		$cObjCount = 1;	
		if (is_array($arrConf)) {
			$sKeyArray=t3lib_TStemplate::sortedKeyList($arrConf);
			foreach($sKeyArray as $theKey) {		
				$theValue=$arrConf[$theKey];
				if (intval($theKey) && !strstr($theKey,'.')) {
					$conf=$arrConf[$theKey.'.'];
					if ($cObjCount == 1) {
						$objTop = self::cObjGetSingle($theValue,$conf);
						$cObjCount++;
					} elseif ($cObjCount == 2) {
						$objBottom = self::cObjGetSingle($theValue,$conf);
						$cObjCount++;
					} else {
						break;
					}
				}
			}
		}
	    $Vertical = tx_pbimagegraph::vertical(
	    	$objTop,
			$objBottom,	    	
			30
	    );
	    return $Vertical;
	}
	
	function TITLE($arrConf) {
		$Title = tx_pbimagegraph::factory('title', array('Antialiased Sample Chart', array('size' => 30, 'angle' => 0, 'color' => '#FF00FF')));
		$Title->setAlignment(IMAGE_GRAPH_ALIGN_CENTER);
		$Title->setText('Die zou ik dus kunnen veranderen');
		$Title->setBackground(tx_pbimagegraph::factory('gradient', array(IMAGE_GRAPH_GRAD_DIAGONALLY_TL_BR, 'green', 'lightblue')));
//		$Title->setBackgroundColor('blue');
		$Title->setBorderColor('#FF0000');
//		$Title->setBorderStyle('dashed');
//		$Font2 =& $Graph->addNew('font', 'arial');
//		$Title->setFont($Font2);
//		$Title->setFontAngle(45);
		$Title->setFontColor('#0000FF');
		$Title->setFontSize(15);
//		$Title->setLineColor();
//		$Title->setLineStyle();
		$Title->setPadding(15);
		$Title->showShadow();
		return $Title;
	}
	
	function PLOTAREA($arrConf) {
		$id = $arrConf['id'];
		$Plotarea = tx_pbimagegraph::factory('plotarea');
		$Plotarea->setFillColor('silver@0.3');
		self::cObjGet($arrConf,$Plotarea);
		//eval($this->.'')
		return $Plotarea;
	}
	
	function GRID(&$Plotarea) {
		$Grid =& $Plotarea->addNew('line_grid', IMAGE_GRAPH_AXIS_Y);
		$Grid->setLineColor('silver');
	}
	function AREA(&$Plotarea,$arrConf) {
		if (is_array($arrConf)) {
			$sKeyArray=t3lib_TStemplate::sortedKeyList($arrConf);
			foreach($sKeyArray as $theKey) {		
				$theValue=$arrConf[$theKey];
				if (intval($theKey) && !strstr($theKey,'.')) {
					$conf=$arrConf[$theKey.'.'];
					if ($theValue=='DATASET') {
						$Datasets = self::cObjGetSingle($theValue,$conf);
					}
					if ($theValue=='FILL_ARRAY') {
						$FillArray = self::cObjGetSingle($theValue,$conf);
					}
				}
			}
		}			
//		$Plot =& $Plotarea->addNew('area', array($Datasets, 'stacked'));
		$Plot =& $Plotarea->addNew('area', array($Datasets));
		$Plot->setLineColor('gray');
		$Plot->setFillStyle($FillArray);
	}

	function DATASET($arrConf) {
		$cObjCount = 0;	
		if (is_array($arrConf)) {
			$sKeyArray=t3lib_TStemplate::sortedKeyList($arrConf);
			foreach($sKeyArray as $theKey) {		
				$theValue=$arrConf[$theKey];
				if (intval($theKey) && !strstr($theKey,'.')) {
					$conf=$arrConf[$theKey.'.']; 
					$Datasets[$cObjCount] = self::cObjGetSingle($theValue,$conf);
					$cObjCount++;
				}
			}
		}
		return $Datasets;
	}
	
	function FILL_ARRAY($arrConf) {
		$FillArray =& tx_pbimagegraph::factory('tx_pbimagegraph_Fill_Array');
		self::cObjGet($arrConf,$FillArray);
		return $FillArray;
	}
	
	function ADDCOLOR(&$FillArray,$arrConf) {
		$color = $arrConf['color'];
		$transparency = $arrConf['transparency'];
		$FillArray->addColor($color.'@'.$transparency);
	}
	
	function RANDOM($arrConf) {
		$random = tx_pbimagegraph::factory('random', array(10, 2, 15, true));
		return $random;
	}
	
	function LEGEND(&$Plotarea) {
		$Legend = tx_pbimagegraph::factory('legend');
		$Legend->setPlotarea($Plotarea);
		return $Legend;		
	}

//	function main($strContent,$arrConf)	{
//		// create a PNG canvas and enable antialiasing (canvas implementation)
//		$Canvas =& tx_pbimagegraph_Canvas::factory('png', array('width' => 600, 'height' => 300, 'antialias' => 'native'));    
//		//debug($Canvas);
//		// create the graph
//		$Graph =& tx_pbimagegraph::factory('graph', $Canvas);
//		$Graph->showShadow();
//		// add a TrueType font
//		$Font =& $Graph->addNew('font', 'Verdana');
//		$Font->setColor('black');
//		$Font->setSize(8);
//		$Font->setAngle(0);
//		$Graph->setFont($Font);
//		
//		// create the layout
//		$Graph->add(
//		    tx_pbimagegraph::vertical(
//		    $Title = tx_pbimagegraph::factory('title', array('Antialiased Sample Chart', array('size' => 30, 'angle' => 0, 'color' => '#FF00FF'))),
////		    $Title = tx_pbimagegraph::factory('title', 'title'),
//
//		        tx_pbimagegraph::vertical(
//		            tx_pbimagegraph::horizontal(
//		                $Plotarea1 = tx_pbimagegraph::factory('plotarea'),
//		                $Plotarea2 = tx_pbimagegraph::factory('plotarea')
//		            ),
//		            $Legend = tx_pbimagegraph::factory('legend'),
//		            80
//		        ),
//		    30
//		    )
//		);
//		
////		// create the plotarea
////		$Graph->add(
////		    Image_Graph::vertical(
////		        Image_Graph::factory('title', array('Matrix Layout', 10)),               
////		        $Matrix = Image_Graph::factory('Image_Graph_Layout_Matrix', array(3, 3)),           
////		        5            
////		    )
////		);
//		$Title->setAlignment(IMAGE_GRAPH_ALIGN_LEFT);
//		$Title->setText('Die zou ik dus kunnen veranderen');
//		$Title->setBackground(tx_pbimagegraph::factory('gradient', array(IMAGE_GRAPH_GRAD_DIAGONALLY_TL_BR, 'green', 'lightblue')));
////		$Title->setBackgroundColor('blue');
//		$Title->setBorderColor('#FF0000');
////		$Title->setBorderStyle('dashed');
//		$Font2 =& $Graph->addNew('font', 'arial');
//		$Title->setFont($Font2);
////		$Title->setFontAngle(45);
//		$Title->setFontColor('#0000FF');
//		$Title->setFontSize(15);
////		$Title->setLineColor();
////		$Title->setLineStyle();
//		$Title->setPadding(15);
//		$Title->showShadow();
//	//debug($Title);
//		// add grids
//		$Grid =& $Plotarea1->addNew('line_grid', IMAGE_GRAPH_AXIS_Y);
////		$Grid->setBackground();
////		$Grid->setBackgroundColor('blue');
////		$Grid->setBorderColor();
////		$Grid->setBorderStyle();
////		$Grid->setFillColor();
////		$Grid->setFillStyle();
////		$Grid->setFont();
////		$Grid->setFontAngle(45);
////		$Grid->setFontColor('#FF000');
////		$Grid->setFontSize(6);
//		$Grid->setLineColor('silver');
////		$Grid->setLineStyle();
////		$Grid->setPadding();
////		$Grid->showShadow();
//		//debug($Grid);
//		
//		$Grid =& $Plotarea2->addNew('line_grid', IMAGE_GRAPH_AXIS_Y);
//		$Grid->setLineColor('silver');
//		
//		// setup legend
//		$Legend->setPlotarea($Plotarea1);
//		$Legend->setPlotarea($Plotarea2);
//		
//		// create the dataset
//		$Datasets =
//		    array(
//		        tx_pbimagegraph::factory('random', array(10, 2, 15, true)),
//		        tx_pbimagegraph::factory('random', array(10, 2, 15, true)),
//		        tx_pbimagegraph::factory('random', array(10, 2, 15, true))
//		    );
//		// create the plot as stacked area chart using the datasets
//		$Plot =& $Plotarea1->addNew('area', array($Datasets, 'stacked'));
//		
//		// set names for datasets (for legend)
//		$Datasets[0]->setName('Jylland');
//		$Datasets[1]->setName('Fyn');
//		$Datasets[2]->setName('Sjælland');
//		
//		// set line color for plot
//		$Plot->setLineColor('gray');
//		
//		// create and populate the fillarray
//		$FillArray =& tx_pbimagegraph::factory('tx_pbimagegraph_Fill_Array');
//		$FillArray->addColor('blue@0.2');
//		$FillArray->addColor('yellow@0.2');
//		$FillArray->addColor('green@0.2');
//		
//		// set a fill style
//		$Plot->setFillStyle($FillArray);
//		
//		// add other plots
//		$Plot =& $Plotarea2->addNew('line', $Datasets[0]);
//		$Plot->setLineColor('blue@0.2');
//		$Plot =& $Plotarea2->addNew('line', $Datasets[1]);
//		$Plot->setLineColor('yellow@0.2');
//		$Plot =& $Plotarea2->addNew('line', $Datasets[2]);
//		$Plot->setLineColor('green@0.2');
//		
//		// set color
//		$Plotarea1->setFillColor('silver@0.3');
//		$Plotarea2->setFillColor('silver@0.3');
//
//		// output the Graph
//		t3lib_div::unlink_tempfile(PATH_site . 'typo3temp/' . 'antialias.png');
//		$Graph->done(array('filename' => PATH_site . 'typo3temp/' . 'antialias.png'));
//		$strOutput = '<img src="typo3temp/' . 'antialias.png' . '" alt="" title="" />';
//		return $strOutput;
//	}



		
//		// create the graph
//		$Graph =& tx_pbimagegraph::factory('graph', array(600, 300));
//		
//		// add a TrueType font
//		$Font =& $Graph->addNew('font', 'Verdana');
//		// set the font size to 11 pixels
//		$Font->setSize(10);
//		
//		$Graph->setFont($Font);
//		
//		// setup the plotarea, legend and their layout
//		$Graph->add(
//		   tx_pbimagegraph::vertical(
//		      tx_pbimagegraph::factory('title', array('Changing Axis Direction', 12)),        
//		      tx_pbimagegraph::horizontal(
//		         $Plotarea1 = tx_pbimagegraph::factory('plotarea'),
//		         $Plotarea2 = tx_pbimagegraph::factory('plotarea'),
//		         50
//		      ),
//		      5
//		   )
//		);   
//		
//		$Dataset =& tx_pbimagegraph::factory('random', array(10, 2, 15, true));
//		$Plot1 =& $Plotarea1->addNew('line', array(&$Dataset));
//		$Plot1->setLineColor('red');                  
//		
//		$Plot2 =& $Plotarea2->addNew('line', array(&$Dataset));
//		$Plot2->setLineColor('red');
//		
//		$AxisY =& $Plotarea2->getAxis('y');
//		$AxisY->setInverted(true);                  
//		     
//		// output the Graph
//		t3lib_div::unlink_tempfile(PATH_site . 'typo3temp/' . 'axis_direction.png');
//		$Graph->done(array('filename' => PATH_site . 'typo3temp/' . 'axis_direction.png'));
//		$strOutput .= '<img src="typo3temp/' . 'axis_direction.png' . '" alt="" title="" />';
//		return $strOutput;
//	}
	
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/pi1/class.tx_pbimagegraph_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/pi1/class.tx_pbimagegraph_pi1.php']);
}
?>