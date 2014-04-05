<?php

class Admin extends \Ghastly\Plugin\Plugin {
    public $events;

    private $config;

    public function __construct($config)
    {
        session_start();

        $this->config = $config;

        if( !isset($config['username']) || !isset($config['password'])) {
            throw new Exception('Username and Password must be set if using Admin');
        }

        $this->events = [
            ['event'=>'Ghastly.PreRoute', 'func'=>'onPreRoute']
        ];
    }

    public function onPreRoute(\Ghastly\Event\PreRouteEvent $event)
    {
        $event->router->respond('GET','/admin/login', function() use ($event){
            $errors = $event->router->service()->flashes('error');

            $event->renderer->addTemplateDir('plugins/admin');
            $event->renderer->setTemplateVar('errors', $errors);
            $event->renderer->setTemplate('login.html');
        });

        $event->router->respond('POST', '/admin/login', function($request) use ($event){

            $username = $request->username;
            $password = $request->password;

            if($username === $this->config['username'] && $password === $this->config['password']) {
                $_SESSION['logged_in'] = true;
                $_SESSION['username'] = $username;

                $event->router->response()->redirect('/admin', 200);
            } else {
                $event->router->service()->flash('Bad credentials', 'error');
                $event->router->response()->redirect('/admin/login');
            }
        });

        $event->router->respond('/admin/?', function() use ($event){

            if( !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] === false) {
                $event->router->response()->redirect('/admin/login', 401);
            }

            $posts = $event->postModel->findAllHeaders();

            $event->renderer->setTemplateVar('posts', $posts);         
            $event->renderer->addTemplateDir('plugins/admin');
            $event->renderer->setTemplate('admin.html'); 
        });

        $event->router->respond('POST', '/admin/post', function($request) use ($event){

            if( !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] === false) {
                $event->router->response()->redirect('/admin/login', 401);
            }

            $title = $request->title;
            $date = $request->date;
            $content = $request->content;

            $slug = $date.'-'.$this->slugify($title);
            $filename = $slug . '.md';

            $front_matter = "---\n";
            $front_matter .= 'title: ' . $title."\n"; 
            $front_matter .= 'date: ' . $date . "\n";
            
            if($request->summary) {
                $front_matter .= 'summary: ' . $request->summary . "\n";
            }

            if($request->tags) {
                $front_matter .= 'tags: ' . $request->tags . "\n";
            }

            $front_matter .= "---\n";

            $content = $front_matter . $content;

            file_put_contents('posts/'.$filename, $content);

            $event->router->response()->redirect('/admin/edit/'.$slug, 200);
        });

        $event->router->respond('/admin/posts', function() use ($event){
            
            if( !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] === false) {
                $event->router->response()->redirect('/admin/login', 401);
            }

            $posts = $event->postModel->findAllHeaders();

            $event->renderer->setTemplateVar('posts', $posts);         
            $event->renderer->addTemplateDir('plugins/admin');
            $event->renderer->setTemplate('posts.html'); 
        });

        $event->router->respond('/admin/edit/[:post_id]', function($request) use ($event){
            if( !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] === false) {
                $event->router->response()->redirect('/admin/login', 401);
            }

            $post = $event->postModel->getPostById($request->post_id, true);

            if( ! $post)
            {
                $event->router->response()->redirect('/admin/posts');
            }

            $event->renderer->setTemplateVar('post', $post);         
            $event->renderer->addTemplateDir('plugins/admin');
            $event->renderer->setTemplate('edit.html'); 
        });

        $event->router->respond('/admin/logout', function() use ($event){
            $_SESSION['logged_in'] = false;
            $_SESSION['username'] = '';

            $event->router->response()->redirect('/');
        });
    } 

    public function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        // trim
        $text = trim($text, '-');
        // transliterate
        if (function_exists('iconv'))
        {
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        }
        // lowercase
        $text = strtolower($text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        if (empty($text))
        {
            return 'n-a';
        }
        return $text;
    }
}