<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

//include_once(t3lib_extMgm::extPath($_EXTKEY).'Image/Graph.php');

t3lib_extMgm::addPlugin(array('LLL:EXT:pbimagegraph/lang/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');

?>