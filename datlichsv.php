<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

if (!isset($_SESSION['lich'])) {
    $_SESSION['lich'] = [];
}

$message = "";
$errors = [];

$ten = "";
$masv = "";
$giangvien = "";
$ngay = "";
$gio = "";
$noidung = "";

$dsGiangVien = [
    "Nguyễn Thị Lan - Tiếng Anh",
    "Trần Thị Hương - Tiếng Anh",
    "Lê Minh Anh - Tiếng Trung",
    "Phạm Thu Hà - Tiếng Nhật",
    "Nguyễn Hoàng Nam - Tiếng Hàn"
];

$dsGio = [
    "07:30",
    "08:00",
    "08:30",
    "09:00",
    "09:30",
    "10:00",
    "13:30",
    "14:00",
    "14:30",
    "15:00",
    "15:30",
    "16:00"
];

function e($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function chuanHoa($value)
{
    $value = trim($value);
    return preg_replace('/\s+/u', ' ', $value);
}

function coHTML($value)
{
    return preg_match('/<[^>]*>/u', $value);
}

function layTrangThai($ngay, $gio, $trangthai)
{
    if ($trangthai == "Đã hủy") {
        return "Đã hủy";
    }

    if ($trangthai == "Đã từ chối") {
        return "Đã từ chối";
    }

    $batDau = strtotime($ngay . " " . $gio);
    $ketThuc = $batDau + 30 * 60;
    $hienTai = time();

    if ($trangthai == "Chờ xác nhận") {
        if ($hienTai >= $ketThuc) {
            return "Đã hoàn thành";
        }

        return "Chờ xác nhận";
    }

    if ($trangthai == "Đã xác nhận") {
        if ($hienTai < $batDau) {
            return "Đã xác nhận";
        }

        if ($hienTai < $ketThuc) {
            return "Đang diễn ra";
        }

        return "Đã hoàn thành";
    }

    return $trangthai;
}

function kiemTraTrungLich($lich, $giangvien, $ngay, $gio)
{
    foreach ($lich as $item) {

        $trangthai = $item['trangthai'] ?? "";

        if (
            $trangthai == "Đã hủy" ||
            $trangthai == "Đã từ chối"
        ) {
            continue;
        }

        if (
            ($item['giangvien'] ?? "") == $giangvien &&
            ($item['ngay'] ?? "") == $ngay &&
            ($item['gio'] ?? "") == $gio
        ) {
            return true;
        }
    }

    return false;
}

/* =========================
   SINH VIÊN ĐẶT LỊCH
========================= */

if (isset($_POST['datlich'])) {

    $ten = chuanHoa($_POST['ten'] ?? "");
    $masv = chuanHoa($_POST['masv'] ?? "");
    $giangvien = chuanHoa($_POST['giangvien'] ?? "");
    $ngay = chuanHoa($_POST['ngay'] ?? "");
    $gio = chuanHoa($_POST['gio'] ?? "");
    $noidung = chuanHoa($_POST['noidung'] ?? "");

    if ($ten == "") {

        $errors['ten'] = "Vui lòng nhập họ và tên.";

    } elseif (
        mb_strlen($ten) < 2 ||
        mb_strlen($ten) > 50
    ) {

        $errors['ten'] = "Họ và tên phải từ 2 đến 50 ký tự.";

    } elseif (
        !preg_match('/^[\p{L}\s]+$/u', $ten)
    ) {

        $errors['ten'] = "Họ và tên chỉ được chứa chữ cái.";

    } elseif (coHTML($ten)) {

        $errors['ten'] = "Họ và tên không được chứa HTML.";

    }

    if ($masv == "") {

        $errors['masv'] = "Vui lòng nhập mã sinh viên.";

    } elseif (!preg_match('/^[0-9]+$/', $masv)) {

        $errors['masv'] = "Mã sinh viên chỉ được chứa chữ số.";

    } elseif (
        strlen($masv) < 5 ||
        strlen($masv) > 20
    ) {

        $errors['masv'] = "Mã sinh viên phải từ 5 đến 20 chữ số.";

    }

    if ($giangvien == "") {

        $errors['giangvien'] = "Vui lòng chọn giảng viên.";

    } elseif (
        !in_array($giangvien, $dsGiangVien, true)
    ) {

        $errors['giangvien'] = "Giảng viên không hợp lệ.";

    }

    if ($ngay == "") {

        $errors['ngay'] = "Vui lòng chọn ngày.";

    } else {

        $date = DateTime::createFromFormat(
            'Y-m-d',
            $ngay
        );

        if (
            !$date ||
            $date->format('Y-m-d') !== $ngay
        ) {

            $errors['ngay'] = "Ngày không hợp lệ.";

        } elseif ($ngay < date("Y-m-d")) {

            $errors['ngay'] = "Không thể chọn ngày đã qua.";

        }
    }

    if ($gio == "") {

        $errors['gio'] = "Vui lòng chọn giờ.";

    } elseif (
        !in_array($gio, $dsGio, true)
    ) {

        $errors['gio'] = "Giờ không hợp lệ.";

    }

    if ($noidung == "") {

        $errors['noidung'] = "Vui lòng nhập nội dung.";

    } elseif (
        mb_strlen($noidung) < 5 ||
        mb_strlen($noidung) > 500
    ) {

        $errors['noidung'] =
            "Nội dung phải từ 5 đến 500 ký tự.";

    } elseif (coHTML($noidung)) {

        $errors['noidung'] =
            "Nội dung không được chứa HTML.";
    }

    if (empty($errors)) {

        $thoiGian = strtotime(
            $ngay . " " . $gio
        );

        if ($thoiGian === false) {

            $errors['ngay'] =
                "Ngày hoặc giờ không hợp lệ.";

        } elseif ($thoiGian <= time()) {

            $errors['gio'] =
                "Không thể đặt lịch vào thời gian đã qua.";

        } elseif (
            kiemTraTrungLich(
                $_SESSION['lich'],
                $giangvien,
                $ngay,
                $gio
            )
        ) {

            $errors['gio'] =
                "Giảng viên đã có lịch vào thời gian này.";
        }
    }

    if (empty($errors)) {

        $_SESSION['lich'][] = [
            "ten" => $ten,
            "masv" => $masv,
            "giangvien" => $giangvien,
            "ngay" => $ngay,
            "gio" => $gio,
            "noidung" => $noidung,
            "trangthai" => "Chờ xác nhận"
        ];

        $message =
            "Đặt lịch thành công. Vui lòng chờ giảng viên xác nhận.";

        $ten = "";
        $masv = "";
        $giangvien = "";
        $ngay = "";
        $gio = "";
        $noidung = "";
    }
}

/* =========================
   GIẢNG VIÊN XÁC NHẬN
========================= */

if (isset($_POST['xacnhanlich'])) {

    $id = intval($_POST['id'] ?? -1);

    if (isset($_SESSION['lich'][$id])) {

        if (
            $_SESSION['lich'][$id]['trangthai']
            == "Chờ xác nhận"
        ) {

            $_SESSION['lich'][$id]['trangthai']
                = "Đã xác nhận";

            $message =
                "Đã xác nhận lịch hẹn.";
        }
    }
}

/* =========================
   GIẢNG VIÊN TỪ CHỐI
========================= */

if (isset($_POST['tuchoi'])) {

    $id = intval($_POST['id'] ?? -1);

    if (isset($_SESSION['lich'][$id])) {

        if (
            $_SESSION['lich'][$id]['trangthai']
            == "Chờ xác nhận"
        ) {

            $_SESSION['lich'][$id]['trangthai']
                = "Đã từ chối";

            $message =
                "Đã từ chối lịch hẹn.";
        }
    }
}

/* =========================
   SINH VIÊN HỦY LỊCH
   CHỈ ĐƯỢC HỦY KHI
   CHỜ XÁC NHẬN
========================= */

if (isset($_POST['huylich'])) {

    $id = intval($_POST['id'] ?? -1);

    if (isset($_SESSION['lich'][$id])) {

        $lich = $_SESSION['lich'][$id];

        $trangthai =
            $lich['trangthai'] ?? "";

        $thoiGian = strtotime(
            $lich['ngay'] . " " . $lich['gio']
        );

        if ($trangthai == "Chờ xác nhận") {

            if ($thoiGian > time()) {

                $_SESSION['lich'][$id]['trangthai']
                    = "Đã hủy";

                $message =
                    "Đã hủy lịch hẹn.";

            } else {

                $message =
                    "Không thể hủy lịch đã qua.";
            }

        } elseif ($trangthai == "Đã xác nhận") {

            $message =
                "Lịch đã được giảng viên xác nhận nên không thể hủy.";

        } elseif ($trangthai == "Đã từ chối") {

            $message =
                "Lịch đã bị từ chối nên không thể hủy.";

        } elseif ($trangthai == "Đã hủy") {

            $message =
                "Lịch này đã được hủy.";

        } else {

            $message =
                "Không thể hủy lịch này.";
        }
    }
}

$homNay = date("Y-m-d");

$giangVienDangChon =
    $_GET['gv'] ?? "";

if (
    $giangVienDangChon != "" &&
    !in_array(
        $giangVienDangChon,
        $dsGiangVien,
        true
    )
) {

    $giangVienDangChon = "";
}

?>

<!DOCTYPE html>

<html lang="vi">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Hệ thống đặt lịch tư vấn</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 30px;
    font-family: Arial, sans-serif;
    background: #fff4f7;
    color: #444;
}

