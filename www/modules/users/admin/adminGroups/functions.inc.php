<?php
/**
* @author Mojtaba Eskandari
* @since 2009-10-13
* @name Module Admin Panel. Necessary functions;
*/

/*-----------------------------------------------*/
/*
* Make permissions Check box for sub modules...
*/
function mkPermissionChks( $mdPermissionsArr, $mdId, $permission)
{
	global $inpt;
	
	defined( 'DEBUG_MODE') and !isset( $inpt) and printr( 'Error!, The [ $inpt ] Object is not Exist! <br />In: '. __FILE__ . ', Line: '. __LINE__); 
	
	$rslt = '';
	foreach( $mdPermissionsArr as $name => $val)
	{
		
		if( is_array( $val))
		{
			$rslt .= $inpt -> html( $name, mkPermissionChks( $val, $mdId, $permission));
			//$rslt .= '&nbsp;&nbsp;'. Lang::getVal( $name) .'<br />&nbsp;&nbsp;'. mkPermissionChks( $val, $mdId, $permission);
			continue;
		}

		$rslt .= '&nbsp;&nbsp;'. $inpt -> chkBx( 
				$name,
				0,
				array( 
					'name'	=> 'permission['. $mdId .']['. $name .']',
					'value'	=> 1,
					'checked' => @$permission[ $mdId ][ $name ] ? 'checked' : NULL,
				),
				false // do not use template
			);
		$rslt .= Lang::getVal( $name);

	}//End of foreach( $mdPermissionsArr as $name => $val);
	
	return $rslt;
}

/*-----------------------------------------------*/

?>
