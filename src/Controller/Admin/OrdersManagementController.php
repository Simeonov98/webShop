<?php


namespace App\Controller\Admin;

use App\Entity\Addresses;
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
use Symfony\Component\Validator\Constraints\Date;

/**
 * @property Security security
 */
class OrdersManagementController extends AbstractController
{

    public function __construct(Security $security)
    {
        $this->security=$security;
    }
    /**
     * @Route("admin/order/management", name="order_management")
     * @return Response
     */
    public function printOrders():Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em= $this->getDoctrine()->getManager();
        $categories = $em->getRepository(Category::class)->findAll();
        $users = $em->getRepository(User::class)->findAll();
        $articles = $em->getRepository(Article::class)->findBy(['deletedAt'=>null]);
        $news = $em->getRepository(News::class)->findAll();
        $addresses = $em->getRepository(Addresses::class)->findAll();
        $orders=$em->getRepository(OrderRow::class)->findAll();
        $showDeleted=false;



        return $this->render('admin/order_management/index.html.twig', [
            'categories' => $categories,
            'controller_name'=>"Category Management",
            'users'=>$users,
            'news'=>$news,
            'articles'=>$articles,
            'orders'=>$orders,
            'addresses'=>$addresses,
            'showDeleted'=>$showDeleted
        ]);
    }

    /**
     * @Route("admin/order/management/see/{id}", name="order_see")
     * @param Request $request
     * @param OrderRow $order
     * @return Response
     */
    public function showOrders(Request $request, OrderRow $order):Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em= $this->getDoctrine()->getManager();
        $categories = $em->getRepository(Category::class)->findAll();
        $users = $em->getRepository(User::class)->findAll();
        $articles = $em->getRepository(Article::class)->findAll();
        $news = $em->getRepository(News::class)->findAll();
        $addresses = $em->getRepository(Addresses::class)->findAll();
        $orders=$em->getRepository(OrderRow::class)->findBy(["deletedAt"=>null]);
        $showDeleted=false;




        return $this->render('admin/order_management/show.html.twig', [
            'categories' => $categories,
            'controller_name'=>"Category Management",
            'users'=>$users,
            'news'=>$news,
            'articles'=>$articles,
            'orders'=>$orders,
            'addresses'=>$addresses,
            'order'=>$order,
            'showDeleted'=>$showDeleted
        ]);
    }

    /**
     * @Route("admin/order/management/call_edit", name="order_call_edit")
     * @param Request $request
     * @return Response
     */
    public function callFormEdit(Request $request): Response
    {
        $orderID = $request->get("id");
        if ($orderID != null && $orderID != 0) {
            $order = $this->getDoctrine()->getRepository(OrderRow::class)->findOneBy(['id'=>$orderID]);
        }
        else{
            $order = null;
        }
        $em= $this->getDoctrine()->getManager();
        $categories = $em->getRepository(Category::class)->findAll();
        $users = $em->getRepository(User::class)->findAll();
        $articles = $em->getRepository(Article::class)->findAll();
        $news = $em->getRepository(News::class)->findAll();
        $orders= $em->getRepository(OrderRow::class)->findAll();
        $orderHeads = $em->getRepository(OrderHead::class)->findAll();
        $addresses = $em->getRepository(Addresses::class)->findAll();
        return $this->render("admin/order_management/create.html.twig", [
            'categories' => $categories,
            'controller_name'=>"Category Management",
            'users'=>$users,
            'news'=>$news,
            'orders'=>$orders,
            'addresses'=>$addresses,
            'articles'=>$articles,
            'orderHeads' => $orderHeads,
            'order'=>$order
        ]);
    }

    /**
     * @Route("admin/order/management/new", name="order_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function createOrder(Request $request): Response
    {

        $orderID = $request->get("id");
        if ($orderID != null && $orderID != 0)
            $orderRow = $this->getDoctrine()->getRepository(OrderRow::class)->findOneBy(['id'=>$orderID]);
        else{ // create
            $city=$request->get('city');
            $street=$request->get('street');
            $number=$request->get('number');
            $phone=$request->get('phone');
            $email=$request->get('email');
            $comment=$request->get('comment');
            $fName=$request->get('fName');
            $lName=$request->get('lName');
            $userId= $this->security->getUser();
            $addressNickname=$request->get('addressNickname');

            $note=$request->get('note');
            $status=$request->get('status');

            $articles=$request->get('orederedArticles');
            $price=$request->get('price');
            $quantity=$request->get('quantity');

            $address=new Addresses();
            $address->setCreatedAt(new DateTime());
            $address->setCity($city);
            $address->setStreet($street);
            $address->setNumber($number);
            $address->setPhone($phone);
            $address->setEmail($email);
            $address->setComment($comment);
            $address->setFirstName($fName);
            $address->setLastName($lName);
            $address->setUser($userId);
            $address->setAddressNickname($addressNickname);

            $orderHead=new OrderHead();
            $orderHead->setAddress($address);
            $orderHead->setCreatedAt(new DateTime());
            $orderHead->setCreatedBy($userId);
            $orderHead->setNote($note);
            $orderHead->setStatus($status);

            $orderRow=new OrderRow();
            $orderRow->setArticle($articles);
              foreach ($articles as $article){
                $orderRow->setArticleName($article->getName());
            }
              $orderRow->setPrice($price);
              $orderRow->setQuantity($quantity);
              $orderRow->setOrder($orderHead);
              $orderRow->setCreatedBy($userId);
              $orderRow->setCreatedAt(new DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($orderRow);
            $entityManager->persist($orderHead);
            $entityManager->persist($address);
            $entityManager->flush();

            return $this->redirectToRoute('order_management');
        }
        // edit
        $city=$request->get('city');
        $street=$request->get('street');
        $number=$request->get('number');
        $phone=$request->get('phone');
        $email=$request->get('email');
        $comment=$request->get('comment');
        $fName=$request->get('fName');
        $lName=$request->get('lName');
        $addressNickname=$request->get('addressNickname');
        $userId= $this->security->getUser();

        $note=$request->get('note');
        $status=$request->get('status');

        $articles=$request->get('orederedArticles');
        $price=$request->get('price');
        $quantity=$request->get('quantity');

        $orderRow->getOrder()->getAddress()->setUpdatedAt(new DateTime);
        $orderRow->getOrder()->getAddress()->setUpdatedBy($userId);
        $orderRow->getOrder()->getAddress()->setCity($city);
        $orderRow->getOrder()->getAddress()->setStreet($street);
        $orderRow->getOrder()->getAddress()->setNumber($number);
        $orderRow->getOrder()->getAddress()->setPhone($phone);
        $orderRow->getOrder()->getAddress()->setEmail($email);
        $orderRow->getOrder()->getAddress()->setComment($comment);
        $orderRow->getOrder()->getAddress()->setFirstName($fName);
        $orderRow->getOrder()->getAddress()->setLastName($lName);
        $orderRow->getOrder()->getAddress()->setAddressNickname($addressNickname);

        $orderRow->getOrder()->setNote($note);
        $orderRow->getOrder()->setStatus($status);
        $orderRow->getOrder()->setUpdatedAt(new DateTime());
        $orderRow->getOrder()->setUpdatedBy($userId);

        $orderRow->setArticle($articles);
        $orderRow->setPrice($price);
        $orderRow->setQuantity($quantity);



        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($orderRow);
        $entityManager->flush();

        return $this->redirectToRoute('order_management');


//        return $this->render('admin/category_management/create.html.twig', [
//            'category' => $category,
//            'form' => $form->createView(),
//        ]);

    }

    /**
     * @Route ("admin/order/management/delete/{id}", name="order_delete")
     * @param Request $request
     * @param OrderRow $order
     * @return Response
     */
    public function deleteOrder(Request $request, OrderRow $order): Response
    {

        $order->setDeletedAt(new DateTime());
        $user = $this->security->getUser();
        $order->setDeletedBy($user);

        $entityManager = $this->getDoctrine()->getManager();
        // $entityManager->remove($order);
        $entityManager->flush();


        return $this->redirectToRoute('order_management');
    }


    /**
     * @Route("admin/category/management/showdeleted", name="orders_management_show_deleted")
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
}