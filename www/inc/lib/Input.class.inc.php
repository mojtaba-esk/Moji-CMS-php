<?php
/**
* @author Mojtaba Eskandari
* @since 2009-02-10
* @name HTML Inputs Class.
*/

defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

class Input
{
	public $tmpltNo;
	public $lblLang; // Lables' titles translated if this is set to true
	
	public $shwQuota; // This shows a progress bar for clarifying how quota is remained for the current domain.

	private $num;
	private $prfx;
	private $lngIds;
	
	private $rwPtr;
	private $lngPtr;
	private $fRwPtr;
	private $fLngPtr;

	/*-----------------------------------------------*/
	
	public function Input( $lngIds = array(), $prfx = 'rws', $num = 0)
	{
		$this -> lngIds = & $lngIds;
		$this -> prfx = $prfx;
		$this -> num = $num;
		$this -> tmpltNo = 0;
		$this -> lblLang = true;

		$this -> shwQuota = true;
		
		$this -> rwPtr = $this -> lngPtr = $this -> fRwPtr = $this -> fLngPtr = 0;
	}
	
	/*-----------------------------------------------*/
	
	public function incNum()
	{
		$this -> num++;
	}

	/*-----------------------------------------------*/

	public function prValidate( $frmId, $prms = '')
	{
		global $_cfg;
		return '
		<script type="text/javascript">
		ldJS("'. $_cfg['URL'] .'ext/scr/jq.vldt.js", "$.validator", function(){
			$(document).ready(function(){
				$.extend($.validator.messages, {
					required:"'. Lang::getVal( 'validatorRequired') .'",
					email:"'. Lang::getVal( 'validatorEmail') .'",
					url:"'. Lang::getVal( 'validatorUrl') .'",
					digits:"'. Lang::getVal( 'validatorDigits') .'"
				});
				$("#'. $frmId .'").validate('. $prms .');
			});
		});
		</script>';

		//equalTo:"'. Lang::getVal( 'validatorEqualTo') .'"
	}

	/*-----------------------------------------------*/

	public function hiddenRqst( $lst = NULL, $fromQS = false)
	{
		$rslt = '';

		if( $fromQS) // From Query String...
		{
			preg_match_all( '/(\&?)([^\&]+)\=([^&]+)/', urldecode( $_SERVER['QUERY_STRING']), $matches);
			
			if( is_array( $fromQS)) $excldPtrn = '/^'. implode( '|^', $fromQS). '/';
			for( $i = 0; $i != sizeof( $matches[0]); $i++)
			{
				if( isset( $excldPtrn) && preg_match( $excldPtrn, $matches[2][ $i ], $tmp)) continue;
				$rslt .= '<input type="hidden" name="'. $matches[2][ $i ] .'" value="'. @$matches[3][ $i ] .'" />';

			}//End of for( $i = 0; $i != sizeof( $matches[0]); $i++);

		}//End of if( $fromQS);

		$lst or $lst = array_keys( $_REQUEST);
		foreach( $lst as $key)
		{
			$rslt .= '<input type="hidden" name="'. $key .'" value="'. @$_REQUEST[ $key ] .'" />';
		}

		return $rslt;
	}

	/*-----------------------------------------------*/
	
	public function hidden( $title, $lngId = 0, $value = NULL, $optns = array())
	{
		$rslt = '';
		if( is_array( $lngId))
		{
			$attr = '';
			foreach( $optns as $k => $v)
			{
				$attr .= ' '. $k .'="'. $v .'"';

			}//End of foreach( $optns as $k => $v);

			foreach( $lngId as $id)
			{
				$rslt .= '<input type="hidden" '. $attr .' id="'. $this -> prfx .'_'. $this -> num .'_'. $id .'_'. $title .'" name="'. $this -> prfx .'['. $this -> num .']['. $id .']['. $title .']" value="'. @$_REQUEST[ $this -> prfx ][ $this -> num][ $id ][ $title] .'" />';
			}

		}else{
		
			empty( $optns['id']) and $optns['id'] = $this -> prfx .'_'. $this -> num .'_'. $lngId .'_'. $title;
			
			$attr = '';
			foreach( $optns as $k => $v)
			{
				$attr .= ' '. $k .'="'. $v .'"';

			}//End of foreach( $optns as $k => $v);

			$value = $value === NULL ? @$_REQUEST[ $this -> prfx ][ $this -> num][ $lngId ][ $title] : $value;
			$rslt = '<input type="hidden" '. $attr .' name="'. $this -> prfx .'['. $this -> num .']['. $lngId .']['. $title .']" value="'. $value .'" />';

			if( !empty( $optns['jsVal']))
			{
				$rslt .= '<script type="text/javascript">
					var smbF = $e("'. $optns['id'] .'").form.onsubmit;
					$e("'. $optns['id'] .'").form.onsubmit = function(e){
						$e("'. $optns['id'] .'").value = '. $optns['jsVal'] .';
						if( smbF) return smbF(e);
					}
				</script>';
			
			}//End of if( !empty( $optns['jsVal']));
		
		}//End of if( is_array( $lngId));
		
		return $rslt;
	}

	/*-----------------------------------------------*/
	
