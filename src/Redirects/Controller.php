<?php

namespace PP\SMW\Redirects;

use WikiPage;
use PP\SMW\Helpers\PageHelper;
use PP\SMW\Redirects\ProxyObjectBuilder;

class Controller {

	public static function process( WikiPage $page ) {
	
		// Is the current page one that should be recorded as a redirect?
		$displayName = self::getDisplayPageNameFor( $page );

		if ( self::shouldBeRecorded( $page ) ) {

			// Enter/update the database record for the page.
			RecordHandler::getInstance()->record( 
				$page, 
				$displayName,
				ProxyObjectBuilder::buildProxyFor( $page )
			);

			// Rebuild the display page.
			DisplayHandler::update($displayName);

		} elseif ( RecordHandler::isRecorded( $page ) ) {
			// The page should not be recorded, but it is.
			// (e.g., it was a redirect before the current edit)

			// Remove the database record for the page
			RecordHandler::getInstance()->delete( $page );

		    // Regenerate the corresponding display page
			DisplayHandler::update( $displayName );

		}
		
	}

	private static function shouldBeRecorded(WikiPage $page) : bool {

		// We only treat pages in the main (article) namespace.
		if ( $page->getTitle()->getNamespace() !== NS_MAIN ) {
			return false;
		}

		// A page is only recorded if it is a redirect.
		if (!$page->isRedirect()) {
			return false;
		}

		$categories = PageHelper::getCagegoriesOf($page);


		// A page is (for now) only recorded if it is a Neo page.
		if (!in_array('Kategorie:Perry_Rhodan_Neo', $categories)) {
			return false;
		}

		// A page is not recorded if it has the category
		// 'Keine fehlenden Kategorieeinträge'.
		// (Since then, the redirect is just a variant spelling.)
		if (in_array('Kategorie:Keine_fehlenden_Kategorieeinträge', $categories)) {
			return false;
		}

		// Otherwise, yes.
		return true;
	}

	public static function getDisplayPageNameFor(WikiPage $page) : string {
		$begins_with = substr(
			$page->getTitle()->getText(),
			0,
			3
		);
		return 'Perrypedia:Auto/Redirects/' . $begins_with;
	}

}