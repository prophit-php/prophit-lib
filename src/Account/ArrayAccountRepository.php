<?php

namespace Prophit\Core\Account;

use Prophit\Core\Exception\AccountNotFoundException;

class ArrayAccountRepository implements AccountRepository
{
    /** @var array<string, Account> **/
    private array $accounts;

    public function __construct(Account... $accounts)
    {
        $this->accounts = [];
        foreach ($accounts as $account) {
            $this->saveAccount($account);
        }
    }

    public function saveAccount(Account $account): void
    {
        $this->accounts[$account->getId()] = $account;
    }

    public function getAccountById(string $id): Account
    {
        if (!isset($this->accounts[$id])) {
            throw new AccountNotFoundException($id);
        }
        return $this->accounts[$id];
    }

    public function getAllAccounts(): AccountIterator
    {
        return new AccountIterator(...$this->accounts);
    }

    public function searchAccounts(AccountSearchCriteria $criteria): AccountIterator
    {
        $ids = $criteria->getIds();
        if ($ids !== null) {
            $accounts = array_map(fn(string $id): Account => $this->getAccountById($id), $ids);
            return new AccountIterator(...$accounts);
        }

        $name = $criteria->getName();
        if ($name !== null) {
            $accounts = array_filter(
                $this->accounts,
                fn(Account $account): bool => $account->getName() === $name,
            );
            return new AccountIterator(...$accounts);
        }

        $parentIds = $criteria->getParentIds();
        if ($parentIds !== null) {
            $accounts = array_filter(
                $this->accounts,
                fn(Account $account): bool => in_array($account->getParentId(), $parentIds),
            );
            return new AccountIterator(...$accounts);
        }

        return $this->getAllAccounts();
    }
}
