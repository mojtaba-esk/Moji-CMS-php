<?php
/**
* @author Mojtaba Eskandari
* @since 2009-08-16
* @name Addable Class.
* @Comment This Class use Input Class.
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

class Addable
{
	private $inpts;	//Store the input Names and types, ( Input Class).
	private $vals = array();
	private $lngInfo;
	private $inpt = NULL;

	/*-----------------------------------------------*/
	
	public function Addable( & $lngInfo, $inpts, $prfx = 'add')
	{
		$this -> inpts	 = $inpts;
		$this -> lngInfo = & $lngInfo;
		$this -> inpt or $this -> inpt = new Input( array( 1), $prfx);
	}
	
	/*-----------------------------------------------*/
	
	public function add( $vals)
	{
		$this -> vals[] = $vals;
	}
	
	/*-----------------------------------------------*/

	public function getHTML()
	{
		$rslt = '';
		foreach( $this -> vals as $val)
		{
			foreach( $this -> inpts as $name => $type)
			{
				$rslt .= $this -> getElmnt( $name, $type, $val);
			}
			$this -> inpt -> incNum();

		}//End of foreach( $this -> vals as $val);
		
		return $rslt;
	}

	/*-----------------------------------------------*/
	
	private function getElmnt( & $name, & $type, $val)
	{
		if( $type == 'hidden')	return $this -> inpt -> hidden( $name, 0, $val[ $name ]);
		if( $type == 'imgUpld') return $this -> inpt -> imgUpld( $name, 0, $val[ $name ]);
		if( $type == 'fileUpld')	return $this -> inpt -> fileUpld( $name, 0, $val[ $name ]);
		
		is_array( $val[ $name ])	or $val[ $name ] = array( 'value' => $val[ $name ]);
		$val[ $name ][ 'dir']	= $this -> lngInfo['dir'];
		$val[ $name ][ 'align']	= $this -> lngInfo['align'];

		if( $type == 'text') return $this -> inpt -> text( $name, 0, $val[ $name ]);
		if( $type == 'textArea') return $this -> inpt -> textArea( $name, 0, $val[ $name ]);
		if( $type == 'dropDown') return $this -> inpt -> dropDown( $name, 0, $val[ $name ]);
		
		return $val[ $name ][ 'value' ];
	}
	
	/*-----------------------------------------------*/
	
	public function getRow( $rst = 0)
	{
		return $this -> inpt -> getRow( $rst);
	}
	
	/*-----------------------------------------------*/

	public function getFiles( $rst = 0)
	{
		return $this -> inpt -> getFiles( $rst);
	}
	
	/*-----------------------------------------------*/
}//End of class Input;

?>
