<?php

/**
 * post 와 get 값에 대한 XSS 및 보완 처리를 함
 * @author pdc222
 * @version 0.2
 */

define("INJECTION_KEY", "/[#\+\-%@=\/\:;,\.\"\^`~\_|\!\?\[\]\{\}]/i");

class DNWInput
{
	// 허용하지 않는 문자열 정의
	private $disAllowedStringArray = array(
		'document.cookie'	=> '',
		'document.write'	=> '',
		'.parentNode'		=> '',
		'.innerHTML'		=> '',
		'window.location'	=> '',
		'-moz-binding'		=> '',
		'<!--'				=> '&lt;!--',
		'-->'				=> '--&gt;',
		'<![CDATA['			=> '&lt;![CDATA['
	);

	// 허용하지 않는 패턴 정의
	private $disAllowedPatternArray = array(
		"javascript\s*:"			=> '',
		"expression\s*(\(|&\#40;)"	=> '', // CSS and IE
		"vbscript\s*:"				=> '', // IE, surprise!
		"Redirect\s+302"			=> ''
	);

	// 허용하지 않는 인젝션 문자열 패턴
	private $disAllowedInjectionArray = array(
		INJECTION_KEY => ""
	);

	private $urlPatternString = 'http://';

	// 백 슬러쉬 추가 여부
	private $addSlashes = true;

	// html 특수 문자 처리 여부
	private $htmlSpecialchars = true;

	private $injectionPattern = false;


	private $size = 2048;

	private $listCount = 1000;

	private $throwAble  = false;

	private $isChange = false;

	private $checkUrl = false;

	private $checkByte = true;




	/**
	 * 모든 요청에 대하여 보완 처리
	 * 보완 관계상 전역변수에 직접 값을 수정하지 않고 배열 값으로 결과 값을 전달함
	 * @return array (
	 * 					"POST" => post 값에 대한 처리 값
	 * 				 	"GET" => get 값에 대한 처리 값
	 * 				  )
	 */
	public function all($isInteger = true){
		global $_GET, $_POST, $_REQUEST;
		$result = array("GET" => array(), "POST" => "", "REQUEST" => "");
		if($this->checkGlobalCount($_GET) === true)
			$result["GET"] = $this->allGet($isInteger);
		if($this->checkGlobalCount($_POST) === true)
			$result["POST"] = $this->allPost($isInteger);
		if($this->checkGlobalCount($_REQUEST) === true)
			$result["REQUEST"] = $this->allRequest($isInteger);
		return $result;
	}

	/**
	 * 전달인자로 전달 받은 배열의 패턴을 injection 체크 패턴에서 제외한다.
	 * @param array $removePatternList 변경해야할 injection 패턴을 저장한 배열
	 */

	public function removeInjectionPattern(array $removePatternList){
		unset($this->disAllowedInjectionArray);
		$removeList = array("#", "-", "+", "%", "@", "=", "/", ":", ";", ",", ".", "\"", "^", "`", "~", "_", "|", "!", "?", "[", "]", "{", "}");
		$keyString  = "#\+\-%@=\/\:;,\.\"\^`~\_|\!\?\[\]\{\}";
		$noSlashList = array('#', '%', '@', '=', ';', ',', '`', '~', '|');


		foreach($removePatternList as $removePattern){
			if(in_array($removePattern, $removeList) === true){
				if(in_array($removePattern, $noSlashList) === false){
					$removePattern = '\\'.$removePattern;
				}
				$keyString = str_replace($removePattern, "", $keyString);
			}
		}
		$keyString = "/[".$keyString."]/i";
		$this->disAllowedInjectionArray = array($keyString => "");
	}

	/**
	 * injection 패턴 형식을 초기화 한다
	 */
	public function resetInjectionPattern(){
		unset($this->disAllowedInjectionArray);
		$this->disAllowedInjectionArray = array(INJECTION_KEY => "");
	}

