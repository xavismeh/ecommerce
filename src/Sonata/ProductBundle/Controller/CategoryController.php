<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\ProductBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Sonata\BaseApplicationBundle\Tool\DoctrinePager as Pager;
use Doctrine\ORM\Query\Expr;

class CategoryController extends Controller
{

    /**
     * List the main categories
     *
     * @return Response
     */
    public function indexAction()
    {
        $em             = $this->get('doctrine.orm.default_entity_manager');
        $repository     = $em->getRepository('Application\Sonata\ProductBundle\Entity\Category');
        $class          = $em->getClassMetaData('Application\Sonata\ProductBundle\Entity\Category')->name;

        $query_buidler = $repository
            ->createQueryBuilder('c')
            ->where('c.parent IS NULL');


        $pager = new Pager($class);
        $pager->setQueryBuilder($query_buidler);
        $pager->setPage($this->get('request')->get('page', 1));
        $pager->init();

        return $this->render('SonataProductBundle:Category:index.twig.html', array(
            'pager' => $pager,
        ));
        
    }

    /**
     * Display one category
     *
     * @throws NotFoundHttpException
     * @param  $category_id
     * @param  $slug
     * @return Response
     */
    public function viewAction($category_id, $slug)
    {

        $em             = $this->get('doctrine.orm.default_entity_manager');
        $repository     = $em->getRepository('Application\Sonata\ProductBundle\Entity\Category');

        $category = $repository->findOneById($category_id);

        if(!$category) {
            throw new NotFoundHttpException(sprintf('Unable to find the category with id=%d', $category_id));
        }

        return $this->render('SonataProductBundle:Category:view.twig.html', array(
           'category' => $category
        ));
    }

    /**
     * List categories from one categories
     *
     * @param  $category_id
     * @return void
     */
    public function listSubCategoriesAction($category_id)
    {
        $em             = $this->get('doctrine.orm.default_entity_manager');
        $repository     = $em->getRepository('Application\Sonata\ProductBundle\Entity\Category');
        $class          = $em->getClassMetaData('Application\Sonata\ProductBundle\Entity\Category')->name;

        $query_buidler = $repository
            ->createQueryBuilder('c')
            ->where('c.parent = :category_id');

        $pager = new Pager($class);
        $pager->setQueryBuilder($query_buidler);
        $pager->setParameter('category_id', $category_id);
        $pager->setPage($this->get('request')->get('page', 1));
        $pager->setMaxPerPage(30);
        $pager->init();

        return $this->render('SonataProductBundle:Category:list_sub_categories.twig.html', array(
            'pager' => $pager
        ));
    }

    /**
     * List the product related to one category
     * 
     * @param  $category_id
     * @return
     */
    public function listProductsAction($category_id)
    {

        $em             = $this->get('doctrine.orm.default_entity_manager');
        $repository     = $em->getRepository('Application\Sonata\ProductBundle\Entity\Product');
        $class          = $em->getClassMetaData('Application\Sonata\ProductBundle\Entity\Product')->name;
        
        $query_buidler = $repository
            ->createQueryBuilder('p')
            ->leftJoin('p.productCategories', 'pc')
            ->leftJoin('p.image', 'i')
            ->where('pc.category = :category_id');

        $pager = new Pager($class);
        $pager->setQueryBuilder($query_buidler);
        $pager->setParameter('category_id', $category_id);
        $pager->setPage($this->get('request')->get('page', 1));
        $pager->setMaxPerPage(30);
        $pager->init();

        return $this->render('SonataProductBundle:Category:list_products.twig.html', array(
            'pager' => $pager
        ));
    }


    public function listSideMenuCategoriesAction($category = null, $depth = 1, $deep = 0)
    {
        $em             = $this->get('doctrine.orm.default_entity_manager');
        $repository     = $em->getRepository('Application\Sonata\ProductBundle\Entity\Category');

        $category = $category ?: $repository->getRootCategory();

        return $this->render('SonataProductBundle:Category:side_menu_category.twig.html', array(
          'root_category' => $category,
          'depth'         => $depth,
          'deep'          => $deep + 1
        ));
    }
}