	public function html( $title = NULL, $value = NULL)
	{
		return $this -> tmplt( $title, $value, NULL);

	}//End of function html( $title, $value = NULL);

	/*-----------------------------------------------*/
	
	public function getVal( $title, $lngId = 0)
	{
		return @$_REQUEST[ $this -> prfx ][ $this -> num][ $lngId ][ $title];
	}
	
	/*-----------------------------------------------*/

	public function text( $title, $lngId = 0, $optns = NULL, $useTmplt = true)
	{

		isset( $optns['type'])	or $optns['type'] = 'text';
		isset( $optns['name'])	or $optns['name'] = $this -> prfx .'['. $this -> num .']['. $lngId .']['. $title .']';
		isset( $optns['value'])	or $optns['value'] = @$_REQUEST[ $this -> prfx ][ $this -> num][ $lngId ][ $title];
		$validate = @$optns['validate'];unset( $optns['validate']);
		$validate .= ' '. @$optns['class'];unset( $optns['class']);

		$attribs = '';
		foreach( $optns as $name => $value)
		{
			$attribs .= "$name=\"$value\" ";
		}
		
		$id	= $this -> prfx .'_'. $this -> num .'_'. $lngId .'_'. $title;
		$rslt	= '<input class="txt '. $validate .'" '. $attribs .' id="'. $id .'" />';
		isset( $optns['focus'])	and 	$rslt .= $this -> focus( $id);
		return $useTmplt ? $this -> tmplt( $title, $rslt, $id) : $rslt;

	}//End of function text( $title, $lngId);

	/*-----------------------------------------------*/

	public function chkBx( $title, $lngId = 0, $optns = NULL, $useTmplt = true)
	{

		isset( $optns['name'])		or	$optns['name'] = $this -> prfx .'['. $this -> num .']['. $lngId .']['. $title .']';
		isset( $optns['checked'])	or	@$_REQUEST[ $this -> prfx ][ $this -> num][ $lngId ][ $title] == @$optns['value'] and $optns['checked'] = 'checked';
		isset( $optns['value'])		or	$optns['value']	= @$_REQUEST[ $this -> prfx ][ $this -> num][ $lngId ][ $title];

		$validate = @$optns['validate'];unset( $optns['validate']);
		$validate .= ' '. @$optns['class'];unset( $optns['class']);
		
		if( empty( $optns['checked'])) unset( $optns['checked']);
		if( !empty( $optns['defChecked']) && !isset( $_REQUEST[ $this -> prfx ][ $this -> num][ $lngId ][ $title])) $optns['checked'] = 'checked';

		$attribs = '';
		foreach( $optns as $name => $value)
		{
			$attribs .= "$name=\"$value\" ";
		}

		$attribs .= @$optns['disabled'] ? 'disabled="disabled"' : '';
		
		$id	= $this -> prfx .'_'. $this -> num .'_'. $lngId .'_'. $title;
		$rslt	= '<input type="checkbox" class="chk '. $validate .'" '. $attribs .' id="'. $id .'" />';
		isset( $optns['focus'])	and $rslt .= $this -> focus( $id);

		return $useTmplt ? $this -> tmplt( $title, $rslt, $id) : $rslt;

	}//End of function chkBx( $title, $lngId);

	/*-----------------------------------------------*/
	
	public function quataPrgrss( $optns = array())
	{
		global $_cfg;
		if( empty( $_cfg['domain']['id'])) return '';

		$dRws = DB::load(
			array( 
				'tableName' => 'domains_main',
				'cols' => array( 'quotaLimit', 'usedQuota'),
				'where' => array(
					'rltdId' => $_cfg['domain']['id'],
				),
			),
			false
		);

		if( empty( $dRws)) return '';
		if( $dRws[0]['quotaLimit'] == 0) return '';//Without any limitations

		//<!-- Extra Attributes...

			$attr = '';
			foreach( $optns as $name => $value)
			{
				$attr .= "$name=\"$value\" ";
			}

		//-->

		$usedPr = 100 * round( $dRws[0]['usedQuota'] / $dRws[0]['quotaLimit'], 2);

		return '
		<div class="qtPrgrs" '. $attr .'>
			<div class="txt">
				'. Lang::numFrm( Lang::getVal( 'qtUsage', array( '{r}' => round( ( $dRws[0]['quotaLimit'] - $dRws[0]['usedQuota']) / 1024, 2), '{a}' => $dRws[0]['quotaLimit'] / 1024))) .'
			</div>
			<div class="used" style="width:'. $usedPr .'%">
			</div>
		</div>';
	}
	
	/*-----------------------------------------------*/
	
	public function imgUpld( $title, $lngId = 0, $imgSrc = NULL, $useTmplt = true)
	{
		$id	= $title;
		$rslt = $this -> fileUpld( $title, $lngId, $imgSrc ? 'Image' : NULL, false);
		$imgSrc and $rslt .= ' <br /><img src="'. $imgSrc .'" />';

		return $useTmplt ? $this -> tmplt( $title, $rslt, $id) : $rslt;

	}//End of function imgUpld( $title, $useTmplt = true, $imgSrc = NULL);

