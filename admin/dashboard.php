            <?php
                session_start();
                include "../db.php";



                /* ================= DOWNLOAD REPORT CSV ================= */

                if(isset($_GET['download']) && $_GET['download'] == 'reports'){

                    header('Content-Type: text/csv');
                    header('Content-Disposition: attachment; filename=reports.csv');

                    $output = fopen("php://output", "w");

                    fputcsv($output, [
                        'Faculty Name',
                        'Student Name',
                        'Feedback',
                        'Rating',
                        'Date'
                    ]);

                    $export = $conn->query("
                    SELECT
                    reports.*,

                    student_user.name as student_name,
                    faculty_user.name as faculty_name

                    FROM reports

                    JOIN students
                    ON reports.student_id = students.student_id

                    JOIN users student_user
                    ON students.user_id = student_user.id

                    JOIN faculty
                    ON reports.faculty_id = faculty.faculty_id

                    JOIN users faculty_user
                    ON faculty.user_id = faculty_user.id

                    ORDER BY reports.report_date DESC
                    ");

                    while($row = $export->fetch_assoc()){

                        fputcsv($output, [
                            $row['faculty_name'],
                            $row['student_name'],
                            $row['feedback'],
                            $row['rating'],
                            $row['report_date']
                        ]);
                    }

                    fclose($output);
                    exit();
                }



                if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
                    header("Location: ../login.php");
                    exit();
                }

                $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

                /* ================= COUNTS ================= */

                $students = $conn->query("SELECT * FROM students")->num_rows;
                $faculty = $conn->query("SELECT * FROM faculty")->num_rows;
                $topics = $conn->query("SELECT * FROM topics")->num_rows;
                $applications = $conn->query("SELECT * FROM applications")->num_rows;



                    $admin_id = $_SESSION['user_id'];

                $notificationCount = $conn->query("
                SELECT COUNT(*) as total
                FROM notifications
                WHERE user_id='$admin_id'
                AND is_read=0
                ")->fetch_assoc()['total'];

                /* ================= SAVE SEAT LIMIT ================= */

                $message = "";

                if(isset($_POST['save_limit'])){

                    $limit = intval($_POST['seat_limit']);

                    if($limit < 1){
                        $message = "Seat limit must be greater than 0.";
                    }else{

                        $check = $conn->query("
                        SELECT *
                        FROM settings
                        WHERE setting_name='max_student_limit'
                        ");

                        if($check->num_rows > 0){

                            $conn->query("
                            UPDATE settings
                            SET setting_value='$limit'
                            WHERE setting_name='max_student_limit'
                            ");

                        }else{

                            $conn->query("
                            INSERT INTO settings(setting_name, setting_value)
                            VALUES('max_student_limit','$limit')
                            ");
                        }

                        $message = "Seat limit updated successfully!";
                    }
                }

                /* ================= GET LIMIT ================= */

                $limitData = $conn->query("
                SELECT setting_value
                FROM settings
                WHERE setting_name='max_student_limit'
                ")->fetch_assoc();

                $current_limit = $limitData ? $limitData['setting_value'] : 5;

                ?>

                <!DOCTYPE html>
                <html>
                <head>
                <title>Admin Dashboard</title>

                <meta name="viewport" content="width=device-width, initial-scale=1.0">

                <link rel="stylesheet"
                href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

                <style>

                *{
                    margin:0;
                    padding:0;
                    box-sizing:border-box;
                }

                body{
                    font-family:'Segoe UI',sans-serif;
                    background: #0f172a;
                    color:white;
                }

                /* SIDEBAR */

                .sidebar{
                    position:fixed;
                    left:0;
                    top:0;
                    width:260px;
                    height:100vh;
                    background: #111827;
                    border-right:1px solid rgba(255,255,255,0.08);
                    padding-top:20px;
                }

                .logo{
                    text-align:center;
                    padding:20px;
                    border-bottom:1px solid rgba(255,255,255,0.08);
                }

                .logo h2{
                    color:white;
                    font-size:24px;
                }

                .logo p{
                    color:#9ca3af;
                    margin-top:5px;
                    font-size:13px;
                }

                .sidebar a{
                    display:flex;
                    align-items:center;
                    gap:12px;
                    padding:16px 22px;
                    color:white;
                    text-decoration:none;
                    transition:0.3s;
                    font-size:15px;
                }

                .sidebar a:hover{
                    background:#1f2937;
                    color:white;
                    padding-left:28px;
                }

                .active{
                    background:#2563eb;
                    color:white !important;
                }

                /* TOPBAR */

                .topbar{
                    margin-left:260px;
                    height:75px;
                    background:#111827;
                    display:flex;
                    align-items:center;
                    justify-content:space-between;
                    padding:0 30px;
                    border-bottom:1px solid rgba(255,255,255,0.08);
                }

                .topbar h1{
                    font-size:22px;
                }

                /* MAIN */

                .main{
                    margin-left:260px;
                    padding:30px;
                }

                /* CARDS */

                .cards{
                    display:grid;
                    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
                    gap:20px;
                    margin-bottom:30px;
                }

                .stat-card{
                    background:#1e293b;
                    border-radius:18px;
                    padding:25px;
                    transition:0.3s;
                    border:1px solid rgba(255,255,255,0.05);
                }

                .stat-card:hover{
                    transform:translateY(-5px);
                }

                .stat-card i{
                    font-size:30px;
                    margin-bottom:15px;
                    color:#60a5fa;
                }

                .stat-card h2{
                    font-size:32px;
                    margin-bottom:8px;
                }

                /* CONTENT CARD */

                .card{
                    background:#1e293b;
                    border-radius:18px;
                    padding:25px;
                    margin-bottom:25px;
                }

                /* TABLE */

                .table-container{
                    overflow-x:auto;
                }

                table{
                    width:100%;
                    border-collapse:collapse;
                }

                th{
                    background:#2563eb;
                    padding:15px;
                    text-align:left;
                    margin-top:5px
                }

                td{
                    padding:15px;
                    border-bottom:1px solid rgba(255,255,255,0.06);
                }

                /* BUTTONS */

                .btn{
                    border:none;
                    padding:9px 12px;
                    border-radius:10px;
                    cursor:pointer;
                    color:white;
                }

                .btn-approve{
                    background:#10b981;
                }

                .btn-reject{
                    background:#f59e0b;
                }
                .action-col{
                    width:120px;
                    text-align:center;
                    white-space:nowrap;
                }
                .input{
                    width:100%;
                    padding:14px;
                    border:none;
                    border-radius:10px;
                    margin-top:10px;
                    margin-bottom:15px;
                }

                .success{
                    background:#166534;
                    padding:14px;
                    border-radius:10px;
                    margin-bottom:20px;
                }


            .rating{
                padding:6px 12px;
                border-radius:30px;
                font-size:13px;
                font-weight:bold;
                color:white;
                display:inline-block;
            }

            .excellent{
                background:#16a34a;
            }

            .good{
                background:#2563eb;
            }

            .average{
                background:#f59e0b;
            }

            .poor{
                background:#dc2626;
            }
                @media(max-width:768px){

                    .sidebar{
                        width:80px;
                    }

                    .sidebar a span,
                    .logo p,
                    .logo h2{
                        display:none;
                    }

                    .sidebar a{
                        justify-content:center;
                    }

                    .topbar{
                        margin-left:80px;
                    }

                    .main{
                        margin-left:80px;
                    }

                }

                </style>
                </head>

                <body>

                <div class="sidebar">

                <div class="logo">
                <h2>ADMIN</h2>
                <p>Dashboard Panel</p>
                </div>

                <a class="<?php if($page=='dashboard') echo 'active'; ?>"
                href="?page=dashboard">
                <i class="fa fa-home"></i>
                <span>Dashboard</span>
                </a>

                <a class="<?php if($page=='students') echo 'active'; ?>"
                href="?page=students">
                <i class="fa fa-user-graduate"></i>
                <span>Students</span>
                </a>

                <a class="<?php if($page=='faculty') echo 'active'; ?>"
                href="?page=faculty">
                <i class="fa fa-chalkboard-teacher"></i>
                <span>Faculty</span>
                </a>

                <a class="<?php if($page=='topics') echo 'active'; ?>"
                href="?page=topics">
                <i class="fa fa-book"></i>
                <span>Topics</span>
                </a>

                <a class="<?php if($page=='applications') echo 'active'; ?>"
                href="?page=applications">
                <i class="fa fa-file"></i>
                <span>Applications</span>
                </a>

                <a class="<?php if($page=='reports') echo 'active'; ?>"
                href="?page=reports">
                <i class="fa fa-chart-line"></i>
                <span>Reports</span>
                </a>



                <a class="<?php if($page=='settings') echo 'active'; ?>"
                href="?page=settings">
                <i class="fa fa-gear"></i>
                <span>Seat Limit</span>
                </a>


                    <a class="<?php if($page=='notifications') echo 'active'; ?>"
                href="?page=notifications">

                <i class="fa fa-bell"></i>

                <span>Notifications</span>

                <?php if($notificationCount > 0){ ?>

                <span style="
                margin-left:auto;
                background:red;
                padding:3px 8px;
                border-radius:20px;
                font-size:11px;
                ">

                <?= $notificationCount ?>

                </span>

                <?php } ?>

                </a>

                <a href="../logout.php">
                <i class="fa fa-sign-out-alt"></i>
                <span>Logout</span>
                </a>

                </div>



                <div class="topbar">
                <h1>Research Supervision System</h1>

                <div>
                <i class="fa fa-user-shield"></i> Admin Panel
                </div>
                </div>



                <div class="main">

                <?php if($page == 'dashboard'){ ?>
                <div class="cards">

                <div class="stat-card">
                <i class="fa fa-user-graduate"></i>
                <h2><?= $students ?></h2>
                <p>Total Students</p>
                </div>

                <div class="stat-card">
                <i class="fa fa-chalkboard-teacher"></i>
                <h2><?= $faculty ?></h2>
                <p>Total Faculty</p>
                </div>

                <div class="stat-card">
                <i class="fa fa-book"></i>
                <h2><?= $topics ?></h2>
                <p>Total Topics</p>
                </div>

                <div class="stat-card">
                <i class="fa fa-file"></i>
                <h2><?= $applications ?></h2>
                <p>Total Applications</p>
                </div>

                </div>
                <?php } ?>

            <!-- STUDENTS -->

            <?php if($page == 'students'){ 

            if(isset($_GET['delete'])){
                $id = $_GET['delete'];
                $conn->query("DELETE FROM users WHERE id='$id'");
            }

            $sql = "SELECT users.id, users.name, users.email, students.cgpa
                    FROM users
                    JOIN students ON users.id = students.user_id";

            $res = $conn->query($sql);

            ?>

            <div class="card">

            <h3>
            <i class="fa fa-user-graduate"></i>
            Students List
            </h3>

            <br>

            <div class="table-container">

            <table>

            <tr>
            <th>Name</th>
            <th>Email</th>
            <th>CGPA</th>
            <th>Action</th>
            </tr>

            <?php while($row = $res->fetch_assoc()){ ?>

            <tr>

            <td><?= $row['name'] ?></td>

            <td><?= $row['email'] ?></td>

            <td><?= $row['cgpa'] ?></td>

            <td>

            <a href="?page=view_student&id=<?= $row['id'] ?>">

            <button class="btn btn-approve">

            <i class="fa fa-eye"></i>

            </button>

            </a>

            <a href="?page=students&delete=<?= $row['id'] ?>">

            <button class="btn btn-reject">

            <i class="fa fa-trash"></i>

            </button>

            </a>

            </td>

            </tr>

            <?php } ?>

            </table>

            </div>

            </div>

            <?php } ?>


            <!-- VIEW STUDENT -->

            <?php if($page == 'view_student'){ 

            $id = $_GET['id'];

            $data = $conn->query("

            SELECT
            users.*,
            students.*

            FROM users

            JOIN students
            ON users.id = students.user_id

            WHERE users.id='$id'

            ")->fetch_assoc();

            ?>

            <div class="card">

            <h3>
            <i class="fa fa-user"></i>
            Student Details
            </h3>

            <div style="line-height:2; font-size:16px;">

            <p>
            <b>Name:</b>
            <?= htmlspecialchars($data['name']); ?>
            </p>

            <p>
            <b>Email:</b>
            <?= htmlspecialchars($data['email']); ?>
            </p>

            <p>
            <b>CGPA:</b>
            <?= htmlspecialchars($data['cgpa']); ?>
            </p>

            <p>
            <b>Skills:</b>
            <?= htmlspecialchars($data['skills']); ?>
            </p>

            <p>
            <b>Interests:</b>
            <?= htmlspecialchars($data['interests']); ?>
            </p>

            </div>

            <br>

            <a href="?page=students">

            <button class="btn btn-approve">

            <i class="fa fa-arrow-left"></i>

            Back

            </button>

            </a>

            </div>

            <?php } ?>


            <!-- VIEW FACULTY -->

<?php if($page == 'view_faculty'){ 

$id = $_GET['id'];

$data = $conn->query("

SELECT
users.*,
faculty.*

FROM users

JOIN faculty
ON users.id = faculty.user_id

WHERE users.id='$id'

")->fetch_assoc();

?>

<div class="card">

<h3>
<i class="fa fa-chalkboard-teacher"></i>
Faculty Details
</h3>

<div style="line-height:2; font-size:16px;">

<p>
<b>Name:</b>
<?= htmlspecialchars($data['name']); ?>
</p>

<p>
<b>Email:</b>
<?= htmlspecialchars($data['email']); ?>
</p>

<p>
<b>Department:</b>
<?= htmlspecialchars($data['department']); ?>
</p>
</div>

<br>

<a href="?page=faculty">

<button class="btn btn-approve">

<i class="fa fa-arrow-left"></i>

Back

</button>

</a>

</div>

<?php } ?>


            <!-- FACULTY -->

            <?php if($page == 'faculty'){ 

if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $conn->query("DELETE FROM users WHERE id='$id'");
}

$sql = "SELECT users.id, users.name, users.email, faculty.department
        FROM users
        JOIN faculty ON users.id = faculty.user_id";

$res = $conn->query($sql);

            $res = $conn->query($sql);

            ?>

            <div class="card">

            <h3>
            <i class="fa fa-chalkboard-teacher"></i>
            Faculty List
            </h3>

            <br>
            <div class="table-container">

            <table>

<tr>
<th>Name</th>
<th>Email</th>
<th>Department</th>
<th>Action</th>
</tr>

<?php while($row = $res->fetch_assoc()){ ?>

<tr>

<td><?= $row['name'] ?></td>

<td><?= $row['email'] ?></td>

<td><?= $row['department'] ?></td>

<td>

<a href="?page=view_faculty&id=<?= $row['id'] ?>">

<button class="btn btn-approve">
<i class="fa fa-eye"></i>
</button>

</a>

<a href="?page=faculty&delete=<?= $row['id'] ?>">

<button class="btn btn-reject">
<i class="fa fa-trash"></i>
</button>

</a>

</td>

</tr>

<?php } ?>

</table>

            </div>

            </div>

            <?php } ?>



                <!-- APPLICATION MANAGEMENT -->

                <?php if($page == 'applications'){ ?>

                <div class="card">

                <h3>
                <i class="fa fa-file"></i>
                Manage Applications
                </h3>
                 
                <br>
                
                <div class="table-container">

                <table>

                <tr>
                <th>Student</th>
                <th>Topic</th>
                <th>Faculty</th>
                <th>Status</th>
                <th>Applied Date</th>
                <th>Seats Filled</th>
                <th>Seats Left</th>
                </tr>

                <?php

                $sql = "
                SELECT
                applications.*,
                users.name as student_name,
                topics.title,
                topics.max_students,
                faculty_user.name as faculty_name,

                (
                SELECT COUNT(*)
                FROM applications a2
                WHERE a2.topic_id = topics.topic_id
                AND a2.status='approved'
                ) as filled

                FROM applications

                JOIN students
                ON applications.student_id = students.student_id

                JOIN users
                ON students.user_id = users.id

                JOIN topics
                ON applications.topic_id = topics.topic_id

                JOIN faculty
                ON topics.faculty_id = faculty.faculty_id

                JOIN users faculty_user
                ON faculty.user_id = faculty_user.id

                ORDER BY applications.created_at DESC
                ";

                $res = $conn->query($sql);

                while($row = $res->fetch_assoc()){

                $left = $row['max_students'] - $row['filled'];

                ?>

                <tr>

                <td><?= $row['student_name'] ?></td>

                <td><?= $row['title'] ?></td>

                <td><?= $row['faculty_name'] ?></td>

                <td><?= ucfirst($row['status']) ?></td>

                <td><?= $row['created_at'] ?? 'N/A' ?></td>

                <td><?= $row['filled'] ?></td>

                <td><?= $left ?></td>

                </tr>

                <?php } ?>

                </table>

                </div>

                </div>

                <?php } ?>


                <!-- REPORTS -->

                    <?php if($page == 'reports'){ ?>

                    <div class="card">

                    <div style="
                    display:flex;
                    justify-content:space-between;
                    align-items:center;
                    margin-bottom:20px;
                    flex-wrap:wrap;
                    gap:15px;
                    ">

                    <h3>
                    <i class="fa fa-chart-line"></i>
                    Faculty Reports
                    </h3>

                    <a
                    href="?page=reports&download=reports"
                    style="
                    background:#10b981;
                    color:white;
                    padding:12px 18px;
                    border-radius:10px;
                    text-decoration:none;
                    font-weight:600;
                    display:inline-flex;
                    align-items:center;
                    gap:8px;
                    "
                    >

                    <i class="fa fa-download"></i>

                    Download Report

                    </a>

                    </div>

                    <div class="table-container">

                    <table>

                    <tr>
                    <th>Faculty</th>
                    <th>Student</th>
                    <th>Feedback</th>
                    <th>Rating</th>
                    <th>Date</th>
                    </tr>

                    <?php

                    $reports = $conn->query("

                    SELECT
                    reports.*,

                    student_user.name as student_name,
                    faculty_user.name as faculty_name

                    FROM reports

                        JOIN students
                        ON reports.student_id = students.student_id

                        JOIN users student_user
                        ON students.user_id = student_user.id

                        JOIN faculty
                        ON reports.faculty_id = faculty.faculty_id

                        JOIN users faculty_user
                        ON faculty.user_id = faculty_user.id

                        ORDER BY reports.report_date DESC

                        ");

                        if($reports->num_rows > 0){

                        while($row = $reports->fetch_assoc()){

                            $rating = intval($row['rating']);

                            if($rating >= 5){
                                $class = "excellent";
                            }
                            elseif($rating >= 4){
                                $class = "good";
                            }
                            elseif($rating >= 3){
                                $class = "average";
                            }
                            else{
                                $class = "poor";
                            }
                        ?>

                        <tr>

                        <td>

                        <?= htmlspecialchars($row['faculty_name']); ?>

                        </td>

                        <td>

                        <?= htmlspecialchars($row['student_name']); ?>

                        </td>

                        <td style="max-width:350px; line-height:1.7;">

                        <?= nl2br(htmlspecialchars($row['feedback'])); ?>

                        </td>

                        <td>

                        <span class="rating <?= $class; ?>">

                        <?= $row['rating']; ?>/5

                        </span>

                        </td>

                        <td>

                        <?= $row['report_date']; ?>

                        </td>

                        </tr>

                        <?php } } else { ?>

                        <tr>

                        <td colspan="5" style="text-align:center;">

                        No reports found.

                        </td>

                        </tr>

                        <?php } ?>

                        </table>

                        </div>

                        </div>

                        <?php } ?>




                        <?php if($page == 'notifications'){ ?>

                <div class="card">

                <h3>
                <i class="fa fa-bell"></i>
                Notifications
                </h3>

                <?php

                $conn->query("
                UPDATE notifications
                SET is_read=1
                WHERE user_id='$admin_id'
                ");

                $notes = $conn->query("
                SELECT *
                FROM notifications
                WHERE user_id='$admin_id'
                ORDER BY created_at DESC
                ");

                if($notes->num_rows > 0){

                while($n = $notes->fetch_assoc()){
                ?>

                <div style="
                background:#111827;
                padding:15px;
                margin-top:12px;
                border-radius:10px;
                ">

                <b><?= ucfirst($n['type']) ?></b>

                <p style="margin-top:8px;">
                <?= htmlspecialchars($n['message']) ?>
                </p>

                <small>
                <?= $n['created_at'] ?>
                </small>

                </div>

                <?php
                }

                }else{
                ?>

                <p>No notifications found.</p>

                <?php } ?>

                </div>

                <?php } ?>

                    <!-- SETTINGS -->

                    <?php if($page == 'settings'){ ?>

                    <div class="card">

                    <h3>
                    <i class="fa fa-gear"></i>
                    Seat Availability Settings
                    </h3>

                    <?php if($message != ""){ ?>

                    <div class="success">
                    <?= $message ?>
                    </div>

                    <?php } ?>

                    <form method="POST">

                    <label>
                    Fixed Maximum Seat Limit For Faculty Topics
                    </label>

                    <input
                    type="number"
                    name="seat_limit"
                    class="input"
                    value="<?= $current_limit ?>"
                    min="1"
                    required
                    >

                        <button class="btn btn-approve" name="save_limit">
                        Save Settings
                        </button>

                        </form>

                        </div>

                        <?php } ?>

                        <!-- TOPICS -->

                        <?php if($page == 'topics'){ 

                        if(isset($_GET['approve'])){

                    $id = $_GET['approve'];

                    $conn->query("
                    UPDATE topics
                    SET status='approved'
                    WHERE topic_id='$id'
                    ");

                    $topic = $conn->query("
                    SELECT
                    users.id as user_id,
                    topics.title

                    FROM topics

                    JOIN faculty
                    ON topics.faculty_id = faculty.faculty_id

                    JOIN users
                    ON faculty.user_id = users.id

                    WHERE topics.topic_id='$id'
                    ")->fetch_assoc();

                    $conn->query("
                    INSERT INTO notifications
                    (
                        user_id,
                        message,
                        type
                    )
                    VALUES
                    (
                        '".$topic['user_id']."',
                        'Your topic \"".$topic['title']."\" has been approved.',
                        'topic'
                    )
                    ");
                }

                if(isset($_GET['reject'])){

                    $id = $_GET['reject'];
                
                    $conn->query("
                    UPDATE topics
                    SET status='rejected'
                    WHERE topic_id='$id'
                    ");
                
                    $topic = $conn->query("
                    SELECT
                    users.id as user_id,
                    topics.title
                
                    FROM topics
                
                    JOIN faculty
                    ON topics.faculty_id = faculty.faculty_id
                
                    JOIN users
                    ON faculty.user_id = users.id
                
                    WHERE topics.topic_id='$id'
                    ")->fetch_assoc();
                
                    $conn->query("
                    INSERT INTO notifications
                    (
                        user_id,
                        message,
                        type
                    )
                    VALUES
                    (
                        '".$topic['user_id']."',
                        'Your topic \"".$topic['title']."\" has been rejected.',
                        'topic'
                    )
                    ");
                }

                $sql = "SELECT topics.*, users.name
                FROM topics
                JOIN faculty ON topics.faculty_id = faculty.faculty_id
                JOIN users ON faculty.user_id = users.id
                ORDER BY topics.topic_id DESC";

                        $res = $conn->query($sql);

                        ?>

                        <div class="card">

                        <h3><i class="fa fa-book"></i> Topics List</h3>

                        <br>

<div class="table-container">

                        <table>

                        <tr>
        <th>Title</th>
        <th>Faculty</th>
        <th>Created Date</th>
        <th>Max Students</th>
        <th>Status</th>
        <th class="action-col">Action</th>
        </tr>

 <?php while($row = $res->fetch_assoc()){ ?>

         <tr>
        <td><?= $row['title'] ?></td>

        <td><?= $row['name'] ?></td>

        <td><?= $row['deadline'] ?></td>

        <td><?= $row['max_students'] ?></td>

        <td><?= ucfirst($row['status']) ?></td>

        <td class="action-col">

            <a href="?page=topics&approve=<?= $row['topic_id'] ?>">
                <button class="btn btn-approve">
                    <i class="fa fa-check"></i>
                </button>
            </a>

            <a href="?page=topics&reject=<?= $row['topic_id'] ?>">
                <button class="btn btn-reject">
                    <i class="fa fa-times"></i>
                </button>
            </a>

        </td>
    </tr>

  <?php } ?>

                    </table>

                    </div>

                    </div>

                    <?php } ?>

                    </div>

                    </body>
                    </html>
