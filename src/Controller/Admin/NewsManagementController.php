<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\News;
use App\Entity\OrderHead;
use App\Entity\OrderRow;
use App\Entity\User;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @property Security security
 */
class NewsManagementController extends AbstractController
{


    public function __construct(Security $security)
    {
        $this->security = $security;
    }



    /**
     * @Route("admin/news/management", name="news_management")
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->findAll();
        $categories = $em->getRepository(Category::class)->findAll();
        $articles = $em->getRepository(Article::class)->findBy(['deletedAt'=>null]);
        $news = $em->getRepository(News::class)->findBy(["deletedAt"=>null]);
        $orders = $em->getRepository(OrderRow::class)->findAll();
        $showDeleted=false;
        return $this->render('admin/news_management/index.html.twig', [
            'controller_name' => 'NewsManagementController',
            'users' => $users,
            'categories' => $categories,
            'articles' => $articles,
            'news' => $news,
            'orders'=>$orders,
            'showDeleted'=>$showDeleted
        ]);
    }

    /**
     * @Route("admin/news/management/call_edit",  name="call_news_edit")
     * @param Request $request
     * @return Response
     */
    public function callEditNews(Request $request): Response
    {
        $newID = $request->get("id");
        if ($newID != null && $newID != 0) {
            $new = $this->getDoctrine()->getRepository(News::class)->findOneBy(['id' => $newID]);
        } else {
            $new = null;
        }
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository(Category::class)->findAll();
        $users = $em->getRepository(User::class)->findAll();
        $articles = $em->getRepository(Article::class)->findAll();
        $news = $em->getRepository(News::class)->findAll();
        $orders = $em->getRepository(OrderRow::class)->findAll();
        return $this->render("admin/news_management/create.html.twig", [
            'categories' => $categories,
            'controller_name' => "News Management",
            'users' => $users,
            'news' => $news,
            'new' => $new,
            'orders'=>$orders,
            'articles' => $articles
        ]);
    }


    /**
     * @Route("admin/news/management/edit", name="admin_news_edit")
     * @param Request $request
     * @return Response
     */
    public function editNews(Request $request): Response
    {
        $newID = $request->get("id");
        if ($newID != null && $newID != 0)
            $new = $this->getDoctrine()->getRepository(News::class)->findOneBy(['id' => $newID]);
        else { // create
            $title = $request->get('title');
            $content = $request->get('content');
            $user = $this->security->getUser();

            $new = new News();
            $new->setTitle($title);
            $new->setContent($content);
            $new->setCreatedAt(new DateTime());
            $new->setCreatedBy($user);


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($new);
            $entityManager->flush();

            return $this->redirectToRoute('news_management');
        }
        // edit
        $title = $request->get('title');
        $content = $request->get('content');
        $user = $this->security->getUser();

        $new->setTitle($title);
        $new->setContent($content);
        $new->setUpdatedAt(new DateTime());
        $new->setUpdatedBy($user);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($new);
        $entityManager->flush();

        return $this->redirectToRoute('news_management');
    }
    /**
     * @Route("admin/news/management/delete/{id}", name="news_delete")
     *
     * @param Request $request
     * @param News $news
     * @return Response
     */

    public function delete(Request $request, News $news): Response
    {
        $news->setDeletedAt(new DateTime());
        $user = $this->security->getUser();
        $news->setDeletedBy($user);

        $entityManager = $this->getDoctrine()->getManager();
        // $entityManager->remove($category);
        $entityManager->flush();


        return $this->redirectToRoute('news_management');
    }
    /**
     * @Route("admin/news/management/showdeleted", name="news_management_show_deleted")
     */
    public function showDeleted(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em= $this->getDoctrine()->getManager();
        $categories = $em->getRepository(Category::class)->findAll();
        $users = $em->getRepository(User::class)->findAll();
        $articles = $em->getRepository(Article::class)->findBy(['deletedAt'=>null]);
        $news = $em->getRepository(News::class)->findAll();
        $orders = $em->getRepository(OrderRow::class)->findAll();
        $showDeleted=true;

        return $this->render('admin/news_management/index.html.twig', [
            'categories' => $categories,
            'controller_name'=>"News Management",
            'users'=>$users,
            'news'=>$news,
            'orders'=>$orders,
            'articles'=>$articles,
            'showDeleted'=>$showDeleted
        ]);
    }
}
