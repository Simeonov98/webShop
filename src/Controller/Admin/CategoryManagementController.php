<?php

namespace App\Controller\Admin;


use App\Entity\Article;
use App\Entity\Category;
use App\Entity\News;
use App\Entity\OrderHead;
use App\Entity\OrderRow;
use App\Entity\User;
use App\Form\CategoryType;
use DateTime;
use Exception;
use phpDocumentor\Reflection\Types\Parent_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\Date;
use function Composer\Autoload\includeFile;

/**
 * @property Security security
 */
class CategoryManagementController extends AbstractController
{

    public function __construct(Security $security)
    {
        $this->security=$security;
    }
//    /**
//     * @Route("admin/category/management", name="category_management")
//     */
//    public function index(): Response
//    {
//        return $this->render('admin/category_management/index.html.twig', [
//            'controller_name' => 'CategoryManagementController',
//        ]);
//    }


    /**
     * @Route("admin/category/management", name="category_management")
     * @return Response
     */
    public function printCategories():Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em= $this->getDoctrine()->getManager();
        $categories = $em->getRepository(Category::class)->findBy(["deletedAt"=>null]);
        $users = $em->getRepository(User::class)->findAll();
        $articles = $em->getRepository(Article::class)->findBy(['deletedAt'=>null]);
        $news = $em->getRepository(News::class)->findAll();
        $orders = $em->getRepository(OrderRow::class)->findAll();
        $showDeleted=false;



        return $this->render('admin/category_management/index.html.twig', [
            'categories' => $categories,
            'controller_name'=>"Category Management",
            'users'=>$users,
            'news'=>$news,
            'orders'=>$orders,
            'articles'=>$articles,
            'showDeleted'=>$showDeleted
        ]);
    }

    /**
     * @Route("admin/category/management/call_edit", name="category_call_edit")
     * @param Request $request
     * @return Response
     */
    public function callFormEdit(Request $request): Response
    {
        $categoryID = $request->get("id");
        if ($categoryID != null && $categoryID != 0){
            $category = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['id'=>$categoryID]);}
        else{$category = null;}
        $em= $this->getDoctrine()->getManager();
        $categories = $em->getRepository(Category::class)->findAll();
        $users = $em->getRepository(User::class)->findAll();
        $articles = $em->getRepository(Article::class)->findAll();
        $news = $em->getRepository(News::class)->findAll();
        $orders = $em->getRepository(OrderRow::class)->findAll();
        return $this->render("admin/category_management/create.html.twig", [
            'categories' => $categories,
            'controller_name'=>"Category Management",
            'users'=>$users,
            'news'=>$news,
            'orders'=>$orders,
            'articles'=>$articles,
            'category' => $category
        ]);
    }

    /**
     * @Route("admin/category/management/new", name="category_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function createCategory(Request $request): Response
    {

        $categoryID = $request->get("id");
        if ($categoryID != null && $categoryID != 0)
            $category = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['id'=>$categoryID]);
        else{ // create
            $name = $request->get('name');
            $parent =$this->getDoctrine()->getRepository(Category::class)->findOneBy(['name'=>$request->get('parent')]);
            $tags = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['name'=>$request->get('tags')]);
            $user = $this->security->getUser();

            $category = new Category();
            $category->setName($name);
            if ($parent != null){
            $category->setParent($parent);}
            if( $this->getDoctrine()->getRepository(Category::class)->findOneBy(['name'=>$request->get('tags')]) != null)
            {
                $category->setTags($tags);
            }
//            else {$category->setTags(null); }
            $category->setCreatedAt(new DateTime());
            $category->setCreatedBy($user);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('category_management');
        }
            // edit
        $name = $request->get('name');
        $parent = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['name'=>$request->get('parent')]);
        $tags = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['name'=>$request->get('tags')]);
        $user = $this->security->getUser();

        $category->setName($name);
        if ($parent != null && $parent->getId() != null && $parent->getId() > 0){
            $category->setParent($parent);
        }
        if ($tags != null && $tags !=0)
        $category->setTags($tags);
        $category->setUpdatedAt(new DateTime());
        $category->setUpdatedBy($user);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($category);
        $entityManager->flush();

        return $this->redirectToRoute('category_management');


//        return $this->render('admin/category_management/create.html.twig', [
//            'category' => $category,
//            'form' => $form->createView(),
//        ]);

    }




    /**
     * @Route("admin/category/management/showdeleted", name="category_management_show_deleted")
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

        return $this->render('admin/category_management/index.html.twig', [
            'categories' => $categories,
            'controller_name'=>"Category Management",
            'users'=>$users,
            'news'=>$news,
            'orders'=>$orders,
            'articles'=>$articles,
            'showDeleted'=>$showDeleted
        ]);
    }



    /**
     * @Route("admin/category/management/delete/{id}", name="category_delete")
     *
     * @param Request $request
     * @param Category $category
     * @return Response
     */

    public function delete(Request $request, Category $category): Response
    {
        $category->setDeletedAt(new DateTime());
        $user = $this->security->getUser();
        $category->setDeletedBy($user);

            $entityManager = $this->getDoctrine()->getManager();
           // $entityManager->remove($category);
            $entityManager->flush();


        return $this->redirectToRoute('category_management');
    }

}
