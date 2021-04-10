<?php
/**
* @author Mojtaba Eskandari
* @since 2010-Oct-21
* @name Module Admin Panel permission array;
*/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

	return array(

				'access' => array( 
					'ownDataOnly'			=> 1,
				),

				'mod' => array(
					'lst'	=>	1,
					'view'	=>	1,
					'del'	=>	1,
					'edt'	=>	1,
					'new'	=>	1,
					'logs'	=>	1,
				),

			);

?>
