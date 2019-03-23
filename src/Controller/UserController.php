<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route(name="security.")
 * Class UserController
 * @package App\Controller
 */
class UserController extends AbstractController
{

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, UserRepository $userRepository)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
    }

    /**
     * NOTE : this will just display the form so the user can enter his email and get a notification
     * @Route("/reset", name="reset.password")
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resetPassword(Request $request, \Swift_Mailer $mailer)
    {
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class)
            ->add('submit', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn btn-success pull-right'
                    ]
                ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $email = ($form->getData()['email']);
            // check if the user exists in the database
            $user = $this->userRepository->findOneBy(['email' => $email]);

            if ($user) {
                // send the actual message and set the
                $em = $this->getDoctrine()->getManager();
                $uniqueToken = md5(uniqid());

                $user->setResetPasswordToken($uniqueToken);

                $em->persist($user);
                $em->flush();

                $message = (new \Swift_Message())
                    ->setFrom('no-reply@admin.com')
                    ->setTo($email)
                    ->setBody(
                        $this->renderView('email/forgotPassword.html.twig',
                            [
                                'token' => $uniqueToken,
                                'email' => $email
                            ])
                    )
                ;

                $mailer->send($message);

                $this->addFlash('success', 'An email has just been sent to you :) ');
            } else {
                $this->addFlash('error', 'This email is not associated with any account');
            }

        }

        return $this->render('security/passwordReset.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/validate", name="validate")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resetPasswordToken(Request $request)
    {
        $token = $request->get('token');
        $email = $request->get('email');

        if ($token && $email) {
            $form = $this->createFormBuilder()
                ->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'invalid_message' => 'The password fields must match.',
                    'options' => ['attr' => ['class' => 'password-field', 'autocomplete' => false]],
                    'required' => true,
                    'first_options'  => ['label' => 'Password'],
                    'second_options' => ['label' => 'Confirm Password'],
                ])
                ->add('save', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn btn-primary pull-right'
                    ]
                ])
                ->getForm()
            ;

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                dump($form->getData());
                /** @var User $user */
                $user = $this->userRepository->findOneBy([
                    'email' => $email,
                ]);
                if ($user) {
                    // FIXME: is this okay or should i always get it using DI ??
                    $em = $this->getDoctrine()->getManager();

                    // update the user with the new password
                    $password = $form->getData()['password'];

                    $user->setPassword(
                        $this->passwordEncoder->encodePassword($user, $password)
                    );

                    // clear the reset token,
                    $user->setResetPasswordToken(null);
                    // update the last passwordReset datetime
                    $user->setLastPasswordReset(new \DateTime());

                    $em->persist($user);
                    $em->flush();

                    $this->addFlash('success', 'Use your new password to login');
                    return $this->redirect($this->generateUrl('login'));
                }

                $this->addFlash('error', 'Something wen\'t wrong, please try again.');
            }

            return $this->render('security/passwordReset.html.twig', [
                'form' => $form->createView()
            ]);
        }

        return $this->redirect($this->generateUrl('security.reset.password'));
    }

}