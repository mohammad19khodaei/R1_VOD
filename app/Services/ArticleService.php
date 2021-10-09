<?php

namespace App\Services;

use App\Article;
use App\Enums\SettingKey;
use App\Exceptions\NotEnoughBalanceException;
use App\User;
use Illuminate\Support\Facades\DB;

class ArticleService
{
    /**
     * @param int $userId
     * @param array $parameters
     * @return Article
     * @throws NotEnoughBalanceException
     */
    public function createArticle(int $userId, array $parameters): Article
    {
        DB::transaction(function () use ($userId, $parameters, &$article) {
            /** @var User $user */
            $user = User::query()->lockForUpdate()->whereKey($userId)->first();

            if (!(new UserBalanceService($user))->canSubmitArticle()) {
                throw new NotEnoughBalanceException();
            }

            /** @var Article $article */
            $article = $user->articles()->create($parameters);

            $transaction = (new TransactionService($user))->withdraw(setting(SettingKey::ARTICLE_CREATION_WITHDRAW));
            (new FactorService($transaction))->create($article);

        });

        return $article;
    }
}