	/**
	 * injection 패턴 배열을 리턴한다. ...
	 * @return array
	 */
	public function getInjectionPattern(){
		return $this->disAllowedInjectionArray;
	}


	/**
	 * get 요청에 대한 모든 처리값을 배열로 받음
	 * @return array(key : 요청 인자 이름, value: 요청 값)
	 */
	public function allGet($isInteger = true){
		global $_GET;
		// injection or xss 에 변경에 대한 clear 처리
		$this->clearChange();
		if($this->checkGlobalCount($_GET) === false)
			return array();
		$result = array();
		try{
			foreach($_GET as $name => $value){
				$result[$name] = $this->getAction($name, $isInteger);
			}
		}catch(InvalidArgumentException $e){
			throw $e;
		}catch(UnexpectedValueException $e){
			throw $e;
		}
		return $result;
	}

	/**
	 * post 요청에 대한 모든 처리값을 배열로 받음
	 * @return array(key : 요청 인자 이름, value: 요청 값)
	 */
	public function allPost($isInteger = true){
		global $_POST;
		// injection or xss 에 변경에 대한 clear 처리
		$this->clearChange();
		if($this->checkGlobalCount($_POST) === false)
			return array();
		$result = array();
		$postCount =0;
		try{
			foreach($_POST as $name => $value){
				$result[$name] = $this->postAction($name, $isInteger);
				$postCount++;
				if($postCount == $this->listCount)
					break;
			}
		}catch(InvalidArgumentException $e){
			throw $e;
		}catch(UnexpectedValueException $e){
			throw $e;
		}
		return $result;
	}


	/**
	 * request 요청에 대한 모든 처리값을 배열로 받음
	 * @return array(key : 요청 인자 이름, value: 요청 값)
	 */
	public function allRequest($isInteger = true){
		global $_REQUEST;
		// injection or xss 에 변경에 대한 clear 처리
		$this->clearChange();
		if($this->checkGlobalCount($_REQUEST) === false)
			return array();
		$result = array();
		$requestCount =0;
		try{
			foreach($_REQUEST as $name => $value){
				$result[$name] = $this->requestAction($name, $isInteger);
				$requestCount++;
				if($requestCount == $this->listCount)
					break;
			}
		}catch(InvalidArgumentException $e){
			throw $e;
		}catch(UnexpectedValueException $e){
			throw $e;
		}
		return $result;
	}

	/**
	 * get 요청에 대한 모든 처리값을 배열로 받음
	 * @param string $name (get 으로 요청한 인자의 이름)
	 * @param bool $isInteger (true 일 경우 강제로 Int로 형변환을 함)
	 * @return array(key : 요청 인자 이름, value: 요청 값)
	 */
	public function get($name, $isInteger = true, $isFloat = false){
		global $_GET;
		// injection or xss 에 변경에 대한 clear 처리
		$this->clearChange();
		try{
			$returnString = $this->getAction($name, $isInteger, $isFloat);
		}catch(InvalidArgumentException $e){
			throw $e;
		}catch(UnexpectedValueException $e){
			throw $e;
		}

		return $returnString;
	}


	/**
	 * get 값을 int 값으로 변환
	 * @param string $name
	 * @return int
	 */

	public function getInt($name){
		return  $this->get($name, true);
	}

	/**
	 * get 값을 string 값으로 변환
	 * @param string $name
	 * @return string
	 */
	public function getString($name){
		try{
			return $this->get($name, false);
		}catch(InvalidArgumentException $e){
			throw $e;
		}catch(UnexpectedValueException $e){
			throw $e;
		}
	}

	/**
	 * get 값을 float 값으로 변환
	 * @param string $name
	 * @return float
	 */
	public function getFloat($name){
		return $this->get($name, true, true);
	}


