<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Response\ValueObject;

final class Banner
{
    private string $id;

    private string $name;

    /** @var int|float */
    private $score;

    private ?string $campaignId;

    private ?string $campaignCode;

    private ?string $campaignName;

    private ?int $closedExpiration;

    /** @var array<int, ContentInterface> */
    private array $contents;

    /**
     * @param int|float                    $score
     * @param array<int, ContentInterface> $contents
     */
    public function __construct(
        string $id,
        string $name,
        $score,
        ?string $campaignId,
        ?string $campaignCode,
        ?string $campaignName,
        ?int $closedExpiration,
        array $contents
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->score = $score;
        $this->campaignId = $campaignId;
        $this->campaignCode = $campaignCode;
        $this->campaignName = $campaignName;
        $this->closedExpiration = $closedExpiration;
        $this->contents = $contents;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return float|int
     */
    public function getScore()
    {
        return $this->score;
    }

    public function getCampaignId(): ?string
    {
        return $this->campaignId;
    }

    public function getCampaignCode(): ?string
    {
        return $this->campaignCode;
    }

    public function getCampaignName(): ?string
    {
        return $this->campaignName;
    }

    public function getClosedExpiration(): ?int
    {
        return $this->closedExpiration;
    }

    /**
     * @return array<int, ContentInterface>
     */
    public function getContents(): array
    {
        return $this->contents;
    }
}
