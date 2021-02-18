<?php


namespace App\Twig;

use App\Entity\News;
use Twig\Environment;
use Twig\Extra\Intl\IntlExtension;

use DateInterval;
use DateTime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use function Composer\Autoload\includeFile;

class LatestNewsExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('latestNews',[$this, 'filterDate']),
        ];
    }
    public function filterDate(DateTime $date)
    {


        $currentTime = new DateTime();

            if ($date > $currentTime-($currentTime->add(new DateInterval('P1D'))) ){
                $date=date_format($date,"Y/m/d H:i:s");

                return $date;
            }


            return 0;

    }
}