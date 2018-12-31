<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRegistrationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Guard\Provider\GuardAuthenticationProvider;

/**
 * @Route("/signup", name="register.")
 * Class RegistrationController
 * @package App\Controller
 */
class RegistrationController extends AbstractController
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/", name="createAccount")
     * @param Request $request
     * @param GuardAuthenticatorHandler $handler
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function signUp(Request $request, GuardAuthenticatorHandler $handler)
    {
        $em = $this->getDoctrine()->getManager();
        $user = new User();

        $form = $this->createForm(UserRegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $this->passwordEncoder->encodePassword($user, $user->getPassword()
            ));

            $em->persist($user);
            $em->flush();

            // TODO: log the user in and redirect him to the profile edit page
//            $handler->authenticateUserAndHandleSuccess(
//                $user,
//                $request,
//            );
        }

        return $this->render('registration/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