	/*-----------------------------------------------*/

	public function fileUpld( $title, $lngId = 0, $fileName = NULL, $useTmplt = true)
	{
		$qt = '';
		if( $this -> shwQuota)
		{
			$this -> shwQuota = false;
			$qt = $this -> quataPrgrss();
			
		}//End of if( $this -> shwQuota);
		
		$id	= $title;
		$rslt = '<input type="file" class="file" id="'. $this -> prfx .'_'. $this -> num .'_'. $lngId .'_'. $title .'" name="'. $this -> prfx .'['. $this -> num .']['. $lngId .']['. $title .']" />';
		if( $fileName)
		{

			$rslt .= '['. $fileName .'] <input type="checkbox" name="'. $this -> prfx .'['. $this -> num .']['. $lngId .'][del_'. $title .']" />'. Lang::getVal( 'deleteThisFile');

		}//End of if( $fileName);

		return ( $useTmplt ? $this -> tmplt( $title, $rslt, $id) : $rslt). $qt;

	}//End of function fileUpld( $title, $fileName = NULL, $useTmplt = true);
	
	/*-----------------------------------------------*/
	
	public function textArea( $title, $lngId = 0, $optns = NULL, $useTmplt = true)
	{
		global $_cfg;
		static $xinhaLoadd = false;
		
		isset( $optns['name']) 	or $optns['name'] = $this -> prfx .'['. $this -> num .']['. $lngId .']['. $title .']';
		$value = isset( $optns['value']) ? $optns['value'] : @$_REQUEST[ $this -> prfx ][ $this -> num][ $lngId ][ $title];
		unset( $optns['value']);
		$validate = @$optns['validate'];unset( $optns['validate']);
		$validate .= ' '. @$optns['class'];unset( $optns['class']);

		$id = $this -> prfx .'_'. $this -> num .'_'. $lngId .'_'. $title;

		if( isset( $optns[ 'fck']))
		{
			is_array( $optns[ 'fck']) or $optns[ 'fck'] = array();

			isset( $optns[ 'fck']['path'])	or $optns[ 'fck']['path']	= '../';
			isset( $optns[ 'fck']['lang'])	or $optns[ 'fck']['lang']	= Lang::$info['shortName'];
			isset( $optns[ 'dir'])	or $optns[ 'dir'] = Lang::$info['dir'];
			  
			include_once( $optns[ 'fck']['path'] .'ext/fck/fckeditor.php');
			$fck = new FCKeditor( $optns['name']) ;
			$fck -> BasePath	=	$optns[ 'fck']['path'] .'ext/fck/';
			
			isset( $optns[ 'fck']['skin'])		or	$optns[ 'fck']['skin'] = 'silver';
			isset( $optns[ 'fck']['skin'])		and	$fck -> Config['SkinPath'] = $_cfg['URL'] .'ext/fck/editor/skins/'. $optns[ 'fck']['skin'] .'/';
			isset( $optns[ 'fck']['toolBar'])	and	$fck -> ToolbarSet = $optns[ 'fck']['toolBar'];

			$fck -> Config['AutoDetectLanguage']	= false ;
			$fck -> Config['DefaultLanguage']		= $optns[ 'fck']['lang'];
			$fck -> Config['ContentLangDirection']	= $optns[ 'dir'];

			$fck -> Height	= isset( $optns[ 'height']) ? $optns[ 'height'] : 450;

			$fck -> Value	=	$value;
			
			$rslt = $fck -> CreateHtml();

		}else{//else of if( isset( $optns[ 'fck']));

			$attribs = '';
			foreach( $optns as $key => $val)
			{
				$attribs .= "$key=\"$val\" ";
			}

			$rslt = '<textarea class="txtArea '. $validate .'" '. $attribs .' id="'. $id .'" >'. $value .'</textarea>';
			isset( $optns['focus'])	and 	$rslt .= $this -> focus( $id);

		}//End of if( isset( $optns[ 'fck']));

		return $useTmplt ? $this -> tmplt( $title, $rslt, $id) : $rslt;

	}//End of function textArea( $title, $optns = NULL, $lngId = 0);

	/*-----------------------------------------------*/

	public function dropDown( $title, $lngId = 0, $optns = NULL, $useTmplt = true)
	{
		isset( $optns['attribs']['name'])  or $optns['attribs']['name'] 	= $this -> prfx .'['. $this -> num .']['. $lngId .']['. $title .']';
		isset( $optns['attribs']['value']) or $optns['attribs']['value']	= @$_REQUEST[ $this -> prfx ][ $this -> num][ $lngId ][ $title];
		$validate = @$optns['validate'];unset( $optns['validate']);
		$validate .= ' '. @$optns['class'];unset( $optns['class']);

		$optnsTag = $attribs = '';
		if( is_array( $optns))
		{
			isset( $optns['defltItem']) and $optnsTag .= "<option selected=\"selected\" value=\"{$optns['defltItem']['key']}\">{$optns['defltItem']['value']}</option>";
			if( is_array( $optns['items']))
			{
				foreach( $optns['items'] as $key => $value)
				{
					$selected = isset( $optns['attribs']['value']) && $optns['attribs']['value'] == $key ? 'selected="selected"' : '';
					$optnsTag .= "<option $selected value=\"$key\">$value</option>";
				}
			}

		}//End of if( is_array( $optns));

		foreach( $optns['attribs'] as $name => $value)
		{
			$attribs .= "$name=\"$value\" ";
		}

		$id = $this -> prfx .'_'. $this -> num .'_'. $lngId .'_'. $title;
		$rslt = '<select '. $attribs .' class="slct '. $validate .'" id="'. $id .'">
					'. $optnsTag .'
				</select>';
		isset( $optns['focus'])	and 	$rslt .= $this -> focus( $id);
		
		return $useTmplt ? $this -> tmplt( $title, $rslt, $id) : $rslt;

	}//End of function textArea( $title, $optns = NULL, $lngId = 0);

