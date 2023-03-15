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
 * Strings for component 'tool_uploadexternalcontentresults', language 'en'
 *
 * @package    tool_uploadexternalcontentresults
 * @copyright  2019-2023 LushOnline
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Upload external content completions';
$string['importfile'] = 'CSV file';
$string['import'] = 'Import';
$string['completionsprocessed'] = 'Completions processed successfully';

$string['cachedef_helper'] = 'Upload external content caching';

$string['confirm'] = 'Confirm';
$string['confirmcolumnmappings'] = 'Confirm the columns mappings';
$string['csvdelimiter'] = 'CSV delimiter';
$string['csvdelimiter_help'] = 'CSV delimiter of the CSV file.';
$string['encoding'] = 'Encoding';
$string['encoding_help'] = 'Encoding of the CSV file.';
$string['columnsheader'] = 'Columns';

// Tracker.
$string['csvline'] = 'Line';
$string['id'] = 'ID';
$string['result'] = 'Result';
$string['uploadexternalcontentresultsresult'] = 'Upload results';
$string['completionstotal'] = 'Completions total: {$a}';
$string['completionsadded'] = 'Completions added/updated: {$a}';
$string['completionsskipped'] = 'Completions skipped: {$a}';
$string['completionserrors'] = 'Completions errors: {$a}';

// CLI.
$string['invalidcsvfile'] = 'File format is invalid.';
$string['invalidencoding'] = 'Invalid encoding specified';

// Helper.
$string['coursedoesnotexist'] = 'Course with idnumber {$a} does not exist.';
$string['externalcontentdoesnotexist'] = 'External content with idnumber {$a} does not exist.';
$string['userdoesnotexist'] = 'User with username {$a} does not exist.';

// Importer.
$string['invalidfileexception'] = 'File format is invalid. {$a}';
$string['invalidimportfile'] = 'File format is invalid.';
$string['invalidimportfileheaders'] = 'File headers are invalid. Not enough columns, please verify the delimiter setting.';
$string['invalidimportfilenorecords'] = 'No records in import file.';
$string['invalidimportrecord'] = 'Invalid Import Record.';
$string['statuscompletionadded'] = 'External content completion data added/updated.';
$string['statuscompletionskipped'] = 'External content completion skipped.';
