<?php 

namespace PP\SMW\Redirects;

use WikiPage;
use Title;
use WikitextContent;
use User;
use Wikimedia\Rdbms\IDatabase;
use PP\SMW\Helpers\DatabaseHelper;



class DisplayHandler {

	const BEGIN_DISPLAY_PAGE_WITH = 
<<<'EOD'
__NOINDEX__
Diese Seite wird automatisch generiert. Sie sollte niemals von Hand bearbeitet werden. 
Jegliche Änderungen an dieser Seite werden automatisch überschrieben.

EOD;

	const END_DISPLAY_PAGE_WITH = '';

	public function __construct( IDatabase $dbr ) {
 		$this->dbr = $dbr;
	}

	public function updateDisplay( string $displayPageName ) {

		$displayPage = WikiPage::factory( Title::newFromText($displayPageName) );

		$content = $this->getContentForDisplay( $displayPageName );

		$displayPage->doEditContent(
			new WikitextContent($content), 
			'Updated because a redirect was altered.',
			0, 
			false, 
			User::newSystemUser('PPsmwRedirectRecorder')
		);
	}

	public function getContentForDisplay($displayPageName) : string {
		// get all rows belonging to this display
		$res = $this->dbr->select(
			'pp_smw_redirects',
			'object_declaration',
			array(
				'displayed_in' => $displayPageName,
			)
		);

		// concatenate the object declarations, one per line.
		$content = self::BEGIN_DISPLAY_PAGE_WITH;
		foreach ($res as $row) {
			$content .= $row->object_declaration;
			$content .= "\n";
		}
		$content .= self::END_DISPLAY_PAGE_WITH;
		return $content;
	}

	public static function update(string $displayPageName) {

		$writer = new DisplayHandler(DatabaseHelper::getConnection('read-only'));
		$writer->updateDisplay( $displayPageName );
	}


}