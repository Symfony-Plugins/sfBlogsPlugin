<?php

class PluginsfBlogPostFinder extends Dbfinder
{
  protected $class = 'sfBlogPost';

  public function recent()
  {
    $userClass = sfConfig::get('app_sfBlogs_user_class', 'sfGuardUser');
    return $this->
      innerJoin($userClass)->
      with($userClass)->
      published()->
      orderBy('CreatedAt', 'desc');
  }
  
  public function published()
  {
    return $this->
      where('IsPublished', true)->
      where('PublishedAt', '<=', time());
  }
  
  public function draft()
  {
    return $this->
      where('IsPublished', false);
  }

  public function tagged($tag)
  {
    return $this->
      join('sfBlogTag')->
      where('sfBlogTag.Tag', $tag);
  }
  
  public function managedBy($user)
  {
    return $this->
      _if(!$user->hasCredential('administrator'))->
        join('sfBlog')->join('sfBlogUser')->
        where('sfBlogUser.UserId', $user->getGuardUser()->getId())->
      _endif();
  }
  
  public function whereBlogTitle($strippedTitle)
  {
    return $this->
      with('sfBlog')->
      where('sfBlog.StrippedTitle', $strippedTitle);
  }
  
  public function findArchives($blog = null)
  {
    return $this->
      published()->
      _if($blog)->
        relatedTo($blog)->
      _endif()->
      withColumn('date_format(sfBlogPost.PublishedAt, \'%y%m\')', 'month')->
      withColumn('count(Id)', 'count')->
      groupBy('month')->
      orderBy('month', 'desc')->
      find();
  }
  
  public function archived($month)
  {
    return $this->
      whereCustom('date_format(sfBlogPost.PublishedAt, \'%%y%%m\') = ?',$month);
  }
  
  public function filterByText($text)
  {
    $text = trim($text);
    if ($text == '' || preg_match('/^[\%\*]+$/', $text))
    {
      return $this;
    }
    $text = '%'.trim($text, '*%').'%';
    return $this->
      where('Title', 'like', $text)->
      orWhere('Content', 'like', $text);
  }
  
  public function filterByBlogId($blogId)
  {
    if ($blogId)
    {
      return $this->where('sfBlogId', $blogId);
    }
  }
  
  public function filterbyTag($tag)
  {
    if ($tag)
    {
      return $this->
        join('sfBlogTag')->
        where('sfBlogTag.Tag', $tag);
    }
  }
  
  public function applySort($sorts)
  {
    $sort_column = $sorts['sort'];
    switch($sort_column)
    {
      case 'blog':
      case 'author':
      case 'nb_comments':
      case 'title':
        $sort_method = 'orderBy' . sfInflector::camelize($sort_column);
        $this->$sort_method($sorts['type']);
        break;
      case 'default':
        $this->orderByDate($sorts['type']);
        break;
      default:
        $sort_column = sfInflector::camelize($sort_column);
        $this->orderBy($sort_column, $sorts['type']);
    }
    
    return $this;
  }
  
  public function findByStrippedTitleAndDate($text, $date)
  {
    return $this->
      where('StrippedTitle', $text)->
      _if(sfConfig::get('app_sfBlogs_use_date_in_url', false))->
        where('PublishedAt', $date)->
      _endif()->
      findOne();
  }
  
  public function orderByTitle($order = 'asc')
  {
    return $this->
      withColumn('UPPER(sfBlogPost.Title)', 'UTitle')->
      orderBy('UTitle', $order);
  }
  
  public function orderByBlog($order = 'asc')
  {
    return $this->
      leftJoin('sfBlog')->
      orderBy('sfBlog.Title', $order);
  }
  
  public function orderByAuthor($order = 'asc')
  {
    return $this->
      leftJoin('sfGuardUser')->
      orderBy('sfGuardUser.username', $order);
  }
  
  public function orderByNbComments($order = 'asc')
  {
    return $this->
      orderBy('NbComments', $order);
  }
  
  public function orderByDate($order = 'asc')
  {
    // Order by publication date, and fallback to creation date if not yet published
    return $this->
      withColumn('IFNULL(sfBlogPost.PublishedAt, sfBlogPost.CreatedAt)', 'date_sort')->
      orderBy('date_sort', $order);
  }
}