<?php 

class sfBlogCommentLogger
{
  public static function create($comment, $author)
  {
    foreach ($comment->getInterestedAuthors() as $user)
    {
      $post = $comment->getsfBlogPost();
      $prefix = $comment->isAccepted() ? '' : '[Please moderate] ';
      $log = new sfBlogLog();
      $log->setElements($user, $author, 'create_comment', $comment, $prefix . 'User comment: %subject% said "%object%" on "%complement%"', null, $post);
      $log->setObjectLink('sfBlogAdmin/comments?filter=filter&filters[parent_id]=post_'.$post->getId());
      $log->setComplementLink('sfBlogAdmin/postEdit?id='.$post->getId());
      $log->save();
    }
  }
  
  public static function approve($comment)
  {
    $logs = DbFinder::from('sfBlogLog')->
      where('Verb', 'create_comment')->
      where('ObjectClass', 'sfBlogComment')->
      where('ObjectId', $comment->getId())->
      find();
    foreach ($logs as $log)
    {
      // remove the '[Please moderate]' prefix
      $log->setMessage('User comment: %subject% said "%object%" on "%complement%"');
      $log->save();
    }
  }
  
  public static function remove($comment)
  {
    $logs = DbFinder::from('sfBlogLog')->
      where('Verb', 'create_comment')->
      where('ObjectClass', 'sfBlogComment')->
      where('ObjectId', $comment->getId())->
      find();
    foreach ($logs as $log)
    {
      // remove the log message
      $log->delete();
    }
  }

}