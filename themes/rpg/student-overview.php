<?php

/*
Template Name: student-overview
*/

$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

global $wpdb;
$current_user = wp_get_current_user();
$table_name = $wpdb->prefix . "student_information";
$table_name2 = $wpdb->prefix . "student_information_subjects";
$table_name3 = $wpdb->prefix . "student_information_quests";
$table_name4 = $wpdb->prefix . "student_information_notices";

if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
    $result1 = $wpdb->get_results("SELECT student_id from wp_student_information");
    foreach ($result1 as $individual_user) {
        if ($individual_user->student_id == $current_user->ID) {
            $context = fetch_student_data($wpdb, $current_user, $context);
        }
        else {
            $wpdb->insert($table_name, array("student_id" => $current_user->id, "first_name" => $current_user->first_name, "last_name" => $current_user->last_name, "level_no" => 4), array("%d", "%s", "%s", "%d"));
            $context = fetch_student_data($wpdb, $current_user, $context);
        }
    }
}
else {
    create_student_tables($wpdb, $table_name, $table_name2, $table_name3, $table_name4);
    insert_student_data($wpdb, $current_user, $table_name, $table_name2, $table_name3, $table_name4);
    $context = fetch_student_data($wpdb, $current_user, $context);
}

Timber::render( array( 'pages/student-overview.twig' ), $context );

function fetch_student_data($wpdb, $current_user, $context)
{
    $result = $wpdb->get_results("SELECT * from wp_student_information");

    $user_level_no = null;

    foreach ($result as $individual_user) {
        if ($individual_user->student_id == $current_user->id) {
            $context['name'] = $individual_user->first_name . " " . $individual_user->last_name;
            $context['level_no'] = $individual_user->level_no;
            $user_level_no = $individual_user->level_no;
        }
    }

    $subject_array = array();

    $result2 = $wpdb->get_results("SELECT * FROM wp_student_information_subjects");
    $x = 0;
    foreach ($result2 as $user_subjects) {
        if ($user_subjects->level_no == $user_level_no) {
            $subject_array[$x] = $user_subjects->subject_code;
            $x++;
        }
    }

    $context['subject_code'] = $subject_array;

    $result3 = $wpdb->get_results("SELECT * FROM wp_student_information_quests WHERE student_id = $current_user->id");
    $quests_array = array();
    $y = 0;
    foreach ($result3 as $user_quest) {
        $quests_array[$y] = $user_quest->quest_info;
        $y++;
    }

    $context['quest_info'] = $quests_array;

    $subject_array = array_map(function($v) {return "'" . esc_sql($v) . "'";}, $subject_array);
    $subject_array = implode(',', $subject_array);

    $sql = "SELECT * FROM wp_student_information_notices WHERE subject_id IN (1)";

    $result4 = $wpdb->get_results($sql);
    $notices_array = array();
    $z = 0;
    foreach ($result4 as $user_notice) {
        $notices_array[$z] = $user_notice->notice_info;
        $z++;
    }

    $context['user_notices'] = $notices_array;

    return $context;
}

function create_student_tables($wpdb, $table_name, $table_name2, $table_name3, $table_name4) {

    $charset_collate = $wpdb->get_charset_collate();

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $sql_query1 = "CREATE TABLE $table_name (
        student_id int NOT NULL,
		first_name varchar(60),
		last_name varchar(60),
		level_no int,
  		PRIMARY KEY  (student_id)
		) $charset_collate;";

    $wpdb->query($sql_query1);
		
	$sql_query2 = "CREATE TABLE $table_name2 (
        subject_id int NOT NULL,
		subject_code varchar(10),
		subject_name varchar(60),
		level_no int,
		PRIMARY KEY  (subject_id)
		) $charset_collate;";

    $wpdb->query($sql_query2);
		
	$sql_query3 = "CREATE TABLE $table_name3 (
  		quest_id int NOT NULL,
		quest_info varchar(255),
		student_id int,
		PRIMARY KEY  (quest_id),
		FOREIGN KEY (student_id) REFERENCES $table_name(student_id)
		) $charset_collate;";

    $wpdb->query($sql_query3);
  		
	$sql_query4 = "CREATE TABLE $table_name4 (
  		notice_id int NOT NULL,
		notice_info varchar(255),
		subject_id int,
		PRIMARY KEY  (notice_id)
		) $charset_collate;";

    $wpdb->query($sql_query4);
}