	/**
	 * post 요청에 대한 모든 처리값을 배열로 받음
	 * @param string $name (post 으로 요청한 인자의 이름)
	 * @param bool $isInteger (true 일 경우 강제로 Int로 형변환을 함)
	 * @return array(key : 요청 인자 이름, value: 요청 값)
	 */
	public function post($name, $isInteger = true, $isFloat = false){
		global $_POST;

		// injection or xss 에 변경에 대한 clear 처리
		$this->clearChange();
		try{
			$returnString = $this->postAction($name, $isInteger, $isFloat);
		}catch(InvalidArgumentException $e){
			throw $e;
		}catch(UnexpectedValueException $e){
			throw $e;
		}
        return $returnString;
	}

	/**
	 * post 값을 int 값으로 변환
	 * @param string $name
	 * @return int
	 */
	public function postInt($name){
		return  $this->post($name, true);
	}

	/**
	 * post 값을 string 변환
	 * @param string $name
	 * @return string
	 */
	public function postString($name){
		try{
			return $this->post($name, false);
		}catch(InvalidArgumentException $e){
			throw $e;
		}catch(UnexpectedValueException $e){
			throw $e;
		}
	}

	/**
	 * post 값을 float 로 변환
	 * @param string $name
	 * @return float
	 */
	public function postFloat($name){
		return $this->post($name, true, true);
	}


	/**
	 * request 요청에 대한 모든 처리값을 배열로 받음
	 * @param string $name (post 으로 요청한 인자의 이름)
	 * @param bool $isInteger (true 일 경우 강제로 Int로 형변환을 함)
	 * @return array(key : 요청 인자 이름, value: 요청 값)
	 */
	public function request($name, $isInteger = true, $isFloat = false){

		// injection or xss 에 변경에 대한 clear 처리
		$this->clearChange();
		try{
			$returnString = $this->requestAction($name, $isInteger, $isFloat);
		}catch(InvalidArgumentException $e){
			throw $e;
		}catch(UnexpectedValueException $e){
			throw $e;
		}
        return $returnString;
	}

	/**
	 * request 값을 int 값으로 변환
	 * @param string $name
	 * @return int
	 */
	public function requestInt($name){
		return  $this->request($name, true);
	}

	/**
	 * request 값을 string 변환
	 * @param string $name
	 * @return string
	 */
	public function requestString($name){
		try{
			return $this->request($name, false);
		}catch(InvalidArgumentException $e){
			throw $e;
		}catch(UnexpectedValueException $e){
			throw $e;
		}
	}

	/**
	 * request 값을 float 로 변환
	 * @param string $name
	 * @return float
	 */
	public function requestFloat($name){
		return $this->request($name, true, true);
	}


	// 백 슬래시와 뉴라인 처리
	private function cleanString($string) {
		// 배열인 경우에는 재귀호출을 이용하여 clean 작업을 한다.
		if (is_array($string)) {
			$newArray = array();
			foreach ($string as $key => $value) {
				$newArray[$key] = $this->cleanString($value);
			}
			return $newArray;
		}

		// 백슬래시 추가
		if($this->addSlashes ===  true){
			if (get_magic_quotes_gpc()) {
				$returnString = $string;
			} else {
				$returnString = addslashes($string);
			}
			// 백슬래시 제거
		}else{
			if (get_magic_quotes_gpc()) {
				$returnString = stripslashes($string);
			} else {
	            $returnString = $string;
	        }
		}
		//뉴라인 통일
		if (strpos($returnString, "\r") !== FALSE) {
			$returnString = str_replace(array("\r\n", "\r"), "\n", $returnString);
		}
		return $returnString;
	}

