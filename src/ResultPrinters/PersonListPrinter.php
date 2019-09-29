<?php

namespace PP\SMW\ResultPrinters;

use SMWQueryResult;
use PP\SMW\Helpers\ResultHelper;

/**
 * Specialized alphabetical query format for person lists.
 *
 * NOTE:  The result format currently will not work for paged results, as
 * results have to be re-sorted in memory.
 *
 * @author Lars Juergenson
 */

class PersonListPrinter extends \PP\SMW\ResultPrinters\AbcListPrinter {


	/**
	 * @see ResultPrinter::getName
	 *
	 * {@inheritDoc}
	 */

	public function getName() {
		return "PP: Personenliste";
	}
	
	/**
	 * Constructs an nested array representation from the db result set.
	 *    
	 * The keys of the outer array are the first letter of the sortkey of
	 * the rows in the inner array.
	 *
	 * Here, "sortkey" means: 
	 *
	 *  (a) The value of the "sortkey property" (if this is defined and 
	 *      the page has the property) 
	 *      or
	 *  (b) The default sortkey of the page (otherwise)
	 *
	 * This allows us to sensibly sort by a property value, even if not all
	 * rows in the result set have the property set. This in turn is necessary
	 * since SMW does not allow easy access to category-specific sortkeys.
	 * 
	 * The values in the inner arrays are template-rendered strings, ready
	 * for display.
	 *
	 * @param SMWQueryResult $res The query result.
	 *
	 * @return array The nested array.
	 */

	protected function getKeyedContents( SMWQueryResult $res ){
		
		// If the sortkey property was not specified, we behave like a 
		// normal AbcList.
		if (!$this->params['sortkey property']) {
			return parent::getKeyedContents( $res );
		}

		$res->reset();
        $contents = [];

		while ( $row = $res->getNext() ) {

			$sortkey = '';
			$sortkey = ResultHelper::getPropertyValue(
				$this->params['sortkey property'], 
				$row
			);

			if ( !$sortkey ) {
				$sortkey = ResultHelper::getDefaultSortkey( $row );
			}
		

			$contents[$sortkey] = $this->renderRow( $row );

		}
		// Resort results to correctly interleave sortkey property values with
		// default sortkeys
		ksort($contents);

		return $this->sortByFirstLetter($contents);
	}

	/**
	 * @see ResultPrinter::getParamDefinitions
	 *
	 * {@inheritDoc}
	 */
	public function getParamDefinitions( array $definitions ) {
		$definitions = parent::getParamDefinitions( $definitions );

		$definitions[] = [
			'name' => 'sortkey property',
			'message' => 'pp-smw-printer-param-msg-sortkey_property',
			'default' => false,
		];

		return $definitions;
	}

}
