<?php

namespace PP\SMW\ResultPrinters;

use SMW\MediaWiki\Collator;
use PP\SMW\Helpers\TemplateHelper;
use PP\SMW\Helpers\ResultHelper;
use SMWQueryResult;

/**
 * Result format that prints rows in groups headed by alphabetic headers.
 *
 * This is a more flexible solution than the "category" format included in SMW.
 * The output of the rows and headers/letters are is completely done via 
 * templates. 
 *
 * Much like with SMW's "category" format, it does not make much sense to 
 * specify the "sort" parameter of the #ask parser function with this format. 
 * The "sort" parameter will only influence the database result, while the 
 * sorting-by-first-letter is done on the PHP side using the default category
 * sortkey. 
 *
 * This may change in the future, but for now, we don't need the additional 
 * complexity that taking "sort" into account would bring.
 * 
 *
 * @uses PP\SMW\Helpers\TemplateHelper to build template calls for the output.
 * @uses PP\SMW\Helpers\ResultHelper   to retrieve values from the db result 
 *                                     array.
 *
 * @author Lars Juergenson
 */

class AbcListPrinter extends \SMW\Query\ResultPrinters\ResultPrinter {

	/**
	 * @see ResultPrinter::getName
	 *
	 * {@inheritDoc}
	 */
	public function getName() {
		return "PP: Alphabetische Liste";
	}

	/**
	 * Formatiert den Ausgabetext. 
	 *
	 * For us, the main entry point. The method is called by SMW in the course
	 * of constructing the query result output.
	 *
	 * @param SMWQueryResult $res        The query result we are displaying.
	 * @param string         $outputMode Ignored: We always return wikitext.
	 *
	 * @see ResultPrinter::getResultText
	 *
	 * 
	 */
	protected function getResultText( SMWQueryResult $res, $outputMode ) {

		return $this->renderNestedList( $this->getKeyedContents($res) );
	}

	/**
	 * Renders the keys of a nested array as headings.
	 *
	 * The elements of the inner arrays are expected to be already-rendered
	 * rows. They are simply concatenated and added after the heading. 
	 *
	 * If the 'header template' parameter is specified in the query, its value
	 * is used to render the header rows. Otherwise, they are rendered as a 
	 * (single) bulleted list with the heading in bold. 
	 *
	 *  @param array $contents
	 *
	 *  @return string The rendered list.
	 */

	private function renderNestedList($contents) : string {

		$this->hasTemplates = true;

		$output = '';

		foreach ($contents as $heading => $entries) {

			if ( $this->params['header template'] ) {

				$args = array('heading' => $heading);
				$args = $args + $this->params['header template arguments'];

				$output .= TemplateHelper::getTemplateCall(
					$this->params['header template'],
					$args
				);

			} else {

				$output .= "* '''" . $heading . "'''\n";

			}

			$output .= join('',$entries);
			$output .= "\n";
		}

		return $output;
	}

	/**
	 * Constructs an nested array representation from the db result set.
	 *    
	 * The keys of the outer array are the first letter of the (default)
	 * category sortkey of the rows in the inner array.
	 *
	 * The values in the inner arrays are template-rendered strings, ready
	 * for display.
	 *
	 * @param SMWQueryResult $res The query result.
	 *
	 * @return array The nested array.
	 */

	protected function getKeyedContents( SMWQueryResult $res ){

		$res->reset();
		$contents = [];

		while ( $row = $res->getNext() ) {

			$sortkey = ResultHelper::getDefaultSortkey( $row );

			$contents[$sortkey] = $this->renderRow( $row );
		}

		return $this->sortByFirstLetter($contents);
	}

	/**
	 * Constructs an nested array from a flat array.
	 *    
	 * $entries must be an array keyed by strings. In the returned array, the
	 * key is alwyas the first letter of the key in the input array. I.e.
	 *
	 * array(
	 *   'abc' => 'foo',
	 *   'adc' => 'bar',
	 *   'brt' => 'baz',
	 * )
	 *
	 * is transformed into:
	 *
	 * array(
	 *   'a' => array('foo', 'bar'),
	 *   'b' =? array('baz'),
	 * )
	 *
	 * @param SMWQueryResult $entries The flat input array.
	 *
	 * @return array The nested array.
	 */
	protected function sortByFirstLetter( array $entries ) : array {
		
		$sorted = [];

		foreach ( $entries as $key => $entry ) {

			$first = Collator::singleton()->getFirstLetter( $key );

			if ( !isset( $contents[$first] ) ) {
				$contents[$first] = [];
			}

			$contents[$first][] = $entry;
		}

		return $contents;
	}

	/**
	 *  Renders a row, using the template named in the "template" paramater.
	 *
	 *  @param array $row Database result row.
	 *
	 *  @return string The result of rendering the row.
	 */

	protected function renderRow( $row ) : string {

		$args = ResultHelper::getAllValues( $row, ', ');
		$args = $args + $this->params['template arguments'];

		return TemplateHelper::getTemplateCall(
			$this->params['template'], 
			$args
		);
		
	}

	/**
	 * @see ResultPrinter::getParamDefinitions
	 *
	 * {@inheritDoc}
	 */
	public function getParamDefinitions( array $definitions ) {
		$definitions = parent::getParamDefinitions( $definitions );

		$definitions[] = [
			'name' => 'template',
			'message' => 'pp-smw-printer-param-msg-template',
			'default' => 'ABCListRow'
		];

		$definitions[] = [
			'name' => 'template parameters',
			'message' => 'pp-smw-printer-param-msg-template_parameters',
			'default' => false,
			'islist' => true,
		];

		$definitions[] = [
			'name' => 'header template',
			'message' => 'pp-smw-printer-param-msg-header_template',
			'default' => false,
		];

		$definitions[] = [
			'name' => 'header template parameters',
			'message' => 'pp-smw-printer-param-msg-header_template_parameters',
			'default' => false,
			'islist' => true,

		];

		return $definitions;
	}
	/**
	 * @see ResultPrinter::handleParameters
	 *
	 * {@inheritDoc}
	 */
	protected function handleParameters( array $params, $outputmode ) {

		parent::handleParameters( $params, $outputmode );

		if ( $params['template parameters'] ) {
			$this->params['template arguments'] 
			     = self::parseArguments($params['template parameters']);
		}  else {
			$this->params['template arguments'] = array();
		}

		if ( $params['header template parameters'] ) {
			$this->params['header template arguments'] 
			     = self::parseArguments($params['header template parameters']);
		} else {
			$this->params['header template arguments'] = array();
		}
	}

	/**
	 * Parses template arguments given as a parameter to the query.
	 *
	 * Takes an array of the form 
	 *   array( 
	 *     'key1=value1', 
	 *     'key2 = value2', 
	 *      ...
	 *   )
	 * and returns one of the form
	 *   array( 
	 *      'key1' => 'value1', 
	 *      'key2' => 'value2',
	 *      ...
	 *   )
	 *
	 * @param array $params Each element is expected to be a string in the
	 *                      form "key=value", with arbitrary whitespace around
	 *                      the "=".
	 *
	 * @return array An array keyed by the argument names.
	 * 
	 */

	private static function parseArguments ( array $params ) : array {
		$args = [];
		foreach ( $params as $arg ){
			list( $name, $value ) = explode('=', $arg);
			$args[trim($name)] = trim($value);
		}
		return $args;
	}
}
