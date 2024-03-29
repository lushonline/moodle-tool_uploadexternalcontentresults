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
 * This file contains the procesing for the add/update of a single external content course result.
 *
 * @package   tool_uploadexternalcontentresults
 * @copyright 2019-2023 LushOnline
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_uploadexternalcontentresults;

/**
 * Main processing class for adding and updating single external content course result.
 *
 * @package   tool_uploadexternalcontentresults
 * @copyright 2019-2023 LushOnline
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class importer {

    /**
     * @var array $error   Last error message.
     */
    public $error = array();

    /**
     * @var array $records   The records to process.
     */
    public $records = array();

    /**
     * @var int $importid   The import id.
     */
    public $importid = 0;

    /**
     * @var object $importer   The importer object.
     */
    public $importer = null;

    /**
     * @var array $foundheaders   The headers found in the import file.
     */
    public $foundheaders = array();

    /**
     * @var object $generator   The generator used for creating the courses and activities.
     */
    public $generator = null;

    /**
     * @var array $errors   The array of all errors identified.
     */
    public $errors = array();

    /**
     * @var int $error   The current line number we are processing.
     */
    public $linenb = 0;

    /**
     * @var bool $processstarted   Indicates if we have started processing.
     */
    public $processstarted = false;

    /**
     * Return a Failure
     *
     * @param string $msg
     */
    public function fail($msg) {
        array_push($this->error, $msg);
    }

    /**
     * Get the importid
     *
     * @return string the import id
     */
    public function get_importid() {
        return $this->importid;
    }

    /**
     * Return the list of required headers for the import
     *
     * @return array contains the column headers
     */
    public static function list_required_headers() {
        return array(
        'COURSE_IDNUMBER',
        'USER_USERNAME',
        'USER_COMPLETED',
        'USER_SCORE',
        );
    }

    /**
     * Retunr the list of headers found in the CSV
     *
     * @return array contains the column headers
     */
    public function list_found_headers() {
        return $this->foundheaders;
    }

    /**
     * Get the mapping array of file column position to our object values
     *
     * @param object $data
     * @return array the object key to column
     */
    private function read_mapping_data($data) {
        if ($data) {
            return array(
            'course_idnumber' => $data->header0,
            'user_username' => $data->header1,
            'user_completed' => $data->header2,
            'user_score' => $data->header3,
            );
        } else {
            return array(
            'course_idnumber' => 0,
            'user_username' => 1,
            'user_completed' => 2,
            'user_score' => 3,
            );
        }
    }

    /**
     * Get the row of data from the CSV
     *
     * @param int $row
     * @param int $index
     * @return object
     */
    private function get_row_data($row, $index) {
        if ($index < 0) {
            return '';
        }
        return isset($row[$index]) ? $row[$index] : '';
    }

    /**
     *
     * Validate as a minimum the CSV contains the same number of columns as we require
     *
     * @return bool
     */
    private function validateheaders() {

        $foundcount = count($this->list_found_headers());
        $requiredcount = count($this->list_required_headers());

        if ($foundcount < $requiredcount) {
            return false;
        }
        return true;
    }

    /**
     *
     * Start a new CSV importer, and return true if successful
     *
     * @param string $text
     * @param string $encoding
     * @param string $delimiter
     * @param string $type
     * @return bool
     */
    private function startcsvimporter(
                                    $text = null,
                                    $encoding = null,
                                    $delimiter = 'comma',
                                    $type = 'csvimport' ) {
        if ($text === null) {
            return false;
        }

        $this->importid = \csv_import_reader::get_new_iid($type);
        $this->importer = new \csv_import_reader($this->importid, $type);

        if (!$this->importer->load_csv_content($text, $encoding, $delimiter)) {
            $this->importer->cleanup();
            return false;
        }

        return true;
    }


    /**
     * Constructor
     *
     * @param string $text
     * @param string $encoding
     * @param string $delimiter
     * @param integer $importid
     * @param object $mappingdata
     */
    public function __construct($text = null, $encoding = null, $delimiter = 'comma',
                                $importid = 0, $mappingdata = null) {
        global $CFG;
        require_once($CFG->libdir . '/csvlib.class.php');

        $type = 'singleexternalcontentcoursecompletion';
        $this->importid = $importid;

        if (!$this->importid) {
            if (!$this->startcsvimporter($text, $encoding, $delimiter, $type)) {
                $this->fail(get_string('invalidimportfile', 'tool_uploadexternalcontentresults'));
                return;
            }
        } else {
            $this->importer = new \csv_import_reader($this->importid, $type);
        }

        if (!$this->importer->init()) {
               $this->fail(get_string('invalidimportfile', 'tool_uploadexternalcontentresults'));
               $this->importer->cleanup();
               return;
        }

        $this->foundheaders = $this->importer->get_columns();
        if (!$this->validateheaders()) {
            $this->fail(get_string('invalidimportfileheaders', 'tool_uploadexternalcontentresults'));
            $this->importer->cleanup();
            return;
        }

        $record = null;
        $records = array();

        while ($row = $this->importer->next()) {
              $mapping = $this->read_mapping_data($mappingdata);

              $record = new \stdClass();
              $record->course_idnumber = $this->get_row_data($row, $mapping['course_idnumber']);
              $record->user_username = $this->get_row_data($row, $mapping['user_username']);
              $record->user_completed = clean_param(
                                                $this->get_row_data($row, $mapping['user_completed']),
                                                PARAM_BOOL);
              $record->user_score = clean_param(
                                                $this->get_row_data($row, $mapping['user_score']),
                                                PARAM_INT);
              array_push($records, $record);
        }

        $this->records = $records;

        $this->importer->close();

        if ($this->records == null) {
            $this->fail(get_string('invalidimportfilenorecords', 'tool_uploadexternalcontentresults'));
            return;
        }
    }

    /**
     * Get the error information
     *
     * @return string the last error
     */
    public function haserrors() {
        return count($this->error) > 0;
    }


    /**
     * Get the error information array
     *
     * @return array the error messages
     */
    public function geterrors() {
        return $this->error;
    }

    /**
     * Execute the process.
     *
     * @param object $tracker the output tracker to use.
     * @return void
     */
    public function execute($tracker = null) {
        if ($this->processstarted) {
              throw new \coding_exception('Process has already been started');
        }
        $this->processstarted = true;

        if (empty($tracker)) {
              $tracker = new \tool_uploadexternalcontentresults\tracker(\tool_uploadexternalcontentresults\tracker::NO_OUTPUT);
        }
        $tracker->start();

        $total = $added = $skipped = $errors = 0;

        // We will most certainly need extra time and memory to process big files.
        \core_php_time_limit::raise();
        raise_memory_limit(MEMORY_EXTRA);

        $completionaddedmsg = get_string('statuscompletionadded', 'tool_uploadexternalcontentresults');
        $completionskippedmsg = get_string('statuscompletionskipped', 'tool_uploadexternalcontentresults');
        $invalidrecordmsg = get_string('invalidimportrecord', 'tool_uploadexternalcontentresults');

        // Now actually do the work.
        foreach ($this->records as $record) {
            $this->linenb++;
            $total++;

            if (\tool_uploadexternalcontentresults\helper::validate_import_record($record)) {
                $response = \tool_uploadexternalcontentresults\helper::mark_externalcontent_as_completed($record);
                $added = $added + $response->added;
                $skipped = $skipped + $response->skipped;

                if ($response->added != 0) {
                    $status = array($completionaddedmsg, $response->message);
                } else {
                    $status = array($completionskippedmsg, $response->message);
                }

                $tracker->output($this->linenb, true, $status, $response);
            } else {
                $errors++;
                $status = array($invalidrecordmsg);
                $tracker->output($this->linenb, false, $status, null);
            }
        }

        $tracker->finish();
        $tracker->results($total, $added, $skipped, $errors);
        return $tracker->get_buffer();
    }
}
