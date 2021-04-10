<?php
    /*******************************************************************************
    *                                                                             *
    * @version  sbpayment.php version 0.1                                         *
    * @copyright Copyright (c) 2008.                                              *
    * @license http://opensource.org/licenses/gpl-license.php GNU Public License. *
    * @author said shamspour  saidshp@yahoo.com.                                  *
    * Modified By Mojiz on Sat 06 Aug 2011 10:49:39 PM IRDT                       *
    *                                                                             *
    *******************************************************************************/

	defined( 'IN_MJY_CMS') or die( 'Rstrct Acs!');

    /*
      CREATE TABLE sbpayment (id INT(10) NOT NULL AUTO_INCREMENT,
                              res_num CHAR(20) NOT NULL,
                              ref_num CHAR(20) NOT NULL,
                              total_amont INT NOT NULL,
                              payment INT NOT NULL DEFAULT 0,
                              date_start INT(12) NOT NULL,
                              primary key(id),
                              unique(res_num),
                              index(ref_num));
    */

    class SBPayment
    {

        public $action  = 'https://acquirer.samanepay.com/Payment.aspx';//'https://acquirer.sb24.com/CardServices/controller';

        public $webMethodURL = 'https://acquirer.samanepay.com/payments/referencepayment.asmx?WSDL';//'https://acquirer.sb24.com/ref-payment/ws/ReferencePayment?WSDL';

        public $redirectURL = 'http//www.yourdomain.com/sbpayment.php';

        public $totalAmont;

        public $refNum;

        public $resNum;

        protected $payment;

        protected $merchantID;

        protected $password;

        protected $msg = array();

        protected $errorState = array(
        'Canceled By User'     => 'تراکنش بوسيله خريدار کنسل شده',
        'Invalid Amount'       => 'مبلغ سند برگشتي  از مبلغ تراکنش اصلي بيشتر است',
        'Invalid Transaction'  => 'درخواست برگشت تراکنش رسيده است در حالي که تراکنش اصلي پيدا نمي شود',
        'Invalid Card Number'  => 'شماره کارت اشتباه است',
        'No Such Issuer'       => 'چنين صادر کننده کارتي وجود ندارد',
        'Expired Card Pick Up' => 'از تاريخ انقضاي کارت گذشته است',
        'Incorrect PIN'        => 'رمز کارت اشتباه است pin',
        'No Sufficient Funds'  => 'موجودي به اندازه کافي در حساب شما نيست',
        'Issuer Down Slm'      => 'سيستم کارت بنک صادر کننده فعال نيست',
        'TME Error'            => 'خطا در شبکه بانکي',
        'Exceeds Withdrawal Amount Limit'      => 'مبلغ بيش از سقف برداشت است',
        'Transaction Cannot Be Completed'      => 'امکان سند خوردن وجود ندارد',
        'Allowable PIN Tries Exceeded Pick Up' => 'رمز کارت 3 مرتبه اشتباه وارد شده کارت شما غير فعال اخواهد شد',
        'Response Received Too Late'           => 'تراکنش در شبکه بانکي تايم اوت خورده',
        'Suspected Fraud Pick Up'              => 'اشتباه وارد شده cvv2 ويا ExpDate فيلدهاي'
        );

        protected $errorVerify = array(
        '-1'  => 'خطاي داخلي شبکه',
        '-2'  => 'سپرده ها برابر نيستند',
        '-3'  => 'ورودي ها حاوي کاراکترهاي غير مجاز ميباشد',
        '-4'  => 'کلمه عبور يا کد فروشنده اشتباه است',
        '-5'  => 'خطاي بانک اطلاعاتي',
        '-6'  => 'سند قبلا برگشت کامل خورده',
        '-7'  => 'رسيد ديجيتالي تهي است',
        '-8'  => 'طول ورودي ها بيشتر از حد مجاز است',
        '-9'  => 'وجود کارکترهاي غير مجاز در مبلغ برگشتي',
        '-10' => 'رسيد ديجيتالي حاوي کارکترهاي غير مجاز است',
        '-11' => 'طول ورودي ها کمتر از حد مجاز است',
        '-12' => 'مبلغ برگشتي منفي است',
        '-13' => 'مبلغ برگشتي براي برگشت جزيي بيش از مبلغ برگشت نخورده رسيد ديجيتالي است',
        '-14' => 'چنين تراکنشي تعريف نشده است',
        '-15' => 'مبلغ برگشتي به صورت اعشاري داده شده',
        '-16' => 'خطاي داخلي سيستم',
        '-17' => 'برگشت زدن تراکنشي که با کارت بانکي غير از بانک سامان انجام شده',
        '-18' => 'فروشنده نامعتبر است ip address'
        );

        public $style = array('TableBorderColor' => '',
                              'TableBGColor'     => '',
                              'PageBGColor'      => '',
                              'PageBorderColor'  => '',
                              'TitleFont'        => '',
                              'TitleColor'       => '',
                              'TitleSize'        => '',
                              'TextFont'         => '',
                              'TextColor'        => '',
                              'TextSize'         => '',
                              'TypeTextColor'    => '',
                              'TypeTextColor'    => '',
                              'TypeTextSize'     => '',
                              'LogoURI'          => ''
                              );

        function __construct( $mID = '', $pass = '')
        {
            $this->merchantID = $mID;
            $this->password   = $pass;

        }

        protected function createResNum()
        {
            do{
                $m = md5( microtime());
                $resNum = substr( $m, 0, 20);

                $SQL = "SELECT `res_num` FROM `sbpayment` WHERE `res_num` = '$resNum'";
                if( !DB::load( $SQL))break;

            }while( true );
            $this->resNum = $resNum;
        }

        protected function searchResNum( $resNum)
        {
            $SQL = "SELECT * FROM `sbpayment` WHERE `res_num` = '$resNum'";
            $rws = DB::load( $SQL);
            return $rws ? $rws[0] : false;
        }

        protected function searchRefNum( $refNum )
        {
            $SQL = "SELECT * FROM `sbpayment` WHERE `ref_num` = '$refNum'";
			$rws = DB::load( $SQL);
            return $rws ? $rws[0] : false;
        }

        protected function saveBankInfo( $payment )
        {
            $this->payment = $payment;
            $SQL = "UPDATE 
            			`sbpayment` 
            		SET 
            			`ref_num` = '{$this->refNum}',
            			`payment` = '$payment'
            		WHERE
            			`res_num` = '{$this->resNum}'";;

            DB::exec( $SQL);
            return DB::affctdRws();
        }

        public function saveStoreInfo( $totalAmont )
        {
            if( $totalAmont == '' ) {
                $this->setMsg( "Error: TotalAmont" );
                return false;
            }
            $time = time();
            $this->totalAmont = $totalAmont;
            $this->createResNum();
            $SQL = "INSERT INTO 
            			`sbpayment`
            		SET 
            			`res_num` = '{$this->resNum}',
            			`total_amont` = '{$this->totalAmont}',
            			`date_start` = $time";
            DB::exec( $SQL);
            return DB::affctdRws();
        }

         public function receiverParams( $resNum = '' , $refNum = '' ,$state = '' )
        {
            //if( ( empty($state) or empty($resNum) or strlen($refNum) != 20 ) or $state != 'OK' ) {
            if( ( empty($state) or empty($resNum) or strlen($refNum) != 30 ) or $state != 'OK' ) {
            	
            	printr( array( $resNum, $refNum, $state));
            	
                if(isset($this->errorState[$state])) {
                    $this->setMsg( 'state',$state );

                } else {
                    $this->setMsg("error state");
                }
                return false;
            }

            $searchResNum = $this->searchResNum( $resNum );

            if( is_array( $searchResNum ) ) {
                if( $searchResNum['payment'] > 0) {
                    $this->setMsg( "لطفا به قسمت رهگيري سفارش مراجعه کنيد" );
                    return false;
                }
            } else {
                $this->setMsg("همچين تراکنشي در سمت فروشنده تعريف نشده");
                return false;
            }

            $this->refNum     = $refNum;
            $this->resNum     = $resNum;
            $this->totalAmont = $searchResNum['total_amont'];

            return $this->lastCheck();
        }


        protected function lastCheck()
        {
            //if( empty($this->resNum) or strlen($this->refNum) != 20 ) {
            if( empty($this->resNum) or strlen($this->refNum) != 30 ) {
                $this->setMsg( "Error: resNum or refNum is empty" );
                return false;
            }
            //web method verify transaction
            $verify     = $this->verifyTrans();

            if( $verify > 0 ) {
                if( $verify == $this->totalAmont ) {

                    $this->saveBankInfo( $verify );
                    $this->setMsg("پرداخت با موفقيت انجام شد  لطفا کد رهگيري را يادداشت کنيد");
                    $this->setMsg( "$this->resNum"." : کد رهگيري " );
                    return true;


                } elseif( $verify > $this->totalAmont ) {

                    //web method partial reverse transaction
                    $revAmont = $verify - $this->totalAmont;
                    $reverse  = $this->reverseTrans( $revAmont );

                    $this->setMsg("کاربر گرامي  مبلغ پرداختي بيش از مبلغ درخواستي است");
                    if( $reverse == 1 ) {
                        $this->setMsg("مابقي مبلغ پرداخت شده به حساب شما برگشت خورده");
                        $this->saveBankInfo( $this->totalAmont );
                    } else {
                        $this->setMsg( 'verify',$reverse );
                        $this->setMsg( "ما بقي مبلغ پرداختي شما در اينده اي نزديک به حساب شما برگشت خواهد خورد " );
                        $this->saveBankInfo( $verify );
                    }
                    $this->setMsg("پرداخت با موفقيت انجام شد  لطفا کد رهگيري را يادداشت کنيد");
                    $this->setMsg( "$this->resNum"." : کد رهگيري " );
                    return true;


                } elseif( $verify < $this->totalAmont ) {

                    //web method full reverse transaction
                    $rev     = $this->reverseTrans( $verify );
                    $this->setMsg("مبلغ پرداختي شما کمتر از مباغ سفارش است ");
                    if( $rev == 1 ) {
                        $this->setMsg("کل مبلغ پرداختي به حساب شما برگشت خورده");
                        $this->saveBankInfo( 0 );
                    } else {
                        $this->setMsg("در اينده اي نزديک کل مبلغ پرداختي به حساب شما برگشت خواهد خورد لطفا براي پي گيري کد رهگيري را يادداشت کنيد");
                        $this->setMsg( "$this->resNum"." : کد رهگيري " );
                        $this->setMsg( 'verify',$rev );
                        $this->saveBankInfo( $verify );
                    }
                    return false;
                }
                //Error transaction
            } elseif ( $verify < 0 or $verify == false ) {
                $this->setMsg( "کاربر گرامي مشکلي در تاييد  پرداخت پيش امده" );
                $this->setMsg( 'verify',$verify );
                $this->saveBankInfo( 0 );
                return false;
            }
        }
       protected function verifyTrans()
        {
            if(empty($this->refNum) or empty($this->merchantID) ) {
                return false;
            }
            $soapClient = new nusoapclient( $this->webMethodURL,'wsdl' );
            $soapProxy  = $soapClient->getProxy();
            $result     = false;
            
            for( $a=1;$a<6;++$a ) {
                $result  = $soapProxy->verifyTransaction( $this->refNum,$this->merchantID );
                //if( $result != false ) {
                if( $result > 0)//Modified By Moji
                {
                    break;
                }
            }
            return $result;
        }

        protected function reverseTrans( $revNumber )
        {
            if( $revNumber <= 0 or empty($this->refNum) or empty($this->merchantID) or empty($this->password) ) {
                return false;
            }
            $soapClient = new nusoapclient( $this->webMethodURL,'wsdl' );
            $soapProxy  = $soapClient->getProxy();
            $result     = false;

            for( $a=1;$a<6;++$a ) {
                $result     = $soapProxy->reverseTransaction( $this->refNum,$this->merchantID,$this->password,$revNumber );
                if( $result != false )
                    break;
            }
            return $result;
        }

        public function sendParams()
        {

            if ( $this->totalAmont <= 0 or empty($this->action) or empty($this->redirectURL) or empty($this->resNum) or empty($this->merchantID) ) {
                $this->setMsg( "Error: function sendParams()" );
                return false;
            }
            $form  = "<html>";
            $form .= "<body onLoad=\"document.forms['sendparams'].submit();\" >";
            $form .= "<form name=\"sendparams\" method=\"POST\" action=\"$this->action\" enctype=\"application/x-www-form-urlencoded\" >\n";
            foreach ( $this->style as $key=>$val ) {
                if( $val != '' ) {
                    $form .= "<input type=\"hidden\" name=\"$key\" value=\"$val\" />\n";
                }
            }
            $form .= "<input type=\"hidden\" name=\"Amount\" value=\"$this->totalAmont\" />\n";
            $form .= "<input type=\"hidden\" name=\"ResNum\" value=\"$this->resNum\" />\n";
            $form .= "<input type=\"hidden\" name=\"MID\" value=\"$this->merchantID\" />\n";
            $form .= "<input type=\"hidden\" name=\"RedirectURL\" value=\"$this->redirectURL\" />\n";
            
            $form .= "</form>";
            $form .= "</body>";
            $form .= "</html>";

            print $form;
        }

        protected function setMsg($type='',$index='')
        {
            if ( $type == 'state' and isset( $this->errorState[$index] ) ) {
                $this->msg[] = $this->errorState[$index];

            } elseif( $type == 'verify' and isset($this->errorVerify[$index]) ) {
                $this->msg[] = $this->errorVerify[$index];

            } elseif( $type != 'verify' and $type != 'state') {
                $this->msg[] = "$type";
            }
        }

        public function getMsg($dis='')
        {
            if( count($this->msg) == 0 ) return array();
            if( $dis == 'display' ) {
                $msg  = "<ul>\n";
                foreach ( $this->msg as $v ) { $msg .= "<li> $v </li>\n"; }
                $msg .= "</ul>\n";
                return print $msg;
            }
            return $this->msg;
        }
    }


?> 
