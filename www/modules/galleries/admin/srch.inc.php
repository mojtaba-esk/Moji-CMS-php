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

			'SEARCH_FORM_ELEMENTS' =>	$inpt -> text( 'query', Lang::id(), array( 'dir' => Lang::$info['dir'], 'align' => Lang::$info['align'], 'size' => 40)) . 
															
															$inpt -> hiddenRqst( array( 'vLng', 'md', 'lng')) .
															
															( Module::$opt['categoryMod'] ? $inpt -> dropDown( 'cat', Lang::id(), array( 
																'items'		=> getCats( Module::$name, Lang::viewId()),
																'defltItem'	=> array( 
																		'key'		=> 0,
																		'value'	=> Lang::getVal( 'all')
																),
																'dir'		=> Lang::$info['dir'],
																'align'	=> Lang::$info['align'])) : '') .
																
															$inpt -> date( 'prodDateBgn', Lang::id(), array( 
																				'elmnts' => 'Y,M,d',
																				//'type' => 'jalali',
																				//'value' => 'now',
																				'difY'		=> array( -15, 1),
																				'attribs' => array( 
																					'dir'		=> Lang::$info['dir'],
																					'align'	=> Lang::$info['align']
																			)
																		)
																	) .

															$inpt -> date( 'prodDateEnd', Lang::id(), array( 
																				'elmnts' => 'Y,M,d',
																				//'type' => 'jalali',
																				//'value' => 'now',
																				'difY'		=> array( -15, 1),
																				'attribs' => array( 
																					'dir'		=> Lang::$info['dir'],
																					'align'	=> Lang::$info['align']
																			)
																		)
																	),
		)
	);
		
	$srchSQL = '1';
	if( isset( $_GET['srch']))
	{
		while( ( $cols = $inpt -> getRow()) && $cols['lngId'] != Lang::id());
	
		isset( $srch) or $srch = new Search( Module::$opt[ 'id']);
		empty( $cols['query']) or $srchSQL = '`rltdId` IN ( '. $srch -> getIdsSQL( $cols['query'], Lang::viewId()) .')';
		
		isset( $cols['prodDateBgn']) and $cols['prodDateBgn'] = Date::mkTime( $cols['prodDateBgn']) and $srchSQL .= ' AND `prodDate` >= '. $cols['prodDateBgn'];
		if( isset( $cols['prodDateEnd']))
		{
			if( !empty( $cols['prodDateEnd']['Y']))
			{
				empty( $cols['prodDateEnd']['M']) and $cols['prodDateEnd']['M']	= 12;
				empty( $cols['prodDateEnd']['d']) and $cols['prodDateEnd']['d']	= 31;
			}

			$cols['prodDateEnd'] = Date::mkTime( $cols['prodDateEnd']) and $srchSQL .= ' AND `prodDate` <= '. $cols['prodDateEnd'];

		}//End of if( isset( $cols['prodDateEnd']));
		
		empty( $cols['cat']) or $srchSQL .= ' AND `catId` = '. intval( $cols['cat']);

	}//End of if( isset( $_GET['srch']));
	
	return $srchSQL;
?>
