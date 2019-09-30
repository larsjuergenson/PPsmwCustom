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

}