	/*-----------------------------------------------*/
	
	public function chkBxGrp( $title, $lngId = 0, $optns = NULL, $useTmplt = true)
	{
		$validate = @$optns['validate'];unset( $optns['validate']);
		$validate .= ' '. @$optns['class'];unset( $optns['class']);
		
		$rslt = '';
		if( is_array( $optns))
		{
			if( is_array( $optns['items']))
			{
				if( @$optns['chkAll'])
				{
					$rslt .= '<input type="checkbox" name="'. $this -> prfx .'_'. $this -> num .'_'. $lngId .'_'. $title .'_chkAll" id="'. $this -> prfx .'_'. $this -> num .'_'. $lngId .'_'. $title .'_" onclick="selectAllChks(this);" />';
					$rslt .= ' <label class="chkAll" for="'. $this -> prfx .'_'. $this -> num .'_'. $lngId .'_'. $title .'_">'. Lang::getVal( 'chkAll') .'</label>'. @$optns['delimiter'];
				}

				$idPstFx = 0;
				foreach( $optns['items'] as $key => $value)
				{
					$name = $this -> prfx .'['. $this -> num .']['. $lngId .']['. $title .']['. $key .']';
					$id = $this -> prfx .'_'. $this -> num .'_'. $lngId .'_'. $title .'_'. ( $idPstFx++ );

					$isChkd = isset( $optns['values']) && 
								in_array( $key, $optns['values']) || 
									isset( $_REQUEST[ $this -> prfx ][ $this -> num][ $lngId ][ $title]) && 
										is_array( $_REQUEST[ $this -> prfx ][ $this -> num][ $lngId ][ $title]) && 
											isset( $_REQUEST[ $this -> prfx ][ $this -> num][ $lngId ][ $title][ $key ]);
					$checked = $isChkd ? 'checked="checked"' : '';
					$disabled = @$optns['disabled'] ? 'disabled="disabled"' : '';

					$rslt .= '<input type="checkbox" '. $checked .' '. $disabled .' class="chk '. $validate .'" name="'. $name .'" id="'. $id .'" />';
					$rslt .= ' <label for="'. $id .'">'. $value .'</label>'. @$optns['delimiter'];

				}//End of foreach( $optns['items'] as $key => $value);
			}

		}//End of if( is_array( $optns));

		return $useTmplt ? $this -> tmplt( $title, $rslt, '') : $rslt;

	}//End of function textArea( $title, $optns = NULL, $lngId = 0);
	
	/*-----------------------------------------------*/
	
	public function rdoBx( $title, $lngId = 0, $optns = NULL, $useTmplt = true)
	{
		$validate = @$optns['validate'];unset( $optns['validate']);
		$validate .= ' '. @$optns['class'];unset( $optns['class']);
		isset( $optns['name'])  or $optns['name'] = $this -> prfx .'['. $this -> num .']['. $lngId .']['. $title .']';
		
		$rslt = '';
		if( is_array( $optns))
		{
			if( is_array( $optns['items']))
			{
				$idPstFx = 0;
				foreach( $optns['items'] as $key => $value)
				{
					$id = $this -> prfx .'_'. $this -> num .'_'. $lngId .'_'. $title .'_'. ( $idPstFx++ );

					$isChkd = isset( $optns['values']) && 
								in_array( $key, $optns['values']) || 
									isset( $_REQUEST[ $this -> prfx ][ $this -> num][ $lngId ][ $title]) && 
										is_array( $_REQUEST[ $this -> prfx ][ $this -> num][ $lngId ][ $title]) && 
											isset( $_REQUEST[ $this -> prfx ][ $this -> num][ $lngId ][ $title][ $key ]);
					$checked = $isChkd ? 'checked="checked"' : '';
					$disabled = @$optns['disabled'] ? 'disabled="disabled"' : '';

					$rslt .= '<input type="radio" '. $checked .' '. $disabled .' class="rdo '. $validate .'" value="'. $key .'" name="'. $optns['name'] .'" id="'. $id .'" />';
					$rslt .= ' <label for="'. $id .'">'. $value .'</label>'. @$optns['delimiter'];

				}//End of foreach( $optns['items'] as $key => $value);
			}

		}//End of if( is_array( $optns));

		return $useTmplt ? $this -> tmplt( $title, $rslt, '') : $rslt;

	}//End of function rdoBx( $title, $optns = NULL, $lngId = 0);	

