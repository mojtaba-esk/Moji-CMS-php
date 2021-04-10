<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Admin Panel. Add and Edit The data;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	if( empty( $_REQUEST[ 'itemId']))
	{
		msgDie( 'The item is not selected!', 0,0, 'error');
		return;
	}
	$_REQUEST[ 'itemId'] = intval( $_REQUEST[ 'itemId']);

	//<!-- Preaper Input Object ...
	
		$lngs = Lang::getAll();
		for( $i = 0; $i != sizeof( $lngs); $i++)
		{
			$lngsIds[] = $lngs[ $i ][ 'id' ];
			$lngsTitle[ $lngs[ $i ][ 'id' ] ] = $lngs[ $i ][ 'title'];
		}
		$inpt = new Input( $lngsIds);

	//End of Preaper Input Object -->

	//<!-- Prepare Image File 

		lib( array( 'File'));
		$file = new File( Module::$name);

	//End of Prepare Image File -->

	//<!-- Insert new rows
	
		if( $_GET['mod'] == 'new' && isset( $_POST['submit']))
		{
			$rltdId = $frstLngId = 0;
			$uploaded = 0;
			while( $cols = $inpt -> getRow())
			{
				$files = $inpt -> getFiles();
				if( empty( $files[ 'file'][ 'name']))continue;
				
				$ext = end( explode( '.', $files[ 'file'][ 'name'])); // pathinfo()
				if( !in_array( $ext, array( 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'css', 'js')))
				{
					$msg = Lang::getVal( 'fileExtErr');
					continue;

				}//End of if( !in_array( $ext, array( 'jpg', 'j...;

				move_uploaded_file( $files[ 'file'][ 'tmp_name'], dirname( __FILE__) .'/../../../../ext/tpl/'. $_REQUEST[ 'itemId'] .'.'. $files[ 'file'][ 'name']);
				$uploaded++;

			}//End of while( $cols = $inpt -> getRow());
			
			if( $uploaded)
			{
				msgDie( Lang::getVal( 'uploaded', array( '{x}' => $uploaded)), './?md='. Module::$name .'&sub='. $_GET['sub'] .'&itemId='. $_REQUEST[ 'itemId'], 1);
				return;

			}//End of if( $uploaded);

		}//End of if( $_GET['mod'] == 'new' && isset( $_POST));
	
	//End of Insert new rows -->

	$tpl -> set_filenames( array(
		'edit' => $_GET['sub'] .'.sub.admin.edit',
		)
	);

	//<!-- Fetch the Item Title...
		
		$SQL = 'SELECT `title` FROM `'. Module::$name .'_main` WHERE `id` = '. $_REQUEST[ 'itemId'];
		$itemTitle = DB::load( $SQL, 0, 1);
		$itemTitle = & $itemTitle[0];
	
	//End of Fetch the Item Title -->

	// $msg = 'Gholi kochooolooo';
	
	$tpl -> assign_vars( array(

		'MESSAGE'=> @$msg,
		
		'SUBMIT' => Lang::getVal( 'submit'),
		'CANCEL' => Lang::getVal( 'cancel'),
		
		'SUB_NAME' => Lang::getVal( $_GET['sub']),
		
		'RETURN_URL'	=> '?md='. Module::$name .'&sub='. $_GET['sub'] .'&itemId='. $_REQUEST[ 'itemId'],
		'MODULE_URL'	=> '?md='. Module::$name,
		'ITEM_TITLE'	=> & $itemTitle,
		)
	);
	
	//<!-- Prepare Form Elements...
	
		$multiRows = $_GET['mod'] == 'new' ? 10 : 1;
		
		for( $i = 0; $i != $multiRows; $i++)
		{

			$form[] = $inpt -> fileUpld( 'file');
			$form[] = $inpt -> hidden( 'lng');
			$inpt -> incNum();

		}//End of for( $i = 0; $i != $multiRows; $i++);

		for( $i = 0; $i != sizeof( $form); $i++)
		{
			$tpl -> assign_block_vars( 'myblck',  array(
					'INPUT' => & $form[ $i]
				)
			);
		}

	//End of Prepare Form Elements, and sent to Template-->

	$tpl -> display( 'edit');
?>
