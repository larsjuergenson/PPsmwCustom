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
            "table where appropriate. \n\n"
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

        print "\nDeleting old redirect data ... \n";

        $this->purgeData();

        print "\nCollecting data from redirect pages ... \n";

        $this->collectData();

        print "\n\nRebuilding display pages ...";

        $this->rebuildDisplays();


    }

    private function purgeData() {
        $dbw = wfGetDB( DB_MASTER );
        $dbw->delete('pp_smw_redirects', ['1']);
    }

    private function collectData() {

        $dbr = wfGetDB( DB_REPLICA );

        $res = $dbr->select(
            array(
                'c' => 'categorylinks',
            ),
            'c.cl_from',
            array( 
                "c.cl_to='Redirect'",
            ),
            __METHOD__
        );
        
        
        $total = $res->numRows();

        $n = 0;
        $start_time = time();
        foreach ( $res as $row ) {

            $page = WikiPage::factory( Title::newFromID( $row->cl_from ) );
  
            RedirectController::process($page, false);
            
            $n++;
            $percent = (int) ( ( $n / $total ) * 100 );

            if ( $n % 100 === 0 ) {
                $redirects_remain = $total - $n;
                $secs_elapsed = time() - $start_time;
                print "\nProcessed {$n} redirects (" . $percent  . "%) in " . round($secs_elapsed / 60, 1) . " minutes";    
                $last_reported_percent = $percent;
            }   
        }
        print " Done. \n\n Processed {$n} redirects overall.";
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