	/*-----------------------------------------------*/	

	public function getRow( $rst = 0)
	{
		$rst and $this -> rwPtr = $this -> lngPtr = 0;
		
		if( !isset( $this -> lngIds[ $this -> lngPtr]))
		{
			$this -> rwPtr++;
			$this -> lngPtr = 0;
		}

		if( !isset( $_REQUEST[ $this -> prfx ][ $this -> rwPtr])) return NULL;
		
		$_REQUEST[ $this -> prfx ][ $this -> rwPtr][0][ 'lngId'] = $this -> lngIds[ $this -> lngPtr];
		isset( $_REQUEST[ $this -> prfx ][ $this -> rwPtr][ $this -> lngIds[ $this -> lngPtr]]) or $_REQUEST[ $this -> prfx ][ $this -> rwPtr][ $this -> lngIds[ $this -> lngPtr]] = array();

		//return  $this -> cnvPrsian( array_merge( $_REQUEST[ $this -> prfx ][ $this -> rwPtr][0], $_REQUEST[ $this -> prfx ][ $this -> rwPtr][ $this -> lngIds[ $this -> lngPtr++]]));
		return  $this -> cnvPrsian( $_REQUEST[ $this -> prfx ][ $this -> rwPtr][0]) + $this -> cnvPrsian( $_REQUEST[ $this -> prfx ][ $this -> rwPtr][ $this -> lngIds[ $this -> lngPtr++]]);
	}
	
	/*-----------------------------------------------*/	

	public function dbClr( & $s, $html = false)
	{
		if( $html) return str_replace( "'", "''", htmlspecialchars( $s));
		return str_replace( "'", "''", $s);
	}

	/*-----------------------------------------------*/

	public function getFiles( $rst = 0)
	{
		$rst and $this -> fRwPtr = $this -> fLngPtr = 0;
		if( !isset( $_FILES[ $this -> prfx ]['name'][ $this -> fRwPtr])) return NULL;
		
		//if( $lngPtr && !isset( $_FILES[ $this -> prfx ]['name'][ $rwPtr][ @$this -> lngIds[ $lngPtr]]))
		if( !isset( $this -> lngIds[ $this -> fLngPtr]))
		{
			$this -> fRwPtr++;
			$this -> fLngPtr = 0;
		}
		
		$rslt = NULL;

		$lngId = 0;	//Common files. 
		for( $i = 0; $i != 2; $i++)
		{
			if( isset( $_FILES[ $this -> prfx ]['name'][ $this -> fRwPtr][ $lngId]))
			{
				foreach( $_FILES[ $this -> prfx ]['name'][ $this -> fRwPtr][ $lngId] as $elmName => $name)
				{
					$rslt[ $elmName ] = array(
						'lngId'	=> $lngId,
						'name'	=> $name,
						'type'	=> $_FILES[ $this -> prfx ]['type'][ $this -> fRwPtr][ $lngId][ $elmName ],
						'error'	=> $_FILES[ $this -> prfx ]['error'][ $this -> fRwPtr][ $lngId][ $elmName ],
						'size'		=> $_FILES[ $this -> prfx ]['size'][ $this -> fRwPtr][ $lngId][ $elmName ],
						'tmp_name'	=> $_FILES[ $this -> prfx ]['tmp_name'][ $this -> fRwPtr][ $lngId][ $elmName ],
					);

				}//End of foreach( $_FILES[ $this -> prfx ]['name'][ $this -> fRwPtr][ $lngId] as $elmName => $name);
			
			}//End of if( isset( $_FILES[ $this -> prfx ]['name'][ $this -> fRwPtr][ $lngId]));
			
			$lngId = @$this -> lngIds[ $this -> fLngPtr++];
		
		}//End of for( $i = 0; $i != 2; $i++);
		
		return $rslt;
	}
	
	/*-----------------------------------------------*/
	
	/**
	 * @desc This Function used for Edit Mode. Set The Retrived values into $_REQUEST Array.( Input Class used this Array);
	 * */
	public function setVals( & $rws, $rst = 0)
	 {
		static $rwPtr = -1;
		$rst and $rwPtr = -1; //Reset The Pointer;
		
		if( !is_array( $rws)) return false;
		
		$_REQUEST[ $this -> prfx ][ ++$rwPtr][0] = & $rws[0];
		
		if( !isset( $rws[0][ 'lngId'])) return true;
		
		for( $i = 0; $i != sizeof( $rws); $i++)
		{
			$_REQUEST[ $this -> prfx ][ $rwPtr][ $rws[ $i][ 'lngId']] = & $rws[ $i];
		}
		return true;
	}
	
	/*-----------------------------------------------*/
	
	public function tmplt( $title, $value = '', $id = '')
	{
		if( $this -> tmpltNo == 0)
			return '
			<tr>
				<td class="label">
					<label for="'. $id .'">
						'. ( $title ? ( $this -> lblLang ? Lang::getVal( $title): $title) : '&nbsp;') .'
					</label>
				</td>
				<td>
					'. $value .'
				</td>
			</tr>';

		//if( $this -> tmpltNo == 1)
		return '
			<div class="input">
				<label for="'. $id .'">
					'. ( $title ? Lang::getVal( $title) : '&nbsp;') .': 
				</label>
				'. $value .'
			</div>';
	}

