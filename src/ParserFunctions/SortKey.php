<?php

namespace PP\SMW\ParserFunctions;

/**
 * Implements the {{#SortKey}} parser function.
 *
 * Currently, this just mirrors the "{{SortKey}}"-Template in Perrypedia.
 *
 * The only public function is the static toSortKey(), which takes a string
 * (e.g., a page name) and returns the corresponding sort key.
 *
 * This is called, in turn, by hook(), which implements SMWs parser function
 * hook.
 */
class SortKey {

	/**
	 * Generates a sortkey for a string, e.g., a page name.
	 *
	 * @param string $input 
	 *
	 * @return string The sortkey version of $string.
	 */
	public static function for( string $input ) : string {

		// Replace accented (etc.) characters with their "basic" version
		$key = self::translateCharacters( $input );

		// Remove punctuation:
		$key = self::removePunctuation( $key );

		// The removal of punctuation may have resulted in several spaces in
		// a row. We fix that here: 
		$key = self::fixSpaces( $key );

		// Make all lower case 
		$key = strtolower($key);

		// Move articles (der,die, das ...) to the end, captalizing them:
		$key = self::moveArticlesToEnd($key);

		// Make the first character uppercase again:
		$key = ucfirst($key);

		return $key;
	}
	/**
	 * An array specifying which characters should be replaced by what.
	 * Note that the replacement CAN contain more than one character.
	 *
	 * @var array TRANSLATIONS 
	 */

	const TRANSLATIONS = array(
		'á' => 'a',
		'Á' => 'A',
		'à' => 'a',
		'â' => 'a',
		'Â' => 'A',
		'ä' => 'a',
		'Ä' => 'A',
		'ã' => 'a',
		'č' => 'c',
		'ç' => 'c',
		'Ç' => 'C',
		'é' => 'e',
		'É' => 'E',
		'è' => 'e',
		'È' => 'E',
		'ê' => 'e',
		'ë' => 'e',
		'ě' => 'e',
		'í' => 'i',
		'Î' => 'I',
		'ï' => 'i',
		'Ñ' => 'N',
		'ó' => 'o',
		'Ó' => 'O',
		'ô' => 'o',
		'ö' => 'o',
		'Ö' => 'O',
		'õ' => 'o',
		'ř' => 'r',
		'š' => 's',
		'ß' => 'ss',
		'ť' => 't',
		'ú' => 'u',
	    'Ú' => 'U',
	    'û' => 'u',
	    'Û' => 'U',
	    'ü' => 'u',
	    'Ü' => 'U',
	    'ý' => 'Y',
	); 
	/**
	 * An array specifying which characters count as punctuation and should be
	 * removed from the input.
	 *
	 * @var array PUNCTUATION 
	 */
	private const PUNCTUATION = array(  
		'-', 
		'–', 
		'!', 
		'(', 
		')', 
		'*', 
		',', 
		'.', 
		'/', 
		':', 
		'?', 
		'’', 
		'“', 
		'„',
	    '' , 
	    '«', 
	    '»', 
	    '·', 
	    '&#39;', 
	    '&#34;', 
	    '&#38;',
	);

	/**
	 * An array specifying which words are articles, and hence should be moved
	 * to the end if they occur at the beginning of the input. The articles
	 * must be given in lower case.
	 *
	 * @var array ARTICLES
	 */
	private const ARTICLES = array('der', 'das', 'die', 'ein', 'eine');

	/**
	 * Translates characters according to the TRANSLATIONS table.
	 *
	 * @param string $key 
	 *
	 * @return string The translated $key.
	 */

	static private function translateCharacters( string $key ) : string {
		return strtr($key, self::TRANSLATIONS);
	}

	/**
	 * Removes punctuation according to the PUNCTUATION list.
	 *
	 * @param string $key 
	 *
	 * @return string $key without punctuation.
	 */

	static private function removePunctuation( string $key ) : string {
		
		return str_replace(self::PUNCTUATION, '', $key);
	}

	/**
	 * Turns multiple spaces into a single space. 
	 *
	 * For performance reasons, we only treat up to six spaces, which is much
	 * more than enough for our purposes.
	 *
	 * @param string $key 
	 *
	 * @return string $key without runs of spaces.
	 */
	static private function fixSpaces( string $key ) : string {
		// The elegant and theoretically always-working way would be to 
		// simply do:
		// return preg_replace('/\s+/', ' ', $key);
		// But we otherwise can avoid using regular expressions, so we fake it
		// here:
		$replace = array(
			'      ' => ' ',
			'     ' => ' ',
			'    ' => ' ',
			'   '  => ' ',
			'  '   => ' ',
		);
		// strtr starts with the longest key, so the following line will
		// correctly replace up to six spaces in a row (which would be
		// produced by five punctuation characters in a row, separated by 
		// spaces, which is something that does not happen. 
		return strtr( $key, $replace );

	}

	/**
	 * Moves articles to the end of the key
	 *
	 * If the downcased key begins with one of the words in the ARTICLES 
	 * list, this word is removed, and appended (capitalized and with a comma) 
	 * to the end of the key.
	 *
	 * Example: "die grosse leere" => "grosse leere, Die"
	 *
	 * @param string $key 
	 *
	 * @return string The transformed $key.
	 */

	static private function moveArticlesToEnd( string $key ) : string {

		foreach ( self::ARTICLES as $article ) {
			
			$begin = $article . ' ';
			
			$end = ', ' . ucfirst( $article );
				
			$key = self::moveToEnd( $key, $begin, $end );	
			
		}
		return $key;
	}

	/**
	 * Removes a specified portion from the beginning of a string an appends 
	 * a replacement at the end.
	 *
	 * Helper function for moveArticlesToEnd().
	 *
	 * If $key begins with $begin, this portion is removed and $end is appended
	 * to the end. Otherwise, $key is returned unchanged.
	 *
	 * @param string $key 
	 * @param string $begin
	 * @param string $end
	 *
	 * @return string The transformed $key.
	 */
	static private function moveToEnd( 
		string $key, 
		string $begin, 
		string $end
	) : string {
		// if $key starts with $begin ...
		if ( strncmp( $key, $begin, strlen( $begin ) ) === 0 ) {
			// .. remove $begin from $key, and append $end to it:
			$key = substr( $key, strlen( $begin ) ) . $end;
		}
		return $key;
	}

	public static function hook( &$parser, $string = '') {
		return SortKey::for( $string );
	}
}