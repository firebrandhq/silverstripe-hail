<?php

class HailCallbackController extends Controller {
	/**
	* Default URL handlers - (Action)/(ID)/(OtherID)
	*/

	private static $allowed_actions = array(
		'index',
	);

	public function index(SS_HTTPRequest $request) {

		$org = isset($_GET['org']) ? HailOrganisation::get()->byID($_GET['org']) : HailOrganisation::get()->first();

		if($org->canEdit()) {

			$org->HailRedirectCode = $_GET['code'];

			$provider = new HailProvider($org);

			try {
				$token = $provider->getAccessToken('authorization_code', [
					'code' => $org->HailRedirectCode,
				]);

			} catch (Exception $ex) {
				die($ex->getMessage());
			}

			$org->HailAccessToken = $token->accessToken;
			$org->HailAccessTokenExpire = $token->expires;
			$org->HailRefreshToken = $token->refreshToken;

			$org->write();

			// Refresh site config and save the user id
			$user = HailApi::getUser($org);
			$org->HailUserID = $user->id;
			$org->write();
		}

		$this->redirect('admin/settings/EditForm/field/Organisations/item/' . $org->ID);

	}
}
