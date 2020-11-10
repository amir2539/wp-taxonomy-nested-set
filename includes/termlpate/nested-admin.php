<?php
$current_page = admin_url( '/admin.php?page=nested-terms' );

?>

<div class="wrap">
    <form action="<?php echo $current_page; ?>" method="post" >

        <input type="hidden" name="neste_term_action" value="install-nested-term"/>
        <input type="submit" class="button-secondary" value="شروع نصب"/>
    </form>
</div>
