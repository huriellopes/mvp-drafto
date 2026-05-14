<?php

declare(strict_types=1);

return [
    'social' => [
        'subject' => 'New interaction on Drafto: :name',
        'action' => 'View on Site',
        'thanks' => 'Thank you for being part of our community!',
        'messages' => [
            'like_post' => 'liked your post: :title',
            'like_comment' => 'liked your comment',
            'mention' => 'mentioned you in a comment',
            'follow' => 'started following you',
            'default' => 'interacted with you',
        ],
    ],
    'support' => [
        'subject' => '[Support] :subject',
        'greeting' => 'Hello, Team Drafto!',
        'received' => 'You have received a new support message from :name (:email).',
        'subject_line' => 'Subject: :subject',
        'message_line' => 'Message:',
        'respond' => 'Please respond to the user as soon as possible.',
        'action' => 'View Support Dashboard',
        'thanks' => 'Thank you for using our platform!',
    ],
    'report' => [
        'feedback' => [
            'subject' => 'Update on your report - :app',
            'greeting' => 'Hello, :name!',
            'body' => 'Your report regarding :type content has been reviewed by our team.',
            'status' => 'Current status: **:status**',
            'admin_feedback' => 'Moderation message: ":feedback"',
            'thanks' => 'Thank you for helping us keep the community safe.',
            'action' => 'View Guidelines',
            'database_message' => 'Your report has been reviewed.',
        ],
        'banned' => [
            'subject' => 'Your account has been suspended - :app',
            'greeting' => 'Hello, :name.',
            'body' => 'We regret to inform you that your account has been temporarily suspended due to a violation of our guidelines.',
            'reason' => '**Reason for suspension:** :reason',
            'until' => 'Your account will remain blocked until: **:date**',
            'error_contact' => 'If you believe this was a mistake, please contact support.',
            'action' => 'Review Terms of Use',
        ],
    ],
    'auth' => [
        'reset_password' => [
            'subject' => 'Password Recovery on Drafto',
            'title' => 'Password Recovery',
            'body' => 'We received a request to reset the password for your account.<br>If it was you, click the button below to choose a new password.',
            'action' => 'Reset password',
            'expire' => 'This password reset link will expire in :count minutes.<br>If you did not request this, no further action is required.',
            'footer' => 'Protect your account. Do not share this link with anyone.',
        ],
        'verify_email' => [
            'subject' => 'Confirm your email on Drafto',
            'title' => 'Verify your email',
            'greeting' => 'Hello, <strong>:name</strong>!',
            'body' => 'To start writing and publishing on Drafto, please confirm that this email belongs to you by clicking the button below.',
            'action' => 'Confirm email',
            'ignore' => 'If you did not create an account on Drafto, please ignore this email.<br>This link expires in 60 minutes.',
            'footer' => 'Write with clarity. Publish with identity.',
        ],
    ],
    'newsletter' => [
        'subject' => 'News on Drafto: :category',
        'subject_important' => 'Notice: :app',
        'important_title' => 'Important Notice',
        'news_title' => 'News for you',
        'category_label' => 'CATEGORY: :category',
        'read_more' => 'Read more',
        'greeting_reader' => 'Hello, Reader!',
        'default_body' => 'We have important news waiting for you on the platform. Stop by to check out this week\'s exclusive content!',
        'action' => 'Access Drafto',
        'unsubscribe' => 'You received this email because you are subscribed to our newsletter.<br><a href=":url" class="link">Unsubscribe</a> • © :year Drafto',
        'verification' => [
            'subject' => 'Confirm your subscription to Radar Drafto',
            'greeting' => 'Hello!',
            'body1' => 'Thank you for your interest in <strong>Radar Drafto</strong>. We\'re almost there!',
            'body2' => 'To confirm your subscription and start receiving the best stories and news from the platform, please click the button below:',
            'action' => 'Confirm Subscription',
            'ignore' => 'If you did not request this subscription, you can safely ignore this email.',
            'footer' => 'Drafto - Your digital bookshelf of great stories.',
        ],
    ],
    'common' => [
        'platform_footer' => '© :year Drafto. Platform for writers.',
    ],
];
