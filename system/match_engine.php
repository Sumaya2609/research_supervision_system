<?php
include "../db.php";

/* ================= COSINE ================= */

function cosineSimilarity($sSkills, $tSkills){

    $s = array_map('strtolower', array_map('trim', explode(',', $sSkills)));
    $t = array_map('strtolower', array_map('trim', explode(',', $tSkills)));

    $all = array_unique(array_merge($s, $t));

    $v1 = [];
    $v2 = [];

    foreach($all as $w){
        $v1[] = in_array($w,$s)?1:0;
        $v2[] = in_array($w,$t)?1:0;
    }

    $dot=0;$a=0;$b=0;

    for($i=0;$i<count($v1);$i++){
        $dot += $v1[$i]*$v2[$i];
        $a += $v1[$i];
        $b += $v2[$i];
    }

    if($a==0 || $b==0) return 0;

    return $dot/(sqrt($a)*sqrt($b));
}

/* ================= GLOBAL MATCHING ================= */

function runMatching($conn){

    $apps = $conn->query("
        SELECT 
        applications.application_id,
        applications.student_id,
        applications.topic_id,
        students.skills,
        topics.skills_required,
        users.id as student_user

        FROM applications
        JOIN students ON applications.student_id = students.student_id
        JOIN topics ON applications.topic_id = topics.topic_id
        JOIN users ON students.user_id = users.id

        WHERE applications.status='pending'
    ");

    $list = [];
    while($r = $apps->fetch_assoc()){
        $r['score'] = cosineSimilarity($r['skills'],$r['skills_required']);
        $list[] = $r;
    }

    usort($list,function($a,$b){
        return $b['score'] <=> $a['score'];
    });

    $usedStudents = [];

    foreach($list as $row){

        if(in_array($row['student_id'],$usedStudents)) continue;

        $app_id = $row['application_id'];

        /* APPROVE */
        $conn->query("
            UPDATE applications
            SET status='approved'
            WHERE application_id='$app_id'
        ");

        $usedStudents[] = $row['student_id'];

        /* NOTIFY STUDENT */
        $conn->query("
            INSERT INTO notifications(user_id,message)
            VALUES(
            '{$row['student_user']}',
            'You have been assigned a supervisor via AI matching'
            )
        ");
    }
}
?>