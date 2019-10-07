<?php

namespace PP\SMW\Helpers;

use SMWResultArray;
use SMWQueryResult;
use SMW\DIWikiPage;
use SMW\DataItem;
use SMW\DataValueFactory;

class SemanticDataHelper {


	public static function getStringValuesForProperty( 
		string $property_name, 
		$data 
	) {
		return self::getStringValues(
			$data->getPropertyValues(new \SMW\DIProperty( $property_name ))
		);
		
	}

	protected static function getStringValues ( array $dataItems) {
		$values = [];
		foreach ( $dataItems as $dataItem ) {
			$values[] = self::getStringValue( $dataItem );
		}
		return $values;
	}

	protected static function getStringValue( $dataItem ) {
		
		return DataValueFactory::getInstance()->newDataValueByItem($dataItem)
		                                      ->getWikiValue();
	}
}