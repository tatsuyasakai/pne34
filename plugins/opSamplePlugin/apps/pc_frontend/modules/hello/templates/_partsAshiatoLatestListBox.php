<div id="<?php echo $id ?>" class="dparts homeRecentList">
<div class="parts">
 
<div class="partsHeading">
<h3><?php echo $options['title'] ?></h3>
</div>
 
<div class="block">
 
<table>
<tbody>
<ul class="articleList">
    <?php foreach ($options['pager']->getResults() as $ashiato) : ?>
    <?php $member = $ashiato->getMemberRelatedByMemberIdFrom(); ?>
    <li><?php echo $ashiato->getUpdatedAt(); ?><?php echo link_to($member->getName(), 'member/profile?id=' . $member->getId()); ?></li>
    <?php endforeach; ?>
</ul>
 
<?php if (isset($options['moreInfo'])): ?>
<div class="moreInfo"><ul class="moreInfo"><li>
<?php echo link_to(__('More info'), $options['moreInfo']) ?>
</li></ul></div>
<?php endif; ?>
 
</tbody>
</table>
 
</div>
 
</div></div>
