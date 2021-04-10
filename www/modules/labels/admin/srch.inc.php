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
										$inpt -> hiddenRqst( array( 'vLng', 'md', 'lng'))
		)
	);
	
	if( !isset( $_GET['srch'])) return '1';

	$srchSQL = '1';

	while( ( $cols = $inpt -> getRow()) && $cols['lngId'] != Lang::id());

	//empty( $cols['key']) or $srchSQL .= " AND `key` LIKE '%{$cols['key']}%' ";
	empty( $cols['query']) or $srchSQL .= " AND ( `m`.`value` LIKE '%{$cols['query']}%' OR `n`.`value` LIKE '%{$cols['query']}%')";

	return $srchSQL;
?>
