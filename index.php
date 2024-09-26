<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Markify</title>
</head>

<body>
    <h1>Welcome to Markify!</h1>
    <div class="flex-container">
        <div class="content">
            <h2>View the most popular bookmarks here, or login to bookmark websites for yourself.</h2>
            <div class="flex-container">
                <div class="content">
                    <h3>Most popular sites:</h3>
                    <h4>
                        <ol>
                            <?php
                            function connectToDatabase()
                            {
                                $database = mysqli_connect("localhost", "root", "password123", "bookmarks");
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

                            if (!($database = mysqli_connect("localhost", "root", "password123")))
                                die("Could not connect to the database</body></html>");

                            if (!mysqli_select_db($database, "bookmarks"))
                                die("Could not find bookmarks db");

                            if (!($result = mysqli_query($database, $query)))
                                die("Could not run the query" . $query);

                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<li><a href='" . $row["website_url"] . "' target='_blank'>" . $row["website_url"] . "</a></li>";
                            }

                            ?>

                        </ol>
                    </h4>
                </div>
            </div>
            <h1>Login</h1>
            <div class="flex-container">
                <div class="content">
                    <form method="post" action="login.php">
                        <p>Username </p> <input name="username" class="login-input" type="text">
                        <p>Password </p> <input name="password" class="login-input" type="password">
                        <button type="submit" class="edit-button">Login</button>
                    </form>
                </div>
            </div>
            <button id="sign-up" class="floating-button">Sign Up</button>
        </div>
    </div>
    <script>
        document.getElementById('sign-up').addEventListener('click', function () {
            window.location.href = 'http://localhost:3000/assignment_1/src/sign-up.php';
        });
    </script>
</body>

</html>