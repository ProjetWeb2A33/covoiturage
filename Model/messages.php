<?php

class Message
{
    private int $id;
    private string $content;
    private string $created_at;

    public function __construct(string $content, string $created_at = null, int $id = null)
    {
        $this->content = $content;
        $this->created_at = $created_at ?? date('Y-m-d H:i:s'); // Par défaut, date actuelle
        if ($id !== null) {
            $this->id = $id;
        }
    }

    // Getters
    public function getID(): ?int
    {
        return $this->id ?? null;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    // Setters
    public function setID(int $id): void
    {
        $this->id = $id;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function setCreatedAt(string $created_at): void
    {
        $this->created_at = $created_at;
    }
}

?>