	/**
	 * xss 문자열과 injection 관련 문자 검사
	 *
	 * @param string $string
	 */
	 private function cleanXss($string) {
		$str = $string;



		try{
			//배열인 경우에는 재귀 호출
			if (is_array($str)) {
				foreach ($str as $key => $value) {
					$str[$key] = $this->cleanXss($str[$key]);
				}
				return $str;
			}
		}catch(InvalidArgumentException $e){
			throw $e;
		}catch(UnexpectedValueException $e){
			throw $e;
		}

		if($this->checkUrl === true){
			if(stripos($str, $this->urlPatternString) !== false){
				throw new UnexpectedValueException("인자로 http://가 들어간 url 값은 허용하지 않습니다.");
			}
		}


		//html 스페셜문자열 변환
		if($this->htmlSpecialchars === true){
			if (mb_check_encoding($str, 'UTF-8') == true) {
				$str = htmlspecialchars($str, ENT_NOQUOTES, 'UTF-8');
			} else {
				$str = htmlspecialchars($str, ENT_NOQUOTES, 'EUC-JP');
			}
		}

		$orgString =  $str;

		// injection 패턴 처리
		if($this->injectionPattern === true){
			foreach($this->disAllowedInjectionArray as  $key => $disAllowedInjection){
				$str = preg_replace($key , $disAllowedInjection, $str);
			}
			$str = preg_replace('/\s\s+/', ' ', $str);
			$str = str_ireplace(" or ", " ", $str);
		}

		//허용하지 않을 문자열
		foreach ($this->disAllowedStringArray as $key => $val) {
			$str = str_replace($key, $val, $str);
		}
		//허용하지 않을 패턴
		foreach ($this->disAllowedPatternArray as $key => $val) {
			$str = preg_replace("#".$key."#i", $val, $str);
		}

		// xss 나 injection 체크가 있었으면
		if($str != $orgString){
			$this->isChange = true;
			if($this->throwAble === true){
				throw new InvalidArgumentException("invalid xss string or injection string".$orgString.",".$str);
			}
		}
		return $str;
	}

	/**
	 * 변화 여부 flag 를 구함
	 */
	public function isChange(){
		return $this->isChange;
	}

	public function clearChange(){
		$this->isChange = false;
	}

	public function setThrowAble($throwAble){
		$this->throwAble = $throwAble;
	}

	/**
	 * 백 슬래시 추가 여부 결정
	 * @param bool $isAdd
	 */
	public function setAddSlashes($isAdd){
		$this->addSlashes = $isAdd;
	}

	/**
	 * html 특수 문자 변환 처리 여부 결정
	 * @param bool $isHtmlSpecialchars
	 * */
	 public function setHtmlSpecialchars($isHtmlSpecialchars){
		$this->htmlSpecialchars = $isHtmlSpecialchars;
	}
	/**
	 * injection 적용 여부
	 * @param bool $isInjectionPattern
	 */
	public function setInjectionPattern($isInjectionPattern){
		$this->injectionPattern = $isInjectionPattern;
	}

	/**
	 * 지정된 이름의 쿠키값을 검증하여 가지고 옴 ...
	 * @param string $name
	 * @param bool $isInteger
	 */

	public function cookie($name, $isInteger = true, $isFloat = false){
		// injection or xss 에 변경에 대한 clear 처리
		$this->clearChange();
		try{
			$returnString  = $this->cookieAction($name, $isInteger, $isFloat);
		}catch(InvalidArgumentException $e){
			throw $e;
		}catch(UnexpectedValueException $e){
			throw $e;
		}
		return $returnString;
	}

	/**
	 * cookie 값을 int 값으로 변환
	 * @param string $name
	 * @return int
	 */

	public function cookieInt($name){
		return  $this->cookie($name, true);
	}

	/**
	 * cookie 값을 string 값으로 변환
	 * @param string $name
	 * @return string
	 */
	public function cookieString($name){
		try{
			return $this->cookie($name, false);
		}catch(InvalidArgumentException $e){
			throw $e;
		}catch(UnexpectedValueException $e){
			throw $e;
		}
	}

	/**
	 * cookie 값을 float 값으로 변환
	 * @param string $name
	 * @return float
	 */
	public function cookieFloat($name){
		return $this->cookie($name, true, true);
	}


