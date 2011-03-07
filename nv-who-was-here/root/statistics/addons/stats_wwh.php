<?php
/**
*
* @package phpBB Statistics
* @version $Id: stats_wwh.php 152 2010-06-20 11:48:44Z marc1706 $
* @copyright (c) 2010 Marc Alexander(marc1706) www.m-a-styles.de
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/


/**
* @ignore
*/
if (!defined('IN_PHPBB') || !defined('IN_STATS_MOD'))
{
	exit;
}

/**
* @package phpBB Statistics - NV Who was here? Add-On
*/
class stats_wwh
{	
	/**
	* module filename
	* file must be in "statistics/addons/"
	*/
	var $module_file = 'stats_wwh';
	
	/**
	* module language name
	* please choose a distinct name, i.e. 'STATS_...'
	* $module_name always has to be $module_file in capital letters
	*/
	var $module_name = 'STATS_WWH';
	
	/**
	* module-language file
	* file must be in "language/{$user->lang}/mods/stats/addons/"
	*/
	var $template_file = 'stats_wwh';
	
	/**
	* set this to false if you do not need any acp settings
	*/
	var $load_acp_settings = true;
	
	/**
	* you can add some vars here
	*/
	var $u_action;
	
	/**
	* add-on functions below
	*/
	function load_stats()
	{
		global $config, $db, $template, $stats_config, $user;
		global $phpbb_root_path, $phpEx, $table_prefix;
		
		$wwh_info = $current_info = $users = $user_info = array();
		
		$limit_count = request_var('limit_count', 10); //replace 10 by the config option

		//create an array containing the limit_count options as $option=>$option_lang
		$limit_options = array(
			'1'		=> 1,
			'3'		=> 3,
			'5'		=> 5,
			'10'	=> 10,
			'15'	=> 15,
		);
		$limit_prompt = sprintf($user->lang['LIMIT_PROMPT'], $user->lang['USERS']);
		
		if(!defined('STATS_WWH_TABLE'))
		{
			define('STATS_WWH_TABLE',	$table_prefix . 'stats_wwh');
		}
		
		$sql = 'SELECT config_value AS stats_wwh_purge FROM ' . STATS_CONFIG_TABLE . " WHERE config_name = 'stats_wwh_purge '";
		$result = $db->sql_query($sql);
		$purge = $db->sql_fetchfield('stats_wwh_purge');
		$db->sql_freeresult($result);
		
		if($purge)
		{
			$sql = 'DELETE FROM ' . STATS_WWH_TABLE;
			$db->sql_query($sql);
			set_stats_config('stats_wwh_purge', 0);
		}
		
		// first get totals
		if ($db->sql_layer != 'mssql' && $db->sql_layer != 'mssql_odbc')
		{
			$sql = 'SELECT name, data 
					FROM ' . STATS_WWH_TABLE . "
					WHERE LENGTH(data) > LENGTH(REPLACE(data, ',', ''))
					ORDER BY name ASC";
		}
		else
		{
			$sql = 'SELECT name, data 
					FROM ' . STATS_WWH_TABLE . "
					WHERE LEN(data) > LEN(REPLACE(data, ',', ''))
					ORDER BY name ASC";
		}
		
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			$current_info = explode(',', $row['data']);
			$wwh_info[] = array(
				'time' => $row['name'], 
				'total' => $current_info[0], 
				'registered' => $current_info[1], 
				'guests' => $current_info[2], 
				'bots' => $current_info[3],
				'hidden' => $current_info[4]);
		}
		
