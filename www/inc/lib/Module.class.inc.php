<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-06
* @name Module Class.
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

class Module
{

	private	static $data = NULL;
	private	static $planId = 0;
	private	static $domId = 0;
	public	static $name = NULL;
	public	static $opt	 = NULL;
	
	/*-----------------------------------------------*/
	
	public static function isExist( $mdName, $planId = 0, $domId = 0)
	{
		Module::$name	= $mdName;
		Module::$planId	= $planId;
		Module::$domId	= $domId;

		Module::$data or Module::load();
		return isset( Module::$data[ $mdName ][ 'options' ]) && Module::$opt = & Module::$data[ $mdName ][ 'options' ];
		//return !empty( Module::$data[ $mdName ][ 'options' ]) && Module::$opt = & Module::$data[ $mdName ][ 'options' ];
	}
	
	/*-----------------------------------------------*/

	private static function load()
	{
		$prfx = Module::$planId ? ( 'p'. Module::$planId) : ( 'd'. Module::$domId);
		if( Module::$data = Cache::getData( 'modules_'. $prfx . Module::$name)) return;
		
		$modulsRws = DB::load( 
							array( 
								'tableName' => 'modules',
								'where' => array(
									'name' => & Module::$name
								)
							)
						);

		$mdRw = & $modulsRws[0];

		if( Module::$planId)
		{
			$mdRws = DB::load( 
							array( 
								'tableName' => 'domains_plans',
								'where' => array(
									'rltdId' => & Module::$planId
								)
							)
						);
			$optns = unserialize( $mdRws[0]['options']);
			Module::$data[ $mdRw['name']]['options'] = @$optns['md'][ $mdRw['id'] ][ 'options'];

		}//End of if( Module::$planId);
		
		if( Module::$domId && empty( Module::$data[ $mdRw['name']]))
		{
			$mdRws = DB::load(
						array(
							'tableName' => 'domains_main',
							'where' => array(
								'rltdId' => & Module::$domId
							)
						)
					);
			$optns = unserialize( $mdRws[0]['options']);
			Module::$data[ $mdRw['name']]['options'] = @$optns['md'][ $mdRw['id'] ][ 'options' ];

		}//End of if( Module::$domId && empty( Module::$data[ $mdRw['name']]));
		
		//if( Module::$domId === 0) // For Administration use... (Commented temporarily)
		{
			Module::$data[ $mdRw['name']] = array(
				'id' => $mdRw[ 'id'],
				'options' => array_combine( explode( ',', $mdRw[ 'options']), explode( ',', $mdRw[ 'values'])),
			);

		}//End of if( Module::$domId === 0);

		empty( Module::$data[ $mdRw['name']]['options']) or Module::$data[ $mdRw['name']][ 'options' ][ 'id' ] = $mdRw[ 'id'];

		if( defined( 'DEVELOPER_MODE'))
		{
			Module::$data[ 'developer'] = array(
					'id'		=> 0,
					'options'	=> array( 'id' => 0),
				);

		}//End of if( defined( 'DEBUG_MODE'));
		
		//printr( Module::$data);
		
		Cache::putFile( 'modules_'. $prfx . Module::$name, Cache::arrToSrc( Module::$data));
		return;
	}

	/*-----------------------------------------------*/
	
	public static function getId( $mdName)
	{
		$rw = DB::load(
			array( 
				'tableName' => 'modules',
				'cols' => array( 'id'),
				'where' => array(
					'name' => & $mdName
				)
			),
			0, 1
		);
		
		return $rw[0];
	}
	
	/*-----------------------------------------------*/

}//End of class Module;

?>