.container {
    width: 1100px;
    max-width: 100%;
    margin: auto;
}

h1 {
    text-align: center;
    color: #c05278;
    margin-bottom: 30px;
}

.box {
    background: white;
    border: 1px solid #f0ccd8;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 4px 12px rgba(192, 82, 120, 0.08);
}

h2 {
    color: #c05278;
    margin-top: 0;
    padding-bottom: 12px;
    border-bottom: 2px solid #f5d8e1;
}

h3 {
    color: #a84467;
}

label {
    display: block;
    margin-top: 15px;
    margin-bottom: 6px;
    font-weight: bold;
}

input,
select,
textarea {
    width: 100%;
    padding: 11px;
    border: 1px solid #e3bdca;
    border-radius: 7px;
    background: #fffafb;
    font-size: 14px;
}

input:focus,
select:focus,
textarea:focus {
    outline: none;
    border-color: #d56f91;
}

textarea {
    height: 90px;
    resize: vertical;
}

.datlich {
    width: 100%;
    padding: 12px;
    margin-top: 20px;
    border: none;
    border-radius: 7px;
    background: #d56f91;
    color: white;
    font-size: 15px;
    cursor: pointer;
}

.datlich:hover {
    background: #c4577c;
}

.message {
    padding: 13px;
    margin-bottom: 20px;
    border-radius: 7px;
    background: #fff0f4;
    border: 1px solid #efc5d2;
    color: #a84467;
    text-align: center;
    font-weight: bold;
}

