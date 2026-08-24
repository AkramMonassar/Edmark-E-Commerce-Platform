<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/include/csrf.php';
require("connection/connection.php");

$id = (int) ($_GET['id'] ?? 0);

// معالجة الإضافة قبل أي output عشان الـ redirect يشتغل
if (isset($_POST['addtocart'])) {
    csrf_check();
    if (!isset($_SESSION['u_id'])) {
        header("Location: login.php");
        exit;
    }
    $uid = (int) $_SESSION['u_id'];
    $pid = (int) $_POST['id'];
    $res = mysqli_query($con_db, "SELECT * FROM product WHERE p_id = $pid");
    if ($res && ($row = mysqli_fetch_assoc($res))) {
        $check = mysqli_query($con_db, "SELECT * FROM cart WHERE id = $pid AND u_id = $uid");
        if ($check && mysqli_num_rows($check) == 0) {
            $name_esc = mysqli_real_escape_string($con_db, $row['p_name']);
            $img_esc  = mysqli_real_escape_string($con_db, $row['p_img']);
            $price    = (int) $row['p_price'];
            mysqli_query($con_db, "INSERT INTO cart (u_id, id, c_name, c_price, c_total, c_img) VALUES ($uid, $pid, '$name_esc', $price, $price, '$img_esc')");
        }
    }
}

include("include/header.php");

$result = mysqli_query($con_db, "SELECT * FROM product WHERE p_id = $id");
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
?>
    <center>
        <div id="details_page">
            <h2><?php echo htmlspecialchars($row['p_name'], ENT_QUOTES); ?></h2>
            <img id="img2" src="<?php echo htmlspecialchars($row['p_img'], ENT_QUOTES); ?>">
            <p><?php echo htmlspecialchars($row['p_describe'], ENT_QUOTES); ?></p>
            <label><?php echo (int)$row['p_price']; ?>$</label>
            <form method="post" action="details.php?id=<?php echo $id; ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="submit" name="addtocart" value="Add To Cart">
            </form>
        </div>
    </center>
<?php
} else {
    echo "<center><h3>المنتج غير موجود</h3></center>";
}
include("include/footer.php");
?>