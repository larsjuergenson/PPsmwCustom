<?php

use PP\SMW\Redirects\RecordHandler;

/**
 * @group Database
 * @covers PP\SMW\Redirects\RecordHandler
 */
class RecordHandlerTest extends MediaWikiTestCase {
    
    protected function setUp() {
        parent::setUp();
        $this->handler = RecordHandler::getInstance();
    }

    protected function tearDown() {
        unset( $this->handler );
        parent::tearDown();
    }

    public function testRecordNewPagesAndDelete() {
        $this->handler->record( 24, 'foo bar', 'declare this!');
        $this->handler->record( 26, 'foo bar1', 'declare this!1');

        self::assertSelect( 
            'pp_smw_redirects', 
            array( 'rid', 'displayed_in', 'object_declaration' ), 
            array(),
            array( 
                array( 24, 'foo bar', 'declare this!' ),
                array( 26, 'foo bar1', 'declare this!1' ),
            ) 
        );
        $this->handler->delete( 24 );
         self::assertSelect( 
            'pp_smw_redirects', 
            array( 'rid', 'displayed_in', 'object_declaration' ), 
            array(),
            array( 
                array( 26, 'foo bar1', 'declare this!1' ),
            ) 
        );

        $this->handler->delete( 26 );

        $this->handler->delete( 24 );
             self::assertSelect( 
                'pp_smw_redirects', 
                array( 'rid', 'displayed_in', 'object_declaration' ), 
                array(),
                array() 
            );

    }

    public function testRecordExistingPage() {
        $this->handler->record( 24, 'foo bar', 'declare this!');
        $this->handler->record( 24, 'foo bar1', 'declare this!1');

        self::assertSelect( 
            'pp_smw_redirects', 
            array( 'rid', 'displayed_in', 'object_declaration' ), 
            array(),
            array( 
                array( 24, 'foo bar1', 'declare this!1' ),
            ) 
        );

        $this->handler->delete( 24 );
        // Ensure that deleting a non-recorded page does not give an error:
        $this->handler->delete( 26 );

    }

    public function testIsRecorded() {
        $this->handler->record( 24, 'foo bar', 'declare this!');

        self::assertTrue(RecordHandler::isRecorded( 24 ));
        self::assertFalse(RecordHandler::isRecorded( 28 ));

    }



 
}