.error {
    color: #d33;
    font-size: 13px;
    margin-top: 5px;
}

.note {
    color: #999;
    font-size: 13px;
    margin-top: 6px;
}

.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

th {
    background: #f7dce5;
    color: #8f405d;
    padding: 10px;
    border: 1px solid #edc5d2;
}

td {
    padding: 10px;
    border: 1px solid #efd7df;
    text-align: center;
}

tr:nth-child(even) {
    background: #fff9fb;
}

tr:hover {
    background: #fff0f4;
}

.chua-dien-ra,
.dang-dien-ra,
.da-hoan-thanh,
.da-huy,
.da-xac-nhan,
.da-tu-choi {
    display: inline-block;
    padding: 5px 9px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}

.chua-dien-ra {
    background: #fff0c7;
    color: #916c00;
}

.da-xac-nhan {
    background: #d8f3e4;
    color: #217044;
}

.dang-dien-ra {
    background: #d8f3e4;
    color: #217044;
}

.da-hoan-thanh {
    background: #dcecff;
    color: #376a9b;
}

.da-huy {
    background: #eee;
    color: #888;
}

.da-tu-choi {
    background: #ffe0e0;
    color: #b33a3a;
}

.huy {
    padding: 7px 12px;
    border: 1px solid #d58aa3;
    border-radius: 5px;
    background: white;
    color: #bd5278;
    cursor: pointer;
}

