<?php

namespace App\Controller;
use Symfony\Flex\Response;


class HomeController {
    #[Route('/')]
    function home()
    {
       echo "Hello World " ;
    }
}
