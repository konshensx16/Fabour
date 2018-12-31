<?php

namespace App\Controller;

use App\Form\UserFormType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     * @param AuthenticationUtils $utils
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function login(AuthenticationUtils $utils)
    {
        $error = $utils->getLastAuthenticationError();
        $lastUsername = $utils->getLastUsername();

        $form = $this->createForm(UserFormType::class, null, [
            'action' => $this->generateUrl('login'),
        ]);

        return $this->render('security/login.html.twig', [
            'error' 		=> $error,
            'last_username'	=> $lastUsername,
            'form'          => $form->createView()
        ]);
    }
    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
    }
}