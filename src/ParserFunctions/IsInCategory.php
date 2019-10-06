<?php

namespace PP\SMW\ParserFunctions;

use Title;
use PP\SMW\Helpers\PageHelper;
use PP\SMW\Helpers\DatabaseHelper;

/**
 * Implements the {{#isInCategory}} parser function.
 *
 * This function takes two arguments: A category name and a page title. If the
 * page belonging to that title is in the category, it returns the string 
 * 'TRUE'. Otherwise, it returns the empty string.
 *
 * The function is intended to be used with the #if-function of the 
 * ParserFunctions extensions. We could have gone the the route of the PageInCat
 * and CategoryTests extensions, and provided full if-constructs, but it strikes
 * me as suboptimal if every extension that implements a "test" reproduces 
 * the #if-functionality.
 *
 * NOTE: This function is not intended to check the categories of the current
 * page. It will allow this, but strange things may happen if you do 
 * conditional category assignment based on the output of this function.
 */
class IsInCategory {

	/**
	 * Tests whether a page is in a category.
	 *
	 * @param string $categoryName The name of the category
	 * @param Title  $title        Title object for the page we are checking.
	 *
	 *
	 * @return bool 
	 *
	 */
	public static function isInCategory(  string $category, string $page ) : bool {

		if ( $category === '' || $page === '') {
			return FALSE;
		}

		list( $catKey, $catNS ) = self::getDBKeys( 'Category:' . $category );
		
		list( $pageKey, $pageNS ) = self::getDBKeys( $page );
	
		if ( !$catKey || !$pageKey ) {
			return FALSE;
		}
		return self::queryCategorylinksTable( $pageNS, $pageKey, $catKey );
	}

	private static function getDBKeys( string $pageName ) {

		$title = Title::newFromText( $pageName );

		if ( !( $title instanceOf Title ) || !$title->exists() ) {
			return NULL;
		}

		return array( $title->getDBkey(), $title->getNamespace() );
	}

	private static function queryCategorylinksTable($pageNS, $pageKey, $catKey) {
		// Query the categorylinks table
		$res = DatabaseHelper::getConnection('read-only')->select(
			array( 'page', 'categorylinks' ),
			'cl_from',
			array(
				'page_id=cl_from',
				'page_namespace' => $pageNS,
				'page_title' => $pageKey,
				'cl_to' => $catKey
			),
			__METHOD__,
			array( 'LIMIT' => 1 )
		);

		if ( $res->numRows() == 0 ) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	public static function hook( &$parser, $category = '', $page = '') {
		return self::isInCategory( $category, $page ) ? 'TRUE' : '';
	}
}