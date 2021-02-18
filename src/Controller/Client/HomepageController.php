<?php


namespace App\Controller\Client;


use App\Entity\Article;
use App\Entity\Category;
use App\Entity\News;
use App\Entity\OrderRow;
use App\Entity\User;
use ContainerJtPc292\getConsole_ErrorListenerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->findAll();
        $categories = $em->getRepository(Category::class)->findBy(["deletedAt"=>null]);
        $articles = $em->getRepository(Article::class)->findAll();
        $news = $em->getRepository(News::class)->findAll();
        $orders = $em->getRepository(OrderRow::class)->findAll();

        $totalValue = 0;
        foreach ($articles as $article) {
            $totalValue += $article->getPrice();
        }
        $latestNews = $em->getRepository(News::class)->findBy(["deletedAt" => null], ['createdAt' => 'DESC']);
        $latestNews = array_slice($latestNews, 0, 3);

        $latestArticles = $em->getRepository(Article::class)->findBy(['deletedAt'=>null],["createdAt"=>"DESC"]);
        $latestArticles = array_slice($latestArticles,0,3);
        $latestArticles1 = array_pop($latestArticles);
        $latestArticles2 = array_pop($latestArticles);
        $latestArticles3 = array_pop($latestArticles);


        return $this->render('/homepage/index.html.twig', [
            'controller_name' => 'AdminDashboardController',
            'users' => $users,
            'categories' => $categories,
            'articles' => $articles,
            'news' => $news,
            'orders' => $orders,
            'latestNews' => $latestNews,
            'latestArticles1' => $latestArticles1,
            'latestArticles2' => $latestArticles2,
            'latestArticles3' => $latestArticles3
        ]);
    }
}