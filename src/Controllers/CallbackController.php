<?php

namespace Firebrand\Hail\Controllers;

use Firebrand\Hail\Api\Client;
use GuzzleHttp\Exception\ClientException;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Security\Permission;

/**
 * Hail Callback Controller
 *
 * Used by the Hail authorization process
 *
 * @package silverstripe-hail
 * @author Marc Espiard, Firebrand
 * @version 2.0
 *
 */
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

            $hail_api_client = new Client();
            try {
                $hail_api_client->fetchAccessToken($_GET['code']);
                // Update user ID everytime, in case Client ID has changed
                $hail_api_client->setUserID();
            } catch (ClientException $ex) {
                $request->getSession()->set('notice', true);
                $request->getSession()->set('noticeType', 'bad');
                $request->getSession()->set('noticeText', $ex->getMessage());

                return $this->redirect('admin/settings');
            }

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