		// Now let's go through all the info and get some important vars for the statistics
		if(isset($wwh_info) && sizeof($wwh_info) > 0)
		{
			$max_count = array(
				'total' => 0, 
				'registered' => 0, 
				'guests' => 0, 
				'bots' => 0,
				'hidden' => 0);
				
			$totals = array(
				'total' => 0, 
				'registered' => 0, 
				'guests' => 0, 
				'bots' => 0,
				'hidden' => 0);
			foreach($wwh_info as $current_wwh)
			{
				$max_count['total'] = ($max_count['total'] < $current_wwh['total']) ? $current_wwh['total'] : $max_count['total'];
				$max_count['registered'] = ($current_wwh['registered'] > $max_count['registered']) ? $current_wwh['registered'] : $max_count['registered'];
				$max_count['guests'] = ($current_wwh['guests'] > $max_count['guests']) ? $current_wwh['guests'] : $max_count['guests'];
				$max_count['bots'] = ($current_wwh['bots'] > $max_count['bots']) ? $current_wwh['bots'] : $max_count['bots'];
				$max_count['hidden'] = ($current_wwh['hidden'] > $max_count['hidden']) ? $current_wwh['hidden'] : $max_count['hidden'];
				
				$totals['total'] = $totals['total'] + $current_wwh['total'];
				$totals['registered'] = $totals['registered'] + $current_wwh['registered'];
				$totals['guests'] = $totals['guests'] + $current_wwh['guests'];
				$totals['bots'] = $totals['bots'] + $current_wwh['bots'];
				$totals['hidden'] = $totals['hidden'] + $current_wwh['hidden'];
			}
			
			// show the purges (total)
			if($max_count['total'] > 0)
			{
				foreach($wwh_info as $key => $current_purge)
				{
					$id = $key - 1;
					if($key != 0)
					{
						$time = $user->format_date($wwh_info[$id]['time']) . ' - ' . $user->format_date($current_purge['time']);
					}
					else
					{
						$time = $user->format_date($current_purge['time']);
					}
					$template->assign_block_vars('purge_total_row', array(
						'TIME'				=> $time,
						'COUNT'				=> $current_purge['total'],
						'PCT'				=> number_format($current_purge['total'] / $totals['total'] * 100, 3),
						'BARWIDTH'			=> number_format($current_purge['total'] / $max_count['total'] * 100, 1),
						'IS_MAX'			=> ($current_purge['total'] == $max_count['total']) ? true : false,
					));
					
				}
				$purge_total = true;
			}
			else
			{
				$purge_total = false;
			}
			
			// show the purges (registered)
			if($max_count['registered'] > 0)
			{
				foreach($wwh_info as $key => $current_purge)
				{
					$id = $key - 1;
					if($key != 0)
					{
						$time = $user->format_date($wwh_info[$id]['time']) . ' - ' . $user->format_date($current_purge['time']);
					}
					else
					{
						$time = $user->format_date($current_purge['time']);
					}
					$template->assign_block_vars('purge_registered_row', array(
						'TIME'				=> $time,
						'COUNT'				=> $current_purge['registered'],
						'PCT'				=> number_format($current_purge['registered'] / $totals['registered'] * 100, 3),
						'BARWIDTH'			=> number_format($current_purge['registered'] / $max_count['registered'] * 100, 1),
						'IS_MAX'			=> ($current_purge['registered'] == $max_count['registered']) ? true : false,
					));
					
				}
				$purge_registered = true;
			}
			else
			{
				$purge_registered = false;
			}
			
			// show the purges (guests)
			if($max_count['guests'] > 0)
			{
				foreach($wwh_info as $key => $current_purge)
				{
					$id = $key - 1;
					if($key != 0)
					{
						$time = $user->format_date($wwh_info[$id]['time']) . ' - ' . $user->format_date($current_purge['time']);
					}
					else
					{
						$time = $user->format_date($current_purge['time']);
					}
					$template->assign_block_vars('purge_guests_row', array(
						'TIME'				=> $time,
						'COUNT'				=> $current_purge['guests'],
						'PCT'				=> number_format($current_purge['guests'] / $totals['guests'] * 100, 3),
						'BARWIDTH'			=> number_format($current_purge['guests'] / $max_count['guests'] * 100, 1),
						'IS_MAX'			=> ($current_purge['guests'] == $max_count['guests']) ? true : false,
					));
					
				}
				$purge_guests = true;
			}
			else
			{
				$purge_guests = false;
			}
			
			// show the purges (bots)
			if($max_count['bots'] > 0)
			{
				foreach($wwh_info as $key => $current_purge)
				{
					$id = $key - 1;
					if($key != 0)
					{
						$time = $user->format_date($wwh_info[$id]['time']) . ' - ' . $user->format_date($current_purge['time']);
					}
					else
					{
						$time = $user->format_date($current_purge['time']);
					}
					$template->assign_block_vars('purge_bots_row', array(
						'TIME'				=> $time,
						'COUNT'				=> $current_purge['bots'],
						'PCT'				=> number_format($current_purge['bots'] / $totals['bots'] * 100, 3),
						'BARWIDTH'			=> number_format($current_purge['bots'] / $max_count['bots'] * 100, 1),
						'IS_MAX'			=> ($current_purge['bots'] == $max_count['bots']) ? true : false,
					));
					
				}
				$purge_bots = true;
			}
			else
			{
				$purge_bots = false;
			}
			
			// show the purges (hidden)
			if($max_count['hidden'] > 0)
			{
				foreach($wwh_info as $key => $current_purge)
				{
					$id = $key - 1;
					if($key != 0)
					{
						$time = $user->format_date($wwh_info[$id]['time']) . ' - ' . $user->format_date($current_purge['time']);
					}
					else
					{
						$time = $user->format_date($current_purge['time']);
					}
					$template->assign_block_vars('purge_hidden_row', array(
						'TIME'				=> $time,
						'COUNT'				=> $current_purge['hidden'],
						'PCT'				=> number_format($current_purge['hidden'] / $totals['hidden'] * 100, 3),
						'BARWIDTH'			=> number_format($current_purge['hidden'] / $max_count['hidden'] * 100, 1),
						'IS_MAX'			=> ($current_purge['hidden'] == $max_count['hidden']) ? true : false,
					));
					
				}
				$purge_hidden = true;
			}
			else
			{
				$purge_hidden = false;
			}
			
		}
		
