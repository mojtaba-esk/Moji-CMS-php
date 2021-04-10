<?php
/**
* @author Ghasem Babaie
* @since 2013-01-19
* @name Module Admin Panel. Add and Edit The data;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	lib( array(
			'Tab',
			'File',
			'Img',
			'Addable',
		)
	);
	
	//<!-- Preaper Input Object ...
	
		$lngs = Lang::getAll();
		//require( $_cfg['path'] .'/inc/lib/Input.class.inc.php');
		for( $i = 0; $i != sizeof( $lngs); $i++)
		{
			$lngsIds[] = $lngs[ $i ][ 'id' ];
			$lngsTitle[ $lngs[ $i ][ 'id' ] ] = $lngs[ $i ][ 'title'];
		}
		$inpt = new Input( $lngsIds);

	//End of Preaper Input Object -->

	$tpl -> set_filenames( array(
		'edit' => Module::$name .'.admin.edit',
		)
	);

	$mRws = DB::load(
		array( 
			'tableName' => Module::$name . '_main',
			'where' => array(
				'rltdId'	=>	intval( $_GET['id']),
				'lngId'		=>	Lang::viewId(),
				'domId'		=>	$_cfg['domain']['id'],
			),
		)
	);

	$mRw = & $mRws[0];
	
	$tpl -> assign_vars( array(

		'MESSAGE'=> @$msg,

		'SUBMIT' => NULL,	//Lang::getVal( 'submit'),
		'CANCEL' => Lang::getVal( 'cancel'),

		'RETURN_URL' => '?md='. Module::$name,
		'ACTION_TITLE'	=>	Lang::getVal( $_GET['mod']) .' [ '. $mRw['title'] .' ]',

		)
	);
	
	
	//<!-- Fetch the sum poll count...
		
		$SQL = 'SELECT
					SUM( `count`)	AS	`total`
				FROM
					`'. Module::$name .'_options`
				WHERE
					`itemId` = 0'. $mRw['rltdId'] . '
					AND
					`lngId` = '. Lang::viewId();
		$pollSum = DB::load( $SQL, 0, 1);
		$pollSum = $pollSum[0];
	
	//End of Fetch the sum poll count -->		

	$SQL = 'SELECT *
			FROM
				`'. Module::$name . '_options`
			WHERE
					`itemId`=	0'. $mRw['rltdId'] .'
				AND
					`lngId`	=	'. Lang::viewId() .'
			ORDER BY
				`ordrId`	ASC';
	$rws = DB::load( $SQL, Module::$name . $_cfg['domain']['id'] . '_options');

	printr( $rws);

	unset( $items); $items = array();
	for( $i = 0; $i != sizeof( $rws); $i++)
	{	
		$items[ $rws[$i]['rltdId'] ] = $rws[$i]['title'] .' ('. Lang::numFrm(  $rws[$i]['count']). ' ' . Lang::getVal( 'vote') . ' | ' .Lang::numFrm( round((($rws[$i]['count'] / $pollSum ) * 100), 2, PHP_ROUND_HALF_DOWN)). ' ' . Lang::getVal( 'percent') .')';
	}

	//<!-- Preparing Chart...
	
		$chart = '';
	
	
	//End of Chart-->
	
	$tpl -> assign_block_vars( 'myblck',  array(
			'INPUT' => & $chart
		)
	);

	$tpl -> display( 'edit');
?>