	/**
	 * 쿠키에 저장된 모든 값을 가져옴
	 * @param bool $isInteger
	 */
	public function allCookie($isInteger = true){
		global $_COOKIE;
		// injection or xss 에 변경에 대한 clear 처리
		$this->clearChange();
		$result = array();
		$cookieCount = 0;

		try{
			foreach($_COOKIE as $name => $value){
				$result[$name] = $this->cookieAction($name, $isInteger);
				$cookieCount++;
				if($cookieCount == $this->listCount)
					break;
			}
		}catch(InvalidArgumentException $e){
			throw $e;
		}catch(UnexpectedValueException $e){
			throw $e;
		}
		return $result;
	}

	/**
	 * 요청된 인자의 limit size 로 설정된 값을 구함
	 * @return int
	 */
	public function getSize()
	{
	    return $this->size;
	}

	/**
	 * 요청된 인자의 limit size 로 설정된 값을 설정
	 * @param int size
	 */
	public function setSize($size)
	{
	    $this->size = $size;
	}

	/**
	 * 요청된 인자의 limit count 로 설정된 값을 구함
	 * @return int size
	 */
	public function getListCount()
	{
	    return $this->listCount;
	}

	/**
	 * 요청된 인자의 limit count 로 설정된 값을 설정
	 * @param int $listCount
	 */
	public function setListCount($listCount)
	{
	    $this->listCount = $listCount;
	}

	/**
	 *
	 * 전달받은 배열이나 문자열을  int 값으로 변환하여 전달
	 * @param mixed $string
	 * @return mixed (전달 인자와 동일한 type)
	 */
	private function changeTypeInt($string){
		if(is_array($string) === true){
			foreach($string as $key => $value){
				if(is_array($string[$key]) === true)
					$string[$key] = $this->changeTypeFloat($string[$key]);
				else
					$string[$key] = (int)$value;
			}
			return $string;
		}else{
			return (int)$string;
		}
	}

	/**
	 *
	 * 전달받은 배열이나 문자열을 float 값으로 변환하여 전달
	 * @param mixed $string
	 * @return mixed (전달 인자와 동일한 type)
	 */

	private function changeTypeFloat($string){
		$orgString = $string;

		if(is_array($string) === true){
			foreach($string as $key => $value){
				if(is_array($string[$key]) === true)
					$string[$key] = $this->changeTypeFloat($value);
				else
					$string[$key] = floatval($string[$key]);
			}
			return $string;
		}else{
			return floatval($string);
		}
	}

	/**
	 *
	 * 전달받은 문자열이나 배열 요소 값의 문자열 길이가 size 로 지정된 값 보다 크면 해당 요서를 빈문자열로 변경해서 전달
	 * @param mixed $string
	 * @return mixed (전달인자가 string 경우는 string, 배열일 경우는 배열)
	 */

	private function sizeCheck($string){
		if(is_array($string) === true){
			foreach($string as $key => $value){
				if(is_array($string[$key]) === true){
					$string[$key] = $this->sizeCheck($value);
				}else{
					if(strlen($value) >= $this->size){
						$string[$key] = "";
					}
				}
			}
			return $string;
		}else{
			if(strlen($string) > $this->size){
				return "";
			}
			return $string;
		}
	}

	/**
	 *
	 * 설정된 listCount 값과 전달받은 인자의 배열크기를 체크
	 *
	 * @param mixed $data
	 * @return bool (배열크기가 listCount 보다 작거나 같으면 true, 전달인자가 배열이 아닐 경우 true)
	 *
	 */

	private function checkGlobalCount($data){
		if(is_array($data) === false)
			return true;

		if($this->listCount >= count($data, COUNT_RECURSIVE))
			return true;
		return false;
	}



