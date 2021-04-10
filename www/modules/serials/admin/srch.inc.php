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

			'SEARCH_FORM_ELEMENTS' =>	$inpt -> text( 'serial', 0, array( 'class' => 'ltr', 'size' => 40)) . 
										
										$inpt -> text( 'url', 0, array( 'class' => 'ltr', 'size' => 40)) . 
										
										$inpt -> hiddenRqst( array( 'vLng', 'md')).
										
										( Module::$opt['categoryMod'] ? $inpt -> dropDown( 'cat', 0, array( 
											'items'		=> getCats( Module::$name, Lang::viewId()),
											'defltItem'	=> array( 
													'key'	=> 0,
													'value'	=> Lang::getVal( 'all')
											),
											'dir'	=> Lang::$info['dir'],
											'align'	=> Lang::$info['align'])) : '') .
																
											$inpt -> date( 'actvTimeBgn', 0, array( 
																'elmnts' => 'Y,M,d',
																//'type' => 'jalali',
																//'value' => 'now',
																'difY'		=> array( -15, 1),
																'attribs' => array( 
																	'dir'	=> Lang::$info['dir'],
																	'align'	=> Lang::$info['align']
															)
														)
													) .

											$inpt -> date( 'actvTimeEnd', 0, array( 
																'elmnts' => 'Y,M,d',
																//'type' => 'jalali',
																//'value' => 'now',
																'difY'		=> array( -15, 1),
																'attribs' => array( 
																	'dir'	=> Lang::$info['dir'],
																	'align'	=> Lang::$info['align']
															)
														)
													),
		)
	);
		
	$srchSQL = '1';
	if( !isset( $_GET['srch'])) return $srchSQL;

	//while( ( $cols = $inpt -> getRow()) && $cols['lngId'] != Lang::id());
	$cols = $inpt -> getRow();

	//isset( $srch) or $srch = new Search( Module::$opt[ 'id']);
	//empty( $cols['query']) or $srchSQL = '`rltdId` IN ( '. $srch -> getIdsSQL( $cols['query'], Lang::viewId()) .')';
	
	isset( $cols['actvTimeBgn']) and $cols['actvTimeBgn'] = Date::mkTime( $cols['actvTimeBgn']) and $srchSQL .= ' AND `actvTime` >= '. $cols['actvTimeBgn'];
	if( isset( $cols['actvTimeEnd']))
	{
		if( !empty( $cols['actvTimeEnd']['Y']))
		{
			empty( $cols['actvTimeEnd']['M']) and $cols['actvTimeEnd']['M']	= 12;
			empty( $cols['actvTimeEnd']['d']) and $cols['actvTimeEnd']['d']	= 31;
		}

		$cols['actvTimeEnd'] = Date::mkTime( $cols['actvTimeEnd']) and $srchSQL .= ' AND `actvTime` <= '. $cols['actvTimeEnd'];

	}//End of if( isset( $cols['actvTimeEnd']));
	
	if( !empty( $cols['serial']))
	{
		//S2
		$serial = strrev( sha1( strrev( md5( trim( $cols['serial'])))));
		//S3
		$serial = md5( strrev( sha1( $serial) . crc32( $serial)));
		$srchSQL .= " AND `serial` = '$serial'";

	}//End of if( !empty( $cols['serial']));

	empty( $cols['url']) or $srchSQL .= " AND `url` = '". str_replace( array( 'www.', 'http:', '/'), '', strtolower( trim( $cols['url']))) ."'";
	empty( $cols['cat']) or $srchSQL .= ' AND `catId` = '. intval( $cols['cat']);
		
	return $srchSQL;
?>
