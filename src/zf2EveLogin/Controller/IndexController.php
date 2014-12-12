<?php
namespace zf2EveLogin\Controller;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * Class IndexController
 *
 * Routing functions
 * @package zf2EveLogin\Controller
 */
class IndexController extends AbstractActionController
{
    /**
     * Display SSO welcome page, if not logged in then redirect to EVE SSO
     * @return array
     */
    public function indexAction()
    {
        $authService = $this->getServiceLocator()->get('eve_sso_auth_service');
        if (!$authService->hasIdentity())
        {
            $this->redirect()->toRoute('zf2-eve-login/login');
        }
        $user = $authService->getIdentity();
        return array('user' => $user);
    }

    /**
     * Redirect to EVE SSO
     */
    public function loginAction()
    {
        $authService = $this->getServiceLocator()->get('eve_sso_auth_service');
        $url = $authService->getRedirectUrl();
        $this->redirect()->toUrl($url);
    }

    /**
     * Call back from EVE SSO, authorise login
     * @return array
     */
    public function authoriseAction()
    {
        // Get the code and state passed back from EVE SSO
        $code = $this->params()->fromQuery('code', null);
        $state = $this->params()->fromQuery('state', null);

        $authService = $this->getServiceLocator()->get('eve_sso_auth_service');
        $token = $authService->auth($code, $state);
        if ($token !== null)
        {
            if ($result = $authService->verify($token))
            {
                $this->redirect()->toRoute('zf2-eve-login');
            }
        }
        return array();
    }

    /**
     * Logout - destroy session variables
     */
    public function logoutAction()
    {
        $authService = $this->getServiceLocator()->get('eve_sso_auth_service');
        $authService->logout();
    }
}
