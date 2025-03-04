<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>;.

/**
 * @package     local_greetings
 * @copyright   2023 Prakeet Singh <prakeetsingh@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once ('../../config.php');

$context = context_system::instance();

$PAGE->set_context($context);

$PAGE->set_url(new moodle_url('/local/greetings/index.php'));

// $baseurl = $PAGE->url;

// print_r($baseurl);
// exit();

$PAGE->set_pagelayout('standard');

$PAGE->set_title($SITE->fullname);

// $PAGE->set_heading(get_string('pluginname', 'local_greetings', $username));

$messageform = new \local_greetings\form\message_form();

// $exportpdf = new \local_greetings\pdf\pdf_export();

echo $OUTPUT->header();

if (isloggedin()) {
    echo '<h3>Greetings, ' . fullname($USER) . '</h3>';
} else {
    echo '<h3>Greetings, user</h3>';
}

$messageform->display();

if ($data = $messageform->get_data()) {
    $message = required_param('message', PARAM_TEXT);

    if (!empty($message)) {
        $record = new stdClass;
        $record->message = $message;
        $record->timecreated = time();
        $record->userid = $USER->id;

        $DB->insert_record('local_greetings_messages', $record);
    }
}

$userfields = \core_user\fields::for_name()->with_identity($context);
$userfieldssql = $userfields->get_sql('u');

$sql = "SELECT m.id, m.message, m.timecreated, m.userid {$userfieldssql->selects}
          FROM {local_greetings_messages} m
     LEFT JOIN {user} u ON u.id = m.userid
      ORDER BY timecreated DESC";

$messages = $DB->get_records_sql($sql);

echo $OUTPUT->box_start('card-columns');

foreach ($messages as $m) {
    echo html_writer::start_tag('div', array('class' => 'card'));
    echo html_writer::start_tag('div', array('class' => 'card-body'));
    echo html_writer::tag('p', $m->message, array('class' => 'card-text'));
    echo html_writer::tag('p', get_string('postedby', 'local_greetings', $m->firstname), array('class' => 'card-text'));
    echo html_writer::start_tag('p', array('class' => 'card-text'));
    echo html_writer::tag('small', userdate($m->timecreated), array('class' => 'text-muted'));
    echo html_writer::end_tag('p');
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');
}

echo $OUTPUT->box_end();



// if ($data = $messageform->get_data()) {
//     $message = required_param('message', PARAM_TEXT);
//     echo $OUTPUT->heading($message, 4);
// }

// // $messages = $DB->get_records('local_greetings_messages');

// // foreach ($messages as $m){
// //     echo '<p>' . $m->message . ', ' . $m->timecreated . '</p>';
// // }

echo $OUTPUT->footer();

