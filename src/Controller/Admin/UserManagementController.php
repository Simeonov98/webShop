<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\News;
use App\Entity\OrderRow;
use App\Entity\User;
use App\Form\UserType;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @property Security security
 */
class UserManagementController extends AbstractController
{

    public function __construct(Security $security)
    {
        $this->security=$security;

    }


    /**
     * @Route("admin/user/management", name="user_management")
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em= $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->findAll();
        $categories = $em->getRepository(Category::class)->findAll();
        $articles = $em->getRepository(Article::class)->findBy(['deletedAt'=>null]);
        $news = $em->getRepository(News::class)->findAll();
        return $this->render('admin/user_management/index.html.twig', [
            'controller_name' => 'UserManagementController',
            'users' => $users,
            'categories' => $categories,
            'articles' => $articles,
            'news' => $news,
        ]);
    }


    /**
     * @Route("admin/user/management", name="user_management")
     * @return Response
     */
    public function printUsers():Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em= $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->findAll();
        $categories = $em->getRepository(Category::class)->findAll();
        $articles = $em->getRepository(Article::class)->findAll();
        $news = $em->getRepository(News::class)->findAll();
        $orders = $em->getRepository(OrderRow::class)->findAll();
        return $this->render('admin/user_management/index.html.twig', [
            'users' => $users,
            'controller_name'=>"User Management",
            'categories' => $categories,
            'articles' => $articles,
            'orders'=>$orders,
            'news' => $news,
        ]);
    }


    /**
     * @Route("admin/user/management/edit/{id}", name="editUser")
     * @param Request $request
     * @return Response
     */
    public function callFormEdit(Request $request): Response
    {
        $userID = $request->get("id");
        if ($userID != null && $userID != 0){
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id'=>$userID]);}
        else{$user = null;}
        $em= $this->getDoctrine()->getManager();
        $categories = $em->getRepository(Category::class)->findAll();
        $users = $em->getRepository(Category::class)->findAll();
        $articles = $em->getRepository(Category::class)->findAll();
        $news = $em->getRepository(Category::class)->findAll();
        $orders = $em->getRepository(OrderRow::class)->findAll();
        return $this->render("admin/user_management/edit.html.twig", [
            'user'=>$user,
            'categories' => $categories,
            'controller_name'=>"User Management",
            'users'=>$users,
            'news'=>$news,
            'orders'=>$orders,
            'articles'=>$articles,
        ]);
    }

    /**
     * @Route("admin/userManagement/editt/{id}", name="editUserr", methods={"GET","POST"})
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function editUser(Request $request,User $user)
    {

        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id'=>$request->get("id")]);
        $email = $request->get("email");
        $fname = $request->get("fname");
        $lname = $request->get("lname");


        $user->setCreatedAt(new DateTime());
        $user->setEmail($email);
        $user->setFname($fname);
        $user->setLname($lname);


        $em= $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('user_management');
    }




}