.huy:hover {
    background: #d56f91;
    color: white;
}

.xacnhan {
    padding: 7px 10px;
    border: none;
    border-radius: 5px;
    background: #67b77a;
    color: white;
    cursor: pointer;
    margin: 2px;
}

.xacnhan:hover {
    background: #4c9d60;
}

.tuchoi {
    padding: 7px 10px;
    border: none;
    border-radius: 5px;
    background: #d56f91;
    color: white;
    cursor: pointer;
    margin: 2px;
}

.tuchoi:hover {
    background: #bd5278;
}

.teacher-box {
    border: 2px solid #e8c0ce;
}

.teacher-title {
    color: #a84467;
}

.khong-co {
    text-align: center;
    padding: 20px;
    color: #999;
}

.teacher-select {
    max-width: 500px;
}

@media (max-width: 600px) {

    body {
        padding: 15px;
    }

    .box {
        padding: 18px;
    }

    h1 {
        font-size: 24px;
    }

    th,
    td {
        font-size: 12px;
        padding: 7px;
    }

    .teacher-select {
        max-width: 100%;
    }
}

</style>

</head>

<body>

<div class="container">

<h1>
    Hệ thống đặt lịch tư vấn
</h1>

<?php if ($message != "") { ?>

<div class="message">
    <?= e($message) ?>
</div>

<?php } ?>


<!-- =========================
     FORM SINH VIÊN
========================= -->

<div class="box">

<h2>
    Đặt lịch tư vấn
</h2>

<form method="POST">

<label>
    Họ và tên sinh viên
</label>

<input
    type="text"
    name="ten"
    placeholder="Nhập họ và tên"
    value="<?= e($ten) ?>"
    required
>

<?php if (isset($errors['ten'])) { ?>

<div class="error">
    <?= e($errors['ten']) ?>
</div>

<?php } ?>


<label>
    Mã sinh viên
</label>

<input
    type="text"
    name="masv"
    placeholder="Nhập mã sinh viên"
    value="<?= e($masv) ?>"
    required
>

<?php if (isset($errors['masv'])) { ?>

<div class="error">
    <?= e($errors['masv']) ?>
</div>

<?php } ?>


<label>
    Giảng viên Ngoại ngữ
</label>

<select name="giangvien" required>

<option value="">
    -- Chọn giảng viên --
</option>

<?php foreach ($dsGiangVien as $gv) { ?>

<option
    value="<?= e($gv) ?>"
    <?= $giangvien == $gv ? 'selected' : '' ?>
>
    <?= e($gv) ?>
</option>

<?php } ?>

</select>

<?php if (isset($errors['giangvien'])) { ?>

<div class="error">
    <?= e($errors['giangvien']) ?>
</div>

<?php } ?>


<label>
    Ngày hẹn
</label>

<input
    type="date"
    name="ngay"
    id="ngay"
    min="<?= $homNay ?>"
    value="<?= e($ngay) ?>"
    required
>

<?php if (isset($errors['ngay'])) { ?>

<div class="error">
    <?= e($errors['ngay']) ?>
</div>

<?php } ?>

<div class="note">
    Không thể chọn ngày đã qua.
</div>


<label>
    Giờ hẹn
</label>

<select name="gio" id="gio" required>

<option value="">
    -- Chọn giờ --
</option>

<?php foreach ($dsGio as $g) { ?>

<option
    value="<?= $g ?>"
    <?= $gio == $g ? 'selected' : '' ?>
>
    <?= $g ?>
</option>

<?php } ?>

</select>

<?php if (isset($errors['gio'])) { ?>

<div class="error">
    <?= e($errors['gio']) ?>
</div>

<?php } ?>

<div class="note">
    Nếu chọn hôm nay, các giờ đã qua sẽ tự động bị khóa.
</div>


<label>
    Nội dung cần tư vấn
</label>

<textarea
    name="noidung"
    placeholder="Nhập nội dung cần tư vấn..."
    required
><?= e($noidung) ?></textarea>

<?php if (isset($errors['noidung'])) { ?>

<div class="error">
    <?= e($errors['noidung']) ?>
</div>

<?php } ?>


