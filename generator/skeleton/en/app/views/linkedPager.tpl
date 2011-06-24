<?php if ($paginator) : ?>
  <?php if ($paginator->hasPrev() || $paginator->hasNext()) : ?>
    <div class="sbl_pager">
      <?php if ($paginator->hasPrev()) : ?>
        <?php echo $paginator->prev("<< Prev", array("class" => "prev")) ?>
      <?php endif ?>
      <?php if ($paginator->hasNext()) : ?>
        <?php echo $paginator->next("Next >>", array("class" => "next")) ?>
      <?php endif ?>
    </div>
  <?php endif ?>
<?php endif ?>