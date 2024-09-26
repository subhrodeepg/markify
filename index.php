<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Markify</title>
</head>

<body>
    <div class="login">
        <div class='nav-bar'>
            <h1 id='welcome-msg'>Welcome to Markify</h1>
        </div>
        <h2>View the most popular bookmarks here, or login to bookmark websites for yourself.</h2>
        <h3>Most popular sites:</h3>
        <h4>
            <ol>
                <?php
                function connectToDatabase()
                {
                    $database = mysqli_connect('easy-learn-server.mysql.database.azure.com', 'mwvasqfzwh', 'Password123?', 'bookmarks');
                    if (!$database) {
                        die("Could not connect to the database</body></html>");
                    }
                    return $database;
                }
                $query = "SELECT website_url, COUNT(*) AS bookmark_count
                                        FROM bookmark
                                        GROUP BY website_url
                                        ORDER BY bookmark_count DESC
                                        LIMIT 10;";

                $database = connectToDatabase();

                mysqli_select_db($database, "bookmarks");

                $result = mysqli_query($database, $query);

                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<li><a href='" . $row["website_url"] . "' target='_blank'>" . $row["website_url"] . "</a></li>";
                }

                ?>

            </ol>
        </h4>
        <h1>Login</h1>
        <?php
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["error"]) && $_POST["error"] == "login error") {
            print("<p style=\"color: red;\">Incorrect Username or Password. Please try again.</p>");
        }
        ?>
        <form id="login-form" method="post" action="login.php">
            <p>Username </p> <input name="username" class="login-input" type="text">
            <p>Password </p> <input name="password" class="login-input" type="password">
        </form>
        <div class="submit-login-buttons"><button id="login">Login</button> <button id="sign-up">Sign Up</button>
        </div>
    </div>

    <script>
        document.getElementById('login').addEventListener('click', function () {
            document.getElementById('login-form').submit();
        });

        document.getElementById('sign-up').addEventListener('click', function () {
            window.location.href = 'https://markify-c9bnayeubnagc9ad.canadacentral-01.azurewebsites.net/sign-up.php';
        });
    </script>
</body>

</html>