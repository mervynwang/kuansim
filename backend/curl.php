<?php
/**
 * curl & fopen get remote page
 *
 */
class curl_wapper {

	public $html = '';

	private  $_url ='';

	private $_rUrl = '';
	private $_Method = 'fopen';
	private $_vars = 'NULL';
	private $_header = FALSE;
	private $_use_cookie = true;
	private $_cookie_f = 'cookie.txt';
	private $_use_agent = '';
	private $_curl_opts = '';
	private $_a_copt = array();

	/**
	 * construct
	 *
	 * @param string $url URL
	 * @param string $var GET/POST var
	 * @param string $method get/podt/fopen
	 */
	function __construct($url='', $var=null, $method='fopen'){
		$this->set_url($url);
		$this->_vars = $var;
		$this->set_method($method);
	}

	function Get_Url($url, $var=null, $method='fopen'){
		$this->__construct($url, $var, $method);
	}

	/**
	 * set url
	 *
	 * @param string $url
	 * @package pubilc
	 */
	function set_url($url){
		$this->_url = $url;
	}

	/**
	 * set method
	 *
	 * @param string $method post/get/fopen
	 * @package var
	 */
	function set_method($method){
		if (preg_match('/(post|get|fopen)/i',$method ) === false) die('Method Error!');
		$this->_Method = strtolower($method);
	}

	/**
	 * curl default setting
	 *
	 * @param boolean $header return header
	 * @param boolean $use_cookie use cookies or not
	 * @param string $cookis	cookis_file
	 * @param string $use_agent  $use_agent
	 * @package curl
	 */
	function set_defcurl($header = false, $use_cookie = false, $cookis= '', $use_agent = 'defined'){
		$this->_header = ($header)? TRUE : FALSE;
		$this->_use_cookie = ($use_cookie)? true : false;
		if (!empty($cookis)) {
			if(!is_file($cookis)) return false;
			$this->_cookie_f = $cookis;
		}
		$definedUserAgent = isset($_SERVER['HTTP_USER_AGENT'])? $_SERVER['HTTP_USER_AGENT']  : 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:22.0) Gecko/20100101 Firefox/22.0';
		$this->_use_agent = (strtolower($use_agent) == 'defined')? $definedUserAgent : $user_agent;
	}

	/**
	 * set curl opt
	 *
	 * @param string/array $optname curl_setopt
	 * @param mixed $optvar  value
	 * @package curl
	 */
	function set_curl_opt($optname, $optvar=true){
		if(is_string($optname)){
			$this->_a_copt[$optname] = $optvar;
		} else {
			$this->_a_copt = $optname;
		}
		return true;
	}

	/**
	 * set post / get var
	 *
	 * @param string $sent_var the value sent by post & get
	 * @package var
	 */
	function set_var($sent_var=''){
		$this->_vars = $sent_var;
	}


	/**
	 * get remote html
	 *
	 * @param boolean $not_close
	 * @return string
	 * @package var
	 */
	function get($not_close=false){
		if ($this->_url == '' ) dir("URL NOT BEEN SET ! \n");
		if ($this->_Method == 'fopen') {
			$_GetFunc = function_exists('stream_get_contents')? 'stream' : 'ff';
			$this->_rUrl = fopen($this->_url, 'r');
			if (!$this->_rUrl) return false;
			$this->$_GetFunc();
			fclose($this->_rUrl);

		} elseif ($this->_Method == 'get' || $this->_Method == 'post') {

			if (!is_resource($this->_rUrl))	{
				$this->_rUrl = curl_init();
				$this->curl_opt();
				$this->curl_set();
			}

			if (!is_resource($this->_rUrl)) return false;
			if ($this->_Method == 'post') $this->crul_post();
			$this->html = curl_exec($this->_rUrl);
			if (!$this->html) $this->html = curl_error($this->_rUrl);
			if (!$not_close) $this->close();
		}
		return $this->html;
	}

	/**
	 * curl set opt
	 *
	 * @package curl
	 */
	function curl_set(){
		foreach($this->_a_copt as $opt => $var){
			if(!curl_setopt($this->_rUrl, constant($opt), $var)) die($opt. ' : ' .$var);
		}
	}

	/**
	 * curl default setting
	 *
	 */
	function curl_opt(){
		if($this->_use_agent == '') $this->set_defcurl();
		if($this->_curl_opts != '') $this->act_crul_opt();
		curl_setopt($this->_rUrl, CURLOPT_URL, $this->_url);
		curl_setopt($this->_rUrl, CURLOPT_HEADER, $this->_header);
		curl_setopt($this->_rUrl, CURLOPT_USERAGENT, $this->_use_agent);
		curl_setopt($this->_rUrl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($this->_rUrl, CURLOPT_RETURNTRANSFER, 1);
		if ($this->_use_cookie){
			curl_setopt($this->_rUrl, CURLOPT_COOKIEJAR, $this->_cookie_f);
			curl_setopt($this->_rUrl, CURLOPT_COOKIEFILE, $this->_cookie_f);
		}
	}

	/**
	 * curl post
	 *
	 * @package curl
	 */
	function crul_post(){
		curl_setopt($this->_rUrl, CURLOPT_POST, 1);
		curl_setopt($this->_rUrl, CURLOPT_POSTFIELDS, $this->_vars);
	}

	/**
	 * curl close
	 *
	 * @return boolean;
	 */
	function close(){
		if($this->_Method == 'fopen') return false;
		return @curl_close($this->_rUrl);
	}

	/**
	 * stream
	 *
	 * @return string html
	 * @package fopen
	 */
	function stream(){
		$this->html = stream_get_contents($this->_rUrl);
		if (!$this->html) return false;
	}

	/**
	 * feof
	 *
	 * @return string html
	 * @package fopen
	 */
	function ff(){
		$this->html = '';
		while(!feof($this->_rUrl)){
			$this->html .= fread($this->_rUrl, 8192);
		}
		if (!$this->html) return false;
	}


	/**
	 * destruct
	 *
	 * @return void
	 */
	function __destruct(){
		$this->close();
		unset($this->html, $this->_rUrl, $this->_curl_opts);
		return true;
	}

}



