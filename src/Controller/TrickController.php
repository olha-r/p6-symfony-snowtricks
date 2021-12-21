<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Form\CommentType;
use App\Form\TrickType;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/trick")
 */
class TrickController extends AbstractController
{

    /**
     * @Route("/new", name="trick_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $trick = new Trick();
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trick->setCreatedAt(new \DateTime());
            $trick->setUpdatedAt(new \DateTime());

            $entityManager->persist($trick);
            $entityManager->flush();

            return $this->redirectToRoute('trick_show', ['id' => $trick->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('trick/new.html.twig', [
            'trick' => $trick,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="trick_show", methods={"GET", "POST"})
     */
    public function show($id, Trick $trick, CommentRepository $commentRepository, EntityManagerInterface $entityManager, Request $request, Security $security): Response
    {
        $comment = $commentRepository->findBy([
            'trick' => $id
        ]);

        $new_comment = new Comment();
        $form = $this->createForm(CommentType::class, $new_comment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $new_comment->setCreatedAt(new \DateTime())
                ->setTrick($trick)
                ->setUser($security->getUser())
                ->setContent($new_comment->getContent());
            $entityManager->persist($new_comment);
            $entityManager->flush();

            return $this->redirectToRoute('trick_show', ['id' => $trick->getId()]);

        }

        return $this->renderForm('trick/show.html.twig', [
            'trick' => $trick,
            'comments' => $comment,
            'commentForm' => $form
        ]);
    }


    /**
     * @Route("/{id}/edit", name="trick_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Trick $trick, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trick->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            return $this->redirectToRoute('trick_show', ['id' => $trick->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('trick/edit.html.twig', [
            'trick' => $trick,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="trick_delete", methods={"POST"})
     */
    public function delete(Request $request, Trick $trick, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $trick->getId(), $request->request->get('_token'))) {
            $entityManager->remove($trick);
            $entityManager->flush();
        }

        return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
    }
}
