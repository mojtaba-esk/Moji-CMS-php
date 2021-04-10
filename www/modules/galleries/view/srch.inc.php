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
		
		lib( array( 'Input'));
		$inpt = new Input( $lngsIds, 'srh');

	//End of Preaper Input Object -->

	$tpl -> assign_vars( array(

			'SEARCH_FORM_ELEMENTS' =>	$inpt -> text( 'query', Lang::id(), array( 'class' => & Lang::$info['dir'], 'size' => 30), false) . 
															
															$inpt -> hiddenRqst( array( 'lng', 'md', 'lng')) .
															
															( Module::$opt['categoryMod'] ? $inpt -> dropDown( 'cat', Lang::id(), array( 
																'items'		=> getCats( Module::$name, Lang::viewId()),
																'defltItem'	=> array( 
																		'key'		=> 0,
																		'value'	=> Lang::getVal( 'all')
																),
																'dir'		=> Lang::$info['dir'],
																'align'	=> Lang::$info['align'])) : '')
																
		)
	);
	
	if( !isset( $_GET['srch'])) return '1';

	$srchSQL = '1';
	while( ( $cols = $inpt -> getRow()) && $cols['lngId'] != Lang::id());

	lib( array( 'Search'));
	isset( $srch) or $srch = new Search( Module::$opt[ 'id']);
	
	empty( $cols['query']) or $srchSQL = '`rltdId` IN ( '. $srch -> getIdsSQL( $cols['query'], Lang::viewId()) .')';
	empty( $cols['cat']) or $srchSQL .= ' AND `catId` = '. intval( $cols['cat']);

	return $srchSQL;
?>
