<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Search in Informations;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	//<!-- Preaper Input Object ...

		$lngs = Lang::getAll();
		//require( $_cfg['path'] .'/inc/lib/Input.class.inc.php');
		for( $i = 0; $i != sizeof( $lngs); $i++)
		{
			$lngsIds[] = $lngs[ $i ][ 'id' ];
			$lngsTitle[ $lngs[ $i ][ 'id' ] ] = $lngs[ $i ][ 'title'];
		}
		$inpt = new Input( $lngsIds, 'srh');

	//End of Preaper Input Object -->
	
	$tpl -> assign_vars( array(

			'SEARCH_FORM_ELEMENTS' =>	$inpt -> text( 'query', Lang::id(), array( 'class' => & Lang::$info['dir'], 'size' => 40)) .

										$inpt -> hiddenRqst( array( 'vLng', 'md', 'lng')) .

										( Module::$opt['categoryMod'] ? $inpt -> dropDown( 'cat', Lang::id(), array( 
											'items'		=> getCats( Module::$name, Lang::viewId()),
											'defltItem'	=> array( 
													'key'		=> 0,
													'value'	=> Lang::getVal( 'all')
											),
											'class'	=> & Lang::$info['dir'])) : '')

		)
	);
		
	$srchSQL = '1';
	if( isset( $_GET['srch']))
	{
		while( ( $cols = $inpt -> getRow()) && $cols['lngId'] != Lang::id());
	
		isset( $srch) or $srch = new Search( Module::$opt[ 'id']);
		empty( $cols['query']) or $srchSQL = '`rltdId` IN ( '. $srch -> getIdsSQL( $cols['query'], Lang::viewId()) .')';
		
		isset( $cols['saleTimeBgn']) and $cols['saleTimeBgn'] = Date::mkTime( $cols['saleTimeBgn']) and $srchSQL .= ' AND `saleTime` >= '. $cols['saleTimeBgn'];
		
		if( isset( $cols['saleTimeEnd']))
		{
			if( !empty( $cols['saleTimeEnd']['Y']))
			{
				empty( $cols['saleTimeEnd']['M']) and $cols['saleTimeEnd']['M']	= 12;
				empty( $cols['saleTimeEnd']['d']) and $cols['saleTimeEnd']['d']	= 31;
			}

			$cols['saleTimeEnd'] = Date::mkTime( $cols['saleTimeEnd']) and $srchSQL .= ' AND `saleTime` <= '. $cols['saleTimeEnd'];

		}//End of if( isset( $cols['saleTimeEnd']));
		
		empty( $cols['cat']) or $srchSQL .= ' AND `catId` = '. intval( $cols['cat']);
		empty( $cols['verId']) or $srchSQL .= ' AND `verId` = '. intval( $cols['verId']);
		
		if( !empty( $cols['updateVerId']))
		{
			$updtRws = DB::load(
					array( 
						'tableName' => Module::$name. '_update_logs',
						'cols'	=> array( 'cuId'),
						'where' => array(
							'verId' => intval( $cols['updateVerId']),
						),
					),
					NULL,
					true
				);
		
			$srchSQL .= ' AND `rltdId` IN ( 0'. implode( ',', $updtRws) .')';

			unset( $updtRws);
		
		}//End of if( !empty( $cols['updateVerId']));
		
		empty( $cols['lockSerial']) or $srchSQL .= ' AND `lockSerial` = \''. $inpt -> dbClr( $cols['lockSerial']) .'\'';

	}//End of if( isset( $_GET['srch']));
	
	return $srchSQL;
?>
