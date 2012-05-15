<?php
$cookies['local'] = Array(
                  Array(
                         'name' => 'MOODLEID1_',
                         'title' => 'Moodle username',
                         'description' => "Remembers your username for the login form, so you don't have to type it in every time.",
                         'more_info' => 'Expires two months after creation/update.',
                         'consent' => null,
                         'permanent' => false
                       ),
                  Array(
                         'name' => 'MoodleSession',
                         'title' => 'Moodle session',
                         'description' => 'Required to keep you logged in whilst using the site.',
                         'more_info' => 'Expires when you close your browser.',
                         'consent' => null,
                         'permanent' => false
                       )
                );

$cookies['other'] = Array(
                  Array(
                         'name' => 'PREF',
                         'title' => 'YouTube preferences',
                         'description' => 'Stores your YouTube preferences.',
                         'more_info' => 'Expires 10 years after creation/update.',
                         'consent' => null,
                         'permanent' => false
                       ),
                  Array(
                         'name' => 'VISITOR_INFO1_LIVE',
                         'title' => 'YouTube analytics',
                         'description' => 'Supports video performance analysis.',
                         'more_info' => 'Expires 7 months after creation/update.',
                         'consent' => null,
                         'permanent' => false
                       )
                );
?>
