<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Form\LivreType;
use App\Repository\AuteurRepository;
use App\Repository\GenreRepository;
use App\Repository\LivreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class LivreController extends AbstractController
{
    /**
     * @Route("/", name="livre_index", methods={"GET"})
     */
    public function index(LivreRepository $livreRepository, GenreRepository $genreRepository, AuteurRepository $auteurRepository): Response
    {
        return $this->render('livre/index.html.twig', [
            'livres' => $livreRepository->findAll(),
            'genres' => $genreRepository->findAll(),
            'auteurs' => $auteurRepository->findAll(),
            'dates' => $livreRepository->findDates(),
        ]);
    }

    /**
     * @Route("/new", name="livre_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $livre = new Livre();
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($livre);
            $entityManager->flush();

            return $this->redirectToRoute('livre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('livre/new.html.twig', [
            'livre' => $livre,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="livre_show", methods={"GET"})
     */
    public function show(Livre $livre): Response
    {
        return $this->render('livre/show.html.twig', [
            'livre' => $livre,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="livre_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, Livre $livre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('livre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('livre/edit.html.twig', [
            'livre' => $livre,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="livre_delete", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, Livre $livre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$livre->getId(), $request->request->get('_token'))) {
            $entityManager->remove($livre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('livre_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/livre/liste-livres-par-titre={keyword}", name="livre_liste", methods={"GET"})
     */
    public function listByTitle(String $keyword, LivreRepository $livreRepository): Response {
        return $this->render('livre/find.html.twig', [
            'livres' => $livreRepository->findByTitre($keyword)
        ]);
    }

    /**
     * @Route("/livre/liste-livres-date-entre-{dateMin}-et-{dateMax}", name="liste_entre_deux_dates", methods={"GET"})
     */
    public function listeLivresEntreDeuxDates(String $dateMin, String $dateMax, LivreRepository $livreRepository): Response {
        
        $livres = $livreRepository->findBetweenTwoDates(strval($dateMin), strval($dateMax));

        return $this->render('livre/find.html.twig', [
            'livres' => $livres
        ]);
    }

    /**
     * @Route("/livre/liste-par-date/{date}/", name="liste_par_date", methods={"GET"})
     */
    public function listByDate(LivreRepository $livreRepository, $date): Response {
        return $this->render('livre/chercher.html.twig', [
            'livres' => $livreRepository->findByDate($date)
        ]);
    }
    
    /**
     * @Route("/livre/liste-livres-par-note={note}", name="liste_par_note", methods={"GET"})
     */
    public function listByNote(int $note, LivreRepository $livreRepository): Response {
        return $this->render('livre/find.html.twig', [
            'livres' => $livreRepository->findByNote($note)
        ]);
    }

    /**
     * @Route("/livre/liste-par-auteur/{auteur}/", name="liste_par_auteur", methods={"GET"})
     */
    public function listByAuteur(LivreRepository $livreRepository, $auteur): Response {
        return $this->render('livre/find.html.twig', [
            'livres' => $livreRepository->findByAuteur($auteur)
        ]);
    }

    /**
     * @Route("/livre/liste-par-genre/{genre}/", name="liste_par_genre", methods={"GET"})
     */
    public function listByGenre(LivreRepository $livreRepository, $genre): Response {
        return $this->render('livre/chercher.html.twig', [
            'livres' => $livreRepository->findByGenre($genre)
        ]);
    }
}
