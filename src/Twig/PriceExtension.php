<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PriceExtension extends AbstractExtension
{
    public function getFilters()
    {
        return array(
            new TwigFilter('price', array($this, 'formatPrice')),
        );
    }

    public function formatPrice($number)
    {
        dump($number);
        return 'hello from the filter';
    }
}