<?php use_helper('Date') ?>
<?php $link = $sf_data->getRaw('link') ?>
<h2><?php echo __('Archives') ?></h2>
<ul>
  <?php foreach($archives as $archive): ?>
  <li>
    <?php echo link_to(
      format_date(sfBlogTools::convertMonthToDate($archive->getColumn('month')), 'MMMM yyyy'), 
      $link . 'month=' . $archive->getColumn('month')
    ) ?>
    (<?php echo $archive->getColumn('count') ?>)
  </li>
  <?php endforeach; ?>
</ul>