	/*-----------------------------------------------*/
	/**
	* @desc Convert KEH( with Hamzeh) & YEH( with under dot) to normal Persian KEH & YEH
	*/
	public function cnvPrsian( & $str)
	{
		if( is_array( $str))
		{
			foreach( $str as $key => $val)
			{
				$str[ $key] = $this -> cnvPrsian( $val);
			}
			return $str;

		}//End of if( is_array( $str));

		$arY = chr( hexdec( 'D9')) . chr( hexdec( '8A'));
		$arK = chr( hexdec( 'D9')) . chr( hexdec( '83'));
		$faY = chr( hexdec( 'DB')) . chr( hexdec( '8C'));
		$faK = chr( hexdec( 'DA')) . chr( hexdec( 'A9'));
		return str_replace( array( $arY, $arK), array( $faY, $faK), $str);
	}

	/*-----------------------------------------------*/
	
	public function date( $title, $lngId = 0, $optns = NULL, $useTmplt = true)
	{
		$validate = @$optns['validate'];unset( $optns['validate']);
		$validate .= ' '. @$optns['class'];unset( $optns['class']);
		
		$name = isset( $optns['attribs']['name']) ? $optns['attribs']['name'] : $this -> prfx .'['. $this -> num .']['. $lngId .']['. $title .']';
		
		isset( $optns['sprtr']) or $optns['sprtr']	= ' ';//Separator.
		isset( $optns['type']) or $optns['type']	= NULL;//Type of date; Jalali or Gregorian or...

		isset( $optns['value']) or $optns['value']	= @$_REQUEST[ $this -> prfx ][ $this -> num][ $lngId ][ $title];
		$optns['value'] or isset( $optns['defVal']) and $optns['value'] = $optns['defVal'];
		$optns['value'] == 'now' and $optns['value'] = time();

		if( is_numeric( $optns['value']) && !empty( $optns['value']))
		{
			$value = $optns['value'];
			$optns['value'] = array();
			list( $optns['value']['Y'], $optns['value']['m'], $optns['value']['d'], $optns['value']['G'], $optns['value']['i']) = explode( ',', Date::get( 'Y,m,d,G,i', $value, $optns['type']));
		}

		empty( $optns['value']['Y']) and $defYear = Date::get( 'Y', time(), $optns['type']);
		isset( $optns['difY']) or $optns['difY'] = 10;
		is_array( $optns['difY']) or $optns['difY'] = array( ( -1) * $optns['difY'] , $optns['difY'] );
		isset( $defYear) or $defYear = $optns['value']['Y'];
		isset( $optns['yearRng']) and is_array( $optns['yearRng']) or $optns['yearRng'] = array( $defYear + $optns['difY'][0], $defYear + $optns['difY'][1]);
		
		if( defined( 'DEBUG_MODE') && $optns['yearRng'][0] > $optns['yearRng'][1])
		{
			printr( '<b>Fatal Error!</b><br />The start of year Range is grater than end year!');
			printr( $optns['yearRng']);
			return false;
		}
		
		$rslt = $attribs = '';
		foreach( $optns['attribs'] as $key => $val)
		{
			$attribs .= "$key=\"$val\" ";
		}
		
		if( isset( $optns['elmnts']))
		{
				$optns['elmnts'] = explode( ',', $optns['elmnts']);
				$id = $this -> prfx .'_'. $this -> num .'_'. $lngId .'_'. $title;

				foreach( $optns['elmnts'] as $elmnt)//For save order. use this loop.
				{
					$opnTag = '<select '. $attribs .' name="'. $name .'['. $elmnt .']" class="slct '. $validate .'" id="'. $id .'_'. $elmnt .'">';
					
					if( $elmnt == 'Y')//Year
					{
						$rslt .= Lang::getVal( 'year'). ': '. $opnTag .'<option value="0"></option>';
						for( $i = $optns['yearRng'][1]; $i >= $optns['yearRng'][0]; $i--)
						{
							$selected = @$optns['value']['Y'] == $i ? 'selected="selected"' : '';
							$rslt .= "<option $selected value=\"$i\">". Lang::numFrm( $i) ."</option>";
						}
						$rslt .= '</select>'. $optns['sprtr'];
						continue;

					}//End of if( $elmnt == 'Y');
					
					if( $elmnt == 'm')//Month by Number
					{
						$rslt .= Lang::getVal( 'month'). ': '. $opnTag .'<option value="0"></option>';
						for( $i = 1; $i != 13; $i++)
						{
							$selected = @$optns['value']['m'] == $i ? 'selected="selected"' : '';
							$rslt .= "<option $selected value=\"$i\">". Lang::numFrm( $i) ."</option>";
						}
						$rslt .= '</select>'. $optns['sprtr'];
						continue;

					}//End of if( $elmnt == 'm');

					if( $elmnt == 'M')//Month By Name
					{
						empty( $optns['value']['M']) or $optns['value']['m'] = $optns['value']['M'];
						$rslt .= Lang::getVal( 'month'). ': '. $opnTag .'<option value="0"></option>';
						for( $i = 1; $i != 13; $i++)
						{
							$selected = @$optns['value']['m'] == $i ? 'selected="selected"' : '';
							$rslt .= "<option $selected value=\"$i\">". Lang::getVal( 'month_'. $i) ."</option>";
						}
						$rslt .= '</select>'. $optns['sprtr'];
						continue;

					}//End of if( $elmnt == 'M');
					
					if( $elmnt == 'd')//Day
					{
						$rslt .= Lang::getVal( 'day'). ': '. $opnTag .'<option value="0"></option>';
						for( $i = 1; $i != 32; $i++)
						{
							$selected = @$optns['value']['d'] == $i ? 'selected="selected"' : '';
							$rslt .= "<option $selected value=\"$i\">". Lang::numFrm( $i) ."</option>";
						}
						$rslt .= '</select>'. $optns['sprtr'];
						continue;

					}//End of if( $elmnt == 'd');
					
					if( $elmnt == 'G')//Hour
					{
						$rslt .= Lang::getVal( 'hour'). ': '. $opnTag .'<option value=""></option>';
						for( $i = 0; $i != 24; $i++)
						{
							$selected = @$optns['value']['G'] == $i ? 'selected="selected"' : '';
							$rslt .= "<option $selected value=\"$i\">". Lang::numFrm( $i) ."</option>";
						}
						$rslt .= '</select>'. $optns['sprtr'];
						continue;

					}//End of if( $elmnt == 'G');
					
					if( $elmnt == 'i')//minute
					{
						$rslt .= Lang::getVal( 'minute'). ': '. $opnTag .'<option value=""></option>';
						for( $i = 0; $i != 60; $i++)
						{
							$selected = @$optns['value']['i'] == $i ? 'selected="selected"' : '';
							$rslt .= "<option $selected value=\"$i\">". Lang::numFrm( $i) ."</option>";
						}
						$rslt .= '</select>'. $optns['sprtr'];
						continue;

					}//End of if( $elmnt == 'i');
				
				}//End of foreach( $optns['elmnt'] as $elmnt);

		}//End of if( is_array( $optns));

		return $useTmplt ? $this -> tmplt( $title, $rslt, $id) : $rslt;
	}
	
