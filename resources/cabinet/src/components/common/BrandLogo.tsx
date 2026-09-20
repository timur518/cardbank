import { useEffect, useState } from 'react';
import { fetchBrandSettings } from '../../api/settings';

interface BrandLogoProps {
    className?: string;
}

/** Логотип из настроек; пока настройки грузятся
 * или логотип не загружен — показываем название сайта текстом (.brand-mark —
 * тот же класс, что и у логотипа-текста на лендинге). Используется в шапке ЛК
 * (DashboardLayout) и в верхней части карточки входа (LoginPage). */
export function BrandLogo({ className = 'h-8 w-auto' }: BrandLogoProps) {
    const [logo, setLogo] = useState<string | null>(null);
    const [siteName, setSiteName] = useState('CardBank');

    useEffect(() => {
        fetchBrandSettings()
            .then((brand) => {
                setLogo(brand.brand_logo);
                if (brand.brand_site_name) {
                    setSiteName(brand.brand_site_name);
                }
            })
            .catch(() => {
                // Настройки не критичны для рендера — молча остаёмся на дефолте.
            });
    }, []);

    if (logo) {
        return <img src={logo} alt={siteName} className={className} />;
    }

    return <span className="brand-mark">{siteName}</span>;
}
