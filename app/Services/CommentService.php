<?php

namespace App\Services;

use App\Article;
use App\Comment;
use App\Enums\SettingKey;
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
            if ($user->charge < 0 && $commentCount >= Setting::get(SettingKey::MAX_NUMBER_OF_FREE_COMMENT)) {
                throw new NotEnoughChargeException();
            }

            /** @var Comment $comment */
            $comment = $article->comments()->create([
                'body' => $body,
                'user_id' => $user->id,
            ]);

            if ($commentCount >= Setting::get(SettingKey::MAX_NUMBER_OF_FREE_COMMENT)) {
                (new TransactionService())
                    ->withdraw($user, Setting::get(SettingKey::COMMENT_CREATION_WITHDRAW))
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