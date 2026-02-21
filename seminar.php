<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: index.php");
    exit;
}

$seminar = [
    ["AI Conference", "20 Juni 2026", "Aula Kampus"],
    ["Web Development Workshop", "5 Juli 2026", "Lab Komputer"],
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Seminar</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">
    <h2>Data Seminar</h2>
    <table border="1">
        <tr>
            <th>Nama Seminar</th>
            <th>Tanggal</th>
            <th>Lokasi</th>
        </tr>

        <?php foreach($seminar as $s){ ?>
        <tr>
            <td><?= $s[0] ?></td>
            <td><?= $s[1] ?></td>
            <td><?= $s[2] ?></td>
        </tr>
        <?php } ?>
    </table>
</div>

</body>
</html>