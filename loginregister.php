<?php
session_start();
if (isset($_SESSION["user"])) {
    header("Location: index.php");
}

if (isset($_POST["login"])) {
    $emailOrUsername = $_POST["email_or_username"];
    $password = $_POST["password"];
    require_once "database.php";
    $sql = "SELECT * FROM users WHERE email = '$emailOrUsername' OR username = '$emailOrUsername'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_array($result, MYSQLI_ASSOC);
    if ($user) {
        if (password_verify($password, $user["password"])) {
            $_SESSION["user"] = $user["username"];
            header("Location: index.php");
            die();
        } else {
            $loginError = "Password does not match";
        }
    } else {
        $loginError = "Email/Username does not match";
    }
}

if (isset($_POST["submit"])) {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $passwordRepeat = $_POST["repeat_password"];

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $errors = array();

    if (empty($username) || empty($email) || empty($password) || empty($passwordRepeat)) {
        array_push($errors, "All fields are required");
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        array_push($errors, "Email is not valid");
    }
    if (strlen($password) < 8) {
        array_push($errors, "Password must be at least 8 characters long");
    }
    if ($password !== $passwordRepeat) {
        array_push($errors, "Password does not match");
    }

    require_once "database.php";
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    $rowCount = mysqli_num_rows($result);
    if ($rowCount > 0) {
        array_push($errors, "Email already exists!");
    }
    if (count($errors) > 0) {
        foreach ($errors as $error) {
            $registerError = $error;
        }
    } else {
        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = mysqli_stmt_init($conn);
        $prepareStmt = mysqli_stmt_prepare($stmt, $sql);
        if ($prepareStmt) {
            mysqli_stmt_bind_param($stmt, "sss", $username, $email, $passwordHash);
            mysqli_stmt_execute($stmt);
            $registerSuccess = "You are registered successfully.";
        } else {
            die("Something went wrong");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login and Regis Form</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="login.css">
</head>

<body>
    <div class="form-structor">
        <form class="signup" action="loginregister.php" method="post">
            <h2 class="form-title" id="signup">Sign up</h2>
            <div class="form-holder">
                <input type="text" class="input" placeholder="Name" name="username" />
                <input type="email" class="input" placeholder="Email" name="email" />
                <input type="password" class="input" placeholder="Password" name="password" />
                <input type="password" name="repeat_password" class="input" placeholder="Repeat Password">
            </div>
            <button class="submit-btn" name="submit">Sign up</button>
            <?php
            if (isset($_POST["submit"]) && !empty($errors)) {
                echo '<div class="error-message">';
                foreach ($errors as $error) {
                    echo '<p style="color:red; text-align:center; font-family:verdana;">' . $error . '</p>';
                }
                echo '</div>';
            }
            ?>
        </form>
        <form class="login slide-up" action="loginregister.php" method="post">
            <div class="center">
                <h2 class="form-title" id="login">Log in</h2>
                <div class="form-holder">
                    <input type="text" class="input" placeholder="Email or Username" name="email_or_username" />
                    <input type="password" class="input" placeholder="Password" name="password" />
                </div>
                <button class="submit-btn" name="login">Log in</button>
                <?php
                if (isset($_POST["login"]) && isset($loginError)) {
                    echo '<div class="error-message">';
                    echo '<p style="color:red; text-align:center; font-family:verdana;">' . $loginError . '</p>';
                    echo '</div>';
                }
                ?>
            </div>
        </form>
    </div>



</body>
<script>
    console.clear();

    const loginBtn = document.getElementById('login');
    const signupBtn = document.getElementById('signup');

    loginBtn.addEventListener('click', (e) => {
        let parent = e.target.parentNode.parentNode;
        Array.from(e.target.parentNode.parentNode.classList).find((element) => {
            if (element !== "slide-up") {
                parent.classList.add('slide-up')
            } else {
                signupBtn.parentNode.classList.add('slide-up')
                parent.classList.remove('slide-up')
            }
        });
    });

    signupBtn.addEventListener('click', (e) => {
        let parent = e.target.parentNode;
        Array.from(e.target.parentNode.classList).find((element) => {
            if (element !== "slide-up") {
                parent.classList.add('slide-up')
            } else {
                loginBtn.parentNode.parentNode.classList.add('slide-up')
                parent.classList.remove('slide-up')
            }
        });
    });
</script>

</html>