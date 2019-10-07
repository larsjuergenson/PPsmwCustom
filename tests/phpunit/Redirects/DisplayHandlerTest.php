<?php

use PP\SMW\Redirects\DisplayHandler;
use PP\SMW\Redirects\RecordHandler;

/**
 * @group Database
 * @covers PP\SMW\Redirects\DisplayHandler
 * @uses PP\SMW\Redirects\RecordHandler
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
        $this->recordHandler = RecordHandler::getInstance();
        $this->page1 = $this->getPageMock( 1 );
        $this->page2 = $this->getPageMock( 2 );
        $this->page3 = $this->getPageMock( 3 );

        $this->recordHandler->record($this->page1, 'displayA', 'declaration1');
        $this->recordHandler->record($this->page2, 'displayB', 'declaration2');
        $this->recordHandler->record($this->page3, 'displayA', 'declaration3');

        // The actual page output
        $this->should_displayA = DisplayHandler::BEGIN_DISPLAY_PAGE_WITH;
        $this->should_displayA .= "declaration1\n";
        $this->should_displayA .= "declaration3\n";
        $this->should_displayA .= DisplayHandler::END_DISPLAY_PAGE_WITH;

        $this->should_displayB = DisplayHandler::BEGIN_DISPLAY_PAGE_WITH;
        $this->should_displayB .= "declaration2\n";
        $this->should_displayB .= DisplayHandler::END_DISPLAY_PAGE_WITH;

        $this->displayHandler = new DisplayHandler( wfGetDB( DB_REPLICA ) );
    }

    protected function tearDown() {
        $this->recordHandler->delete($this->page1);
        $this->recordHandler->delete($this->page2);
        $this->recordHandler->delete($this->page3);
        unset( $this->recordHandler );
        unset( $this->displayHandler );
        unset( $this->page1 );
        unset( $this->page2 );
        unset( $this->page3 );
        parent::tearDown();
    }

    public function testGetContentForDisplay() {

        self::assertEquals(
            $this->should_displayA, 
            $this->displayHandler->getContentForDisplay('displayA')
        );

        self::assertEquals(
            $this->should_displayB, 
            $this->displayHandler->getContentForDisplay('displayB')
        );
    }

    public function testDisplayIsWritten() {
        DisplayHandler::update('displayA');
        $displayATitle = Title::newFromText('displayA');
        
        // Has the page been created?
        self::assertTrue($displayATitle->exists());

        $text = WikiPage::factory( $displayATitle )
            ->getContent()
            ->getNativeData();
   

        self::assertContains($this->should_displayA, $text);

    }
}