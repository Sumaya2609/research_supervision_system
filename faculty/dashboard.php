<?php
session_start();
include "../db.php";

/* ================= LOGIN CHECK ================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'faculty') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================= PAGE CONFIG ================= */
$pages = [
    'home' => [
        'title'    => 'Faculty Dashboard',
        'subtitle' => 'Faculty Research Management Dashboard',
        'icon'     => 'fa-house',
        'file'     => null
    ],
    'profile' => [
        'title'    => 'My Profile',
        'subtitle' => 'Manage faculty profile information',
        'icon'     => 'fa-user',
        'file'     => 'profile.php'
    ],
    'create_topic' => [
        'title'    => 'Research Fields',
        'subtitle' => 'Create and manage research fields',
        'icon'     => 'fa-lightbulb',
        'file'     => 'create_topic.php'
    ],
    'students' => [
        'title'    => 'Students',
        'subtitle' => 'View approved students',
        'icon'     => 'fa-users',
        'file'     => null
    ],
    'applications' => [
        'title'    => 'Applications',
        'subtitle' => 'Manage student applications',
        'icon'     => 'fa-file-circle-check',
        'file'     => null
    ],
    'tasks' => [
        'title'    => 'Task Management',
        'subtitle' => 'Assign and review tasks',
        'icon'     => 'fa-list-check',
        'file'     => 'tasks.php'
    ],
    'progress' => [
        'title'    => 'Progress Tracking',
        'subtitle' => 'Track student progress',
        'icon'     => 'fa-chart-line',
        'file'     => 'progress.php'
    ],
    'reports' => [
        'title'    => 'Reports',
        'subtitle' => 'View student reports',
        'icon'     => 'fa-clipboard',
        'file'     => 'reports.php'
    ],
    'chat' => [
        'title'    => 'Chat',
        'subtitle' => 'Communicate with students',
        'icon'     => 'fa-comments',
        'file'     => 'chat.php'
    ],
    'notifications' => [
        'title'    => 'Notifications',
        'subtitle' => 'Recent alerts and updates',
        'icon'     => 'fa-bell',
        'file'     => null
    ]
];

/* ================= CURRENT PAGE ================= */
$page = $_GET['page'] ?? 'home';
if (!array_key_exists($page, $pages)) {
    $page = 'home';
}
$current = $pages[$page];

