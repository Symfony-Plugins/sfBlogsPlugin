<?php foreach ($pager->getResults() as $post): ?>
  <?php include_partial('sfBlogAdmin/post', array('post' => $post)) ?>
<?php endforeach; ?>