function insert_student_data($wpdb, $current_user, $table_name, $table_name2, $table_name3, $table_name4) {
    //users
    $wpdb->insert($table_name, array('student_id' => $current_user->ID, 'first_name' => $current_user->first_name, 'last_name' => $current_user->last_name, 'level_no' => 4), array("%d", "%s", "%s", "%d"));

    //subjects
    $wpdb->insert($table_name2, array('subject_id' => 1, 'subject_code' => "CP1401", 'subject_name' => "Introduction to Programming", 'level_no' => 4), array("%d", "%s", "%s", "%d"));
    $wpdb->insert($table_name2, array('subject_id' => 2, 'subject_code' => "CP2404", 'subject_name' => "Introduction to Databases", 'level_no' => 4 ), array('%d', '%s', '%s', '%d'));
    $wpdb->insert($table_name2, array('subject_id' => 3, 'subject_code' => "CP3401", 'subject_name' => "Mobile Design", 'level_no' => 4 ), array('%d', '%s', '%s', '%d'));
    $wpdb->insert($table_name2, array('subject_id' => 4, 'subject_code' => "CP1403", 'subject_name' => "Web Design", 'level_no' => 3 ), array('%d', '%s', '%s', '%d'));
    $wpdb->insert($table_name2, array('subject_id' => 5, 'subject_code' => "CP3407", 'subject_name' => "Programming Advanced II", 'level_no' => 3 ), array('%d', '%s', '%s', '%d'));

    //quests
    $wpdb->insert($table_name3, array('quest_id' => 1, 'quest_info' => "Complete CP1401 Practical 3", 'student_id' => $current_user->id), array("%d", "%s", "%d"));
    $wpdb->insert($table_name3, array('quest_id' => 2, 'quest_info' => "Complete CP2404 Mini Test 4", 'student_id' => $current_user->id), array('%d', '%s', '%d'));
    $wpdb->insert($table_name3, array('quest_id' => 3, 'quest_info' => "Complete CP1401 Practical 4", 'student_id' => $current_user->id), array('%d', '%s', '%d'));
    $wpdb->insert($table_name3, array('quest_id' => 4, 'quest_info' => "Complete CP1403 Mini Test 2", 'student_id' => 5), array('%d', '%s', '%d'));
    $wpdb->insert($table_name3, array('quest_id' => 5, 'quest_info' => "Complete CP3407 Practical 1", 'student_id' => 5), array('%d', '%s', '%d'));

    //notices
    $wpdb->insert($table_name4, array('notice_id' => 1, 'notice_info' => "Assignment is due next week.", 'subject_id' => 1), array("%d", "%s", "%d"));
    $wpdb->insert($table_name4, array('notice_id' => 2, 'notice_info' => "Lecture time has been changed", 'subject_id' => 2), array('%d', '%s', '%d'));
    $wpdb->insert($table_name4, array('notice_id' => 3, 'notice_info' => "Practical has been cancelled", 'subject_id' => 3), array('%d', '%s', '%d'));
    $wpdb->insert($table_name4, array('notice_id' => 4, 'notice_info' => "Substitute tutor announced for lecture", 'subject_id' => 4), array('%d', '%s', '%d'));
    $wpdb->insert($table_name4, array('notice_id' => 5, 'notice_info' => "Assignment due date delayed", 'subject_id' => 5), array('%d', '%s', '%d'));
}
?>