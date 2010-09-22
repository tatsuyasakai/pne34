<?php
if (count($pager))
{
  include_parts(
    'AshiatoLatestListBox',
    'ashiatoHomeList',
    array(
      'title' => "最新のあしあと",
      'pager' => $pager,
      'moreInfo' => 'ashiato/list'
    )
  );
}
?>
