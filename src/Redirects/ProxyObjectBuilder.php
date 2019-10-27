<?php

namespace PP\SMW\Redirects;

use Wikipage;
use PP\SMW\Helpers\PageHelper;
use PP\SMW\Helpers\TemplateHelper;

/**
  * Constructs a string that declares a subobject that stands proxy for a 
  * redirected page.
  *
  * Main public entry point is the static function buildProxyFor(), which 
  * takes a Wikipage object and returns the code declaring the corresponding 
  * proxy subobject.
  *
  */
class ProxyObjectBuilder {

	/** 
	 * Name of the SMW property that specifies how values will occur in 
	 * lists. 
	 */

	const DISPLAY_AS_PROPERTY = 'Im_Portal_anzeigen_als';

	/** 
	 * @var PageInfo
	 */

	private $info; 

	/**
	 * Returns a subobject declaration for the proxy object for $page.
	 *
	 * Usual entry point.
	 *
	 * @param Wikipage $page 
	 *
	 * @return string Contains a subobject declaration, suitable for inclusion
	 *                in a wiki page.
	 */

	public static function buildProxyFor(Wikipage $page) : string {
		$builder = new self(PageHelper::for($page));
		return $builder->getDeclaration();
	}

	/**
	 * Constructor.
	 *
	 * @param PageInfo $info PageHelper object that provides information about
	 *                       the redirect page we are building a proxy object
	 *                       for.
	 */

	public function __construct(PageHelper $info) {
		$this->info = $info;
	}

	/**
	 * Returns a subobject declaration for the proxy object.
	 *
	 * Only public method. Usually called via 
	 * SubObjectBuilder::getProxyDeclarationFor().
	 *
	 *
	 * @return string Contains a subobject declaration, suitable for inclusion
	 *                in a wiki page.
	 */

	public function getDeclaration() : string {

		$args = [];

		// Categories
		$args['@category'] = $this->getCategoryArgument(';;', ['Redirect']);

		// Sortkey
		$args['@sortkey'] = $this->getSortKey();

		// SMW Properties
		$args = $args + $this->getPropertyArguments(';;');

		$command = '#subobject:' . $this->getSortKey();

		return TemplateHelper::getTemplateCall($command, $args);

	}

	private function getSortKey() : string {
		$mwKey = $this->info->getSortKey();
		return $mwKey ? $mwKey : '{{#SortKey:' . $this->info->getName() . '}}';
	}

	/**
	 * Retrieve the list of categories the page belongs to, as a string.
	 *
	 * @param string $separator Will be inserted between the names of 
	 *        the categories.
	 * @param array $exclude Array of category names that should not be 
	 *                       included in the list.
	 *
	 * @return array Array of strings, each of which is a category name 
	 *               without prefix.
	 */
	private function getCategoryArgument(string $separator, array $exclude = array()) : string {
		$categories = array_diff($this->info->getCategories(), $exclude);
		$arg = implode($separator, $categories) . '|+sep=' . $separator;
		return $arg;
	}

	private function getPropertyArguments(string $separator) {

		$properties = $this->info->getPropertyValues();

		$args = [];

		foreach ( $properties as $key => $values ) {
			$args[$key] = implode( $separator, $values );
			if ( count($values) > 1) {
				$args[$key] .= '|+sep=' . $separator;
			}
		}
		
		// Add default property values
		$args = $args + $this->getDefaultPropertyArguments();

		return $args;

	}

	private function getDefaultPropertyArguments() : array {
		$title = $this->info->getName();

		$args =  array(
			self::DISPLAY_AS_PROPERTY => "[[$title|{{KlammernEntfernen|$title}}]]",
			'Subobjekt-Typ' => 'Redirect',
		);
		return $args;
	}

}