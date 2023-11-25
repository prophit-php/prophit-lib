<?php

namespace Prophit\Core\Tests\Transaction;

use Prophit\Core\{
    Date\DateRange,
    Transaction\Posting,
    Transaction\Transaction,
};

use DateInterval;
use DateTime;
use DateTimeInterface;

use function Pest\Faker\fake;

class TransactionFactory
{
    private PostingFactory $postingFactory;

    private int $lastId;

    public function __construct()
    {
        $this->postingFactory = new PostingFactory;
        $this->lastId = 0;
    }

    /**
     * @param Posting[]|null $postings
     */
    public function create(
        ?string $id = null,
        DateTimeInterface|DateRange|null $transactionDates = null,
        ?array $postings = null,
        ?string $description = null,
    ): Transaction {
        $id ??= (string) ++$this->lastId;

        $transactionDates ??= new DateTime(
            fake()
                ->dateTimeBetween('-2 months')
                ->format('Y-m-d 00:00:00')
        );

        if ($postings === null) {
            $clearedBeforeDate = $transactionDates instanceof DateRange
                ? $transactionDates->getEndDate()
                : $transactionDates;
            $clearedDate = new DateTime(
                fake()
                    ->dateTimeInInterval($clearedBeforeDate->format('Y-m-d'))
                    ->format('Y-m-d 00:00:00'),
            );
            $postings = [
                $this->postingFactory->create(clearedDate: $clearedDate),
                $this->postingFactory->create(clearedDate: $clearedDate),
            ];
        }

        if ($description === null) {
            /** @var string $description */
            $description = fake()->words(3, true);
        }

        return new Transaction(
            $id,
            $transactionDates,
            $postings,
            $description,
        );
    }

    /**
     * @return Transaction[]
     */
    public function count(int $count): array
    {
        return array_map(fn() => $this->create(), range(1, $count));
    }
}
