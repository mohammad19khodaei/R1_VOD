<?php

namespace App\Services;

use App\Article;
use App\Comment;
use App\Enums\SettingKey;
use App\Exceptions\NotEnoughBalanceException;
use App\User;
use Illuminate\Support\Facades\DB;

class CommentService
{
    /**
     * @param Article $article
     * @param User $user
     * @param string $body
     * @return Comment
     * @throws NotEnoughBalanceException
     */
    public function addComment(Article $article, User $user, string $body): Comment
    {
        DB::transaction(function () use ($article, $user, $body, &$comment) {
            $commentCount = $user->comments()->lockForUpdate()->count();

            if (!(new UserBalanceService($user))->canSubmitComment($commentCount)) {
                throw new NotEnoughBalanceException();
            }

            /** @var Comment $comment */
            $comment = $article->comments()->create([
                'body' => $body,
                'user_id' => $user->id,
            ]);

            if ($commentCount >= setting(SettingKey::MAX_NUMBER_OF_FREE_COMMENT)) {
                $transaction = (new TransactionService($user))
                    ->withdraw(setting(SettingKey::COMMENT_CREATION_WITHDRAW));
                (new FactorService($transaction))->create($comment);
            }
        });

        return $comment;
    }
}