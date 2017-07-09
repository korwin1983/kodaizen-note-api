<?php

namespace AppBundle\Controller;

use AppBundle\Entity\AuthToken;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User; //importation du modèle Project
use AppBundle\Entity\Validation;
use FOS\RestBundle\View\View; // utilisation de la vue de FOSRestBundle
use FOS\RestBundle\Controller\Annotations as Rest; //annotations pour FOSRestBundle
use AppBundle\Form\Type\UserType;
use AppBundle\Form\Type\UpdateUserType;
use AppBundle\Form\Type\ValidationType;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends Controller
{

    //activate user
    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Post("/users/activate")
     *
     */
    public function activateUserAction(Request $request)
    {

        $validation = new Validation();

        $form = $this->createForm(ValidationType::class, $validation);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $secretkey = $request->get('secretkey');
            $user = $this->get('doctrine.orm.entity_manager')
                ->getRepository('AppBundle:User')
                ->findOneBy(array('secretkey' => $secretkey));
            /* @var $user User */

            if (empty($user)) {
                return \FOS\RestBundle\View\View::create(['message' => 'Aucun compte n\'est associé à cette clé secrète.'], Response::HTTP_NOT_FOUND);
            }
            if ($user->getActive()) {
                return \FOS\RestBundle\View\View::create([
                    'success' => false,
                    'message' => 'Votre compte est déjà activé.'
                ], Response::HTTP_BAD_REQUEST);
            }

                $user->setActive(true);
                $user->setSecretKey(null);
                $em = $this->get('doctrine.orm.entity_manager');
                $em->persist($user);
                $em->flush();
                return \FOS\RestBundle\View\View::create([
                    'user' => $user,
                    'message' => 'Votre compte a bien été activé.'
                ], Response::HTTP_OK);


        } else {
            return $form;
        }


    }


    //add user

    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Post("/users")
     */
    public function postUsersAction(Request $request)
    {

        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->submit($request->request->all()); // Validation des données

        $email = $user->getEmail();
        $existingUser = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:User')
            ->findOneBy(array('email' => $email));

        if ($existingUser) {
            return \FOS\RestBundle\View\View::create(['message' => 'Le compte associé à cette adresse email est déjà créé.'], Response::HTTP_BAD_REQUEST);
        }

        if ($form->isValid()) {
            $role = $user->getRole();

            if ($role === "ROLE_ADMIN" && !$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                return \FOS\RestBundle\View\View::create(['message' => 'Droits insuffisants.'], Response::HTTP_FORBIDDEN);
            }
            $user->setRoles(array($role));

            $encoder = $this->get('security.password_encoder');
            // le mot de passe en claire est encodé avant la sauvegarde
            $encoded = $encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($encoded);
            $activationkey = uniqid('kdz');
            $user->setSecretKey($activationkey);
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();
            $this->sendUserCredentials($user->getEmail(), $user->getPlainPassword(), $user->getSecretKey());


            return \FOS\RestBundle\View\View::create(['message' => 'Votre compte a bien été créé, vous devez maintenant l\'activer.'], Response::HTTP_CREATED);
            return $user;
        } else {
            return \FOS\RestBundle\View\View::create(['message' => 'Données du formulaire invalides.'], Response::HTTP_BAD_REQUEST);
            return $form;
        }
    }


    //send a secret key to user

    /**
     * @Rest\Post("/users/sendsecretkey")
     */
    public function sendSecretKeyAction(Request $request)
    {
        $user = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:User')
            ->findOneBy(array('email' => $request->get('email')));
        /* @var $user User */

        if (empty($user)) {
            return \FOS\RestBundle\View\View::create(['message' => 'Aucun compte n\'est associé à cette adresse email.'], Response::HTTP_NOT_FOUND);
        }
        $form = $this->createForm(UserType::class, $user, []);
        $form->submit($request->request->all(), false);
        if ($form->isValid()) {
            $user->setSecretKey(uniqid('kdz'));
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();

            $this->sendSecretKey($user);
            return \FOS\RestBundle\View\View::create(['message' => 'La clé secrète a bien été envoyée.'], Response::HTTP_CREATED);
        }
        else {
            return \FOS\RestBundle\View\View::create(['message' => 'Erreur de validation.'], Response::HTTP_BAD_REQUEST);
        }
    }



    //get all users

    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Get("/users")
     */
    public function getUsersAction(Request $request)
    {

        // On vérifie que l'utilisateur dispose bien du rôle ROLE_ADMIN
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            // Sinon on déclenche une exception « Accès interdit »
            //  return \FOS\RestBundle\View\View::create(['message' => 'Droits insuffisants.'], Response::HTTP_FORBIDDEN);
        }

        $users = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:User')
            ->findAll();
        /* @var $users User[] */

        return $users;
    }


    //get user by id

    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Get("/users/{id}")
     */
    public function getUserAction($id, Request $request)
    {

        // On vérifie que l'utilisateur dispose bien du rôle ROLE_ADMIN
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            // Sinon on déclenche une exception « Accès interdit »
            return \FOS\RestBundle\View\View::create(['message' => 'Invalid access rights'], Response::HTTP_FORBIDDEN);
        }

        $user = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:User')
            ->find($id);
        /* @var $user User */

        if (empty($user)) {
            return $this->userNotFound();
        }
        return $user;
    }


    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Put("/users/{secretKey}")
     */
    public function updateUserAction(Request $request, $secretKey)
    {

        // On vérifie que l'utilisateur dispose bien du rôle ROLE_ADMIN
//        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
//            return \FOS\RestBundle\View\View::create(['message' => 'Invalid access rights'], Response::HTTP_FORBIDDEN);
//        }
        return $this->updateUser($request, true, $secretKey);
    }

    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Patch("/users/{secretKey}")
     */
    public function patchUserAction($secretKey, Request $request)
    {
//        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
//            return \FOS\RestBundle\View\View::create(['message' => 'Invalid access rights'], Response::HTTP_FORBIDDEN);
//        }
        return $this->updateUser($request, false, $secretKey);
    }


    private function updateUser(Request $request, $clearMissing, $secretKey)
    {
        $user = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:User')
            ->findOneBy(array('secretkey' => $secretKey));
        /* @var $user User */

        if (empty($user)) {
            return \FOS\RestBundle\View\View::create(['message' => 'Clé de sécurité incorrecte.'], Response::HTTP_BAD_REQUEST);
        }

        if ($clearMissing) {
            $options = ['validation_groups' => ['Default', 'FullUpdate']];
        } else {
            $options = [];
        }

        $form = $this->createForm(UpdateUserType::class, $user, $options);

        $form->submit($request->request->all(), $clearMissing);

        if ($form->isValid()) {
            if (!empty($user->getPlainPassword())) {
                $encoder = $this->get('security.password_encoder');
                $encoded = $encoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($encoded);
            }
            $user->setSecretKey(null);
            $em = $this->get('doctrine.orm.entity_manager');
            $em->merge($user);
            $em->flush();
            return $user;
        } else {
            return $form;
        }
    }


    //delete user

    /**
     * @Rest\View()
     * @Rest\Delete("/users/{secretKey}")
     */
    public function removeUserAction($secretKey, Request $request)
    {

        // On vérifie que l'utilisateur dispose bien du rôle ROLE_ADMIN
//        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            // Sinon on déclenche une exception « Accès interdit »
            // return \FOS\RestBundle\View\View::create(['message' => 'Droits insuffisants.'], Response::HTTP_FORBIDDEN);
//        }

        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('AppBundle:User')
            ->findOneBy(array('secretkey' => $secretKey));
        /* @var $user User */

        if ($user) {
            $em->remove($user);
            $em->flush();
            return \FOS\RestBundle\View\View::create(['message' => 'Votre compte a bien été supprimé.'], Response::HTTP_NO_CONTENT);
        } else {
            return \FOS\RestBundle\View\View::create(['message' => 'Clé de sécurité incorrecte.'], Response::HTTP_BAD_REQUEST);
        }
    }


    //FUNCTIONS

    private function sendUserCredentials($login, $password, $secretkey)
    {

        if ($login) {
            $subject = "Informations compte Kodaizen Note";
            $data = array('login' => $login, 'password' => $password, 'secretkey' => $secretkey);
        } else {
            $subject = "Clé secrète Kodaizen Note";
            $data = array('secretkey' => $secretkey);
        }
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom('no-reply@kodaizen.com')
            ->setTo($login)
            ->setBody(
                $this->renderView(
                // app/Resources/views/Emails/registration.html.twig
                    'sendCredentials.html.twig',
                    $data
                ),
                'text/html'
            );
        $this->get('mailer')->send($message);
    }

    private function sendSecretKey($user)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Clé secrète Kodaizen Note')
            ->setFrom('no-reply@kodaizen.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                // app/Resources/views/Emails/unregistration.html.twig
                    'Emails/unregistration.html.twig',
                    array('secretkey' => $user->getSecretKey())
                ),
                'text/html'
            );
        $this->get('mailer')->send($message);
    }

    private
    function userNotFound()
    {
        //return \FOS\RestBundle\View\View::create(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('User not found');
    }


}