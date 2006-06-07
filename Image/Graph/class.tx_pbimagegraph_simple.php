<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * tx_pbimagegraph - Main class for the graph creation.
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
 * @author     Jesper Veggerby <pear.nosey@veggerby.dk>
 * @copyright  Copyright (C) 2003, 2004 Jesper Veggerby Hansen
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/tx_pbimagegraph
 */

/**
 * Include file Image/Graph.php
 */
require_once(PATH_site.t3lib_extMgm::siteRelPath("pbimagegraph").'Image/class.tx_pbimagegraph.php');

/**
 * Class for simple creation of graphs
 *
 * @category   Images
 * @package    tx_pbimagegraph
 * @author     Jesper Veggerby <pear.nosey@veggerby.dk>
 * @copyright  Copyright (C) 2003, 2004 Jesper Veggerby Hansen
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/tx_pbimagegraph
 */
class tx_pbimagegraph_Simple extends tx_pbimagegraph
{

    /**
     * tx_pbimagegraph_Simple [Constructor]
     *
     * @param int $width The width of the graph in pixels
     * @param int $height The height of the graph in pixels
     */
    function tx_pbimagegraph_Simple($width, $height, $plotType, $data, $title, $lineColor = 'black', $fillColor = 'white', $font = false)
    {
        parent::tx_pbimagegraph($width, $height);

        $plotarea =& tx_pbimagegraph::factory('plotarea');

        $dataset =& tx_pbimagegraph::factory('dataset', array($data));

        if ($font === false) {
            $font =& tx_pbimagegraph::factory('tx_pbimagegraph_Font');
        } elseif (is_string($font)) {
            $font =& tx_pbimagegraph::factory('ttf_font', $font);
            $font->setSize(8);
        }

        $this->setFont($font);

        $this->add(
            tx_pbimagegraph::vertical(
                tx_pbimagegraph::factory('title',
                    array(
                        $title,
                        array('size_rel' => 2)
                    )
                ),
                $plotarea,
                10
            )
        );

        $plotarea->addNew('line_grid', array(), IMAGE_GRAPH_AXIS_Y);

        $plot =& $plotarea->addNew($plotType, array(&$dataset));
        $plot->setLineColor($lineColor);
        $plot->setFillColor($fillColor);

        $axisX =& $plotarea->getAxis(IMAGE_GRAPH_AXIS_X);
        $axisX->showLabel(
            IMAGE_GRAPH_LABEL_MINIMUM +
            IMAGE_GRAPH_LABEL_ZERO +
            IMAGE_GRAPH_LABEL_MAXIMUM
        );

    }

    /**
     * Factory method to create the Image_Simple_Graph object.
     */
    function &factory($width, $height, $plotType, $data, $title, $lineColor = 'black', $fillColor = 'white', $font = false)
    {
        $obj =& tx_pbimagegraph::factory('tx_pbimagegraph_Simple',
            array(
                $width,
                $height,
                $plotType,
                $data,
                $title,
                $lineColor,
                $fillColor,
                $font
            )
        );
        return $obj;
    }

}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/class.tx_pbimagegraph_simple.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pbimagegraph/Image/Graph/class.tx_pbimagegraph_simple.php']);
}
?>