	/*-----------------------------------------------*/
	
	public function captcha( $title, $lngId = 0, $optns = NULL, $useTmplt = true)
	{
		global $_cfg;

		isset( $optns['name']) or $optns['name'] 	= $this -> prfx .'['. $this -> num .']['. $lngId .']['. $title .']';
		isset( $optns['maxlength']) or $optns['maxlength']	=	6;
		isset( $optns['size']) or $optns['size']	=	10;
		isset( $optns['imgId']) or $optns['imgId']	=	Module::$opt['id'];

		$attribs = '';
		foreach( $optns as $name => $value)
		{
			$attribs .= "$name=\"$value\" ";
		}

		$id		= $this -> prfx .'_'. $this -> num .'_'. $lngId .'_'. $title;
		$rslt	= '<input type="text" class="txt cptch required" '. $attribs .' id="'. $id .'" autocomplete="off" />';
		$rslt	.= '<img src="'. $_cfg['URL'] .'ext/P1.gif" id="imgCptch'. $title .'" class="cptch" onclick="CimgR(this.id);" title="'. Lang::getVal( 'clickForNewImg') .'" />';
		$rslt	.= '<script type="text/javascript">function CimgR(e){var u="'. $_cfg['URL'] .'";$e(e).src=u+"ext/P1.gif";$e(e).src=u+"cptch-'. $optns['imgId'] .'.jpg?"+(new Date().getTime());};CimgR("imgCptch'. $title .'");</script>';
		isset( $optns['focus'])	and 	$rslt .= $this -> focus( $id);

		return $useTmplt ? $this -> tmplt( $title, $rslt, $id) : $rslt;

	}//End of function captcha( $title, $lngId = 0, $optns = NULL, $useTmplt = true);

	/*-----------------------------------------------*/
	
	private function focus( $id)
	{
		static $shwd = 1;
		if( $shwd)
		{
			$shwd = 0;	
			return '<script type="text/javascript">$e("'. $id .'").focus();</script>';
		}	
		return '';
	}
	
	/*-----------------------------------------------*/

	public function fldSetOpn( $title, $useTmplt = true, $clpsbl = false)
	{
		static $id = 0;
		$id++;
		
		$clpsblCd = $clpsbl ? 'onclick="$(\'#clpsblfldset'. $this -> prfx . $id .'\').slideToggle();" class="clpsbl"' : '';
		$tblCd = $clpsbl == 'closed' ? 'style="display:none;"' : '';

		$rslt = '<fieldset><legend '. $clpsblCd .'>'. $title .'</legend>';
		return $useTmplt ? ( '<tr><td colspan="20">'. $rslt .'<table '. $tblCd .' id="clpsblfldset'. $this -> prfx . $id .'">') : $rslt;

	}//End of function fldSetOpn( $title, $useTmplt = true);
	
	/*------------------*/

