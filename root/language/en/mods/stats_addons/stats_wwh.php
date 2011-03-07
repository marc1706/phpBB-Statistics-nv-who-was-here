<?php
/**
*
* @package phpBB Statistics
* @version $Id: stats_wwh.php 152 2010-06-20 11:48:44Z marc1706 $
* @copyright (c) 2009 - 2010 Marc Alexander(marc1706) www.m-a-styles.de
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @translator (c) ( Marc Alexander - http://www.m-a-styles.de ), TheUniqueTiger - Nayan Ghosh
*/

if (!defined('IN_PHPBB') || !defined('IN_STATS_MOD'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}


/*	Example:
$lang = array_merge($lang, array(	
	'STATS'								=> 'phpBB Statistics',	

));
*/

$lang = array_merge($lang, array(	
	'STATS_WWH'					=> 'NV Who was here? Add-On',
	'STATS_WWH_EXPLAIN'			=> '',
	'STATS_WWH_PURGE'			=> 'Purge database',
	'STATS_WWH_PURGE_EXPLAIN'	=> 'This will remove all data that was previously recorded from the database',
	'TOTAL_VISITS'				=> 'Total visited users',
	'TOTAL_VISITS_REG'			=> 'Total visited Registered Users',
	'TOTAL_VISITS_ANOM'			=> 'Total visited Guests',
	'TOTAL_VISITS_BOTS'			=> 'Total visited Bots',
	'TOTAL_VISITS_HID'			=> 'Total visited Hidden Users',
	'REAL_TOTAL_VISITS'			=> 'Total visits',
	'TOTAL_PURGES'				=> 'Number of visited Users (per defined timeframe)',
	'REGISTERED_PURGES'			=> 'Number of visited Registered Users (per defined timeframe)',
	'GUESTS_PURGES'				=> 'Number of visited Guests (per defined timeframe)',
	'BOTS_PURGES'				=> 'Number of visited Bots (per defined timeframe)',
	'HIDDEN_PURGES'				=> 'Number of visited Hidden Users (per defined timeframe)',
	'VISIT_BY_TIME'				=> 'Visits by day time',
	'VISITS_BY_USER'			=> 'Top %d users (by visits)',
));
?>