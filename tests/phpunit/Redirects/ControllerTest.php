<?php

namespace PP\SMW\Tests;

use MediaWikiTestCase;
use WikiPage;
use Title;
use PP\SMW\Redirects\Controller;

/**
 * @group Database
 * @covers PP\SMW\Redirects\Controller
 */
class ControllerTest extends MediaWikiTestCase {
 
    protected function setUp() {
        parent::setUp();
        $this->insertPage('page1', 'nothing of importance');
        $this->redirectContent = <<<'EOT'
#REDIRECT [[page1]]
[[Kategorie:Perry Rhodan Neo]]   
EOT;

        $this->nonRedirectContent = '[[Kategorie:Perry Rhodan Neo]]';
    }

    protected function tearDown() {
        parent::tearDown();
    }

    public function testInsertRedirectPage() {

        $info = $this->insertPage('redirect1', $this->redirectContent);
        
        $page = WikiPage::factory( $info['title'] );

        Controller::process( $page );

        self::assertPageContains(
            Controller::getDisplayPageNameFor( $page ), 
            '{{#subobject:Redirect1'
        );
    }

    public function testNonRedirectDoesNotUpdateDisplayPage () {
        // Trigger creation of display page
        $this->insertPage('redirect1', $this->redirectContent);

        // Insert a non-redirect page
        $info = $this->insertPage('redirect2', $this->nonRedirectContent);

        $page = WikiPage::factory( $info['title'] );
    
        Controller::process( $page );

        self::assertPageNotContains(
            Controller::getDisplayPageNameFor( $page ), 
            '{{#subobject:Redirect2'
        );
    }

    public function testNonRedirectPageDoesNotCreateNewDisplayPage() {

        $info = $this->insertPage('no_redirect1', $this->nonRedirectContent);

        $page = WikiPage::factory( $info['title'] );

        Controller::process( $page );

        self::assertPageDisplayPageDoesNotExistFor( $page );

    }

    // For the following tests, we rely on the fact that a display page will not
    // be generated if the page should not be recorded.
    // That is, we rely on the outcome of the previous test.

    public function testRedirectPageWithoutNeoIsIgnored() {
  
       $info = $this->insertPage('no_redirect1', '#REDIRECT [[page1]]');

        $page = WikiPage::factory( $info['title'] );

        self::assertPageDisplayPageDoesNotExistFor( $page );
    }

    public function testRedirectPageWithKeineFehlendenKategorieEintraegeIsIgnored() {
  
       $info = $this->insertPage(
            'no_redirect1',
            $this->nonRedirectContent . '[[Kategorie:Keine Fehlende Kategorieeinträge]]'
        );

        $page = WikiPage::factory( $info['title'] );
        
        self::assertPageDisplayPageDoesNotExistFor( $page );
    }
    
    private static function assertPageContains( string $pageName, string $text ) {

        $pageText = self::getPageText( $pageName );
   
        self::assertContains( $text, $pageText );

    }

    private static function assertPageNotContains( string $pageName, string $text ) {

        $pageText = self::getPageText( $pageName );
   
        self::assertNotContains( $text, $pageText );

    }

    private static function assertPageDisplayPageDoesNotExistFor( WikiPage $page ) {
        $title = Title::newFromText( Controller::getDisplayPageNameFor( $page ) );
        self::assertFalse( $title->exists() );
    }
    private static function getPageText( string $pageName ) : string {
        return WikiPage::factory( Title::newFromText( $pageName ) )
            ->getContent()
            ->getNativeData();
    }
}