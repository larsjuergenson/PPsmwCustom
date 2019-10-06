<?php

use PP\SMW\ParserFunctions\SortKey;

/**
 * @group PPsmwCustom
 * @covers PP\SMW\ParserFunctions\SortKey
 */
class SortKeyTest extends MediaWikiTestCase {

    public function testTranslateAccentedChars() {
        self::assertEquals( 
            'Baeae', 
            SortKey::for( 'BäéÄÉ' ) 
        );
    }
  
    public function testRemovePunctuation() {
        self::assertEquals( 
            'Baeae', 
            SortKey::for( '?B,.a.e/a(e)' ) 
        );
    }

    public function testSqueezeMultipleSpaces() {
        // if in the input
        self::assertEquals( 
            'B a e a e',
            SortKey::for( 'B  a   e     a      e' )
        );
        // if created by removing punctuation
        self::assertEquals( 
            'B a eae',
            SortKey::for( 'B . a : ? eae' )
        );
    }

    public function testDonwcaseUpcase() {
        self::assertEquals( 
            'Abcde', 
            SortKey::for( 'aBcDE' ) 
        );
    }

    public function testMoveArticles() {
        self::assertEquals( 
            'Grosse leere, Die', 
            SortKey::for( 'die grosse leere' )
        );
    }

    public function testComplex() {
        self::assertEquals( 
            'Ao, Der',
            SortKey::for( 'Der . äÖ' )
        );   
    }

    public function testIdentity() {
        self::assertEquals( 
            'Aum',
            SortKey::for( 'Aum' )
        );   
    }

    public function testEmpty() {
        self::assertEquals( 
            '',
            SortKey::for( '' )
    );   
  }
}