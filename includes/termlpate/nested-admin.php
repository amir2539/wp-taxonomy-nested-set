<?php
$current_page = admin_url( '/admin.php?page=nested-terms' );

?>

<div class="wrap">
    <form action="<?php echo $current_page; ?>" method="post" >
        <p>برای شروع نصب و وارد کردن term ها در قالب جدید رو گزینه "شروع نصب کلیگ کنید. ممکن است تا یک ساعت طول بکشد.</p>
        <input type="hidden" name="neste_term_action" value="install-nested-term"/>
        <input type="submit" class="button button-primary" value="شروع نصب"/>
    </form>

    <br>
    <form action="<?php echo $current_page; ?>" method="post" >
        <p>برای شروع تصحیح درخت روی گزینه زیر کلیک کینید.</p>
        <input type="hidden" name="neste_term_action" value="fixtree-nested-term"/>
        <input type="submit" class="button button-primary" value="شروع تصحیح"/>
    </form>
</div>
