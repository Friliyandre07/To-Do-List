<?php
session_start();
if (isset($_SESSION["user"])) {
    $username = $_SESSION["user"];
} else {
    echo "Session user is not set.";
}

if (!isset($_SESSION["user"])) {
    header("Location: loginregister.php");
}

$errors = "";
$db = mysqli_connect("localhost", "root", "", "todo");

$editing = false;

if (isset($_POST['submit'])) {
    if (empty($_POST['task'])) {
        $errors = "You must fill in the task";
    } else {
        $username = $_SESSION["user"];
        $task = $_POST['task'];
        $task_status = $_POST['task_status'];
        $deadline = $_POST['deadline'];

        require_once "database.php";


        $check_query = mysqli_query($db, "SELECT * FROM tasks WHERE task = '$task' AND status = '$task_status' AND username = '$username'");

        if (mysqli_num_rows($check_query) > 0) {
            $errors = "Task already exists for this user";
        } else {
            $sql = "INSERT INTO tasks (username, task, deadline, done, status) VALUES ('$username', '$task', '$deadline', '0', '$task_status')";
            mysqli_query($db, $sql);
            header('location: index.php');
        }
    }
}

if (isset($_GET['del_task'])) {
    $id = $_GET['del_task'];
    mysqli_query($db, "DELETE FROM tasks WHERE id=" . $id);
    header('location: index.php');
}

if (isset($_GET['edit_task'])) {
    $id = $_GET['edit_task'];

    $edit_query = mysqli_query($db, "SELECT * FROM tasks WHERE id=" . $id);

    if (mysqli_num_rows($edit_query) > 0) {
        $edit_row = mysqli_fetch_assoc($edit_query);
        $edit_task = $edit_row['task'];
        $editing = true;
    }
}

if (isset($_POST['edit'])) {
    $edited_id = $_POST['edit_id'];
    $edited_task = $_POST['edited_task'];
    $edited_task_status = $_POST['edited_task_status'];
    $deadline = $_POST['deadline'];
    if (empty($edited_task)) {
        $errors = "You must fill in the task";
    } else {
        mysqli_query($db, "UPDATE tasks SET task='$edited_task', deadline='$deadline', status='$edited_task_status' WHERE id=" . $edited_id);
        header('location: index.php');
    }
}
if (isset($_POST['done'])) {
    $task_id = $_POST['task_id'];
    $done = $_POST['done'] == 'on' ? 1 : 0;
    mysqli_query($db, "UPDATE tasks SET done=$done WHERE id=$task_id");
    header('location: index.php');
}

