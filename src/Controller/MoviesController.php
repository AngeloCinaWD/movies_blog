<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Form\MovieFormType;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Repository\RepositoryFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;

class MoviesController extends AbstractController
{
    private EntityManagerInterface $em;
    private MovieRepository $movieRepository;

    public function __construct(EntityManagerInterface $em, MovieRepository $movieRepository)
    {
        $this->em = $em;
        $this->movieRepository = $movieRepository;
    }

        #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->redirectToRoute('app_login');
    }

//    #[Route('/movies', name: 'app_movies')]
//    public function index(): Response
//    {
//        $movies = ['Rambo', 'Rambo 2', 'Rambo 3'];
//
//        return $this->render('movies/index.html.twig', [
//            'nome' => 'Angelo',
//            'movies' => $movies
//        ]);
//    }

//    #[Route('/repomovies', name: 'repo_movies')]
//    public function tryRepoMovies(MovieRepository $movieRepository): Response
//    {
//        $movies = $movieRepository->findAll();
//
//        dd($movies);
//
//        return $this->render('base.html.twig');
//    }

//    // passo l'oggetto EntityManagerInterface come argomento del metodo
//    // l'ooggetto viene chiamato solo quando chiamo questo metodo
//    #[Route('/entitymanagermovies', name: 'entity_manager_movies')]
//    public function tryEntityManagerMovies(EntityManagerInterface $em): Response
//    {
//        $movies = $em->getRepository(Movie::class)->findAll();
//
//        dd($movies);
//
//        return $this->render('base.html.twig');
//    }

//    // creo una proprietà privata $em alla quale attribuisco come valore l'oggetto EntityManagerInterface tramite costruttore
//    // quindi l'oggetto verrà chiamato ogni volta che viene chiamata la classe MoviesController e sarà utilizzabile all'interno di tutta la classe
//    #[Route('/entitymanagermovies', name: 'entity_manager_movies')]
//    public function tryEntityManagerMovies(): Response
//    {
//        // definisco la repository in una variabile
////        $repository = $this->em->getRepository('App\Entity\Movie');
////        $repository = $this->em->getRepository('App:Movie');
//        $repository = $this->em->getRepository(Movie::class);
//
//        // metodo findAll corrisponde al SELECT * from movies;
//        $movies = $repository->findAll();
//
//        dump($movies);
//
//        // metodo find, cerca per id, corrisponde al SELECT * from movie where id=?;
//        $movie = $repository->find(5);
//
//        dump($movie);
//
//        // metodo findBy, cerca secondo criteri, restituisce un array, corrisponde al SELECT * from movie where release_year = 2008 order by id desc
//        // limit e offset si utilizzano per la paginazione, quanti prenderne e da dove iniziare a prendere
//        $movies2 = $repository->findBy(
//            [
//            'releaseYear' => 2008
//            ],
//            [
//             'id' => 'desc'
//            ],
//            null,
//            null
//        );
//
//        dump($movies2);
//
//        // metodo findOneBy, cerca le rows secondo determinati criteri, prende il primo dei risultati
//        // a seconda di come utilizziamo il secondo argomento order by, decidiamo se prendere il primo o l'ultimo dei risultati, come se fosse findBy() con limit 1
//        // ritorna null se non trova nulla
//        $movie2 = $repository->findOneBy(
//            ['releaseYear' => 2008],
//            ['id' => 'asc']
//        );
//
//        dump($movie2);
//
//        // metodo count, con array di criteri di ricerca vuoto come argomento, dà il totale degli elementi, SELECT COUNT(*) FROM movie
//        // altrimenti passando un array con criteri di ricerca conta quelli corrispondenti, SELECT COUNT(*) FROM movie WHERE id = ?
//        // se non trova nulla restituisce 0
//        $count = $repository->count([]);
//
//        dump($count);
//
//        return $this->render('base.html.twig');
//    }

//    #[Route('/old', name: 'old')]
//    public function oldMethod() :Response
//    {
//        return $this->json([
//            'nome' => 'Angelo',
//            'cognome' => 'Cinà'
//        ]);
//    }

