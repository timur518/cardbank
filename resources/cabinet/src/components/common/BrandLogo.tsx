import { useEffect, useState } from 'react';
import { fetchBrandSettings } from '../../api/settings';

interface BrandLogoProps {
    className?: string;
}

// Тот же вордмарк, что и в шапке лендинга (site-header.blade.php) и в прелоадере (Preloader.tsx) —
// ЛК живёт на отдельном поддомене (mne.mojno.cc), поэтому ссылка абсолютная, на основной домен.
const DEFAULT_LOGO = 'https://mojno.cc/assets/images/logo.svg';

/** Показывает логотип из настроек админки (BrandSettings), а до их загрузки и если админ
 * ничего не загружал — реальный логотип сайта (DEFAULT_LOGO), без текстовой заглушки.
 * Используется в шапке ЛК (DashboardLayout) и в верхней части карточки входа (LoginPage). */
export function BrandLogo({ className = 'h-8 w-auto' }: BrandLogoProps) {
    const [logo, setLogo] = useState(DEFAULT_LOGO);
    const [siteName, setSiteName] = useState('Можно');

    useEffect(() => {
        fetchBrandSettings()
            .then((brand) => {
                if (brand.brand_logo) {
                    setLogo(brand.brand_logo);
                }
                if (brand.brand_site_name) {
                    setSiteName(brand.brand_site_name);
                }
            })
            .catch(() => {
                // Настройки не критичны для рендера — молча остаёмся на дефолте.
            });
    }, []);

    return <img src={logo} alt={siteName} className={className} />;
}
