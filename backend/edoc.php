<?php
/**
 * 監察院彈劾紀錄、公職人員財產申報、政治獻金明細 - 糾正案文
 *
 *
 * @param debug bool 
 * @param page int
 * @param type int 0~3 
 *
 * SOURCE URL 
 * // xdURL=./di/edoc/db2.asp&edoc_no=1&ctNode=910  調查
 * // xdURL=./di/edoc/db2.asp&edoc_no=2&ctNode=911  糾正案文
 * // xdURL=./di/edoc/db2.asp&edoc_no=3&ctNode=912  彈劾案文
 * // xdURL=./di/edoc/db2.asp&edoc_no=4&ctNode=913  糾舉案文 
 *
 * @author Mervyn Wang <alpe.g2@gmail.com>
 **/

date_default_timezone_set('Asia/Taipei');
require( __DIR__ . '/curl.php');

class edoc extends curl_wapper {

	//http://www.cy.gov.tw/sp.asp?xdURL=./di/edoc/db2.asp&ctNode=911&doQuery=1&cPage=2&edoc_no=2&intYear=102
	private $uri = 'sp.asp?xdURL=./di/edoc/db2.asp&edoc_no=__type__&ctNode=__type2__';
	//private $type = [ [1,910], [2,911], [3,912], [4,913] ] ;
	private $type = array( array(1,910), array(2,911), array(3,912), array(4,913) ) ;

	private $domain = 'http://www.cy.gov.tw/';

	public $data = array('totalPage' => 0, 'nowPage' => 1, 'totalRow' => 0, 'rows' => '');

	public function getData($page = 1, $type = 0, $debug = false){
		$type = (is_numeric($type) && isset($this->type[$type]))? 
			$this->type[$type] : $this->type[0];

		$this->uri = str_replace(array('__type__', '__type2__'), $type, $this->uri);

		parent::set_url($this->domain. $this->uri.'&cPage='.$page);
		parent::set_method('get');
		parent::get();
		if($debug){
			var_dump($this->html);
			echo "\n\n";
		}

		preg_match('/<div class="lpTb">(.*?)<\/div>/ms', $this->html, $target);

		if($debug){
			var_dump($target);
			echo "\n\n";
		};

		preg_match('/<div class="page">(.*>)<ul>/ms', $this->html, $page);

		if(!isset($page[1])){
			$this->data['totalPage'] = 1;
			$this->data['totalRow'] = 0;
			$this->data['nowPage'] = 1;
		} else {
			preg_match_all('/<em>.*?<\/em>/ms', $page[1], $page);
			$this->data['totalPage'] = $page[0];
			$this->data['totalRow'] = $page[1];
			$this->data['nowPage'] = $page[2];
		}


		$target = $target[1];

		preg_match_all('/<tr>(.*?)<\/tr>/ms', $target, $row);
		$this->data['row'] = $row[0];

		array_walk($this->data['row'], function(&$row, $key, $uri){
			$row = preg_replace('/<\/?(tr|ul|li|img).*?>/ms', '', $row);
			$row = preg_replace('/<a href="(.*?)".*?>.*?<\/a>/ms', ' '.$uri.'\\1', $row);
			$row = preg_replace('/\s/ms', '', $row);
			preg_match_all('/<td.*?>(.*?)<\/td>/ms', $row, $cols);
			$row = $cols[1];
			$row[2] = preg_replace('/http.*?$/', '', $row[2]);
		}, $this->domain);
		
		
		return $this->data;
	}

	private function getDoc(){
		return ;
	}


	private function getPdf(){
		return ;
	}

}

 
// for manual
$sargv = join(';', $argv);
$debug = (strpos($sargv, 'debug') !== FALSE)?  true : false;
if(strpos($sargv, 'help') !== FALSE){
	print " [debug] [page=\d] [type=\d]
	debug 	輸出部份除錯訊息
	page=(\d+) 頁數
	type=(\d+)  [調查, 糾正案文, 彈劾案文, 糾舉案文]
	\n";
	exit;
}
if(strpos($sargv, 'debug') !== FALSE){
    ini_set('display_errors', true);
    error_reporting(E_ALL | E_STRICT);
    $debug = true;
}
else{
    $debug = false;
}
if(strpos($sargv, 'page=') !== FALSE){
	preg_match('/page\=(\d+)/', $sargv, $m);
	$page = (int)$m[1];
} else {
	$page = 1;
}

if(strpos($sargv, 'type=') !== FALSE){
	preg_match('/type\=(\d+)/', $sargv, $m);
	$type = (int)$m[1];
} else {
	$type = 0;
}

$get = new edoc();
$a = $get->getData($page, $type, $debug);

if($debug) var_dump($a);
echo json_encode($a);

?>