//    #[Route('/old/{name?}', name: 'old_name', methods: ['GET', 'HEAD'])]
//    public function oldMethodName(Request $request, ?string $name, #[MapQueryParameter] ?int $anno): Response
//    {
//        return $this->json([
//            $request->query->all(),
//            $anno,
//            $name ?? 'isnull'
//        ]);
//    }

    #[Route('/movies', name: 'movies', methods: ['GET'])]
    public function index(): Response
    {
        $movies = $this->movieRepository->findAll();

        return $this->render('movies/index.html.twig', [
            'movies' => $movies
        ]);
    }

    #[Route('/movies/{id}', name: 'movie', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show($id = null): Response
    {
        $movie = $this->movieRepository->find($id);

        if ($movie) {
            return $this->render('movies/show.html.twig', [
                'movie' => $movie
            ]);
        }

        return $this->json('Film non trovato');
    }

    #[Route('/movies/create', name: 'create_movie')]
    public function create(Request $request): Response
    {
        $movie = new Movie();
        $form = $this->createForm(MovieFormType::class, $movie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //$movie = $form->getData();

            $imagePath = $form->get('imagePath')->getData();

            if ($imagePath) {
                // questi metodi sono della classe uploaded file che estende file, Symfony\Component\HttpFoundation\File
                // https://symfony.com/doc/current/reference/forms/types/file.html
                // When the form is submitted, the attachment field will be an instance of UploadedFile. It can be used to move the attachment file to a permanent location:
                $newFileName =uniqid() . '.' . $imagePath->guessExtension();

                try {
                    $imagePath->move(
//                        $this->getParameter('kernel.project_dir') . '/public/uploads',
                        $this->getParameter('uploads_dir'),
                        $newFileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $movie->setImagePath('/uploads/' . $newFileName);
            }

            $this->em->persist($movie);
            $this->em->flush();

            return $this->redirectToRoute('movies');
        }

        return $this->render('movies/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/movies/edit/{id}', name: 'edit_movie', requirements: ['id' => '\d+'])]
    public function edit($id, Request $request): Response
    {
        $movie = $this->movieRepository->find($id);

        $form = $this->createForm(MovieFormType::class, $movie);

        $form->handleRequest($request);
        $imagePath = $form->get('imagePath')->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            if ($imagePath) {
                if ($movie->getImagePath() !== null) {
                    if (file_exists($this->getParameter('public_dir'). $movie->getImagePath())) {
                        $newFileName = uniqid() . '.' . $imagePath->guessExtension();
                        try {
                            $imagePath->move(
                                $this->getParameter('uploads_dir'),
                                $newFileName
                            );
                        } catch (FileException $e) {
                            return new Response($e->getMessage());
                        }

                        $movie->setImagePath('/uploads/' . $newFileName);
                    }
                }
            }

            // questi set non servono:
            // I'm a newbie at Symfony but at 10:44 where you add the set methods, aren't they redundant?
            // You already get the submitted form data via the $request and store it in the $form object so it will get saved to the db when you run $this->em->flush().
            // That is why the title, year and description is updated when you add an image even though you do not use the set methods in that condition.
            // Removing the three lines of set methods doesn't seem to affect anything?
//            else {
//                $movie->setTitle($form->get('title')->getData());
//                $movie->setReleaseYear($form->get('releaseYear')->getData());
//                $movie->setDescription($form->get('description')->getData());
//            }

            $this->em->flush();
            return $this->redirectToRoute('movies');
        }

        return $this->render('movies/edit.html.twig', [
           'form' => $form->createView(),
            'movie' => $movie
        ]);
    }

    #[Route('/movies/delete/{id}', name: 'delete_movie', requirements: ['id' => '\d+'])]
    public function delete($id): Response
    {
        $movie = $this->movieRepository->find($id);

        if ($movie->getImagePath()) {
            if (file_exists($this->getParameter('public_dir') . $movie->getImagePath())) {
                try {
                    unlink($this->getParameter('public_dir') . $movie->getImagePath());
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }
            }
        }

        $this->em->remove($movie);

        $this->em->flush();

        return $this->redirectToRoute('movies');
    }
}
