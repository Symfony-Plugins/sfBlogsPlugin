<?php

class PluginsfBlogCommentFinder extends Dbfinder
{
  protected $class = 'sfBlogComment';
  
  public function recent()
  {
    return $this->
      orderBy('CreatedAt', 'desc');
  }
  
  public function published()
  {
    // FIXME: rely on the blog's comment publication policy
    return $this->
      where('Status', '<=', sfConfig::get('app_sfBlogs_moderation_treshold', sfBlogComment::ACCEPTED));
  }
  
  public function spam()
  {
    // FIXME: rely on the blog's comment publication policy
    return $this->
      where('Status', '>=', sfConfig::get('app_sfBlogs_spam_treshold', sfBlogComment::DUBIOUS));
  }
  
  public function pending()
  {
    return $this->
      where('Status', sfBlogComment::PENDING);
  }
  
  public function relatedToBlog($blog)
  {
    return $this->
      join('sfBlogPost')->
      where('sfBlogPost.SfBlogId', $blog->getId());
  }
  
  public function managedBy($user)
  {
    return $this->
      _if(!$user->hasCredential('administrator'))->
        join('sfBlogPost')->join('sfBlog')->join('sfBlogUser')->
        where('sfBlogUser.UserId', $user->getGuardUser()->getId())->
      _endif();
  }
  
  public function filterByText($text)
  {
    $text = trim($text);
    if($text == '' || preg_match('/^[\%\*]+$/', $text))
    {
      return $this;
    }
    $text = '%'.trim($text, '*%').'%';
    return $this->
      where('AuthorName', 'like', $text)->
      orWhere('AuthorEmail', 'like', $text)->
      orWhere('Content', 'like', $text);
  }
  
  public function filterByParentId($parent)
  {
    if(substr($parent, 0, 4) == 'blog')
    {
      return $this->
        join('sfBlogPost')->
        where('sfBlogPost.sfBlogId', substr($parent, 5));
    }
    elseif(substr($parent, 0, 4) == 'post')
    {
      return $this->
        where('sfBlogPost.Id', substr($parent, 5));
    }
  }
  
  public function filterByStatus($status)
  {
    switch ($status)
    {
      case 'pending':
        return $this->
          where('Status', sfBlogComment::PENDING)->
          orWhere('Status', sfBlogComment::DUBIOUS);
      case 'approved':
        return $this->where('Status', sfBlogComment::ACCEPTED);
      case 'spam':
        return $this->where('Status', sfBlogComment::REJECTED);
      default:
        return $this->where('Status', '<>', sfBlogComment::REJECTED);
    }
  }
  
  public function isAuthorApproved($author_name, $author_email)
  {
    $acceptedComments = $this->
      where('AuthorName', $author_name)->
      where('AuthorEmail', $author_email)->
      where('Status', sfBlogComment::ACCEPTED)->
      count();
    
    return $acceptedComments > 0;
  }
}