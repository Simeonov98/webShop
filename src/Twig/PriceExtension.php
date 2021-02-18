<?php


namespace App\Twig;


use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PriceExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('price',[$this, 'formatPrice']),
        ];
    }
    public function formatPrice($number)
    {
        $price = number_format($number);
        $price = $price.' лв';

        return $price;
    }
}