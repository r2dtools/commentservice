<?php

namespace CommentService;

class Comment
{
    /**
     * @var int|null
     */
    private ?int $id;

    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $text;

    /**
     * @param string $name
     * @param string $text
     * @param int|null $id
     */
    public function __construct(string $name, string $text, int $id = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->text = $text;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Comment
     */
    public function setName(string $name): Comment
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return Comment
     */
    public function setText(string $text): Comment
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'text' => $this->text,
            'id' => $this->id,
        ];
    }
}