/*


/*
class Get_lotto extends Get_Url {

	public $darws = '';
	public $OpenDay = '';
	public $numbers = '';
	public $extra = '';

	private $_LottoUrl = array(
	'a' => 'http://www.taiwanlottery.com.tw/Lotto/Lotto649/drawing.aspx',
	'b' => 'http://www.taiwanlottery.com.tw/Lotto/Lotto638/drawing.aspx',
	'c' => 'http://www.taiwanlottery.com.tw/Lotto/4D/drawing.aspx',
	'd' => 'http://www.taiwanlottery.com.tw/Lotto/38M6/drawing.aspx'
	);

	public function lotto($unit, $debug = false){
		if ($unit == '' ) die(" UNIT NOT BEEN SET! \n ");
		parent::set_url($this->_LottoUrl[$unit]);
		parent::get();

		// Draw_No
		preg_match('/<span id="labDrawTerm">(\d*?)</', $this->html, $d);
		if($debug) {echo '<hr/> <pre> Draw:'; print_r($d) ; echo '</pre>';}
		$this->darws = $d[1];

		// Year Month Day
		preg_match('/(\d+)年(\d+).*?(\d+)/', $this->html, $day);
		if($debug) {echo '<hr/> <pre>'; print_r($day) ; echo '</pre>';}
		$this->OpenDay = $day[1] .'年' . $day[2]. '月'. $day[3] .'日';

		// Number
		preg_match_all('/number"?[^>]?>(\d{1,2})</', $this->html, $num);
		if($debug) {echo '<hr/> <pre> number:'; print_r($num) ; echo '</pre>';}

		switch ($unit) {
			case 'a' :
			case 'b' :
				$this->extra_num();
			case 'd' :
				$this->numbers = array_slice($num[1], 6);
			break;
			case 'c' :
				$this->numbers = $num[1];
			break;
		}
			$sep = ($unit == 'c' )? '' : ',' ;
			$this->numbers = join($sep, $this->numbers);
	}

	function extra_num(){
		$this->html = preg_replace('/[\r\n\t\ ]/si', '', $this->html);
		preg_match('/special"?[^>]>(\d*?)</', $this->html, $n);
		if($debug) {echo '<hr/> <pre>SD: '; print_r($n) ; echo '</pre>';}
		$this->extra = $n[1];
	}
}


/ *
define('DBSERVER','192.168.11.76');
define('DBUSR','lotto');
define('DBUSRPASSWD','qazwsx');
define('DBNAME','lotto');
* /

//require('get_url_class.php');
$unit =$_GET['u'];
$debug = ($_GET['d'] != '')? 1 : 0 ;
$get = new Get_lotto();
$get->lotto($unit, $debug);



echo '<pre>Draw:'. $get->darws .'  Days:'. $get->OpenDay . "\n\n  Numbers:". $get->numbers .':'. $get->extra;
*/
?>