<button
    type="submit"
    name="datlich"
    class="datlich"
>
    Đặt lịch
</button>

</form>

</div>


<!-- =========================
     LỊCH SINH VIÊN
========================= -->

<div class="box">

<h2>
    Lịch của sinh viên
</h2>

<?php if (empty($_SESSION['lich'])) { ?>

<p class="khong-co">
    Chưa có lịch hẹn nào.
</p>

<?php } else { ?>

<div class="table-wrapper">

<table>

<tr>

<th>STT</th>
<th>Giảng viên</th>
<th>Ngày</th>
<th>Giờ</th>
<th>Nội dung</th>
<th>Trạng thái</th>
<th>Thao tác</th>

</tr>

<?php

$stt = 1;

foreach ($_SESSION['lich'] as $id => $item) {

    $trangthai = layTrangThai(
        $item['ngay'],
        $item['gio'],
        $item['trangthai'] ?? ""
    );

?>

<tr>

<td>
    <?= $stt ?>
</td>

<td>
    <?= e($item['giangvien']) ?>
</td>

<td>
    <?= date(
        "d/m/Y",
        strtotime($item['ngay'])
    ) ?>
</td>

<td>
    <?= e($item['gio']) ?>
</td>

<td>
    <?= e($item['noidung']) ?>
</td>

<td>

<?php if ($trangthai == "Chờ xác nhận") { ?>

<span class="chua-dien-ra">
    Chờ xác nhận
</span>

<?php } elseif ($trangthai == "Đã xác nhận") { ?>

<span class="da-xac-nhan">
    Đã xác nhận
</span>

<?php } elseif ($trangthai == "Đang diễn ra") { ?>

<span class="dang-dien-ra">
    Đang diễn ra
</span>

<?php } elseif ($trangthai == "Đã hoàn thành") { ?>

<span class="da-hoan-thanh">
    Đã hoàn thành
</span>

<?php } elseif ($trangthai == "Đã từ chối") { ?>

<span class="da-tu-choi">
    Đã từ chối
</span>

<?php } elseif ($trangthai == "Đã hủy") { ?>

<span class="da-huy">
    Đã hủy
</span>

<?php } ?>

</td>

<td>

<?php if ($trangthai == "Chờ xác nhận") { ?>

<form
    method="POST"
    onsubmit="return confirm('Bạn có chắc muốn hủy lịch này không?');"
>

<input
    type="hidden"
    name="id"
    value="<?= $id ?>"
>

<button
    type="submit"
    name="huylich"
    class="huy"
>
    Hủy
</button>

</form>

<?php } elseif ($trangthai == "Đã xác nhận") { ?>

<span style="color:#217044;font-weight:bold;">
    Không thể hủy
</span>

<?php } else { ?>

-

<?php } ?>

</td>

</tr>

<?php

$stt++;

}

?>

</table>

</div>

<?php } ?>

</div>


<!-- =========================
     KHU VỰC GIẢNG VIÊN
========================= -->

<div class="box teacher-box">

<h2 class="teacher-title">
    Khu vực giảng viên
</h2>

<form method="GET">

<label>
    Chọn giảng viên
</label>

<select
    name="gv"
    class="teacher-select"
    onchange="this.form.submit()"
>

<option value="">
    -- Chọn giảng viên --
</option>

<?php foreach ($dsGiangVien as $gv) { ?>

<option
    value="<?= e($gv) ?>"
    <?= $giangVienDangChon == $gv
        ? 'selected'
        : '' ?>
>
    <?= e($gv) ?>
</option>

<?php } ?>

</select>

</form>


