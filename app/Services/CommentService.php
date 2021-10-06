<?php

namespace App\Services;

use App\Article;
use App\Comment;
use App\Enums\TransactionKey;
use App\Exceptions\NotEnoughChargeException;
use App\Setting;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommentService
{
    public function addComment(Article $article, User $user, string $body): ?Comment
    {
        $comment = null;
        DB::beginTransaction();
        try {
            $commentCount = $user->comments()->lockForUpdate()->count();

            // check if the user can submit a comment
            if ($commentCount >= Comment::MAX_NUMBER_OF_FREE_COMMENT && $user->charge < 0) {
                throw new NotEnoughChargeException();
            }

            /** @var Comment $comment */
            $comment = $article->comments()->create([
                'body' => $body,
                'user_id' => $user->id,
            ]);

            if ($commentCount >= Comment::MAX_NUMBER_OF_FREE_COMMENT) {
                (new TransactionService())
                    ->withdraw($user, Setting::get(TransactionKey::COMMENT_CREATION_WITHDRAW))
                    ->createFactor($comment);
            }

            DB::commit();
        } catch (NotEnoughChargeException $exception) {
            DB::commit();
            throw new NotEnoughChargeException();
        } catch (\Exception $exception) {
            $comment = null;
            Log::error($exception->getMessage());
            DB::rollback();
        }

        return $comment;
    }
}