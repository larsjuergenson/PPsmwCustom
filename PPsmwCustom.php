<?php

/**
 * @codeCoverageIgnore
 */
class PPsmwCustom {
	/**
	 * Registers the new result formats.
	 */
	public static function onExtensionFunction() {
		
		$GLOBALS['smwgResultFormats']['pp abc list'] = 'PP\\SMW\\ResultPrinters\\AbcListPrinter';
		$GLOBALS['smwgResultFormats']['pp person list'] = 'PP\\SMW\\ResultPrinters\\PersonListPrinter';
		
	}
	/**
	 * Registers the new parser functions
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
}