	private function postAction($name, $isInteger = true, $isFloat = false){
		global $_POST;

		if (false === array_key_exists($name, $_POST)) {
            return ($isInteger)? 0 : '';
        }

        if($this->checkGlobalCount($_POST[$name]) === false)
			return "";

		if($isInteger === true && $isFloat === true)
	     	return  $this->changeTypeFloat($_POST[$name]);
        else if($isInteger === true)
        	return  $this->changeTypeInt($_POST[$name]);

        //post로 넘어온 데이터에 대한 byte체크 예외처리 추가
        $postString = $_POST[$name];
        if($this->getCheckByte() === true){
			$postString = $this->sizeCheck($postString);
        }

		if($postString == "")
			return "";

        $postString = $this->cleanString($postString);
		try{
        	$returnString = $this->cleanXss($postString);
		}catch(InvalidArgumentException $e){
			throw $e;
		}catch(UnexpectedValueException $e){
			throw $e;
		}
        return $returnString;
	}


	private function getAction($name, $isInteger = true, $isFloat = false){
		global $_GET;

		if (false === array_key_exists($name, $_GET)) {
           return ($isInteger)? 0 : '';
        }

		if($this->checkGlobalCount($_GET[$name]) === false)
			return "";

        if($isInteger === true && $isFloat === true)
	     	return  $this->changeTypeFloat($_GET[$name]);
        else if($isInteger === true)
        	return  $this->changeTypeInt($_GET[$name]);

		$getString = $this->sizeCheck($_GET[$name]);
		if($getString == "")
			return "";

		$getString = $this->cleanString($getString);

		try{
			$returnString =  $this->cleanXss($getString);
		}catch(InvalidArgumentException $e){
			throw $e;
		}catch(UnexpectedValueException $e){
			throw $e;
		}
		return $returnString;
	}


	private function cookieAction($name, $isInteger = true, $isFloat = false){
		global $_COOKIE;

		if (false === array_key_exists($name, $_COOKIE)) {
            return ($isInteger)? 0 : '';
        }

		if($isInteger === true && $isFloat === true)
	     	return  $this->changeTypeFloat($_COOKIE[$name]);
        else if($isInteger === true)
        	return $this->changeTypeInt($_COOKIE[$name]);

        $cookieString = $this->sizeCheck($_COOKIE[$name]);

		if($cookieString == "")
			return "";

		$cookieString = $this->cleanString($cookieString);

		try{
			$returnString =  $this->cleanXss($cookieString);
		}catch(InvalidArgumentException $e){
			throw $e;
		}catch(UnexpectedValueException $e){
			throw $e;
		}

		return $returnString;
	}


	private function requestAction($name, $isInteger = true, $isFloat = false){
		global $_REQUEST;

		if (false === array_key_exists($name, $_REQUEST)) {
            return ($isInteger)? 0 : '';
        }

		if($isInteger === true && $isFloat === true)
	     	return  $this->changeTypeFloat($_REQUEST[$name]);
        else if($isInteger === true)
        	return $this->changeTypeInt($_REQUEST[$name]);

        $requestString = $this->sizeCheck($_REQUEST[$name]);

		if($requestString == "")
			return "";

		$requestString = $this->cleanString($requestString);

		try{
			$returnString =  $this->cleanXss($requestString);
		}catch(InvalidArgumentException $e){
			throw $e;
		}catch(UnexpectedValueException $e){
			throw $e;
		}
		return $returnString;
	}


	public function getCheckUrl()
	{
	    return $this->checkUrl;
	}

	public function setCheckUrl($checkUrl)
	{
	    $this->checkUrl = $checkUrl;
	}

	public function getCheckByte(){
		return $this->checkByte;
	}

	public function setCheckByte($checkByte){
		$this->checkByte = $checkByte;
	}
}

/* 
require_once 'com/danawa/web/util/DNWInput.php';	// GET, POST, COOKIE 값에 대한 XSS, sql injection 체크	
require 
include
include_once 


$oDnwInput = new DNWInput();
$oDnwInput->setInjectionPattern(true);
$aGetResult = $oDnwInput->allGet(false);
$aPostResult = $oDnwInput->allPost(false); */