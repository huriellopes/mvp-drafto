<?php

declare(strict_types=1);

return [

    'user_status' => [
        'active' => 'Active',
        'suspended' => 'Suspended',
        'pending' => 'Pending',
        'inactive' => 'Inactive',
        'banned' => 'Banned',
    ],

    'theme' => [
        'light' => 'Light',
        'dark' => 'Dark',
        'system' => 'System',
    ],

    'link_visibility' => [
        'public' => 'Public',
        'private' => 'Private (inactive)',
    ],

    'social_platform' => [
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'twitter' => 'X / Twitter',
        'linkedin' => 'LinkedIn',
        'youtube' => 'YouTube',
        'github' => 'GitHub',
        'tiktok' => 'TikTok',
        'website' => 'Website / Other',
    ],

    'role' => [
        'super_admin' => 'Super Admin',
        'writer' => 'Writer',
        'reader' => 'Reader',
    ],

    'report_status' => [
        'pending' => 'Pending',
        'reviewed' => 'Reviewed',
        'dismissed' => 'Dismissed',
        'action_taken' => 'Action Taken',
    ],

    'report_reason' => [
        'spam' => 'Spam',
        'abuse' => 'Abuse',
        'harassment' => 'Harassment',
        'plagiarism' => 'Plagiarism',
        'inappropriate' => 'Inappropriate',
        'other' => 'Other',
    ],

    'post_visibility' => [
        'public' => 'Public',
        'unlisted' => 'Unlisted',
        'followers_only' => 'Followers Only',
    ],

    'post_status' => [
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ],

    'post_type' => [
        'post' => 'Post',
        'article' => 'Article',
    ],

    'comment_status' => [
        'visible' => 'Visible',
        'hidden' => 'Hidden',
        'pending' => 'Pending',
    ],

    'update_audience' => [
        'all' => 'Everyone',
        'writers' => 'Writers',
        'readers' => 'Readers',
        'description' => [
            'all' => 'All eligible users.',
            'writers' => 'Writers only.',
            'readers' => 'Readers only.',
        ],
    ],

];
