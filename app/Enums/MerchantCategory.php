<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Категория мерчанта — на что похожа покупка, чтобы клиент понимал, за что списание
 * (используется в {@see \App\Models\Merchant}, определяется по подстроке в merchant-поле
 * {@see \App\Models\CardTransaction}). Список заведомо шире, чем текущий набор
 * засеянных мерчантов — под все направления, которые обычно оплачивают зарубежными
 * виртуальными картами.
 */
enum MerchantCategory: string implements HasLabel
{
    case AiServices = 'ai_services';
    case Streaming = 'streaming';
    case Music = 'music';
    case Gaming = 'gaming';
    case AdultContent = 'adult_content';
    case CreatorSubscriptions = 'creator_subscriptions';
    case Messengers = 'messengers';
    case SocialNetworks = 'social_networks';
    case CloudStorage = 'cloud_storage';
    case DevTools = 'dev_tools';
    case Domains = 'domains';
    case DesignProductivity = 'design_productivity';
    case Marketplaces = 'marketplaces';
    case Travel = 'travel';
    case FoodDelivery = 'food_delivery';
    case Marketing = 'marketing';
    case Finance = 'finance';
    case Education = 'education';
    case Dating = 'dating';
    case VpnSecurity = 'vpn_security';
    case BooksMedia = 'books_media';
    case FitnessWellness = 'fitness_wellness';
    case Telecom = 'telecom';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::AiServices => 'AI сервисы',
            self::Streaming => 'Стриминг и видео',
            self::Music => 'Музыка',
            self::Gaming => 'Игры и гейминг',
            self::AdultContent => 'Контент 18+',
            self::CreatorSubscriptions => 'Подписки на авторов',
            self::Messengers => 'Мессенджеры',
            self::SocialNetworks => 'Социальные сети',
            self::CloudStorage => 'Облачные хранилища',
            self::DevTools => 'Разработка и хостинг',
            self::Domains => 'Домены и сайты',
            self::DesignProductivity => 'Дизайн и продуктивность',
            self::Marketplaces => 'Маркетплейсы и шопинг',
            self::Travel => 'Путешествия',
            self::FoodDelivery => 'Еда и доставка',
            self::Marketing => 'Реклама и маркетинг',
            self::Finance => 'Финансы и криптовалюта',
            self::Education => 'Образование',
            self::Dating => 'Знакомства',
            self::VpnSecurity => 'VPN и безопасность',
            self::BooksMedia => 'Книги и медиа',
            self::FitnessWellness => 'Спорт и здоровье',
            self::Telecom => 'Связь и мобильная связь',
            self::Other => 'Другое',
        };
    }
}
