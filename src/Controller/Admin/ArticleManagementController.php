<?php

namespace App\Controller\Admin;

use App\Entity\Addresses;
use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Images;
use App\Entity\News;
use App\Entity\OrderHead;
use App\Entity\OrderRow;
use App\Entity\User;
use DateTime;
use Symfony\Component\DomCrawler\Image;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\Date;


/**
 * @property Security security
 */
class ArticleManagementController extends AbstractController
{


    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @Route("admin/article/management", name="article_management")
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->findAll();
        $categories = $em->getRepository(Category::class)->findAll();
        $articles = $em->getRepository(Article::class)->findBy(["deletedAt" => null]);
        $news = $em->getRepository(News::class)->findAll();
        $orders = $em->getRepository(OrderRow::class)->findAll();
        $images = $em->getRepository(Images::class)->findAll();
        $showDeleted = false;

        return $this->render('admin/article_management/index.html.twig', [
            'controller_name' => 'ArticleManagementController',
            'users' => $users,
            'categories' => $categories,
            'articles' => $articles,
            'news' => $news,
            'orders' => $orders,
            'images' => $images,
            'showDeleted' => $showDeleted
        ]);
    }

    /**
     * @Route("admin/article/management/call_edit",  name="call_article_edit")
     * @param Request $request
     * @return Response
     */
    public function callEditArticle(Request $request): Response
    {
        $articleID = $request->get("id");
        if ($articleID != null && $articleID != 0) {
            $article = $this->getDoctrine()->getRepository(Article::class)->findOneBy(['id' => $articleID]);
        } else {
            $article = null;
        }
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository(Category::class)->findBy(['deletedAt'=>null]);
        $users = $em->getRepository(User::class)->findAll();
        $articles = $em->getRepository(Article::class)->findAll();
        $news = $em->getRepository(News::class)->findAll();
        $orders = $em->getRepository(OrderRow::class)->findAll();
        return $this->render("admin/article_management/create.html.twig", [
            'categories' => $categories,
            'controller_name' => "Article Management",
            'users' => $users,
            'news' => $news,
            'orders' => $orders,
            'articles' => $articles,
            'article' => $article
        ]);
    }


    /**
     * @Route("admin/article/management/edit", name="admin_article_edit")
     * @param Request $request
     * @param SluggerInterface $slugger
     * @return Response
     */
    public function editArticle(Request $request, SluggerInterface $slugger): Response
    {
        $articleID = $request->get("id");
        if ($articleID != null && $articleID != 0)
            $article = $this->getDoctrine()->getRepository(Article::class)->findOneBy(['id' => $articleID]);
        else { // create
            $name = $request->get('name');
            $price = $request->get('price');
            $categoryID = $request->get('category');
            $category = $this->getDoctrine()->getRepository(Category::class)->find($categoryID);
            $description = $request->get('description');
            $techData = $request->get('techdata');
            $quantity = $request->get('quantity');
            $manufacturer = $request->get('manufacturer');
            $imageData = $request->files->get('image');
            $imagesData = $request->files->get('images');
            $tags = $request->get('tags');
            $user = $this->security->getUser();

            $article = new Article();
            $article->setName($name);
            $article->setPrice($price);
            $article->setDescription($description);
            $article->setCategory($category);
            $article->setTechData($techData);
            $article->setQuantity($quantity);
            $article->setManufacturer($manufacturer);
            //$article->setImageId($imageID);
            $article->setCreatedAt(new DateTime());
            $article->setCreatedBy($user);
            if ($this->getDoctrine()->getRepository(Article::class)->findOneBy(['name' => $request->get('tags')]) != null) {
                $article->setTags($tags);
            }
//            else {$category->setTags(null); }
//            if ($this->getDoctrine()->getRepository(Article::class)->findOneBy(['name' => $request->get('imageID')]) != null) {
//                $article->setImageId($imageID);
//            }
//            else{
//                $article->setImageId(1);
//            }

            // $imageData=$request->files->get('image');
            $article->setImageId(100000);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();

            dump($imageData);

            if ($imageData) {
                $image = new Images();
                $imageName = md5(uniqid()) . '.' . $imageData->guessExtension();
                $imageData->move(
                    "uploads/images/articles/",
                    $imageName
                );
                $image->setPath("uploads/images/articles/" . $imageName);
                $image->setCreatedAt(new DateTime());
                $image->setCreatedBy($user);
                $image->setArticleId($article->getId());
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($image);
                $entityManager->flush();
                $article->setImageId($image->getPath());
            }
            if ($imagesData) {
                foreach ($imagesData as $imagesOne) {
                    $image1 = new Images();
                    $imageName = md5(uniqid()) . '.' . $imagesOne->guessExtension();
                    $imagesOne->move(
                        "uploads/images/articles/",
                        $imageName
                    );
                    $image1->setPath("uploads/images/articles/" . $imageName);
                    $image1->setCreatedAt(new DateTime());
                    $image1->setCreatedBy($user);
                    $image1->setArticleId($article->getId());
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($image1);
                    $entityManager->flush();
                    //$article->setImageId($image1->getPath());
                }
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();


            return $this->redirectToRoute('article_management');
        }
        // edit
        $name = $request->get('name');
        $price = $request->get('price');
        $description = $request->get('description');
        $techData = $request->get('techdata');
        $quantity = $request->get('quantity');
        $manufacturer = $request->get('manufacturer');
        //$imageID = $request->get('image');
        $tags = $this->getDoctrine()->getRepository(Article::class)->findOneBy(['name' => $request->get('tags')]);
        $imageData = $request->files->get('image');
        $imagesData = $request->files->get('images');
        $user = $this->security->getUser();

        $article->setName($name);
        $article->setPrice($price);
        $article->setDescription($description);
        $article->setTechData($techData);
        $article->setQuantity($quantity);
        $article->setManufacturer($manufacturer);

        if ($tags != null && $tags != 0)
            $article->setTags($tags);
        $article->setUpdatedAt(new DateTime());
        $article->setUpdatedBy($user);

        $article->setImageId(100000);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($article);
        $entityManager->flush();

        dump($imageData);

        if ($imageData) {
            $image = new Images();
            $imageName = md5(uniqid()) . '.' . $imageData->guessExtension();
            $imageData->move(
                "uploads/images/articles/",
                $imageName
            );
            $image->setPath("uploads/images/articles/" . $imageName);
            $image->setCreatedAt(new DateTime());
            $image->setCreatedBy($user);
            $image->setArticleId($article->getId());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($image);
            $entityManager->flush();
            $article->setImageId($image->getPath());
        }
        if ($imagesData) {
            foreach ($imagesData as $imagesOne) {
                $image1 = new Images();
                $imageName = md5(uniqid()) . '.' . $imagesOne->guessExtension();
                $imagesOne->move(
                    "uploads/images/articles/",
                    $imageName
                );
                $image1->setPath("uploads/images/articles/" . $imageName);
                $image1->setCreatedAt(new DateTime());
                $image1->setCreatedBy($user);
                $image1->setArticleId($article->getId());
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($image1);
                $entityManager->flush();
                //$article->setImageId($image1->getPath());
            }
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($article);
        $entityManager->flush();


        return $this->redirectToRoute('article_management');
    }

    /**
     * @Route ("admin/article/management/delete/{id}", name="article_delete")
     */
    public function deleteArticle(Request $request, Article $article): Response
    {

        $article->setDeletedAt(new DateTime());
        $user = $this->security->getUser();
        $article->setDeletedBy($user);

        $entityManager = $this->getDoctrine()->getManager();
        // $entityManager->remove($category);
        $entityManager->flush();


        return $this->redirectToRoute('article_management');
    }

    /**
     * @Route("admin/article/management/showdeleted", name="article_management_show_deleted")
     */
    public function showDeleted(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository(Category::class)->findAll();
        $users = $em->getRepository(User::class)->findAll();
        $articles = $em->getRepository(Article::class)->findAll();
        $news = $em->getRepository(News::class)->findAll();
        $orders = $em->getRepository(OrderRow::class)->findAll();

        $showDeleted = true;

        return $this->render('admin/article_management/index.html.twig', [
            'categories' => $categories,
            'controller_name' => "Article Management",
            'users' => $users,
            'news' => $news,
            'orders' => $orders,
            'articles' => $articles,
            'showDeleted' => $showDeleted
        ]);
    }

    /**
     * @Route("admin/article/management/see/{id}", name="article_see")
     * @param Request $request
     * @param Article $article
     * @return Response
     */
    public function showArticle(Request $request, Article $article): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository(Category::class)->findAll();
        $users = $em->getRepository(User::class)->findAll();
        $articles = $em->getRepository(Article::class)->findBy(["deletedAt" => null]);
        $news = $em->getRepository(News::class)->findAll();
        $addresses = $em->getRepository(Addresses::class)->findAll();
        $orders = $em->getRepository(OrderRow::class)->findAll();
        $images = $em->getRepository(Images::class)->findBy(["articleId" => $article->getId()]);
        $showDeleted = false;


        return $this->render('admin/article_management/show.html.twig', [
            'categories' => $categories,
            'controller_name' => "Category Management",
            'users' => $users,
            'news' => $news,
            'articles' => $articles,
            'orders' => $orders,
            'addresses' => $addresses,
            'article' => $article,
            'images' => $images,
            'showDeleted' => $showDeleted
        ]);
    }

    /**
     * @Route("admin/article/delete/image/{imageId}", name="del_img", methods={"POST","GET"})
     * @param Request $request
     * @return Response
     */
    public function deletePicResponse(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $imageId = $request->get('imageId');
            $em = $this->getDoctrine()->getManager();
            $image = $em->getRepository(Images::class)->findOneBy(["id" => $imageId]);
            $user = $this->security->getUser();

            $image->setDeletedBy($user);
            $image->setDeletedAt(new DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($image);
            $entityManager->flush();


            return new JsonResponse('good', 200);
        }
        return new JsonResponse('ne stana ', 400);
    }
}