<?php

namespace App\Entity;

use App\Entity\Shared\UUID;
use App\Repository\CrontabRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CrontabRepository::class)]
class Crontab
{
    use UUID;

    #[ORM\Column(length: 255)]
    private ?string $expression = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $last_execution = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $next_execution = null;

    #[ORM\Column(length: 255)]
    private ?string $command = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $result = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $arguments = null;

    public function getCommand(): ?string
    {
        return $this->command;
    }

    /**
     * @return string|null
     */
    public function getExpression(): ?string
    {
        return $this->expression;
    }

    public function getLastExecution(): ?\DateTimeImmutable
    {
        return $this->last_execution;
    }

    public function getNextExecution(): ?\DateTimeImmutable
    {
        return $this->next_execution;
    }

    public function setCommand(string $command): self
    {
        $this->command = $command;

        return $this;
    }

    /**
     * @param string|null $expression
     */
    public function setExpression(?string $expression): self
    {
        $this->expression = $expression;

        return $this;
    }

    public function setLastExecution(?\DateTimeImmutable $last_execution): self
    {
        $this->last_execution = $last_execution;

        return $this;
    }

    public function setNextExecution(?\DateTimeImmutable $next_execution): self
    {
        $this->next_execution = $next_execution;

        return $this;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(?string $result): self
    {
        $this->result = $result;

        return $this;
    }

    public function getArguments(): ?string
    {
        return $this->arguments;
    }

    public function setArguments(?string $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }
}
