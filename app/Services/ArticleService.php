<?php

namespace App\Services;

use App\User;
use App\Article;
use App\Enums\TransactionAmount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\NotEnoughChargeException;

class ArticleService
{
    /**
     * @param int $userId
     * @param array $parameters
     * @return Article|null
     * @throws NotEnoughChargeException
     */
    public function createArticle(int $userId, array $parameters): ?Article
    {
        $article = null;
        DB::beginTransaction();
        /** @var User $user */
        $user = User::query()->lockForUpdate()->whereKey($userId)->first();
        try {
            if ($user->charge < 0) {
                throw new NotEnoughChargeException();
            }

            /** @var Article $article */
            $article = $user->articles()->create($parameters);
            (new TransactionService())
                ->withdraw($user, TransactionAmount::ARTICLE_CREATION_WITHDRAW)
                ->createFactor($article);

            DB::commit();

        } catch (NotEnoughChargeException $exception) {
            DB::commit();
            throw new NotEnoughChargeException();
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            DB::rollback();
        }

        return $article;
    }
}