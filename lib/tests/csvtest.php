<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Tests csv import and export functions
 *
 * @package    core
 * @category   phpunit
 * @copyright  2012 Adrian Greeve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/csvlib.class.php');

class csv_testcase extends advanced_testcase {

    var $testdata = array();
    var $teststring = '';
    var $teststring2 = '';
    var $teststring3 = '';

    protected function setUp(){

        $this->resetAfterTest(true);

        $csvdata = array();
        $csvdata[0][] = 'fullname';
        $csvdata[0][] = 'description of things';
        $csvdata[0][] = 'beer';
        $csvdata[1][] = 'William H T Macey';
        $csvdata[1][] = '<p>A field that contains "double quotes"</p>';
        $csvdata[1][] = 'Asahi';
        $csvdata[2][] = 'Phillip Jenkins';
        $csvdata[2][] = '<p>This field has </p>
<p>Multiple lines</p>
<p>and also contains "double quotes"</p>';
        $csvdata[2][] = 'Yebisu';
        $this->testdata = $csvdata;

        // Please note that each line needs a carriage return.
        $this->teststring = 'fullname,"description of things",beer
"William H T Macey","<p>A field that contains ""double quotes""</p>",Asahi
"Phillip Jenkins","<p>This field has </p>
<p>Multiple lines</p>
<p>and also contains ""double quotes""</p>",Yebisu
';

        $this->teststring2 = 'fullname,"description of things",beer
"Fred Flint","<p>Find the stone inside the box</p>",Asahi,"A fourth column"
"Sarah Smith","<p>How are the people next door?</p>,Yebisu,"Forget the next"
';
    }

    public function test_csv_functions() {
        // Testing that the content is imported correctly.
        $iid = csv_import_reader::get_new_iid('lib');
        $csvimport = new csv_import_reader($iid, 'lib');
        $contentcount = $csvimport->load_csv_content($this->teststring, 'utf-8', 'comma');
        $csvimport->init();
        $dataset = array();
        $dataset[] = $csvimport->get_columns();
        while ($record = $csvimport->next()) {
            $dataset[] = $record;
        }
        $csvimport->cleanup();
        $csvimport->close();
        $this->assertEquals($dataset, $this->testdata);

        // Testing for the wrong count of columns.
        $errortext = get_string('csvweirdcolumns', 'error');

        $iid = csv_import_reader::get_new_iid('lib');
        $csvimport = new csv_import_reader($iid, 'lib');
        $contentcount = $csvimport->load_csv_content($this->teststring2, 'utf-8', 'comma');
        $importerror = $csvimport->get_error();
        $csvimport->cleanup();
        $csvimport->close();
        $this->assertEquals($importerror, $errortext);

        // Testing for empty content
        $errortext = get_string('csvemptyfile', 'error');

        $iid = csv_import_reader::get_new_iid('lib');
        $csvimport = new csv_import_reader($iid, 'lib');
        $contentcount = $csvimport->load_csv_content($this->teststring3, 'utf-8', 'comma');
        $importerror = $csvimport->get_error();
        $csvimport->cleanup();
        $csvimport->close();
        $this->assertEquals($importerror, $errortext);
    }
}