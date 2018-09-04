<?php

/**
 * Defining namespace and useful components.
 */
namespace Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use Model\PhotosModel;
use Model\PagesModel;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

/**
 *  This class defines global settings if the website including theme, layout, logo, footer, header etc.
 *
 * @package Controller
 */
class PhotosController implements ControllerProviderInterface
{

    protected $_model;

    /**
     * Routing.
     *
     * @param application
     * @return $globalController instance
     */
    public function connect(Application $app)
    {
        $this->_model = new PhotosModel($app);

        $photosController = $app['controllers_factory'];

        $photosController->match('/upload/{id}', array($this, 'upload'))->bind('/photos/upload');

        return $photosController;
    }


    /**
     * This function is used to edit site logo.
     *
     * @return void
     */
    public function upload(Application $app, Request $request, $id)
    {
        //if ($app['security']->isGranted('ROLE_ADMIN')) {
//            $idPage = (int)$request->get('id', null);

            if (!$id) {
                $app['session']->getFlashBag()->set('error', 'No page specified.');
                return $app->redirect($app['url_generator']->generate('/pages/admin'), 301);
            }

            $form = $app['form.factory']->createBuilder(FormType::class)
                ->add(
                    'file', FileType::class, array(
                        'label' => 'Choose file',
                        'constraints' => array(
                            new Assert\Image()
                        )
                    )
                )
                ->add(
                    'save', SubmitType::class, array(
                        'label' => 'Upload file'
                    )
                )
                ->getForm();

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    try {
                        $files = $request->files->get($form->getName());
                        $path = dirname(dirname(dirname(__FILE__))).'/web/media';
                        $originalFilename = $files['file']->getClientOriginalName();
                        $newFilename = $this->_model->createName($originalFilename);
                        $files['file']->move($path, $newFilename);
                        $this->_model->saveFile($newFilename);

                        $idPhoto = $this->getId($app);



                        $pagesModel = new PagesModel($app);
                        $success = $pagesModel->updatePhoto($idPhoto, $id);


                        if ($success) {
                            $app['session']->getFlashBag()->set('success', 'File successfully uploaded.');
                            return $app->redirect($app['url_generator']->generate('/pages/admin'), 301);
                        } else {
                            $app['session']->getFlashBag()->set('error', 'Cannot upload file.');
                            return $app->redirect($app['url_generator']->generate('/pages/admin'), 301);
                        }
                    } catch (Exception $e) {
                        $app['session']->getFlashBag()->set('error', 'Cannot upload file.');
                        return $app->redirect($app['url_generator']->generate('/pages/admin'), 301);
                    }
                }
            } else {
                return $app['twig']->render('settings/upload.twig',
                    array(
                        'form' => $form->createView(),
                        'id' => $id
                    )
                );
            }

//                $form->bind($request);

//                if ($form->isValid()) {
//
//                    try {
//
//                        $files = $request->files->get($form->getName());
//                        $path = dirname(dirname(dirname(__FILE__))).'/web/media';
//
//                        $originalFilename = $files['file']->getClientOriginalName();
//
//                        $newFilename = $this->_model->createName($originalFilename);
//                        $files['file']->move($path, $newFilename);
//                        $this->_model->saveFile($newFilename);
//
//                        $option = (string) $request->get('option', 0); // checking which option has been selected
//
//                        var_dump($option);
//                        $app['session']->getFlashBag()->set('success', 'File successfully uploaded.');
//
//                        //logo upload
//                        if (strcmp($option, 'logo') == 0) {// we want the new picture to be the logo
//                            $id=$this->getId($app); // retrieve id of new picture
//                            $globalModel = new GlobalModel($app);  // connecting to GlobalModel
//                            $success = $globalModel->updateSettings($id, 'LOGO'); // inserting new database entry
//                            if (!$success) { // it was successful
//                                $app['session']->getFlashBag()->set(
//                                    'success', 'File successfully uploaded, logo was replaced.'
//                                );
//                                if ($app['setup'] == true) {
//                                    return $app->redirect($app['url_generator']->generate('/global/layout'), 301);
//                                } else {
//                                    return $app->redirect($app['url_generator']->generate('/global/logo'), 301);
//                                }
//                            } else { // it was not succesful
//                                $app['session']->getFlashBag()->set(
//                                    'error', 'Cannot upload file. Logo cannot be changed'
//                                );
//                            }
//                        }
//
//                        //background upload
//                        if (strcmp($option, 'background') == 0) { // we want the new picture to be the background
//                            $id=$this->getId($app); // retrieve id of new picture
//                            $globalModel = new GlobalModel($app);  // connecting to GlobalModel
//                            $success = $globalModel->updateSettings($id, 'BACKGROUND');
//                            // inserting new database entry
//                            if (!$success) { // it was successful
//                                $app['session']->getFlashBag()->set(
//                                    'success', 'File successfully uploaded, background was replaced.'
//                                );
//                                if ($app['setup'] == true) {
//                                    return $app->redirect($app['url_generator']->generate('/pages/add'), 301);
//                                } else {
//                                    return $app->redirect(
//                                        $app['url_generator']->generate(
//                                            '/global/background'
//                                        ),
//                                        301
//                                    );
//                                }
//                            } else { // it was not succesful
//                                $app['session']->getFlashBag()->set(
//                                    'error', 'Cannot upload file. Background cannot be changed'
//                                );
//                            }
//                        }
//
//
//
//                    } catch (Exception $e) {
//                        $app['session']->getFlashBag()->set('error', 'Cannot upload file.');
//                    }
//                }
//            } else {
//                return $app['twig']->render(
//                    'settings/upload.twig', array(
//                        'form' => $form->createView()
//                    )
//                );
//            }

//            return $app->redirect($app['url_generator']->generate('/auth/login'), 301);
    }

    /**
     * This function is used to retrieve gallery of all files uploaded.
     *
     * @return array
     */
    public function getGallery(Application $app)
    {
        $model = new PhotosModel($app);
        $gallery = $model->gallery();
        return $gallery;
    }

    /**
     * This function is used to retrieve id of last uploaded photo.
     *
     * @return array
     */
    public function getId(Application $app)
    {
        $model = new PhotosModel($app);
        $id = $model->getId();
        return $id;
    }



}
