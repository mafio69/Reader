<?php

namespace App\Controller;

class HomeController
{
    #[Route('/')]
    public function home()
    {
        echo 'Hello World ';
    }
}
