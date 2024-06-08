<?php

namespace App\Model;

class Job
{
    private int $id;
    private string $title;
    private string $description;
    private bool $draft;
    private bool $active;

    public function __construct(int $id, string $title, string $description, bool $draft, bool $active)
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->draft = $draft;
        $this->active = $active;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function isDraft(): bool
    {
        return $this->draft;
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