		// Fill array with initial vars so we don't have to execute an isset everytime we get data from the database
		$times = array(
			0 => 0,
			1 => 0,
			2 => 0,
			3 => 0,
			4 => 0,
			5 => 0,
			6 => 0,
			7 => 0,
			8 => 0,
			9 => 0,
			10 => 0,
			11 => 0,
			12 => 0,
			13 => 0,
			14 => 0,
			15 => 0,
			16 => 0,
			17 => 0,
			18 => 0,
			19 => 0,
			20 => 0,
			21 => 0,
			22 => 0,
			23 => 0,
		);
		
		$total_visits = $max_count = 0;
		
		/** 
		* Get visits by time and the users info
		* We have to distinguish between mssql and other databases
		* Also, we filter out the unneeded data by only selecting the data with a length less than 9
		* This won't be any issue until you reach 100,000,000 users
		* If you reach that number, please tell me
		*/
		if ($db->sql_layer != 'mssql' && $db->sql_layer != 'mssql_odbc')
		{
			$sql = 'SELECT name, data 
				FROM ' . STATS_WWH_TABLE . "
				WHERE LENGTH(data) < 9
				ORDER BY name ASC";
		}
		else
		{
			$sql = 'SELECT name, data 
				FROM ' . STATS_WWH_TABLE . "
				WHERE LEN(data) < 9
				ORDER BY name ASC";

		}
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			++$times[$user->format_date($row['name'], 'G')];
			++$total_visits;
			
