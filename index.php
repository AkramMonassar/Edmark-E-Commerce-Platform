<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("include/header.php");
?>
<!-- part of movement picture -->
<div id="mov_picture">
<!-- Start WOWSlider.com BODY section -->
<div id="wowslider-container1">
    <div class="ws_images">
        <ul>
            <li><img src="photo/2.png" alt="1" title="1" id="wows1_0"/></li>
            <li><img src="photo/1.png" alt="2" title="2" id="wows1_1"/></li>
            <li><img src="photo/3.png" alt="3" title="3" id="wows1_2"/></li>
            <li><img src="photo/4.png" alt="4" title="4" id="wows1_3"/></li>
            <li><img src="photo/5.png" alt="5" title="5" id="wows1_4"/></li>
            <li><img src="photo/6.png" alt="6" title="6" id="wows1_5"/></li>
            <li><img src="photo/10.png" alt="7" title="7" id="wows1_6"/></li>
        </ul>
    </div>
    <div class="ws_bullets">
        <div>
            <a href="#" title="1"><span><img src="photo/2.png" alt="1"/>1</span></a>
            <a href="#" title="2"><span><img src="photo/1.png" alt="2"/>2</span></a>
            <a href="#" title="3"><span><img src="photo/3.png" alt="3"/>3</span></a>
            <a href="#" title="4"><span><img src="photo/4.png" alt="4"/>4</span></a>
            <a href="#" title="5"><span><img src="photo/5.png" alt="5"/>5</span></a>
            <a href="#" title="6"><span><img src="photo/6.png" alt="6"/>6</span></a>
            <a href="#" title="7"><span><img src="photo/10.png" alt="7"/>7</span></a>
        </div>
    </div>
    <div class="ws_script" style="position:absolute;left:-99%"><a href="http://wowslider.com/vi">css slider</a> by WOWSlider.com v7.8</div>
    <div class="ws_shadow"></div>
</div>
<script type="text/javascript" src="engine1/wowslider.js"></script>
<script type="text/javascript" src="engine1/script.js"></script>
<!-- End WOWSlider.com BODY section -->
</div>

<center>
<?php
require('connection/connection.php');

// ===== عرض المنتجات =====
$query  = "SELECT * FROM product";
$result = mysqli_query($con_db, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($show_data = mysqli_fetch_assoc($result)) {
        echo "
        <div id='det'>
            <div id='details'>
                <h4 style='background:gray'>" . htmlspecialchars($show_data['p_name'], ENT_QUOTES) . "</h4>
                <a href='details.php?id=" . $show_data['p_id'] . "'><img id='img2' src='" . $show_data['p_img'] . "'></a>";

        if (!isset($_SESSION['user']) && !isset($_SESSION['u_email'])) {
            echo "<form action='login.php' method='post'>";
        } else {
            echo "<form action='index.php' method='post'>";
        }

        echo "
                <input type='hidden' name='id' value='" . $show_data['p_id'] . "'/>
                <input type='submit' name='addtocart' value='Add To Cart'/>
            </form>
            <label>" . $show_data['p_price'] . "$</label>
            </div>
        </div>";
    }
}

// ===== إضافة منتج للسلة =====
if (isset($_POST['addtocart'])) {
    $id = (int) $_POST['id'];

    $sql    = "SELECT * FROM product WHERE p_id = $id";
    $result = mysqli_query($con_db, $sql);

    if (!$result) {
        printf('Errormessage1: %s', mysqli_error($con_db));
    } else {
        $row = mysqli_fetch_assoc($result);

        if ($row) {
            $name  = $row['p_name'];
            $price = (int) $row['p_price'];
            $img   = $row['p_img'];

            // إضافة المنتج للسلة مرة وحدة فقط
            $sql0    = "SELECT * FROM cart WHERE id = $id";
            $result0 = mysqli_query($con_db, $sql0);

            if ($result0 && mysqli_num_rows($result0) == 0) {
                $name_esc = mysqli_real_escape_string($con_db, $name);
                $img_esc  = mysqli_real_escape_string($con_db, $img);

                $sql1 = "INSERT INTO cart (id, c_name, c_price, c_total, c_img)
                         VALUES ($id, '$name_esc', $price, $price, '$img_esc')";

                $result1 = mysqli_query($con_db, $sql1);
                if (!$result1) {
                    printf('Errormessage2: %s', mysqli_error($con_db));
                }
            }
        }
    }
}
?>
</center>

<?php
include("include/footer.php");
?>