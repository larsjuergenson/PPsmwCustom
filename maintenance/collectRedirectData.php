<?php

namespace PP\SMW\Maintenance;

use Category;
use WikiPage;
use Title;
use PP\SMW\Redirects\Controller as RedirectController;
use PP\SMW\Redirects\DisplayHandler;

$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ? getenv( 'MW_INSTALL_PATH' ) : __DIR__ . '/../../..';

require_once $basePath . '/maintenance/Maintenance.php';

/**
 *
 */
class collectRedirectData extends \Maintenance {

	public function __construct() {
		parent::__construct();

		$this->addDescription( "\n" .
			"Processes all redirect pages and saves their data in the pp_smwredirects \n" .
			"table if appropriate. \n" .
			"NOTE: For maximum performance page does not do any deletions. If you want \n" .
			"get rid of potentially-erroneous entries, you'll have to manually empty the " .
			"table first.\n\n"
		);

		$this->addDefaultParams();
	}

	/**
	 * @see Maintenance::addDefaultParams
	 */
	protected function addDefaultParams() {

		parent::addDefaultParams();

	}

	/**
	 * @see Maintenance::execute
	 */
	public function execute() {
		
		print "\nCollecting data from redirect pages ... \n";

		$this->collectData();
 
 		print "\n\nRebuilding display pages ...";

 		$this->rebuildDisplays();
 		
		
	}

	private function collectData() {

		$dbr = wfGetDB( DB_REPLICA );

		// This is a bit of a large join, but it yields significant performance
		// gains later (as long as we are only interested in NEO-Redirects anyways).
		$res = $dbr->select(
 			array(
 				'c1' => 'categorylinks',
 				'c2' => 'categorylinks',
 			),
			'c1.cl_from',
			array( 
				"c1.cl_from=c2.cl_from",
				"c1.cl_to='Redirect'",
				"c2.cl_to='Perry_Rhodan_Neo'",
			),
			__METHOD__
		);
		
		
		$total = $res->numRows();

		 $n = 0;
 		 $start_time = time(true);
         foreach ( $res as $row ) {

         	$page = WikiPage::factory( Title::newFromID( $row->cl_from ) );
  
         	RedirectController::process($page, false);
         	
         	$n++;
         	$percent = (int) ( ( $n / $total ) * 100 );

         	if ( $n % 100 === 0 ) {
         		$redirects_remain = $total - $n;
         		$secs_elapsed = time() - $start_time;
         		$est_secs_remain = ( $secs_elapsed / $n ) * $redirects_remain;
         		print "\nProcessed {$n} NEO redirects (" . $percent  . "%). About " . (int) ($est_secs_remain / 60) . " minutes remaining ...";	
         		$last_reported_percent = $percent;
         	}  	
         }
         " Done. \n\n Processed {$n} NEO redirects overall.";
	}

	private function rebuildDisplays() {
		$dbr = wfGetDB( DB_REPLICA );

 		$res = $dbr->select(
 			'pp_smw_redirects',
			'displayed_in',
			array(),
			__METHOD__,
			array( 'GROUP BY' => 'displayed_in')
		);

 		$handler = new DisplayHandler( $dbr );

 		$n = 0;
		foreach ( $res as $row ) {
			print ".";
			$handler->updateDisplay( $row->displayed_in );
			$n++;
		}
		print "\nRebuilt {$n} display pages.\n";
	}

}

$maintClass = collectRedirectData::class;
require_once RUN_MAINTENANCE_IF_MAIN;
