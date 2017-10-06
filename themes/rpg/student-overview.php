<?php

/*
Template Name: student-overview
*/

global $wpdb;
echo "1";
$current_user = wp_get_current_user();
$table_name = $wpdb->prefix . "student_information";

if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
    echo "3";
    $result1 = $wpdb->get_results("SELECT ID from wp_student_information");
    foreach ($result1 as $individual_user) {
        if ($individual_user == $current_user) {
            fetch_student_data($wpdb, $current_user);
        }
        else {
            $wpdb->insert($wpdb->student_information, array("Student_ID" => $current_user->ID, "First_Name" => $current_user->first_name, "Last_Name" => $current_user->last_name, "Level_No" => 4), array("%d", "%s", "%s", "%d"));
            fetch_student_data($wpdb, $current_user);
        }
    }
}
else {
    echo "2";
    create_student_tables($wpdb, $table_name);
}

function fetch_student_data($wpdb, $current_user)
{
    $subject_array = array();

    $result = $wpdb->get_results("SELECT ID from wp_student_information");

    foreach ($result as $individual_user) {
        if ($individual_user == $current_user) {
            $individual_user = $wpdb->get_results("SELECT * FROM wp_student_information WHERE Student_ID = $current_user->ID");
            echo $individual_user->First_Name . $result->Last_Name;
            echo "Level: " . $individual_user->Level_No;
        }
    }

    $result2 = $wpdb->get_results("SELECT * FROM wp_student_information_subjects");
    $x = 0;
    foreach ($result2 as $user_subjects) {
        if ($user_subjects->level_no == $result->Level_No) {
            echo $user_subjects->Subject_Code;
            $subject_array[$x] = $user_subjects->Subject_ID;
            $x++;
        }
    }

    $subject_array_imploded = implode(", ", $subject_array);

    $result3 = $wpdb->get_results("SELECT * FROM wp_student_information_quests WHERE Student_ID = $current_user->ID");
    foreach ($result3 as $user_quest) {
        echo $user_quest->Quest_Info;
    }

    $result4 = $wpdb->get_results("SELECT * FROM wp_student_information_notices WHERE Subject_ID IN $subject_array_imploded");

    foreach ($result4 as $user_notice) {
        echo $user_notice->Notice_Info;
    }
}

function create_student_tables($wpdb, $table_name)
{
    $table_name2 = $wpdb->prefix . "student_information_subjects";
    $table_name3 = $wpdb->prefix . "student_information_quests";
    $table_name4 = $wpdb->prefix . "student_information_notices";

    $charset_collate = $wpdb->get_charset_collate();

    $sql_query1 = "CREATE TABLE $table_name (
        Student_ID int NOT NULL,
		First_Name varchar(60),
		Last_Name varchar(60),
		Level_No int,
  		PRIMARY KEY (Student_ID)
		) $charset_collate;
		
	CREATE TABLE $table_name2 (
        Subject_ID int NOT NULL,
		Subject_Code varchar(10),
		Subject_Name varchar(60),
		Level_No int,
		PRIMARY KEY (Subject_ID),
		FOREIGN KEY (Level_No) REFERENCES $table_name(Level_No)
		) $charset_collate;
		
	CREATE TABLE $table_name3 (
  		Quest_ID int NOT NULL,
		Quest_Info varchar(255),
		Student_ID int,
		PRIMARY KEY (Quest_ID),
		FOREIGN KEY (Student_ID) REFERENCES $table_name(Student_ID)
		) $charset_collate;
  		
	CREATE TABLE $table_name4 (
  		Notice_ID int NOT NULL,
		Notice_Info varchar(255),
		Subject_ID int,
		PRIMARY KEY (Notice_ID),
		FOREIGN KEY (Subject_ID) REFERENCES $table_name3(Subject_ID)
		) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_query1);


    echo "this is successful";

    //insert_student_data($wpdb, $current_user);
}

function insert_student_data($wpdb, $current_user) {
    $wpdb->insert($wpdb->student_information, array("Student_ID" => $current_user->ID, "First_Name" => $current_user->first_name, "Last_Name" => $current_user->last_name, "Level_No" => 4), array("%d", "%s", "%s", "%d"));
    $wpdb->insert($wpdb->student_information_subjects, array("Subject_ID" => 1, "Subject_Code" => "CP1401", "Subject_Name" => "Introduction to Programming", "Level_No" => 1), array("%d", "%s", "%s", "%d"));
    $wpdb->insert($wpdb->student_information_quests, array("Quest_ID" => 1, "Quest_Info" => "Complete CP1401 Practical 3", "Student_ID" => $current_user->ID), array("%d", "%s", "%d"));
    $wpdb->insert($wpdb->student_information_quests, array("Notice_ID" => 1, "Notice_Info" => "Assignment is due next week.", "Subject_ID" => 1), array("%d", "%s", "%d"));
}
?>