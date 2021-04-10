<?php
/**
* @author: Mojtaba Eskandari
* @since 2009-08-27
* @name Admin Panel Functions.
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

/*-----------------------------------------------*/

	/**
	* @desc Create the Row Title for show informations in table, for sorting.;
	*/
	function rwTitle( $name, $title = NULL)
	{
		global $_cfg;
		static $url = NULL;
		$url or $url = URL::get( array( 'sort', 'srtType', 'pg'));

		$title = Lang::getVal( $title ? $title : $name); 
		isset( $_GET['sort']) and $_GET['sort'] == $name and $title = '<b>'. $title .'</b>';

		return $title .' <div class="srtIcn"><a href="'. $url .'&sort='. $name .'&srtType=DESC"><img src="'. $_cfg['URL'] .'ext/imgs/desc.png" /></a> <a href="'. $url .'&sort='. $name .'&srtType=ASC"><img src="'. $_cfg['URL'] .'ext/imgs/asc.png" /></a></div>';
	}

/*-----------------------------------------------*/

	/**
	* @desc Save The Change Log of admin panel, for security reasons.;
	* @examples:
	* 				sLog( array(
	*						'itemId'	=> $cols[ 'id'],
	*						'desc'		=> & $cols['title'],
	*					)
	*				);
	*
	*	OR:
	*				sLog( array(
	*						'itemId'	=> DB::insrtdId(),
	*						'desc'		=> & $cols['title'],
	*						'action'	=> 'new',
	*					)
	*				);
	*
	*
	*/
	function sLog( $prms = array())
	{
		if( defined( 'DISABLE_LOG')) return;
		
		isset( $prms['itemId'])	or $prms['itemId']	= @$_GET['id'];
		
		if( !$prms['itemId']) return;
		
		global $_cfg;
		$prms['domId']	= $_cfg['domain']['id'];
		
		$prms['userId']	= Session::$userId;
		$prms['logTime']= time();
		$prms['ip']		= & $_SERVER['REMOTE_ADDR'];
		
		isset( $prms['mdId'])	or $prms['mdId']	= Module::$opt['id'];
		isset( $prms['sub'])	or $prms['sub']		= @$_GET['sub'];
		isset( $prms['action'])	or $prms['action']	= @$_GET['mod'];
		
		DB::insert( array(
				'tableName'	=> 'config_admin_logs',
				'cols'		=> & $prms,
			)
		);		
	}

/*-----------------------------------------------*/
	/**
	* @desc Fetch the Group Info From groups table...;
	*/
	function getGrpInf( $grId = 0)
	{
		$grId or $grId = @$_SESSION['groupId'];

		if( $grId)
		{
			$SQL = "SELECT * FROM `admin_users_groups` WHERE `id` = $grId";

		}else{

			$SQL = "SELECT
						 `g`.* 
					FROM
						`admin_users_groups`	AS	`g`,
						`admin_users_main`		AS	`u`
					WHERE
						`u`.`id` = ". Session::$userId ."
						AND
							`u`.`groupId` = `g`.`id`
					";

		}//End of if( $grId);

		$rws = DB::load( $SQL, 'users');
		return $rws[0];
	}

/*-----------------------------------------------*/

	/**
	* @desc Fetch the ENUM values from structure of table...;
	*/
	function enumItems( $tbl, $fld)
	{
		$SQL = "SHOW COLUMNS FROM `$tbl` WHERE `Field` = '$fld'";
		$rws = DB::load( $SQL);

		$enum = str_replace( array( 'enum(\'', '\')'), '', $rws[0]['Type']);
		return explode( '\',\'', $enum);
	}

/*-----------------------------------------------*/

	/**
	* @desc Makes an array with a language, and flip the array keys and values...;
	*/
	function arrStLang( & $arr)
	{
		$rs = array();
		if( !is_array( $arr)) return $rs;
		
		foreach( $arr as $val)
		{
			$rs[ $val ] = Lang::getVal( $val);
		}

		return $rs;
	}

/*-----------------------------------------------*/
	/**
	* @desc highlight the searched keywords in the shown list;
	*/
	function hgLightSrch( & $rws)
	{
		global $inpt, $srch;
		if( empty( $inpt) || empty( $srch)) return false;

		//<!-- Prepare the keywords and their highlights...

			$kwrds = $srch -> getKwrds( $inpt -> getVal( 'query', Lang::id()));
			$kwrdsH = array();
			foreach( $kwrds as $id => $kw)
			{
				$kwrdsH[ $id ] = '<span class="srchHighL">'. $kw .'</span>';

			}//End of foreach( $kwrds as $id => $kw);

		//-->

		$sizeRws = sizeof( $rws);
		for( $i = 0; $i != $sizeRws; $i++)
		{
			foreach( @$rws[ $i ] as $key => $tmp)
			{
				$rws[ $i ][ $key ] = str_replace( $kwrds, $kwrdsH, $rws[ $i ][ $key ]);

			}//End of foreach( @$rws[ $i ] as $key => $tmp);
			
		}//End of for( $i = 0; $i != $sizeRws; $i++);

		return true;
	}

/*-----------------------------------------------*/
?>
