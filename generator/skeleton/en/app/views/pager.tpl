<div class="sbl_pager">
<?php if ($paginator->count > $paginator->limit) : ?>
  <a class="prev" href="<?php echo uri($paginator->uri) ?>?<?php echo $paginator->getUriQuery($paginator->viewer->getFirst()) ?>">&lt;&lt;</a>
  
  <?php foreach ($paginator->viewer as $v) : ?>
    <?php if ($v->isCurrent()) : ?>
      <span><?php echo $v->getCurrent() ?></span>
    <?php else : ?>
      <?php echo a($paginator->uri, $v->getCurrent(), $paginator->getUriQuery($v->getCurrent())) ?>
    <?php endif ?>
  <?php endforeach ?>
  
  <a class="next" href="<?php echo uri($paginator->uri) ?>?<?php echo $paginator->getUriQuery($paginator->viewer->getLast()) ?>">&gt;&gt;</a>
<?php endif ?>
</div>
