<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("include/header.php");
?>
<div id="mov_picture">
    <div id="wowslider-container1">
        <div class="ws_images">
            <ul>
                <li><img src="photo/2.png" alt="1" title="1" id="wows1_0" /></li>
                <li><img src="photo/1.png" alt="2" title="2" id="wows1_1" /></li>
                <li><img src="photo/3.png" alt="3" title="3" id="wows1_2" /></li>
                <li><img src="photo/4.png" alt="4" title="4" id="wows1_3" /></li>
                <li><img src="photo/5.png" alt="5" title="5" id="wows1_4" /></li>
                <li><img src="photo/6.png" alt="6" title="6" id="wows1_5" /></li>
                <li><img src="photo/10.png" alt="7" title="7" id="wows1_6" /></li>
            </ul>
        </div>
        <div class="ws_bullets">
            <div>
                <a href="#" title="1"><span><img src="photo/2.png" alt="1" />1</span></a>
                <a href="#" title="2"><span><img src="photo/1.png" alt="2" />2</span></a>
                <a href="#" title="3"><span><img src="photo/3.png" alt="3" />3</span></a>
                <a href="#" title="4"><span><img src="photo/4.png" alt="4" />4</span></a>
                <a href="#" title="5"><span><img src="photo/5.png" alt="5" />5</span></a>
                <a href="#" title="6"><span><img src="photo/6.png" alt="6" />6</span></a>
                <a href="#" title="7"><span><img src="photo/10.png" alt="7" />7</span></a>
            </div>
        </div>
        <div class="ws_shadow"></div>
    </div>
    <script type="text/javascript" src="engine1/wowslider.js"></script>
    <script type="text/javascript" src="engine1/script.js"></script>
</div>
<center>
    <?php
    require('connection/connection.php');

    $query  = "SELECT * FROM product";
    $result = mysqli_query($con_db, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        while ($show_data = mysqli_fetch_assoc($result)) {
            echo "
        <div id='det'>
            <div id='details'>
                <h4 style='background:gray'>" . htmlspecialchars($show_data['p_name'], ENT_QUOTES) . "</h4>
                <a href='details.php?id=" . (int)$show_data['p_id'] . "'><img id='img2' src='" . htmlspecialchars($show_data['p_img'], ENT_QUOTES) . "'></a>";
            if (!isset($_SESSION['user']) && !isset($_SESSION['u_email'])) {
                echo "<form action='login.php' method='post'>";
            } else {
                echo "<form action='index.php' method='post'>";
            }
            echo "
                <input type='hidden' name='csrf_token' value='" . csrf_token() . "'/>
                <input type='hidden' name='id' value='" . $show_data['p_id'] . "'/>
                <input type='submit' name='addtocart' value='Add To Cart'/>
            </form>
            <label>" . (int)$show_data['p_price'] . "$</label>
            </div>
        </div>";
        }
    }

    if (isset($_POST['addtocart'])) {
        csrf_check();
        if (!isset($_SESSION['u_id'])) {
            header("Location: login.php");
            exit;
        }
        $uid = (int) $_SESSION['u_id'];
        $id  = (int) $_POST['id'];

        $res = mysqli_query($con_db, "SELECT * FROM product WHERE p_id = $id");
        if ($res && ($row = mysqli_fetch_assoc($res))) {
            $check = mysqli_query($con_db, "SELECT * FROM cart WHERE id = $id AND u_id = $uid");
            if ($check && mysqli_num_rows($check) == 0) {
                $name_esc = mysqli_real_escape_string($con_db, $row['p_name']);
                $img_esc  = mysqli_real_escape_string($con_db, $row['p_img']);
                $price    = (int) $row['p_price'];
                if (!mysqli_query($con_db, "INSERT INTO cart (u_id, id, c_name, c_price, c_total, c_img) VALUES ($uid, $id, '$name_esc', $price, $price, '$img_esc')")) {
                    printf('Errormessage2: %s', mysqli_error($con_db));
                }
            }
        }
    }
    ?>
</center>
<?php
include("include/footer.php");
?>