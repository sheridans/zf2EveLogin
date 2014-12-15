<?php
namespace zf2EveLogin\Service;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Http\Client as httpClient;
use Zend\Session\Container as sessionContainer;


class authService implements ServiceLocatorAwareInterface
{
    protected $services;

    protected $authUrl = "/oauth/authorize";
    protected $sessionName = 'zf2EveLogin';

    /**
     * Builds the redirect url for EVE SSO server
     * @return string
     * @throws \Exception
     */
    public function getRedirectUrl()
    {
        $state = uniqid();

        $session = new sessionContainer($this->sessionName);
        $session->offsetSet('state', $state);

        $redirectUrl = $this->getConfig('server_url') . $this->authUrl
            . '?response_type=code&redirect_uri=' . $this->getConfig('callback_url')
            . '&client_id=' . $this->getConfig('client_id')
            . '&scope=&state=' . $state;

        return $redirectUrl;
    }

    /**
     * Use authorisation code provided by CCP from developer portal
     * to request verification.
     *
     * @param string $code authorisation code
     * @param string $state original state
     * @throws \Exception
     * @return bool|string access token or nothing
     */
    public function auth($code, $state)
    {
        $session = new sessionContainer($this->sessionName);
        if (!$session->offsetExists('state'))
        {
            throw new \Exception('State variable is not valid');
        } else if ($session->offsetGet('state') !== $state) {
            throw new \Exception("State variable is not valid");
        }

        $headers = array (
            'Authorization' => 'Basic ' . base64_encode($this->getConfig('client_id') . ':' . $this->getConfig('secret')),
            'User-Agent' => $this->getConfig('user-agent'),
        );

        $url = $this->getConfig('server_url') . '/oauth/token';
        $params = array(
            'grant_type' => 'authorization_code',
            'code' => $code,
        );

        $response = $this->fetchUrl($url, $params, 'POST', $headers);
        if ($response->isSuccess() && $result = json_decode($response->getContent()) !== null)
        {
            $result = json_decode($response->getContent());
            if ($result && isset($result->access_token))
            {
                return $result->access_token;
            }
        }
        return false;
    }

    /**
     * Verify CCP authentication
     * @param string $token
     * @return array|bool array containing character details or nothing
     */
    public function verify($token)
    {
        $headers = array(
            'Authorization' => 'Bearer ' . $token,
            'User-Agent' => $this->getConfig('user-agent'),
        );
        $response = $this->fetchUrl($this->getConfig('server_url') . '/oauth/verify',  null, 'GET', $headers);
        if ($response->isSuccess() && $result = json_decode(($response->getContent())))
        {
            if (isset($result->CharacterID) && isset($result->CharacterName))
            {

                $session = new sessionContainer($this->sessionName);
                $session->offsetSet('characterName', $result->CharacterName);
                $session->offsetSet('characterId', $result->CharacterID);
                return array('characterName' => $result->CharacterName, 'characterId' => $result->CharacterID);
            }
        }
        return false;
    }
    /**
     * Fetch content from url
     * @param string $string the url to fetch
     * @param null|array $parameters null or key/value pair array
     * @param string $method fetch method (POST/GET)
     * @param array $_headers headers
     * @return \Zend\Http\Response
     */
    protected function fetchUrl($string, $parameters = null, $method = 'GET', $_headers)
    {
        $client = new httpClient($string, array(
            'adapter'       => 'Zend\Http\Client\Adapter\Curl',
            'httpVersion'   => '1.0',
            'maxredirects'  => 0,
            'timeout'       => 30,
            'keepalive'     => true,
            'curloptions'   => array(
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_ENCODING => 'UTF-8',
            ),
        ));
        $client->setMethod(strtoupper($method));

        if (is_array($parameters) && !empty($parameters))
        {
            if ($method == 'POST')
            {
                $client->setParameterPost($parameters);
            }
            else
            {
                $client->setParameterGet($parameters);
            }

        }

        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders($_headers);

        $response = $client->send();
        return $response;
    }

    /**
     * Checks to see if session vars have been setup, thus the user being
     * authenticated.
     *
     * @return bool
     */
    public function hasIdentity()
    {
        $session = new sessionContainer($this->sessionName);
        $characterName = $session->offsetGet('characterName');
        $characterId = $session->offsetGet('characterId');

        if ($characterName !== null && intval($characterId) > 0)
        {
            return true;
        }
        return false;
    }

    /**
     * Retrieve the character ID and character name from session
     * @return array
     */
    public function getIdentity()
    {
        $session = new sessionContainer($this->sessionName);
        $characterName = $session->offsetGet('characterName');
        $characterId = $session->offsetGet('characterId');
        return array('characterName' => $characterName, 'characterId' => $characterId);
    }

    /**
     * Logout user
     */
    public function logout()
    {
        $session = new sessionContainer($this->sessionName);
        $session->getManager()->getStorage()->clear($this->sessionName);
    }

    /**
     * Grab vars from configuration
     * @param string $var the name of the value to grab from config
     * @return mixed
     * @throws \Exception
     */
    public function getConfig($var)
    {
        $config = $this->getServiceLocator()->get('config');
        $configVar = $config['zf2EveLogin'];
        if (array_key_exists($var, $configVar))
        {
            return $configVar[$var];
        }
        throw new \Exception('config variable not found: '. $var);
    }


    /**
     * ZF2 service locator function
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->services = $serviceLocator;
    }

    /**
     * ZF2 service locator function
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->services;
    }
}