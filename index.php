<!DOCTYPE html>
<html>
<head>
<title>Research System</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body {
    margin: 0;
    font-family: Arial;
}

.hero {
    height: 100vh;
    background: url('index.jpg') no-repeat center center/cover;
    position: relative;
}

.overlay {
    position: absolute;
    width: 100%;
    height: 100%;
    background: rgba(9, 20, 19, 0.88);
}

.content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    color: white;
    width: 80%;
}

h1 {
    font-size: 38px;
    margin-bottom: 15px;
    color: #B0E4CC;
}

p {
    color: #B0E4CC;
}

.btn-container {
    margin-top: 40px;
}

.btn {
    display: inline-block;
    margin: 12px;
    padding: 14px 30px;
    background: #285A48;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    transition: 0.3s;
}

.btn:hover {
    background: #408A71;
}

.btn i {
    margin-right: 10px;
}
</style>

</head>

<body>

<div class="hero">
    <div class="overlay"></div>

    <div class="content">
        <h1>Research Supervision and Topic Matching System</h1>
        <p>Connecting Students with Faculty through Smart Topic Matching</p>

        <div class="btn-container">
            <a href="login.php?role=student" class="btn">
                <i class="fa fa-user-graduate"></i> Student
            </a>

            <a href="login.php?role=faculty" class="btn">
                <i class="fa fa-chalkboard-teacher"></i> Faculty
            </a>

            <a href="login.php?role=admin" class="btn">
                <i class="fa fa-user-shield"></i> Admin
            </a>
        </div>
    </div>
</div>

</body>
</html>