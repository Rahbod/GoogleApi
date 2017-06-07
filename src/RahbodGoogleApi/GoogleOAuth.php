<?php
namespace RahbodGoogleApi;
/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class GoogleOAuth
{
	const GOOGLE_OAUTH = 'google';
	const USER_AGENT_SUFFIX = "google-api-php-client/";
	const OAUTH2_REVOKE_URI = 'https://accounts.google.com/o/oauth2/revoke';
	const OAUTH2_TOKEN_URI = 'https://www.googleapis.com/oauth2/v3/token';
	const OAUTH2_AUTH_URL = 'https://accounts.google.com/o/oauth2/auth';
	const API_BASE_PATH = 'https://www.googleapis.com';

	private $debug = true;

	/**
	 * @var $scope OAuth options
	 */
	private $_access_type = 'offline';
	private $_scope;
	private $_client_id;
	private $_client_secret;
	private $_redirect_uri;
	private $_login_url;
	private $_token;
	private $_error = '';
	private $_error_description = '';

	/**
	 * GoogleOAuth constructor.
	 */
	public function __construct()
	{
	}

	public function getAuthUrl()
	{
		$params = [
			'redirect_uri' => $this->_redirect_uri,
			'response_type' => 'code',
			'client_id' => $this->_client_id,
			'scope' => $this->_scope,
			'access_type' => $this->_access_type,
		];
		return $this->_login_url = self::OAUTH2_AUTH_URL . "?" . http_build_query($params);
	}

	/**
	 * connect to oauth server and fetch access token
	 * @return bool|string
	 */
	public function authenticate()
	{
		if(!isset($_GET['code'])){
			header('Location:' . $this->getAuthUrl());
		}
		$header = array("Content-Type: application/x-www-form-urlencoded");
		$data = http_build_query(
			array(
				'code' => str_replace("#", null, $_GET['code']),
				'client_id' => $this->_client_id,
				'client_secret' => $this->_client_secret,
				'redirect_uri' => $this->_redirect_uri,
				'grant_type' => 'authorization_code'
			)
		);
		$result = $this->send_request('POST', self::OAUTH2_TOKEN_URI, $header, $data);
		if(isset($result['access_token'])){
			$this->_token = $result;
			$this->_token['created'] = time();
			if($this->_access_type == 'offline' && !$this->getRefreshToken()){
				$this->revokeToken($this->getAccessToken());
				header('Location:' . $this->getAuthUrl());
			}
		}else{
			$this->_error = $result['error'];
			$this->_error_description = $result['error_description'];
			if($this->debug)
				$this->showError();
			else
				return false;
		}
		return $this->_token;
	}

	/**
	 * @param null|string $refreshToken can pass refresh token here Or set it before call this function, in setRefreshToken()
	 * @return bool|mixed
	 */
	public function refreshAccessToken($refreshToken = null)
	{
		$refreshToken = $refreshToken?$refreshToken:$this->getRefreshToken();
		$header = array("Content-Type: application/x-www-form-urlencoded");
		$data = http_build_query(
			array(
				'refresh_token' => $refreshToken,
				'client_id' => $this->_client_id,
				'client_secret' => $this->_client_secret,
				'redirect_uri' => $this->_redirect_uri,
				'grant_type' => 'refresh_token'
			)
		);
		$result = $this->send_request('POST', self::OAUTH2_TOKEN_URI, $header, $data);
		if(isset($result['access_token'])){
			$this->_token = $result;
			$this->_token['refresh_token'] = $refreshToken;
			$this->_token['created'] = time();
		}else{
			$this->_error = $result['error'];
			$this->_error_description = $result['error_description'];
			if($this->debug)
				$this->showError();
			else
				return false;
		}
		return $this->_token;
	}

	public function revokeToken($token = null)
	{
		if(!$token){
			if($this->getRefreshToken()){
				$token = $this->getRefreshToken();
			}else{
				$token = $this->getAccessToken();
			}
		}
		if($token){
			$header = array('Cache-Control' => 'no-store', "Content-Type: application/x-www-form-urlencoded");
			$data = http_build_query(
				array(
					'token' => $token,
				)
			);
			$result = $this->send_request('POST', self::OAUTH2_REVOKE_URI, $header, $data);
			if(isset($result['error'])){
				$this->_error = $result['error'];
				$this->_error_description = $result['error_description'];
				if($this->debug)
					$this->showError();
				else
					return false;
			}else
				return true;
		}else{
			$this->_error = 'token_invalid';
			if($this->debug)
				$this->showError();
		}
		return false;
	}

	public function isAccessTokenExpired()
	{
		if(!$this->_token){
			return true;
		}
		$created = 0;
		if(isset($this->_token['created'])){
			$created = $this->_token['created'];
		}elseif(isset($this->_token['id_token'])){
			// check the ID token for "iat"
			// signature verification is not required here, as we are just
			// using this for convenience to save a round trip request
			// to the Google API server
			$idToken = $this->_token['id_token'];
			if(substr_count($idToken, '.') == 2){
				$parts = explode('.', $idToken);
				$payload = json_decode(base64_decode($parts[1]), true);
				if($payload && isset($payload['iat'])){
					$created = $payload['iat'];
				}
			}
		}

		// If the token is set to expire in the next 30 seconds.
		return ($created + ($this->_token['expires_in'] - 30)) < time();
	}

	/**
	 * Send Curl Request
	 * @param $method string GET|POST
	 * @param $url string
	 * @param $headers
	 * @param $data
	 * @return mixed
	 */
	private function send_request($method, $url, $headers, $data)
	{
		$curl = new Curl($url);
		if($headers)
			$curl->setHeader($headers);
		$response = $curl->run($method, $data);
		$json = json_decode($response, true);
		return $json;
	}

	public function getToken()
	{
		return $this->_token;
	}

	public function getAccessToken()
	{
		return !isset($this->_token['access_token'])?null:$this->_token['access_token'];
	}

	public function getRefreshToken()
	{
		return !isset($this->_token['refresh_token'])?null:$this->_token['refresh_token'];
	}

	/**
	 * @param $accessType
	 */
	public function setAccessType($accessType)
	{
		$this->_access_type = $accessType;
	}

	/**
	 * @param $clientId
	 */
	public function setClientId($clientId)
	{
		$this->_client_id = $clientId;
	}

	/**
	 * @param $clientSecret
	 */
	public function setClientSecret($clientSecret)
	{
		$this->_client_secret = $clientSecret;
	}

	/**
	 * @param $scope
	 */
	public function setScope($scope)
	{
		$this->_scope = $scope;
	}

	/**
	 * @param $url
	 */
	public function setRedirectUri($url)
	{
		$this->_redirect_uri = $url;
	}

	/**
	 * @param $token string
	 */
	public function setToken($token)
	{
		if(is_array($token))
			$this->_token = $token;
		elseif(is_string($token) && json_decode($token))
			$this->_token = json_decode($token, true);
	}

	/**
	 * @param $token string
	 */
	public function setAccessToken($token)
	{
		$this->_token['access_token'] = $token;
	}

	/**
	 * @param $token string
	 */
	public function setRefreshToken($token)
	{
		$this->_token['refresh_token'] = $token;
	}

	/**
	 * @param $type string
	 */
	public function setTokenType($type)
	{
		$this->_token['token_type'] = $type;
	}

	public function showError()
	{
		echo '<pre>';
		echo 'Error: ' . $this->_error;
		if(!empty($this->_error_description)){
			echo '<br>';
			echo 'Description: ' . $this->_error_description;
		}
		echo '</pre>';
	}
}