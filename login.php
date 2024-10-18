<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <?php print "<title>Markify - " . $_POST["username"] . "</title>"; ?>
</head>

<body>
    <div class="nav-bar">
        <h1 id="welcome-msg">Welcome to Markify,
            <?php print $_POST["username"]; ?>
        </h1>

        <div class="button-div">
            <?php
            print('<button id="logoff-button">Logoff</button>');
            ?>
        </div>
    </div>

    <div class="home">

        <?php
        $encryption_iv = '9876543211234567';
        function connectToDatabase()
        {
            $database = mysqli_connect('easy-learn-server.mysql.database.azure.com', 'mwvasqfzwh', 'Password123?', 'bookmarks');
            if (!$database) {
                die("Could not connect to the database</body></html>");
            }
            return $database;
        }

        function validateUser()
        {
            $username = $_POST["username"] ?? "";
            $password = $_POST["password"] ?? "";

            $hashed_password = hash("sha256", $password);

            $query = "SELECT * FROM users WHERE username = ? and password = ?";

            $database = connectToDatabase();
            $stmt = mysqli_prepare($database, $query);
            mysqli_stmt_bind_param($stmt, "ss", $username, $hashed_password);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) == 0) {
                print("
                            <form id='redirect-form' method='post' action='index.php'>
                                <input type='hidden' name='error' value='login error'>
                            </form>
                            <script type='text/javascript'>
                                document.getElementById('redirect-form').submit();
                            </script>");;
                exit();
            }
        }

        function validateActiveURL()
        {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $_POST["website_url"]);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_exec($ch);

            return $ch;
        }

        function getUserID($database)
        {
            $sql = "SELECT * FROM users WHERE users.username = ?";

            $stmt = mysqli_prepare($database, $sql);
            mysqli_stmt_bind_param($stmt, "s", $_POST["username"]);

            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            return $row["id"];
        }

        function checkBookmarkExists($database, $user_id)
        {
            $checkSql = "SELECT * FROM bookmark INNER JOIN users ON bookmark.user_id = users.id WHERE users.id = ? AND bookmark.website_url = ?";
            $checkStmt = mysqli_prepare($database, $checkSql);
            mysqli_stmt_bind_param($checkStmt, "is", $user_id, $_POST["website_url"]);
            mysqli_stmt_execute($checkStmt);
            return mysqli_stmt_get_result($checkStmt);
        }

        function insertBookmark($database, $user_id)
        {
            $insertSql = "INSERT INTO bookmark (user_id, website_url) VALUES (?, ?)";
            $insertStmt = mysqli_prepare($database, $insertSql);
            mysqli_stmt_bind_param($insertStmt, "ss", $val1, $val2);

            $val1 = $user_id;
            $val2 = $_POST["website_url"];
            mysqli_stmt_execute($insertStmt);
        }

        function updateBookmark($database)
        {
            $sql = "UPDATE bookmark SET website_url = ?, date_created = NOW() WHERE bookmark_id = ?";
            $stmt = mysqli_prepare($database, $sql);
            mysqli_stmt_bind_param($stmt, "si", $val1, $val2);

            $val1 = $_POST["website_url"];
            $val2 = $_POST["update_id"];
            mysqli_stmt_execute($stmt);
        }

        function deleteBookmark($database)
        {
            $sql = "DELETE FROM bookmark WHERE bookmark_id = ?";
            $stmt = mysqli_prepare($database, $sql);
            mysqli_stmt_bind_param($stmt, "i", $_POST["delete_id"]);
            mysqli_stmt_execute($stmt);
        }

        function getAllBookmarks($database)
        {
            $sql = "SELECT * FROM bookmark INNER JOIN users ON users.id = bookmark.user_id WHERE users.username = ?";
            $stmt = mysqli_prepare($database, $sql);
            mysqli_stmt_bind_param($stmt, "s", $_POST["username"]);
            mysqli_stmt_execute($stmt);
            return mysqli_stmt_get_result($stmt);
        }

        function encryptPassword($username, $password, $encryption_iv)
        {
            return openssl_encrypt($password, "AES-128-CTR", $username, 0, $encryption_iv);
        }

        function decryptPassword($username, $encrypted_key, $encryption_iv)
        {
            return openssl_decrypt($encrypted_key, "AES-128-CTR", $username, 0, $encryption_iv);
        }

        if (isset($_POST["key"])) {
            $_POST["password"] = decryptPassword($_POST["username"], $_POST["key"], $encryption_iv);
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["password"])) {
            validateUser();
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["website_url"]) && !isset($_POST["update_id"])) {
            $ch = validateActiveURL();

            if (!curl_errno($ch)) {
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($httpCode < 400) {
                    $database = connectToDatabase();
                    $user_id = getUserID($database);

                    $bookmarkResult = checkBookmarkExists($database, $user_id);

                    if (mysqli_num_rows($bookmarkResult) > 0) {
                        print "<p style=\"color: red;\">The bookmark already exists</p>";
                    } else {
                        insertBookmark($database, $user_id);
                    }
                } else {
                    print "<script type=\"text/javascript\">alert('The bookmark URL is not valid: HTTP Status Code $httpCode');</script>";
                }
            } else {
                print "<script type=\"text/javascript\">alert('The bookmark URL is not valid.');</script>";
            }
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["website_url"]) && isset($_POST["update_id"])) {
            $ch = validateActiveURL();

            if (!curl_errno($ch)) {
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                $database = connectToDatabase();

                if ($httpCode < 400) {
                    updateBookmark($database);
                } else {
                    print "<script type=\"text/javascript\">alert('The bookmark URL is not valid: HTTP Status Code $httpCode');</script>";
                }
            } else {
                print "<script type=\"text/javascript\">alert('The bookmark URL is not valid.');</script>";
            }
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {
            $database = connectToDatabase();
            deleteBookmark($database);
        }
        ?>

        <div class="add-bookmark-form">
            <form method="POST" action="" id="addBookmarkForm" onsubmit="return validateURL(this);">
                <h3>Add Bookmark</h3>
                <input type="text" id="website_url" name="website_url" oninput="validateInputURL(event)" required>
                <input type="hidden" value="<?php print $_POST["username"]; ?>" name="username">
                <input type="hidden" value="<?php print encryptPassword($_POST["username"], $_POST["password"], $encryption_iv); ?>" name="key">
                <button type="submit" class="add-button">Add Bookmark</button>
            </form>
        </div>

        <table>
            <?php
            $database = connectToDatabase();
            $result = getAllBookmarks($database);

            $id = 0;
            while ($row = mysqli_fetch_assoc($result)) {
                print "<tr>";

                print sprintf("<td><a href='%s' target='_blank'>%s</a></td>", $row["website_url"], $row["website_url"]);
                $date_created = date_create($row["date_created"]);
                $today = date_create();
                $interval = date_diff($date_created, $today);
                print "<td>" . $interval->format("%a days ago") . "</td>";
                print "<td>";
                print '<form method="POST" action="" style="display:inline;">';
                print '<input type="hidden" name="username" value="' . $_POST["username"] . '">';
                print '<input type="hidden" name="key" value="' . encryptPassword($_POST["username"], $_POST["password"], $encryption_iv) . '">';
                print '<input type="hidden" name="delete_id" value="' . $row["bookmark_id"] . '">';
                print '<button type="submit" class="delete-button">X</button>';
                print "</form>";
                print "</td>";
                print "<td>";
                print '<button class="edit-button" id="button-' . $id . '">Edit</button>';
                print "</td>";
                print "<td>";
                print '<div style = "display:none" id="edit-div-' . $id . '">';
                print '<form method="POST" action="" style="display:inline;" onsubmit="return validateURL(this);">';
                print '<input type="text" name="website_url" oninput = "validateInputURL(event)">';
                print '<input type="hidden" name="username" value="' . $_POST["username"] . '">';
                print '<input type="hidden" name="key" value="' . encryptPassword($_POST["username"], $_POST["password"], $encryption_iv) . '">';
                print '<input type="hidden" name="update_id" value="' . $row["bookmark_id"] . '">';
                print '<button type="submit">Submit Edit</button>';
                print "</form>";
                print "</div>";
                print "</td>";
                print "</tr>";

                $id = $id + 1;
            }
            ?>
        </table>

        <script>
            const editButtons = document.querySelectorAll('.edit-button');

            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const text = button.innerText;
                    const idx = button.id.split('-')[1];

                    if (text === "Edit") {
                        document.getElementById('edit-div-' + idx).style.display = "block";
                        button.innerText = "Close";
                    } else {
                        document.getElementById('edit-div-' + idx).style.display = "none";
                        button.innerText = "Edit";
                    }
                });
            });

            document.getElementById('logoff-button').addEventListener('click', function() {
                window.location.href = 'https://markify-c9bnayeubnagc9ad.canadacentral-01.azurewebsites.net/index.php';
            });

            function validateInputURL(event) {
                const textbox = event.target;
                const input = textbox.value;

                var res = input.match(/^(https:\/\/|http:\/\/)?(www\.)?([-a-zA-Z0-9]{2,})(\.[a-z]{2,8})(\/[a-zA-Z0-9?/:@~!$&'()*+,;=.-]*)?$/);

                if(res !== null && res[3] !== "www"){
                    textbox.style.borderColor = 'green';
                } else{
                    textbox.style.borderColor = 'red';
                }
            }

            function validateURL(form) {
                try {
                    const url = new URL(form.elements['website_url'].value);
                    event.preventDefault();

                    let xmlhttp = new XMLHttpRequest();
                    xmlhttp.open("GET", "verifyURL.php?website_url=" + form.elements['website_url'].value, true);
                    xmlhttp.send();

                    xmlhttp.onreadystatechange = function() {
                        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                            const http_code = xmlhttp.responseText;
                            if ( http_code != null && http_code.length > 0 && http_code < 400 && http_code >= 200) {
                                form.submit();
                            } else {
                                alert("The bookmark URL is not active or request for verification has been denied.");
                                return false;
                            }
                        }
                    };

                } catch (error) {
                    alert("Bookmark is not in the correct URL format. Are you forgetting the http or https?");
                }

                return false;
            }
        </script>
    </div>
</body>

</html>