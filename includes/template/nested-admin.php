<?php
$current_page = admin_url( '/admin.php?page=nested-terms' );

?>

<div class="wrap">
    <form action="<?php echo $current_page; ?>" method="post" >
        <p>Click "Begin Install" for creating table and inserting terms in "nested-set" algorithm</p>
        <input type="hidden" name="nested_term_action" value="install-nested-term"/>
        <input type="submit" class="button button-primary" value="Begin Install"/>
    </form>

    <br>
    <form action="<?php echo $current_page; ?>" method="post" >
        <p>For Re-Generating the tree and fix it click in "Re-Generate"</p>
        <input type="hidden" name="nested_term_action" value="fixtree-nested-term"/>
        <input type="submit" class="button button-primary" value="Re-Generate"/>
    </form>
</div>
