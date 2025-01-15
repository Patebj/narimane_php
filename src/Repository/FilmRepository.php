<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\DatabaseConnection;
use App\Service\EntityMapper;
use App\Entity\Film;
use PDO;

class FilmRepository
{
    private PDO $db;
    private EntityMapper $entityMapperService;

    public function __construct()
    {
        $this->db = DatabaseConnection::getConnection();
        $this->entityMapperService = new EntityMapper();
    }

    public function findAll(): array
    {
        $query = 'SELECT * FROM film';
        $stmt = $this->db->query($query);
        $films = $stmt->fetchAll();
        return $this->entityMapperService->mapToEntities($films, Film::class);
    }

    public function find(int $id): Film
    {
        $query = 'SELECT * FROM film WHERE id = :id';
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        $film = $stmt->fetch();
        return $this->entityMapperService->mapToEntity($film, Film::class);
    }

    private function executeQuery(string $query, array $params = []): void
    {
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
    }

    public function createFilm(Film $film): void
    {
        $sql = "INSERT INTO film (title, year, type, synopsis, director, created_at, updated_at) 
                VALUES (:title, :year, :type, :synopsis, :director, :createdAt, :updatedAt)";
        
        $params = [
            'title' => $film->getTitle(),
            'year' => $film->getYear(),
            'type' => $film->getType(),
            'synopsis' => $film->getSynopsis(),
            'director' => $film->getDirector(),
            'createdAt' => $film->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $film->getUpdatedAt() ? $film->getUpdatedAt()->format('Y-m-d H:i:s') : null,
        ];

        $this->executeQuery($sql, $params);
    }

    public function deleteFilm(Film $film): void
    {
        $sql = "DELETE FROM film WHERE id = :id";
        $this->executeQuery($sql, ['id' => $film->getId()]);
    }

    public function updateFilm(Film $film): void
    {
        $sql = "UPDATE film 
                SET title = :title, 
                    year = :year, 
                    type = :type, 
                    synopsis = :synopsis, 
                    director = :director, 
                    updated_at = :updatedAt 
                WHERE id = :id";
        
        $params = [
            'id' => $film->getId(),
            'title' => $film->getTitle(),
            'year' => $film->getYear(),
            'type' => $film->getType(),
            'synopsis' => $film->getSynopsis(),
            'director' => $film->getDirector(),
            'updatedAt' => $film->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];

        $this->executeQuery($sql, $params);
    }
}
