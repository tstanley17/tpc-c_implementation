<?php
    require "header.php";
?>

<main>
    <div class="wrapperMain">
        <section class="sectionDefault">
            <?php
                if(!isset($_GET['OL_I_ID'])) {
                    echo '<p class="inputStatus">Order Not Successful!</p>';
                }
                else if(isset($_GET['OL_I_ID'])) {
                    echo '<p class="inputStatus">Order Successful!</p>';
                }
            ?>
        </section>
    </div>
</main>
