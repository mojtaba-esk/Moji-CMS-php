<?php
/**
* @author Mojtaba Eskandari
* @since 2010-02-20
* @name Module Admin Panel. Functions...
*/

return ;

// THIS FUNCTION is NOT Completed yet... and Dos Not Work.

function mkPermissionChksX( & $mdPermissionsArr, $mdId, & $values)
{
	global $inpt;
	
	defined( 'DEBUG_MODE') and !isset( $inpt) and printr( 'Error!, The [ $inpt ] Object is not Exist! <br />In: '. __FILE__ . ', Line: '. __LINE__); 
	
	if( !is_array( $mdPermissionsArr)) return '';
	
	$rs = '';
	
	foreach( $mdPermissionsArr as $name => $mdVal)
	{
		if( is_array( $mdVal))
		{
			return '&nbsp;&nbsp;'. Lang::getVal( $name) .'<br />&nbsp;&nbsp;'. mkPermissionChks( $mdVal, $mdId, $values);
		}
		
		$rs .= $form[] = $inpt -> chkBx( $name,
							0,
							array( 
								'name'	=> 'permission['. $mdId .']',
								'value'	=> 1,
								'checked' => @$permission[ $rw['id'] ] ? 'checked' : NULL,
							)
						);
	
	}//End of foreach( $mdPermissionsArr as $name => $val);

}

/*---------------------------------------------*/

?>
