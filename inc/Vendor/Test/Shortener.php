<?php
namespace Vendor\Test;

use PDO;

class Shortener {

	protected $db = null;
	protected $errors = array();
	
	protected static $chars = '123456789abcdefghijklmnopqrstuvxyzwABCDEFGHIJKLMNOPQRSTUVXYZW';
	
	function __construct()
	{
		$this->dbConnect();
	}
	
	protected function dbConnect()
	{
		try {
			$this->db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $e) {
			$this->errors[] = $e->getMessage();
		}
	}
	
	public function validateUrl($url)
	{
		if (empty($url)) return false;
		
		return filter_var($url, FILTER_VALIDATE_URL);			
	}
	
	public function validateShortcode($shortcode)
	{
		if (preg_match("/^([" . self::$chars . "]+)$/",$shortcode)) return true; else return false;
	}
	
	public function urlExists($url)
	{
		try {
			$url_hash = sha1($url);
			$stmt = $this->db->prepare("SELECT id, TIMESTAMPDIFF(MINUTE,created,now()) as created, url, clicks, status FROM `url` WHERE url_hash = :url_hash LIMIT 1");
			$stmt->bindParam(':url_hash', $url_hash);
			$stmt->execute();
			return $stmt->fetch();
		} catch (Exception $e) {
			$this->errors[] = $e->getMessage();
			return false;
		}
	}

	public function getUrlById($id)
	{
		try {
			$url_hash = sha1($url);
			$stmt = $this->db->prepare("SELECT `url` FROM `url` WHERE id = :id LIMIT 1");
			$stmt->bindParam(':id', $id);
			$stmt->execute();
			return $stmt->fetch();
		} catch (Exception $e) {
			$this->errors[] = $e->getMessage();
			return false;
		}
	}
	
	private function insertUrl($url)
	{
		try {
			$url_hash = sha1($url);
			$stmt = $this->db->prepare("INSERT INTO `url` SET url_hash = :url_hash, url = :url, status = 1 ");
			$stmt->bindParam(':url_hash', $url_hash);
			$stmt->bindParam(':url', $url);
			$stmt->execute();
			return $this->db->lastInsertId();
		} catch (PDOException $e) {
			$this->errors[] = $e->getMessage();
			return false;
		}	
	}

	private function recordClicks($id)
	{
		try {
			$stmt = $this->db->prepare("UPDATE `url` SET clicks = clicks+1 WHERE id = :id LIMIT 1");
			$stmt->bindParam(':id', $id);
			$stmt->execute();
			return true;
		} catch (PDOException $e) {
			$this->errors[] = $e->getMessage();
			return false;
		}	
	}
	
	//input  = [1, 18446744073709551615]
	//output = [2112, qRru42hdPcy]
	private function generateShortcode($id)
	{
		$result = '';
		
		$base = strlen( self::$chars );
		$start = pow($base,3);
		$num = $start + $id;

		for ( $t = floor( log10( $num ) / log10( $base ) ); $t >= 0; $t-- ) {
			$a = floor( $num / pow( $base, $t ) );
			$result = $result . substr( self::$chars, $a, 1 );
			$num = $num - ( $a * pow( $base, $t ) );
		}
		return MAIN_URL . $result;
	}
	
	private function decodeShortcode($shortcode)
	{
		$base = strlen( self::$chars );
		$start = pow($base,3);
		
		$result = 0;
		$len = strlen( $shortcode ) - 1;
		for ( $t = 0; $t <= $len; $t++ ) {
			$result = $result + strpos( self::$chars, substr( $shortcode, $t, 1 ) ) * pow( $base, $len - $t );
		}
		return $result-$start;
	}
	
	public function getShortcode($url)
	{
		
		if (!$this->validateUrl($url)) {
			$this->errors[] = 'Invalid url!';
			return false;
		}
		
		$check = $this->urlExists( $url );
		if (empty($check)) {
			$id = $this->insertUrl($url);
			$created = 0;
			$clicks = 0;
			$status = 1;
		} else {
			$id = $check['id'];
			$created = $check['created'];
			$clicks = $check['clicks'];
			$status = $check['status'];
		}
		$created = 60*24*7;
		$result = array(
			'id' => $id,
			'shortcode' => $this->generateShortcode($id),
			'created' => $this->showCreated($created),
			'url' => $url,
			'clicks' => $clicks,
			'status' => $status
		);	

		return $result;
	}
	
	public function redirectToUrl($shortcode)
	{
		
		if (!$this->validateShortcode($shortcode)) return false;

		$id = intval($this->decodeShortcode( $shortcode ));
		if ($id<=0) return false;
		
		$check = $this->getUrlById($id);
		if (empty($check)) return false;
		
		$this->recordClicks($id);
		
		header("Location: " . $check['url']);

	}
	
	public function showCreated($min)
	{
		if ($min<60) {
			return "$min minutes";			
		} else if ($min>=60 && $min<1440) {
			$h = $min / 60;
			return "$h hours";
		} else if ($min>=1440) {
			$d = $min / 1440;
			return "$d days";
		}		
	}
	
	public function getErrors()
	{
		if (sizeof($this->errors)==0) {
			return false;
		} else {
			if (DEBUG) return $this->errors; else return true;
		}
	}
}
