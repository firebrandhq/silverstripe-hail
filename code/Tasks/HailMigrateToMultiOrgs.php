<?php

/**
 * Task used to migrate to the Hail multi organisations
 *
 * @author Firebrand Holding Limited <developers@firebrand.nz>
 */
class HailMigrateToMultiOrgs extends BuildTask {
	
	/**
	 * @return string
	 */
	public function getDescription() {
		return _t(
            'Hail',
            'Migrate Hail to the multi organisations functionality'
        );
	}

	/**
	 * @param SS_HTTPRequest $request
	 */
	public function run($request) {

	   if(HailOrganisation::get()->count() > 0) {
			echo "A Hail organisation already exists. Aborting\n\n";
			exit();
	   }
	   
	   try {
		   $q = new SQLQuery();
		   $q->setFrom('SiteConfig');
		   $q->selectField('*');
		   $q->addWhere('ID = 1');
		   
		   $siteconfig = $q->execute()->first();
		   
		   echo "Creating Hail organisation... ";
		   $org = new HailOrganisation();
		   $org->Title = isset($siteconfig['Title']) ? $siteconfig['Title'] : '';
		   $org->HailClientID = isset($siteconfig['HailClientID']) ? $siteconfig['HailClientID'] : '';
		   $org->HailClientSecret = isset($siteconfig['HailClientSecret']) ? $siteconfig['HailClientSecret'] : '';
		   $org->HailAccessToken = isset($siteconfig['HailAccessToken']) ? $siteconfig['HailAccessToken'] : '';
		   $org->HailAccessTokenExpire = isset($siteconfig['HailAccessTokenExpire']) ? $siteconfig['HailAccessTokenExpire'] : '';
		   $org->HailRefreshToken = isset($siteconfig['HailRefreshToken']) ? $siteconfig['HailRefreshToken'] : '';
		   $org->HailRedirectCode = isset($siteconfig['HailRedirectCode']) ? $siteconfig['HailRedirectCode'] : '';
		   $org->HailUserID = isset($siteconfig['HailUserID']) ? $siteconfig['HailUserID'] : '';
		   $org->HailOrgID = isset($siteconfig['HailOrgID']) ? $siteconfig['HailOrgID'] : '';
		   $org->HailTimeout = isset($siteconfig['HailTimeout']) ? $siteconfig['HailTimeout'] : 10;
		   $org->PrimaryHailHolderID = isset($siteconfig['PrimaryHailHolderID']) ? $siteconfig['PrimaryHailHolderID'] : 0;
		   
		   $org->write();
		   echo "[SUCCESS]\n";
		   
		   foreach (HailApiObject::fetchables() as $hailObjType) {
				echo "Migrating $hailObjType... ";
				foreach($hailObjType::get() as $item) {
					$item->OrganisationID = $org->ID;
					$item->write();
				}
				echo "[SUCCESS]\n";
			}
		   
		   echo "\nHail migration was successful.\n";
		   echo "Please note that the redirect code has changed, You will need to update this in Hail\n";
		   
		   if(isset($_SERVER['HTTP_HOST'])) {
			  echo Director::absoluteURL('HailCallbackController', true) . '?org=' . $org->ID;
		   } else {
			  echo 'http(s)://yourdomain.com/HailCallbackController?org=' . $org->ID; 
		   }
		   echo "\n\n";
	   }
	   catch(Exception $e) {
		   echo "\n\n" . $e->getMessage();
	   }
	}
}
