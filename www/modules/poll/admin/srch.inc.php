<?php
/**
* @author Ghasem Babaie
* @since 2013-01-13
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
															
															/*( Module::$opt['categoryMod'] ? $inpt -> dropDown( 'cat', Lang::id(), array( 
																'items'		=> getCats( Module::$name, Lang::viewId()),
																'defltItem'	=> array( 
																		'key'		=> 0,
																		'value'	=> Lang::getVal( 'all')
																),
																'dir'		=> Lang::$info['dir'],
																'align'	=> Lang::$info['align'])) : '') .*/
																
															$inpt -> date( 'startTimeBgn', Lang::id(), array( 
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
																	
															$inpt -> date( 'startTimeEnd', Lang::id(), array( 
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
																	
															$inpt -> date( 'endTimeBgn', Lang::id(), array( 
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
																	
															$inpt -> date( 'endTimeEnd', Lang::id(), array( 
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
																
															$inpt -> date( 'pblishTimeBgn', Lang::id(), array( 
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

															$inpt -> date( 'pblishTimeEnd', Lang::id(), array( 
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
		
		isset( $cols['pblishTimeBgn']) and $cols['pblishTimeBgn'] = Date::mkTime( $cols['pblishTimeBgn']) and $srchSQL .= ' AND `pblishTime` >= '. $cols['pblishTimeBgn'];
		if( isset( $cols['pblishTimeEnd']))
		{
			if( !empty( $cols['pblishTimeEnd']['Y']))
			{
				empty( $cols['pblishTimeEnd']['M']) and $cols['pblishTimeEnd']['M']	= 12;
				empty( $cols['pblishTimeEnd']['d']) and $cols['pblishTimeEnd']['d']	= 31;
			}

			$cols['pblishTimeEnd'] = Date::mkTime( $cols['pblishTimeEnd']) and $srchSQL .= ' AND `pblishTime` <= '. $cols['pblishTimeEnd'];

		}//End of if( isset( $cols['pblishTimeEnd']));

		isset( $cols['startTimeBgn']) and $cols['startTimeBgn'] = Date::mkTime( $cols['startTimeBgn']) and $srchSQL .= ' AND `startTime` >= '. $cols['startTimeBgn'];
		if( isset( $cols['startTimeEnd']))
		{
			if( !empty( $cols['startTimeEnd']['Y']))
			{
				empty( $cols['startTimeEnd']['M']) and $cols['startTimeEnd']['M']	= 12;
				empty( $cols['startTimeEnd']['d']) and $cols['startTimeEnd']['d']	= 31;
			}

			$cols['startTimeEnd'] = Date::mkTime( $cols['startTimeEnd']) and $srchSQL .= ' AND `startTime` <= '. $cols['startTimeEnd'];

		}//End of if( isset( $cols['startTimeEnd']));

		isset( $cols['endTimeBgn']) and $cols['endTimeBgn'] = Date::mkTime( $cols['endTimeBgn']) and $srchSQL .= ' AND `endTime` >= '. $cols['endTimeBgn'];
		if( isset( $cols['endTimeEnd']))
		{
			if( !empty( $cols['endTimeEnd']['Y']))
			{
				empty( $cols['endTimeEnd']['M']) and $cols['endTimeEnd']['M']	= 12;
				empty( $cols['endTimeEnd']['d']) and $cols['endTimeEnd']['d']	= 31;
			}

			$cols['endTimeEnd'] = Date::mkTime( $cols['endTimeEnd']) and $srchSQL .= ' AND `endTime` <= '. $cols['endTimeEnd'];

		}//End of if( isset( $cols['endTimeEnd']));
		
		//empty( $cols['cat']) or $srchSQL .= ' AND `catId` = '. intval( $cols['cat']);

	}//End of if( isset( $_GET['srch']));
	
	return $srchSQL;
?>
