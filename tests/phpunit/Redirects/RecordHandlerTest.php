<?php

use PP\SMW\Redirects\RecordHandler;

/**
 * @group Database
 * @covers PP\SMW\Redirects\RecordHandler
 */
class RecordHandlerTest extends MediaWikiTestCase {
    


    private function getPageMock(int $id) {
        $page = $this->createMock(WikiPage::class);

        $page->method('getId')
             ->willReturn($id);
    
        return $page;
  }
    protected function setUp() {
        parent::setUp();
        $this->handler = RecordHandler::getInstance();
        $this->page24 = $this->getPageMock( 24 );
        $this->page25 = $this->getPageMock( 25 );
        $this->page26 = $this->getPageMock( 26 );
    }

    protected function tearDown() {
        unset( $this->handler );
        unset( $this->page24 );
        unset( $this->page25 );
        unset( $this->page26 );
        parent::tearDown();
    }

    public function testRecordNewPagesAndDelete() {

        $this->handler->record( $this->page24, 'foo bar', 'declare this!');
        $this->handler->record( $this->page25, 'foo bar1', 'declare this!1');

        self::assertSelect( 
            'pp_smw_redirects', 
            array( 'rid', 'displayed_in', 'object_declaration' ), 
            array(),
            array( 
                array( 24, 'foo bar', 'declare this!' ),
                array( 25, 'foo bar1', 'declare this!1' ),
            ) 
        );
        $this->handler->delete( $this->page24 );
         self::assertSelect( 
            'pp_smw_redirects', 
            array( 'rid', 'displayed_in', 'object_declaration' ), 
            array(),
            array( 
                array( 25, 'foo bar1', 'declare this!1' ),
            ) 
        );

        $this->handler->delete( $this->page25 );

        self::assertSelect( 
            'pp_smw_redirects', 
            array( 'rid', 'displayed_in', 'object_declaration' ), 
            array(),
            array() 
        );

    }

    public function testRecordExistingPage() {
        $this->handler->record( $this->page24, 'foo bar', 'declare this!');
        $this->handler->record( $this->page24, 'foo bar1', 'declare this!1');

        self::assertSelect( 
            'pp_smw_redirects', 
            array( 'rid', 'displayed_in', 'object_declaration' ), 
            array(),
            array( 
                array( 24, 'foo bar1', 'declare this!1' ),
            ) 
        );

        $this->handler->delete( $this->page24 );
        // Ensure that deleting a non-recorded page does not give an error:
        $this->handler->delete( $this->page26 );

    }

    public function testIsRecorded() {
        $this->handler->record( $this->page24, 'foo bar', 'declare this!');

        self::assertTrue(RecordHandler::isRecorded( $this->page24 ));
        self::assertFalse(RecordHandler::isRecorded( $this->page26 ));

    }



 
}