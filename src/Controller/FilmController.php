<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\TemplateRenderer;
use App\Entity\Film;
use App\Repository\FilmRepository;

class FilmController
{
    private TemplateRenderer $renderer;
    private FilmRepository $filmRepository;

    public function __construct()
    {
        $this->renderer = new TemplateRenderer();
        $this->filmRepository = new FilmRepository();
    }

    private function isPostRequest(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    private function sanitize(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    private function populateFilm(Film $film, array $data): Film
    {
        return $film->setTitle($this->sanitize($data['title'] ?? ''))
                    ->setYear($this->sanitize($data['year'] ?? null))
                    ->setType($this->sanitize($data['type'] ?? ''))
                    ->setSynopsis($this->sanitize($data['synopsis'] ?? null))
                    ->setDirector($this->sanitize($data['director'] ?? null));
    }

    private function renderNotFound(): void
    {
        echo $this->renderer->render('errors/404.html.twig');
        exit();
    }

    public function list(array $queryParams)
    {
        $films = $this->filmRepository->findAll();
        echo $this->renderer->render('film/list.html.twig', ['films' => $films]);
    }

    public function create() : void
    {
        if ($this->isPostRequest()) {
            $film = $this->populateFilm(new Film(), $_POST);
            $film->setCreatedAt(new \DateTime());
            $this->filmRepository->createFilm($film);

            header('Location: /film/list');
            exit();
        }

        echo $this->renderer->render('film/create.html.twig');
    }

    public function delete(array $queryParams) : void
    {
        if ($this->isPostRequest()) {
            $film = $this->filmRepository->find((int) $queryParams['id']);
            if ($film) {
                $this->filmRepository->deleteFilm($film);
                header('Location: /film/list');
                exit();
            } else {
                $this->renderNotFound();
            }
        }

        echo $this->renderer->render('film/delete.html.twig', ['id' => $queryParams['id']]);
    }

    public function update(array $queryParams) : void
    {
        $film = $this->filmRepository->find((int) $queryParams['id']);
        if (!$film) {
            $this->renderNotFound();
        }

        if ($this->isPostRequest()) {
            $film = $this->populateFilm($film, $_POST);
            $film->setUpdatedAt(new \DateTime());
            $this->filmRepository->updateFilm($film);

            header('Location: /film/list');
            exit();
        }

        echo $this->renderer->render('film/update.html.twig', ['film' => $film]);
    }

    public function read(array $queryParams) : void
    {
        $film = $this->filmRepository->find((int) $queryParams['id']);
        if ($film) {
            echo $this->renderer->render('film/read.html.twig', ['film' => $film]);
        } else {
            $this->renderNotFound();
        }
    }
}
