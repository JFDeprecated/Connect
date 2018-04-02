<?php
session_start();

//Connect to Database
$dbhost = "localhost";
$dbuser = "username";
$dbpass = "password";
$dbname = "database";

$connection = mysqli_connect($dbhost, $dbuser, $dbpass);
if (!$connection) {
    die("Database Connection Failed" . mysqli_error($connection));
}
$select_db = mysqli_select_db($connection, $dbname);
if (!$select_db) {
    die("Database Selection Failed" . mysqli_error($connection));
}

//Register Account
if (isset($_REQUEST['register'])) {
    $username = $_REQUEST['username'];
    $password = $_REQUEST['password'];
    $email = $_REQUEST['email'];

    if (preg_match("/^[A-Za-z][A-Za-z0-9]*(?:_[A-Za-z0-9]+)*$/", $username) == 0) {
        $ac_message = "Username contains invalid characters. Please try again.";

    } else {
        $result = mysqli_query($connection, "INSERT INTO `user` (username, password, email) VALUES ('$username', '$password', '$email')");
        if ($result) {
            $ac_message = "Account registered successfully. You may now login to your account.";
            session_destroy();

        } else {
            $ac_message = "Username or email already exists. Please try again.";
        }
    }
}

//Login Account
if (isset($_REQUEST['login'])) {
    $username = $_REQUEST['username'];
    $password = $_REQUEST['password'];

    $result = mysqli_query($connection, "SELECT * FROM `user` WHERE username='$username' and password='$password'");
    $count = mysqli_num_rows($result);
    if ($count == 1) {
        $_SESSION['username'] = $username;
    } else {
        $ac_message = 'Invalid Login Credentials. Please try again.';
    }
}

//Logout Account
if (isset($_REQUEST['logout'])) {
    session_start();
    session_destroy();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>JF Connect</title>

    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid">
    <nav class="navbar navbar-light bg-light">
        <a class="navbar-brand" href="#">JF Connect</a>
    </nav>

    <div class="row">
        <div class="col-md-7">

            <h1>Browse by Hashtag</h1>
            <form>
                <input type="text" name="tag" class="form-control" placeholder="#tag"><br>
                <input type="submit" name="browse" value="Browse" class="btn btn-success">
            </form>

            <?php
            $tag = (isset($_GET['tag']) ? $_GET['tag'] : NULL);

            if ($tag) {
                echo "Search results for <b>#$tag</b>:";
                $result = mysqli_query($connection, "SELECT * FROM `post` WHERE tag='$tag' ORDER BY id DESC");
                while ($row = mysqli_fetch_array($result)) {
                    $username = $row['username'];
                    $content = $row['content'];

                    echo "<h3>@$username</h3>
                    <p>$content</p>";
                }
            }
            ?>

            <hr>

            <!-- News Feed -->
            <h1>News Feed</h1>
            <?php
            $result = mysqli_query($connection, "SELECT * FROM `post` ORDER BY id DESC LIMIT 10");
            while ($row = mysqli_fetch_array($result)) {
                $username = $row['username'];
                $tag = $row['tag'];
                $content = $row['content'];
                echo "<h3>$username</h3><b>#$tag</b> $content";
            } ?>
            <hr>
        </div>

        <div class="col-md-5">

            <h1>My Account</h1>
            <?php
            if (isset($ac_message)) echo $ac_message;

            if (isset($_SESSION['username'])) {
                $username = $_SESSION['username']; ?>

                Welcome, @<?= $username ?>! <a href="?logout=1">Logout</a>

                <hr>

                <?php
                if (isset($_REQUEST['post'])) {
                    $content = $_REQUEST['content'];
                    $tag = $_REQUEST['tag'];

                    mysqli_query($connection, "INSERT INTO `post`(`username`,`content`,`tag`) VALUES('$username','$content','$tag')");
                    ?>

                    Posted Successfully. Refreshing...
                    <meta http-equiv="refresh" content="2; url=index.php"/>

                <?php } ?>

                <h2>Create a new post</h2>
                <form method="post">
                    <input type="text" name="tag" class="form-control" placeholder="#tag"/><br>
                    <textarea name="content" rows="10" width=100% placeholder="Content"
                              class="form-control"></textarea><br>
                    <input type="submit" value="Post" class="btn btn-success" name="post"/>
                </form>

            <?php } else { ?>

                <h3>Login</h3>
                <form action="" method="POST">
                    <p>Username: @
                        <input id="username" type="text" name="username" placeholder="Username"/>
                    </p>

                    <p>Password:
                        <input id="password" type="password" name="password" placeholder="Password"/>
                    </p>

                    <input type="submit" name="login" value="Login" class="btn btn-success"/>
                </form>

                <hr>

                <h3>Register</h3>
                <form action="#" method="POST">
                    <p>Username: @
                        <input type="text" name="username" placeholder="Username"/>
                    </p>
                    <p>Email:
                        <input type="email" name="email" placeholder="Email"/>
                    </p>
                    <p>Password:
                        <input type="password" name="password" placeholder="Password"/>
                    </p>
                    <input type="submit" name="register" value="Register" class="btn btn-success"/>
                </form>

            <?php } ?>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