<?php if ($giangVienDangChon != "") { ?>

<h3>
    Lịch của:
    <?= e($giangVienDangChon) ?>
</h3>

<div class="table-wrapper">

<table>

<tr>

<th>STT</th>
<th>Sinh viên</th>
<th>Mã SV</th>
<th>Ngày</th>
<th>Giờ</th>
<th>Nội dung</th>
<th>Trạng thái</th>
<th>Thao tác</th>

</tr>

<?php

$sttGV = 1;
$coLich = false;

foreach ($_SESSION['lich'] as $id => $item) {

    if (
        $item['giangvien']
        != $giangVienDangChon
    ) {
        continue;
    }

    $coLich = true;

    $trangthai = layTrangThai(
        $item['ngay'],
        $item['gio'],
        $item['trangthai'] ?? ""
    );

?>

<tr>

<td>
    <?= $sttGV ?>
</td>

<td>
    <?= e($item['ten']) ?>
</td>

<td>
    <?= e($item['masv']) ?>
</td>

<td>
    <?= date(
        "d/m/Y",
        strtotime($item['ngay'])
    ) ?>
</td>

<td>
    <?= e($item['gio']) ?>
</td>

<td>
    <?= e($item['noidung']) ?>
</td>

<td>

<?php if ($trangthai == "Chờ xác nhận") { ?>

<span class="chua-dien-ra">
    Chờ xác nhận
</span>

<?php } elseif ($trangthai == "Đã xác nhận") { ?>

<span class="da-xac-nhan">
    Đã xác nhận
</span>

<?php } elseif ($trangthai == "Đang diễn ra") { ?>

<span class="dang-dien-ra">
    Đang diễn ra
</span>

<?php } elseif ($trangthai == "Đã hoàn thành") { ?>

<span class="da-hoan-thanh">
    Đã hoàn thành
</span>

<?php } elseif ($trangthai == "Đã từ chối") { ?>

<span class="da-tu-choi">
    Đã từ chối
</span>

<?php } elseif ($trangthai == "Đã hủy") { ?>

<span class="da-huy">
    Đã hủy
</span>

<?php } ?>

</td>

<td>

<?php if ($trangthai == "Chờ xác nhận") { ?>

<form
    method="POST"
    style="display:inline;"
>

<input
    type="hidden"
    name="id"
    value="<?= $id ?>"
>

<button
    type="submit"
    name="xacnhanlich"
    class="xacnhan"
>
    Xác nhận
</button>

</form>

<form
    method="POST"
    style="display:inline;"
    onsubmit="return confirm('Bạn có chắc muốn từ chối lịch này không?');"
>

<input
    type="hidden"
    name="id"
    value="<?= $id ?>"
>

<button
    type="submit"
    name="tuchoi"
    class="tuchoi"
>
    Từ chối
</button>

</form>

<?php } else { ?>

-

<?php } ?>

</td>

</tr>

<?php

$sttGV++;

}

if (!$coLich) {

?>

<tr>

<td
    colspan="8"
    class="khong-co"
>
    Giảng viên chưa có lịch hẹn nào.
</td>

</tr>

<?php } ?>

</table>

</div>

<?php } else { ?>

<p class="khong-co">
    Vui lòng chọn giảng viên để xem lịch hẹn.
</p>

<?php } ?>

</div>

</div>


<script>

const ngayInput =
    document.getElementById("ngay");

const gioSelect =
    document.getElementById("gio");

function capNhatGio() {

    const ngayChon =
        ngayInput.value;

    const now =
        new Date();

    const nam =
        now.getFullYear();

    const thang =
        String(
            now.getMonth() + 1
        ).padStart(2, "0");

    const ngay =
        String(
            now.getDate()
        ).padStart(2, "0");

    const ngayHienTai =
        nam + "-" +
        thang + "-" +
        ngay;

    for (
        let option of gioSelect.options
    ) {

        if (!option.value) {
            continue;
        }

        if (
            ngayChon === ngayHienTai
        ) {

            const parts =
                option.value.split(":");

            const gio =
                parseInt(parts[0]);

            const phut =
                parseInt(parts[1]);

            const thoiGian =
                new Date();

            thoiGian.setHours(
                gio,
                phut,
                0,
                0
            );

            option.disabled =
                thoiGian <= now;

        } else {

            option.disabled = false;
        }
    }

    if (
        gioSelect.value &&
        gioSelect.selectedOptions[0].disabled
    ) {

        gioSelect.value = "";
    }
}

ngayInput.addEventListener(
    "change",
    capNhatGio
);

capNhatGio();

setInterval(
    capNhatGio,
    30000
);

</script>

</body>

</html>