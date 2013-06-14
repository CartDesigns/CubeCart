<?php
if(!defined('CC_DS')) die('This file can not be accessed directly.');

include 'controllers'.CC_DS.'controller.index.inc.php';

$htmlout = $GLOBALS['smarty']->fetch('templates/'.$global_template_file);
$htmlout = ($GLOBALS['gui']->disableJS) ? preg_replace('~<\s*\bscript\b[^>]*>(.*?)<\s*\/\s*script\s*>~is', '', $htmlout) : $htmlout;

## Copyright notices
## Decide if copyright remains or not
if (preg_match("#^([0-9]{6})+[-]+([0-9])+[-]+([0-9]{4})$#", $GLOBALS['config']->get('config','lkv'))) {
	$copyRightBody	= '';
	$copyRightTitle	= '';
} else {
	$copyRightBody	= '<div style="text-align: center; margin: 10px; font-size: 80%;"><p><a href="http://www.cubecart.com" target="_blank" title="eCommerce Software by CubeCart">eCommerce Software</a> by CubeCart</p></div>';
	$copyRightTitle	= ' - (Powered by CubeCart)';
}
$googleAnalytics = isset($googleAnalytics) ? $googleAnalytics : '';
$htmlout = preg_replace(
	array('/(\<\/body\>)/i','/(\<\/title\>)/i', '/(\<\/head\>)/i'),
	array($copyRightBody.'$1', $copyRightTitle.'$1', $googleAnalytics.'$1'),
	$htmlout
);
die($htmlout);