<?php

namespace App\Services;

use App\Article;
use App\Comment;
use App\Enums\SettingKey;
use App\Exceptions\NotEnoughBalanceException;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommentService
{
    /**
     * @param Article $article
     * @param User $user
     * @param string $body
     * @return Comment|null
     * @throws NotEnoughBalanceException
     */
    public function addComment(Article $article, User $user, string $body): ?Comment
    {
        DB::beginTransaction();
        $commentCount = $user->comments()->lockForUpdate()->count();

        try {
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

            DB::commit();
        } catch (NotEnoughBalanceException $exception) {
            DB::commit();
            throw $exception;
        } catch (\Exception $exception) {
            $comment = null;
            Log::error($exception->getMessage());
            DB::rollback();
        }

        return $comment;
    }
}