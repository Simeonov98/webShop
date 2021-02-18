<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\News;
use App\Entity\OrderHead;
use App\Entity\OrderRow;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class AdminDashboardController extends AbstractController
{
    /**
     * @Route("/admin/dashboard", name="admin_dashboard")
     */
    public function index(): Response
    {
        $em= $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->findAll();
        $categories = $em->getRepository(Category::class)->findAll();
        $articles = $em->getRepository(Article::class)->findBy(['deletedAt'=>null]);
        $news = $em->getRepository(News::class)->findAll();
        $orders = $em->getRepository(OrderRow::class)->findAll();

        $totalValue=0;
         foreach ($articles as $article){
            $totalValue+= $article->getPrice();
        }
        $latestNews = $em->getRepository(News::class)->findBy(["deletedAt"=>null],['createdAt'=>'DESC']);
        $latestNews = array_slice($latestNews, 0, 3);





        return $this->render('/admin/admin_dashboard/index.html.twig', [
            'controller_name' => 'AdminDashboardController',
            'users' => $users,
            'categories' => $categories,
            'articles' => $articles,
            'news' => $news,
            'orders'=> $orders,
            'totalValue'=>$totalValue,
            'latestNews'=>$latestNews
        ]);
    }


}
