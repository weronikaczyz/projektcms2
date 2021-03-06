<?php
/**
 * This file contains controller of pages administration.
 */

/**
 * Defining namespace and useful components.
 */
namespace Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Model\PagesModel;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * This class contains definitions of menu elements administration methods.
 */
class PagesController implements ControllerProviderInterface
{
    protected $_model;

    /**
     * Routing.
     *
     * @param Application $app
     * @return mixed|\Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        $pagesController = $app['controllers_factory'];

        $this->_model = new PagesModel($app);

        $pagesController->match('/', array($this, 'index'))->bind('/');
        $pagesController->match('pages/admin', array($this, 'admin'))->bind('/pages/admin');
        $pagesController->match('pages/edit', array($this, 'edit'))->bind('/pages/edit');
        $pagesController->match('pages/new', array($this, 'newPage'))->bind('/pages/new');
        $pagesController->match('pages/delete', array($this, 'delete'))->bind('/pages/delete');

        return $pagesController;
    }

    /**
     * This method allows admin to view entries.
     *
     * @param application
     * @param request
     */
    public function admin(Application $app, Request $request)
    {
//        if ($app['security.authorization_checker']->isGranted('ROLE_EDITOR')) {
            $pages = $this->_model->getPagesEntries();
            return $app['twig']->render('pages/admin.twig', array('pages' => $pages));
//        } else {
//              return $app->redirect($app['url_generator']->generate('/auth/login'), 301);
//        }
    }

    /**
     * This method allows admin to view entries.
     *
     * @param application
     * @param request
     */
    public function index(Application $app, Request $request)
    {
        $id = (int)$request->get('id', $app['homepage']);

        $page = $this->_model->getPage($id);

        if ($page['published'] == true) { // page is published
            return $app['twig']->render('pages/index.twig', array('page' => $page));
        } else { // page unpublished or it doesnt exist
            if ($app['security.authorization_checker']->isGranted('ROLE_ADMIN') && $page['published'] == 'NO') {
                // page is unpublished, but user is an admin
                return $app['twig']->render('pages/index.twig', array('page' => $page));
            } else { // user is not an admin, page is not published or it doesn't exist
                $app['session']->getFlashBag()->set('error', 'Page was not found or it has not been published!');
                return $app->redirect($app['url_generator']->generate('/pages/admin'), 301);
            }
        }
    }

    /**
     * This method allows administrator to edit page.
     *
     * @param application
     * @param request
     */
    public function edit(Application $app, Request $request)
    {
        $id = (int)$request->get('id', 1);


        $data = array();
        //if ($app['security.authorization_checker']->isGranted('ROLE_ADMIN')) {
        // default values:


        $entry = $this->_model->getPage($id);
        $form = $app['form.factory']->createBuilder(FormType::class, $entry)
            ->add(
                'idpages', HiddenType::class, array(
                    'constraints' => array(
                        new Assert\NotBlank()
                    )
                )
            )
            ->add(
                'title', TextType::class, array(
                    'constraints' => array(
                        new Assert\NotBlank(), new Assert\Length(
                            array(
                                'min' => 3
                            )
                        )
                    )
                )
            )
            ->add(
                'content', TextareaType::class, array(
                    'constraints' => array(
                        new Assert\NotBlank(), new Assert\Length(
                            array(
                                'min' => 2
                            )
                        )
                    )
                )
            )
            ->add(
                'published', ChoiceType::class, array(
                    'choices' => array(
                        'YES' => 1, 'NO' => 0
                    ),
                    'expanded' => true,
                )
            )
            ->add('Submit', SubmitType::class)
            ->getForm();

        //$builder =$app['form.factory']->FormBuilderInterface
        //->add('subject', TextType::class, array(
        //        'label' => 'Subject',
        //        'attr' => array(
        //            'class' => 'form-group')
        //    )
        //);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $model = $this->_model->saveEntry($data);
            if (!$model) {
                $app['session']->getFlashBag()->set('success', 'Information was updated sucessfully');
                return $app->redirect($app['url_generator']->generate('/'), 301);
            } else {
                $app['session']->getFlashBag()->set('error', 'Ooops! An error occured!');
            }
        }


        //} else {
        //  return $app->redirect($app['url_generator']->generate('/auth/login'), 301);
        //}

        return $app['twig']->render('pages/edit.twig', array('form' => $form->createView(), 'item' => 'page'));
    }

    /**
     * This method allows administrator to create new menu entry.
     *
     * @param application
     * @param request
     */
    public function newPage(Application $app, Request $request)
    {
        $data = array();

        //if ($app['security']->isGranted('ROLE_ADMIN')) {
        $form = $app['form.factory']->createBuilder(FormType::class, $data)
            ->add(
                'title', TextType::class, array(
                    'constraints' => array(
                        new Assert\NotBlank(), new Assert\Length(
                            array(
                                'min' => 3
                            )
                        )
                    )
                )
            )
            ->add(
                'content', TextareaType::class, array(
                    'constraints' => array(
                        new Assert\NotBlank(), new Assert\Length(
                            array(
                                'min' => 2
                            )
                        )
                    )
                )
            )
            ->add(
                'published', ChoiceType::class, array(
                    'choices' => array(
                        'YES' => '1', 'NO' => '0'
                    ),
                    'expanded' => true,
                )
            )
            ->add('Submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entry = $this->_model->newPage($form->getData());
            if ($entry != NULL) {  // entry should contain id of newly created page or NULL
                $app['session']->getFlashBag()->set('success', 'New entry was created!');
                return $app->redirect($app['url_generator']->generate('/pages/admin'), 301);
            } else { // user does not want a link
                $app['session']->getFlashBag()->set(
                    'error', 'Ooops! An error occured! Neither entry or link were created!'
                );
            }
        }

        return $app['twig']->render('pages/new.twig', array('form' => $form->createView()));
    }




    /**
     * This method allows administrator to delete page.
     *
     * @param application
     * @param request
     */

    public function delete(Application $app, Request $request)
    {
        $id = (int)$request->get('id', 0);

        //if ($app['security']->isGranted('ROLE_ADMIN')) {
        $form = $app['form.factory']->createBuilder(FormType::class)
            ->add(
                'Yes', SubmitType::class
            )
            ->add (
                'No', SubmitType::class
            )
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) { // if the form is valid, getting its data
            $data = $form->getData();
            if ($form->get('Yes')->isClicked()) { // checking which button was clicked
                $delete = $this->_model->deletePage($id); // if yes, proceed to deleting entry
                if (!$delete) { // if successful
                    $app['session']->getFlashBag()->set('success', 'Entry was deleted successfully!');
                    return $app->redirect(
                        $app['url_generator']->generate(
                            '/pages/admin'
                        ),
                        301
                    ); // redirecting to main menu admin site
                } else { // if not
                    $app['session']->getFlashBag()->set('error', 'Ooops! An error occured!');
                }
            } else { // this happens when 'No' is clicked
                return $app->redirect($app['url_generator']->generate('/pages/admin'), 301);
            } // redirecting to main menu admin site
        }
        //     else { // if user is not admin
        //      return $app->redirect($app['url_generator']->generate('/auth/login'), 301);
        // }
        return $app['twig']->render('pages/delete.twig', array('form' => $form->createView(), 'item' => 'page'));
    }

}

