<?php

namespace App\Model;

class Job
{

    public function __construct(private int $id, private string $title, private string $description, private string $date_created)
    {}

    /**
     * Vrátí ID inzerátu
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Vrátí nadpis inzerátu
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Vrátí popis inzerátu
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /** 
     * Vrátí datum vytvoření inzerátu
     *
     * @return string
     */
    public function getDateCreated(): string
    {
        $timestamp = date("d.m.Y H:m", strtotime(date($this->date_created)));
        return $timestamp;
    }
}
