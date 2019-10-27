<?php

use PP\SMW\Redirects\Controller as RedirectController;

/**
 * @codeCoverageIgnore
 */
class PPsmwCustom {
	/**
	 * Registers the result formats.
	 */
	public static function onExtensionFunction() {
		
		$GLOBALS['smwgResultFormats']['pp abc list'] = 'PP\\SMW\\ResultPrinters\\AbcListPrinter';
		$GLOBALS['smwgResultFormats']['pp person list'] = 'PP\\SMW\\ResultPrinters\\PersonListPrinter';
		
	}
	/**
	 * Registers the parser functions.
	 */
	public static function onParserFirstCallInit( Parser $parser ) {

		$parser->setFunctionHook( 
			'SortKey', 
			'PP\SMW\ParserFunctions\SortKey::hook' 
		);

		$parser->setFunctionHook( 
        	'isInCategory', 
        	'PP\SMW\ParserFunctions\IsInCategory::hook'
        );
   }

   /**
    * Executed whenever a page is saved. Triggers the creation of a proxy 
    * subobject if the page is a(n eligible) redirect.
    *
    * This hook should return TRUE unless further hooks should not be processed.
    * Since we have no reason to interfere with other hooks, we always return
    * TRUE.
    */
   public static function onPageContentSaveComplete( 
		$wikiPage, 
		$user, 
		$mainContent, 
		$summaryText, 
		$isMinor, 
		$isWatch, 
		$section, 
		&$flags, 
		$revision, 
		$status, 
		$originalRevId, 
		$undidRevId ) {

   		// Extra safety measure: There is a theoretical possibility of an 
   		// infintite loop, in case the edit was done by this hook.
   		// It is hard to imagine how that would happen (even if someone were
        // to turn a "display page" into a redirect, the loop should terminate
        // after one iteration). But just in case, we terminate early if the
   		// edit was done by this extension.
   		if ( $user->getName() === 'PPsmwRedirectRecorder') {
   			return TRUE;
   		}
   		
    	RedirectController::process( $wikiPage );

    	return TRUE;
    }

   /**
	 * Creates the 'pp_smw_redirects' table on update.php run.
	 */
   public static function onLoadExtensionSchemaUpdates( $updater = NULL ) {
		$updater->addExtensionTable(
				'pp_smw_redirects',
				dirname( __FILE__ ) . '/sql/pp_smw_redirects.sql'
		);
	}
}