<?php

use PP\SMW\ParserFunctions\IsInCategory;

/**
 * @group PPsmwCustom
 * @covers PP\SMW\ParserFunctions\IsInCategory
 */
class IsInCategoryTest extends MediaWikiTestCase {
    
    protected function setUp() {
        parent::setUp();

        // We need category PAGES, not just categories
        $this->category1 = $this->insertPage('Category:category1', '');
        $this->category2 = $this->insertPage('Category:category2', '');

        // And now some pages in those categories
        $this->page1 = $this->insertPage('page1', '[[Category:category1]]');
    }

    protected function tearDown() {
        unset( $this->category1 );
        unset( $this->category2 );
        unset( $this->page1 );
        parent::tearDown();
    }

    public function testIsInCategory() {
        self::assertTrue(  IsInCategory::isInCategory( 'category1', 'page1' ));
    }

    public function testIsNotInCategory() {
        self::assertFalse( IsInCategory::isInCategory( 'category2', 'page1' ));
    }

    public function testCategoryDoesNotExist() {
        self::assertFalse( IsInCategory::isInCategory( 'category3', 'page1' ));
    }

    public function testPageDoesNotExist() {
        self::assertFalse( IsInCategory::isInCategory( 'category1', 'page2' ));
    }

    public function testBothDoNotExist() {
        self::assertFalse( IsInCategory::isInCategory( 'category3', 'page2' ));
    }

    public function testCategoryEmpty() {
        self::assertFalse( IsInCategory::isInCategory( '', 'page1' ));
    }
    
    public function testPageEmpty() {
        self::assertFalse( IsInCategory::isInCategory( 'category2', '' ));
    }
    
    public function testBothEmpty() {
        self::assertFalse( IsInCategory::isInCategory( '', '' ));
    }
  
}