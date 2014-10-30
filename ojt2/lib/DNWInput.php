<?php

/**
 * post �� get ���� ���� XSS �� ���� ó���� ��
 * @author pdc222
 * @version 0.2
 */

define("INJECTION_KEY", "/[#\+\-%@=\/\:;,\.\"\^`~\_|\!\?\[\]\{\}]/i");

class DNWInput
{
	// ������� �ʴ� ���ڿ� ����
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

	// ������� �ʴ� ���� ����
	private $disAllowedPatternArray = array(
		"javascript\s*:"			=> '',
		"expression\s*(\(|&\#40;)"	=> '', // CSS and IE
		"vbscript\s*:"				=> '', // IE, surprise!
		"Redirect\s+302"			=> ''
	);

	// ������� �ʴ� ������ ���ڿ� ����
	private $disAllowedInjectionArray = array(
		INJECTION_KEY => ""
	);

	private $urlPatternString = 'http://';

	// �� ������ �߰� ����
	private $addSlashes = true;

	// html Ư�� ���� ó�� ����
	private $htmlSpecialchars = true;

	private $injectionPattern = false;


	private $size = 2048;

	private $listCount = 1000;

	private $throwAble  = false;

	private $isChange = false;

	private $checkUrl = false;

	private $checkByte = true;




	/**
	 * ��� ��û�� ���Ͽ� ���� ó��
	 * ���� ����� ���������� ���� ���� �������� �ʰ� �迭 ������ ��� ���� ������
	 * @return array (
	 * 					"POST" => post ���� ���� ó�� ��
	 * 				 	"GET" => get ���� ���� ó�� ��
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
	 * �������ڷ� ���� ���� �迭�� ������ injection üũ ���Ͽ��� �����Ѵ�.
	 * @param array $removePatternList �����ؾ��� injection ������ ������ �迭
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
	 * injection ���� ������ �ʱ�ȭ �Ѵ�
	 */
	public function resetInjectionPattern(){
		unset($this->disAllowedInjectionArray);
		$this->disAllowedInjectionArray = array(INJECTION_KEY => "");
	}

	/**
	 * injection ���� �迭�� �����Ѵ�. ...
	 * @return array
	 */
	public function getInjectionPattern(){
		return $this->disAllowedInjectionArray;
	}


