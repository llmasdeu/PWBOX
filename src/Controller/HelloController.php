<?php

    namespace Pwbox\Controller;

    use Psr\Container\ContainerInterface;
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

   /* use Dflydev\FigCookies\FigRequestCookies;
    use Dflydev\FigCookies\FigResponseCookies;
    use Dflydev\FigCookies\SetCookie;

*/
    class HelloController{
        protected $container;

        public function __contruct(ContainerInterface $container){
            $this->container = $container;
        }

        public function indexAction(Request $request, Response $response, array $args){
            $name = $args['name'];
            //$this->container->get('view')->render($response, 'landing.html.twig', ['name' => $name]);
        }

        public function __invoke(Request $request, Response $response, array $args){
            if(!isset($_SESSION['counter'])){
                $_SESSION['counter'] = 1;
            }else{
                $_SESSION['counter']++;
            }

            $cookie = FigRequestCookies::get($request, 'advice', 0);

            /*if(empty($cookie->getValue())){
                $response = FigResponseCookies\::set($response, SetCookie::create('advice')
                ->withValue(1)->withDomain('Pwbox.test')->withPath('/')
                );
            }
*/
            $name = $request->getAttribute('name');
            return $this->container->get('view')->render($response, 'landing.html', ['name' => $name, 'counter' => $_SESSION['counter'], 'advice' => $cookie->getValue(),]);
            //$name = $args['name'];
            //$this->container->get('view')->render($response, 'hello.twing', ['name' => $name]);

        }
    }
