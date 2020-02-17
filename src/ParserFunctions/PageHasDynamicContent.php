<?php

namespace PP\SMW\ParserFunctions;

/**
 * Implements the {{#Page Has dynamic content}} parser function.
 *
 * This function is needed only to ensure that the pagelinks table is updated
 * with a pages' outgoing links whenEVER the page is rendered anew (i.e., not
 * taken from the cache). This is necessary for dynamic pages such as portal
 * pages, as the links can change with each rendering.
 *
 * (Standardly, outgoing links are only updated when the source code of the
 * page has changed, i.e. on an edit.)
 *
 */
class PageHasDynamicContent {


	public static function hook( &$parser) {

		$parser->getOutput()->setExtensionData( 'pp_smw_is_dynamic', true );
		return '';
	}
}