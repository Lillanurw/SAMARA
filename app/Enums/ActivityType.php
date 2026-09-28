<?php

namespace App\Enums;

enum ActivityType: string
{
    case SALES_VISIT = 'SALES_VISIT';
    case ACCOUNT_MAINTENANCE = 'ACCOUNT_MAINTENANCE';
    case PRODUCT_DEMO = 'PRODUCT_DEMO';
    case TECHNICAL_SURVEY = 'TECHNICAL_SURVEY';
    case MARKETING_ENGAGEMENT = 'MARKETING_ENGAGEMENT';
    case EVENT = 'EVENT';
    case OTHER = 'OTHER';

    public function label(): string
    {
        return match($this) {
            self::SALES_VISIT => 'Sales Visit',
            self::ACCOUNT_MAINTENANCE => 'Account Maintenance',
            self::PRODUCT_DEMO => 'Product Demo',
            self::TECHNICAL_SURVEY => 'Technical Survey',
            self::MARKETING_ENGAGEMENT => 'Marketing Engagement',
            self::EVENT => 'Event / Exhibition',
            self::OTHER => 'Lainnya',
        };
    }
}
