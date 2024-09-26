<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Markify</title>
</head>

<body>
    <div class='nav-bar'>
        <h1 id='welcome-msg'>Welcome to Markify</h1>

        <div class="button-div">
            <?php
            print('<button id="login-button">Login</button>');
            ?>
        </div>
    </div>
    <div class="sign-up">

        <?php
        function connectToDatabase()
        {
            $database = mysqli_connect('easy-learn-server.mysql.database.azure.com', 'mwvasqfzwh', 'Password123?', 'bookmarks');
            if (!$database) {
                die("Could not connect to the database</body></html>");
            }
            return $database;
        }

        function isUsernameTaken($database, $username)
        {
            $query = "SELECT * FROM users WHERE username = ?";
            $stmt = mysqli_prepare($database, $query);
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            return mysqli_num_rows($result) > 0;
        }

        function insertUser($database, $username, $password)
        {
            $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
            $stmt = mysqli_prepare($database, $sql);
            $hashedPassword = hash('sha256', $password);
            mysqli_stmt_bind_param($stmt, "ss", $username, $hashedPassword);
            return mysqli_stmt_execute($stmt);
        }

        function redirectToLogin($username, $password)
        {
            echo "
        <form id='redirectForm' method='post' action='login.php'>
            <input type='hidden' name='username' value='" . htmlspecialchars($username) . "'>
            <input type='hidden' name='password' value='" . htmlspecialchars($password) . "'>
        </form>
        <script type='text/javascript'>
            document.getElementById('redirectForm').submit();
        </script>";
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST["username"];
            $password = $_POST["password"];
            $database = connectToDatabase();

            if (isUsernameTaken($database, $username)) {
                echo ("<p style=\"color: red;\">Username ($username) already exists. Please select another username.</p>");
            } else {
                if (insertUser($database, $username, $password)) {
                    redirectToLogin($username, $password);
                } else {
                    echo ("<p style=\"color: red;\">Failed to create an account. Please try again.</p>");
                }
            }

            mysqli_close($database);
        }
        ?>

        <form method="post" action="">
            <p>Username </p> <input name="username" class="login-input" type="text">
            <p>Password </p> <input name="password" class="login-input" type="password">
            <button type="submit">Submit</button>
        </form>
    </div>
    <script>
        document.getElementById('login-button').addEventListener('click', function () {
            window.location.href = 'https://markify-c9bnayeubnagc9ad.canadacentral-01.azurewebsites.net/index.php';
        });
    </script>
</body>

</html>