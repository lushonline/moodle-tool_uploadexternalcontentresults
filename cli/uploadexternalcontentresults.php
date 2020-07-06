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
 * CLI Bulk upload external activity completions.
 *
 * @package    tool_uploadexternalcontentresults
 * @copyright  2019-2020 LushOnline
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/phpunit/classes/util.php');
require_once($CFG->libdir . '/clilib.php');


// Now get cli options.
list($options, $unrecognized) = cli_get_params(array(
    'help' => false,
    'source' => '',
    'delimiter' => 'comma',
    'encoding' => 'UTF-8'
),
array(
    'h' => 'help',
    's' => 'source',
    'd' => 'delimiter',
    'e' => 'encoding'
));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

$help =
"Upload External content activity completions.

Options:
-h, --help                 Print out this help
-s, --source               CSV file
-d, --delimiter            CSV delimiter: colon, semicolon, tab, cfg, comma
-e, --encoding             CSV file encoding: utf8, ... etc

Example:
\$sudo -u www-data /usr/bin/php admin/tool/uploadexternalcontentresults/cli/uploadexternalcontentresults.php
--source=./completions.csv
";

if ($options['help']) {
    echo $help;
    die();
}
echo "Upload running ...\n";

// File.
if (!empty($options['source'])) {
    $options['source'] = realpath($options['source']);
}

if (!file_exists($options['source'])) {
    echo get_string('invalidcsvfile', 'tool_uploadexternalcontentresults')."\n";
    echo $help;
    die();
}

// Encoding.
$encodings = core_text::get_encodings();
if (!isset($encodings[$options['encoding']])) {
    echo get_string('invalidencoding', 'tool_uploadexternalcontentresults')."\n";
    echo $help;
    die();
}

// Emulate normal session.
cron_setup_user();

// Let's get started!
$content = file_get_contents($options['source']);
$importer = new tool_uploadexternalcontentresults_importer($content, $options['encoding'], $options['delimiter']);

$importid = $importer->get_importid();
unset($content);

if ($importer->haserrors()) {
    print_error('invalidimportfile', 'tool_uploadexternalcontentresults', '', implode(PHP_EOL, $importer->get_error()));
}

$importer = new tool_uploadexternalcontentresults_importer(null, null, null, $importid, null);
$importer->execute(new tool_uploadexternalcontentresults_tracker(tool_uploadexternalcontentresults_tracker::OUTPUT_PLAIN, true));
