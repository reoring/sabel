<?php if (!empty($errors)) : ?>
<div id="sbl_errmsg" class="sbl_error">
  <ul>
    <?php foreach ($errors as $error) : ?>
      <li><?php echo $error ?></li>
    <?php endforeach ?>
  </ul>
<?php else : ?>
<div id="sbl_errmsg" class="sbl_error" style="display: none;">
<?php endif ?>
</div>