/* ================= FACULTY INFO ================= */
$stmt = $conn->prepare("SELECT faculty_id FROM faculty WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$faculty     = $stmt->get_result()->fetch_assoc();
$faculty_id  = $faculty['faculty_id'] ?? 0;
$stmt->close();

/* ================= STATS ================= */
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM applications a
    JOIN topics t ON a.topic_id = t.topic_id
    WHERE t.faculty_id = ? AND a.status = 'approved'
");
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$studentCount = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM applications a
    JOIN topics t ON a.topic_id = t.topic_id
    WHERE t.faculty_id = ?
");
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$appCount = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = ? AND is_read = 0
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifCount = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($current['title']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f7fb;
            color: #1e293b;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 270px;
            height: 100vh;
            background: white;
            position: fixed;
            left: 0; top: 0;
            overflow-y: auto;
            border-right: 1px solid #e2e8f0;
            box-shadow: 5px 0 20px rgba(0,0,0,0.04);
            z-index: 999;
        }

        /* ===== LOGO ===== */
        .logo {
            padding: 28px 25px;
            background: #2563eb;
            color: white;
            font-size: 24px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* ===== MENU ===== */
        .menu { padding: 20px 15px; }

        .menu a {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            color: #334155;
            padding: 14px 18px;
            border-radius: 14px;
            margin-bottom: 10px;
            transition: 0.3s;
            font-weight: 500;
        }
        .menu a:hover {
            background: #eff6ff;
            color: #2563eb;
            transform: translateX(4px);
        }
        .menu a.active {
            background: #2563eb;
            color: white;
        }

        /* ===== BADGE ===== */
        .badge {
            background: red;
            color: white;
            padding: 4px 9px;
            border-radius: 50px;
            font-size: 11px;
            margin-left: auto;
        }

        /* ===== MAIN ===== */
        .main {
            margin-left: 270px;
            padding: 30px;
        }

        /* ===== TOPBAR ===== */
        .topbar {
            background: white;
            padding: 22px 28px;
            border-radius: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.04);
        }
        .topbar h1 { font-size: 28px; color: #2563eb; }
        .topbar p  { color: #64748b; margin-top: 5px; }

        /* ===== BUTTON ===== */
        .btn {
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 12px;
            cursor: pointer;
            text-decoration: none;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }
        .btn:hover { background: #1d4ed8; transform: translateY(-2px); }

        /* ===== GRID ===== */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 22px;
            margin-bottom: 30px;
        }

        /* ===== STAT BOX ===== */
        .box {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.04);
            transition: 0.3s;
            position: relative;
            overflow: hidden;
        }
        .box:hover { transform: translateY(-5px); }
        .box::before {
            content: '';
            position: absolute;
            width: 100px; height: 100px;
            background: #dbeafe;
            border-radius: 50%;
            top: -40px; right: -40px;
        }
        .box h3 { font-size: 34px; color: #2563eb; margin-bottom: 10px; }
        .box p  { color: #64748b; font-size: 15px; }

        /* ===== CARD ===== */
        .card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.04);
            margin-bottom: 25px;
        }
        .card h2 { margin-bottom: 18px; color: #2563eb; }

        /* ===== TABLE ===== */
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2563eb; color: white; padding: 15px; text-align: left; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; color: #334155; }
        tr:hover { background: #f8fafc; }

        /* ===== STATUS ===== */
        .approved  { color: #16a34a; font-weight: bold; }
        .pending   { color: #f59e0b; font-weight: bold; }
        .rejected  { color: #dc2626; font-weight: bold; }

        /* ===== NOTIFICATION ===== */
        .notification {
            background: #f8fafc;
            padding: 18px;
            border-radius: 14px;
            margin-bottom: 15px;
            border-left: 5px solid #2563eb;
        }
        .notification small { color: #64748b; }

        /* ===== EMPTY ===== */
        .empty { text-align: center; padding: 40px; color: #94a3b8; }
        .empty i { font-size: 70px; margin-bottom: 15px; }

        /* ===== MOBILE ===== */
        @media (max-width: 900px) {
            .sidebar { width: 100%; height: auto; position: relative; }
            .main    { margin-left: 0; padding: 15px; }
            .topbar  { flex-direction: column; align-items: flex-start; gap: 15px; }
        }
    </style>
</head>
<body>

<!-- ================= SIDEBAR ================= -->
<div class="sidebar">
    <div class="logo">
        <i class="fa-solid fa-user-tie"></i>
        Faculty Panel
    </div>
    <div class="menu">
        <?php foreach ($pages as $key => $item): ?>
        <a class="<?= ($page === $key) ? 'active' : '' ?>" href="?page=<?= $key ?>">
            <i class="fa-solid <?= $item['icon'] ?>"></i>
            <?= $item['title'] ?>
            <?php if ($key === 'notifications' && $notifCount > 0): ?>
                <span class="badge"><?= $notifCount ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
        <a href="../logout.php">
            <i class="fa-solid fa-right-from-bracket"></i>
            Logout
        </a>
    </div>
</div>

<!-- ================= MAIN ================= -->
<div class="main">

    <!-- ================= TOPBAR ================= -->
    <div class="topbar">
        <div>
            <h1><?= htmlspecialchars($current['title']) ?></h1>
            <p><?= htmlspecialchars($current['subtitle']) ?></p>
        </div>
        <div>
            <a href="javascript:history.back()" class="btn">
                <i class="fa-solid fa-arrow-left"></i>
                Back
            </a>
        </div>
    </div>

    <?php

    /* ================= HOME ================= */
    if ($page === 'home'):
    ?>
    <div class="grid">
        <div class="box">
            <h3><?= $studentCount ?></h3>
            <p>Approved Students</p>
        </div>
        <div class="box">
            <h3><?= $appCount ?></h3>
            <p>Total Applications</p>
        </div>
        <div class="box">
            <h3><?= $notifCount ?></h3>
            <p>Unread Notifications</p>
        </div>
    </div>

    <?php

    /* ================= STUDENTS ================= */
    elseif ($page === 'students'):
        $stmt = $conn->prepare("
            SELECT u.name, u.email
            FROM applications a
            JOIN students s  ON a.student_id = s.student_id
            JOIN users u     ON s.user_id    = u.id
            JOIN topics t    ON a.topic_id   = t.topic_id
            WHERE t.faculty_id = ? AND a.status = 'approved'
        ");
        $stmt->bind_param("i", $faculty_id);
        $stmt->execute();
        $res = $stmt->get_result();
    ?>
    <div class="card">
        <h2>Approved Students</h2>
        <div class="table-container">
            <?php if ($res->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                </tr>
                <?php while ($row = $res->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
            <?php else: ?>
            <div class="empty">
                <i class="fa-regular fa-user"></i>
                <h3>No Students Found</h3>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php $stmt->close(); ?>

    <?php

    /* ================= APPLICATIONS ================= */
    elseif ($page === 'applications'):
        $stmt = $conn->prepare("
            SELECT u.name, t.title, a.status
            FROM applications a
            JOIN students s ON a.student_id = s.student_id
            JOIN users u    ON s.user_id    = u.id
            JOIN topics t   ON a.topic_id   = t.topic_id
            WHERE t.faculty_id = ?
        ");
        $stmt->bind_param("i", $faculty_id);
        $stmt->execute();
        $res = $stmt->get_result();
    ?>
    <div class="card">
        <h2>Applications</h2>
        <div class="table-container">
            <?php if ($res->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Student</th>
                    <th>Topic</th>
                    <th>Status</th>
                </tr>
                <?php while ($row = $res->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td class="<?= strtolower($row['status']) ?>">
                        <?= ucfirst($row['status']) ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
            <?php else: ?>
            <div class="empty">
                <i class="fa-regular fa-folder-open"></i>
                <h3>No Applications Found</h3>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php $stmt->close(); ?>

    <?php

    /* ================= NOTIFICATIONS ================= */
    elseif ($page === 'notifications'):
        $stmt = $conn->prepare("
            SELECT * FROM notifications
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();

        // Mark all as read
        $mark = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $mark->bind_param("i", $user_id);
        $mark->execute();
        $mark->close();
    ?>
    <div class="card">
        <h2>Notifications</h2>
        <?php if ($res->num_rows > 0): ?>
            <?php while ($row = $res->fetch_assoc()): ?>
            <div class="notification">
                <p><?= htmlspecialchars($row['message']) ?></p>
                <br>
                <small><?= htmlspecialchars($row['created_at']) ?></small>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
        <div class="empty">
            <i class="fa-regular fa-bell-slash"></i>
            <h3>No Notifications Yet</h3>
        </div>
        <?php endif; ?>
    </div>

    <?php

    /* ================= DYNAMIC INCLUDE ================= */
    elseif (!empty($current['file'])):
        $file_path = __DIR__ . '/' . $current['file'];
        if (file_exists($file_path)) {
            include $file_path;
        } else {
            echo "<div class='card'><h2>Page file not found: " . htmlspecialchars($current['file']) . "</h2></div>";
        }

    /* ================= NOT FOUND ================= */
    else:
    ?>
    <div class="card">
        <h2>Page Not Found</h2>
    </div>
    <?php endif; ?>

</div><!-- /.main -->
</body>
</html>