<?php

namespace App\Actions\Comments;

use App\Models\Module;
use App\Enums\ModuleEnum;
use App\Models\Comment;

class ProcessCommentModeration
{
    public function execute(Comment $comment): void
    {
        // 1. Recupera as configurações do módulo de comentários
        $module = Module::where('slug', ModuleEnum::COMMENTS)->first();

        // 2. Se o módulo não existir ou estiver desativado, tratamos como erro ou padrão seguro
        if (!$module || !$module->is_enabled) {
            $comment->update(['status' => 'pending']);
            return;
        }

        // 3. Lê a regra de moderação das settings (definida no nosso Seeder)
        // Se 'moderation_queue' for true, o comentário nasce como 'pending'
        $requiresModeration = $module->getSetting('moderation_queue', true);

        if ($requiresModeration) {
            $comment->update([
                'status' => 'pending',
                'published_at' => null
            ]);
        } else {
            $comment->update([
                'status' => 'approved',
                'published_at' => now()
            ]);
        }
    }
}
