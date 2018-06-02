<?php

return array (
  'autoload' => false,
  'hooks' => 
  array (
  ),
  'route' => 
  array (
    '/cms$' => 'cms/index/index',
    '/cms/c/[:diyname]' => 'cms/channel/index',
    '/cms/t/[:name]' => 'cms/tags/index',
    '/cms/a/[:diyname]' => 'cms/archives/index',
    '/cms/p/[:diyname]' => 'cms/page/index',
    '/cms/s' => 'cms/search/index',
  ),
);