	public function fldSetClos( $useTmplt = true)
	{

		$rslt = '</fieldset>';
		return $useTmplt ? ( '</table>'. $rslt .'</td></tr>') : $rslt;

	}//End of function fldSetClos( $useTmplt = true);
	
	/*-----------------------------------------------*/
	
	/* Example:
	* $inpt -> popup( 'clickHere', array(
	*						'inpt'	=>	'title', 						// Typically, a hidden input which is used by target page.
	*						'url'	=>	'?md=gholi&sub=users&mod=pop',	// Target page will be opened in popup.
	*						'w'		=>	500,							// Popup window's width in pixel
	*						'h'		=>	400,							// Popup window's height in pixel
	*						'prfx'	=>	'pop',							// Parameters' input object's Prefix ( the default value is set to this value)
	*	));
	*
	*	OR
	*
	* $inpt -> popup( 'clickHere', array(
	*						'inpt'	=>	array(
	*										'title', 
	*										 array(						// Each Item can be also an array
	*												'title' => 'name',	// 
	*												'lngId' => 1,		// Language Id can be specified individually
	*										), 
	*										'pblishTime'
	*									)
	*						'url'	=>	'?md=gholi&sub=users&mod=pop',	// Target page will be opened in popup.
	*						'w'		=>	500,							// Popup window's width in pixel
	*						'h'		=>	400,							// Popup window's height in pixel
	*						'prfx'	=>	'pop',							// Parameters' input object's Prefix ( the default value is set to this value)
	*	));
	*/
	
	public function popup( $title, $lngId = 0, $optns = array())
	{
		static $isLdd = 0;
		global $_cfg;
		
		isset( $optns['prfx']) or $optns['prfx'] = 'pop';
		
		$id	= $this -> prfx .'_'. $this -> num .'_'. $lngId .'_'. $title; // On the current page...

		//!<-- Preparing the URL and its parameters...

			$url = '"'. $optns['url'] .'"';
			if( isset( $optns['inpt']))
			{
				if( is_array( $optns['inpt']))
				{
					foreach( $optns['inpt'] as $in)
					{
						$inTitle = is_array( $in) && isset( $in['title']) ? $in['title'] : $in;
						$inLngId = is_array( $in) && isset( $in['lngId']) ? $in['lngId'] : $lngId;
						$inName = $optns['prfx'] .'['. $this -> num .']['. $inLngId .']['. $inTitle .']';

						$url .= '+"&'. $inName .'="+$e("'. $this -> prfx .'_'. $this -> num .'_'. $inLngId .'_'. $inTitle .'").value';
						$url .= '+"&ids['. $inTitle .'][]='. $this -> prfx .'_'. $this -> num .'_'. $inLngId .'_'. $inTitle .'"';
						// The HTML Ids should have been managed in a better way, but this solution satisfies us...

					}//End of foreach( $optns['inpt'] as $in);

				}else{

					$url .= '+"&'. $optns['prfx'] .'['. $this -> num .']['. $lngId .']['. $optns['inpt'] .']' .'="+$e("'. $this -> prfx .'_'. $this -> num .'_'. $lngId .'_'. $optns['inpt'] .'").value';
					$url .= '+"&ids['. $optns['inpt'] .'][]='. $this -> prfx .'_'. $this -> num .'_'. $lngId .'_'. $optns['inpt'] .'"';

				}//End of if( is_array( $optns['inpt']));

				//$url .= ';';

			}//End of if( isset( $optns['inpt']));

		//-->

		isset( $optns['w']) or $optns['w'] = 400;
		isset( $optns['h']) or $optns['h'] = 200;
		
		$out = '';
		if( !$isLdd)
		{
			$isLdd = 1;
			$out .= '<div id="popup" class="popup">
				<script type="text/javascript">
					function closePopup(){$("#popup").trigger("close");}
				</script>
		        <iframe id="popupFrm" src="about:blank" name="popupFrm" style="width:100%;height:100%;"> No inline frame support</iframe>
		        <a id="lghtbxCloseX" class="close sprited" href="#">[ '. Lang::getVal( 'close') .' ]</a>
		    </div>';

		}//End of if( !$isLdd);
            
        return $out .'
		    <script type="text/javascript">
				ldJS("'. $_cfg['URL'] .'ext/scr/jq.lghtbx.js", "$.fn.lightbox_me", function(){
					$(document).ready(function(){
						$e("'. $id .'").style.display="inline";
						$e("prld'. $id .'").style.display="none";
						$("#'. $id .'").click(function(e){
							$("#popup").lightbox_me({
								centered: true, 
								onLoad: function() {}
								});
							e.preventDefault();
							$e("popupFrm").src='. $url .';
							$e("popup").style.width="'. $optns['w'] .'px";
							$e("popup").style.height="'. $optns['h'] .'px";
						});
					});
				});
			</script>
			<img src="'. $_cfg['URL'] .'ext/P1.gif" id="prld'. $id .'" class="popup" alt="loading..." />
		    <a id="'. $id .'" class="popup pp_'. $title .'" href="#">'. Lang::getVal( $title) .'</a>';
	}
	
	/*-----------------------------------------------*/

}//End of class Input;

?>