if (isset($_GET['undone_task'])) {
    $undone_id = $_GET['undone_task'];

    mysqli_query($db, "UPDATE tasks SET done=0 WHERE id=" . $undone_id);
    header('location: index.php');
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>To Do List</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/dd2fb49190.js" crossorigin="anonymous"></script>
</head>

<body>
    <div class="heading">
        <img src="./img/logo_todo.png" alt="">
        <h2 style="font-style: 'Hervetica';">To Do List</h2>
        <a style="
        color: #333;
        text-decoration: none;
        padding: 8px 16px;
        border-radius: 4px;
        background-color: #fff;
        display: inline-block;" href="logout.php" class="logout">Logout</a>
    </div>
    <div class="container">
        <div>
            <form method="post" action="index.php" class="input_form">
                <?php if (isset($errors)) { ?>
                    <p><?php echo $errors; ?></p>
                <?php } ?>
                <?php if (isset($editing) && $editing == true) : ?>
                    <input type="hidden" name="edit_id" value="<?php echo $id; ?>">
                    <input type="text" name="edited_task" class="task_input" value="<?php echo $edit_task; ?>">
                    <input type="date" name="deadline" id="deadline" class="deadlinecss" style="width:150px;height:30px;margin-top:15px;margin-right:10px;border-radius:10px;font-weight:bold;">
                    <select name="edited_task_status" class="task_status">
                        <option value="On Progress">On Progress</option>
                        <option value="Not Yet Started">Not Yet Started</option>
                        <option value="Waiting On">Waiting On</option>
                    </select>
                    <button type="edit" name="edit" id="add_btn" class="add_btn">Edit Task</button>
                <?php else : ?>
                    <input type="hidden" name="username" value="<?php echo $_SESSION["user"]; ?>">
                    <input type="text" name="task" class="task_input">
                    <input type="date" name="deadline" id="deadline" class="deadlinecss" style="width:150px;height:30px;margin-top:15px;margin-right:10px;border-radius:10px;font-weight:bold;">
                    <select name="task_status" class="task_status">
                        <option value="On Progress">On Progress</option>
                        <option value="Not Yet Started">Not Yet Started</option>
                        <option value="Waiting On">Waiting On</option>
                    </select>
                    <button type="submit" name="submit" id="add_btn" class="add_btn">Add Task</button>
                <?php endif; ?>

            </form>
        </div>
        <div class="container2">
            <table>
                <thead>
                    <tr style="border-bottom: 2px solid #ddd;">
                        <th>No.</th>
                        <th>Tasks</th>
                        <th style="text-align:center;">Due Date</th>
                        <th style="text-align:center;">Action</th>
                        <th style="display:flex;justify-content:center;position:relative;padding-right:25%;">Progress</th>
                        <th style="text-align:center;">Done</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // select all tasks if page is visited or refreshed
                    $tasks = mysqli_query($db, "SELECT * FROM tasks WHERE username='$username' AND done = 0");
                    $i = 1;
                    while ($row = mysqli_fetch_array($tasks)) { ?>
                        <tr style="border-bottom: 2px solid #ddd;">
                            <td> <?php echo $i; ?> </td>
                            <td style="font-family: 'Lato', sans-serif; font-weight:bolder;" class="task"> <?php echo $row['task']; ?> </td>
                            <td style="text-align:center; font-family: 'Baloo Bhai 2', sans-serif;"><?= $row['deadline']; ?></td>
                            <td class="action">
                                <a href="index.php?del_task=<?php echo $row['id'] ?>"><i class="fa-solid fa-trash" style="color: #1a3461;"></i></a>
                                <a href="index.php?edit_task=<?php echo $row['id'] ?>"><i class="fa-solid fa-pen-to-square" style="color: #1d355d;"></i></a>
                            </td>
                            <td class="progress <?php echo strtolower(str_replace(' ', '_', $row['status'])); ?>"> <a><?php echo $row['status']; ?></a> </td>
                            <td style="display:flex;justify-content:center;" class="done">
                                <form method="post" action="index.php">
                                    <input type="hidden" name="task_id" value="<?php echo $row['id'] ?>">
                                    <label class="checkbox-image">
                                        <input type="checkbox" name="done" style="display: none;" <?php echo $row['done'] ? 'checked' : ''; ?> onchange="this.form.submit()">
                                        <img class="uncheckedbox" style="width:20px;height:20px;" src="<?php echo $row['done'] ? 'img/checkedbox.png' : 'img/uncheckedbox.png'; ?>" alt="Checkbox">
                                    </label>
                                </form>
                            </td>
                        </tr>
                    <?php $i++;
                    } ?>
                </tbody>
            </table>
            <h3 style="text-align:center;">COUNT: <?= $i - 1; ?></h3>
            <hr style="border: 2px solid black;">
            <h3>Completed Tasks</h3>
            <table>
                <thead>
                    <tr style="border-bottom: 2px solid #ddd;">
                        <th>No.</th>
                        <th>Tasks</th>
                        <th style="display:flex;justify-content:flex-end;padding-right:60px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $completed_tasks = mysqli_query($db, "SELECT * FROM tasks WHERE username='$username' AND done=1");
                    $i = 1;
                    while ($row = mysqli_fetch_array($completed_tasks)) { ?>
                        <tr style="border-bottom: 2px solid #ddd;">
                            <td><?php echo $i; ?></td>
                            <td style="font-family: 'Lato', sans-serif; font-weight:bolder;" class="task"><?php echo $row['task']; ?></td>
                            <td style="text-align:right;" class="actions">
                                <a class="deletebawah" href="index.php?del_task=<?php echo $row['id'] ?>">Delete</a>
                                <a href="index.php?undone_task=<?php echo $row['id'] ?>">
                                    <img style="margin-top:5px;" src="img/checkedbox.png" alt="Undone" />
                                </a>
                            </td>
                        </tr>
                    <?php $i++;
                    } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>