	/**
	 * get ��û�� ���� ��� ó������ �迭�� ����
	 * @return array(key : ��û ���� �̸�, value: ��û ��)
	 */
	public function allGet($isInteger = true){
		global $_GET;
		// injection or xss �� ���濡 ���� clear ó��
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
	 * post ��û�� ���� ��� ó������ �迭�� ����
	 * @return array(key : ��û ���� �̸�, value: ��û ��)
	 */
	public function allPost($isInteger = true){
		global $_POST;
		// injection or xss �� ���濡 ���� clear ó��
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
	 * request ��û�� ���� ��� ó������ �迭�� ����
	 * @return array(key : ��û ���� �̸�, value: ��û ��)
	 */
	public function allRequest($isInteger = true){
		global $_REQUEST;
		// injection or xss �� ���濡 ���� clear ó��
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
	 * get ��û�� ���� ��� ó������ �迭�� ����
	 * @param string $name (get ���� ��û�� ������ �̸�)
	 * @param bool $isInteger (true �� ��� ������ Int�� ����ȯ�� ��)
	 * @return array(key : ��û ���� �̸�, value: ��û ��)
	 */
	public function get($name, $isInteger = true, $isFloat = false){
		global $_GET;
		// injection or xss �� ���濡 ���� clear ó��
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
	 * get ���� int ������ ��ȯ
	 * @param string $name
	 * @return int
	 */

	public function getInt($name){
		return  $this->get($name, true);
	}

	/**
	 * get ���� string ������ ��ȯ
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
	 * get ���� float ������ ��ȯ
	 * @param string $name
	 * @return float
	 */
	public function getFloat($name){
		return $this->get($name, true, true);
	}


	/**
	 * post ��û�� ���� ��� ó������ �迭�� ����
	 * @param string $name (post ���� ��û�� ������ �̸�)
	 * @param bool $isInteger (true �� ��� ������ Int�� ����ȯ�� ��)
	 * @return array(key : ��û ���� �̸�, value: ��û ��)
	 */
	public function post($name, $isInteger = true, $isFloat = false){
		global $_POST;

		// injection or xss �� ���濡 ���� clear ó��
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
	 * post ���� int ������ ��ȯ
	 * @param string $name
	 * @return int
	 */
	public function postInt($name){
		return  $this->post($name, true);
	}

	/**
	 * post ���� string ��ȯ
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
	 * post ���� float �� ��ȯ
	 * @param string $name
	 * @return float
	 */
	public function postFloat($name){
		return $this->post($name, true, true);
	}


	/**
	 * request ��û�� ���� ��� ó������ �迭�� ����
	 * @param string $name (post ���� ��û�� ������ �̸�)
	 * @param bool $isInteger (true �� ��� ������ Int�� ����ȯ�� ��)
	 * @return array(key : ��û ���� �̸�, value: ��û ��)
	 */
	public function request($name, $isInteger = true, $isFloat = false){

		// injection or xss �� ���濡 ���� clear ó��
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
	 * request ���� int ������ ��ȯ
	 * @param string $name
	 * @return int
	 */
	public function requestInt($name){
		return  $this->request($name, true);
	}

	/**
	 * request ���� string ��ȯ
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
	 * request ���� float �� ��ȯ
	 * @param string $name
	 * @return float
	 */
	public function requestFloat($name){
		return $this->request($name, true, true);
	}


	// �� �����ÿ� ������ ó��
	private function cleanString($string) {
		// �迭�� ��쿡�� ���ȣ���� �̿��Ͽ� clean �۾��� �Ѵ�.
		if (is_array($string)) {
			$newArray = array();
			foreach ($string as $key => $value) {
				$newArray[$key] = $this->cleanString($value);
			}
			return $newArray;
		}

		// �齽���� �߰�
		if($this->addSlashes ===  true){
			if (get_magic_quotes_gpc()) {
				$returnString = $string;
			} else {
				$returnString = addslashes($string);
			}
			// �齽���� ����
		}else{
			if (get_magic_quotes_gpc()) {
				$returnString = stripslashes($string);
			} else {
	            $returnString = $string;
	        }
		}
		//������ ����
		if (strpos($returnString, "\r") !== FALSE) {
			$returnString = str_replace(array("\r\n", "\r"), "\n", $returnString);
		}
		return $returnString;
	}

	/**
	 * xss ���ڿ��� injection ���� ���� �˻�
	 *
	 * @param string $string
	 */
	 private function cleanXss($string) {
		$str = $string;



		try{
			//�迭�� ��쿡�� ��� ȣ��
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
				throw new UnexpectedValueException("���ڷ� http://�� �� url ���� ������� �ʽ��ϴ�.");
			}
		}


		//html ����ȹ��ڿ� ��ȯ
		if($this->htmlSpecialchars === true){
			if (mb_check_encoding($str, 'UTF-8') == true) {
				$str = htmlspecialchars($str, ENT_NOQUOTES, 'UTF-8');
			} else {
				$str = htmlspecialchars($str, ENT_NOQUOTES, 'EUC-JP');
			}
		}

		$orgString =  $str;

		// injection ���� ó��
		if($this->injectionPattern === true){
			foreach($this->disAllowedInjectionArray as  $key => $disAllowedInjection){
				$str = preg_replace($key , $disAllowedInjection, $str);
			}
			$str = preg_replace('/\s\s+/', ' ', $str);
			$str = str_ireplace(" or ", " ", $str);
		}

		//������� ���� ���ڿ�
		foreach ($this->disAllowedStringArray as $key => $val) {
			$str = str_replace($key, $val, $str);
		}
		//������� ���� ����
		foreach ($this->disAllowedPatternArray as $key => $val) {
			$str = preg_replace("#".$key."#i", $val, $str);
		}

		// xss �� injection üũ�� �־�����
		if($str != $orgString){
			$this->isChange = true;
			if($this->throwAble === true){
				throw new InvalidArgumentException("invalid xss string or injection string".$orgString.",".$str);
			}
		}
		return $str;
	}

	/**
	 * ��ȭ ���� flag �� ����
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
	 * �� ������ �߰� ���� ����
	 * @param bool $isAdd
	 */
	public function setAddSlashes($isAdd){
		$this->addSlashes = $isAdd;
	}

	/**
	 * html Ư�� ���� ��ȯ ó�� ���� ����
	 * @param bool $isHtmlSpecialchars
	 * */
	 public function setHtmlSpecialchars($isHtmlSpecialchars){
		$this->htmlSpecialchars = $isHtmlSpecialchars;
	}
	/**
	 * injection ���� ����
	 * @param bool $isInjectionPattern
	 */
	public function setInjectionPattern($isInjectionPattern){
		$this->injectionPattern = $isInjectionPattern;
	}

	/**
	 * ������ �̸��� ��Ű���� �����Ͽ� ������ �� ...
	 * @param string $name
	 * @param bool $isInteger
	 */

	public function cookie($name, $isInteger = true, $isFloat = false){
		// injection or xss �� ���濡 ���� clear ó��
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
	 * cookie ���� int ������ ��ȯ
	 * @param string $name
	 * @return int
	 */

	public function cookieInt($name){
		return  $this->cookie($name, true);
	}

	/**
	 * cookie ���� string ������ ��ȯ
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
	 * cookie ���� float ������ ��ȯ
	 * @param string $name
	 * @return float
	 */
	public function cookieFloat($name){
		return $this->cookie($name, true, true);
	}


	/**
	 * ��Ű�� ����� ��� ���� ������
	 * @param bool $isInteger
	 */
	public function allCookie($isInteger = true){
		global $_COOKIE;
		// injection or xss �� ���濡 ���� clear ó��
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
	 * ��û�� ������ limit size �� ������ ���� ����
	 * @return int
	 */
	public function getSize()
	{
	    return $this->size;
	}

	/**
	 * ��û�� ������ limit size �� ������ ���� ����
	 * @param int size
	 */
	public function setSize($size)
	{
	    $this->size = $size;
	}

	/**
	 * ��û�� ������ limit count �� ������ ���� ����
	 * @return int size
	 */
	public function getListCount()
	{
	    return $this->listCount;
	}

	/**
	 * ��û�� ������ limit count �� ������ ���� ����
	 * @param int $listCount
	 */
	public function setListCount($listCount)
	{
	    $this->listCount = $listCount;
	}

	/**
	 *
	 * ���޹��� �迭�̳� ���ڿ���  int ������ ��ȯ�Ͽ� ����
	 * @param mixed $string
	 * @return mixed (���� ���ڿ� ������ type)
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
	 * ���޹��� �迭�̳� ���ڿ��� float ������ ��ȯ�Ͽ� ����
	 * @param mixed $string
	 * @return mixed (���� ���ڿ� ������ type)
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
	 * ���޹��� ���ڿ��̳� �迭 ��� ���� ���ڿ� ���̰� size �� ������ �� ���� ũ�� �ش� �伭�� ���ڿ��� �����ؼ� ����
	 * @param mixed $string
	 * @return mixed (�������ڰ� string ���� string, �迭�� ���� �迭)
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
	 * ������ listCount ���� ���޹��� ������ �迭ũ�⸦ üũ
	 *
	 * @param mixed $data
	 * @return bool (�迭ũ�Ⱑ listCount ���� �۰ų� ������ true, �������ڰ� �迭�� �ƴ� ��� true)
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

        //post�� �Ѿ�� �����Ϳ� ���� byteüũ ����ó�� �߰�
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
require_once 'com/danawa/web/util/DNWInput.php';	// GET, POST, COOKIE ���� ���� XSS, sql injection üũ	
require 
include
include_once 


$oDnwInput = new DNWInput();
$oDnwInput->setInjectionPattern(true);
$aGetResult = $oDnwInput->allGet(false);
$aPostResult = $oDnwInput->allPost(false); */