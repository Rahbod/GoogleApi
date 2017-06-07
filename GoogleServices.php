<?php
use RahbodGoogleApi\GoogleOAuth;
use RahbodGoogleApi\GoogleCalendar;
use RahbodGoogleApi\GoogleDrive;

/**
 * Class GoogleServices
 *
 * @property GoogleCalendar $calendar
 * @property GoogleDrive $drive
 * @property GoogleOAuth $oauth
 */
class GoogleServices
{
    /**
     * @var GoogleCalendar
     */
    private $_calendar;
    /**
     * @var GoogleDrive
     */
    private $_drive;
    /**
     * @var GoogleOAuth
     */
    private $oauth;

    /**
     * @var mixed
     */
    private $_config;

    public function __construct()
    {
        $this->_config = json_decode(file_get_contents(__DIR__ . '/config.json'));
    }

    public function __get($name)
    {
        return $this->{'get' . ucfirst($name)}();
    }

    public function isConnected()
    {
        if($this->getOauth() && $this->getOauth()->getAccessToken() && !$this->getOauth()->isAccessTokenExpired()){
            return true;
        }elseif($this->getOauth()->getToken() && $this->getOauth()->isAccessTokenExpired() && $this->getOauth()->getRefreshToken()){
            $this->getOauth()->refreshAccessToken();
            return true;
        }
        return false;
//        elseif($this->getOauth()->isAccessTokenExpired())
//            die('Access Token not expired. Expired in: ' . date('Y/m/d H:i:s', $this->getOauth()->getToken()['created'] + $this->getOauth()->getToken()['expires_in']));
//        elseif(!$this->getOauth()->getRefreshToken())
//            die('Access Token expired. refresh token not exists.');
    }

    /**
     * Authenticate in google server with OAuth 2
     *
     * @return array OAuth response token
     */
    public function authenticate()
    {
        if(!$this->isConnected()){
            return $this->getOauth()->authenticate();
        }
        return true;
    }

    public function setAuthToken($accessToken, $tokenType, $expiresIn, $created, $refreshToken = null)
    {
        $this->getOauth()->setToken([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => $expiresIn,
            'token_type' => $tokenType,
            'created' => $created
        ]);
    }

    public function getCalendar()
    {
        $this->authenticate();
        if(!$this->_calendar){
            $this->_calendar = new GoogleCalendar($this->getOauth()->getAccessToken());
            $this->_calendar->debug = $this->_config->debug_mode;
        }
        return $this->_calendar;
    }

    public function getDrive()
    {
        $this->authenticate();
        if(!$this->_drive){
            $this->_drive = new GoogleDrive($this->getOauth()->getAccessToken());
            $this->_drive->debug = $this->_config->debug_mode;
        }
        return $this->_drive;
    }

    public function getOauth()
    {
        if(!$this->oauth){
            $this->oauth = new GoogleOAuth();
            $this->oauth->setClientId($this->_config->client_id);
            $this->oauth->setClientSecret($this->_config->client_secret);
            $this->oauth->setRedirectUri($this->_config->auth_redirect_uri);
            $this->oauth->setAccessType('offline');
            $this->oauth->setScope(implode(' ', $this->_config->auth_scopes));
        }
        return $this->oauth;
    }

    public function getAuthToken()
    {
        return $this->getOauth()->getToken();
    }
}