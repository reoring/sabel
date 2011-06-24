<h2>5xx Server Error</h2>

<p>
  サーバーでエラーが発生し、処理は中断されました。
</p>

<? if (isset($exception_message)) : ?>
<div style="border: solid 2px #f00; margin: 10px; padding: 10px;">
  <?php echo $exception_message ?>
</div>
<? endif ?>
