<?php
namespace PP\SMW\Helpers;

use Wikipage;
use Title;

/**
  * Provides information about a page needed for building a proxy subobject.
  *
  * This class handles all SMW-specific operations (and some non-SMW-specific
  * operations) for retrieving info from a Wikipage object.
  *
  * Its main intended use is to allow PP\SMW\Redirects\SubObjectBuilder to
  * obtain the necessary information for building a proxy subobject.
  */
class PageHelper {

	/** 
	 * @var Wikipage The page we are providing information for. 
	 */

	private $page; 

	private $semanticData;

	/**
	 * Factory method.
	 *
	 * @param Wikipage $page 
	 *
	 * @return PageInfo
	 */

	public static function for(Wikipage $page) : PageHelper {
		return new PageHelper($page);
	}

	/**
	 * Constructor, usually not called directly.
	 *
	 *
	 * @param Wikipage $page Page we are building a proxy object for.
	 */

	public function __construct(Wikipage $page) {
		$this->page = $page;
	}

	public function getName() : string {
		return $this->page->getTitle()->getText();
	}

	public function getDBKey() : string {
		return $this->page->getTitle()->getDBKey();
	}
	/**
	 * Retrieve the list of categories the page belongs to.
	 *
	 * @return array Array of strings, each of which is a category name 
	 *               without prefix.
	 */
	public function getCategories() : array {
		return self::getCagegoriesOf($this->page);
	}

	/**
	 * Extract the values of properties, in a form that can be used to define
	 * the same values on another page.
	 *
	 * Implementation note:
	 * While the parser functions of SMW do their job on redirect pages,
	 * SMW chooses to discard the property values later on. So what we do here
	 * is to let Mediawiki parse the page, then extract the property values and
	 * encode them so that they can be inserted into the proxy subobject.
	 *
	 *
	 * @return string An array of property values (as strings),  keyed by 
	 *                property names.
	 *
	 * LIMITATIONS:
	 * - Right now, only user-defined properties get returned.
	 */
	public function getPropertyValues() : array {

		$data = $this->getSemanticData();

		$values = array();
		foreach ( $data->getProperties() as $property ) {

			if ( $property->isUserDefined() ){

				$key = $property->getKey();

				$values[$key] = SemanticDataHelper::getStringValuesForProperty( 
					$key,
					$data 
				);
			}
		}
		return $values;
	}

	public function getSortKey() {
		// SMW conveniently stores the (default) sort key in the property
		// _SKEY
		return SemanticDataHelper::getStringValuesForProperty( '_SKEY', $this->getSemanticData() )[0];

	}

	protected function getSemanticData() {
		if (!$this->semanticData) {
			$this->semanticData = $this->page
				->getParserOutput( $this->page->makeParserOptions('canonical') )
		  		->getExtensionData('smwdata');
		}

		// Property values are saved as 'extension data' for SMW after
		// a parse.

		return $this->semanticData;
	}

	public static function getCagegoriesOf(WikiPage $page) : array {
		// We get the links from the parser output, as we need the most
		// current version early.
		return $page
			->getParserOutput( $page->makeParserOptions('canonical') )
			->getCategoryLinks();
	}

}