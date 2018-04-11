<?php

namespace Firebrand\Hail\Controllers;

use Firebrand\Hail\Api\Provider;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Security\Permission;

class CallbackController extends Controller
{
    private static $allowed_actions = [
        'index',
    ];

    public function index(HTTPRequest $request)
    {
        // Check user is admin and logged in
        if (Permission::check('ADMIN ')) {
            //Validate request
            if (!isset($_GET['code'])) {
                //Error message for user
                $request->getSession()->set('notice', true);
                $request->getSession()->set('noticeType', 'bad');
                $request->getSession()->set('noticeText', 'No OAuth redirect code found in the callback request.');

                return $this->redirect('admin/settings');
            }

            $provider = new Provider();
            try {
                $provider->fetchAccessToken($_GET['code']);
            } catch (Exception $ex) {
                $request->getSession()->set('notice', true);
                $request->getSession()->set('noticeType', 'bad');
                $request->getSession()->set('noticeText', $ex->getMessage());

                return $this->redirect('admin/settings');
            }

            // Update user ID everytime, in case Client ID has changed
            $provider->setUserID();

            //Return message for user
            $request->getSession()->set('notice', true);
            $request->getSession()->set('noticeType', 'good');
            $request->getSession()->set('noticeText', 'Hail is now authorized on the website');

            return $this->redirect('admin/settings/');
        }

        //401 Unauthorized access by default
        return $this->httpError(401, 'Unauthorized access.');
    }
}