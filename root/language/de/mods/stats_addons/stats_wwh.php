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
	'STATS_WWH'					=> 'NV Wer war da? Add-On',
	'STATS_WWH_EXPLAIN'			=> '',
	'STATS_WWH_PURGE'			=> 'Leere Datenbank',
	'STATS_WWH_PURGE_EXPLAIN'	=> 'Dies wird alle Daten, die bisher gespeichert wurden, aus der Datenbank löschen',
	'TOTAL_VISITS'				=> 'Gesamtanzahl Besuche',
	'TOTAL_VISITS_REG'			=> 'Anzahl Besuche durch Registrierte Benutzer',
	'TOTAL_VISITS_ANOM'			=> 'Anzahl Besuche durch Gäste',
	'TOTAL_VISITS_BOTS'			=> 'Anzahl Besuche durch Bots',
	'TOTAL_VISITS_HID'			=> 'Anzahl Besuche durch unsichtbare Benutzer',
	'REAL_TOTAL_VISITS'			=> 'Gesamtanzahl Zugriffe',
	'TOTAL_PURGES'				=> 'Gesamtanzahl Besuche (pro angegebenem Zeitraum)',
	'REGISTERED_PURGES'			=> 'Anzahl Besuche durch Registrierte Benutzer (pro angegebenem Zeitraum)',
	'GUESTS_PURGES'				=> 'Anzahl Besuche durch Gäste (pro angegebenem Zeitraum)',
	'BOTS_PURGES'				=> 'Anzahl Besuche durch Bots (pro angegebenem Zeitraum)',
	'HIDDEN_PURGES'				=> 'Anzahl Besuche durch unsichtbare Bentuzer (pro angegebenem Zeitraum)',
	'VISIT_BY_TIME'				=> 'Besuche nach Uhrzeit',
	'VISITS_BY_USER'			=> 'Top %d Benutzer (nach Besuchen)',
));
?>