			if(isset($users[$row['data']]))
			{
				++$users[$row['data']];
			}
			else
			{
				$users[$row['data']] = 1;
			}
		}
		
		$max_count = max($times);
		
		// visits by daytime
		if($max_count > 0)
		{
			foreach($times as $key => $current_time)
			{
				$template->assign_block_vars('times_row', array(
					'TIME'				=> date('H:i', mktime($key, 0, 0, 0, 0, 0)) . ' - ' . date('H:i', mktime($key + 1, 0, 0, 0, 0, 0)),
					'COUNT'				=> $current_time,
					'PCT'				=> number_format($current_time / $total_visits * 100, 3),
					'BARWIDTH'			=> number_format($current_time / $max_count * 100, 1),
					'IS_MAX'			=> ($current_time == $max_count) ? true : false,
				));
			}
			$times_row = true;
		}
		else
		{
			$times_row = false;
		}
		
		if(isset($users) && sizeof($users) > 0)
		{
			$max_count = max($users);
			
			// visits by users
			if($max_count > 0)
			{
				arsort($users);
				$start_count = 1;
				$users = array_slice($users, 0, $limit_count, true);
				
				$sql_where = $db->sql_in_set('user_id', array_keys($users));
				$sql = 'SELECT user_id, user_colour, username 
						FROM '. USERS_TABLE . '
						WHERE ' . $sql_where . '
						GROUP BY user_id, username, user_colour';
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$user_info[$row['user_id']] = array('colour' => $row['user_colour'], 'username' => $row['username']);
				}			
				$db->sql_freeresult($result);
				
				foreach($users as $id => $count)
				{
					$template->assign_block_vars('users_row', array(
						'USER'				=> get_username_string('full', $id, $user_info[$id]['username'], $user_info[$id]['colour']),
						'COUNT'				=> $count,
						'PCT'				=> number_format($count / $total_visits * 100, 3),
						'BARWIDTH'			=> number_format($count / $max_count * 100, 1),
						'IS_MAX'			=> ($count == $max_count) ? true : false,
					));
				}
				$user_row = true;
			}
		}
		else
		{
			$user_row = false;
		}

		$template->assign_vars(array(
			'REAL_TOTAL_VISITS'	=> $total_visits,
			'TOTAL_VISITS'		=> (isset($totals['total'])) ? $totals['total'] : 0,
			'TOTAL_VISITS_REG'	=> (isset($totals['registered'])) ? $totals['registered'] : 0,
			'TOTAL_VISITS_ANOM'	=> (isset($totals['guests'])) ? $totals['guests'] : 0,
			'TOTAL_VISITS_BOTS'	=> (isset($totals['bots'])) ? $totals['bots'] : 0,
			'TOTAL_VISITS_HID'	=> (isset($totals['hidden'])) ? $totals['hidden'] : 0,
			'S_PURGE_TOTAL'		=> (isset($purge_total)) ? $purge_total : 0,
			'S_TIMES_ROW'		=> (isset($max_count) && $max_count > 0) ? true : false,
			'S_PURGE_REGISTERED'=> (isset($purge_registered)) ? $purge_registered : 0,
			'S_PURGE_GUESTS'	=> (isset($purge_guests)) ? $purge_guests : 0,
			'S_PURGE_BOTS'		=> (isset($purge_bots)) ? $purge_bots : 0,
			'S_PURGE_HIDDEN'	=> (isset($purge_hidden)) ? $purge_hidden : 0,
			'S_USER_VISITS'		=> $user_row,
			'VISITS_BY_USER'	=> sprintf($user->lang['VISITS_BY_USER'], $limit_count),
		));
		
		$template->assign_var('LIMIT_SELECT_BOX', make_select_box($limit_options, $limit_count, 'limit_count', $limit_prompt, $user->lang['GO'], $this->u_action));
	}
	
	
	/**
	* acp frontend for the add-on
	* if you want to use this, set $load_acp_settings to true
	*/
	function load_acp()
	{
		$display_vars = array(
					'title' => 'STATS_WWH',
					'vars' => array(
						'legend1' 							=> 'STATS_WWH',
						'stats_wwh_purge'					=> array('lang' => 'STATS_WWH_PURGE'  , 'validate' => 'bool'  , 'type' => 'radio:yes_no'  , 'explain' => true),
					)
				);
		return $display_vars;
	}
	
	
	/**
	* API functions
	*/
	function install()
	{
		global $db, $table_prefix;
		
		$sql = 'SELECT MAX(addon_id) FROM ' . STATS_ADDONS_TABLE;
		$result = $db->sql_query($sql);
		$id = (int) $db->sql_fetchfield('addon_id');
		$db->sql_freeresult($result);
	
		set_stats_addon($this->module_file, 1);
		set_stats_config('stats_wwh_purge', 0);
		
		$sql = 'UPDATE ' . STATS_ADDONS_TABLE . '
				SET addon_id = ' . ($id + 1) . "
				WHERE addon_classname = '" . $this->module_file . "'";
		$result = $db->sql_query($sql);
		$db->sql_freeresult($result);
		
		switch ($db->sql_layer)
		{
			case 'mysql':
				$sql = "CREATE TABLE " . $table_prefix . "stats_wwh (
						name varchar(255) DEFAULT '' NOT NULL,
						data mediumtext NOT NULL,
						PRIMARY KEY (name)
				);";
			break;

			case 'mysql4':
				if (version_compare($db->sql_server_info(true), '4.1.3', '>='))
				{
					$sql = "CREATE TABLE " . $table_prefix . "stats_wwh (
						name varchar(255) DEFAULT '' NOT NULL,
						data mediumtext NOT NULL,
						PRIMARY KEY (name)
					) CHARACTER SET utf8 COLLATE utf8_bin;";
				}
				else
				{
					$sql = "CREATE TABLE " . $table_prefix . "stats_wwh (
						name varchar(255) DEFAULT '' NOT NULL,
						data mediumtext NOT NULL,
						PRIMARY KEY (name)
					);";
				}
			break;

			case 'mysqli':
				$sql = "CREATE TABLE " . $table_prefix . "stats_wwh (
					name varchar(255) DEFAULT '' NOT NULL,
					data mediumtext NOT NULL,
					PRIMARY KEY (name)
				) CHARACTER SET utf8 COLLATE utf8_bin;";
			break;

			case 'mssql':
			case 'mssql_odbc':
				$sql = "CREATE TABLE [" . $table_prefix . "stats_wwh] (
						[name] [varchar] (255) DEFAULT ('') NOT NULL ,
						[data] [text] DEFAULT ('') NOT NULL 
					) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
					GO

					ALTER TABLE [" . $table_prefix . "stats_wwh] WITH NOCHECK ADD 
						CONSTRAINT [PK_" . $table_prefix . "stats_wwh] PRIMARY KEY  CLUSTERED 
						(
							[name]
						)  ON [PRIMARY] 
					GO";
			break;

			case 'postgres':
				$sql = "CREATE TABLE " . $table_prefix . "stats_wwh (
					name varchar(255) DEFAULT '' NOT NULL,
					data TEXT DEFAULT '' NOT NULL,
					PRIMARY KEY (name)
				);";
			break;

			case 'sqlite':
				$sql = "CREATE TABLE " . $table_prefix . "stats_wwh (
					name varchar(255) NOT NULL DEFAULT '',
					data mediumtext(16777215) NOT NULL DEFAULT '',
					PRIMARY KEY (name)
				);";
			break;

			case 'firebird':
				$sql = "CREATE TABLE " . $table_prefix . "stats_wwh (
						name VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
						data BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
					);;

					ALTER TABLE " . $table_prefix . "stats_wwh ADD PRIMARY KEY (name);;";
			break;

			case 'oracle':
				$sql = "CREATE TABLE " . $table_prefix . "stats_wwh (
					name varchar2(255) DEFAULT '' ,
					data clob DEFAULT '' ,
					CONSTRAINT pk_" . $table_prefix . "stats_wwh PRIMARY KEY (name)
				)
				/";
			break;

			default:
				trigger_error($user->lang['UNSUPPORTED_DB']);
			break;
		}
		$db->sql_query($sql);
	}
	
	function uninstall()
	{
		global $db, $table_prefix;
		
		$del_addon = $this->module_file;
		
		$sql = 'DELETE FROM ' . STATS_ADDONS_TABLE . "
			WHERE addon_classname = '" . $del_addon . "'";
		$db->sql_query($sql);
		
		$sql = 'DELETE FROM ' . STATS_CONFIG_TABLE . "
			WHERE config_name = 'stats_wwh_purge'";
		$db->sql_query($sql);
		
		$table_name = 'stats_wwh';
		
		if ($db->sql_layer != 'mssql' && $db->sql_layer != 'mssql')
		{
			$sql = 'DROP TABLE IF EXISTS ' . $table_prefix . $table_name;
			$result = $db->sql_query($sql);
			$db->sql_freeresult($result);
		}
		else
		{
			$sql = 'if exists (select * from sysobjects where name = ' . $table_prefix . $table_name . ')
				drop table ' . $table_prefix . $table_name;
			$sql = "IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '{$table_prefix}{$table_name}')
				DROP TABLE {$table_prefix}{$table_name}";
			$result = $db->sql_query($sql);
			$db->sql_freeresult($result);
		}
		
		return true;
	}
}
?>