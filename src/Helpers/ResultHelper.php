<?php

namespace PP\SMW\Helpers;

use SMWResultArray;
use SMWQueryResult;
use SMW\DIWikiPage;




/**
 * Encapsulates the details of the (complex) structure of SMWs result sets.
 *
 * SMW's result sets are very flexible, but also quite complex. In many cases,
 * a class that handles results does not need to know the precise details of
 * their structure. This class is intended to hide this complexity, providing
 * static functions that extract values as plain strings (or arrays of those)
 * from result rows and individual fields.
 *
 */
class ResultHelper {

   /**
     * Get the wikipage associated with this result row.
     *
     * @param ResultArray[] $row 
     *
     * @return DIWikiPage
     */
	public static function getPage( array $row ) : DIWikiPage {
		return 	$row[0]->getResultSubject();
	}


	/**
     * Get the default category sortkey of the page belonging to the result row.
     *
     * @param ResultArray[] $row The result row.
     *
     * @return string 
     */
	public static function getDefaultSortkey( $row ) : string {
		return $row[0]->getStore()->getWikiPageSortKey( self::getPage( $row ) );
	}

    /**
     * Get a single value of a single property.
     *
     * @param string $label The label of the property. This can be different 
     *                      from the name of the property if specified in the 
     *                      the query (?property = label).
     * @param ResultArray[] $row
     * @param $index        The index of the value to be returned, in case 
     *                      there is more than one value for the property. 
     *                      Defaults to 0, which is appropriate for 
     *                      single-valued properties. 
     *
     * @return string The value as a string or NULL, if the property is not set
     *                or was not included in the query.
     */
	public static function getPropertyValue(
		string $label, 
		array $row, 
		int $index = 0 
	) : ?string {
		$values = self::getAllValues( $row );
		if ( !empty( $values[$label] ) ) {
			return $values[$label][$index];
		} else {
			return null;
		}
	}

	/**
     * Get all values from a result row.
     *
     * The returned array is alwyas keyed by the labels of the associated 
     * properties. 
     *
     * The format of the values depends on whether the $sep parameter
     * is specified:
     *
     * - If $sep is a string, then this string will be used to concatenate
     *   multiple values for a single property. The returned array will be
     *   a flat array of strings.
     * - If $sep is not specified, the values will ALWAYS be arrays of strings,
     *   even for single-valued properties.
     *
     * @param ResultArray[] $row
     * @param string        $sep  (optional) 
     *
     *
     * @return array 
     */
	public static function getAllValues( 
		array $row, 
		?string $sep = null 
	) : array {

		reset($row);

		$values = array();
		
		foreach ( $row as $field ) {

			$label = $field->getPrintRequest()->getLabel();

			$values[$label] = self::getValuesFromField( $field, $sep );				
		}
		return $values;
	}

	/**
     * Get the values for a single "field" lf a result row.
     *
     * A "field" is comprised of all values for one property. Result rows
     * are just arrays of fields.
     *
     * The return value depends on whether the $sep parameter
     * is specified:
     *
     * - If $sep is a string, then this string will be used to concatenate
     *   multiple values. The return value will be a single string.
     * - If $sep is not specified, the return value will be an array of strings,
     *   one for each value, even if the provided field has only one value.
     *
     * @param ResultArray  $field
     * @param string       $sep  (optional) 
     *
     *
     * @return string[]|string 
     */
	public static function getValuesFromField ( 
		SMWResultArray $field, 
		?string $sep = null
	) {

          $field->reset();
		$values = [];

		while ( $dataValue = $field->getNextDataValue() ) {
			$values[] = $dataValue->getWikiValue();
		}	

		return isset($sep) ? implode($sep, $values) : $values;
	}

}