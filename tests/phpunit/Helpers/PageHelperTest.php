<?php

use PP\SMW\Helpers\PageHelper;

/**
 * @group PPsmwCustom
 * @covers PP\SMW\Helpers\PageHelper
 */
class PageHelperTest extends MediaWikiTestCase {
    
    protected function setUp() {
        parent::setUp();

        $info = $this->insertPage(
            'The title', 
           '[[Kategorie:Category1]]
            [[Kategorie:Category 2]]
            {{#set:Foo=Bar|Foo=Baz|Test=Good}}
            {{DEFAULTSORT:blabla}}
            '
        );

        $page = WikiPage::Factory( $info['title'] );

        $this->pageHelper = PageHelper::for( $page );
    }

    protected function tearDown() {
        unset( $this->pageHelper );
        parent::tearDown();
    }

    public function testGetPageName() {
        self::assertEquals( 'The title', $this->pageHelper->getName() );
    }

    public function testGetDBKey() {
        self::assertEquals( 'The_title', $this->pageHelper->getDBkey() );
    }

    public function testGetCategories() {
        self::assertEquals( array('Kategorie:Category1','Kategorie:Category_2'), $this->pageHelper->getCategories() );
    }

    public function testGetPropertyValues() {
        $values = array(
            'Foo' => array('Bar', 'Baz'),
            'Test' => array('Good'),
        );
        self::assertEquals( $values , $this->pageHelper->getPropertyValues() );
    }

     public function testGetSortKey() {
        self::assertEquals( 'blabla', $this->pageHelper->getSortKey() );
    }
  
}